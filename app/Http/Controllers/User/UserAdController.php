<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdTemplate;
use App\Models\Category;
use App\Models\UserAd;
use App\Support\AdSizes;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserAdController extends Controller
{
    public function index(Request $request): View
    {
        $ads = UserAd::query()
            ->with(['template:id,name,size_type', 'category:id,name', 'subcategory:id,name'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('backend.ads.user.index', [
            'ads' => $ads,
            'sizes' => AdSizes::all(),
        ]);
    }

    public function selectSize(): View
    {
        $user = request()->user();

        return view('backend.ads.user.select-size', [
            'sizes' => $this->visibleSizesForUser($user),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $ads = UserAd::query()
            ->with(['template:id,name', 'category:id,name', 'subcategory:id,name'])
            ->where('user_id', $request->user()->id)
            ->latest();

        $sizes = AdSizes::all();

        return DataTables::of($ads)
            ->addColumn('size_label', fn (UserAd $ad) => $sizes[$ad->size_type]['name'] ?? $ad->size_type)
            ->addColumn('template_name', fn (UserAd $ad) => $ad->template?->name ?? '-')
            ->addColumn('category_name', fn (UserAd $ad) => $ad->category?->name ?? '-')
            ->addColumn('subcategory_name', fn (UserAd $ad) => $ad->subcategory?->name ?? '-')
            ->addColumn('location_name', fn (UserAd $ad) => $ad->location ?? '-')
            ->addColumn('status_badge', function (UserAd $ad) {
                $badge = match ($ad->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'pending' => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-'.$badge.'">'.ucfirst($ad->status).'</span>';
            })
            ->editColumn('submitted_at', fn (UserAd $ad) => $ad->submitted_at?->format('Y-m-d H:i') ?? '-')
            ->addColumn('actions', fn (UserAd $ad) => '<div class="d-flex justify-content-end"><a href="'.route('ads.show', $ad).'" class="btn btn-sm btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></a></div>')
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(Request $request, UserAd $ad): View
    {
        abort_unless($ad->user_id === $request->user()->id, 404);

        $ad->load(['template:id,name,size_type', 'category:id,name', 'subcategory:id,name']);

        return view('backend.ads.user.show', [
            'ad' => $ad,
            'size' => AdSizes::all()[$ad->size_type] ?? null,
        ]);
    }

    public function selectTemplate(string $sizeType): View
    {
        abort_unless(AdSizes::exists($sizeType), 404);
        abort_unless($this->canUserAccessSize(request()->user(), $sizeType), 404);

        $templates = AdTemplate::query()
            ->where('size_type', $sizeType)
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('backend.ads.user.select-template', [
            'sizeType' => $sizeType,
            'size' => AdSizes::all()[$sizeType],
            'templates' => $templates,
        ]);
    }

    public function customize(string $sizeType, AdTemplate $template): View
    {
        abort_unless(AdSizes::exists($sizeType), 404);
        abort_unless($this->canUserAccessSize(request()->user(), $sizeType), 404);
        abort_unless($template->size_type === $sizeType, 404);
        abort_if(! $template->is_active, 404);

        return view('backend.ads.user.customize', [
            'sizeType' => $sizeType,
            'size' => AdSizes::all()[$sizeType],
            'template' => $template,
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->whereJsonContains('modules', 'ads')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function subcategories(Category $category): JsonResponse
    {
        abort_if(! in_array('ads', $category->modules ?? [], true), 404);

        return response()->json(
            $category->children()
                ->whereJsonContains('modules', 'ads')
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function store(Request $request, string $sizeType, AdTemplate $template): RedirectResponse
    {
        abort_unless(AdSizes::exists($sizeType), 404);
        abort_unless($this->canUserAccessSize($request->user(), $sizeType), 404);
        abort_unless($template->size_type === $sizeType, 404);
        abort_if(! $template->is_active, 404);

        $schema = is_array($template->schema_json) ? $template->schema_json : [];
        $fieldRules = [];
        $imageKeys = [];

        $hasCustomHtml = trim((string) $request->input('custom_html', '')) !== '';

        foreach (($schema['fields'] ?? []) as $field) {
            $key = (string) ($field['key'] ?? '');
            $type = (string) ($field['type'] ?? 'text');
            $required = (bool) ($field['required'] ?? false);
            $max = (int) ($field['max'] ?? 0);

            if ($key === '' || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $key)) {
                continue;
            }

            if ($type === 'image') {
                $imageKeys[] = $key;
                $fieldRules[$key] = array_filter([
                    $required ? 'required' : 'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ]);
            } else {
                $rule = ($required && !$hasCustomHtml) ? 'required|string' : 'nullable|string';
                if ($max > 0) {
                    $rule .= '|max:'.$max;
                }
                $fieldRules[$key] = $rule;
            }
        }

        $validated = $request->validate(array_merge([
            'title' => 'required|string|max:140',
            'custom_html' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'generated_image_data' => ['nullable', 'string'],
            'accept_terms' => 'accepted',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->whereNull('parent_id')
                    ->whereJsonContains('modules', 'ads')),
            ],
            'subcategory_id' => ['required', Rule::exists('categories', 'id')],
            'location' => 'required|string|max:255',
            'location_lat' => 'required|numeric|between:-90,90',
            'location_lng' => 'required|numeric|between:-180,180',
        ], $fieldRules));

        $isValidSubcategory = Category::query()
            ->where('id', $validated['subcategory_id'])
            ->where('parent_id', $validated['category_id'])
            ->whereJsonContains('modules', 'ads')
            ->exists();

        if (! $isValidSubcategory) {
            return back()->withErrors([
                'subcategory_id' => 'Selected subcategory does not belong to the selected category.',
            ])->withInput();
        }

        $fields = [];
        foreach (($schema['fields'] ?? []) as $field) {
            $key = (string) ($field['key'] ?? '');
            if ($key === '' || !array_key_exists($key, $validated)) {
                continue;
            }
            if (in_array($key, $imageKeys, true)) {
                continue;
            }
            $fields[$key] = $validated[$key];
        }

        $user = $request->user();

        $ad = DB::transaction(function () use ($request, $template, $sizeType, $validated, $fields, $imageKeys, $user) {
            foreach ($imageKeys as $key) {
                if (!$request->hasFile($key)) {
                    continue;
                }

                $file = $request->file($key);
                $ext = $file->getClientOriginalExtension() ?: $file->extension();
                $fileName = $key.'-'.Str::uuid().'.'.$ext;
                $relativeDirectory = 'uploads/ads/assets';
                $absoluteDirectory = public_path($relativeDirectory);

                if (!is_dir($absoluteDirectory)) {
                    mkdir($absoluteDirectory, 0755, true);
                }

                $file->move($absoluteDirectory, $fileName);
                $fields[$key] = $relativeDirectory.'/'.$fileName;
            }

            $layoutHtml = trim((string) ($validated['custom_html'] ?? '')) !== ''
                ? (string) $validated['custom_html']
                : (string) $template->layout_html;
            $layoutCss = (string) ($validated['custom_css'] ?? '');

            $renderedHtml = $this->renderTemplateHtml($layoutHtml, $fields);

            $size = AdSizes::all()[$sizeType] ?? null;
            $targetWidth = (int) ($size['w'] ?? 0);
            $targetHeight = (int) ($size['h'] ?? 0);
            $finalImagePath = $this->storeGeneratedAdImageFromHtml($renderedHtml, $layoutCss, $targetWidth, $targetHeight);
            if ($finalImagePath === null) {
                $finalImagePath = $this->storeGeneratedAdImage(
                    $validated['generated_image_data'] ?? '',
                    $targetWidth,
                    $targetHeight,
                );
            }

            return UserAd::create([
                'user_id' => $user->id,
                'ad_template_id' => $template->id,
                'size_type' => $sizeType,
                'title' => $validated['title'],
                'category_id' => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'],
                'location' => $validated['location'],
                'location_lat' => $validated['location_lat'],
                'location_lng' => $validated['location_lng'],
                'status' => 'pending',
                'fields_json' => $fields,
                'rendered_html' => $renderedHtml,
                'final_image' => $finalImagePath,
                'submitted_at' => now(),
            ]);
        });

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Your ad was submitted for admin approval.',
                'redirect_url' => route('ads.index'),
                'id' => $ad->id,
            ]);
        }

        return redirect()->route('ads.index')->with('success', 'Your ad was submitted for admin approval.');
    }

    private function renderTemplateHtml(string $layoutHtml, array $fields): string
    {
        $html = $layoutHtml;

        foreach ($fields as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            $isUpload = str_starts_with($value, 'uploads/');
            $replacement = $isUpload ? asset($value) : e($value);

            // 1) Replace text placeholders like {{headline}}
            $html = str_replace('{{'.$key.'}}', $replacement, $html);

            // 2) If an image field uses data-ad-key, inject the src for saved HTML
            if ($isUpload) {
                $quotedKey = preg_quote($key, '/');
                $html = preg_replace(
                    '/(<img[^>]*data-ad-key="'.$quotedKey.'"[^>]*src=")[^"]*(")/i',
                    '$1'.$replacement.'$2',
                    $html
                ) ?? $html;
                $html = preg_replace(
                    "/(<img[^>]*data-ad-key='".$quotedKey."'[^>]*src=')[^']*(')/i",
                    '$1'.$replacement.'$2',
                    $html
                ) ?? $html;

                // If src is missing, add it.
                $html = preg_replace(
                    '/(<img[^>]*data-ad-key="'.$quotedKey.'"[^>]*)(>)/i',
                    '$1 src="'.$replacement.'"$2',
                    $html
                ) ?? $html;
                $html = preg_replace(
                    "/(<img[^>]*data-ad-key='".$quotedKey."'[^>]*)(>)/i",
                    '$1 src="'.$replacement.'"$2',
                    $html
                ) ?? $html;

                $html = preg_replace_callback(
                    '/<img[^>]*data-ad-key=(["\'])'.$quotedKey.'\1[^>]*>/i',
                    fn (array $m) => $this->applyDefaultObjectFitToImageTag($m[0]),
                    $html
                ) ?? $html;
            }
        }

        $html = preg_replace('/\{\{[a-zA-Z][a-zA-Z0-9_]*\}\}/', '', $html) ?? $html;

        return $html;
    }

    private function applyDefaultObjectFitToImageTag(string $tag): string
    {
        if (stripos($tag, 'object-fit:') !== false) {
            $tag = preg_replace('/object-fit\s*:\s*[^;"]+/i', 'object-fit:contain', $tag) ?? $tag;
            $tag = preg_replace('/object-position\s*:\s*[^;"]+/i', 'object-position:center', $tag) ?? $tag;

            return $tag;
        }

        if (preg_match('/style=(["\'])(.*?)\1/i', $tag, $matches) === 1) {
            $quote = $matches[1];
            $style = rtrim($matches[2], '; ');
            $newStyle = $style.';object-fit:contain;object-position:center;';

            return str_replace($matches[0], 'style='.$quote.$newStyle.$quote, $tag);
        }

        return preg_replace('/>$/', ' style="object-fit:contain;object-position:center;">', $tag) ?? $tag;
    }

    private function storeGeneratedAdImage(string $base64Png, int $targetWidth, int $targetHeight): string
    {
        if (!preg_match('/^data:image\/png;base64,/', $base64Png)) {
            throw ValidationException::withMessages([
                'generated_image_data' => 'Unable to generate ad image. Please refresh and try again.',
            ]);
        }

        $decoded = base64_decode(substr($base64Png, strpos($base64Png, ',') + 1), true);
        if ($decoded === false) {
            throw ValidationException::withMessages([
                'generated_image_data' => 'Generated ad image data is invalid. Please try again.',
            ]);
        }

        $srcW = 0;
        $srcH = 0;
        if ($targetWidth > 0 && $targetHeight > 0) {
            $source = @imagecreatefromstring($decoded);
            if (is_resource($source) || is_object($source)) {
                $srcW = (int) imagesx($source);
                $srcH = (int) imagesy($source);
                imagedestroy($source);

                if ($srcW < $targetWidth || $srcH < $targetHeight) {
                    throw ValidationException::withMessages([
                        'generated_image_data' => 'Generated image quality is too low. Please use the live preview export again.',
                    ]);
                }

                $targetRatio = $targetWidth / $targetHeight;
                $sourceRatio = $srcW / $srcH;
                if (abs($sourceRatio - $targetRatio) > 0.02) {
                    throw ValidationException::withMessages([
                        'generated_image_data' => 'Generated image ratio does not match template size. Please regenerate from live preview.',
                    ]);
                }
            }
        }

        $relativeDirectory = 'uploads/ads/final';
        $absoluteDirectory = public_path($relativeDirectory);
        if (!is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0755, true);
        }

        $fileName = 'ad-'.Str::uuid().'.png';
        $absolutePath = $absoluteDirectory.'/'.$fileName;
        file_put_contents($absolutePath, $decoded);

        return $relativeDirectory.'/'.$fileName;
    }

    private function storeGeneratedAdImageFromHtml(string $renderedHtml, string $layoutCss, int $targetWidth, int $targetHeight): ?string
    {
        if (!class_exists(Dompdf::class)) {
            return null;
        }

        try {
            $options = new Options();
            $options->setIsRemoteEnabled(true);
            $options->setIsHtml5ParserEnabled(true);
            $options->setDefaultFont('DejaVu Sans');
            $options->setChroot(public_path());
            $options->set('dpi', 300);
            $options->set('isFontSubsettingEnabled', true);
            $options->set('pdfBackend', 'GD');

            $dompdf = new Dompdf($options);
            $paperWidth = max(1, $targetWidth);
            $paperHeight = max(1, $targetHeight);
            $dompdf->setPaper([0, 0, $paperWidth, $paperHeight]);
            $dompdf->loadHtml($this->wrapHtmlForDompdf($renderedHtml, $layoutCss, $paperWidth, $paperHeight), 'UTF-8');
            $dompdf->render();

            $canvas = $dompdf->getCanvas();
            if (!method_exists($canvas, 'get_image')) {
                return null;
            }

            $image = $canvas->get_image();
            if (!is_resource($image) && !is_object($image)) {
                return null;
            }

            $relativeDirectory = 'uploads/ads/final';
            $absoluteDirectory = public_path($relativeDirectory);
            if (!is_dir($absoluteDirectory)) {
                mkdir($absoluteDirectory, 0755, true);
            }

            $fileName = 'ad-'.Str::uuid().'.png';
            $absolutePath = $absoluteDirectory.'/'.$fileName;
            imagepng($image, $absolutePath, 9);
            imagedestroy($image);

            return $relativeDirectory.'/'.$fileName;
        } catch (\Throwable) {
            return null;
        }
    }

    private function wrapHtmlForDompdf(string $html, string $layoutCss, int $width, int $height): string
    {
        $layoutCss = trim($layoutCss);
        $sanitizedCss = preg_replace('/<\/?style[^>]*>/i', '', $layoutCss) ?? $layoutCss;

        return '<!doctype html><html><head><meta charset="utf-8"><style>'
            .'@page{margin:0;}'
            .'html,body{margin:0;padding:0;width:'.$width.'px;height:'.$height.'px;overflow:hidden;}'
            .'img{max-width:100%;}'
            .$sanitizedCss
            .'</style></head><body>'.$html.'</body></html>';
    }

    private function normalizeGeneratedAdImage(string $absolutePath, int $targetWidth, int $targetHeight): void
    {
        if ($targetWidth <= 0 || $targetHeight <= 0 || !is_file($absolutePath)) {
            return;
        }

        $raw = file_get_contents($absolutePath);
        if ($raw === false) {
            return;
        }

        $source = @imagecreatefromstring($raw);
        if (!is_resource($source) && !is_object($source)) {
            return;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($source);

            return;
        }

        if ($srcW === $targetWidth && $srcH === $targetHeight) {
            imagedestroy($source);

            return;
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if (function_exists('imagesetinterpolation') && defined('IMG_BICUBIC')) {
            imagesetinterpolation($canvas, IMG_BICUBIC);
        }
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);

        $scale = min($targetWidth / $srcW, $targetHeight / $srcH);
        $drawW = (int) max(1, round($srcW * $scale));
        $drawH = (int) max(1, round($srcH * $scale));
        $offsetX = (int) floor(($targetWidth - $drawW) / 2);
        $offsetY = (int) floor(($targetHeight - $drawH) / 2);

        imagecopyresampled($canvas, $source, $offsetX, $offsetY, 0, 0, $drawW, $drawH, $srcW, $srcH);
        if (function_exists('imageconvolution')) {
            @imageconvolution($canvas, [[-1, -1, -1], [-1, 16, -1], [-1, -1, -1]], 8, 0);
        }
        imagepng($canvas, $absolutePath, 9);

        imagedestroy($canvas);
        imagedestroy($source);
    }

    private function visibleSizesForUser($user): array
    {
        $isAdmin = (bool) ($user?->isAdmin());

        return array_filter(
            AdSizes::all(),
            fn (array $size) => ($size['admin_only'] ?? false) === $isAdmin
        );
    }

    private function canUserAccessSize($user, string $sizeType): bool
    {
        return array_key_exists($sizeType, $this->visibleSizesForUser($user));
    }
}
