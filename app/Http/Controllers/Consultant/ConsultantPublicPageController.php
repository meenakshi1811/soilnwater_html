<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Services\PortalNotificationService;
use App\Models\ConsultantBannerSlide;
use App\Models\Consultant;
use App\Models\ConsultantPageSection;
use App\Models\ConsultantService;
use App\Support\ConsultantFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ConsultantPublicPageController extends Controller
{
    protected function isAdminEditor(): bool
    {
        return false;
    }

    protected function editorConsultant(?Consultant $consultant = null): Consultant
    {
        $resolved = $consultant ?? auth()->user()?->consultant;
        abort_unless($resolved, 404);

        return $resolved;
    }

    protected function editorViewData(Consultant $consultant): array
    {
        return [
            'isAdmin' => false,
            'formAction' => route('consultant.public-page.update'),
            'previewUrl' => route('consultant.public-page.preview'),
            'bannerDeleteBaseUrl' => url('consultant/banner-slides').'/',
            'backUrl' => null,
            'editRedirectRoute' => 'consultant.public-page.edit',
            'editRedirectParams' => [],
        ];
    }

    public function edit(?Consultant $consultant = null): View
    {
        $consultant = $this->editorConsultant($consultant);
        $this->normalizeConsultantSectionContentImages($consultant);
        $consultant->load(['bannerSlides', 'pageSections']);
        $editor = $this->editorViewData($consultant);

        return view('backend.consultant.public-page.edit', array_merge(compact('consultant'), $editor));
    }

    public function update(Request $request, ?Consultant $consultant = null): RedirectResponse|JsonResponse
    {
        $consultant = $this->editorConsultant($consultant);
        $editor = $this->editorViewData($consultant);

        if ($consultant->public_page_status === 'approved' && ! is_array($consultant->published_page_data)) {
            $consultant->update(['published_page_data' => $consultant->publicPageSnapshot()]);
        }

        $submissionActions = $this->isAdminEditor() ? 'draft,publish' : 'draft,submit';

        $validated = $request->validate([
            'hero_main_heading' => ['nullable', 'string', $this->maxWordsRule('main heading', 500)],
            'hero_sub_heading' => ['nullable', 'string'],
            'hero_sub_heading_encoded' => ['nullable', 'string'],
            'hero_main_style' => ['nullable', 'array'],
            'hero_main_style.*' => ['nullable', 'string', 'max:255'],
            'hero_sub_style' => ['nullable', 'array'],
            'hero_sub_style.*' => ['nullable', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:consultants,slug,'.$consultant->id],
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
            'submission_action' => ['required', 'in:'.$submissionActions],
        ]);

        if (array_key_exists('hero_sub_heading_encoded', $validated)) {
            $validated['hero_sub_heading'] = $this->decodedField((string) $validated['hero_sub_heading_encoded']);
        }

        if (array_key_exists('description_encoded', $validated)) {
            $validated['description'] = $this->decodedField((string) $validated['description_encoded']);
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = ConsultantFileUploader::storeImage($request->file('logo'), 'logos');
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

        $consultant->update($updateData);

        if ($request->hasFile('banner_slides')) {
            $sort = (int) $consultant->bannerSlides()->max('sort_order');
            foreach ($request->file('banner_slides') as $file) {
                $sort++;
                $consultant->bannerSlides()->create([
                    'image_path' => ConsultantFileUploader::storeImage($file, 'banners'),
                    'sort_order' => $sort,
                ]);
            }
        }

        $this->syncSections($consultant, $request->input('sections', []), $request);

        $consultant = $consultant->fresh()->load(['bannerSlides', 'pageSections']);

        if ($this->isAdminEditor()) {
            $isPublish = $validated['submission_action'] === 'publish';
            if ($isPublish) {
                $consultant->update([
                    'public_page_status' => 'approved',
                    'pending_page_data' => null,
                    'published_page_data' => $consultant->publicPageSnapshot(),
                    'public_page_submitted_at' => null,
                    'public_page_approved_at' => now(),
                    'public_page_approved_by' => $request->user()->id,
                ]);
                PortalNotificationService::notifyUser(
                    $consultant->user,
                    'Consultant page updated by admin',
                    ($consultant->display_name ?: $consultant->company_name).' consultant page was updated and published by admin.',
                    route('consultant.public-page.edit'),
                    'reviewed'
                );
                $message = 'Store page published. Changes are live — no approval needed.';
            } else {
                $consultant->update([
                    'public_page_status' => 'draft',
                    'pending_page_data' => null,
                    'public_page_submitted_at' => null,
                ]);
                $message = 'Draft saved. Visible only in Live Preview until you publish.';
            }
        } else {
            $isSubmission = $validated['submission_action'] === 'submit';
            $consultant->update([
                'public_page_status' => $isSubmission ? 'pending' : 'draft',
                'pending_page_data' => $isSubmission ? $consultant->publicPageSnapshot() : null,
                'public_page_submitted_at' => $isSubmission ? now() : null,
            ]);

            if ($isSubmission) {
                PortalNotificationService::notifyAdminsOfApprovalRequest('Consultant public page', $consultant->display_name ?: $consultant->company_name, route('admin.consultants.public-page.review', $consultant));
            }

            $message = $isSubmission
                ? 'Public page saved and sent to admin for approval.'
                : 'Draft saved. Your changes are available only in Live Preview.';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'public_page_status' => $consultant->public_page_status,
                'preview_url' => $editor['previewUrl'],
                'logo_url' => $consultant->logo ? asset($consultant->logo) : null,
                'sections' => $consultant->pageSections->map(fn ($section) => [
                    'id' => $section->id,
                    'title' => $section->title,
                    'content' => $section->content,
                    'image_url' => $section->image_path ? asset($section->image_path) : null,
                ])->values(),
                'banner_slides' => $consultant->bannerSlides->map(fn ($slide) => [
                    'id' => $slide->id,
                    'image_url' => asset($slide->image_path),
                ])->values(),
            ]);
        }

        return redirect()
            ->route($editor['editRedirectRoute'], $editor['editRedirectParams'])
            ->with('success', $message);
    }

    public function deleteBannerSlide(ConsultantBannerSlide $slide): JsonResponse
    {
        abort_unless($slide->consultant_id === $this->editorConsultant()->id, 403);
        $slide->delete();

        return response()->json(['message' => 'Banner slide removed.']);
    }

    public function preview(?Consultant $consultant = null): View
    {
        $consultant = $this->editorConsultant($consultant);
        $this->normalizeConsultantSectionContentImages($consultant);
        $consultant->load(['bannerSlides', 'pageSections']);

        $approvedServices = ConsultantService::query()
            ->where('consultant_id', $consultant->id)
            ->where('status', 'approved')
            ->latest('updated_at')
            ->get();

        return view('frontend.consultant.show', [
            'consultant' => $consultant,
            'preview' => true,
            'activeNav' => 'home',
            'approvedServices' => $approvedServices,
            'consultantRecentAds' => collect(),
            'selectedCategoryNamesByConsultantAdId' => [],
        ]);
    }

    protected function syncSections($consultant, array $sections, Request $request): void
    {
        $sort = 0;
        foreach ($sections as $index => $sectionData) {
            if (! empty($sectionData['_delete']) && ! empty($sectionData['id'])) {
                $section = ConsultantPageSection::where('consultant_id', $consultant->id)->find($sectionData['id']);
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
                ? ConsultantPageSection::where('consultant_id', $consultant->id)->find($sectionData['id'])
                : null;

            if (! $section) {
                $section = new ConsultantPageSection(['consultant_id' => $consultant->id]);
            }

            $imageFile = $request->file("sections.{$index}.image");
            if ($imageFile) {
                $section->image_path = ConsultantFileUploader::storeImage($imageFile, 'sections');
            }

            $videoFile = $request->file("sections.{$index}.video_file");
            if ($videoFile) {
                $directory = public_path('uploads/consultants/sections/videos');
                if (! File::isDirectory($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }
                $filename = uniqid('section-video-', true).'.'.$videoFile->getClientOriginalExtension();
                $videoFile->move($directory, $filename);
                $content .= '<div class="vendor-section-video mt-3"><video controls preload="metadata"><source src="'.asset('uploads/consultants/sections/videos/'.$filename).'"></video></div>';
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
                'content' => $this->consultantEmbeddedContentImages((string) $content),
                'sort_order' => $sort++,
            ]);
            $section->consultant_id = $consultant->id;
            $section->save();
        }
    }


    protected function decodedField(string $value): string
    {
        $decoded = base64_decode($value, true);

        return $decoded !== false ? $decoded : '';
    }

    protected function decodedSectionField(array $sectionData, string $field): string
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


    protected function replaceUploadedContentImages(string $html, array $files, string $sectionIndex): string
    {
        foreach ($files as $imageIndex => $file) {
            if (! $file) {
                continue;
            }

            $token = '__section_content_image_'.$sectionIndex.'_'.((string) $imageIndex).'__';
            if (! str_contains($html, $token)) {
                continue;
            }

            $path = ConsultantFileUploader::storeImage($file, 'sections/content-images');
            $html = str_replace($token, asset($path), $html);
        }

        return $html;
    }

    protected function consultantEmbeddedContentImages(string $html): string
    {
        if (! str_contains($html, 'data:image/')) {
            return $html;
        }

        $directory = public_path('uploads/consultants/sections/content-images');
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

                $relativePath = 'uploads/consultants/sections/content-images/'.$filename;

                return 'src='.$matches[1].asset($relativePath).$matches[1];
            },
            $html
        );
    }

    protected function normalizeConsultantSectionContentImages(Consultant $consultant): void
    {
        $sections = ConsultantPageSection::query()
            ->where('consultant_id', $consultant->id)
            ->where('content', 'like', '%data:image/%')
            ->get(['id', 'content']);

        foreach ($sections as $section) {
            $normalized = $this->consultantEmbeddedContentImages((string) $section->content);
            if ($normalized === (string) $section->content) {
                continue;
            }

            $section->forceFill(['content' => $normalized])->saveQuietly();
        }
    }

    protected function deleteOrphanManagedMedia(string $oldContent, string $newContent): void
    {
        $oldPaths = $this->extractManagedPaths($oldContent);
        $newPaths = $this->extractManagedPaths($newContent);
        foreach (array_diff($oldPaths, $newPaths) as $relativePath) {
            ConsultantFileUploader::deleteIfExists($relativePath);
        }
    }

    protected function deleteManagedMediaFromContent(string $content): void
    {
        foreach ($this->extractManagedPaths($content) as $relativePath) {
            ConsultantFileUploader::deleteIfExists($relativePath);
        }
    }

    protected function maxWordsRule(string $label, int $limit): \Closure
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

    protected function extractManagedPaths(string $html): array
    {
        preg_match_all('#uploads/consultants/sections/(?:content-images|videos)/[^"\'\s<]+#', $html, $matches);
        return array_values(array_unique($matches[0] ?? []));
    }
}
