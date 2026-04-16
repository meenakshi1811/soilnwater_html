<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PostOfferController extends Controller
{
    public function index()
    {
        abort_unless($this->canCreate(request()->user()), 403);

        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        $offerTemplates = $this->offerTemplates();

        return view('backend.post-offers.index', compact('categories', 'offerTemplates'));
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
            'banner_image'      => 'nullable|required_without:selected_template|image|mimes:jpg,jpeg,png,webp|max:2048',
            'selected_template' => ['nullable', 'required_without:banner_image', Rule::in(array_keys($this->offerTemplates()))],
            'short_description' => 'nullable|string|max:300',
            'accept_terms'      => 'accepted',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')
                ->store('offers/banners', 'public');
        } elseif (!empty($validated['selected_template'])) {
            $validated['banner_image'] = $this->generateOfferBannerFromTemplate(
                $validated['selected_template'],
                $validated['title'],
                $validated['discount_tag'],
                $validated['coupon_code'] ?? null
            );
        }

        // Attach the authenticated user
        $validated['user_id'] = auth()->id();
        unset($validated['accept_terms'], $validated['selected_template']);

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

                $url = asset('storage/'.$offer->banner_image);

                return '<img src="'.$url.'" alt="Offer banner" style="width:70px;height:44px;object-fit:cover;border-radius:6px;">';
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

        return response()->json(['offer' => $offer]);
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
                Storage::disk('public')->delete($offer->banner_image);
            }

            $validated['banner_image'] = $request->file('banner_image')->store('offers/banners', 'public');
        }

        $offer->update($validated);

        return response()->json(['message' => 'Offer updated successfully.']);
    }

    public function destroy(Request $request, Offer $offer): JsonResponse
    {
        $user = $request->user();
        $isOwner = (int) $offer->user_id === (int) $user->id;

        abort_unless($this->canDelete($user) && ($this->isStaff($user) || $isOwner), 403);

        if ($offer->banner_image) {
            Storage::disk('public')->delete($offer->banner_image);
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
        return $user->isAdmin() || $user->isGeneralUser() || $user->canModule('vendors', 'approve');
    }

    private function isStaff($user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    private function offerTemplates(): array
    {
        return [
            'summer' => [
                'name' => 'Summer Blast',
                'colors' => [[255, 140, 66], [255, 94, 98]],
            ],
            'forest' => [
                'name' => 'Green Saver',
                'colors' => [[67, 160, 71], [21, 101, 192]],
            ],
            'night' => [
                'name' => 'Night Deal',
                'colors' => [[142, 68, 173], [44, 62, 80]],
            ],
        ];
    }

    private function generateOfferBannerFromTemplate(string $templateKey, string $title, string $discountTag, ?string $couponCode): string
    {
        $templates = $this->offerTemplates();
        $template = $templates[$templateKey] ?? null;
        abort_unless($template !== null, 422);

        $image = imagecreatetruecolor(1200, 400);
        abort_unless($image !== false, 422, 'Could not create selected offer template.');

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $start = $template['colors'][0];
        $end = $template['colors'][1];
        $width = imagesx($image);
        $height = imagesy($image);

        for ($x = 0; $x < $width; $x++) {
            $r = (int) ($start[0] + (($end[0] - $start[0]) * ($x / max(1, $width - 1))));
            $g = (int) ($start[1] + (($end[1] - $start[1]) * ($x / max(1, $width - 1))));
            $b = (int) ($start[2] + (($end[2] - $start[2]) * ($x / max(1, $width - 1))));
            $lineColor = imagecolorallocate($image, $r, $g, $b);
            imageline($image, $x, 0, $x, $height, $lineColor);
        }

        $shape = imagecolorallocatealpha($image, 255, 255, 255, 95);
        imagefilledellipse($image, 190, 85, 250, 250, $shape);
        imagefilledellipse($image, 1000, 320, 390, 390, $shape);

        $white = imagecolorallocate($image, 255, 255, 255);
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, 65);

        $fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $hasTrueType = file_exists($fontPath);

        if ($hasTrueType) {
            $this->drawTtfText($image, $title, 42, 56, 120, $white, $shadow, $fontPath);
            $this->drawTtfText($image, $discountTag, 34, 56, 198, $white, $shadow, $fontPath);
            $couponText = 'Coupon: '.strtoupper($couponCode ?: 'N/A');
            $this->drawTtfText($image, $couponText, 28, 56, 262, $white, $shadow, $fontPath);
        } else {
            imagestring($image, 5, 56, 96, Str::limit($title, 35), $white);
            imagestring($image, 5, 56, 140, Str::limit($discountTag, 30), $white);
            imagestring($image, 5, 56, 182, 'Coupon: '.strtoupper($couponCode ?: 'N/A'), $white);
        }

        $relativePath = 'offers/banners/template-'.Str::uuid().'.png';
        $targetPath = storage_path('app/public/'.$relativePath);
        $directory = dirname($targetPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        imagepng($image, $targetPath, 9);
        imagedestroy($image);

        return $relativePath;
    }

    private function drawTtfText($image, string $text, int $size, int $x, int $y, int $color, int $shadow, string $fontPath): void
    {
        imagettftext($image, $size, 0, $x + 2, $y + 2, $shadow, $fontPath, Str::limit($text, 42));
        imagettftext($image, $size, 0, $x, $y, $color, $fontPath, Str::limit($text, 42));
    }
}
