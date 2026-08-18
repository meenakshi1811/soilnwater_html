<?php

namespace App\Http\Controllers\Discussion;

use App\Events\Discussion\ReplyCreated;
use App\Http\Controllers\Controller;
use App\Models\DiscussionReply;
use App\Models\DiscussionTopic;
use App\Services\DiscussionReadService;
use App\Services\FoulWordFilter;
use App\Support\DiscussionAttachments;
use App\Support\DiscussionFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class DiscussionReplyController extends Controller
{
    public function __construct(
        private DiscussionReadService $readService,
        private FoulWordFilter $foulWordFilter,
    ) {}

    public function store(Request $request, DiscussionTopic $topic): JsonResponse|RedirectResponse
    {
        $this->authorize('reply', $topic);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:discussion_replies,id'],
            'attachments' => ['nullable', 'array', 'max:4'],
            'attachments.*' => ['file', DiscussionAttachments::validationMimesRule(), 'max:10240'],
        ]);

        $body = trim((string) ($data['body'] ?? ''));
        $this->foulWordFilter->assertCleanFields([
            'body' => $body,
        ]);
        $attachments = $this->storeAttachments($request->file('attachments', []));

        if ($body === '' && $attachments === []) {
            throw ValidationException::withMessages([
                'body' => ['Please enter a message or attach a file.'],
            ]);
        }

        if (! empty($data['parent_id'])) {
            $parent = DiscussionReply::query()->findOrFail($data['parent_id']);
            abort_unless($parent->discussion_topic_id === $topic->id, 422);
        }

        $reply = DiscussionReply::query()->create([
            'discussion_topic_id' => $topic->id,
            'user_id' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $body !== '' ? $body : null,
            'attachments' => $attachments ?: null,
        ]);

        $topic->increment('replies_count');

        $reply->load('user');
        $this->readService->markAsRead($request->user(), $topic);

        ReplyCreated::dispatch($reply);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Reply posted.',
                'reply' => $reply->toBroadcastArray(),
            ]);
        }

        return back()->with('success', 'Reply posted.');
    }

    /**
     * @param  list<UploadedFile>  $files
     * @return list<array<string, mixed>>
     */
    private function storeAttachments(array $files): array
    {
        return DiscussionFileUploader::storeMany($files, 'replies');
    }
}
