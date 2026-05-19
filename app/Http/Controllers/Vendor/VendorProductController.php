<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VendorProductController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = auth()->user()->vendor->id;
        $q = trim((string) $request->query('q'));

        $products = VendorProduct::query()
            ->where('vendor_id', $vendorId)
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%")
                ->orWhere('category', 'like', "%{$q}%")
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.vendor.products.index', compact('products', 'q'));
    }

    public function create()
    {
        return view('backend.vendor.products.form', ['product' => new VendorProduct()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['vendor_id'] = auth()->user()->vendor->id;
        $data['sku'] = $data['sku'] ?: 'SKU-'.Str::upper(Str::random(8));
        VendorProduct::create($data);

        return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');
    }

    public function show(VendorProduct $product)
    {
        abort_unless($product->vendor_id === auth()->user()->vendor?->id, 403);
        return view('backend.vendor.products.show', compact('product'));
    }

    public function edit(VendorProduct $product)
    {
        abort_unless($product->vendor_id === auth()->user()->vendor?->id, 403);
        return view('backend.vendor.products.form', compact('product'));
    }

    public function update(Request $request, VendorProduct $product)
    {
        abort_unless($product->vendor_id === auth()->user()->vendor?->id, 403);
        $product->update($this->validated($request));

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(VendorProduct $product)
    {
        abort_unless($product->vendor_id === auth()->user()->vendor?->id, 403);
        $product->delete();

        return response()->json(['ok' => true]);
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:150'],
            'colors' => ['nullable', 'string', 'max:200'],
            'sizes' => ['nullable', 'string', 'max:200'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'final_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'shipping_charges' => ['required', 'numeric', 'min:0'],
            'spec_feature.*' => ['nullable', 'string', 'max:100'],
            'spec_value.*' => ['nullable', 'string', 'max:255'],
            'bulk_min.*' => ['nullable', 'integer', 'min:1'],
            'bulk_price.*' => ['nullable', 'numeric', 'min:0'],
            'images.*' => ['nullable', 'image', 'max:4096'],
            'video_file' => ['nullable', 'mimetypes:video/mp4,video/webm', 'max:20480'],
            'youtube_link' => ['nullable', 'url'],
            'is_online_sale' => ['nullable', 'boolean'],
        ]);

        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;
        $validated['specs'] = collect($request->input('spec_feature', []))
            ->map(fn ($feature, $idx) => ['feature' => $feature, 'value' => $request->input('spec_value.'.$idx)])
            ->filter(fn ($row) => !empty($row['feature']) && !empty($row['value']))
            ->values()->all();
        $validated['bulk_tiers'] = collect($request->input('bulk_min', []))
            ->map(fn ($min, $idx) => ['buy_min' => (int) $min, 'price' => (float) $request->input('bulk_price.'.$idx)])
            ->filter(fn ($row) => $row['buy_min'] > 0 && $row['price'] > 0)
            ->values()->all();

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $images[] = $file->store('vendor-products/images', 'public');
            }
        }
        $validated['images'] = $images;

        if ($request->hasFile('video_file')) {
            $validated['video_file'] = $request->file('video_file')->store('vendor-products/videos', 'public');
        }

        $validated['is_online_sale'] = (bool) $request->boolean('is_online_sale');

        return $validated;
    }
}
