<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorBranch;
use App\Support\VendorFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorBranchController extends Controller
{
    public function index(): View
    {
        $vendor = auth()->user()->vendor;
        $branches = $vendor->branches()->get();

        return view('backend.vendor.branches.index', compact('vendor', 'branches'));
    }

    public function create(): View
    {
        return view('backend.vendor.branches.form', [
            'branch' => new VendorBranch,
            'vendor' => auth()->user()->vendor,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $vendor = auth()->user()->vendor;
        $validated = $this->validateBranch($request);

        if ($request->hasFile('logo')) {
            $validated['logo'] = VendorFileUploader::storeImage($request->file('logo'), 'branches/logos');
        }

        if ($request->hasFile('gallery')) {
            $validated['gallery'] = VendorFileUploader::storeImages($request->file('gallery'), 'branches/gallery');
        }

        $validated['is_primary'] = $request->boolean('is_primary');
        if ($validated['is_primary']) {
            $vendor->branches()->update(['is_primary' => false]);
        }

        $vendor->branches()->create($validated);

        return redirect()->route('vendor.branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit(VendorBranch $branch): View
    {
        $this->authorizeBranch($branch);

        return view('backend.vendor.branches.form', [
            'branch' => $branch,
            'vendor' => auth()->user()->vendor,
        ]);
    }

    public function update(Request $request, VendorBranch $branch): RedirectResponse
    {
        $this->authorizeBranch($branch);
        $validated = $this->validateBranch($request);

        if ($request->hasFile('logo')) {
            VendorFileUploader::deleteIfExists($branch->logo);
            $validated['logo'] = VendorFileUploader::storeImage($request->file('logo'), 'branches/logos');
        }

        if ($request->hasFile('gallery')) {
            $newGallery = VendorFileUploader::storeImages($request->file('gallery'), 'branches/gallery');
            $validated['gallery'] = array_merge($branch->gallery ?? [], $newGallery);
        }

        $validated['is_primary'] = $request->boolean('is_primary');
        if ($validated['is_primary']) {
            auth()->user()->vendor->branches()->where('id', '!=', $branch->id)->update(['is_primary' => false]);
        }

        $branch->update($validated);

        return redirect()->route('vendor.branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(VendorBranch $branch): JsonResponse
    {
        $this->authorizeBranch($branch);

        VendorFileUploader::deleteIfExists($branch->logo);
        if (is_array($branch->gallery)) {
            foreach ($branch->gallery as $path) {
                VendorFileUploader::deleteIfExists($path);
            }
        }

        $branch->delete();

        return response()->json(['message' => 'Branch deleted permanently.']);
    }

    public function removeGalleryImage(VendorBranch $branch, Request $request): JsonResponse
    {
        $this->authorizeBranch($branch);
        $path = $request->string('path')->toString();
        $gallery = $branch->gallery ?? [];

        if (in_array($path, $gallery, true)) {
            VendorFileUploader::deleteIfExists($path);
            $branch->update(['gallery' => array_values(array_filter($gallery, fn ($p) => $p !== $path))]);
        }

        return response()->json(['message' => 'Image removed.']);
    }

    private function validateBranch(Request $request): array
    {
        return $request->validate([
            'branch_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_primary' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function authorizeBranch(VendorBranch $branch): void
    {
        abort_unless($branch->vendor_id === auth()->user()->vendor?->id, 403);
    }
}
