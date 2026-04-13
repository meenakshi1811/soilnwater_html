<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ModulePermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Offer;
class PostOfferController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('backend.post-offers.index', compact('categories'));
    }

    public function subcategories(Category $category)
    {
        return response()->json(
            $category->children()->orderBy('name')->get(['id', 'name'])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'discount_tag'      => 'required|string|max:255',
            'coupon_code'       => 'nullable|string|max:50',
            'valid_until'       => 'nullable|date|after_or_equal:today',
            'category_id'       => ['nullable', Rule::exists('categories', 'id')],
            'subcategory_id'    => ['nullable', Rule::exists('categories', 'id')],
            'banner_image'      => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'short_description' => 'nullable|string|max:300',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')
                ->store('offers/banners', 'public');
        }

        // Attach the authenticated user
        $validated['user_id'] = auth()->id();

        Offer::create($validated);

        return response()->json(['message' => 'Offer posted successfully!']);
    }
    
}
