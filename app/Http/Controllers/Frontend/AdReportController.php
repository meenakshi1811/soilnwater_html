<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AdReport;
use App\Models\UserAd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdReportController extends Controller
{
    public function store(Request $request, UserAd $ad): RedirectResponse
    {
        abort_unless($ad->status === 'approved', 404);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        AdReport::create([
            'user_ad_id' => $ad->id,
            'reported_by' => $request->user()?->id,
            'reason' => $validated['reason'],
        ]);

        return back()->with('success', 'Ad reported successfully.');
    }
}
