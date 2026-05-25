<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\VendorBannerSlide;
use App\Models\VendorPageSection;
use App\Models\VendorProduct;
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
            'sections.*.title' => ['nullable', 'string', 'max:2000'],
            'sections.*.content' => ['nullable', 'string'],
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
            $vendor = $vendor->fresh()->load(['bannerSlides', 'pageSections']);

            return response()->json([
                'message' => 'Public page saved successfully.',
                'preview_url' => route('vendor.public-page.preview'),
                'logo_url' => $vendor->logo ? asset($vendor->logo) : null,
                'sections' => $vendor->pageSections->map(fn ($section) => [
                    'id' => $section->id,
                    'title' => $section->title,
                    'content' => $section->content,
                    'image_url' => $section->image_path ? asset($section->image_path) : null,
                ])->values(),
                'banner_slides' => $vendor->bannerSlides->map(fn ($slide) => [
                    'id' => $slide->id,
                    'image_url' => asset($slide->image_path),
                ])->values(),
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

        $products = VendorProduct::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved')
            ->orderByDesc('updated_at')
            ->get();

        $vendorCategories = Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'vendors')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('frontend.store.show', [
            'vendor' => $vendor,
            'preview' => true,
            'activeNav' => 'home',
            'featuredProducts' => $products->take(4),
            'vendorCategories' => $vendorCategories,
            'sectionAdRails' => [],
        ]);
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
            $plainTitle = trim(strip_tags($title));
            $content = $sectionData['content'] ?? '';
            if ($plainTitle === '' && trim(strip_tags((string) $content)) === '' && empty($sectionData['id'])) {
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
                'title' => $plainTitle !== '' ? $title : 'Section',
                'content' => $content,
                'sort_order' => $sort++,
            ]);
            $section->vendor_id = $vendor->id;
            $section->save();
        }
    }
}
