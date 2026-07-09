<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Concerns\ValidatesVendorProductRequest;
use App\Http\Controllers\Controller;
use App\Services\PortalNotificationService;
use App\Models\Category;
use App\Models\VendorProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VendorProductController extends Controller
{
    use ValidatesVendorProductRequest;

    public function index(Request $request)
    {
        $vendorId = auth()->user()->vendor->id;
        $q = trim((string) $request->query('q'));

        $products = VendorProduct::query()->with(['category:id,name', 'subcategory:id,name', 'childCategory:id,name'])
            ->where('vendor_id', $vendorId)
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%")
            ))
            ->latest()->get();

        return view('backend.vendor.products.index', compact('products', 'q'));
    }

    public function create()
    {
        return view('backend.vendor.products.form', ['product' => new VendorProduct(), 'categories' => $this->vendorCategories()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['vendor_id'] = auth()->user()->vendor->id;
        $data['sku'] = $data['sku'] ?: 'SKU-'.Str::upper(Str::random(8));
        $product = VendorProduct::create($data);

        PortalNotificationService::notifyAdminsOfApprovalRequest('Vendor product', $product->name, route('admin.vendor-products.show', $product));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Product submitted successfully and sent for admin approval.',
                'redirect' => route('vendor.products.index'),
            ]);
        }

        return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');
    }

    public function show(VendorProduct $product)
    {
        abort_unless($product->vendor_id === auth()->user()->vendor?->id, 403);
        $product->load(['category:id,name', 'subcategory:id,name', 'childCategory:id,name']);
        return view('backend.vendor.products.show', compact('product'));
    }

    public function edit(VendorProduct $product)
    {
        abort_unless($product->vendor_id === auth()->user()->vendor?->id, 403);
        return view('backend.vendor.products.form', compact('product') + ['categories' => $this->vendorCategories()]);
    }

    public function update(Request $request, VendorProduct $product)
    {
        abort_unless($product->vendor_id === auth()->user()->vendor?->id, 403);
        $product->update($this->validated($request, false, $product));

        PortalNotificationService::notifyAdminsOfApprovalRequest('Updated vendor product', $product->name, route('admin.vendor-products.show', $product));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Product updated successfully and sent for admin approval.',
                'redirect' => route('vendor.products.index'),
            ]);
        }

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(VendorProduct $product)
    {
        abort_unless($product->vendor_id === auth()->user()->vendor?->id, 403);
        $product->delete();
        return response()->json(['ok' => true]);
    }

    public function subcategories(Category $category): JsonResponse
    {
        $subcategories = Category::query()->where('parent_id', $category->id)->orderBy('name')->get(['id', 'name']);
        return response()->json(['subcategories' => $subcategories]);
    }

    private function validated(Request $request, bool $requireTerms = true, ?VendorProduct $product = null): array
    {
        if ($requireTerms) {
            $request->validate(['accept_terms' => ['accepted']]);
        }

        return $this->validatedVendorProduct($request, $product);
    }
}
