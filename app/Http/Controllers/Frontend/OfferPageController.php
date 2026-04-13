<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class OfferPageController extends Controller
{
    public function home(): View
    {
        $offers = $this->baseOfferQuery()
            ->limit(10)
            ->get();

        return view('frontend.index', [
            'offers' => $offers,
        ]);
    }

    public function index(): View
    {
        $offers = $this->baseOfferQuery()->paginate(12);

        return view('frontend.offers.index', [
            'offers' => $offers,
        ]);
    }

    public function show(Offer $offer): View
    {
        abort_unless($this->isPublished($offer), 404);

        return view('frontend.offers.show', [
            'offer' => $offer,
        ]);
    }

    private function baseOfferQuery(): Builder
    {
        $today = now()->toDateString();

        return Offer::query()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', $today);
            })
            ->orderByRaw('CASE WHEN valid_until = ? THEN 0 ELSE 1 END', [$today])
            ->orderByRaw('CASE WHEN valid_until IS NULL THEN 1 ELSE 0 END')
            ->orderBy('valid_until')
            ->latest('id');
    }

    private function isPublished(Offer $offer): bool
    {
        if ($offer->status !== 'active') {
            return false;
        }

        return $offer->valid_until === null || $offer->valid_until->isToday() || $offer->valid_until->isFuture();
    }
}
