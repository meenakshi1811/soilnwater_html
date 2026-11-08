<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Support\EducatorFileUploader;
use App\Support\EducatorSubjects;
use App\Support\EducatorProfileLists;
use App\Support\ManagedEducator;
use App\Support\ProfilePhoneNumbers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EducatorProfileController extends Controller
{
    public function edit(Request $request): View
    {
        // (the teacher's own account, also when an admin edits it)
        $educator = ManagedEducator::current($request);
        abort_unless($educator, 404);
        $user = ManagedEducator::owner($request);

        if ($educator->display_name) {
            $user->name = $educator->display_name;
        }

        $educator->recalculateRating();
        $educator->refresh();

        $instituteAffiliations = $educator->instituteAffiliations()->with(['institute.user:id,role'])->get();

        return view('backend.educator.profile', [
            'user' => $user,
            'educator' => $educator,
            'instituteAffiliations' => $instituteAffiliations,
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        /** @var Educator $educator */
        $educator = ManagedEducator::current($request);
        abort_unless($educator, 404);
        $user = ManagedEducator::owner($request);
        $byAdmin = ManagedEducator::fromAdmin($request) !== null;

        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{4,10}$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_photo' => [$educator->profile_photo ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'associated_with_school' => ['nullable', 'boolean'],
            'associated_institute' => ['nullable', 'string', 'max:255'],
            'institute_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'institute_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'teaching_city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'professional_headline' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'teaching_method' => ['nullable', 'string', 'max:255'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['nullable', 'string', 'max:80'],
            'teaching_modes' => ['nullable', 'array'],
            'teaching_modes.*' => ['nullable', 'string', 'max:80'],
            'subjects' => ['nullable', 'array'],
            'subjects.*.name' => ['nullable', 'string', 'max:120'],
            'subjects.*.classes' => ['nullable', 'array'],
            'subjects.*.classes.*' => ['nullable', 'string', 'max:80'],
            'subjects.*.boards' => ['nullable', 'array'],
            'subjects.*.boards.*' => ['nullable', 'string', 'max:80'],
            'subjects.*.years_experience' => ['nullable', 'string', 'max:20'],
            'subjects.*.classes_lines' => ['nullable', 'string', 'max:2000'],
            'subjects.*.boards_lines' => ['nullable', 'string', 'max:2000'],
            'qualifications' => ['nullable', 'array'],
            'qualifications.*.degree' => ['nullable', 'string', 'max:255'],
            'qualifications.*.institution' => ['nullable', 'string', 'max:255'],
            'qualifications.*.year' => ['nullable', 'string', 'max:20'],
            'experiences' => ['nullable', 'array'],
            'experiences.*.title' => ['nullable', 'string', 'max:255'],
            'experiences.*.organization' => ['nullable', 'string', 'max:255'],
            'experiences.*.start_year' => ['nullable', 'digits:4'],
            'experiences.*.end_year' => ['nullable', 'digits:4'],
            'experiences.*.is_current' => ['nullable', 'boolean'],
            'experiences.*.description' => ['nullable', 'string', 'max:1000'],
            'achievements' => ['nullable', 'array'],
            'achievements.*' => ['nullable', 'string', 'max:500'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['nullable', 'string', 'max:500'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'students_taught' => ['nullable', 'integer', 'min:0'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'whatsapp_url' => ['nullable', 'url', 'max:500'],
        ], ProfilePhoneNumbers::validationRules()), array_merge([
            'whatsapp_number.regex' => 'WhatsApp number must contain only digits and be between 10 and 15 characters.',
            'pincode.regex' => 'Pincode must contain only digits and be between 4 and 10 characters.',
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old.',
            'profile_photo.required' => 'A profile image is required for teacher / tutor profiles.',
        ], ProfilePhoneNumbers::validationMessages()));

        $phoneNumbers = ProfilePhoneNumbers::normalize($validated['phone_numbers']);
        $phoneChanged = ProfilePhoneNumbers::applyToUser($user, $phoneNumbers);

        $user->name = $validated['name'];
        $user->full_name = $validated['name'];
        $user->whatsapp_number = $validated['whatsapp_number'];
        $user->address = $validated['address'];
        $user->city = $validated['city'];
        $user->pincode = $validated['pincode'];
        $user->latitude = $validated['latitude'] ?? null;
        $user->longitude = $validated['longitude'] ?? null;
        $user->date_of_birth = $validated['date_of_birth'];

        if ($phoneChanged) {
            $user->phone_verified_at = null;
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            EducatorFileUploader::deleteIfExists($educator->profile_photo);
            $validated['profile_photo'] = EducatorFileUploader::storeImage($request->file('profile_photo'), 'photos');
            $user->profile_image = $validated['profile_photo'];
        } else {
            unset($validated['profile_photo']);
        }

        $user->save();

        $validated['languages'] = EducatorProfileLists::strings($validated['languages'] ?? []);
        $validated['teaching_modes'] = EducatorProfileLists::strings($validated['teaching_modes'] ?? []);
        $validated['achievements'] = EducatorProfileLists::strings($validated['achievements'] ?? []);
        $validated['certifications'] = EducatorProfileLists::strings($validated['certifications'] ?? []);
        $validated['subjects'] = EducatorSubjects::fromFormSubmission($validated['subjects'] ?? []);
        $validated['classes'] = EducatorSubjects::aggregateClasses($validated['subjects']);
        $validated['boards'] = EducatorSubjects::aggregateBoards($validated['subjects']);
        $validated['associated_with_school'] = $request->boolean('associated_with_school');
        if (! $validated['associated_with_school']) {
            $validated['associated_institute'] = null;
            $validated['institute_latitude'] = null;
            $validated['institute_longitude'] = null;
        }
        $validated['city'] = trim((string) ($validated['teaching_city'] ?? ''));
        unset($validated['teaching_city']);
        $validated['qualifications'] = EducatorProfileLists::rows($validated['qualifications'] ?? [], ['degree', 'institution', 'year']);
        $validated['experiences'] = EducatorProfileLists::experiences($validated['experiences'] ?? []);
        $validated['tagline'] = Educator::excerptFromAbout($validated['about'] ?? null);
        $validated['display_name'] = $validated['name'];
        $validated['phone'] = ProfilePhoneNumbers::primary($phoneNumbers);
        $validated['phone_numbers'] = $phoneNumbers;
        $validated['whatsapp'] = $validated['whatsapp_number'];
        $validated['residential_address'] = $validated['address'];
        $validated['latitude'] = $validated['latitude'] ?? null;
        $validated['longitude'] = $validated['longitude'] ?? null;
        $validated['email'] = $user->email;

        if ($validated['display_name'] !== $educator->display_name) {
            $validated['slug'] = Educator::generateUniqueSlug($validated['display_name']);
        }

        $educator->update(collect($validated)->except([
            'name',
            'phone_numbers',
            'whatsapp_number',
            'address',
            'date_of_birth',
            'password',
            'success_rate',
        ])->all());

        // Only the owner editing their own profile is logged out to re-verify a changed number;
        // an admin editing on their behalf keeps their admin session.
        if ($phoneChanged && ! $byAdmin) {
            return $this->logoutForPhoneVerification($request);
        }

        if ($request->expectsJson()) {
            $educator->refresh();

            return response()->json([
                'message' => 'Profile updated successfully.',
                'display_name' => $educator->display_name,
                'photo_url' => $educator->photoUrl(),
            ]);
        }

        return redirect()
            ->to($byAdmin ? ManagedEducator::adminRoute($educator, 'profile.edit') : route('educator.profile.edit'))
            ->with('status', 'Profile updated successfully.');
    }

    private function logoutForPhoneVerification(Request $request): RedirectResponse|JsonResponse
    {
        $message = 'You need to verify the number before continuing. Please login again to verify your phone number.';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login')->with('status', $message);
    }
}
