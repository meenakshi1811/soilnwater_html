<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

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

    public function offersIndex()
    {
        return view('backend.post-offers.my-offers');
    }

    public function offersData(Request $request): JsonResponse
    {
        $offers = Offer::query()
            ->with(['category:id,name', 'subcategory:id,name'])
            ->where('user_id', $request->user()->id)
            ->latest();

        return DataTables::of($offers)
            ->addColumn('category_name', fn (Offer $offer) => $offer->category?->name ?? '-')
            ->addColumn('subcategory_name', fn (Offer $offer) => $offer->subcategory?->name ?? '-')
            ->addColumn('status_badge', function (Offer $offer) {
                $class = $offer->status === 'active' ? 'success' : 'secondary';
                $label = ucfirst($offer->status);

                return '<span class="badge bg-'.$class.'">'.$label.'</span>';
            })
            ->editColumn('valid_until', fn (Offer $offer) => $offer->valid_until?->format('Y-m-d') ?? '-')
            ->editColumn('created_at', fn (Offer $offer) => $offer->created_at?->format('Y-m-d H:i'))
            ->addColumn('actions', function (Offer $offer) {
                return '<div class="d-flex justify-content-end gap-2">'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-offer" data-id="'.$offer->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-offer" data-id="'.$offer->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(Offer $offer): JsonResponse
    {
        abort_unless((int) $offer->user_id === (int) auth()->id(), 403);

        return response()->json(['offer' => $offer]);
    }

    public function update(Request $request, Offer $offer): JsonResponse
    {
        abort_unless((int) $offer->user_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'discount_tag'      => 'required|string|max:255',
            'coupon_code'       => 'nullable|string|max:50',
            'valid_until'       => 'nullable|date|after_or_equal:today',
            'short_description' => 'nullable|string|max:300',
            'status'            => ['required', Rule::in(['active', 'inactive'])],
            'banner_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('banner_image')) {
            if ($offer->banner_image) {
                Storage::disk('public')->delete($offer->banner_image);
            }

            $validated['banner_image'] = $request->file('banner_image')->store('offers/banners', 'public');
        }

        $offer->update($validated);

        return response()->json(['message' => 'Offer updated successfully.']);
    }

    public function destroy(Request $request, Offer $offer): JsonResponse
    {
        abort_unless((int) $offer->user_id === (int) $request->user()->id, 403);

        if ($offer->banner_image) {
            Storage::disk('public')->delete($offer->banner_image);
        }

        $offer->delete();

        return response()->json(['message' => 'Offer deleted successfully.']);
    }
}
