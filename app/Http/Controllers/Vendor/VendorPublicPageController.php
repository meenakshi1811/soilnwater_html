<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\VendorBannerSlide;
use App\Models\Vendor;
use App\Models\VendorPageSection;
use App\Models\VendorProduct;
use App\Support\VendorFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class VendorPublicPageController extends Controller
{
    public function edit(): View
    {
        $vendor = auth()->user()->vendor;
        $this->normalizeVendorSectionContentImages($vendor);
        $vendor->load(['bannerSlides', 'pageSections']);

        return view('backend.vendor.public-page.edit', compact('vendor'));
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $vendor = auth()->user()->vendor;

        if ($vendor->public_page_status === 'approved' && ! is_array($vendor->published_page_data)) {
            $vendor->update(['published_page_data' => $vendor->publicPageSnapshot()]);
        }

        $validated = $request->validate([
            'hero_main_heading' => ['nullable', 'string', $this->maxWordsRule('main heading', 500)],
            'hero_sub_heading' => ['nullable', 'string'],
            'hero_sub_heading_encoded' => ['nullable', 'string'],
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
            'description' => ['nullable', 'string'],
            'description_encoded' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'banner_slides' => ['nullable', 'array'],
            'banner_slides.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.title' => ['nullable', 'string', 'max:2000'],
            'sections.*.title_encoded' => ['nullable', 'string'],
            'sections.*.content' => ['nullable', 'string'],
            'sections.*.content_encoded' => ['nullable', 'string'],
            'sections.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sections.*.content_images' => ['nullable', 'array'],
            'sections.*.content_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'sections.*.video_file' => ['nullable', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'],
            'sections.*.youtube_url' => ['nullable', 'url', 'max:1000'],
            'sections.*._delete' => ['nullable', 'boolean'],
            'submission_action' => ['required', 'in:draft,submit'],
        ]);

        if (array_key_exists('hero_sub_heading_encoded', $validated)) {
            $validated['hero_sub_heading'] = $this->decodedField((string) $validated['hero_sub_heading_encoded']);
        }

        if (array_key_exists('description_encoded', $validated)) {
            $validated['description'] = $this->decodedField((string) $validated['description_encoded']);
        }

        if ($request->hasFile('logo')) {
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
            'description',
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

        $vendor = $vendor->fresh()->load(['bannerSlides', 'pageSections']);
        $isSubmission = $validated['submission_action'] === 'submit';
        $vendor->update([
            'public_page_status' => $isSubmission ? 'pending' : 'draft',
            'pending_page_data' => $isSubmission ? $vendor->publicPageSnapshot() : null,
            'public_page_submitted_at' => $isSubmission ? now() : null,
        ]);

        $message = $isSubmission
            ? 'Public page saved and sent to admin for approval.'
            : 'Draft saved. Your changes are available only in Live Preview.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'public_page_status' => $vendor->public_page_status,
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

        return redirect()->route('vendor.public-page.edit')->with('success', $message);
    }

    public function deleteBannerSlide(VendorBannerSlide $slide): JsonResponse
    {
        abort_unless($slide->vendor_id === auth()->user()->vendor?->id, 403);
        $slide->delete();

        return response()->json(['message' => 'Banner slide removed.']);
    }

    public function preview(): View
    {
        $vendor = auth()->user()->vendor;
        $this->normalizeVendorSectionContentImages($vendor);
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
            'vendorRecentAds' => collect(),
            'selectedCategoryNamesByVendorAdId' => [],
        ]);
    }

    private function syncSections($vendor, array $sections, Request $request): void
    {
        $sort = 0;
        foreach ($sections as $index => $sectionData) {
            if (! empty($sectionData['_delete']) && ! empty($sectionData['id'])) {
                $section = VendorPageSection::where('vendor_id', $vendor->id)->find($sectionData['id']);
                if ($section) {
                    $section->delete();
                }

                continue;
            }

            $title = trim((string) $this->decodedSectionField($sectionData, 'title'));
            $plainTitle = trim(strip_tags($title));
            $content = (string) $this->decodedSectionField($sectionData, 'content');
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
                $section->image_path = VendorFileUploader::storeImage($imageFile, 'sections');
            }

            $videoFile = $request->file("sections.{$index}.video_file");
            if ($videoFile) {
                $directory = public_path('uploads/vendors/sections/videos');
                if (! File::isDirectory($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }
                $filename = uniqid('section-video-', true).'.'.$videoFile->getClientOriginalExtension();
                $videoFile->move($directory, $filename);
                $content .= '<div class="vendor-section-video mt-3"><video controls preload="metadata"><source src="'.asset('uploads/vendors/sections/videos/'.$filename).'"></video></div>';
            } elseif (! empty($sectionData['youtube_url'])) {
                $youtubeUrl = e((string) $sectionData['youtube_url']);
                $content .= '<div class="vendor-section-video mt-3"><div class="ratio ratio-16x9"><iframe src="'.$youtubeUrl.'" title="Section video" allowfullscreen loading="lazy"></iframe></div></div>';
            }

            $content = $this->replaceUploadedContentImages(
                (string) $content,
                $request->file("sections.{$index}.content_images", []),
                (string) $index
            );

            $section->fill([
                'title' => $plainTitle !== '' ? $title : 'Section',
                'content' => $this->storeEmbeddedContentImages((string) $content),
                'sort_order' => $sort++,
            ]);
            $section->vendor_id = $vendor->id;
            $section->save();
        }
    }


    private function decodedField(string $value): string
    {
        $decoded = base64_decode($value, true);

        return $decoded !== false ? $decoded : '';
    }

    private function decodedSectionField(array $sectionData, string $field): string
    {
        $encodedKey = $field.'_encoded';
        if (array_key_exists($encodedKey, $sectionData) && $sectionData[$encodedKey] !== null) {
            $decoded = $this->decodedField((string) $sectionData[$encodedKey]);

            if ($decoded !== '') {
                return $decoded;
            }
        }

        return (string) ($sectionData[$field] ?? '');
    }


    private function replaceUploadedContentImages(string $html, array $files, string $sectionIndex): string
    {
        foreach ($files as $imageIndex => $file) {
            if (! $file) {
                continue;
            }

            $token = '__section_content_image_'.$sectionIndex.'_'.((string) $imageIndex).'__';
            if (! str_contains($html, $token)) {
                continue;
            }

            $path = VendorFileUploader::storeImage($file, 'sections/content-images');
            $html = str_replace($token, asset($path), $html);
        }

        return $html;
    }

    private function storeEmbeddedContentImages(string $html): string
    {
        if (! str_contains($html, 'data:image/')) {
            return $html;
        }

        $directory = public_path('uploads/vendors/sections/content-images');
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return (string) preg_replace_callback(
            '/src\s*=\s*(["\'])data:image\/(png|jpeg|jpg|webp|gif);base64,([^"\']+)\1/i',
            function (array $matches) use ($directory) {
                $mimeExtension = strtolower($matches[2]) === 'jpeg' ? 'jpg' : strtolower($matches[2]);
                $decoded = base64_decode($matches[3], true);

                if ($decoded === false) {
                    return $matches[0];
                }

                $filename = sha1($decoded).'.'.$mimeExtension;
                $absolutePath = $directory.'/'.$filename;
                if (! File::exists($absolutePath)) {
                    File::put($absolutePath, $decoded);
                }

                $relativePath = 'uploads/vendors/sections/content-images/'.$filename;

                return 'src='.$matches[1].asset($relativePath).$matches[1];
            },
            $html
        );
    }

    private function normalizeVendorSectionContentImages(Vendor $vendor): void
    {
        $sections = VendorPageSection::query()
            ->where('vendor_id', $vendor->id)
            ->where('content', 'like', '%data:image/%')
            ->get(['id', 'content']);

        foreach ($sections as $section) {
            $normalized = $this->storeEmbeddedContentImages((string) $section->content);
            if ($normalized === (string) $section->content) {
                continue;
            }

            $section->forceFill(['content' => $normalized])->saveQuietly();
        }
    }

    private function deleteOrphanManagedMedia(string $oldContent, string $newContent): void
    {
        $oldPaths = $this->extractManagedPaths($oldContent);
        $newPaths = $this->extractManagedPaths($newContent);
        foreach (array_diff($oldPaths, $newPaths) as $relativePath) {
            VendorFileUploader::deleteIfExists($relativePath);
        }
    }

    private function deleteManagedMediaFromContent(string $content): void
    {
        foreach ($this->extractManagedPaths($content) as $relativePath) {
            VendorFileUploader::deleteIfExists($relativePath);
        }
    }

    private function maxWordsRule(string $label, int $limit): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($label, $limit): void {
            $plainText = trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string) $value))));
            preg_match_all('/\S+/u', $plainText, $words);
            $wordCount = count($words[0] ?? []);

            if ($wordCount > $limit) {
                $fail('The hero '.$label.' may not be greater than '.$limit.' words.');
            }
        };
    }

    private function extractManagedPaths(string $html): array
    {
        preg_match_all('#uploads/vendors/sections/(?:content-images|videos)/[^"\'\s<]+#', $html, $matches);
        return array_values(array_unique($matches[0] ?? []));
    }
}
