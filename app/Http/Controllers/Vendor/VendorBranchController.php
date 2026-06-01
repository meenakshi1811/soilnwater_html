<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorBranch;
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

    public function store(Request $request): JsonResponse
    {
        $vendor = auth()->user()->vendor;
        $validated = $this->validateBranch($request);

        $validated['is_primary'] = $request->boolean('is_primary');
        if ($validated['is_primary']) {
            $vendor->branches()->update(['is_primary' => false]);
        }

        $vendor->branches()->create($validated);

        return response()->json([
            'message' => 'Branch created successfully.',
            'redirect' => route('vendor.branches.index'),
        ]);
    }

    public function edit(VendorBranch $branch): View
    {
        $this->authorizeBranch($branch);

        return view('backend.vendor.branches.form', [
            'branch' => $branch,
            'vendor' => auth()->user()->vendor,
        ]);
    }

    public function update(Request $request, VendorBranch $branch): JsonResponse
    {
        $this->authorizeBranch($branch);
        $validated = $this->validateBranch($request);

        $validated['is_primary'] = $request->boolean('is_primary');
        if ($validated['is_primary']) {
            auth()->user()->vendor->branches()->where('id', '!=', $branch->id)->update(['is_primary' => false]);
        }

        $branch->update($validated);

        return response()->json([
            'message' => 'Branch updated successfully.',
            'redirect' => route('vendor.branches.index'),
        ]);
    }

    public function destroy(VendorBranch $branch): JsonResponse
    {
        $this->authorizeBranch($branch);

        $branch->delete();

        return response()->json(['message' => 'Branch deleted permanently.']);
    }

    private function validateBranch(Request $request): array
    {
        $validated = $request->validate([
            'branch_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
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

        unset($validated['has_gst']);

        return $validated;
    }

    private function authorizeBranch(VendorBranch $branch): void
    {
        abort_unless($branch->vendor_id === auth()->user()->vendor?->id, 403);
    }
}
