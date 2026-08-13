<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Category;
use App\Models\VendorProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait ValidatesVendorProductRequest
{
    protected function vendorCategories()
    {
        return Category::query()->whereNull('parent_id')->whereJsonContains('modules', 'vendors')
            ->with(['children' => fn ($q) => $q->orderBy('name')->select(['id', 'name', 'parent_id'])->with([
                'children' => fn ($childQuery) => $childQuery->orderBy('name')->select(['id', 'name', 'parent_id']),
            ])])
            ->orderBy('name')->get(['id', 'name']);
    }

    protected function validatedVendorProduct(Request $request, ?VendorProduct $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['required', \Illuminate\Validation\Rule::exists('categories', 'id')->where(fn ($q) => $q->where('parent_id', $request->input('category_id')))],
            'child_category_id' => ['nullable', \Illuminate\Validation\Rule::exists('categories', 'id')->where(fn ($q) => $q->where('parent_id', $request->input('subcategory_id')))],
            'colors' => ['nullable', 'string', 'max:200'],
            'sizes' => ['nullable', 'string', 'max:200'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'final_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'shipping_charges' => ['required', 'numeric', 'min:0'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'bulk_min.*' => ['nullable', 'integer', 'min:1'],
            'bulk_price.*' => ['nullable', 'numeric', 'min:0'],
            'spec_feature.*' => ['nullable', 'string', 'max:100'],
            'spec_value.*' => ['nullable', 'string', 'max:255'],
            'images.*' => ['nullable', 'image', 'max:4096'],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['nullable', 'string', 'max:500'],
            'remove_video' => ['nullable', 'boolean'],
            'video_file' => ['nullable', 'mimetypes:video/mp4,video/webm', 'max:20480'],
            'youtube_link' => ['nullable', 'url'],
            'is_online_sale' => ['nullable', 'boolean'],
            'accept_terms' => ['nullable', 'accepted'],
        ], [
            'location.required' => 'This account does not have a registered address. Update the user profile first.',
            'latitude.required' => 'This account does not have saved coordinates. Update the user address first.',
            'longitude.required' => 'This account does not have saved coordinates. Update the user address first.',
        ]);
        $validated['category'] = Category::find($validated['category_id'])?->name;
        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;
        $validated['specs'] = collect($request->input('spec_feature', []))
            ->map(fn ($feature, $idx) => ['feature' => trim((string) $feature), 'value' => trim((string) $request->input('spec_value.'.$idx))])
            ->filter(fn ($row) => $row['feature'] !== '' || $row['value'] !== '')
            ->values()->all();
        $validated['bulk_tiers'] = collect($request->input('bulk_min', []))
            ->map(fn ($min, $idx) => ['buy_min' => (int) $min, 'price' => (float) $request->input('bulk_price.'.$idx)])
            ->filter(fn ($row) => $row['buy_min'] > 0 && $row['price'] > 0)
            ->values()->all();

        $keptImages = collect();
        if ($product) {
            $allowedImages = collect($product->images ?? [])->filter()->values();
            $requestedImages = collect($request->input('existing_images', []))
                ->filter(fn ($path) => is_string($path) && $path !== '')
                ->values();
            $keptImages = $requestedImages
                ->filter(fn ($path) => $allowedImages->contains($path))
                ->values();

            $allowedImages->diff($keptImages)->each(fn ($path) => $this->deleteVendorProductMediaFile($path));
        }

        if ($request->hasFile('images')) {
            $imageDirectory = public_path('uploads/vendor-products/images');
            if (! File::exists($imageDirectory)) {
                File::makeDirectory($imageDirectory, 0755, true);
            }
            foreach ($request->file('images') as $file) {
                $filename = time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
                $file->move($imageDirectory, $filename);
                $keptImages->push('uploads/vendor-products/images/'.$filename);
            }
        }

        $validated['images'] = $keptImages->values()->all();

        if ($request->hasFile('video_file')) {
            if ($product?->video_file) {
                $this->deleteVendorProductMediaFile($product->video_file);
            }
            $videoDirectory = public_path('uploads/vendor-products/videos');
            if (! File::exists($videoDirectory)) {
                File::makeDirectory($videoDirectory, 0755, true);
            }
            $videoFile = $request->file('video_file');
            $videoFilename = time().'_'.Str::random(8).'.'.$videoFile->getClientOriginalExtension();
            $videoFile->move($videoDirectory, $videoFilename);
            $validated['video_file'] = 'uploads/vendor-products/videos/'.$videoFilename;
        } elseif ($product && $request->boolean('remove_video') && $product->video_file) {
            $this->deleteVendorProductMediaFile($product->video_file);
            $validated['video_file'] = null;
        }

        $validated['is_online_sale'] = (bool) $request->boolean('is_online_sale');
        unset($validated['accept_terms'], $validated['existing_images'], $validated['remove_video']);

        $validated['status'] = 'pending';
        $validated['approved_at'] = null;
        $validated['approved_by'] = null;

        return $validated;
    }

    protected function deleteVendorProductMediaFile(?string $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        $normalized = str_replace('\\', '/', $path);
        if (! str_starts_with($normalized, 'uploads/vendor-products/')) {
            return;
        }

        $fullPath = public_path($normalized);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
