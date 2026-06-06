<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\ConsultantBranch;
use App\Support\ConsultantFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultantBranchController extends Controller
{
    public function index(): View
    {
        $consultant = auth()->user()->consultant;
        $branches = $consultant->branches()->get();

        return view('backend.consultant.branches.index', compact('consultant', 'branches'));
    }

    public function create(): View
    {
        return view('backend.consultant.branches.form', [
            'branch' => new ConsultantBranch,
            'consultant' => auth()->user()->consultant,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $consultant = auth()->user()->consultant;
        $validated = $this->validateBranch($request);

        if ($request->hasFile('profile_image')) {
            $validated['logo'] = ConsultantFileUploader::storeImage($request->file('profile_image'), 'branch-profiles');
        }

        $validated['is_primary'] = $request->boolean('is_primary');
        if ($validated['is_primary']) {
            $consultant->branches()->update(['is_primary' => false]);
        }

        $consultant->branches()->create($validated);

        return response()->json([
            'message' => 'Branch created successfully.',
            'redirect' => route('consultant.branches.index'),
        ]);
    }

    public function edit(ConsultantBranch $branch): View
    {
        $this->authorizeBranch($branch);

        return view('backend.consultant.branches.form', [
            'branch' => $branch,
            'consultant' => auth()->user()->consultant,
        ]);
    }

    public function update(Request $request, ConsultantBranch $branch): JsonResponse
    {
        $this->authorizeBranch($branch);
        $validated = $this->validateBranch($request);

        if ($request->hasFile('profile_image')) {
            ConsultantFileUploader::deleteIfExists($branch->logo);
            $validated['logo'] = ConsultantFileUploader::storeImage($request->file('profile_image'), 'branch-profiles');
        }

        $validated['is_primary'] = $request->boolean('is_primary');
        if ($validated['is_primary']) {
            auth()->user()->consultant->branches()->where('id', '!=', $branch->id)->update(['is_primary' => false]);
        }

        $branch->update($validated);

        return response()->json([
            'message' => 'Branch updated successfully.',
            'redirect' => route('consultant.branches.index'),
        ]);
    }

    public function destroy(ConsultantBranch $branch): JsonResponse
    {
        $this->authorizeBranch($branch);

        ConsultantFileUploader::deleteIfExists($branch->logo);

        $branch->delete();

        return response()->json(['message' => 'Branch deleted permanently.']);
    }

    private function validateBranch(Request $request): array
    {
        $validated = $request->validate([
            'branch_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'professional_experience' => ['nullable', 'string', 'max:5000'],
            'services_offered' => ['nullable', 'string', 'max:5000'],
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

    private function authorizeBranch(ConsultantBranch $branch): void
    {
        abort_unless($branch->consultant_id === auth()->user()->consultant?->id, 403);
    }
}
