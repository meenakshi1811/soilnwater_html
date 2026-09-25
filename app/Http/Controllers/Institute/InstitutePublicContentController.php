<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Models\InstituteAchievement;
use App\Models\InstituteBook;
use App\Models\InstituteClass;
use App\Models\InstituteTopPerformer;
use App\Support\InstituteFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstitutePublicContentController extends Controller
{
    public function storeAchievement(Request $request): JsonResponse
    {
        $institute = $this->instituteOrAbort($request);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['required', 'string', 'max:120'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->achievementMessages());

        $validated['image'] = InstituteFileUploader::storeImage($request->file('image'), 'achievements');

        $achievement = $institute->achievements()->create($validated);

        return response()->json([
            'message' => 'Achievement added successfully.',
            'item_html' => view('backend.institute.partials.achievement-item', compact('achievement'))->render(),
        ]);
    }

    public function destroyAchievement(Request $request, InstituteAchievement $achievement): JsonResponse
    {
        $institute = $this->instituteOrAbort($request);
        abort_unless((int) $achievement->institute_id === (int) $institute->id, 403);

        InstituteFileUploader::deleteIfExists($achievement->image);
        $id = $achievement->id;
        $achievement->delete();

        return response()->json(['message' => 'Achievement removed.', 'id' => $id]);
    }

    public function storePerformer(Request $request): JsonResponse
    {
        $institute = $this->instituteOrAbort($request);

        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'max:120'],
            'achievement_title' => ['required', 'string', 'max:255'],
            'score' => ['required', 'string', 'max:120'],
            'rank' => ['required', 'integer', 'min:1', 'max:1000'],
            'academic_year' => ['required', 'string', 'max:20'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->performerMessages());

        $validated['photo'] = InstituteFileUploader::storeImage($request->file('photo'), 'performers');

        $performer = $institute->topPerformers()->create($validated);

        return response()->json([
            'message' => 'Result added successfully.',
            'item_html' => view('backend.institute.partials.performer-item', compact('performer'))->render(),
        ]);
    }

    public function destroyPerformer(Request $request, InstituteTopPerformer $performer): JsonResponse
    {
        $institute = $this->instituteOrAbort($request);
        abort_unless((int) $performer->institute_id === (int) $institute->id, 403);

        InstituteFileUploader::deleteIfExists($performer->photo);
        $id = $performer->id;
        $performer->delete();

        return response()->json(['message' => 'Result removed.', 'id' => $id]);
    }

    public function storeClass(Request $request): JsonResponse
    {
        $institute = $this->instituteOrAbort($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'section' => ['required', 'string', 'max:40'],
            'class_teacher' => ['required', 'string', 'max:255'],
            'strength' => ['required', 'integer', 'min:1', 'max:500'],
            'room' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
        ], $this->classMessages());

        $class = $institute->schoolClasses()->create($validated);

        return response()->json([
            'message' => 'Class added successfully.',
            'item_html' => view('backend.institute.partials.class-item', compact('class'))->render(),
        ]);
    }

    public function destroyClass(Request $request, InstituteClass $class): JsonResponse
    {
        $institute = $this->instituteOrAbort($request);
        abort_unless((int) $class->institute_id === (int) $institute->id, 403);

        $id = $class->id;
        $class->delete();

        return response()->json(['message' => 'Class removed.', 'id' => $id]);
    }

    public function storeBook(Request $request): JsonResponse
    {
        $institute = $this->instituteOrAbort($request);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:120'],
            'publisher' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:40'],
            'cover_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->bookMessages());

        $validated['cover_image'] = InstituteFileUploader::storeImage($request->file('cover_image'), 'books');

        $book = $institute->books()->create($validated);

        return response()->json([
            'message' => 'Book added successfully.',
            'item_html' => view('backend.institute.partials.book-item', compact('book'))->render(),
        ]);
    }

    public function destroyBook(Request $request, InstituteBook $book): JsonResponse
    {
        $institute = $this->instituteOrAbort($request);
        abort_unless((int) $book->institute_id === (int) $institute->id, 403);

        InstituteFileUploader::deleteIfExists($book->cover_image);
        $id = $book->id;
        $book->delete();

        return response()->json(['message' => 'Book removed.', 'id' => $id]);
    }

    /** @return array<string, string> */
    private function classMessages(): array
    {
        return [
            'name.required' => 'Please enter the class name.',
            'section.required' => 'Please enter the section.',
            'class_teacher.required' => 'Please enter the class teacher name.',
            'strength.required' => 'Please enter the class strength.',
            'room.required' => 'Please enter the room number.',
            'description.required' => 'Please enter a class description.',
        ];
    }

    /** @return array<string, string> */
    private function performerMessages(): array
    {
        return [
            'student_name.required' => 'Please enter the student name.',
            'class_name.required' => 'Please enter the class.',
            'achievement_title.required' => 'Please enter the result or highlight.',
            'score.required' => 'Please enter the score or marks.',
            'rank.required' => 'Please enter the rank.',
            'academic_year.required' => 'Please enter the academic year.',
            'photo.required' => 'Please upload a student photo.',
        ];
    }

    /** @return array<string, string> */
    private function achievementMessages(): array
    {
        return [
            'title.required' => 'Please enter the achievement title.',
            'description.required' => 'Please enter the achievement description.',
            'category.required' => 'Please enter the category.',
            'year.required' => 'Please enter the year.',
            'image.required' => 'Please upload an achievement image.',
        ];
    }

    /** @return array<string, string> */
    private function bookMessages(): array
    {
        return [
            'title.required' => 'Please enter the book title.',
            'author.required' => 'Please enter the author name.',
            'class_name.required' => 'Please enter the class.',
            'subject.required' => 'Please enter the subject.',
            'publisher.required' => 'Please enter the publisher.',
            'isbn.required' => 'Please enter the ISBN.',
            'cover_image.required' => 'Please upload a cover image.',
        ];
    }

    private function instituteOrAbort(Request $request)
    {
        $institute = $request->user()?->institute;
        abort_unless($institute, 403);

        return $institute;
    }
}
