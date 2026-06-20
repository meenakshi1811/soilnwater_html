<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\CommunityPostParticipation;
use App\Models\CommunityReportEvidence;
use App\Services\CommunityPostParticipationNotificationService;
use App\Services\CommunityReportTrustScoreService;
use App\Support\CommunityPostFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityPostParticipationController extends Controller
{
    public function storeSuggestion(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeParticipation($post, $request, 'allow_suggestions');

        $data = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        CommunityPostParticipation::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $request->user()->id,
            'type' => CommunityPostParticipation::TYPE_SUGGESTION,
            'body' => $data['body'],
        ]);

        CommunityPostParticipationNotificationService::notifyAuthorOfSuggestion(
            $post,
            $request->user(),
            $data['body']
        );

        return response()->json([
            'message' => 'Your suggestion has been submitted. The author has been notified.',
        ]);
    }

    public function storeFeedback(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeParticipation($post, $request, 'allow_feedback');

        $data = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        CommunityPostParticipation::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $request->user()->id,
            'type' => CommunityPostParticipation::TYPE_FEEDBACK,
            'body' => $data['body'],
        ]);

        CommunityPostParticipationNotificationService::notifyAuthorOfFeedback(
            $post,
            $request->user(),
            $data['body']
        );

        return response()->json([
            'message' => 'Your feedback has been submitted. The author has been notified.',
        ]);
    }

    public function storeEvidence(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeParticipation($post, $request, 'allow_additional_evidence');

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
            'evidence_files' => ['required', 'array', 'min:1', 'max:3'],
            'evidence_files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi,pdf,doc,docx'],
        ]);

        $stored = collect($request->file('evidence_files', []))
            ->map(function ($file) use ($post, $request, $data) {
                $attachment = CommunityPostFileUploader::storeAttachment($file, 'issues/community');

                return CommunityReportEvidence::query()
                    ->create([
                        'community_post_id' => $post->id,
                        'user_id' => $request->user()->id,
                        'path' => $attachment['path'],
                        'url' => $attachment['url'],
                        'name' => $attachment['name'],
                        'type' => $attachment['type'],
                        'note' => filled($data['note'] ?? null) ? $data['note'] : null,
                    ])
                    ->load('user:id,name,full_name');
            })
            ->values();

        CommunityPostParticipationNotificationService::notifyAuthorOfEvidence(
            $post,
            $request->user(),
            $stored->count(),
            $data['note'] ?? null
        );

        $post = CommunityReportTrustScoreService::syncToMeta($post->fresh());

        return response()->json([
            'message' => 'Additional evidence uploaded successfully. The author has been notified.',
            'report_trust_score' => $post->isReportContent() ? $post->reportTrustScore() : null,
            'evidence' => $stored->map(fn (CommunityReportEvidence $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'url' => $item->url,
                'type' => $item->type,
                'note' => $item->note,
                'contributor' => $item->user?->full_name ?: ($item->user?->name ?? 'Community member'),
                'created_at' => $item->created_at?->diffForHumans(),
            ])->all(),
        ]);
    }

    private function authorizeParticipation(CommunityPost $post, Request $request, string $flag): void
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isVisibleInCommunityTo($request->user()), 403, 'This post is not available to your account.');
        abort_unless((bool) $post->{$flag}, 403, 'This participation option is disabled for this post.');
        abort_if($post->user_id === $request->user()->id, 422, 'You cannot submit public participation on your own post.');
    }
}
