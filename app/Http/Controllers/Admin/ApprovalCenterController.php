<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderService;
use App\Models\UserAd;
use App\Models\Vendor;
use App\Models\VendorProduct;
use App\Services\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ApprovalCenterController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('module')->toString();
        $approvals = $this->approvalItems();
        $moduleCounts = $approvals->groupBy('module_key')->map->count();

        if ($filter !== '' && $filter !== 'all') {
            $approvals = $approvals->where('module_key', $filter)->values();
        }

        $approvals = $approvals
            ->sortByDesc(fn (array $item) => $item['requested_at']?->timestamp ?? 0)
            ->values();

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginated = new LengthAwarePaginator(
            $approvals->forPage($page, $perPage)->values(),
            $approvals->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('backend.admin.approvals.index', [
            'approvals' => $paginated,
            'moduleCounts' => $moduleCounts,
            'activeModule' => $filter ?: 'all',
            'totalPendingApprovals' => $this->approvalItems()->count(),
            'moduleFilters' => $this->moduleFilters(),
        ]);
    }

    public function approve(Request $request, string $type, int $id): JsonResponse
    {
        return $this->review($request, $type, $id, true);
    }

    public function decline(Request $request, string $type, int $id): JsonResponse
    {
        return $this->review($request, $type, $id, false);
    }

    private function approvalItems(): Collection
    {
        return collect()
            ->merge($this->pendingAds())
            ->merge($this->pendingOffers())
            ->merge($this->pendingVendorProducts())
            ->merge($this->pendingConsultantServices())
            ->merge($this->pendingServiceProviderServices())
            ->merge($this->pendingVendorPublicPages())
            ->merge($this->pendingConsultantPublicPages())
            ->merge($this->pendingServiceProviderPublicPages())
            ->merge($this->pendingCommunityPosts());
    }

    private function pendingAds(): Collection
    {
        return UserAd::query()
            ->with('user:id,name,full_name')
            ->where('status', 'pending')
            ->get()
            ->map(fn (UserAd $ad): array => $this->makeItem(
                'ad',
                'ads',
                'Ad',
                'fa-rectangle-ad',
                $ad->id,
                $ad->title,
                $ad->user?->full_name ?: ($ad->user?->name ?? 'Unknown user'),
                'Ad submission',
                $ad->submitted_at ?: $ad->created_at,
                route('admin.ads.submissions.show', $ad)
            ));
    }

    private function pendingOffers(): Collection
    {
        return Offer::query()
            ->with('user:id,name,full_name')
            ->where('status', 'inactive')
            ->where('approval_status', 'pending')
            ->get()
            ->map(fn (Offer $offer): array => $this->makeItem(
                'offer',
                'offers',
                'Offer',
                'fa-tags',
                $offer->id,
                $offer->title,
                $offer->user?->full_name ?: ($offer->user?->name ?? 'Unknown user'),
                'Offer awaiting activation',
                $offer->updated_at ?: $offer->created_at,
                route('offers.edit', $offer)
            ));
    }

    private function pendingVendorProducts(): Collection
    {
        return VendorProduct::query()
            ->with('vendor:id,company_name,display_name')
            ->where(fn ($query) => $query->where('status', 'pending')->orWhereNull('status'))
            ->get()
            ->map(fn (VendorProduct $product): array => $this->makeItem(
                'vendor_product',
                'vendor-products',
                'Vendor Product',
                'fa-boxes-stacked',
                $product->id,
                $product->name,
                $product->vendor?->display_name ?: ($product->vendor?->company_name ?? 'Unknown vendor'),
                'Product from vendor',
                $product->updated_at ?: $product->created_at,
                route('admin.vendor-products.show', $product)
            ));
    }

    private function pendingConsultantServices(): Collection
    {
        return ConsultantService::query()
            ->with('consultant:id,company_name,display_name')
            ->where(fn ($query) => $query->where('status', 'pending')->orWhereNull('status'))
            ->get()
            ->map(fn (ConsultantService $service): array => $this->makeItem(
                'consultant_service',
                'consultant-services',
                'Consultant Service',
                'fa-user-tie',
                $service->id,
                $service->name,
                $service->consultant?->display_name ?: ($service->consultant?->company_name ?? 'Unknown consultant'),
                'Service from consultant',
                $service->updated_at ?: $service->created_at,
                route('admin.consultant-services.show', $service)
            ));
    }

    private function pendingServiceProviderServices(): Collection
    {
        return ServiceProviderService::query()
            ->with('service_provider:id,company_name,display_name')
            ->where(fn ($query) => $query->where('status', 'pending')->orWhereNull('status'))
            ->get()
            ->map(fn (ServiceProviderService $service): array => $this->makeItem(
                'service_provider_service',
                'service-provider-services',
                'Service Provider Service',
                'fa-screwdriver-wrench',
                $service->id,
                $service->name,
                $service->service_provider?->display_name ?: ($service->service_provider?->company_name ?? 'Unknown service provider'),
                'Service from service provider',
                $service->updated_at ?: $service->created_at,
                route('admin.service-provider-services.show', $service)
            ));
    }

    private function pendingVendorPublicPages(): Collection
    {
        return Vendor::query()
            ->where('public_page_status', 'pending')
            ->whereNotNull('pending_page_data')
            ->get()
            ->map(fn (Vendor $vendor): array => $this->makeItem(
                'vendor_public_page',
                'public-pages',
                'Vendor Public Page',
                'fa-store',
                $vendor->id,
                $vendor->display_name ?: $vendor->company_name,
                $vendor->company_name,
                'Public page request from vendor',
                $vendor->public_page_submitted_at ?: $vendor->updated_at,
                route('admin.vendors.public-page.review', $vendor)
            ));
    }

    private function pendingConsultantPublicPages(): Collection
    {
        return Consultant::query()
            ->where('public_page_status', 'pending')
            ->whereNotNull('pending_page_data')
            ->get()
            ->map(fn (Consultant $consultant): array => $this->makeItem(
                'consultant_public_page',
                'public-pages',
                'Consultant Public Page',
                'fa-user-tie',
                $consultant->id,
                $consultant->display_name ?: $consultant->company_name,
                $consultant->company_name,
                'Public page request from consultant',
                $consultant->public_page_submitted_at ?: $consultant->updated_at,
                route('admin.consultants.public-page.review', $consultant)
            ));
    }

    private function pendingCommunityPosts(): Collection
    {
        return CommunityPost::query()
            ->with('user:id,name,full_name')
            ->pendingApproval()
            ->get()
            ->map(fn (CommunityPost $post): array => $this->makeItem(
                'community_post',
                'community-posts',
                'Community Post',
                'fa-pen-nib',
                $post->id,
                $post->title,
                $post->user?->full_name ?: ($post->user?->name ?? 'Unknown user'),
                $post->typeLabel().' awaiting publish approval',
                $post->submitted_at ?: $post->created_at,
                route('admin.community-posts.show', $post)
            ));
    }

    private function pendingServiceProviderPublicPages(): Collection
    {
        return ServiceProvider::query()
            ->where('public_page_status', 'pending')
            ->whereNotNull('pending_page_data')
            ->get()
            ->map(fn (ServiceProvider $serviceProvider): array => $this->makeItem(
                'service_provider_public_page',
                'public-pages',
                'Service Public Page',
                'fa-screwdriver-wrench',
                $serviceProvider->id,
                $serviceProvider->display_name ?: $serviceProvider->company_name,
                $serviceProvider->company_name,
                'Public page request from service provider',
                $serviceProvider->public_page_submitted_at ?: $serviceProvider->updated_at,
                route('admin.service_providers.public-page.review', $serviceProvider)
            ));
    }

    private function makeItem(string $type, string $moduleKey, string $moduleLabel, string $icon, int $id, string $title, string $owner, string $description, mixed $requestedAt, string $viewUrl): array
    {
        return [
            'type' => $type,
            'module_key' => $moduleKey,
            'module_label' => $moduleLabel,
            'icon' => $icon,
            'id' => $id,
            'title' => $title,
            'owner' => $owner,
            'description' => $description,
            'requested_at' => $requestedAt,
            'view_url' => $viewUrl,
            'approve_url' => route('admin.approvals.approve', [$type, $id]),
            'decline_url' => route('admin.approvals.decline', [$type, $id]),
        ];
    }

    private function moduleFilters(): array
    {
        return [
            'all' => 'All approvals',
            'ads' => 'Ads',
            'offers' => 'Offers',
            'vendor-products' => 'Vendor products',
            'consultant-services' => 'Consultant services',
            'service-provider-services' => 'Service services',
            'public-pages' => 'Public pages',
            'community-posts' => 'Community posts',
        ];
    }

    private function review(Request $request, string $type, int $id, bool $approved): JsonResponse
    {
        abort_unless(array_key_exists($type, $this->reviewableTypes()), 404);

        return match ($type) {
            'ad' => $approved
                ? app(AdSubmissionController::class)->approve($request, UserAd::findOrFail($id))
                : app(AdSubmissionController::class)->reject($this->withDefaultReviewNote($request), UserAd::findOrFail($id)),
            'offer' => $this->reviewOffer(Offer::findOrFail($id), $approved),
            'vendor_product' => $approved
                ? app(VendorProductApprovalController::class)->approve(VendorProduct::findOrFail($id), $request)
                : app(VendorProductApprovalController::class)->reject(VendorProduct::findOrFail($id)),
            'consultant_service' => $approved
                ? app(ConsultantServiceApprovalController::class)->approve(ConsultantService::findOrFail($id), $request)
                : app(ConsultantServiceApprovalController::class)->reject(ConsultantService::findOrFail($id)),
            'service_provider_service' => $approved
                ? app(ServiceProviderServiceApprovalController::class)->approve(ServiceProviderService::findOrFail($id), $request)
                : app(ServiceProviderServiceApprovalController::class)->reject(ServiceProviderService::findOrFail($id)),
            'vendor_public_page' => $approved
                ? app(VendorController::class)->approvePublicPage($request, Vendor::findOrFail($id))
                : app(VendorController::class)->declinePublicPage(Vendor::findOrFail($id)),
            'consultant_public_page' => $approved
                ? app(ConsultantController::class)->approvePublicPage($request, Consultant::findOrFail($id))
                : app(ConsultantController::class)->declinePublicPage(Consultant::findOrFail($id)),
            'service_provider_public_page' => $approved
                ? app(ServiceProviderController::class)->approvePublicPage($request, ServiceProvider::findOrFail($id))
                : app(ServiceProviderController::class)->declinePublicPage(ServiceProvider::findOrFail($id)),
            'community_post' => $approved
                ? app(CommunityPostApprovalController::class)->approve($request, CommunityPost::findOrFail($id))
                : app(CommunityPostApprovalController::class)->decline($this->withDefaultReviewNote($request), CommunityPost::findOrFail($id)),
        };
    }


    private function withDefaultReviewNote(Request $request): Request
    {
        if (! $request->filled('review_note')) {
            $request->merge(['review_note' => 'Declined from Approval Center.']);
        }

        return $request;
    }

    private function reviewOffer(Offer $offer, bool $approved): JsonResponse
    {
        $offer->update([
            'status' => $approved ? 'active' : 'inactive',
            'approval_status' => $approved ? 'approved' : 'declined',
            'approval_reviewed_at' => now(),
            'approval_reviewed_by' => request()->user()?->id,
        ]);

        PortalNotificationService::notifyOwnerOfReview(
            $offer->user,
            'Offer',
            $offer->title,
            $approved ? 'approved' : 'declined',
            route('offers.index')
        );

        return response()->json(['message' => 'Offer '.($approved ? 'approved' : 'declined').' successfully.']);
    }

    private function reviewableTypes(): array
    {
        return array_fill_keys([
            'ad',
            'offer',
            'vendor_product',
            'consultant_service',
            'service_provider_service',
            'vendor_public_page',
            'consultant_public_page',
            'service_provider_public_page',
            'community_post',
        ], true);
    }
}
