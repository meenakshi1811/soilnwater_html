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
            'description' => ['nullable', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:120'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = InstituteFileUploader::storeImage($request->file('image'), 'achievements');
        }

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
            'class_name' => ['nullable', 'string', 'max:120'],
            'achievement_title' => ['required', 'string', 'max:255'],
            'score' => ['nullable', 'string', 'max:120'],
            'rank' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = InstituteFileUploader::storeImage($request->file('photo'), 'performers');
        }

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
            'section' => ['nullable', 'string', 'max:40'],
            'class_teacher' => ['nullable', 'string', 'max:255'],
            'strength' => ['nullable', 'integer', 'min:1', 'max:500'],
            'room' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

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
            'class_name' => ['nullable', 'string', 'max:120'],
            'subject' => ['nullable', 'string', 'max:120'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:40'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = InstituteFileUploader::storeImage($request->file('cover_image'), 'books');
        }

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

    private function instituteOrAbort(Request $request)
    {
        $institute = $request->user()?->institute;
        abort_unless($institute, 403);

        return $institute;
    }
}
