<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Models\InstituteNotice;
use App\Support\InstituteFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstituteNoticeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $institute = $request->user()->institute;
        abort_unless($institute, 403);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:5000'],
            'expires_at' => ['required', 'date', 'after_or_equal:'.now()->toDateString()],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = InstituteFileUploader::storeImage($request->file('image'), 'notices');
        }

        $notice = $institute->notices()->create($validated);

        return response()->json([
            'message' => 'Notice published successfully.',
            'item_html' => view('backend.institute.partials.notice-item', compact('notice'))->render(),
        ]);
    }

    public function destroy(Request $request, InstituteNotice $notice): JsonResponse
    {
        $institute = $request->user()->institute;
        abort_unless($institute && (int) $notice->institute_id === (int) $institute->id, 403);

        $noticeId = $notice->id;
        InstituteFileUploader::deleteIfExists($notice->image);
        $notice->delete();

        return response()->json([
            'message' => 'Notice removed successfully.',
            'id' => $noticeId,
        ]);
    }
}
