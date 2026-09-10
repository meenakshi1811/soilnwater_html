<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Services\ParentProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ])
            ->where('is_enabled', true);

        return DataTables::of($query)
            ->addColumn('name', fn (ParentProfile $profile) => e($profile->user?->full_name ?: $profile->user?->name ?: '—'))
            ->addColumn('role_label', fn (ParentProfile $profile) => e(ucfirst(str_replace('_', ' ', (string) $profile->user?->role))))
            ->addColumn('email_display', fn (ParentProfile $profile) => e($profile->user?->email ?: '—'))
            ->addColumn('phone_display', fn (ParentProfile $profile) => e($profile->user?->phone_number ?: '—'))
            ->addColumn('location_display', fn (ParentProfile $profile) => e($profile->location ?: $profile->user?->city ?: '—'))
            ->addColumn('children_display', fn (ParentProfile $profile): string => (string) $profile->children_count
                .' total · '.$profile->approved_children_count.' approved · '.$profile->pending_children_count.' pending')
            ->addColumn('completion_display', fn (ParentProfile $profile) => ($profile->profile_completion ?? 0).'%')
            ->editColumn('created_at', fn (ParentProfile $profile) => $profile->created_at?->format('Y-m-d') ?? '')
            ->addColumn('actions', function (ParentProfile $profile): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-parent-profile" data-id="'.$profile->id.'" title="Disable & remove">'
                    .'<i class="fa-solid fa-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function destroy(ParentProfile $parentProfile, ParentProfileService $parentProfileService): JsonResponse
    {
        $user = $parentProfile->user;
        if ($user) {
            $parentProfileService->setEnabled($user, false);
        }

        return response()->json([
            'message' => 'Parent profile disabled successfully.',
        ]);
    }
}
