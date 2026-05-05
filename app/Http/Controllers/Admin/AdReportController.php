<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdReport;
use App\Models\UserAd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AdReportController extends Controller
{
    public function index(): View
    {
        return view('backend.ads.admin.reports.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $reports = AdReport::query()
            ->with(['ad:id,title', 'reporter:id,name,full_name'])
            ->select(['id', 'user_ad_id', 'reported_by', 'reason', 'created_at'])
            ->latest();

        return DataTables::of($reports)
            ->addColumn('ad_title', fn (AdReport $report): string => $report->ad?->title ?? 'Deleted ad')
            ->addColumn('reporter_name', fn (AdReport $report): string => $report->reporter?->full_name ?? $report->reporter?->name ?? 'Guest')
            ->editColumn('created_at', fn (AdReport $report): string => $report->created_at?->format('d M Y H:i') ?? '-')
            ->addColumn('actions', function (AdReport $report): string {
                if (! $report->ad) {
                    return '-';
                }

                $csrf = csrf_field();
                $method = method_field('DELETE');
                $action = route('admin.ads.reports.delete-ad', $report->ad);

                return '<form method="POST" action="'.$action.'" onsubmit="return confirm(\'Delete this ad?\')" class="d-inline">'
                    .$csrf
                    .$method
                    .'<button type="submit" class="btn btn-sm btn-danger">Delete Ad</button>'
                    .'</form>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function deleteAd(UserAd $ad): RedirectResponse
    {
        $ad->delete();

        return back()->with('success', 'Ad deleted successfully.');
    }
}
