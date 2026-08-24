<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FrontendSearchController extends Controller
{
    private const SEARCH_MODULES = [
        'offers' => 'frontend.offers.index',
        'ads' => 'frontend.ads.index',
        'vendors' => 'frontend.vendors.index',
        'consultants' => 'frontend.consultants.index',
        'services' => 'frontend.service_providers.index',
        'community' => 'community.index',
    ];

    public function index(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'module' => ['nullable', 'string'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $module = $validated['module'] ?? 'offers';
        if (! array_key_exists($module, self::SEARCH_MODULES)) {
            $module = 'offers';
        }

        return redirect()->route(self::SEARCH_MODULES[$module], array_filter([
            'search' => trim((string) ($validated['q'] ?? '')),
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
        ], fn ($value) => $value !== null && $value !== ''));
    }
}
