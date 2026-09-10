<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Services\ChildProfileService;
use App\Services\ParentProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentProfileController extends Controller
{
    public function __construct(
        private ParentProfileService $parentProfileService,
        private ChildProfileService $childProfileService,
    ) {
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user()->load(['parentProfile', 'childProfiles.childUser']);

        abort_unless($user->hasParentProfileEnabled(), 403);

        $parentProfile = $user->parentProfile;
        $children = $user->childProfiles()->with('childUser')->latest()->get();
        $approvedChildren = $children->where('status', 'approved');
        $primaryChild = $children->firstWhere('is_primary', true) ?: $approvedChildren->first();

        return view('backend.parent.dashboard', [
            'user' => $user,
            'parentProfile' => $parentProfile,
            'children' => $children,
            'primaryChild' => $primaryChild,
            'stats' => [
                'children' => $children->count(),
                'materials_saved' => 0,
                'enquiries' => 0,
                'discussions' => 0,
                'following' => 0,
            ],
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $profile = $this->parentProfileService->setEnabled(
            $request->user(),
            (bool) $validated['enabled']
        );

        return response()->json([
            'message' => $profile->is_enabled
                ? 'Parent profile enabled successfully.'
                : 'Parent profile disabled successfully.',
            'enabled' => $profile->is_enabled,
            'dashboard_url' => route('parent.dashboard'),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasParentProfileEnabled(), 403);

        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:2000'],
            'languages' => ['nullable'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = $this->parentProfileService->updateProfile($user, $validated);

        return response()->json([
            'message' => 'Parent profile updated successfully.',
            'profile_completion' => $profile->profile_completion,
        ]);
    }

    public function storeChild(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasParentProfileEnabled(), 403);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gender' => ['nullable', 'in:male,female,other'],
            'class_grade' => ['nullable', 'string', 'max:100'],
            'board' => ['nullable', 'string', 'max:100'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'subjects' => ['nullable'],
            'is_primary' => ['nullable', 'boolean'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
        ], [
            'phone_number.regex' => 'Phone number must contain only digits and be between 10 and 15 characters.',
        ]);

        $childProfile = $this->childProfileService->createForParent(
            $user,
            $validated,
            $request->file('profile_image')
        );

        return response()->json([
            'message' => 'Child profile submitted successfully. Admin approval is required before the child can sign in.',
            'child' => [
                'id' => $childProfile->id,
                'full_name' => $childProfile->full_name,
                'status' => $childProfile->status,
            ],
        ], 201);
    }

    public function destroyChild(Request $request, ChildProfile $childProfile): JsonResponse
    {
        abort_unless($childProfile->parent_user_id === $request->user()->id, 403);

        $this->childProfileService->delete($childProfile);

        return response()->json([
            'message' => 'Child profile deleted successfully.',
        ]);
    }
}
