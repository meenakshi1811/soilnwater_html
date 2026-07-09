<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\OfferPriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferPriceController extends Controller
{
    public function index(): View
    {
        $categories = OfferPriceService::offerCategoryTree();

        return view('backend.admin.offer-prices.index', [
            'categories' => $categories,
            'pricedCount' => $categories->sum(fn (Category $category) => 1 + OfferPriceService::countDescendants($category)),
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        abort_unless(OfferPriceService::isOfferPricingCategory($category), 404);

        $validated = $request->validate([
            'offer_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $amount = number_format((float) $validated['offer_price'], 2, '.', '');
        $category->update(['offer_price' => $amount]);

        return response()->json([
            'message' => ($category->parent_id ? 'Subcategory' : 'Category').' offer price updated successfully.',
            'category' => [
                'id' => $category->id,
                'offer_price' => $amount,
                'formatted_price' => OfferPriceService::formatAmount($amount),
                'is_free' => (float) $amount <= 0,
            ],
        ]);
    }

    public function applyToAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'offer_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $amount = number_format((float) $validated['offer_price'], 2, '.', '');
        $updated = OfferPriceService::applyToAll((float) $amount);

        if ($updated === 0) {
            return response()->json([
                'message' => 'No offer categories found. Enable the Offers module on categories first.',
            ], 422);
        }

        return response()->json([
            'message' => 'Offer price applied to all '.$updated.' offer categories.',
            'offer_price' => $amount,
            'formatted_price' => OfferPriceService::formatAmount($amount),
            'updated_count' => $updated,
        ]);
    }
}
