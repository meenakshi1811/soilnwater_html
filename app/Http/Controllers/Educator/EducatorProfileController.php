<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Support\EducatorFileUploader;
use App\Support\EducatorSubjects;
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
        $user = $request->user()->load('educator');

        if ($user->educator?->display_name) {
            $user->name = $user->educator->display_name;
        }

        $educator = $user->educator;
        $educator?->recalculateRating();
        $educator?->refresh();

        $instituteAffiliations = $educator
            ? $educator->instituteAffiliations()->with(['institute.user:id,role'])->get()
            : collect();

        return view('backend.educator.profile', [
            'user' => $user,
            'educator' => $educator,
            'instituteAffiliations' => $instituteAffiliations,
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user()->load('educator');
        /** @var Educator $educator */
        $educator = $user->educator;

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

        $validated['languages'] = $this->cleanStringList($validated['languages'] ?? []);
        $validated['teaching_modes'] = $this->cleanStringList($validated['teaching_modes'] ?? []);
        $validated['achievements'] = $this->cleanStringList($validated['achievements'] ?? []);
        $validated['certifications'] = $this->cleanStringList($validated['certifications'] ?? []);
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
        $validated['qualifications'] = $this->cleanObjectList($validated['qualifications'] ?? [], ['degree', 'institution', 'year']);
        $validated['experiences'] = $this->cleanExperiences($validated['experiences'] ?? []);
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

        if ($phoneChanged) {
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
            ->route('educator.profile.edit')
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

    /**
     * @param  array<int, mixed>  $items
     * @return list<string>
     */
    private function cleanStringList(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => is_string($item) ? trim($item) : '')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<array{title: string, organization: string, start_year: string, end_year: string, is_current: bool, duration: string, description: string}>
     */
    private function cleanExperiences(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                if (! is_array($item)) {
                    return null;
                }

                $title = trim((string) ($item['title'] ?? ''));
                $organization = trim((string) ($item['organization'] ?? ''));
                $description = trim((string) ($item['description'] ?? ''));
                $startYear = trim((string) ($item['start_year'] ?? ''));
                $isCurrent = filter_var($item['is_current'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $endYear = $isCurrent ? '' : trim((string) ($item['end_year'] ?? ''));

                if ($title === '' && $organization === '' && $startYear === '' && $endYear === '' && $description === '') {
                    return null;
                }

                return [
                    'title' => $title,
                    'organization' => $organization,
                    'start_year' => $startYear,
                    'end_year' => $endYear,
                    'is_current' => $isCurrent,
                    'duration' => Educator::formatExperienceDuration($startYear, $endYear, $isCurrent),
                    'description' => $description,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<array{name: string, level: string}>
     */
    /**
     * @param  array<int, mixed>  $items
     * @param  list<string>  $keys
     * @return list<array<string, string>>
     */
    private function cleanObjectList(array $items, array $keys): array
    {
        return collect($items)
            ->map(function ($item) use ($keys) {
                if (! is_array($item)) {
                    return null;
                }
                $row = [];
                foreach ($keys as $key) {
                    $row[$key] = trim((string) ($item[$key] ?? ''));
                }
                if (collect($row)->filter()->isEmpty()) {
                    return null;
                }

                return $row;
            })
            ->filter()
            ->values()
            ->all();
    }
}
