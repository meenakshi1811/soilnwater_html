<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InstituteStatusMail;
use App\Models\Institute;
use App\Models\User;
use App\Services\PortalNotificationService;
use App\Support\AuthActor;
use App\Support\InstituteFileUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class InstituteController extends Controller
{
    protected string $ownerRole = 'institute';

    protected string $adminRoutePrefix = 'admin.institutes';

    protected string $portalRoutePrefix = 'institute';

    protected string $publicRouteName = 'institutes.show';

    protected string $entityLabel = 'Institute';

    protected string $accountLabel = 'Institute account';

    protected string $createAccountRole = 'institute';

    public function index(): View
    {
        return view('backend.institutes.index', $this->adminViewData());
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = $this->scopedQuery()
            ->with('user:id,name,email,phone_number,created_at,role')
            ->select([
                'id',
                'user_id',
                'institution_name',
                'contact_person',
                'slug',
                'email',
                'phone',
                'city',
                'state',
                'institution_type',
                'status',
                'is_verified',
                'approved_at',
                'created_at',
            ]);

        return DataTables::of($query)
            ->addColumn('name', fn (Institute $institute) => e($institute->institution_name ?: '—'))
            ->addColumn('type_label', fn (Institute $institute) => e($institute->institutionTypeLabel()))
            ->addColumn('email_display', fn (Institute $institute) => e($institute->email ?: $institute->user?->email ?: '—'))
            ->addColumn('phone_display', fn (Institute $institute) => e($institute->phone ?: $institute->user?->phone_number ?: '—'))
            ->addColumn('city_display', fn (Institute $institute) => e($institute->city ?: '—'))
            ->addColumn('status_badge', function (Institute $institute): string {
                $badge = match ($institute->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                };

                return '<span class="badge text-bg-'.$badge.'">'.ucfirst($institute->status).'</span>';
            })
            ->addColumn('public_page_link', function (Institute $institute): string {
                if ($institute->isApproved()) {
                    return '<a class="service-page-link" href="'.route($this->publicRouteName, $institute->slug).'" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>View page</span></a>';
                }

                return '<span class="text-muted">—</span>';
            })
            ->editColumn('created_at', fn (Institute $institute) => $institute->created_at?->format('Y-m-d') ?? '')
            ->addColumn('actions', function (Institute $institute): string {
                $approveBtn = $institute->status !== 'approved'
                    ? '<button type="button" class="btn btn-sm btn-success js-approve-institute" data-id="'.$institute->id.'" title="Approve"><i class="fa-solid fa-check"></i></button>'
                    : '';
                $rejectBtn = $institute->status !== 'rejected'
                    ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject-institute" data-id="'.$institute->id.'" title="Reject"><i class="fa-solid fa-ban"></i></button>'
                    : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route($this->adminRoutePrefix.'.show', $institute).'" class="btn btn-sm btn-outline-secondary" title="View"><i class="fa-solid fa-eye"></i></a>'
                    .$approveBtn
                    .$rejectBtn
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-institute" data-id="'.$institute->id.'" title="Delete"><i class="fa-solid fa-trash"></i></button>'
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
            ->rawColumns(['status_badge', 'public_page_link', 'actions'])
            ->make(true);
    }

    public function show(Institute $institute): View
    {
        $this->ensureOwnerRole($institute);
        $institute->load(['user', 'approver:id,name']);

        return view('backend.institutes.show', array_merge($this->adminViewData(), compact('institute')));
    }

    public function approve(Request $request, Institute $institute): JsonResponse
    {
        $this->ensureOwnerRole($institute);
        $institute->loadMissing('user');

        $institute->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => AuthActor::usersTableId(),
            'is_verified' => true,
        ]);

        $emailSent = $this->sendInstituteStatusMail($institute->fresh('user'), 'approved');
        PortalNotificationService::notifyOwnerOfReview(
            $institute->user,
            $this->accountLabel,
            $institute->displayName(),
            'approved',
            route($this->portalRoutePrefix.'.dashboard')
        );

        return response()->json([
            'message' => $this->entityLabel.' approved. They can now access the portal.'.($emailSent ? ' Email and portal notification sent.' : ' Portal notification sent.'),
        ]);
    }

    public function reject(Request $request, Institute $institute): JsonResponse
    {
        $this->ensureOwnerRole($institute);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $reason = trim($validated['reason']);
        $institute->loadMissing('user');
        $owner = $institute->user;

        $institute->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
            'is_verified' => false,
        ]);

        $emailSent = $this->sendInstituteStatusMail($institute->fresh('user'), 'rejected', $reason);
        PortalNotificationService::notifyOwnerOfReview(
            $owner,
            $this->accountLabel,
            $institute->displayName(),
            'rejected',
            route('login'),
            $reason
        );

        return response()->json([
            'message' => $this->entityLabel.' application rejected.'.($emailSent ? ' Email and portal notification sent.' : ' Portal notification sent.'),
        ]);
    }

    public function destroy(Institute $institute): JsonResponse
    {
        $this->ensureOwnerRole($institute);

        $institute->loadMissing('user');
        $owner = $institute->user;
        $recipient = $this->instituteNotificationRecipient($institute);
        $displayName = $institute->displayName();
        $mail = $recipient ? InstituteStatusMail::forInstitute($institute, 'deleted') : null;
        $ownerRole = $this->ownerRole;

        DB::transaction(function () use ($institute, $ownerRole): void {
            $userId = $institute->user_id;
            InstituteFileUploader::deleteIfExists($institute->logo);
            $institute->delete();
            User::whereKey($userId)->where('role', $ownerRole)->delete();
        });

        $emailSent = false;
        if ($recipient && $mail) {
            try {
                Mail::to($recipient)->send($mail);
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::error('Failed to send institute deleted mail', [
                    'email' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($owner) {
            PortalNotificationService::notifyOwnerOfReview(
                $owner,
                $this->accountLabel,
                $displayName,
                'deleted',
                route('login')
            );
        }

        return response()->json([
            'message' => $this->entityLabel.' deleted successfully.'.($emailSent ? ' Email notification sent.' : ''),
        ]);
    }

    protected function scopedQuery(): Builder
    {
        return Institute::query()->forOwnerRole($this->ownerRole);
    }

    /**
     * @return array<string, mixed>
     */
    protected function adminViewData(): array
    {
        return [
            'ownerRole' => $this->ownerRole,
            'adminRoutePrefix' => $this->adminRoutePrefix,
            'entityLabel' => $this->entityLabel,
            'pageTitle' => $this->entityLabel.'s',
            'pageKicker' => $this->entityLabel.' Management',
            'createAccountRole' => $this->createAccountRole,
            'createAccountLabel' => 'Add '.$this->entityLabel,
        ];
    }

    protected function ensureOwnerRole(Institute $institute): void
    {
        $institute->loadMissing('user');
        abort_unless($institute->user?->role === $this->ownerRole, 404);
    }

    private function sendInstituteStatusMail(Institute $institute, string $action, ?string $reason = null): bool
    {
        $recipient = $this->instituteNotificationRecipient($institute);

        if (! $recipient) {
            return false;
        }

        try {
            Mail::to($recipient)->send(InstituteStatusMail::forInstitute($institute, $action, $reason));

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send institute status mail', [
                'institute_id' => $institute->id,
                'action' => $action,
                'email' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function instituteNotificationRecipient(Institute $institute): ?string
    {
        $institute->loadMissing('user');

        return $institute->email ?: $institute->user?->email;
    }
}
