<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\EducatorNotice;
use App\Support\EducatorFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducatorNoticeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $educator = $request->user()->educator;
        abort_unless($educator, 403);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:5000'],
            'expires_at' => ['required', 'date', 'after_or_equal:'.now()->toDateString()],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = EducatorFileUploader::storeImage($request->file('image'), 'notices');
        }

        $notice = $educator->notices()->create($validated);

        return response()->json([
            'message' => 'Notice published successfully.',
            'notice' => $this->mapNotice($notice),
            'item_html' => view('backend.educator.partials.notice-board-item', [
                'notice' => $notice,
            ])->render(),
        ]);
    }

    public function destroy(Request $request, EducatorNotice $notice): JsonResponse
    {
        $educator = $request->user()->educator;
        abort_unless($educator && (int) $notice->educator_id === (int) $educator->id, 403);

        $noticeId = $notice->id;
        EducatorFileUploader::deleteIfExists($notice->image);
        $notice->delete();

        return response()->json([
            'message' => 'Notice removed successfully.',
            'notice_id' => $noticeId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapNotice(EducatorNotice $notice): array
    {
        return [
            'id' => $notice->id,
            'title' => $notice->displayTitle(),
            'message' => $notice->message,
            'expires_at' => $notice->expires_at?->format('d M Y'),
            'expires_at_iso' => $notice->expires_at?->toDateString(),
            'is_expired' => $notice->isExpired(),
            'created_at' => $notice->created_at?->format('d M Y'),
        ];
    }
}
