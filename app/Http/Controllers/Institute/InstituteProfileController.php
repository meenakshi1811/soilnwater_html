<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Support\InstituteFileUploader;
use App\Support\InstituteCourseWings;
use App\Support\InstituteGallery;
use App\Support\InstituteGrades;
use App\Support\InstituteProfileDetails;
use App\Support\ManagedInstitute;
use App\Support\ProfileEmails;
use App\Support\ProfilePhoneNumbers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InstituteProfileController extends Controller
{
    public function edit(Request $request): View
    {
        // (the school's own account, also when an admin edits it)
        $institute = ManagedInstitute::current($request);
        abort_unless($institute, 404);
        $user = ManagedInstitute::owner($request);

        return view('backend.institute.profile', compact('user', 'institute'));
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        /** @var Institute $institute */
        $institute = ManagedInstitute::current($request);
        abort_unless($institute, 404);
        $user = ManagedInstitute::owner($request);
        $byAdmin = ManagedInstitute::fromAdmin($request) !== null;

        $gradesSectionEnabled = $request->boolean('grades_section_enabled');
        $facilitiesSectionEnabled = $request->boolean('facilities_section_enabled');
        $gallerySectionEnabled = $request->boolean('gallery_section_enabled');

        if ($gradesSectionEnabled) {
            $request->merge([
                'grades_offered' => collect($request->input('grades_offered', []))
                    ->filter(function ($row) {
                        if (! is_array($row)) {
                            return is_string($row) && trim($row) !== '';
                        }

                        return trim((string) ($row['class'] ?? '')) !== '';
                    })
                    ->values()
                    ->all(),
            ]);
        }

        // The contact emails, from a form that has them (one open from before keeps the saved ones)
        $emailsInForm = $request->exists('emails');
        if ($emailsInForm) {
            $request->merge(['emails' => ProfileEmails::normalize((array) $request->input('emails', []))]);
        }

        // The page details beyond the main fields (InstituteProfileDetails), from a form that has them
        $detailsInForm = $request->boolean('details_in_form');
        if ($detailsInForm) {
            $request->merge(['details' => InstituteProfileDetails::cleanInput($request->input('details'))]);
        }

        // (a blank, just-added course card or stream is dropped)
        $request->merge([
            'course_wings' => InstituteCourseWings::filledRows($request->input('course_wings', [])),
            'streams' => InstituteCourseWings::cleanStreams($request->input('streams', [])),
        ]);

        $validated = $request->validate(array_merge([
            'institution_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{4,10}$/'],
            'date_of_establishment' => ['required', 'date', 'before_or_equal:today'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'logo' => [$institute->logo ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'brochure' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remove_brochure' => ['nullable', 'boolean'],
            'institution_type' => ['required', 'string', 'in:school,college,university,coaching,other'],
            'board_affiliation' => ['required', 'string', 'max:255'],
            'grades_section_enabled' => ['nullable', 'boolean'],
            'facilities_section_enabled' => ['nullable', 'boolean'],
            'gallery_section_enabled' => ['nullable', 'boolean'],
            'facilities' => [
                Rule::requiredIf($facilitiesSectionEnabled),
                'array',
                Rule::when($facilitiesSectionEnabled, ['min:1']),
            ],
            'facilities.*' => [
                Rule::requiredIf($facilitiesSectionEnabled),
                'string',
                'max:120',
            ],
            'facility_images' => ['nullable', 'array'],
            'facility_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'facility_existing_images' => ['nullable', 'array'],
            'facility_existing_images.*' => ['nullable', 'string', 'max:255'],
            'course_wings' => ['nullable', 'array', 'max:12'],
            'course_wings.*.icon' => ['nullable', 'string', Rule::in(array_keys(InstituteCourseWings::ICONS))],
            'course_wings.*.title' => ['required', 'string', 'max:80'],
            'course_wings.*.grades' => ['nullable', 'string', 'max:80'],
            'course_wings.*.description' => ['nullable', 'string', 'max:300'],
            'streams' => ['nullable', 'array', 'max:12'],
            'streams.*' => ['string', 'max:60'],
            'tagline' => ['required', 'string', 'max:255'],
            'about' => ['required', 'string', 'min:10'],
            'description' => ['nullable', 'string'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'place_id' => ['nullable', 'string', 'max:255'],
            'gallery_uploads' => ['nullable', 'array', 'max:'.InstituteGallery::MAX_NEW_UPLOADS],
            'gallery_uploads.*' => ['file'],
            'removed_gallery' => ['nullable', 'array'],
            'removed_gallery.*' => ['string', 'max:255'],
        ], ProfilePhoneNumbers::validationRules(), $emailsInForm ? ProfileEmails::validationRules() : [], $detailsInForm ? InstituteProfileDetails::validationRules($byAdmin) : [], InstituteGrades::validationRules($gradesSectionEnabled)), array_merge([
            'contact_person.required' => 'Please enter the contact person name.',
            'institution_type.required' => 'Please select an institution type.',
            'board_affiliation.required' => 'Please enter board or affiliation.',
            'tagline.required' => 'Please enter a tagline.',
            'about.required' => 'Please enter the about section.',
            'about.min' => 'About must be at least 10 characters.',
            'state.required' => 'Please enter your state.',
            'facilities.required' => 'Add at least one facility when this section is enabled.',
            'facilities.min' => 'Add at least one facility when this section is enabled.',
            'facilities.*.required' => 'Each facility entry is required.',
            'facility_images.*.image' => 'Each facility photo must be an image.',
            'facility_images.*.mimes' => 'Facility photos must be JPG, PNG or WebP.',
            'facility_images.*.max' => 'Each facility photo can be up to 2 MB.',
            'course_wings.max' => 'You can add up to 12 courses or programs.',
            'course_wings.*.title.required' => 'Each course or program needs a title.',
            'course_wings.*.title.max' => 'A course or program title can be up to 80 characters.',
            'course_wings.*.grades.max' => 'The classes line can be up to 80 characters.',
            'course_wings.*.description.max' => 'A course or program description can be up to 300 characters.',
            'streams.max' => 'You can add up to 12 streams.',
            'streams.*.max' => 'A stream name can be up to 60 characters.',
            'date_of_establishment.required' => 'Please enter the founded date.',
            'date_of_establishment.before_or_equal' => 'Founded date cannot be in the future.',
        ], ProfilePhoneNumbers::validationMessages(), ProfileEmails::validationMessages(), InstituteProfileDetails::validationMessages(), InstituteGrades::validationMessages()));

        $phoneNumbers = ProfilePhoneNumbers::normalize($validated['phone_numbers']);
        $emails = $emailsInForm ? $validated['emails'] : $institute->emailsList();
        $phoneChanged = ProfilePhoneNumbers::applyToUser($user, $phoneNumbers);

        if ($gallerySectionEnabled) {
            $removed = collect($request->input('removed_gallery', []))
                ->filter(fn ($path) => is_string($path) && $path !== '')
                ->values()
                ->all();
            $remainingGalleryCount = InstituteGallery::entries($institute->gallery)
                ->reject(fn (array $item) => in_array($item['path'], $removed, true))
                ->count();
            $newUploadCount = collect($request->file('gallery_uploads', []))->filter()->count();

            if ($remainingGalleryCount + $newUploadCount < 1) {
                throw ValidationException::withMessages([
                    'gallery_uploads' => ['Add at least one gallery photo or video when the gallery section is enabled.'],
                ]);
            }
        }

        if ($request->hasFile('logo')) {
            InstituteFileUploader::deleteIfExists($institute->logo);
            $validated['logo'] = InstituteFileUploader::storeImage($request->file('logo'), 'logos');
        } else {
            unset($validated['logo']);
        }

        $brochurePath = $institute->brochure_path;
        if ($request->boolean('remove_brochure')) {
            InstituteFileUploader::deleteIfExists($brochurePath);
            $brochurePath = null;
        }
        if ($request->hasFile('brochure')) {
            InstituteFileUploader::deleteIfExists($brochurePath);
            $brochurePath = InstituteFileUploader::storeDocument($request->file('brochure'), 'brochures');
        }

        // (an admin keeps the page address they chose; the owner's follows the name)
        $slug = $byAdmin && filled($institute->slug)
            ? $institute->slug
            : Institute::generateUniqueSlug($validated['institution_name'], $institute->id);
        $gallery = $gallerySectionEnabled
            ? $this->syncGallery($request, $institute)
            : ($institute->gallery ?? []);
        $facilityImages = $this->syncFacilityImages($request, $institute, $facilitiesSectionEnabled);

        // The page's other settings: the courses & programs cards and streams, and the page details
        // (only from a form that has them: an emptied list sends no fields, so the form marks itself)
        $profileMeta = (array) ($institute->profile_meta ?? []);
        if ($request->boolean('courses_in_form')) {
            $profileMeta['course_wings'] = InstituteCourseWings::cleanWings($validated['course_wings'] ?? []);
            $profileMeta['streams'] = $validated['streams'] ?? [];
        }
        if ($detailsInForm) {
            $profileMeta = InstituteProfileDetails::toMeta($validated['details'] ?? [], $byAdmin, $profileMeta);
        }

        $institute->update([
            'institution_name' => $validated['institution_name'],
            'contact_person' => $validated['contact_person'] ?? $user->name,
            'display_name' => $validated['institution_name'],
            'slug' => $slug,
            'logo' => $validated['logo'] ?? $institute->logo,
            'phone' => ProfilePhoneNumbers::primary($phoneNumbers),
            'phone_numbers' => $phoneNumbers,
            'whatsapp' => $validated['whatsapp_number'],
            // (the first is the main one; the account's login email is not changed)
            'email' => $emails[0] ?? $user->email,
            'emails' => $emails !== [] ? $emails : null,
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'pincode' => $validated['pincode'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'place_id' => $validated['place_id'] ?? null,
            'institution_type' => $validated['institution_type'] ?? null,
            'board_affiliation' => $validated['board_affiliation'] ?? null,
            'grades_offered' => $gradesSectionEnabled
                ? InstituteGrades::fromValidated($validated['grades_offered'] ?? [])
                : [],
            'facilities' => $facilitiesSectionEnabled
                ? array_values(array_filter($validated['facilities'] ?? []))
                : [],
            'facility_images' => $facilityImages,
            'tagline' => $validated['tagline'] ?? null,
            'about' => $validated['about'] ?? null,
            'description' => $validated['description'] ?? null,
            'brochure_path' => $brochurePath,
            'facebook_url' => $validated['facebook_url'] ?? null,
            'instagram_url' => $validated['instagram_url'] ?? null,
            'youtube_url' => $validated['youtube_url'] ?? null,
            'date_of_establishment' => $validated['date_of_establishment'],
            'gallery' => $gallery,
            'profile_meta' => $profileMeta !== [] ? $profileMeta : null,
        ]);

        $userUpdate = [
            'name' => $validated['institution_name'],
            'full_name' => $validated['institution_name'],
            'phone_number' => ProfilePhoneNumbers::primary($phoneNumbers),
            'phone_numbers' => $phoneNumbers,
            'whatsapp_number' => $validated['whatsapp_number'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'pincode' => $validated['pincode'],
        ];

        if ($phoneChanged && ! $byAdmin) {
            $userUpdate['phone_verified_at'] = null;
        }

        if (filled($validated['password'] ?? null)) {
            $userUpdate['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['logo'])) {
            $userUpdate['profile_image'] = $validated['logo'];
        }

        $user->update($userUpdate);

        $message = 'Profile updated successfully.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'reload' => $request->hasFile('logo')
                    || $request->hasFile('brochure')
                    || $request->hasFile('gallery_uploads')
                    || $request->hasFile('facility_images')
                    || $request->filled('removed_gallery'),
            ]);
        }

        return redirect()->to($byAdmin
            ? ManagedInstitute::adminRoute($institute, 'profile.edit')
            : $request->user()->portalRoute('profile.edit'))->with('status', $message);
    }

    /**
     * Each facility's photo, by name: a new upload, or the one its row already had (the row carries
     * it, so a renamed or moved facility keeps its photo). Photos no longer used are deleted.
     *
     * @return array<string, string>
     */
    private function syncFacilityImages(Request $request, Institute $institute, bool $sectionEnabled): array
    {
        $stored = array_values(array_filter(
            (array) ($institute->facility_images ?? []),
            fn ($path) => is_string($path) && $path !== ''
        ));
        $images = [];

        if ($sectionEnabled) {
            $kept = (array) $request->input('facility_existing_images', []);
            $uploads = (array) $request->file('facility_images', []);

            foreach ((array) $request->input('facilities', []) as $index => $name) {
                $name = is_string($name) ? trim($name) : '';
                if ($name === '' || isset($images[$name])) {
                    continue;
                }

                $upload = $uploads[$index] ?? null;
                if ($upload instanceof UploadedFile) {
                    $images[$name] = InstituteFileUploader::storeImage($upload, 'facilities');
                } elseif (in_array($kept[$index] ?? null, $stored, true)) {
                    // (only a photo this school already has; a row can't point at any other file)
                    $images[$name] = $kept[$index];
                }
            }
        }

        foreach (array_diff($stored, $images) as $unused) {
            InstituteFileUploader::deleteIfExists($unused);
        }

        return $images;
    }

    /** @return list<array{type: string, path: string}> */
    private function syncGallery(Request $request, Institute $institute): array
    {
        $items = InstituteGallery::entries($institute->gallery);
        $removed = collect($request->input('removed_gallery', []))
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values()
            ->all();

        foreach ($removed as $path) {
            InstituteFileUploader::deleteIfExists($path);
        }

        $kept = $items
            ->reject(fn (array $item) => in_array($item['path'], $removed, true))
            ->values();

        $uploads = collect($request->file('gallery_uploads', []))->filter();
        if ($uploads->count() > InstituteGallery::MAX_NEW_UPLOADS) {
            throw ValidationException::withMessages([
                'gallery_uploads' => ['You can upload up to '.InstituteGallery::MAX_NEW_UPLOADS.' gallery files at a time.'],
            ]);
        }

        foreach ($uploads as $file) {
            $type = InstituteGallery::assertUploadValid($file);
            $path = $type === 'video'
                ? InstituteFileUploader::storeVideo($file)
                : InstituteFileUploader::storeImage($file, 'gallery');

            $kept->push([
                'type' => $type,
                'path' => $path,
                'url' => asset($path),
            ]);
        }

        if ($kept->count() > InstituteGallery::MAX_ITEMS) {
            throw ValidationException::withMessages([
                'gallery_uploads' => ['Your gallery can include up to '.InstituteGallery::MAX_ITEMS.' photos and videos.'],
            ]);
        }

        return $kept
            ->map(fn (array $item) => [
                'type' => $item['type'],
                'path' => $item['path'],
            ])
            ->values()
            ->all();
    }
}
