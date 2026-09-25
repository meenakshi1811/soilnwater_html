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
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:5000'],
            'expires_at' => ['required', 'date', 'after_or_equal:'.now()->toDateString()],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'title.required' => 'Please enter a notice title.',
            'message.required' => 'Please enter the notice message.',
            'expires_at.required' => 'Please select an expiry date.',
            'expires_at.after_or_equal' => 'Expiry date cannot be in the past.',
            'image.required' => 'Please upload a notice image.',
            'image.image' => 'Notice image must be a valid image file.',
            'image.mimes' => 'Notice image must be JPG, PNG, or WebP.',
            'image.max' => 'Notice image cannot exceed 2 MB.',
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
