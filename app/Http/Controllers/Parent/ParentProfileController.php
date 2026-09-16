<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Services\ChildProfileService;
use App\Services\ParentProfileService;
use App\Support\ActiveChildSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        $activeChildProfile = ActiveChildSession::profile();

        return view('backend.parent.dashboard', [
            'user' => $user,
            'parentProfile' => $parentProfile,
            'children' => $children,
            'approvedChildren' => $approvedChildren,
            'primaryChild' => $primaryChild,
            'activeChildProfile' => $activeChildProfile,
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
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'dob_day' => ['required', 'integer', 'min:1', 'max:31'],
            'dob_month' => ['required', 'integer', 'min:1', 'max:12'],
            'dob_year' => ['required', 'integer', 'min:'.(now()->year - 25), 'max:'.now()->year],
            'age' => ['required', 'integer', 'min:1', 'max:25'],
            'gender' => ['nullable', 'in:male,female,other'],
            'class_grade' => ['nullable', 'string', 'max:100'],
            'has_board' => ['nullable', 'boolean'],
            'board' => ['nullable', 'required_if:has_board,1,true', 'string', 'max:100'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'subjects' => ['nullable'],
            'is_primary' => ['nullable', 'boolean'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
        ], [
            'phone_number.regex' => 'Phone number must contain only digits and be between 10 and 15 characters.',
            'board.required_if' => 'Please enter the board name when board is enabled.',
        ]);

        if (! ($validated['has_board'] ?? false)) {
            $validated['board'] = null;
        }

        if (! checkdate((int) $validated['dob_month'], (int) $validated['dob_day'], (int) $validated['dob_year'])) {
            return response()->json([
                'message' => 'Please enter a valid date of birth.',
                'errors' => ['date_of_birth' => ['Please enter a valid date of birth.']],
            ], 422);
        }

        $dateOfBirth = Carbon::createFromDate(
            (int) $validated['dob_year'],
            (int) $validated['dob_month'],
            (int) $validated['dob_day']
        );

        if ($dateOfBirth->isFuture()) {
            return response()->json([
                'message' => 'Date of birth cannot be in the future.',
                'errors' => ['date_of_birth' => ['Date of birth cannot be in the future.']],
            ], 422);
        }

        $calculatedAge = $dateOfBirth->age;
        if (abs($calculatedAge - (int) $validated['age']) > 1) {
            return response()->json([
                'message' => 'Age does not match the selected date of birth.',
                'errors' => ['age' => ['Age does not match the selected date of birth.']],
            ], 422);
        }

        $validated['date_of_birth'] = $dateOfBirth->toDateString();

        $childProfile = $this->childProfileService->createForParent(
            $user,
            $validated,
            $request->file('profile_image')
        );

        return response()->json([
            'message' => 'Child profile submitted successfully. Admin approval is required before you can access this profile from your parent dashboard.',
            'child' => [
                'id' => $childProfile->id,
                'full_name' => $childProfile->full_name,
                'status' => $childProfile->status,
            ],
        ], 201);
    }

    public function switchToChild(Request $request, ChildProfile $childProfile): RedirectResponse
    {
        abort_unless($childProfile->parent_user_id === $request->user()->id, 403);
        abort_unless($childProfile->isApproved(), 404);
        abort_unless($request->user()->hasParentProfileEnabled(), 403);

        ActiveChildSession::activate($childProfile);

        return redirect()
            ->route('child.dashboard')
            ->with('success', 'You are now viewing '.$childProfile->full_name.'\'s profile.');
    }

    public function switchBack(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasParentProfileEnabled(), 403);

        ActiveChildSession::clear();

        return redirect()
            ->route('parent.dashboard')
            ->with('success', 'Returned to parent dashboard.');
    }

    public function destroyChild(Request $request, ChildProfile $childProfile): JsonResponse
    {
        abort_unless($childProfile->parent_user_id === $request->user()->id, 403);

        if (ActiveChildSession::id() === $childProfile->id) {
            ActiveChildSession::clear();
        }

        $this->childProfileService->delete($childProfile);

        return response()->json([
            'message' => 'Child profile deleted successfully.',
        ]);
    }
}
