<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Support\EducatorFileUploader;
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

        return view('backend.educator.profile', [
            'user' => $user,
            'educator' => $user->educator,
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user()->load('educator');
        /** @var Educator $educator */
        $educator = $user->educator;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'whatsapp_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{4,10}$/'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_photo' => [$educator->profile_photo ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'associated_institute' => ['nullable', 'string', 'max:255'],
            'institute_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'institute_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'state' => ['nullable', 'string', 'max:120'],
            'professional_headline' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'teaching_method' => ['nullable', 'string', 'max:255'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['nullable', 'string', 'max:80'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['nullable', 'string', 'max:80'],
            'boards' => ['nullable', 'array'],
            'boards.*' => ['nullable', 'string', 'max:80'],
            'teaching_modes' => ['nullable', 'array'],
            'teaching_modes.*' => ['nullable', 'string', 'max:80'],
            'subjects' => ['nullable', 'array'],
            'subjects.*.name' => ['nullable', 'string', 'max:120'],
            'subjects.*.level' => ['nullable', 'in:primary,secondary,specialized'],
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
            'availability' => ['nullable', 'array'],
            'availability.*.day' => ['nullable', 'string', 'max:40'],
            'availability.*.slots' => ['nullable', 'string', 'max:255'],
            'service_area' => ['nullable', 'array'],
            'service_area.*' => ['nullable', 'string', 'max:120'],
            'take_tuitions' => ['nullable', 'boolean'],
            'tuition_batches' => ['nullable', 'array'],
            'tuition_batches.*.class' => ['nullable', 'string', 'max:80'],
            'tuition_batches.*.subject' => ['nullable', 'string', 'max:80'],
            'tuition_batches.*.batch_type' => ['nullable', 'string', 'max:80'],
            'tuition_batches.*.student_count' => ['nullable', 'string', 'max:20'],
            'tuition_batches.*.cost' => ['nullable', 'string', 'max:120'],
            'tuition_location' => ['nullable', 'string', 'max:255'],
            'tuition_timings' => ['nullable', 'string', 'max:255'],
            'tuition_charges' => ['nullable', 'string', 'max:255'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'students_taught' => ['nullable', 'integer', 'min:0'],
            'success_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_available_now' => ['nullable', 'boolean'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'whatsapp_url' => ['nullable', 'url', 'max:500'],
        ], [
            'phone_number.regex' => 'Phone number must contain only digits and be between 10 and 15 characters.',
            'whatsapp_number.regex' => 'WhatsApp number must contain only digits and be between 10 and 15 characters.',
            'pincode.regex' => 'Pincode must contain only digits and be between 4 and 10 characters.',
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old.',
            'profile_photo.required' => 'A profile image is required for teacher / tutor profiles.',
        ]);

        $phoneChanged = $user->phone_number !== $validated['phone_number'];

        $user->name = $validated['name'];
        $user->full_name = $validated['name'];
        $user->phone_number = $validated['phone_number'];
        $user->whatsapp_number = $validated['whatsapp_number'];
        $user->address = $validated['address'];
        $user->city = $validated['city'];
        $user->pincode = $validated['pincode'];
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
        $validated['classes'] = $this->cleanStringList($validated['classes'] ?? []);
        $validated['boards'] = $this->cleanStringList($validated['boards'] ?? []);
        $validated['teaching_modes'] = $this->cleanStringList($validated['teaching_modes'] ?? []);
        $validated['achievements'] = $this->cleanStringList($validated['achievements'] ?? []);
        $validated['certifications'] = $this->cleanStringList($validated['certifications'] ?? []);
        $validated['service_area'] = $this->cleanStringList($validated['service_area'] ?? []);
        $validated['tuition_batches'] = $this->cleanTuitionBatches($validated['tuition_batches'] ?? []);
        $validated['tuition_classes'] = collect($validated['tuition_batches'])->pluck('class')->filter()->unique()->values()->all();
        $validated['tuition_subjects'] = collect($validated['tuition_batches'])->pluck('subject')->filter()->unique()->values()->all();
        $validated['tuition_types'] = collect($validated['tuition_batches'])->pluck('batch_type')->filter()->unique()->values()->all();
        $validated['subjects'] = $this->cleanSubjects($validated['subjects'] ?? []);
        $validated['qualifications'] = $this->cleanObjectList($validated['qualifications'] ?? [], ['degree', 'institution', 'year']);
        $validated['experiences'] = $this->cleanExperiences($validated['experiences'] ?? []);
        $validated['availability'] = $this->cleanObjectList($validated['availability'] ?? [], ['day', 'slots']);
        $validated['take_tuitions'] = $request->boolean('take_tuitions');
        $validated['is_available_now'] = $request->boolean('is_available_now');
        $validated['tagline'] = Educator::excerptFromAbout($validated['about'] ?? null);
        $validated['display_name'] = $validated['name'];
        $validated['phone'] = $validated['phone_number'];
        $validated['whatsapp'] = $validated['whatsapp_number'];
        $validated['residential_address'] = $validated['address'];
        $validated['email'] = $user->email;

        if ($validated['display_name'] !== $educator->display_name) {
            $validated['slug'] = Educator::generateUniqueSlug($validated['display_name']);
        }

        $educator->update(collect($validated)->except([
            'name',
            'phone_number',
            'whatsapp_number',
            'address',
            'date_of_birth',
            'password',
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
                'take_tuitions' => (bool) $educator->take_tuitions,
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
     * @return list<array{class: string, subject: string, batch_type: string, student_count: string, cost: string}>
     */
    private function cleanTuitionBatches(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                if (! is_array($item)) {
                    return null;
                }

                $row = [
                    'class' => trim((string) ($item['class'] ?? '')),
                    'subject' => trim((string) ($item['subject'] ?? '')),
                    'batch_type' => trim((string) ($item['batch_type'] ?? '')),
                    'student_count' => trim((string) ($item['student_count'] ?? '')),
                    'cost' => trim((string) ($item['cost'] ?? '')),
                ];

                if (collect($row)->filter()->isEmpty()) {
                    return null;
                }

                return $row;
            })
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
    private function cleanSubjects(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                if (! is_array($item)) {
                    return null;
                }
                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                return [
                    'name' => $name,
                    'level' => in_array($item['level'] ?? '', ['primary', 'secondary', 'specialized'], true)
                        ? $item['level']
                        : 'primary',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

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
