<?php

namespace App\Services;

use App\Models\CommunityLocalVoiceFollow;
use App\Models\CommunityLocalVoiceSupport;
use App\Models\CommunityPost;

class CommunityLocalVoiceEngagementService
{
    /**
     * @return array<string, mixed>
     */
    public static function stateForPost(CommunityPost $post, ?int $userId = null): array
    {
        $post->loadCount(['localVoiceSupports', 'localVoiceFollows']);

        $userSupported = false;
        $userFollowing = false;

        if ($userId !== null) {
            $userSupported = CommunityLocalVoiceSupport::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();

            $userFollowing = CommunityLocalVoiceFollow::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();
        }

        return [
            'supports_count' => (int) ($post->local_voice_supports_count ?? 0),
            'follows_count' => (int) ($post->local_voice_follows_count ?? 0),
            'user_supported' => $userSupported,
            'user_following' => $userFollowing,
        ];
    }
}
