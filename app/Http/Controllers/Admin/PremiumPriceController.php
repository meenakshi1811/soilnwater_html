<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PremiumPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PremiumPriceController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.premium-prices.index', [
            'prices' => PremiumPrice::ensureDefaults(),
        ]);
    }

    public function update(Request $request, PremiumPrice $premiumPrice): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $premiumPrice->update([
            'amount' => number_format((float) $validated['amount'], 2, '.', ''),
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json([
            'message' => PremiumPrice::typeMeta($premiumPrice->profile_type)['singular'].' premium price updated successfully.',
            'price' => [
                'id' => $premiumPrice->id,
                'profile_type' => $premiumPrice->profile_type,
                'amount' => number_format((float) $premiumPrice->amount, 2, '.', ''),
                'formatted_amount' => $premiumPrice->formatted_amount,
                'is_active' => (bool) $premiumPrice->is_active,
            ],
        ]);
    }
}
