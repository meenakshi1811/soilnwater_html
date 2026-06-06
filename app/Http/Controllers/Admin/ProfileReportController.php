<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use App\Models\ProfileReport;
use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProfileReportController extends Controller
{
    public function consultants(): View
    {
        return $this->index('consultant');
    }

    public function serviceProviders(): View
    {
        return $this->index('service-provider');
    }

    public function consultantData(Request $request): JsonResponse
    {
        return $this->data($request, Consultant::class, 'consultant');
    }

    public function serviceProviderData(Request $request): JsonResponse
    {
        return $this->data($request, ServiceProvider::class, 'service-provider');
    }

    public function deleteConsultant(Consultant $consultant): RedirectResponse
    {
        return $this->deleteReportable($consultant, 'Consultant');
    }

    public function deleteServiceProvider(ServiceProvider $service_provider): RedirectResponse
    {
        return $this->deleteReportable($service_provider, 'Service provider');
    }

    private function index(string $type): View
    {
        $isConsultant = $type === 'consultant';

        return view('backend.profile-reports.index', [
            'title' => $isConsultant ? 'Reported Consultants' : 'Reported Services',
            'entityLabel' => $isConsultant ? 'Consultant' : 'Service',
            'tableId' => $isConsultant ? 'consultantReportsTable' : 'serviceProviderReportsTable',
            'dataUrl' => $isConsultant
                ? route('admin.consultants.reports.data')
                : route('admin.service_providers.reports.data'),
        ]);
    }

    private function data(Request $request, string $reportableType, string $routeType): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $reports = ProfileReport::query()
            ->where('reportable_type', $reportableType)
            ->with(['reportable', 'reporter:id,name,full_name'])
            ->select(['id', 'reportable_type', 'reportable_id', 'reported_by', 'reason', 'created_at'])
            ->latest();

        return DataTables::of($reports)
            ->addColumn('profile_name', function (ProfileReport $report): string {
                $profile = $report->reportable;

                return $profile?->publicDisplayName() ?? 'Deleted profile';
            })
            ->addColumn('reporter_name', fn (ProfileReport $report): string => $report->reporter?->full_name ?? $report->reporter?->name ?? 'Guest')
            ->editColumn('created_at', fn (ProfileReport $report): string => $report->created_at?->format('d M Y H:i') ?? '-')
            ->addColumn('actions', function (ProfileReport $report) use ($routeType): string {
                if (! $report->reportable) {
                    return '-';
                }

                $route = $routeType === 'consultant'
                    ? 'admin.consultants.reports.delete-consultant'
                    : 'admin.service_providers.reports.delete-service-provider';
                $label = $routeType === 'consultant' ? 'Consultant' : 'Service';
                $action = route($route, $report->reportable);

                return '<form method="POST" action="'.$action.'" onsubmit="return confirm(\'Delete this '.strtolower($label).'?\')" class="d-inline">'
                    .csrf_field()
                    .method_field('DELETE')
                    .'<button type="submit" class="btn btn-sm btn-danger">Delete '.$label.'</button>'
                    .'</form>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    private function deleteReportable(Model $reportable, string $label): RedirectResponse
    {
        $reportable->delete();

        return back()->with('success', $label.' deleted successfully.');
    }
}
