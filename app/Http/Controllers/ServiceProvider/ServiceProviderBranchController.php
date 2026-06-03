<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\ServiceProviderBranch;
use App\Support\ServiceProviderFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceProviderBranchController extends Controller
{
    public function index(): View
    {
        $service_provider = auth()->user()->serviceProvider;
        $branches = $service_provider->branches()->get();

        return view('backend.service_provider.branches.index', compact('service_provider', 'branches'));
    }

    public function create(): View
    {
        return view('backend.service_provider.branches.form', [
            'branch' => new ServiceProviderBranch,
            'service_provider' => auth()->user()->serviceProvider,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $service_provider = auth()->user()->serviceProvider;
        $validated = $this->validateBranch($request);

        if ($request->hasFile('profile_image')) {
            $validated['logo'] = ServiceProviderFileUploader::storeImage($request->file('profile_image'), 'branch-profiles');
        }

        $validated['is_primary'] = $request->boolean('is_primary');
        if ($validated['is_primary']) {
            $service_provider->branches()->update(['is_primary' => false]);
        }

        $service_provider->branches()->create($validated);

        return response()->json([
            'message' => 'Branch created successfully.',
            'redirect' => route('service_provider.branches.index'),
        ]);
    }

    public function edit(ServiceProviderBranch $branch): View
    {
        $this->authorizeBranch($branch);

        return view('backend.service_provider.branches.form', [
            'branch' => $branch,
            'service_provider' => auth()->user()->serviceProvider,
        ]);
    }

    public function update(Request $request, ServiceProviderBranch $branch): JsonResponse
    {
        $this->authorizeBranch($branch);
        $validated = $this->validateBranch($request);

        if ($request->hasFile('profile_image')) {
            ServiceProviderFileUploader::deleteIfExists($branch->logo);
            $validated['logo'] = ServiceProviderFileUploader::storeImage($request->file('profile_image'), 'branch-profiles');
        }

        $validated['is_primary'] = $request->boolean('is_primary');
        if ($validated['is_primary']) {
            auth()->user()->serviceProvider->branches()->where('id', '!=', $branch->id)->update(['is_primary' => false]);
        }

        $branch->update($validated);

        return response()->json([
            'message' => 'Branch updated successfully.',
            'redirect' => route('service_provider.branches.index'),
        ]);
    }

    public function destroy(ServiceProviderBranch $branch): JsonResponse
    {
        $this->authorizeBranch($branch);

        ServiceProviderFileUploader::deleteIfExists($branch->logo);

        $branch->delete();

        return response()->json(['message' => 'Branch deleted permanently.']);
    }

    private function validateBranch(Request $request): array
    {
        $validated = $request->validate([
            'branch_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'phone' => ['nullable', 'string', 'max:20'],
            'alt_mobile_number' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:10'],
            'pan_number' => ['required', 'string', 'max:20'],
            'has_gst' => ['required', 'boolean'],
            'gst_number' => ['required_if:has_gst,1', 'nullable', 'string', 'max:20'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        if (! $request->boolean('has_gst')) {
            $validated['gst_number'] = null;
        }

        unset($validated['has_gst'], $validated['profile_image']);

        return $validated;
    }

    private function authorizeBranch(ServiceProviderBranch $branch): void
    {
        abort_unless($branch->service_provider_id === auth()->user()->serviceProvider?->id, 403);
    }
}
