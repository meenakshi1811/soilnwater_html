<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorBannerSlide;
use App\Models\VendorPageSection;
use App\Support\VendorFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorPublicPageController extends Controller
{
    public function edit(): View
    {
        $vendor = auth()->user()->vendor;
        $vendor->load(['bannerSlides', 'pageSections']);

        return view('backend.vendor.public-page.edit', compact('vendor'));
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $vendor = auth()->user()->vendor;

        $validated = $request->validate([
            'hero_main_heading' => ['nullable', 'string', 'max:255'],
            'hero_sub_heading' => ['nullable', 'string', 'max:500'],
            'hero_main_style' => ['nullable', 'array'],
            'hero_main_style.*' => ['nullable', 'string', 'max:255'],
            'hero_sub_style' => ['nullable', 'array'],
            'hero_sub_style.*' => ['nullable', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:vendors,slug,'.$vendor->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'banner_slides' => ['nullable', 'array'],
            'banner_slides.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.title' => ['nullable', 'string', 'max:255'],
            'sections.*.content' => ['nullable', 'string', 'max:20000'],
            'sections.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sections.*._delete' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('logo')) {
            VendorFileUploader::deleteIfExists($vendor->logo);
            $validated['logo'] = VendorFileUploader::storeImage($request->file('logo'), 'logos');
        }

        $updateData = collect($validated)->only([
            'hero_main_heading',
            'hero_sub_heading',
            'hero_main_style',
            'hero_sub_style',
            'display_name',
            'slug',
            'phone',
            'email',
            'city',
            'address',
            'facebook_url',
            'instagram_url',
        ])->toArray();

        if (isset($validated['logo'])) {
            $updateData['logo'] = $validated['logo'];
        }

        $vendor->update($updateData);

        if ($request->hasFile('banner_slides')) {
            $sort = (int) $vendor->bannerSlides()->max('sort_order');
            foreach ($request->file('banner_slides') as $file) {
                $sort++;
                $vendor->bannerSlides()->create([
                    'image_path' => VendorFileUploader::storeImage($file, 'banners'),
                    'sort_order' => $sort,
                ]);
            }
        }

        $this->syncSections($vendor, $request->input('sections', []), $request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Public page saved successfully.',
                'preview_url' => route('store.show', $vendor->fresh()->slug),
            ]);
        }

        return redirect()->route('vendor.public-page.edit')->with('success', 'Public page saved successfully.');
    }

    public function deleteBannerSlide(VendorBannerSlide $slide): JsonResponse
    {
        abort_unless($slide->vendor_id === auth()->user()->vendor?->id, 403);
        VendorFileUploader::deleteIfExists($slide->image_path);
        $slide->delete();

        return response()->json(['message' => 'Banner slide removed.']);
    }

    public function preview(): View
    {
        $vendor = auth()->user()->vendor;
        $vendor->load(['bannerSlides', 'pageSections']);

        return view('frontend.store.show', [
            'vendor' => $vendor,
            'preview' => true,
            'dummyProducts' => $this->dummyProducts(),
        ]);
    }

    public static function dummyProducts(): array
    {
        return [
            ['name' => 'Submersible Pump 1HP', 'price' => '₹8,500 – ₹12,000', 'moq' => 'Min. 5 pcs', 'image' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=400&h=300&fit=crop'],
            ['name' => 'Centrifugal Water Pump', 'price' => '₹15,000 – ₹28,000', 'moq' => 'Min. 2 pcs', 'image' => 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=400&h=300&fit=crop'],
            ['name' => 'Solar Panel 330W', 'price' => '₹9,200 – ₹11,500', 'moq' => 'Min. 10 pcs', 'image' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=400&h=300&fit=crop'],
            ['name' => 'HDPE Irrigation Pipe', 'price' => '₹45 – ₹120 / meter', 'moq' => 'Min. 100 m', 'image' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=400&h=300&fit=crop'],
            ['name' => 'Drip Irrigation Kit', 'price' => '₹2,800 – ₹6,500', 'moq' => 'Min. 20 sets', 'image' => 'https://images.unsplash.com/photo-1464226184884-fa80b87dee3f?w=400&h=300&fit=crop'],
            ['name' => 'Pressure Controller Valve', 'price' => '₹1,200 – ₹3,400', 'moq' => 'Min. 50 pcs', 'image' => 'https://images.unsplash.com/photo-1585771724684-38269b6632f5?w=400&h=300&fit=crop'],
            ['name' => 'Borewell Motor 3HP', 'price' => '₹18,000 – ₹32,000', 'moq' => 'Min. 3 pcs', 'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?w=400&h=300&fit=crop'],
            ['name' => 'Water Storage Tank 1000L', 'price' => '₹5,500 – ₹8,200', 'moq' => 'Min. 10 pcs', 'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=300&fit=crop'],
        ];
    }

    private function syncSections($vendor, array $sections, Request $request): void
    {
        $sort = 0;
        foreach ($sections as $index => $sectionData) {
            if (! empty($sectionData['_delete']) && ! empty($sectionData['id'])) {
                $section = VendorPageSection::where('vendor_id', $vendor->id)->find($sectionData['id']);
                if ($section) {
                    VendorFileUploader::deleteIfExists($section->image_path);
                    $section->delete();
                }

                continue;
            }

            $title = trim((string) ($sectionData['title'] ?? ''));
            $content = $sectionData['content'] ?? '';
            if ($title === '' && $content === '' && empty($sectionData['id'])) {
                continue;
            }

            $section = ! empty($sectionData['id'])
                ? VendorPageSection::where('vendor_id', $vendor->id)->find($sectionData['id'])
                : null;

            if (! $section) {
                $section = new VendorPageSection(['vendor_id' => $vendor->id]);
            }

            $imageFile = $request->file("sections.{$index}.image");
            if ($imageFile) {
                VendorFileUploader::deleteIfExists($section->image_path);
                $section->image_path = VendorFileUploader::storeImage($imageFile, 'sections');
            }

            $section->fill([
                'title' => $title ?: 'Section',
                'content' => $content,
                'sort_order' => $sort++,
            ]);
            $section->vendor_id = $vendor->id;
            $section->save();
        }
    }
}
