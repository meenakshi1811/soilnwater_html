<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdTemplate;
use App\Models\Category;
use App\Models\UserAd;
use App\Support\AdSizes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

            $renderedHtml = $this->renderTemplateHtml($layoutHtml, $fields);

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
            }
        }

        $html = preg_replace('/\{\{[a-zA-Z][a-zA-Z0-9_]*\}\}/', '', $html) ?? $html;

        return $html;
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
