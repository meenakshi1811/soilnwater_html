<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdReport;
use App\Models\UserAd;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdReportController extends Controller
{
    public function index(): View
    {
        $reports = AdReport::query()
            ->with(['ad:id,title,status,final_image', 'reporter:id,name,full_name'])
            ->latest()
            ->paginate(20);

        return view('backend.ads.admin.reports.index', compact('reports'));
    }

    public function deleteAd(UserAd $ad): RedirectResponse
    {
        $ad->delete();

        return back()->with('success', 'Ad deleted successfully.');
    }
}
