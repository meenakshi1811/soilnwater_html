<?php

namespace App\Services;

use App\Models\CommunityEnvironmentFollow;
use App\Models\CommunityEnvironmentSupport;
use App\Models\CommunityEnvironmentVolunteer;
use App\Models\CommunityPost;

class CommunityEnvironmentEngagementService
{
    /**
     * @return array<string, mixed>
     */
    public static function stateForPost(CommunityPost $post, ?int $userId = null): array
    {
        $post->loadCount(['environmentSupports', 'environmentFollows', 'environmentVolunteers']);

        $userSupported = false;
        $userFollowing = false;
        $userVolunteered = false;

        if ($userId !== null) {
            $userSupported = CommunityEnvironmentSupport::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();

            $userFollowing = CommunityEnvironmentFollow::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();

            $userVolunteered = CommunityEnvironmentVolunteer::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();
        }

        return [
            'supports_count' => (int) ($post->environment_supports_count ?? 0),
            'follows_count' => (int) ($post->environment_follows_count ?? 0),
            'volunteers_count' => (int) ($post->environment_volunteers_count ?? 0),
            'user_supported' => $userSupported,
            'user_following' => $userFollowing,
            'user_volunteered' => $userVolunteered,
        ];
    }

    /**
     * @return array{
     *     supports: \Illuminate\Support\Collection,
     *     follows: \Illuminate\Support\Collection,
     *     volunteers: \Illuminate\Support\Collection
     * }
     */
    public static function activityForPost(CommunityPost $post, int $limit = 15): array
    {
        return [
            'supports' => $post->environmentSupports()
                ->with('user:id,name,full_name,email')
                ->latest()
                ->limit($limit)
                ->get(),
            'follows' => $post->environmentFollows()
                ->with('user:id,name,full_name,email')
                ->latest()
                ->limit($limit)
                ->get(),
            'volunteers' => $post->environmentVolunteers()
                ->with('user:id,name,full_name,email')
                ->latest()
                ->limit($limit)
                ->get(),
        ];
    }
}
