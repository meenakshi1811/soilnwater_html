<?php

namespace App\Services;

use App\Models\CommunityBusinessQuery;
use App\Models\CommunityPost;

class CommunityBusinessEngagementService
{
    /**
     * @return array<string, mixed>
     */
    public static function stateForPost(CommunityPost $post, ?int $userId = null): array
    {
        $post->loadCount(['businessQueries']);

        $userSubmitted = false;
        if ($userId !== null) {
            $userSubmitted = CommunityBusinessQuery::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();
        }

        return [
            'queries_count' => (int) ($post->business_queries_count ?? 0),
            'user_submitted' => $userSubmitted,
        ];
    }

    /**
     * @return array{queries: \Illuminate\Support\Collection<int, CommunityBusinessQuery>}
     */
    public static function activityForPost(CommunityPost $post, int $limit = 15): array
    {
        return [
            'queries' => $post->businessQueries()
                ->with('user:id,name,full_name,email')
                ->latest()
                ->limit($limit)
                ->get(),
        ];
    }
}
