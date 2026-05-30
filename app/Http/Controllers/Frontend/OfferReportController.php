<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferReport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfferReportController extends Controller
{
    public function store(Request $request, Offer $offer): RedirectResponse
    {
        abort_unless($offer->status === 'active', 404);
        abort_if($offer->valid_until && $offer->valid_until->lt(Carbon::today()), 404);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        OfferReport::create([
            'offer_id' => $offer->id,
            'reported_by' => $request->user()?->id,
            'reason' => $validated['reason'],
        ]);

        return back()->with('success', 'Offer reported successfully.');
    }
}
