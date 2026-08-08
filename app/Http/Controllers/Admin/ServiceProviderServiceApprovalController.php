<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ValidatesServiceProviderServiceRequest;
use App\Http\Controllers\Controller;
use App\Services\PortalNotificationService;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ServiceProviderServiceApprovalController extends Controller
{
    use ValidatesServiceProviderServiceRequest;

    public function create(): View
    {
        return view('backend.service_provider.services.form', [
            'service' => new ServiceProviderService(),
            'categories' => $this->serviceProviderCategories(),
            'isAdmin' => true,
            'serviceProviders' => $this->approvedServiceProviders(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_provider_id' => ['required', 'exists:service_providers,id'],
        ]);

        $data = $this->validatedServiceProviderService($request);
        $data['service_provider_id'] = (int) $request->input('service_provider_id');
        $data['slug'] = $this->uniqueServiceProviderServiceSlug($data['service_provider_id'], $data['name']);
        $data['status'] = 'approved';
        $data['approved_at'] = now();
        $data['approved_by'] = $request->user()->id;

        $service = ServiceProviderService::create($data);

        $service->loadMissing('service_provider.user');
        PortalNotificationService::notifyUser(
            $service->service_provider?->user,
            'Service added by admin',
            $service->name.' has been added and published on your page by admin.',
            route('service_provider.services.show', $service),
            'reviewed'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Service created and published (no approval needed).',
                'redirect' => route('admin.service-provider-services.all.index'),
            ]);
        }

        return redirect()->route('admin.service-provider-services.all.index')
            ->with('success', 'Service created and published (no approval needed).');
    }

    public function index(): View
    {
        return view('backend.admin.service-provider-services.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = ServiceProviderService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name', 'service_provider:id,company_name,display_name'])
            ->where(fn ($pending) => $pending->where('status', 'pending')->orWhereNull('status'))
            ->select(['id', 'service_provider_id', 'name', 'category', 'category_id', 'subcategory_id', 'price', 'consultation_charges', 'consultation_charge_notes', 'duration', 'status', 'created_at', 'updated_at'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at');

        return $this->datatable($query, true);
    }

    public function allServicesIndex(): View
    {
        return view('backend.admin.service-provider-services.all-services');
    }

    public function allServicesData(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = ServiceProviderService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name', 'service_provider:id,company_name,display_name'])
            ->select(['id', 'service_provider_id', 'name', 'category', 'category_id', 'subcategory_id', 'price', 'consultation_charges', 'consultation_charge_notes', 'duration', 'status', 'created_at', 'updated_at'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at');

        if (in_array($request->string('status')->toString(), ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $request->string('status')->toString());
        }

        return $this->datatable($query, false);
    }

    public function show(ServiceProviderService $service): View
    {
        $service->load(['categoryModel:id,name', 'subcategoryModel:id,name', 'service_provider:id,company_name,display_name']);

        return view('backend.admin.service-provider-services.show', compact('service'));
    }

    public function approve(ServiceProviderService $service, Request $request): JsonResponse
    {
        $service->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $request->user()->id]);
        $service->loadMissing('service_provider.user');

        PortalNotificationService::notifyOwnerOfReview($service->service_provider?->user, 'Service', $service->name, 'approved', route('service_provider.services.show', $service));

        return response()->json(['message' => 'Service approved.']);
    }

    public function reject(ServiceProviderService $service): JsonResponse
    {
        $service->update(['status' => 'rejected', 'approved_at' => null, 'approved_by' => null]);
        $service->loadMissing('service_provider.user');

        PortalNotificationService::notifyOwnerOfReview($service->service_provider?->user, 'Service', $service->name, 'rejected', route('service_provider.services.show', $service));

        return response()->json(['message' => 'Service rejected.']);
    }

    public function destroy(ServiceProviderService $service): JsonResponse
    {
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully.']);
    }

    private function datatable($query, bool $withApprovalActions): JsonResponse
    {
        return DataTables::of($query)
            ->addColumn('service_provider_name', fn (ServiceProviderService $service): string => e($service->service_provider?->display_name ?: $service->service_provider?->company_name ?: '—'))
            ->addColumn('category_display', function (ServiceProviderService $service): string {
                $category = $this->decodeHtmlEntities($service->categoryModel?->name ?? (is_string($service->category) ? $service->category : '-'));
                $subcategory = $this->decodeHtmlEntities($service->subcategoryModel?->name ?? '-');

                return $category.' / '.$subcategory;
            })
            ->addColumn('price_display', function (ServiceProviderService $service): string {
                return collect($service->consultationChargeRows())
                    ->map(function (array $row): string {
                        return '<div>'.e($row['duration'].': '.$row['price']).'</div>';
                    })
                    ->implode('');
            })
            ->addColumn('status_badge', function (ServiceProviderService $service): string {
                $status = $service->status ?? 'pending';
                $badge = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');

                return '<span class="badge bg-'.$badge.'">'.ucfirst($status).'</span>';
            })
            ->addColumn('actions', function (ServiceProviderService $service) use ($withApprovalActions): string {
                $status = $service->status ?? 'pending';
                $approve = $withApprovalActions && $status !== 'approved' ? '<button type="button" class="btn btn-sm btn-success js-approve" data-id="'.$service->id.'">Approve</button>' : '';
                $reject = $withApprovalActions && $status !== 'rejected' ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject" data-id="'.$service->id.'">Reject</button>' : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.service-provider-services.show', $service).'" class="btn btn-sm btn-outline-secondary">View</a>'
                    .$approve
                    .$reject
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete" data-id="'.$service->id.'">Delete</button>'
                    .'</div>';
            })
            ->editColumn('updated_at', function (ServiceProviderService $service): string {
                return optional($service->updated_at)
                    ? $service->updated_at->timezone(config('app.timezone'))->format('d M Y, h:i A')
                    : '-';
            })
            ->rawColumns(['price_display', 'status_badge', 'actions'])
            ->make(true);
    }

    private function decodeHtmlEntities(?string $value): string
    {
        $decoded = (string) $value;

        for ($i = 0; $i < 3; $i++) {
            $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        return $decoded;
    }

    private function approvedServiceProviders()
    {
        return ServiceProvider::query()
            ->where('status', 'approved')
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'display_name']);
    }
}
