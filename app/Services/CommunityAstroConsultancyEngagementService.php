<?php

namespace App\Services;

use App\Models\CommunityAstroConsultancyPrivateQuery;
use App\Models\CommunityPost;

class CommunityAstroConsultancyEngagementService
{
    /**
     * @return array<string, mixed>
     */
    public static function stateForPost(CommunityPost $post, ?int $userId = null): array
    {
        $post->loadCount(['astroConsultancyPrivateQueries']);

        $userSubmitted = false;

        if ($userId !== null) {
            $userSubmitted = CommunityAstroConsultancyPrivateQuery::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();
        }

        return [
            'queries_count' => (int) ($post->astro_consultancy_private_queries_count ?? 0),
            'user_submitted' => $userSubmitted,
        ];
    }

    /**
     * @return array{queries: \Illuminate\Support\Collection<int, CommunityAstroConsultancyPrivateQuery>}
     */
    public static function activityForPost(CommunityPost $post, int $limit = 15): array
    {
        return [
            'queries' => $post->astroConsultancyPrivateQueries()
                ->with('user:id,name,full_name,email')
                ->latest()
                ->limit($limit)
                ->get(),
        ];
    }
}
