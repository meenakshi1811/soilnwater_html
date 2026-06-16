<?php

namespace App\Support;

use App\Models\CommunityPost;
use App\Models\CommunityPostAuditLog;
use Illuminate\Http\Request;

class CommunityPostAuditLogger
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function applySubmissionAcceptance(Request $request, array &$payload, bool $isCreate): void
    {
        $now = now();
        $payload['content_responsibility_accepted_at'] = $now;
        $payload['original_work_accepted_at'] = $now;

        if ($isCreate) {
            $payload['submission_ip'] = $request->ip();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function stripAcceptanceFields(array &$payload): void
    {
        unset(
            $payload['accept_content_responsibility'],
            $payload['accept_original_work_indemnity'],
        );
    }

    public static function logCreated(CommunityPost $post, Request $request): void
    {
        CommunityPostAuditLog::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $request->user()->id,
            'action' => 'created',
            'ip_address' => $request->ip(),
            'changes' => [
                'submission_ip' => $post->submission_ip,
                'content_responsibility_accepted_at' => optional($post->content_responsibility_accepted_at)?->toIso8601String(),
                'original_work_accepted_at' => optional($post->original_work_accepted_at)?->toIso8601String(),
                'submitted_at' => optional($post->submitted_at)?->toIso8601String(),
            ],
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $originalAttributes
     */
    public static function logUpdated(CommunityPost $post, Request $request, array $originalAttributes): void
    {
        $changes = [];

        foreach ($post->getChanges() as $field => $newValue) {
            if (in_array($field, ['updated_at', 'created_at'], true)) {
                continue;
            }

            $changes[$field] = [
                'from' => $originalAttributes[$field] ?? null,
                'to' => $newValue,
            ];
        }

        if ($changes === []) {
            return;
        }

        CommunityPostAuditLog::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $request->user()->id,
            'action' => 'updated',
            'ip_address' => $request->ip(),
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }
}
