<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EducatorStatusMail;
use App\Models\Educator;
use App\Models\User;
use App\Services\PortalNotificationService;
use App\Support\AuthActor;
use App\Support\EducatorFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EducatorController extends Controller
{
    public function index(): View
    {
        return view('backend.educators.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = Educator::query()
            ->with('user:id,name,email,phone_number,created_at')
            ->select([
                'id',
                'user_id',
                'type',
                'display_name',
                'slug',
                'email',
                'phone',
                'city',
                'status',
                'is_verified',
                'approved_at',
                'created_at',
            ]);

        return DataTables::of($query)
            ->addColumn('name', fn (Educator $educator) => e($educator->display_name ?: $educator->user?->name ?: '—'))
            ->addColumn('type_label', function (Educator $educator): string {
                return e($educator->take_tuitions ? 'Teacher / Tutor · Takes tuitions' : 'Teacher / Tutor');
            })
            ->addColumn('email_display', fn (Educator $educator) => e($educator->email ?: $educator->user?->email ?: '—'))
            ->addColumn('phone_display', fn (Educator $educator) => e($educator->phone ?: $educator->user?->phone_number ?: '—'))
            ->addColumn('city_display', fn (Educator $educator) => e($educator->city ?: '—'))
            ->addColumn('status_badge', function (Educator $educator): string {
                $badge = match ($educator->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                };

                return '<span class="badge text-bg-'.$badge.'">'.ucfirst($educator->status).'</span>';
            })
            ->editColumn('created_at', fn (Educator $educator) => $educator->created_at?->format('Y-m-d') ?? '')
            ->addColumn('actions', function (Educator $educator): string {
                $approveBtn = $educator->status !== 'approved'
                    ? '<button type="button" class="btn btn-sm btn-success js-approve-educator" data-id="'.$educator->id.'" title="Approve"><i class="fa-solid fa-check"></i></button>'
                    : '';
                $rejectBtn = $educator->status !== 'rejected'
                    ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject-educator" data-id="'.$educator->id.'" title="Reject"><i class="fa-solid fa-ban"></i></button>'
                    : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.educators.show', $educator).'" class="btn btn-sm btn-outline-secondary" title="View"><i class="fa-solid fa-eye"></i></a>'
                    .$approveBtn
                    .$rejectBtn
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-educator" data-id="'.$educator->id.'" title="Delete"><i class="fa-solid fa-trash"></i></button>'
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

    public function show(Educator $educator): View
    {
        $educator->load(['user', 'approver:id,name', 'studyMaterials' => fn ($q) => $q->latest()->limit(10)]);

        return view('backend.educators.show', compact('educator'));
    }

    public function approve(Request $request, Educator $educator): JsonResponse
    {
        $educator->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => AuthActor::usersTableId(),
            'is_verified' => true,
        ]);

        $emailSent = $this->sendEducatorStatusMail($educator, 'approved');
        PortalNotificationService::notifyOwnerOfReview(
            $educator->user,
            $educator->roleLabel().' account',
            $educator->display_name,
            'approved',
            route('educator.dashboard')
        );

        return response()->json([
            'message' => $educator->roleLabel().' approved. They can now access the educator portal.'.($emailSent ? ' Email notification sent.' : ''),
        ]);
    }

    public function reject(Request $request, Educator $educator): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $reason = trim($validated['reason']);
        $owner = $educator->user;

        $educator->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
            'is_verified' => false,
        ]);

        $emailSent = $this->sendEducatorStatusMail($educator, 'rejected', $reason);
        PortalNotificationService::notifyOwnerOfReview(
            $owner,
            $educator->roleLabel().' account',
            $educator->display_name,
            'rejected',
            route('login'),
            $reason
        );

        return response()->json([
            'message' => $educator->roleLabel().' application rejected.'.($emailSent ? ' Email notification sent.' : ''),
        ]);
    }

    public function destroy(Educator $educator): JsonResponse
    {
        $educator->loadMissing('user');
        $recipient = $this->educatorNotificationRecipient($educator);
        $mail = $recipient ? EducatorStatusMail::forEducator($educator, 'deleted') : null;

        DB::transaction(function () use ($educator): void {
            $userId = $educator->user_id;
            EducatorFileUploader::deleteIfExists($educator->profile_photo);
            EducatorFileUploader::deleteIfExists($educator->video_profile_path);

            foreach ($educator->studyMaterials as $material) {
                EducatorFileUploader::deleteIfExists($material->thumbnail);
                EducatorFileUploader::deleteIfExists($material->file_path);
            }

            $educator->delete();
            User::whereKey($userId)->where('role', 'teacher')->delete();
        });

        if ($recipient && $mail) {
            Mail::to($recipient)->send($mail);
        }

        return response()->json([
            'message' => 'Educator deleted successfully.'.($recipient && $mail ? ' Email notification sent.' : ''),
        ]);
    }

    private function sendEducatorStatusMail(Educator $educator, string $action, ?string $reason = null): bool
    {
        $recipient = $this->educatorNotificationRecipient($educator);

        if (! $recipient) {
            return false;
        }

        Mail::to($recipient)->send(EducatorStatusMail::forEducator($educator, $action, $reason));

        return true;
    }

    private function educatorNotificationRecipient(Educator $educator): ?string
    {
        $educator->loadMissing('user');

        return $educator->email ?: $educator->user?->email;
    }
}
