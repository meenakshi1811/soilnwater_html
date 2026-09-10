<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Services\ChildProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ChildProfileController extends Controller
{
    public function __construct(
        private ChildProfileService $childProfileService,
    ) {
    }

    public function index(): View
    {
        return view('backend.admin.child-profiles.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = ChildProfile::query()
            ->with([
                'parentUser:id,name,full_name,email',
                'childUser:id,name,email',
            ]);

        if ($request->filled('status') && in_array($request->string('status')->toString(), ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $request->string('status')->toString());
        }

        return DataTables::of($query)
            ->addColumn('child_name', fn (ChildProfile $child) => e($child->full_name))
            ->addColumn('parent_name', fn (ChildProfile $child) => e($child->parentUser?->full_name ?: $child->parentUser?->name ?: '—'))
            ->addColumn('email_display', fn (ChildProfile $child) => e($child->email))
            ->addColumn('phone_display', fn (ChildProfile $child) => e($child->phone_number))
            ->addColumn('class_display', fn (ChildProfile $child) => e(trim(($child->class_grade ?: '').($child->board ? ' · '.$child->board : '')) ?: '—'))
            ->addColumn('status_badge', function (ChildProfile $child): string {
                return '<span class="badge text-bg-'.$child->statusBadgeClass().'">'.ucfirst($child->status).'</span>';
            })
            ->editColumn('created_at', fn (ChildProfile $child) => $child->created_at?->format('Y-m-d') ?? '')
            ->addColumn('actions', function (ChildProfile $child): string {
                $approveBtn = $child->status !== 'approved'
                    ? '<button type="button" class="btn btn-sm btn-success js-approve-child" data-id="'.$child->id.'" title="Approve"><i class="fa-solid fa-check"></i></button>'
                    : '';
                $rejectBtn = $child->status !== 'rejected'
                    ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject-child" data-id="'.$child->id.'" title="Decline"><i class="fa-solid fa-ban"></i></button>'
                    : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.child-profiles.show', $child).'" class="btn btn-sm btn-outline-secondary" title="View"><i class="fa-solid fa-eye"></i></a>'
                    .$approveBtn
                    .$rejectBtn
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-child" data-id="'.$child->id.'" title="Delete"><i class="fa-solid fa-trash"></i></button>'
                    .'</div>';
            })
            ->filterColumn('status_badge', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if (str_contains($k, 'approve')) {
                    $query->where('status', 'approved');
                } elseif (str_contains($k, 'reject') || str_contains($k, 'declin')) {
                    $query->where('status', 'rejected');
                } elseif (str_contains($k, 'pending')) {
                    $query->where('status', 'pending');
                }
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(ChildProfile $childProfile): View
    {
        $childProfile->load(['parentUser', 'childUser', 'approver:id,name']);

        return view('backend.admin.child-profiles.show', [
            'childProfile' => $childProfile,
        ]);
    }

    public function approve(ChildProfile $childProfile): JsonResponse
    {
        if ($childProfile->isApproved()) {
            return response()->json(['message' => 'Child profile is already approved.']);
        }

        $this->childProfileService->approve($childProfile);

        return response()->json(['message' => 'Child profile approved successfully.']);
    }

    public function reject(Request $request, ChildProfile $childProfile): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $validated['reason'] ?? $validated['review_note'] ?? null;
        $this->childProfileService->reject($childProfile, $reason);

        return response()->json(['message' => 'Child profile declined successfully.']);
    }

    public function destroy(ChildProfile $childProfile): JsonResponse
    {
        $this->childProfileService->delete($childProfile);

        return response()->json(['message' => 'Child profile deleted successfully.']);
    }
}
