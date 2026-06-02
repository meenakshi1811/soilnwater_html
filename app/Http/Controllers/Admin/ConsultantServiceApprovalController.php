<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ConsultantServiceApprovalController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.consultant-services.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = ConsultantService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name', 'consultant:id,company_name,display_name'])
            ->where(fn ($pending) => $pending->where('status', 'pending')->orWhereNull('status'))
            ->select(['id', 'consultant_id', 'name', 'category', 'category_id', 'subcategory_id', 'price', 'status', 'created_at']);

        return $this->datatable($query, true);
    }

    public function allServicesIndex(): View
    {
        return view('backend.admin.consultant-services.all-services');
    }

    public function allServicesData(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = ConsultantService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name', 'consultant:id,company_name,display_name'])
            ->select(['id', 'consultant_id', 'name', 'category', 'category_id', 'subcategory_id', 'price', 'status', 'created_at']);

        if (in_array($request->string('status')->toString(), ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $request->string('status')->toString());
        }

        return $this->datatable($query, false);
    }

    public function show(ConsultantService $service): View
    {
        $service->load(['categoryModel:id,name', 'subcategoryModel:id,name', 'consultant:id,company_name,display_name']);

        return view('backend.admin.consultant-services.show', compact('service'));
    }

    public function approve(ConsultantService $service, Request $request): JsonResponse
    {
        $service->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $request->user()->id]);

        return response()->json(['message' => 'Consultation service approved.']);
    }

    public function reject(ConsultantService $service): JsonResponse
    {
        $service->update(['status' => 'rejected', 'approved_at' => null, 'approved_by' => null]);

        return response()->json(['message' => 'Consultation service rejected.']);
    }

    public function destroy(ConsultantService $service): JsonResponse
    {
        $service->delete();

        return response()->json(['message' => 'Consultation service deleted successfully.']);
    }

    private function datatable($query, bool $withApprovalActions): JsonResponse
    {
        return DataTables::of($query)
            ->addColumn('consultant_name', fn (ConsultantService $service): string => e($service->consultant?->display_name ?: $service->consultant?->company_name ?: '—'))
            ->addColumn('category_display', function (ConsultantService $service): string {
                $category = $service->categoryModel?->name ?? (is_string($service->category) ? $service->category : '-');
                $subcategory = $service->subcategoryModel?->name ?? '-';

                return e($category.' / '.$subcategory);
            })
            ->addColumn('price_display', fn (ConsultantService $service): string => e($service->formattedConsultationCharges()))
            ->addColumn('status_badge', function (ConsultantService $service): string {
                $status = $service->status ?? 'pending';
                $badge = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');

                return '<span class="badge bg-'.$badge.'">'.ucfirst($status).'</span>';
            })
            ->addColumn('actions', function (ConsultantService $service) use ($withApprovalActions): string {
                $status = $service->status ?? 'pending';
                $approve = $withApprovalActions && $status !== 'approved' ? '<button type="button" class="btn btn-sm btn-success js-approve" data-id="'.$service->id.'">Approve</button>' : '';
                $reject = $withApprovalActions && $status !== 'rejected' ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject" data-id="'.$service->id.'">Reject</button>' : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.consultant-services.show', $service).'" class="btn btn-sm btn-outline-secondary">View</a>'
                    .$approve
                    .$reject
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete" data-id="'.$service->id.'">Delete</button>'
                    .'</div>';
            })
            ->editColumn('created_at', function (ConsultantService $service): string {
                return optional($service->created_at)
                    ? $service->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A')
                    : '-';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }
}
