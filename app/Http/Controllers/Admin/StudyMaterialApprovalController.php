<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StudyMaterialStatusMail;
use App\Models\StudyMaterial;
use App\Services\PortalNotificationService;
use App\Support\AuthActor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudyMaterialApprovalController extends Controller
{
    public function index(Request $request): View
    {
        return view('backend.study-materials.index', [
            'statusFilter' => $request->string('status')->toString() ?: 'pending',
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = StudyMaterial::query()
            ->with(['educator:id,display_name,type', 'user:id,name'])
            ->select([
                'id',
                'educator_id',
                'user_id',
                'title',
                'material_type',
                'subject',
                'class_course',
                'status',
                'created_at',
                'updated_at',
            ])
            ->orderByDesc('updated_at');

        $status = $request->string('status')->toString();
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        return DataTables::of($query)
            ->addColumn('educator_name', fn (StudyMaterial $material): string => e($material->educator?->display_name ?: $material->user?->name ?: '—'))
            ->addColumn('type_label', fn (StudyMaterial $material): string => e($material->materialTypeLabel()))
            ->addColumn('subject_class', function (StudyMaterial $material): string {
                return e(collect([$material->subject, $material->class_course])->filter()->implode(' · ') ?: '—');
            })
            ->addColumn('status_badge', function (StudyMaterial $material): string {
                $status = $material->status ?? 'pending';
                $badge = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');

                return '<span class="badge bg-'.$badge.'">'.ucfirst($status).'</span>';
            })
            ->addColumn('actions', function (StudyMaterial $material): string {
                $status = $material->status ?? 'pending';
                $approve = $status !== 'approved'
                    ? '<button type="button" class="btn btn-sm btn-success js-approve" data-id="'.$material->id.'">Approve</button>'
                    : '';
                $reject = $status !== 'rejected'
                    ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject" data-id="'.$material->id.'">Reject</button>'
                    : '';

                return '<div class="d-flex gap-2 justify-content-end">'.$approve.$reject.'</div>';
            })
            ->editColumn('updated_at', function (StudyMaterial $material): string {
                return optional($material->updated_at)
                    ? $material->updated_at->timezone(config('app.timezone'))->format('d M Y, h:i A')
                    : '—';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function approve(StudyMaterial $study_material): JsonResponse
    {
        $study_material->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => AuthActor::usersTableId(),
            'is_verified' => true,
        ]);

        $study_material->loadMissing(['educator.user', 'user']);
        $owner = $study_material->educator?->user ?: $study_material->user;
        $emailSent = $this->sendStatusMail($study_material, 'approved');

        PortalNotificationService::notifyOwnerOfReview(
            $owner,
            'Study material',
            $study_material->title,
            'approved',
            route('educator.materials.index')
        );

        return response()->json([
            'message' => 'Study material approved.'.($emailSent ? ' Email and portal notification sent.' : ' Portal notification sent.'),
        ]);
    }

    public function reject(StudyMaterial $study_material): JsonResponse
    {
        $study_material->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
            'is_verified' => false,
        ]);

        $study_material->loadMissing(['educator.user', 'user']);
        $owner = $study_material->educator?->user ?: $study_material->user;
        $emailSent = $this->sendStatusMail($study_material, 'rejected', 'Declined by admin.');

        PortalNotificationService::notifyOwnerOfReview(
            $owner,
            'Study material',
            $study_material->title,
            'rejected',
            route('educator.materials.index'),
            'Declined by admin.'
        );

        return response()->json([
            'message' => 'Study material rejected.'.($emailSent ? ' Email and portal notification sent.' : ' Portal notification sent.'),
        ]);
    }

    private function sendStatusMail(StudyMaterial $material, string $action, ?string $reason = null): bool
    {
        $material->loadMissing(['educator.user', 'user']);
        $recipient = $material->educator?->email
            ?: $material->educator?->user?->email
            ?: $material->user?->email;

        if (! $recipient) {
            return false;
        }

        try {
            Mail::to($recipient)->send(StudyMaterialStatusMail::forMaterial($material, $action, $reason));

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send study material status mail', [
                'material_id' => $material->id,
                'action' => $action,
                'email' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
