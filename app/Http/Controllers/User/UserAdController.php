<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdTemplate;
use App\Models\Category;
use App\Models\AdSize;
use App\Models\UserAd;
use App\Models\ContactSupport;
use App\Support\AdSizes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
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
        $isStaff = (bool) ($user?->isStaff());
        $visibleSizes = AdSizes::visibleFor($user);
        $inactiveSizes = AdSize::query()
            ->when(! $isStaff, fn ($query) => $query->where('admin_only', false))
            ->where('is_active', false)
            ->orderBy('name')
            ->get(['size_key', 'name', 'width', 'height'])
            ->mapWithKeys(function (AdSize $size) {
                return [
                    $size->size_key => [
                        'name' => $size->name,
                        'w' => (int) $size->width,
                        'h' => (int) $size->height,
                    ],
                ];
            })
            ->all();

        return view('backend.ads.user.select-size', [
            'sizes' => $visibleSizes,
            'inactiveSizes' => $inactiveSizes,
            'paidSizeAccess' => $this->paidSizeAccessMap($user),
        ]);
    }

    public function requestCustomization(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'size_type' => ['required', 'string'],
            'details' => ['required', 'string', 'max:2000'],
        ]);

        $isStaff = (bool) ($request->user()?->isStaff());
        $inactiveSizes = AdSize::query()
            ->when(! $isStaff, fn ($query) => $query->where('admin_only', false))
            ->where('is_active', false)
            ->get(['size_key', 'name', 'width', 'height'])
            ->mapWithKeys(function (AdSize $size) {
                return [
                    $size->size_key => [
                        'name' => $size->name,
                        'w' => (int) $size->width,
                        'h' => (int) $size->height,
                    ],
                ];
            });

        if (! $inactiveSizes->has($validated['size_type'])) {
            throw ValidationException::withMessages([
                'size_type' => 'Please select a valid inactive size.',
            ]);
        }

        $size = $inactiveSizes->get($validated['size_type']);
        $user = $request->user();
        $body = view('emails.ads.customization-request', [
            'user' => $user,
            'sizeType' => $validated['size_type'],
            'size' => $size,
            'details' => $validated['details'],
        ])->render();

        Mail::send([], [], function ($message) use ($user, $body) {
            $message->to(config('services.email.admin_email'))
                ->subject('Ad Size Customization Request from '.$user->name)
                ->html($body);
        });

        return response()->json(['message' => 'Your customization request has been sent to admin successfully.']);
    }


    public function contactSupport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $entry = ContactSupport::query()->create([
            'user_id' => $request->user()?->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        $adminEmail = config('services.email.admin_email');
        if ($adminEmail) {
            $user = $request->user();
            $body = view('emails.ads.contact-support-request', [
                'user' => $user,
                'entry' => $entry,
            ])->render();

            Mail::send([], [], function ($message) use ($adminEmail, $entry, $body) {
                $message->to($adminEmail)
                    ->subject('Contact Support: '.$entry->subject)
                    ->html($body);
            });
        }

        return response()->json(['message' => 'Your support request has been sent successfully.']);
    }

    public function customizeFromSize(string $sizeType): RedirectResponse|View
    {
       
        $sizeType = $this->resolveSizeType($sizeType);
        abort_unless(AdSizes::exists($sizeType), 404);
        if (! $this->canUserAccessSize(request()->user(), $sizeType)) {
            return redirect()->route('ads.create.size')->withErrors([
                'size_type' => 'Please select and pay for this ad size before continuing.',
            ]);
        }

        // $template = AdTemplate::query()
        //     ->where('size_type', $sizeType)
        //     ->where('is_active', true)
        //     ->latest()
        //     ->first();

        // abort_if(! $template, 404, 'No active template found for this size.');

        return view('backend.ads.user.customize-size', [
            'sizeType' => $sizeType,
            'size' => AdSizes::all()[$sizeType],
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->whereJsonContains('modules', 'ads')
                ->orderBy('name')
                ->get(['id', 'name']),
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
            ->addColumn('banner_preview', function (UserAd $ad) {
                if (! $ad->final_image) {
                    return '-';
                }

                $imageUrl = asset($ad->final_image);

                return '<a href="'.$imageUrl.'" target="_blank" rel="noopener noreferrer">'
                    .'<img src="'.$imageUrl.'" alt="'.$ad->title.' banner" style="width: 96px; height: 56px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">'
                    .'</a>';
            })
            ->editColumn('submitted_at', fn (UserAd $ad) => $ad->submitted_at?->format('Y-m-d H:i') ?? '-')
            ->addColumn('valid_until', fn (UserAd $ad) => $ad->valid_until?->format('Y-m-d') ?? 'No Expiry')
            ->addColumn('actions', fn (UserAd $ad) => '<div class="d-flex justify-content-end gap-2"><a href="'.route('ads.show', $ad).'" class="btn btn-sm btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></a><a href="'.route('ads.edit', $ad).'" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fa-solid fa-pen"></i></a><button type="button" class="btn btn-sm btn-outline-danger js-delete-user-ad" data-id="'.$ad->id.'" title="Delete"><i class="fa-solid fa-trash"></i></button></div>')
            ->rawColumns(['status_badge', 'banner_preview', 'actions'])
            ->make(true);
    }





    public function edit(Request $request, UserAd $ad): View
    {
        abort_unless($ad->user_id === $request->user()->id, 404);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'ads')
            ->orderBy('name')
            ->get(['id', 'name']);

        $subcategories = Category::query()
            ->where('parent_id', $ad->category_id)
            ->whereJsonContains('modules', 'ads')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('backend.ads.user.customize-size', [
            'ad' => $ad,
            'isEdit' => true,
            'sizeType' => $ad->size_type,
            'size' => AdSizes::all()[$ad->size_type] ?? null,
            'categories' => $categories,
            'subcategories' => $subcategories,
        ]);
    }

    public function update(Request $request, UserAd $ad): RedirectResponse|JsonResponse
    {
        abort_unless($ad->user_id === $request->user()->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:140',
            'short_description' => 'nullable|string|max:300',
            'category_id' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->whereNull('parent_id')->whereJsonContains('modules', 'ads'))],
            'subcategory_id' => ['required', Rule::exists('categories', 'id')],
            'location' => 'required|string|max:255',
            'location_lat' => 'required|numeric|between:-90,90',
            'location_lng' => 'required|numeric|between:-180,180',
            'valid_until' => 'required|date|after_or_equal:today',
            'is_sponsored' => 'nullable|in:0,1',
            'generated_image_data' => 'nullable|string|starts_with:data:image/png;base64,',
        ]);

        if ($request->filled('generated_image_data')) {
            $size = AdSizes::all()[$ad->size_type] ?? ['w' => 0, 'h' => 0];
            if ($ad->final_image) {
                File::delete(public_path($ad->final_image));
            }
            $ad->final_image = $this->storeGeneratedAdImage($validated['generated_image_data'], (int) $size['w'], (int) $size['h']);
        }

        $ad->fill([
            'title' => $validated['title'],
            'short_description' => $validated['short_description'] ?? null,
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'],
            'location' => $validated['location'],
            'location_lat' => $validated['location_lat'],
            'location_lng' => $validated['location_lng'],
            'valid_until' => $validated['valid_until'],
            'is_sponsored' => $request->user()->isStaff() ? (bool) ($validated['is_sponsored'] ?? false) : false,
        ]);

        $ad->save();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Ad updated and submitted for admin approval.',
                'redirect_url' => route('ads.index'),
            ]);
        }

        return redirect()->route('ads.index')->with('success', 'Ad updated and submitted for admin approval.');
    }
    public function destroy(Request $request, UserAd $ad): RedirectResponse|JsonResponse
    {
        abort_unless($ad->user_id === $request->user()->id, 404);

        if ($ad->final_image) {
            File::delete(public_path($ad->final_image));
        }

        $ad->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => 'Ad deleted successfully.']);
        }

        return back()->with('success', 'Ad deleted successfully.');
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

    public function selectTemplate(string $sizeType): RedirectResponse|View
    {
        $sizeType = $this->resolveSizeType($sizeType);
        abort_unless(AdSizes::exists($sizeType), 404);
        if (! $this->canUserAccessSize(request()->user(), $sizeType)) {
            return redirect()->route('ads.create.size')->withErrors([
                'size_type' => 'Please select and pay for this ad size before continuing.',
            ]);
        }

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

    public function customize(string $sizeType, AdTemplate $template): RedirectResponse|View
    {
        $sizeType = $this->resolveSizeType($sizeType);
        abort_unless(AdSizes::exists($sizeType), 404);
        if (! $this->canUserAccessSize(request()->user(), $sizeType)) {
            return redirect()->route('ads.create.size')->withErrors([
                'size_type' => 'Please select and pay for this ad size before continuing.',
            ]);
        }
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


    public function markSizeAsPaid(Request $request, string $sizeType): JsonResponse
    {
        $sizeType = $this->resolveSizeType($sizeType);
        abort_unless(AdSizes::exists($sizeType), 404);

        $size = AdSizes::all()[$sizeType];
        abort_unless((bool) ($size['is_paid'] ?? false), 422);

        $paidSizes = Session::get('ads_paid_sizes', []);
        if (! in_array($sizeType, $paidSizes, true)) {
            $paidSizes[] = $sizeType;
            Session::put('ads_paid_sizes', $paidSizes);
        }

        return response()->json([
            'success' => true,
            'redirect_url' => route('ads.create.customize.default', ['sizeType' => $sizeType]),
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

    public function store(Request $request, string $sizeType): RedirectResponse|JsonResponse
    {
        $sizeType = $this->resolveSizeType($sizeType);
        abort_unless(AdSizes::exists($sizeType), 404);
        if (! $this->canUserAccessSize($request->user(), $sizeType)) {
            return redirect()->route('ads.create.size')->withErrors([
                'size_type' => 'Please select and pay for this ad size before continuing.',
            ]);
        }

       

        // abort_if(! $templateId, 404, 'No active template found for this size.');

        $validated = $request->validate(array_merge([
            'title' => 'required|string|max:140',
            'short_description' => 'nullable|string|max:300',
            'custom_html' => 'nullable|string',
            'ad_image_input_type' => 'nullable|in:1,2',
            'generated_image_data' => ['required', 'string', 'starts_with:data:image/png;base64,'],
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
            'valid_until' => 'required|date|after_or_equal:today',
            'is_sponsored' => 'nullable|in:0,1',
        ]));

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

        $user = $request->user();

        $size = AdSizes::all()[$sizeType] ?? null;
        $categoryPrice = $size['category_prices'][(int) $validated['category_id']] ?? null;
        if ((bool) ($size['is_paid'] ?? false) && $categoryPrice === null && ! (bool) ($user?->isAdmin())) {
            return back()->withErrors([
                'category_id' => 'No price is configured for this category and ad size.',
            ])->withInput();
        }

        $targetWidth = (int) ($size['w'] ?? 0);
        $targetHeight = (int) ($size['h'] ?? 0);

        $pricing = $this->buildPricingDetails($categoryPrice, $validated['valid_until']);

        $ad = DB::transaction(function () use ($sizeType, $validated, $fields, $user, $targetWidth, $targetHeight, $pricing) {
            $layoutHtml = (string) ($validated['custom_html'] ?? '');
            $renderedHtml = $layoutHtml;

            $finalImagePath = $this->storeGeneratedAdImage(
                $validated['generated_image_data'] ?? '',
                $targetWidth,
                $targetHeight,
            );

            $isSponsored = $user->isStaff() ? (bool) ($validated['is_sponsored'] ?? false) : false;

            return UserAd::create([
                'user_id' => $user->id,
                'ad_template_id' => null,
                'size_type' => $sizeType,
                'title' => $validated['title'],
                'short_description' => $validated['short_description'] ?? null,
                'category_id' => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'],
                'location' => $validated['location'],
                'location_lat' => $validated['location_lat'],
                'location_lng' => $validated['location_lng'],
                'status' => 'pending',
                'fields_json' => $fields,
                'rendered_html' => $renderedHtml,
                'final_image' => $finalImagePath,
                'valid_until' => $validated['valid_until'],
                'submitted_at' => now(),
                'is_sponsored' => $isSponsored,
                'base_price_per_day' => $pricing['base_price_per_day'],
                'total_days' => $pricing['total_days'],
                'subtotal' => $pricing['subtotal'],
                'gst_rate' => $pricing['gst_rate'],
                'gst_amount' => $pricing['gst_amount'],
                'grand_total' => $pricing['grand_total'],
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


    private function buildPricingDetails(?float $categoryPrice, string $validUntil): array
    {
        if ($categoryPrice === null || $categoryPrice <= 0) {
            return [
                'base_price_per_day' => null,
                'total_days' => null,
                'subtotal' => null,
                'gst_rate' => null,
                'gst_amount' => null,
                'grand_total' => null,
            ];
        }

        $startDate = Carbon::today();
        $endDate = Carbon::parse($validUntil)->startOfDay();
        $days = max(1, $startDate->diffInDays($endDate) + 1);

        $subtotal = round($categoryPrice * $days, 2);
        $gstRate = 5.00;
        $gstAmount = round($subtotal * ($gstRate / 100), 2);
        $grandTotal = round($subtotal + $gstAmount, 2);

        return [
            'base_price_per_day' => round($categoryPrice, 2),
            'total_days' => $days,
            'subtotal' => $subtotal,
            'gst_rate' => $gstRate,
            'gst_amount' => $gstAmount,
            'grand_total' => $grandTotal,
        ];
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
            $tag = preg_replace('/object-fit\s*:\s*[^;"]+/i', 'object-fit:cover', $tag) ?? $tag;
            $tag = preg_replace('/object-position\s*:\s*[^;"]+/i', 'object-position:center', $tag) ?? $tag;

            return $tag;
        }

        if (preg_match('/style=(["\'])(.*?)\1/i', $tag, $matches) === 1) {
            $quote = $matches[1];
            $style = rtrim($matches[2], '; ');
            $newStyle = $style.';object-fit:cover;object-position:center;';

            return str_replace($matches[0], 'style='.$quote.$newStyle.$quote, $tag);
        }

        return preg_replace('/>$/', ' style="object-fit:cover;object-position:center;">', $tag) ?? $tag;
    }

    private function storeGeneratedAdImage(string $base64Png, int $targetWidth, int $targetHeight): string
    {
        if (!preg_match('/^data:image\/png;base64,/', $base64Png)) {
            throw ValidationException::withMessages([
                'generated_image_data' => 'Only PNG ad exports are supported.',
            ]);
        }

        $decoded = base64_decode(substr($base64Png, strpos($base64Png, ',') + 1), true);

        if ($decoded === false) {
            throw ValidationException::withMessages([
                'generated_image_data' => 'Invalid generated ad image data.',
            ]);
        }

        $relativeDirectory = 'uploads/ads/final';
        $absoluteDirectory = public_path($relativeDirectory);
        if (!is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0755, true);
        }

        $fileName = 'ad-'.Str::uuid().'.png';
        $absolutePath = $absoluteDirectory.'/'.$fileName;
        file_put_contents($absolutePath, $decoded);

        $this->normalizeGeneratedAdImage($absolutePath, $targetWidth, $targetHeight);

        return $relativeDirectory.'/'.$fileName;
    }

    private function normalizeGeneratedAdImage(string $absolutePath, int $targetWidth, int $targetHeight): void
    {
        if ($targetWidth <= 0 || $targetHeight <= 0 || !is_file($absolutePath)) {
            return;
        }

        try {
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($absolutePath);

            if ($image->width() === $targetWidth && $image->height() === $targetHeight) {
                return;
            }

            if ($image->width() >= $targetWidth && $image->height() >= $targetHeight) {
                $image->cover($targetWidth, $targetHeight);
            } else {
                $image->scaleDown($targetWidth, $targetHeight);
                $canvas = $manager->create($targetWidth, $targetHeight)->fill('ffffff');
                $canvas->place($image, 'center');
                $image = $canvas;
            }

            $image->toPng()->save($absolutePath);
        } catch (\Throwable) {
            // Keep original generated image if Intervention processing fails.
        }
    }

    private function storeAndResizeUploadedAsset(
        UploadedFile $file,
        string $key,
        int $targetWidth,
        int $targetHeight,
        bool $shouldResize = true
    ): string
    {
        $relativeDirectory = 'uploads/ads/assets';
        $absoluteDirectory = public_path($relativeDirectory);
        if (!is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
        $safeExtension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'png';
        $fileName = $key.'-'.Str::uuid().'.'.$safeExtension;
        $absolutePath = $absoluteDirectory.'/'.$fileName;

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file->getRealPath());

        if ($shouldResize && $targetWidth > 0 && $targetHeight > 0) {
            if ($image->width() >= $targetWidth && $image->height() >= $targetHeight) {
                $image->cover($targetWidth, $targetHeight);
            } else {
                $image->scaleDown($targetWidth, $targetHeight);
                $canvas = $manager->create($targetWidth, $targetHeight)->fill('ffffff');
                $canvas->place($image, 'center');
                $image = $canvas;
            }
        }

        match ($safeExtension) {
            'jpg', 'jpeg' => $image->toJpeg(100)->save($absolutePath),
            'webp' => $image->toWebp(100)->save($absolutePath),
            default => $image->toPng()->save($absolutePath),
        };

        return $relativeDirectory.'/'.$fileName;
    }

    private function canUserAccessSize($user, string $sizeType): bool
    {
        $size = AdSizes::all()[$sizeType] ?? null;
        if (! is_array($size)) {
            return false;
        }

        if ((bool) ($size['admin_only'] ?? false) === true && ! (bool) ($user?->isStaff())) {
            return false;
        }

        if ((bool) ($size['is_paid'] ?? false) === true && ! (bool) ($user?->isAdmin())) {
            return ! empty($size['category_prices'] ?? []);
        }

        return true;
    }

    private function userHasPaidForSize(string $sizeType): bool
    {
        return in_array($sizeType, Session::get('ads_paid_sizes', []), true);
    }

    private function paidSizeAccessMap($user): array
    {
        return collect(AdSizes::visibleFor($user))
            ->mapWithKeys(function (array $size, string $type) use ($user) {
                $isPaid = (bool) ($size['is_paid'] ?? false);
                $hasAccess = ! $isPaid || (bool) ($user?->isAdmin());

                return [$type => $hasAccess];
            })->all();
    }

    private function resolveSizeType(string $sizeType): string
    {
        if (AdSizes::exists($sizeType)) {
            return $sizeType;
        }

        if (str_starts_with($sizeType, 'admin_')) {
            $legacySizeType = substr($sizeType, strlen('admin_'));
            if (is_string($legacySizeType) && AdSizes::exists($legacySizeType)) {
                return $legacySizeType;
            }
        }

        return $sizeType;
    }
}
