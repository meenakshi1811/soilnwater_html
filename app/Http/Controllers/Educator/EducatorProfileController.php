<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Support\EducatorFileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducatorProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('educator');

        return view('backend.educator.profile', [
            'user' => $user,
            'educator' => $user->educator,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user()->load('educator');
        /** @var Educator $educator */
        $educator = $user->educator;

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'associated_institute' => ['nullable', 'string', 'max:255'],
            'institute_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'institute_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'residential_address' => ['nullable', 'string', 'max:1000'],
            'professional_headline' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'video_profile_url' => ['nullable', 'url', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
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
            'experiences.*.duration' => ['nullable', 'string', 'max:120'],
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
            'tuition_classes' => ['nullable', 'array'],
            'tuition_classes.*' => ['nullable', 'string', 'max:80'],
            'tuition_subjects' => ['nullable', 'array'],
            'tuition_subjects.*' => ['nullable', 'string', 'max:80'],
            'tuition_types' => ['nullable', 'array'],
            'tuition_types.*' => ['nullable', 'string', 'max:80'],
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
        ]);

        if ($request->hasFile('profile_photo')) {
            EducatorFileUploader::deleteIfExists($educator->profile_photo);
            $validated['profile_photo'] = EducatorFileUploader::storeImage($request->file('profile_photo'), 'photos');
            $user->forceFill(['profile_image' => $validated['profile_photo']])->save();
        } else {
            unset($validated['profile_photo']);
        }

        $validated['languages'] = $this->cleanStringList($validated['languages'] ?? []);
        $validated['classes'] = $this->cleanStringList($validated['classes'] ?? []);
        $validated['boards'] = $this->cleanStringList($validated['boards'] ?? []);
        $validated['teaching_modes'] = $this->cleanStringList($validated['teaching_modes'] ?? []);
        $validated['achievements'] = $this->cleanStringList($validated['achievements'] ?? []);
        $validated['certifications'] = $this->cleanStringList($validated['certifications'] ?? []);
        $validated['service_area'] = $this->cleanStringList($validated['service_area'] ?? []);
        $validated['tuition_classes'] = $this->cleanStringList($validated['tuition_classes'] ?? []);
        $validated['tuition_subjects'] = $this->cleanStringList($validated['tuition_subjects'] ?? []);
        $validated['tuition_types'] = $this->cleanStringList($validated['tuition_types'] ?? []);
        $validated['subjects'] = $this->cleanSubjects($validated['subjects'] ?? []);
        $validated['qualifications'] = $this->cleanObjectList($validated['qualifications'] ?? [], ['degree', 'institution', 'year']);
        $validated['experiences'] = $this->cleanObjectList($validated['experiences'] ?? [], ['title', 'organization', 'duration', 'description']);
        $validated['availability'] = $this->cleanObjectList($validated['availability'] ?? [], ['day', 'slots']);
        $validated['take_tuitions'] = $request->boolean('take_tuitions');
        $validated['is_available_now'] = $request->boolean('is_available_now');

        if ($validated['display_name'] !== $educator->display_name) {
            $validated['slug'] = Educator::generateUniqueSlug($validated['display_name']);
        }

        $educator->update($validated);

        $user->forceFill([
            'name' => $validated['display_name'],
            'full_name' => $validated['display_name'],
            'city' => $validated['city'] ?? $user->city,
            'pincode' => $validated['pincode'] ?? $user->pincode,
            'address' => $validated['residential_address'] ?? $user->address,
            'phone_number' => $validated['phone'] ?? $user->phone_number,
            'whatsapp_number' => $validated['whatsapp'] ?? $user->whatsapp_number,
        ])->save();

        return redirect()
            ->route('educator.profile.edit')
            ->with('status', 'Profile updated successfully.');
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
