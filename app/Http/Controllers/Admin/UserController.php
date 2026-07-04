<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('backend.users.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $users = User::query()
            ->select([
                'id',
                'name',
                'email',
                'phone_number',
                'whatsapp_number',
                'address',
                'city',
                'pincode',
                'role',
                'profile_image',
                'date_of_birth',
                'email_verified_at',
                'phone_verified_at',
                'is_active',
                'is_blocked',
                'created_at',
            ]);

        return DataTables::of($users)
            ->addColumn('name_display', function (User $user): string {
                $avatar = $user->profile_image
                    ? '<img src="'.e($this->assetUrl($user->profile_image)).'" alt="'.e($user->name).'" class="admin-user-avatar">'
                    : '<span class="admin-user-avatar admin-user-avatar-placeholder">'.e(Str::upper(Str::substr((string) $user->name, 0, 1)) ?: 'U').'</span>';

                return '<div class="d-flex align-items-center gap-2">'
                    . $avatar
                    . '<div class="min-w-0"><div class="fw-semibold text-dark">'.e($user->name ?: '—').'</div><small class="text-muted">ID #'.e((string) $user->id).'</small></div>'
                    . '</div>';
            })
            ->addColumn('role_badge', function (User $user): string {
                $labels = [
                    'user' => ['General User', 'text-bg-primary'],
                    'vendor' => ['Vendor', 'text-bg-info'],
                    'consultant' => ['Consultant', 'text-bg-purple'],
                    'service_provider' => ['Service Provider', 'text-bg-teal'],
                    'admin' => ['Admin', 'text-bg-danger'],
                    'employee' => ['Employee', 'text-bg-dark'],
                    'builder' => ['Builder', 'text-bg-secondary'],
                    'developer' => ['Developer', 'text-bg-secondary'],
                ];
                [$label, $class] = $labels[$user->role] ?? [Str::headline((string) $user->role), 'text-bg-secondary'];

                return '<span class="badge '.$class.'">'.e($label).'</span>';
            })
            ->addColumn('email_display', function (User $user): string {
                $verificationBadge = $user->email_verified_at
                    ? '<span class="badge text-bg-success mt-1">Verified</span>'
                    : '<span class="badge text-bg-warning mt-1">Unverified</span>';

                return '<div class="d-flex flex-column">'
                    . '<span>'.e($user->email).'</span>'
                    . $verificationBadge
                    . '</div>';
            })
            ->addColumn('phone_display', function (User $user): string {
                $verificationBadge = $user->phone_verified_at
                    ? '<span class="badge text-bg-success mt-1">Verified</span>'
                    : '<span class="badge text-bg-warning mt-1">Unverified</span>';

                return '<div class="d-flex flex-column">'
                    . '<span>'.e((string) $user->phone_number).'</span>'
                    . $verificationBadge
                    . '</div>';
            })
            ->addColumn('location', function (User $user): string {
                $parts = array_filter([$user->city, $user->pincode]);

                return e($parts ? implode(' - ', $parts) : '—');
            })
            ->editColumn('created_at', function (User $user) {
                return $user->created_at ? $user->created_at->format('Y-m-d') : '';
            })
            ->editColumn('date_of_birth', function (User $user) {
                return $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '—';
            })
            ->addColumn('status_badge', function (User $user): string {
                if ($user->is_blocked) {
                    return '<span class="badge text-bg-danger"><i class="fa-solid fa-ban me-1"></i>Blocked</span>';
                }

                return $user->is_active
                    ? '<span class="badge text-bg-success">Active</span>'
                    : '<span class="badge text-bg-secondary">Inactive</span>';
            })
            ->addColumn('status_toggle', function (User $user): string {
                if ($user->isAdmin()) {
                    return '<span class="text-muted small">—</span>';
                }

                $checked = $user->is_active ? 'checked' : '';
                $title = $user->is_active ? 'Deactivate user' : 'Activate user';

                return '<div class="form-check form-switch m-0 d-flex justify-content-center align-items-center" title="'.$title.'">'
                    . '<input class="form-check-input js-toggle-status" type="checkbox" role="switch" data-id="'.$user->id.'" '.$checked.'>'
                    . '</div>';
            })
            ->addColumn('actions', function (User $user): string {
                return '<div class="d-flex gap-2 justify-content-end align-items-center">'
                    . '<button type="button" class="btn btn-sm btn-outline-secondary js-view-user" data-id="'.$user->id.'" title="View details"><i class="fa-solid fa-eye"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-user" data-id="'.$user->id.'" title="Edit user"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-user" data-id="'.$user->id.'" title="Delete user"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->filterColumn('status_badge', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if ($k === '' || $k === '^') {
                    return;
                }
                if (str_contains($k, 'block')) {
                    $query->where('is_blocked', true);

                    return;
                }
                if (str_contains($k, 'inactive')) {
                    $query->where('is_active', false)->where('is_blocked', false);

                    return;
                }
                if (str_contains($k, 'active')) {
                    $query->where('is_active', true)->where('is_blocked', false);
                }
            })
            ->filterColumn('role_badge', function ($query, $keyword): void {
                $k = str_replace(' ', '_', strtolower((string) $keyword));
                if ($k !== '') {
                    $query->where('role', 'like', '%'.$k.'%');
                }
            })
            ->rawColumns(['name_display', 'role_badge', 'email_display', 'phone_display', 'status_badge', 'status_toggle', 'actions'])
            ->make(true);
    }

    public function show(User $user): JsonResponse
    {
        $this->loadRoleDetails($user);

        return response()->json([
            'user' => $this->serializeUser($user),
            'role_details' => $this->serializeRoleDetails($user),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['required', 'digits_between:10,15'],
            'whatsapp_number' => ['required', 'digits_between:10,15'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'digits_between:4,10'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'full_name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone_number' => $validated['phone_number'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'pincode' => $validated['pincode'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);
        $user->save();

        return response()->json([
            'message' => 'User updated successfully.',
        ]);
    }

    public function toggleBlock(Request $request, User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Admin accounts cannot be blocked.',
            ], 422);
        }

        if ($request->user() && $request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot block your own account.',
            ], 422);
        }

        $user->is_blocked = ! $user->is_blocked;
        $user->save();

        return response()->json([
            'message' => $user->is_blocked
                ? 'User has been blocked and can no longer sign in.'
                : 'User has been unblocked and can sign in again.',
            'is_blocked' => $user->is_blocked,
        ]);
    }

    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Admin accounts cannot be deactivated.',
            ], 422);
        }

        if ($request->user() && $request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot deactivate your own account.',
            ], 422);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return response()->json([
            'message' => $user->is_active
                ? 'User has been activated.'
                : 'User has been deactivated.',
            'is_active' => $user->is_active,
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    private function loadRoleDetails(User $user): void
    {
        match ($user->role) {
            'vendor' => $user->loadMissing([
                'vendor.branches',
                'vendor.bannerSlides',
                'vendor.pageSections',
                'vendor.products.category',
                'vendor.products.subcategory',
                'vendor.products.childCategory',
            ]),
            'consultant' => $user->loadMissing([
                'consultant.branches',
                'consultant.bannerSlides',
                'consultant.pageSections',
                'consultant.services.categoryModel',
                'consultant.services.subcategoryModel',
            ]),
            'service_provider' => $user->loadMissing([
                'serviceProvider.branches',
                'serviceProvider.bannerSlides',
                'serviceProvider.pageSections',
                'serviceProvider.services.categoryModel',
                'serviceProvider.services.subcategoryModel',
            ]),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'whatsapp_number' => $user->whatsapp_number,
            'address' => $user->address,
            'city' => $user->city,
            'pincode' => $user->pincode,
            'role' => $user->role,
            'role_label' => Str::headline(str_replace('_', ' ', (string) $user->role)),
            'profile_image' => $user->profile_image,
            'profile_image_url' => $this->assetUrl($user->profile_image),
            'date_of_birth' => optional($user->date_of_birth)->format('Y-m-d'),
            'email_verified_at' => optional($user->email_verified_at)->format('Y-m-d H:i'),
            'phone_verified_at' => optional($user->phone_verified_at)->format('Y-m-d H:i'),
            'is_active' => (bool) $user->is_active,
            'is_blocked' => (bool) $user->is_blocked,
            'created_at' => optional($user->created_at)->format('Y-m-d H:i'),
            'updated_at' => optional($user->updated_at)->format('Y-m-d H:i'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeRoleDetails(User $user): ?array
    {
        return match ($user->role) {
            'vendor' => $this->serializeMarketplaceProfile($user->vendor, 'Vendor', 'products'),
            'consultant' => $this->serializeMarketplaceProfile($user->consultant, 'Consultant', 'services'),
            'service_provider' => $this->serializeMarketplaceProfile($user->serviceProvider, 'Service Provider', 'services'),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeMarketplaceProfile(Vendor|Consultant|ServiceProvider|null $profile, string $label, string $itemsKey): ?array
    {
        if (! $profile) {
            return null;
        }

        $items = $itemsKey === 'products'
            ? $profile->products->map(fn ($product): array => [
                'name' => $product->name,
                'brand' => $product->brand,
                'sku' => $product->sku,
                'category' => $this->loadedRelationName($product, 'category') ?: $product->getAttribute('category'),
                'subcategory' => $this->loadedRelationName($product, 'subcategory'),
                'child_category' => $this->loadedRelationName($product, 'childCategory'),
                'base_price' => $product->base_price,
                'discount_percent' => $product->discount_percent,
                'final_price' => $product->final_price,
                'stock_quantity' => $product->stock_quantity,
                'location' => $product->location,
                'status' => $product->status,
                'description' => $product->description,
                'images' => $this->imageList($product->images),
                'video_file_url' => $this->assetUrl($product->video_file),
                'youtube_link' => $product->youtube_link,
                'updated_at' => optional($product->updated_at)->format('Y-m-d H:i'),
            ])->values()->all()
            : $profile->services->map(fn ($service): array => [
                'name' => $service->name,
                'category' => $service->categoryModel?->name ?: $service->category,
                'subcategory' => $service->subcategoryModel?->name,
                'consultation_type' => $service->consultation_type,
                'business_type' => $service->business_type,
                'service_area' => $service->service_area,
                'price' => $service->price,
                'charges' => method_exists($service, 'formattedConsultationCharges') ? $service->formattedConsultationCharges() : null,
                'duration' => $service->duration,
                'location' => $service->location,
                'city' => $service->city,
                'postal_code' => $service->postal_code,
                'service_radius' => $service->service_radius,
                'working_hours' => $service->working_hours,
                'is_online' => (bool) $service->is_online,
                'status' => $service->status,
                'short_description' => $service->short_description,
                'description' => $service->description,
                'image_url' => $this->assetUrl($service->image_path),
                'updated_at' => optional($service->updated_at)->format('Y-m-d H:i'),
            ])->values()->all();

        return [
            'label' => $label,
            'profile' => [
                'id' => $profile->id,
                'company_name' => $profile->company_name,
                'display_name' => $profile->display_name,
                'contact_person' => $profile->contact_person,
                'slug' => $profile->slug,
                'logo_url' => $this->assetUrl($profile->logo),
                'phone' => $profile->phone,
                'whatsapp' => $profile->whatsapp,
                'email' => $profile->email,
                'address' => $profile->address,
                'city' => $profile->city,
                'state' => $profile->state,
                'pincode' => $profile->pincode,
                'pan_number' => $profile->pan_number,
                'gst_number' => $profile->gst_number,
                'government_certificate_number' => $profile->government_certificate_number,
                'description' => $profile->description,
                'facebook_url' => $profile->facebook_url,
                'instagram_url' => $profile->instagram_url,
                'is_premium' => (bool) $profile->is_premium,
                'status' => $profile->status,
                'public_page_status' => $profile->public_page_status,
                'approved_at' => optional($profile->approved_at)->format('Y-m-d H:i'),
                'created_at' => optional($profile->created_at)->format('Y-m-d H:i'),
                'updated_at' => optional($profile->updated_at)->format('Y-m-d H:i'),
            ],
            'gallery' => $this->imageList($profile->gallery),
            'branches' => $profile->branches->map(fn ($branch): array => [
                'branch_name' => $branch->branch_name,
                'contact_person' => $branch->contact_person,
                'occupation' => $branch->occupation ?? null,
                'professional_experience' => $branch->professional_experience ?? null,
                'services_offered' => $branch->services_offered ?? null,
                'logo_url' => $this->assetUrl($branch->logo ?? null),
                'phone' => $branch->phone,
                'alt_mobile_number' => $branch->alt_mobile_number,
                'whatsapp' => $branch->whatsapp,
                'email' => $branch->email,
                'address' => $branch->address,
                'city' => $branch->city,
                'state' => $branch->state,
                'pincode' => $branch->pincode,
                'pan_number' => $branch->pan_number,
                'gst_number' => $branch->gst_number,
                'is_primary' => (bool) $branch->is_primary,
            ])->values()->all(),
            'banner_slides' => $profile->bannerSlides->map(fn ($slide): array => [
                'image_url' => $this->assetUrl($slide->image_path),
                'sort_order' => $slide->sort_order,
            ])->values()->all(),
            'page_sections' => $profile->pageSections->map(fn ($section): array => [
                'title' => $section->title,
                'content' => $section->content,
                'image_url' => $this->assetUrl($section->image_path),
                'sort_order' => $section->sort_order,
            ])->values()->all(),
            $itemsKey => $items,
        ];
    }

    private function loadedRelationName($model, string $relation): ?string
    {
        return $model->relationLoaded($relation) ? $model->getRelation($relation)?->name : null;
    }

    private function assetUrl(?string $path): ?string
    {
        return $path ? asset($path) : null;
    }

    /**
     * @return array<int, array{path:string, url:string}>
     */
    private function imageList(mixed $paths): array
    {
        if (blank($paths)) {
            return [];
        }

        return collect(is_array($paths) ? $paths : [$paths])
            ->filter(fn ($path): bool => filled($path))
            ->map(fn ($path): array => [
                'path' => (string) $path,
                'url' => $this->assetUrl((string) $path),
            ])
            ->values()
            ->all();
    }
}
