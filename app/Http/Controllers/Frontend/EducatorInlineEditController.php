<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Support\EducatorFileUploader;
use App\Support\EducatorProfileLists;
use App\Support\EducatorSubjects;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Inline editing of a teacher / tutor's public page by its owner: the popup form for a section,
 * and saving that section alone (the profile form saves every field at once). Notices and the
 * associated schools save through the teacher's portal endpoints, which already answer in JSON.
 */
class EducatorInlineEditController extends Controller
{
    /** Sections with a popup form; the first five save here */
    public const SECTIONS = ['overview', 'about', 'subjects', 'experience', 'achievements', 'notices', 'affiliations', 'fees', 'materials'];

    public function form(string $slug, string $section): Response
    {
        abort_unless(in_array($section, self::SECTIONS, true), 404);
        $educator = $this->ownedEducatorOrAbort($slug);

        if ($section === 'notices') {
            $educator->load(['notices' => fn ($query) => $query->latest()]);
        }
        if ($section === 'affiliations') {
            $educator->load(['instituteAffiliations' => fn ($query) => $query->orderBy('sort_order')->with('institute.user:id,role')]);
        }

        return response(view('frontend.educator.partials.inline.'.$section.'-form', [
            'educator' => $educator,
        ])->render());
    }

    public function update(Request $request, string $slug, string $section): JsonResponse
    {
        $educator = $this->ownedEducatorOrAbort($slug);

        $message = match ($section) {
            'overview' => $this->saveOverview($request, $educator),
            'about' => $this->saveAbout($request, $educator),
            'subjects' => $this->saveSubjects($request, $educator),
            'experience' => $this->saveExperience($request, $educator),
            'achievements' => $this->saveAchievements($request, $educator),
            default => abort(404),
        };

        return response()->json(['message' => $message]);
    }

    /** Headline, photo, the two numbers, teaching modes and languages (the top of the page) */
    private function saveOverview(Request $request, Educator $educator): string
    {
        $validated = $request->validate([
            'professional_headline' => ['nullable', 'string', 'max:255'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'students_taught' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'teaching_modes' => ['nullable', 'string', 'max:2000'],
            'languages' => ['nullable', 'string', 'max:2000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'profile_photo.image' => 'The photo must be an image.',
            'profile_photo.mimes' => 'The photo must be JPG, PNG or WebP.',
            'profile_photo.max' => 'The photo can be up to 2 MB.',
        ]);

        $educator->fill([
            'professional_headline' => trim((string) ($validated['professional_headline'] ?? '')) ?: null,
            'years_experience' => (int) ($validated['years_experience'] ?? 0),
            'students_taught' => (int) ($validated['students_taught'] ?? 0),
            'teaching_modes' => EducatorProfileLists::strings($validated['teaching_modes'] ?? ''),
            'languages' => EducatorProfileLists::strings($validated['languages'] ?? ''),
        ]);

        if ($request->hasFile('profile_photo')) {
            EducatorFileUploader::deleteIfExists($educator->profile_photo);
            $path = EducatorFileUploader::storeImage($request->file('profile_photo'), 'photos');
            $educator->profile_photo = $path;
            $educator->user?->forceFill(['profile_image' => $path])->save();
        }

        $educator->save();

        return 'Profile details updated.';
    }

    private function saveAbout(Request $request, Educator $educator): string
    {
        $validated = $request->validate([
            'about' => ['nullable', 'string', 'max:10000'],
            'teaching_method' => ['nullable', 'string', 'max:255'],
        ]);

        $about = trim((string) ($validated['about'] ?? ''));
        $educator->update([
            'about' => $about !== '' ? $about : null,
            'tagline' => Educator::excerptFromAbout($about !== '' ? $about : null),
            'teaching_method' => trim((string) ($validated['teaching_method'] ?? '')) ?: null,
        ]);

        return 'About Me updated.';
    }

    private function saveSubjects(Request $request, Educator $educator): string
    {
        $validated = $request->validate([
            'subjects' => ['nullable', 'array', 'max:30'],
            'subjects.*.name' => ['nullable', 'string', 'max:120'],
            'subjects.*.classes_lines' => ['nullable', 'string', 'max:2000'],
            'subjects.*.boards_lines' => ['nullable', 'string', 'max:2000'],
            'subjects.*.years_experience' => ['nullable', 'string', 'max:20'],
        ]);

        $subjects = EducatorSubjects::fromFormSubmission($validated['subjects'] ?? []);
        $educator->update([
            'subjects' => $subjects,
            'classes' => EducatorSubjects::aggregateClasses($subjects),
            'boards' => EducatorSubjects::aggregateBoards($subjects),
        ]);

        return 'Subjects & classes updated.';
    }

    private function saveExperience(Request $request, Educator $educator): string
    {
        $validated = $request->validate([
            'experiences' => ['nullable', 'array', 'max:30'],
            'experiences.*.title' => ['nullable', 'string', 'max:255'],
            'experiences.*.organization' => ['nullable', 'string', 'max:255'],
            'experiences.*.start_year' => ['nullable', 'digits:4'],
            'experiences.*.end_year' => ['nullable', 'digits:4'],
            'experiences.*.is_current' => ['nullable', 'boolean'],
            'experiences.*.description' => ['nullable', 'string', 'max:1000'],
            'qualifications' => ['nullable', 'array', 'max:30'],
            'qualifications.*.degree' => ['nullable', 'string', 'max:255'],
            'qualifications.*.institution' => ['nullable', 'string', 'max:255'],
            'qualifications.*.year' => ['nullable', 'string', 'max:20'],
        ], [
            'experiences.*.start_year.digits' => 'A start year is 4 digits, e.g. 2018.',
            'experiences.*.end_year.digits' => 'An end year is 4 digits, e.g. 2022.',
        ]);

        $educator->update([
            'experiences' => EducatorProfileLists::experiences($validated['experiences'] ?? []),
            'qualifications' => EducatorProfileLists::rows($validated['qualifications'] ?? [], ['degree', 'institution', 'year']),
        ]);

        return 'Experience & education updated.';
    }

    private function saveAchievements(Request $request, Educator $educator): string
    {
        $validated = $request->validate([
            'achievements' => ['nullable', 'string', 'max:10000'],
            'certifications' => ['nullable', 'string', 'max:10000'],
        ]);

        $educator->update([
            'achievements' => EducatorProfileLists::strings($validated['achievements'] ?? ''),
            'certifications' => EducatorProfileLists::strings($validated['certifications'] ?? ''),
        ]);

        return 'Achievements & certifications updated.';
    }

    /** The approved profile at this address, when the signed-in teacher / tutor owns it */
    private function ownedEducatorOrAbort(string $slug): Educator
    {
        $educator = Educator::query()->approved()->where('slug', $slug)->with('user')->firstOrFail();

        $user = auth()->user();
        abort_unless($user && (int) $user->id === (int) $educator->user_id && $user->isTeacher(), 403);

        return $educator;
    }
}
