<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Concerns\AppliesDefaultListingLocation;
use App\Http\Controllers\Concerns\ValidatesServiceProviderServiceRequest;
use App\Http\Controllers\Controller;
use App\Services\PortalNotificationService;
use App\Models\ServiceProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceProviderServiceController extends Controller
{
    use AppliesDefaultListingLocation;
    use ValidatesServiceProviderServiceRequest;

    public function index(): View
    {
        $services = ServiceProviderService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name'])
            ->where('service_provider_id', auth()->user()->serviceProvider->id)
            ->latest()
            ->get();

        return view('backend.service_provider.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('backend.service_provider.services.form', [
            'service' => new ServiceProviderService(),
            'categories' => $this->serviceProviderCategories(),
            'isAdmin' => false,
        ]);
    }

    public function store(Request $request)
    {
        $serviceProvider = auth()->user()->serviceProvider?->loadMissing('user');
        $this->applyDefaultListingLocationToRequest($request, $serviceProvider);

        $data = $this->validatedServiceProviderService($request);
        $data['service_provider_id'] = auth()->user()->serviceProvider->id;
        $data['slug'] = $this->uniqueServiceProviderServiceSlug($data['service_provider_id'], $data['name']);

        $service = ServiceProviderService::create($data);

        PortalNotificationService::notifyAdminsOfApprovalRequest('Service provider service', $service->name, route('admin.service-provider-services.show', $service));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Service submitted successfully and sent for admin approval.',
                'redirect' => route('service_provider.services.index'),
            ]);
        }

        return redirect()->route('service_provider.services.index')
            ->with('success', 'Service submitted successfully and sent for admin approval.');
    }

    public function show(ServiceProviderService $service): View
    {
        $this->authorizeOwner($service);
        $service->load(['categoryModel:id,name', 'subcategoryModel:id,name']);

        return view('backend.service_provider.services.show', compact('service'));
    }

    public function edit(ServiceProviderService $service): View
    {
        $this->authorizeOwner($service);

        return view('backend.service_provider.services.form', [
            'service' => $service,
            'categories' => $this->serviceProviderCategories(),
            'isAdmin' => false,
        ]);
    }

    public function update(Request $request, ServiceProviderService $service)
    {
        $this->authorizeOwner($service);
        $serviceProvider = auth()->user()->serviceProvider?->loadMissing('user');
        $this->applyDefaultListingLocationToRequest($request, $serviceProvider);
        $data = $this->validatedServiceProviderService($request, $service);
        $data['slug'] = $this->uniqueServiceProviderServiceSlug($service->service_provider_id, $data['name'], $service->id);

        $service->update($data);

        PortalNotificationService::notifyAdminsOfApprovalRequest('Updated service provider service', $service->name, route('admin.service-provider-services.show', $service));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Service updated successfully and sent for admin approval.',
                'redirect' => route('service_provider.services.index'),
            ]);
        }

        return redirect()->route('service_provider.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(ServiceProviderService $service): JsonResponse
    {
        $this->authorizeOwner($service);
        $service->delete();

        return response()->json(['ok' => true]);
    }

    private function authorizeOwner(ServiceProviderService $service): void
    {
        abort_unless($service->service_provider_id === auth()->user()->serviceProvider?->id, 403);
    }
}
