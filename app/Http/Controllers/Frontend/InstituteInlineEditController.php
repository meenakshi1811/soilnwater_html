<?php

namespace App\Http\Controllers\Frontend;

use App\Support\SchoolInstituteHelper;
use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Support\InstituteCourseWings;
use App\Support\InstituteFeeStructure;
use App\Support\InstituteFileUploader;
use App\Support\InstituteGallery;
use App\Support\InstituteProfileDetails;
use App\Support\SchoolProfilePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Powers inline (AJAX) editing of a school/institute public page by its owner: serves the popup
 * edit form for a section and re-renders a public section after a save. All saves themselves go to
 * the existing portal endpoints (the "school." / "institute." routes) — this only renders HTML.
 */
class InstituteInlineEditController extends Controller
{
    /** Editor section key => the portal public-page form partial reused inside the popup. */
    private const EDITOR_SECTIONS = [
        'notices' => 'backend.institute.partials.public-page.notices',
        'classes' => 'backend.institute.partials.public-page.classes',
        'performers' => 'backend.institute.partials.public-page.performers',
        'achievements' => 'backend.institute.partials.public-page.achievements',
        'books' => 'backend.institute.partials.public-page.books',
    ];

    /** The editable form for one section, rendered for the popup. */
    public function form(string $slug, string $section, string $ownerRole = 'school'): Response
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);

        if (in_array($section, ['basics', 'admission', 'facilities', 'gallery', 'courses', 'jobs', 'fee', 'diary-holiday', 'diary-leave'], true)) {
            return response(view('frontend.institutes.partials.school-profile.inline.'.$section.'-form', [
                'institute' => $institute,
                'listingContext' => SchoolInstituteHelper::listingContext($ownerRole),
            ])->render());
        }

        abort_unless(isset(self::EDITOR_SECTIONS[$section]), 404);

        $html = view(self::EDITOR_SECTIONS[$section], [
            'institute' => $institute,
            'inlineEditor' => true,
        ])->render();

        return response($html);
    }

    /** Save the simple public-text fields (about + tagline) edited inline. */
    public function updateBasics(Request $request, string $slug, string $ownerRole = 'school'): JsonResponse
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);

        $validated = $request->validate([
            'tagline' => ['required', 'string', 'max:255'],
            'about' => ['required', 'string', 'min:10'],
        ], [
            'tagline.required' => 'Please enter a tagline.',
            'about.required' => 'Please enter the about section.',
            'about.min' => 'About must be at least 10 characters.',
        ]);

        $institute->update([
            'tagline' => $validated['tagline'],
            'about' => $validated['about'],
        ]);

        return response()->json(['message' => 'About section updated.']);
    }

    /**
     * Save the Admission Info text edited inline: the short intro, the key points (one per line;
     * the first two show on the page) and the full details in the Read more popup.
     */
    public function updateAdmission(Request $request, string $slug, string $ownerRole = 'school'): JsonResponse
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);
        [$maxPoints, $maxLength] = InstituteProfileDetails::LINE_LISTS['admission_highlights'];

        $validated = $request->validate([
            'lead' => ['nullable', 'string', 'max:500'],
            'highlights' => ['nullable', 'string', 'max:'.($maxPoints * ($maxLength + 2))],
            'details' => ['nullable', 'string', 'max:5000'],
        ], [
            'lead.max' => 'The short intro can be up to 500 characters.',
            'details.max' => 'The full details can be up to 5,000 characters.',
        ]);

        $highlights = collect(preg_split('/\R/', (string) ($validated['highlights'] ?? '')))
            ->map(fn (string $line): string => trim($line))
            ->filter(fn (string $line): bool => $line !== '')
            ->values();
        if ($highlights->count() > $maxPoints) {
            throw ValidationException::withMessages(['highlights' => 'Up to '.$maxPoints.' key points, one per line.']);
        }
        if ($highlights->contains(fn (string $line): bool => mb_strlen($line) > $maxLength)) {
            throw ValidationException::withMessages(['highlights' => 'Each key point can be up to '.$maxLength.' characters.']);
        }

        $meta = is_array($institute->profile_meta) ? $institute->profile_meta : [];
        $meta['admission'] = array_merge((array) ($meta['admission'] ?? []), [
            'lead' => trim((string) ($validated['lead'] ?? '')),
            'highlights' => $highlights->all(),
            'details' => trim((string) ($validated['details'] ?? '')),
        ]);
        $institute->forceFill(['profile_meta' => $meta])->save();

        return response()->json(['message' => 'Admission info updated.']);
    }

    /** Add one facility (name + photo) to the profile's facilities list. */
    public function addFacility(Request $request, string $slug, string $ownerRole = 'school'): JsonResponse
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Please enter the facility name.',
            'image.required' => 'Please upload a facility photo.',
        ]);

        $name = trim($validated['name']);
        $facilities = $this->facilityNames($institute);
        if (in_array($name, $facilities, true)) {
            throw ValidationException::withMessages(['name' => ['This facility is already listed.']]);
        }

        $images = (array) ($institute->facility_images ?? []);
        $images[$name] = InstituteFileUploader::storeImage($request->file('image'), 'facilities');
        $facilities[] = $name;

        $institute->update(['facilities' => $facilities, 'facility_images' => $images]);

        return response()->json([
            'message' => 'Facility added.',
            'item_html' => view('frontend.institutes.partials.school-profile.inline.facility-item', [
                'institute' => $institute,
                'listingContext' => SchoolInstituteHelper::listingContext($ownerRole),
                'name' => $name,
                'image' => $images[$name],
            ])->render(),
        ]);
    }

    /** Remove one facility (by name) and its photo. */
    public function removeFacility(Request $request, string $slug, string $ownerRole = 'school'): JsonResponse
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);
        $name = (string) $request->input('name');

        $facilities = array_values(array_filter($this->facilityNames($institute), fn ($n) => $n !== $name));
        $images = (array) ($institute->facility_images ?? []);
        if (isset($images[$name])) {
            InstituteFileUploader::deleteIfExists($images[$name]);
            unset($images[$name]);
        }

        $institute->update(['facilities' => $facilities, 'facility_images' => $images]);

        return response()->json(['message' => 'Facility removed.']);
    }

    /** Add one or more gallery photos/videos. */
    public function addGalleryItems(Request $request, string $slug, string $ownerRole = 'school'): JsonResponse
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);

        $request->validate([
            'files' => ['required', 'array', 'max:'.InstituteGallery::MAX_NEW_UPLOADS],
            'files.*' => ['file'],
        ], [
            'files.required' => 'Please choose at least one photo or video.',
            'files.max' => 'You can upload up to '.InstituteGallery::MAX_NEW_UPLOADS.' files at a time.',
        ]);

        $entries = InstituteGallery::persistableEntries($institute->gallery);
        $files = array_values(array_filter((array) $request->file('files', [])));

        if (count($entries) + count($files) > InstituteGallery::MAX_ITEMS) {
            throw ValidationException::withMessages([
                'files' => ['Your gallery can include up to '.InstituteGallery::MAX_ITEMS.' photos and videos.'],
            ]);
        }

        $listingContext = SchoolInstituteHelper::listingContext($ownerRole);
        $itemsHtml = '';
        foreach ($files as $file) {
            $type = InstituteGallery::assertUploadValid($file);
            $path = $type === 'video'
                ? InstituteFileUploader::storeVideo($file)
                : InstituteFileUploader::storeImage($file, 'gallery');
            $entries[] = ['type' => $type, 'path' => $path];
            $itemsHtml .= view('frontend.institutes.partials.school-profile.inline.gallery-item', [
                'institute' => $institute,
                'listingContext' => $listingContext,
                'type' => $type,
                'path' => $path,
            ])->render();
        }

        $institute->update(['gallery' => $entries]);

        return response()->json(['message' => 'Gallery updated.', 'item_html' => $itemsHtml]);
    }

    /** Remove one gallery item (by stored path) and delete its file. */
    public function removeGalleryItem(Request $request, string $slug, string $ownerRole = 'school'): JsonResponse
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);
        $path = (string) $request->input('path');

        $entries = InstituteGallery::persistableEntries($institute->gallery);
        $remaining = array_values(array_filter($entries, fn ($e) => $e['path'] !== $path));
        if (count($remaining) !== count($entries)) {
            InstituteFileUploader::deleteIfExists($path);
        }

        $institute->update(['gallery' => $remaining]);

        return response()->json(['message' => 'Gallery item removed.']);
    }

    /** Add one "Courses & Programs" card. Preserves the default cards when the school has none saved yet. */
    public function addCourseWing(Request $request, string $slug, string $ownerRole = 'school'): JsonResponse
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'icon' => ['nullable', 'string', Rule::in(array_keys(InstituteCourseWings::ICONS))],
            'grades' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'title.required' => 'Please enter a course or program title.',
        ]);

        $wings = InstituteCourseWings::wings(data_get($institute->profile_meta, 'course_wings'), $ownerRole === 'school');
        $title = trim($validated['title']);
        if (collect($wings)->contains(fn ($w) => $w['title'] === $title)) {
            throw ValidationException::withMessages(['title' => ['A card with this title already exists.']]);
        }
        if (count($wings) >= 12) {
            throw ValidationException::withMessages(['title' => ['You can add up to 12 courses or programs.']]);
        }

        $wings[] = [
            'icon' => $validated['icon'] ?? 'fa-book-open',
            'title' => $title,
            'grades' => trim((string) ($validated['grades'] ?? '')),
            'description' => trim((string) ($validated['description'] ?? '')),
            'image' => $request->hasFile('image')
                ? InstituteFileUploader::storeImage($request->file('image'), 'courses')
                : '',
        ];

        $this->saveCourseWings($institute, InstituteCourseWings::cleanWings($wings));

        return response()->json([
            'message' => 'Course added.',
            'item_html' => view('frontend.institutes.partials.school-profile.inline.course-wing-item', [
                'institute' => $institute,
                'listingContext' => SchoolInstituteHelper::listingContext($ownerRole),
                'wing' => end($wings),
            ])->render(),
        ]);
    }

    /** Remove one "Courses & Programs" card (by title) and its photo. */
    public function removeCourseWing(Request $request, string $slug, string $ownerRole = 'school'): JsonResponse
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);
        $title = (string) $request->input('title');

        $wings = InstituteCourseWings::wings(data_get($institute->profile_meta, 'course_wings'), $ownerRole === 'school');
        $removed = collect($wings)->firstWhere('title', $title);
        $wings = array_values(array_filter($wings, fn ($w) => $w['title'] !== $title));

        if ($removed && filled($removed['image'] ?? null)) {
            InstituteFileUploader::deleteIfExists($removed['image']);
        }

        $this->saveCourseWings($institute, $wings);

        return response()->json(['message' => 'Course removed.']);
    }

    /** Save a simplified fee structure (title/session/intro + "Label : Amount" rows + file). */
    public function updateFee(Request $request, string $slug, string $ownerRole = 'school'): JsonResponse
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'session' => ['nullable', 'string', 'max:255'],
            'effective_from' => ['nullable', 'date'],
            'intro' => ['nullable', 'string', 'max:5000'],
            'rows_text' => ['nullable', 'string', 'max:20000'],
            'fee_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'remove_fee_file' => ['nullable', 'boolean'],
        ]);

        // Each non-empty line "Label : Amount" becomes a fee row in a single "Amount" table.
        $rows = [];
        foreach (preg_split('/\r?\n/', (string) ($validated['rows_text'] ?? '')) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $pos = strrpos($line, ':');
            $label = $pos === false ? $line : trim(substr($line, 0, $pos));
            $amount = $pos === false ? '' : trim(substr($line, $pos + 1));
            if ($label === '') {
                continue;
            }
            $rows[] = ['label' => $label, 'values' => [$amount]];
        }
        $tables = $rows !== []
            ? [['title' => '', 'row_heading' => 'Particulars', 'columns' => ['Amount'], 'rows' => $rows, 'show_total' => false, 'note' => '']]
            : [];

        $meta = (array) ($institute->profile_meta ?? []);
        $saved = (array) ($meta['fee_structure'] ?? []);
        $file = $saved['file'] ?? null;
        if ($request->boolean('remove_fee_file') || $request->hasFile('fee_file')) {
            InstituteFileUploader::deleteIfExists($file);
            $file = null;
        }
        if ($request->hasFile('fee_file')) {
            $upload = $request->file('fee_file');
            $file = strtolower($upload->getClientOriginalExtension()) === 'pdf'
                ? InstituteFileUploader::storeDocument($upload, 'fee-structures')
                : InstituteFileUploader::storeImage($upload, 'fee-structures');
        }

        $fee = InstituteFeeStructure::clean([
            'title' => $validated['title'] ?? '',
            'session' => $validated['session'] ?? '',
            'effective_from' => $validated['effective_from'] ?? '',
            'intro' => $validated['intro'] ?? '',
            'tables' => $tables,
            'sections' => $saved['sections'] ?? [], // keep any richer sections added from the portal
            'file' => $file,
        ]);

        if (InstituteFeeStructure::isEmpty($fee)) {
            unset($meta['fee_structure']);
        } else {
            $meta['fee_structure'] = $fee;
        }
        $institute->update(['profile_meta' => $meta !== [] ? $meta : null]);

        return response()->json(['message' => 'Fee structure saved.']);
    }

    /** @param list<array<string, mixed>> $wings */
    private function saveCourseWings(Institute $institute, array $wings): void
    {
        $meta = (array) ($institute->profile_meta ?? []);
        $meta['course_wings'] = $wings;
        $institute->update(['profile_meta' => $meta !== [] ? $meta : null]);
    }

    /** @return list<string> */
    private function facilityNames(Institute $institute): array
    {
        return array_values(array_filter(
            (array) ($institute->facilities ?? []),
            fn ($n) => is_string($n) && trim($n) !== ''
        ));
    }

    /**
     * Freshly rendered public-facing section HTML, to swap in place after a save.
     * 'notices' re-renders the notice board; everything else re-renders the main content block
     * (which already handles empty -> populated sections and correct ordering).
     */
    public function section(string $slug, string $section, string $ownerRole = 'school'): Response
    {
        $institute = $this->ownedInstituteOrAbort($slug, $ownerRole);
        $profile = new SchoolProfilePresenter($institute);
        $listingContext = SchoolInstituteHelper::listingContext($ownerRole);
        $entityLabel = SchoolInstituteHelper::label($ownerRole);

        if ($section === 'notices') {
            return response(view('frontend.institutes.partials.school-profile.inline.notices-public', [
                'institute' => $institute,
                'canEditInline' => true,
            ])->render());
        }

        // The Admission Info Read more popup (outside the main content block)
        if ($section === 'admission-modal') {
            return response(view('frontend.institutes.partials.school-profile.admission-modal', [
                'profile' => $profile,
            ])->render());
        }

        if ($section === 'hero') {
            $aboutText = $profile->aboutText();

            return response(view('frontend.institutes.partials.school-profile.hero', [
                'profile' => $profile,
                'institute' => $institute,
                'engagement' => app(InstituteProfileController::class)->engagementState($institute, auth()->user()),
                'aboutText' => $aboutText,
                'aboutNeedsToggle' => strlen($aboutText) > 320,
                'listingContext' => $listingContext,
                'entityLabel' => $entityLabel,
                'canEditInline' => true,
            ])->render());
        }

        $aboutText = $profile->aboutText();

        return response(view('frontend.institutes.partials.school-profile.content', [
            'profile' => $profile,
            'institute' => $institute,
            'authUser' => auth()->user(),
            'aboutText' => $aboutText,
            'aboutNeedsToggle' => strlen($aboutText) > 320,
            'listingContext' => $listingContext,
            'entityLabel' => $entityLabel,
            'engagement' => app(InstituteProfileController::class)->engagementState($institute, auth()->user()),
            'canEditInline' => true,
        ])->render());
    }

    /** Resolve the profile by slug and ensure the signed-in user owns it. */
    private function ownedInstituteOrAbort(string $slug, string $ownerRole): Institute
    {
        $institute = Institute::query()
            ->forOwnerRole($ownerRole)
            ->where('slug', $slug)
            ->with([
                'user:id,name,profile_image,role',
                'notices',
                'activeNotices',
                'achievements',
                'topPerformers',
                'schoolClasses',
                'books',
                'jobs' => fn ($query) => $query->open()->latest('published_at')->latest('id'),
                'affiliatedEducators' => fn ($query) => $query->approved()->orderByPivot('sort_order'),
            ])
            ->firstOrFail();

        $user = auth()->user();
        abort_unless(
            $user && (int) $user->id === (int) $institute->user_id && $user->role === $ownerRole,
            403
        );

        return $institute;
    }
}
