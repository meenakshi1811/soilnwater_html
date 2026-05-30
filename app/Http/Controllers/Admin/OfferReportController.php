<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class OfferReportController extends Controller
{
    public function index(): View
    {
        return view('backend.offers.admin.reports.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $reports = OfferReport::query()
            ->with(['offer:id,title', 'reporter:id,name,full_name'])
            ->select(['id', 'offer_id', 'reported_by', 'reason', 'created_at'])
            ->latest();

        return DataTables::of($reports)
            ->addColumn('offer_title', fn (OfferReport $report): string => $report->offer?->title ?? 'Deleted offer')
            ->addColumn('reporter_name', fn (OfferReport $report): string => $report->reporter?->full_name ?? $report->reporter?->name ?? 'Guest')
            ->editColumn('created_at', fn (OfferReport $report): string => $report->created_at?->format('d M Y H:i') ?? '-')
            ->addColumn('actions', function (OfferReport $report): string {
                if (! $report->offer) {
                    return '-';
                }

                $csrf = csrf_field();
                $method = method_field('DELETE');
                $action = route('admin.offers.reports.delete-offer', $report->offer);

                return '<form method="POST" action="'.$action.'" onsubmit="return confirm(\'Delete this offer?\')" class="d-inline">'
                    .$csrf
                    .$method
                    .'<button type="submit" class="btn btn-sm btn-danger">Delete Offer</button>'
                    .'</form>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function deleteOffer(Offer $offer): RedirectResponse
    {
        $offer->delete();

        return back()->with('success', 'Offer deleted successfully.');
    }
}
