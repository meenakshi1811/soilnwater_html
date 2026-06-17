<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Concerns\ValidatesConsultantServiceRequest;
use App\Http\Controllers\Controller;
use App\Services\PortalNotificationService;
use App\Models\ConsultantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultantServiceController extends Controller
{
    use ValidatesConsultantServiceRequest;

    public function index(): View
    {
        $services = ConsultantService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name'])
            ->where('consultant_id', auth()->user()->consultant->id)
            ->latest()
            ->get();

        return view('backend.consultant.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('backend.consultant.services.form', [
            'service' => new ConsultantService(),
            'categories' => $this->consultantCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['consultant_id'] = auth()->user()->consultant->id;
        $data['slug'] = $this->uniqueConsultantServiceSlug($data['consultant_id'], $data['name']);

        $service = ConsultantService::create($data);

        PortalNotificationService::notifyAdminsOfApprovalRequest('Consultant service', $service->name, route('admin.consultant-services.show', $service));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Consultation service submitted successfully and sent for admin approval.',
                'redirect' => route('consultant.services.index'),
            ]);
        }

        return redirect()->route('consultant.services.index')
            ->with('success', 'Consultation service submitted successfully and sent for admin approval.');
    }

    public function show(ConsultantService $service): View
    {
        $this->authorizeOwner($service);
        $service->load(['categoryModel:id,name', 'subcategoryModel:id,name']);

        return view('backend.consultant.services.show', compact('service'));
    }

    public function edit(ConsultantService $service): View
    {
        $this->authorizeOwner($service);

        return view('backend.consultant.services.form', [
            'service' => $service,
            'categories' => $this->consultantCategories(),
        ]);
    }

    public function update(Request $request, ConsultantService $service)
    {
        $this->authorizeOwner($service);
        $data = $this->validated($request, $service, false);
        $data['slug'] = $this->uniqueConsultantServiceSlug($service->consultant_id, $data['name'], $service->id);

        $service->update($data);

        PortalNotificationService::notifyAdminsOfApprovalRequest('Updated consultant service', $service->name, route('admin.consultant-services.show', $service));

        return redirect()->route('consultant.services.index')->with('success', 'Consultation service updated successfully.');
    }

    public function destroy(ConsultantService $service): JsonResponse
    {
        $this->authorizeOwner($service);
        $service->delete();

        return response()->json(['ok' => true]);
    }

    private function authorizeOwner(ConsultantService $service): void
    {
        abort_unless($service->consultant_id === auth()->user()->consultant?->id, 403);
    }

    private function validated(Request $request, ?ConsultantService $service = null, bool $requireTerms = true): array
    {
        if ($requireTerms && ! $service?->exists) {
            $request->validate(['accept_terms' => ['accepted']]);
        }

        return $this->validatedConsultantService($request, $service);
    }
}
