<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Support\InstituteFileUploader;
use App\Support\InstituteGallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InstituteProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('institute');
        $institute = $user->institute;

        return view('backend.institute.profile', compact('user', 'institute'));
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user()->load('institute');
        /** @var Institute $institute */
        $institute = $user->institute;

        $gradesSectionEnabled = $request->boolean('grades_section_enabled');
        $facilitiesSectionEnabled = $request->boolean('facilities_section_enabled');
        $gallerySectionEnabled = $request->boolean('gallery_section_enabled');

        $validated = $request->validate([
            'institution_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
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
            'grades_offered' => [
                Rule::requiredIf($gradesSectionEnabled),
                'array',
                Rule::when($gradesSectionEnabled, ['min:1']),
            ],
            'grades_offered.*' => [
                Rule::requiredIf($gradesSectionEnabled),
                'string',
                'max:80',
            ],
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
        ], [
            'contact_person.required' => 'Please enter the contact person name.',
            'institution_type.required' => 'Please select an institution type.',
            'board_affiliation.required' => 'Please enter board or affiliation.',
            'tagline.required' => 'Please enter a tagline.',
            'about.required' => 'Please enter the about section.',
            'about.min' => 'About must be at least 10 characters.',
            'state.required' => 'Please enter your state.',
            'grades_offered.required' => 'Add at least one grade or class when this section is enabled.',
            'grades_offered.min' => 'Add at least one grade or class when this section is enabled.',
            'grades_offered.*.required' => 'Each grade or class entry is required.',
            'facilities.required' => 'Add at least one facility when this section is enabled.',
            'facilities.min' => 'Add at least one facility when this section is enabled.',
            'facilities.*.required' => 'Each facility entry is required.',
            'date_of_establishment.required' => 'Please enter the founded date.',
            'date_of_establishment.before_or_equal' => 'Founded date cannot be in the future.',
        ]);

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

        $slug = Institute::generateUniqueSlug($validated['institution_name'], $institute->id);
        $gallery = $gallerySectionEnabled
            ? $this->syncGallery($request, $institute)
            : ($institute->gallery ?? []);

        $institute->update([
            'institution_name' => $validated['institution_name'],
            'contact_person' => $validated['contact_person'] ?? $user->name,
            'display_name' => $validated['institution_name'],
            'slug' => $slug,
            'logo' => $validated['logo'] ?? $institute->logo,
            'phone' => $validated['phone_number'],
            'whatsapp' => $validated['whatsapp_number'],
            'email' => $user->email,
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
                ? array_values(array_filter($validated['grades_offered'] ?? []))
                : [],
            'facilities' => $facilitiesSectionEnabled
                ? array_values(array_filter($validated['facilities'] ?? []))
                : [],
            'tagline' => $validated['tagline'] ?? null,
            'about' => $validated['about'] ?? null,
            'description' => $validated['description'] ?? null,
            'brochure_path' => $brochurePath,
            'facebook_url' => $validated['facebook_url'] ?? null,
            'instagram_url' => $validated['instagram_url'] ?? null,
            'youtube_url' => $validated['youtube_url'] ?? null,
            'date_of_establishment' => $validated['date_of_establishment'],
            'gallery' => $gallery,
        ]);

        $userUpdate = [
            'name' => $validated['institution_name'],
            'full_name' => $validated['institution_name'],
            'phone_number' => $validated['phone_number'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'pincode' => $validated['pincode'],
        ];

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
                    || $request->filled('removed_gallery'),
            ]);
        }

        return redirect()->to($request->user()->portalRoute('profile.edit'))->with('status', $message);
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
