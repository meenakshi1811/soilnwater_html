<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ParentProfileStatusMail;
use App\Models\ParentProfile;
use App\Services\AccountConversionReversalService;
use App\Services\ParentRegistrationService;
use App\Services\PortalNotificationService;
use App\Support\AuthActor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ParentProfileController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.parent-profiles.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = ParentProfile::query()
            ->with(['user:id,name,full_name,email,phone_number,city,role,created_at'])
            ->withCount([
                'children',
                'children as approved_children_count' => fn ($q) => $q->where('status', 'approved'),
                'children as pending_children_count' => fn ($q) => $q->where('status', 'pending'),
            ]);

        return DataTables::of($query)
            ->addColumn('name', fn (ParentProfile $profile) => e($profile->user?->full_name ?: $profile->user?->name ?: '—'))
            ->addColumn('role_label', fn (ParentProfile $profile) => e(ucfirst(str_replace('_', ' ', (string) $profile->user?->role))))
            ->addColumn('email_display', fn (ParentProfile $profile) => e($profile->user?->email ?: '—'))
            ->addColumn('phone_display', fn (ParentProfile $profile) => e($profile->user?->phone_number ?: '—'))
            ->addColumn('location_display', fn (ParentProfile $profile) => e($profile->location ?: $profile->user?->city ?: '—'))
            ->addColumn('children_display', fn (ParentProfile $profile): string => (string) $profile->children_count
                .' total · '.$profile->approved_children_count.' approved · '.$profile->pending_children_count.' pending')
            ->addColumn('completion_display', fn (ParentProfile $profile) => ($profile->profile_completion ?? 0).'%')
            ->addColumn('status_badge', function (ParentProfile $profile): string {
                return '<span class="badge text-bg-'.$profile->statusBadgeClass().'">'.ucfirst($profile->status).'</span>';
            })
            ->editColumn('created_at', fn (ParentProfile $profile) => $profile->created_at?->format('Y-m-d') ?? '')
            ->addColumn('actions', function (ParentProfile $profile): string {
                $approveBtn = $profile->status !== 'approved'
                    ? '<button type="button" class="btn btn-sm btn-success js-approve-parent-profile" data-id="'.$profile->id.'" title="Approve"><i class="fa-solid fa-check"></i></button>'
                    : '';
                $rejectBtn = $profile->status !== 'rejected'
                    ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject-parent-profile" data-id="'.$profile->id.'" title="Reject"><i class="fa-solid fa-ban"></i></button>'
                    : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .$approveBtn
                    .$rejectBtn
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-parent-profile" data-id="'.$profile->id.'" title="Disable">'
                    .'<i class="fa-solid fa-trash"></i></button>'
                    .'</div>';
            })
            ->filterColumn('status_badge', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if (str_contains($k, 'approve')) {
                    $query->where('status', 'approved');
                } elseif (str_contains($k, 'reject')) {
                    $query->where('status', 'rejected');
                } elseif (str_contains($k, 'pending')) {
                    $query->where('status', 'pending');
                }
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function approve(ParentProfile $parentProfile): JsonResponse
    {
        $parentProfile->loadMissing('user');

        $parentProfile->update([
            'status' => 'approved',
            'is_enabled' => true,
            'approved_at' => now(),
            'approved_by' => AuthActor::usersTableId(),
            'rejection_reason' => null,
        ]);

        $emailSent = $this->sendParentStatusMail($parentProfile, 'approved');
        PortalNotificationService::notifyOwnerOfReview(
            $parentProfile->user,
            'Parent profile',
            ParentRegistrationService::displayName($parentProfile->user),
            'approved',
            route('parent.dashboard')
        );

        return response()->json([
            'message' => 'Parent profile approved.'.($emailSent ? ' Email notification sent.' : ''),
        ]);
    }

    public function reject(Request $request, ParentProfile $parentProfile): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $reason = trim($validated['reason']);
        $parentProfile->loadMissing('user');
        $owner = $parentProfile->user;
        $wasNeverApproved = $parentProfile->approved_at === null;

        $parentProfile->update([
            'status' => 'rejected',
            'is_enabled' => false,
            'approved_at' => null,
            'approved_by' => null,
            'rejection_reason' => $reason,
        ]);

        $emailSent = $this->sendParentStatusMail($parentProfile, 'rejected', $reason);
        $reverted = AccountConversionReversalService::revertParentOnRejection($parentProfile, $wasNeverApproved);
        PortalNotificationService::notifyOwnerOfReview(
            $owner,
            'Parent profile',
            ParentRegistrationService::displayName($owner),
            'rejected',
            $reverted ? route('user.dashboard') : route('parent.pending'),
            $reason
        );

        return response()->json([
            'message' => ($reverted
                ? 'Parent profile rejected and the account was restored as a user.'
                : 'Parent profile rejected.').($emailSent ? ' Email notification sent.' : ''),
        ]);
    }

    public function destroy(ParentProfile $parentProfile): JsonResponse
    {
        $parentProfile->update([
            'is_enabled' => false,
            'status' => $parentProfile->isApproved() ? 'approved' : $parentProfile->status,
        ]);

        return response()->json([
            'message' => 'Parent profile disabled successfully.',
        ]);
    }

    private function sendParentStatusMail(ParentProfile $parentProfile, string $action, ?string $reason = null): bool
    {
        $parentProfile->loadMissing('user');
        $recipient = $parentProfile->user?->email;

        if (! $recipient) {
            return false;
        }

        Mail::to($recipient)->send(ParentProfileStatusMail::forProfile($parentProfile, $action, $reason));

        return true;
    }
}
