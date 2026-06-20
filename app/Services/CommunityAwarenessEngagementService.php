<?php

namespace App\Services;

use App\Models\CommunityAwarenessPledge;
use App\Models\CommunityAwarenessSupport;
use App\Models\CommunityAwarenessVolunteer;
use App\Models\CommunityPost;

class CommunityAwarenessEngagementService
{
    /**
     * @return array<string, mixed>
     */
    public static function stateForPost(CommunityPost $post, ?int $userId = null): array
    {
        $post->loadCount(['awarenessSupports', 'awarenessPledges', 'awarenessVolunteers']);

        $userPledge = null;
        $userSupported = false;

        if ($userId !== null) {
            $userSupported = CommunityAwarenessSupport::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();

            $userPledge = CommunityAwarenessPledge::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->value('pledge_text');
        }

        return [
            'supports_count' => (int) ($post->awareness_supports_count ?? 0),
            'pledges_count' => (int) ($post->awareness_pledges_count ?? 0),
            'volunteers_count' => (int) ($post->awareness_volunteers_count ?? 0),
            'user_supported' => $userSupported,
            'user_pledge' => $userPledge,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function pledgeCounts(CommunityPost $post): array
    {
        return CommunityAwarenessPledge::query()
            ->where('community_post_id', $post->id)
            ->selectRaw('pledge_text, count(*) as total')
            ->groupBy('pledge_text')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => [
                'pledge_text' => (string) $row->pledge_text,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return array{
     *     supports: \Illuminate\Support\Collection,
     *     pledges: \Illuminate\Support\Collection,
     *     volunteers: \Illuminate\Support\Collection
     * }
     */
    public static function activityForPost(CommunityPost $post, int $limit = 15): array
    {
        return [
            'supports' => $post->awarenessSupports()
                ->with('user:id,name,full_name,email')
                ->latest()
                ->limit($limit)
                ->get(),
            'pledges' => $post->awarenessPledges()
                ->with('user:id,name,full_name,email')
                ->latest()
                ->limit($limit)
                ->get(),
            'volunteers' => $post->awarenessVolunteers()
                ->with('user:id,name,full_name,email')
                ->latest()
                ->limit($limit)
                ->get(),
        ];
    }
}
