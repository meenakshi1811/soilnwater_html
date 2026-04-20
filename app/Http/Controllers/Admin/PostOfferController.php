<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PostOfferController extends Controller
{
    private const BANNER_TARGET_WIDTH = 768;
    private const BANNER_TARGET_HEIGHT = 1080;

    public function index()
    {
        abort_unless($this->canCreate(request()->user()), 403);

        $categories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('backend.post-offers.index', compact('categories'));
    }

    public function subcategories(Category $category)
    {
        return response()->json(
            $category->children()->orderBy('name')->get(['id', 'name'])
        );
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($this->canCreate($request->user()), 403);

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'discount_tag'      => 'required|string|max:255',
            'coupon_code'       => 'nullable|string|max:50',
            'valid_until'       => 'nullable|date|after_or_equal:today',
            'category_id'       => ['nullable', Rule::exists('categories', 'id')],
            'subcategory_id'    => ['nullable', Rule::exists('categories', 'id')],
            'banner_image'      => 'nullable|required_without:generated_banner_data|image|mimes:jpg,jpeg,png,webp|max:2048',
            'generated_banner_data' => 'nullable|required_without:banner_image|string',
            'short_description' => 'nullable|string|max:300',
            'accept_terms'      => 'accepted',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $this->storeUploadedBanner($request);
        } elseif (!empty($validated['generated_banner_data'])) {
            $validated['banner_image'] = $this->storeGeneratedBanner($validated['generated_banner_data']);
        }

        // Attach the authenticated user
        $validated['user_id'] = auth()->id();
        unset($validated['accept_terms'], $validated['generated_banner_data']);

        Offer::create($validated);

        return response()->json(['message' => 'Offer posted successfully!']);
    }

    public function offersIndex()
    {
        abort_unless($this->canRead(request()->user()), 403);

        $user = request()->user();

        return view('backend.post-offers.my-offers', [
            'canCreateOffer' => $this->canCreate($user),
            'canEditOffer' => $this->canWrite($user),
            'canDeleteOffer' => $this->canDelete($user),
            'canApproveOffer' => $this->canApprove($user),
            'isAdminView' => $user->isAdmin(),
        ]);
    }

    public function offersData(Request $request): JsonResponse
    {
        abort_unless($this->canRead($request->user()), 403);

        $user = $request->user();
        $isStaff = $user->isAdmin() || $user->isEmployee();

        $offers = Offer::query()
            ->with(['user:id,name,full_name', 'category:id,name', 'subcategory:id,name'])
            ->when(! $isStaff, fn ($query) => $query->where('user_id', $user->id))
            ->latest();

        $canEdit = $this->canWrite($user);
        $canDelete = $this->canDelete($user);
        $canApprove = $this->canApprove($user);

        return DataTables::of($offers)
            ->addColumn('banner_preview', function (Offer $offer) {
                if (!$offer->banner_image) {
                    return '-';
                }

                $url = $this->bannerPublicUrl($offer->banner_image);

                return '<a href="'.$url.'" target="_blank" rel="noopener noreferrer">'
                    . '<img src="'.$url.'" alt="Offer banner" style="width:70px;height:44px;object-fit:cover;border-radius:6px;">'
                    . '</a>';
            })
            ->addColumn('created_by_name', fn (Offer $offer) => $offer->user?->full_name ?: ($offer->user?->name ?? '-'))
            ->addColumn('category_name', fn (Offer $offer) => $offer->category?->name ?? '-')
            ->addColumn('subcategory_name', fn (Offer $offer) => $offer->subcategory?->name ?? '-')
            ->addColumn('status_badge', function (Offer $offer) use ($canApprove) {
                $isExpired = $offer->valid_until && $offer->valid_until->lt(Carbon::today());

                if ($isExpired) {
                    return '<span class="badge bg-danger">Expired</span>';
                }

                if (! $canApprove) {
                    $class = $offer->status === 'active' ? 'success' : 'secondary';
                    $label = ucfirst($offer->status);

                    return '<span class="badge bg-'.$class.'">'.$label.'</span>';
                }

                $activeSelected = $offer->status === 'active' ? 'selected' : '';
                $inactiveSelected = $offer->status === 'inactive' ? 'selected' : '';

                return '<select class="form-select form-select-sm js-offer-status" data-id="'.$offer->id.'" style="min-width:110px;">'
                    . '<option value="active" '.$activeSelected.'>Active</option>'
                    . '<option value="inactive" '.$inactiveSelected.'>Inactive</option>'
                    . '</select>';
            })
            ->editColumn('valid_until', fn (Offer $offer) => $offer->valid_until?->format('Y-m-d') ?? '-')
            ->editColumn('created_at', fn (Offer $offer) => $offer->created_at?->format('Y-m-d H:i'))
            ->addColumn('actions', function (Offer $offer) use ($canEdit, $canDelete) {
                $actions = [];

                if ($canEdit) {
                    $actions[] = '<button type="button" class="btn btn-sm btn-outline-primary js-edit-offer" data-id="'.$offer->id.'"><i class="fa-solid fa-pen"></i></button>';
                }

                if ($canDelete) {
                    $actions[] = '<button type="button" class="btn btn-sm btn-outline-danger js-delete-offer" data-id="'.$offer->id.'"><i class="fa-solid fa-trash"></i></button>';
                }

                if ($actions === []) {
                    return '<span class="text-muted">—</span>';
                }

                return '<div class="d-flex justify-content-end gap-2">'.implode('', $actions).'</div>';
            })
            ->rawColumns(['banner_preview', 'status_badge', 'actions'])
            ->make(true);
    }

    public function show(Offer $offer): JsonResponse
    {
        $user = request()->user();
        $isOwner = (int) $offer->user_id === (int) $user->id;
        $canAccess = $this->canRead($user) && ($this->isStaff($user) || $isOwner);

        abort_unless($canAccess, 403);

        return response()->json([
            'offer' => $offer,
            'banner_url' => $offer->banner_image ? $this->bannerPublicUrl($offer->banner_image) : null,
        ]);
    }

    public function update(Request $request, Offer $offer): JsonResponse
    {
        $user = $request->user();
        $isOwner = (int) $offer->user_id === (int) $user->id;
        $canWrite = $this->canWrite($user) && ($this->isStaff($user) || $isOwner);
        $canApprove = $this->canApprove($user) && ($this->isStaff($user) || $isOwner);

        abort_unless($canWrite || $canApprove, 403);

        $rules = [
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
        ];

        if ($canWrite) {
            $rules = array_merge($rules, [
                'title' => 'required|string|max:255',
                'discount_tag' => 'required|string|max:255',
                'coupon_code' => 'nullable|string|max:50',
                'valid_until' => 'nullable|date|after_or_equal:today',
                'short_description' => 'nullable|string|max:300',
                'banner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);
        }

        $validated = $request->validate($rules);

        if (! $canApprove) {
            unset($validated['status']);
        }

        if (! $canWrite) {
            $offer->update([
                'status' => $validated['status'] ?? $offer->status,
            ]);

            return response()->json(['message' => 'Offer status updated successfully.']);
        }

        if ($request->hasFile('banner_image')) {
            if ($offer->banner_image) {
                $this->deleteBannerFile($offer->banner_image);
            }

            $validated['banner_image'] = $this->storeUploadedBanner($request);
        }

        $offer->update($validated);

        return response()->json(['message' => 'Offer updated successfully.']);
    }

    public function updateOfferStatus(Request $request, Offer $offer): JsonResponse
    {
        $user = $request->user();
        $isOwner = (int) $offer->user_id === (int) $user->id;
        $canApprove = $this->canApprove($user) && ($this->isStaff($user) || $isOwner);

        abort_unless($canApprove, 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $offer->update([
            'status' => $validated['status'],
        ]);

        return response()->json(['message' => 'Offer status updated successfully.']);
    }

    public function destroy(Request $request, Offer $offer): JsonResponse
    {
        $user = $request->user();
        $isOwner = (int) $offer->user_id === (int) $user->id;

        abort_unless($this->canDelete($user) && ($this->isStaff($user) || $isOwner), 403);

        if ($offer->banner_image) {
            $this->deleteBannerFile($offer->banner_image);
        }

        $offer->delete();

        return response()->json(['message' => 'Offer deleted successfully.']);
    }

    private function canRead($user): bool
    {
        return $user->isAdmin() || $user->isGeneralUser() || $user->canModule('vendors', 'read');
    }

    private function canCreate($user): bool
    {
        return $user->isAdmin() || $user->isGeneralUser() || $user->canModule('vendors', 'add');
    }

    private function canWrite($user): bool
    {
        return $user->isAdmin() || $user->isGeneralUser() || $user->canModule('vendors', 'write');
    }

    private function canDelete($user): bool
    {
        return $user->isAdmin() || $user->isGeneralUser() || $user->canModule('vendors', 'delete');
    }

    private function canApprove($user): bool
    {
        return $user->isAdmin() || $user->canModule('vendors', 'approve');
    }

    private function isStaff($user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    private function storeGeneratedBanner(string $base64Png): string
    {
        if (!preg_match('/^data:image\/png;base64,/', $base64Png)) {
            abort(422, 'Only PNG template exports are supported.');
        }

        $decoded = base64_decode(substr($base64Png, strpos($base64Png, ',') + 1), true);

        if ($decoded === false) {
            abort(422, 'Invalid generated banner data.');
        }

        $relativePath = 'uploads/offers/banners/custom-'.Str::uuid().'.png';
        $absoluteDirectory = public_path('uploads/offers/banners');

        if (!is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0755, true);
        }

        $absolutePath = public_path($relativePath);
        file_put_contents($absolutePath, $decoded);
        $this->resizeBannerToTargetSize($absolutePath);
        $this->applyCopyrightLogoToBanner($absolutePath);

        return $relativePath;
    }

    private function storeUploadedBanner(Request $request): string
    {
        $file = $request->file('banner_image');
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $fileName = 'banner-'.Str::uuid().'.'.$extension;
        $relativeDirectory = 'uploads/offers/banners';
        $absoluteDirectory = public_path($relativeDirectory);

        if (!is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0755, true);
        }

        $file->move($absoluteDirectory, $fileName);
        $absolutePath = $absoluteDirectory.'/'.$fileName;
        $this->resizeBannerToTargetSize($absolutePath);
        $this->applyCopyrightLogoToBanner($absolutePath);

        return $relativeDirectory.'/'.$fileName;
    }

    private function applyCopyrightLogoToBanner(string $bannerPath): void
    {
        if (!is_file($bannerPath)) {
            return;
        }

        $imageInfo = @getimagesize($bannerPath);
        if (!is_array($imageInfo) || empty($imageInfo[2])) {
            return;
        }

        $createImageByType = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => function (string $path) {
                return @imagecreatefromjpeg($path);
            },
            IMAGETYPE_PNG => function (string $path) {
                return @imagecreatefrompng($path);
            },
            IMAGETYPE_WEBP => function (string $path) {
                if (!function_exists('imagecreatefromwebp')) {
                    return false;
                }

                return @imagecreatefromwebp($path);
            },
            default => null,
        };

        if ($createImageByType === null) {
            return;
        }

        $bannerImage = $createImageByType($bannerPath);
        if (!is_resource($bannerImage) && !is_object($bannerImage)) {
            return;
        }

        $logoPath = public_path('assets/images/logo_soilnwater.webp');
        if (!is_file($logoPath)) {
            imagedestroy($bannerImage);

            return;
        }

        $logoContent = file_get_contents($logoPath);
        $logoImage = $logoContent !== false ? @imagecreatefromstring($logoContent) : false;

        if (!is_resource($logoImage) && !is_object($logoImage)) {
            imagedestroy($bannerImage);

            return;
        }

        imagealphablending($bannerImage, true);
        imagesavealpha($bannerImage, true);

        $bannerWidth = imagesx($bannerImage);
        $bannerHeight = imagesy($bannerImage);
        $logoWidth = imagesx($logoImage);
        $logoHeight = imagesy($logoImage);

        if ($bannerWidth <= 0 || $bannerHeight <= 0 || $logoWidth <= 0 || $logoHeight <= 0) {
            imagedestroy($logoImage);
            imagedestroy($bannerImage);

            return;
        }

        $targetLogoWidth = max(80, (int) round($bannerWidth * 0.18));
        $targetLogoWidth = min($targetLogoWidth, $bannerWidth - 24);
        if ($targetLogoWidth <= 0) {
            $targetLogoWidth = max(1, $bannerWidth);
        }

        $targetLogoHeight = (int) round(($logoHeight / $logoWidth) * $targetLogoWidth);
        if ($targetLogoHeight <= 0) {
            $targetLogoHeight = 1;
        }

        if ($targetLogoHeight > ($bannerHeight - 24)) {
            $targetLogoHeight = max(1, $bannerHeight - 24);
            $targetLogoWidth = (int) round(($logoWidth / $logoHeight) * $targetLogoHeight);
        }

        $padding = max(8, (int) round(min($bannerWidth, $bannerHeight) * 0.02));
        $destX = max(0, $bannerWidth - $targetLogoWidth - $padding);
        $destY = max(0, $bannerHeight - $targetLogoHeight - $padding);

        imagecopyresampled(
            $bannerImage,
            $logoImage,
            $destX,
            $destY,
            0,
            0,
            $targetLogoWidth,
            $targetLogoHeight,
            $logoWidth,
            $logoHeight
        );

        match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagejpeg($bannerImage, $bannerPath, 90),
            IMAGETYPE_PNG => imagepng($bannerImage, $bannerPath, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($bannerImage, $bannerPath, 90) : null,
            default => null,
        };

        imagedestroy($logoImage);
        imagedestroy($bannerImage);
    }

    private function resizeBannerToTargetSize(string $bannerPath): void
    {
        if (!is_file($bannerPath)) {
            return;
        }

        $imageInfo = @getimagesize($bannerPath);
        if (!is_array($imageInfo) || empty($imageInfo[2])) {
            return;
        }

        $createImageByType = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => function (string $path) {
                return @imagecreatefromjpeg($path);
            },
            IMAGETYPE_PNG => function (string $path) {
                return @imagecreatefrompng($path);
            },
            IMAGETYPE_WEBP => function (string $path) {
                if (!function_exists('imagecreatefromwebp')) {
                    return false;
                }

                return @imagecreatefromwebp($path);
            },
            default => null,
        };

        if ($createImageByType === null) {
            return;
        }

        $sourceImage = $createImageByType($bannerPath);
        if (!is_resource($sourceImage) && !is_object($sourceImage)) {
            return;
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($sourceImage);

            return;
        }

        $targetWidth = self::BANNER_TARGET_WIDTH;
        $targetHeight = self::BANNER_TARGET_HEIGHT;
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        if (!is_resource($targetImage) && !is_object($targetImage)) {
            imagedestroy($sourceImage);

            return;
        }

        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
        imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);

        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if ($sourceRatio > $targetRatio) {
            $copyHeight = $sourceHeight;
            $copyWidth = (int) round($sourceHeight * $targetRatio);
            $sourceX = (int) round(($sourceWidth - $copyWidth) / 2);
            $sourceY = 0;
        } else {
            $copyWidth = $sourceWidth;
            $copyHeight = (int) round($sourceWidth / $targetRatio);
            $sourceX = 0;
            $sourceY = (int) round(($sourceHeight - $copyHeight) / 2);
        }

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            $sourceX,
            $sourceY,
            $targetWidth,
            $targetHeight,
            $copyWidth,
            $copyHeight
        );

        match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagejpeg($targetImage, $bannerPath, 90),
            IMAGETYPE_PNG => imagepng($targetImage, $bannerPath, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($targetImage, $bannerPath, 90) : null,
            default => null,
        };

        imagedestroy($sourceImage);
        imagedestroy($targetImage);
    }

    private function deleteBannerFile(string $relativePath): void
    {
        $filePath = public_path($relativePath);

        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    private function bannerPublicUrl(string $relativePath): string
    {
        return asset(ltrim($relativePath, '/'));
    }
}
