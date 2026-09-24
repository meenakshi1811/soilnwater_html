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

        $validated = $request->validate([
            'institution_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'whatsapp_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{4,10}$/'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'logo' => [$institute->logo ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'brochure' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remove_brochure' => ['nullable', 'boolean'],
            'institution_type' => ['nullable', 'string', 'in:school,college,university,coaching,other'],
            'board_affiliation' => ['nullable', 'string', 'max:255'],
            'grades_offered' => ['nullable', 'array'],
            'grades_offered.*' => ['nullable', 'string', 'max:80'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['nullable', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'date_of_establishment' => ['nullable', 'date', 'before_or_equal:today'],
            'gallery_uploads' => ['nullable', 'array', 'max:'.InstituteGallery::MAX_NEW_UPLOADS],
            'gallery_uploads.*' => ['file'],
            'removed_gallery' => ['nullable', 'array'],
            'removed_gallery.*' => ['string', 'max:255'],
        ]);

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
        $gallery = $this->syncGallery($request, $institute);

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
            'state' => $validated['state'] ?? null,
            'pincode' => $validated['pincode'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'institution_type' => $validated['institution_type'] ?? null,
            'board_affiliation' => $validated['board_affiliation'] ?? null,
            'grades_offered' => array_values(array_filter($validated['grades_offered'] ?? [])),
            'facilities' => array_values(array_filter($validated['facilities'] ?? [])),
            'tagline' => $validated['tagline'] ?? null,
            'about' => $validated['about'] ?? null,
            'description' => $validated['description'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'brochure_path' => $brochurePath,
            'facebook_url' => $validated['facebook_url'] ?? null,
            'instagram_url' => $validated['instagram_url'] ?? null,
            'youtube_url' => $validated['youtube_url'] ?? null,
            'date_of_establishment' => $validated['date_of_establishment'] ?? null,
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
            'date_of_birth' => $validated['date_of_birth'],
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
                'redirect' => $request->user()->portalRoute('profile.edit'),
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
