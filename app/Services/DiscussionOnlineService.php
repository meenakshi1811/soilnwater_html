<?php

namespace App\Services;

use App\Models\DiscussionTopic;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiscussionOnlineService
{
    public function onlineThresholdMinutes(): int
    {
        return 5;
    }

    /**
     * @return list<int>
     */
    public function onlineUserIds(): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        $threshold = now()->subMinutes($this->onlineThresholdMinutes())->getTimestamp();

        return DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $threshold)
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public function relevantUserIdsForTopic(DiscussionTopic $topic): array
    {
        if ($topic->isGroupContainer()) {
            return $this->groupMemberUserIds($topic);
        }

        if ($topic->parent_topic_id) {
            $topic->loadMissing('parent.members');

            return $this->groupMemberUserIds($topic->parent);
        }

        $replyUserIds = $topic->relationLoaded('replies')
            ? $topic->replies->pluck('user_id')
            : $topic->replies()->pluck('user_id');

        return collect([$topic->user_id])
            ->merge($replyUserIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, initials: string}>
     */
    public function onlineUsersForTopic(DiscussionTopic $topic): array
    {
        $onlineIds = $this->onlineUserIds();
        $relevantIds = $this->relevantUserIdsForTopic($topic);

        if ($relevantIds === [] || $onlineIds === []) {
            return [];
        }

        $matchedIds = array_values(array_intersect($relevantIds, $onlineIds));

        if ($matchedIds === []) {
            return [];
        }

        return User::query()
            ->whereIn('id', $matchedIds)
            ->orderBy('name')
            ->get(['id', 'name', 'full_name'])
            ->map(fn (User $user) => $this->summarizeUser($user))
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, initials: string, is_online: bool}>
     */
    public function membersWithOnlineStatus(DiscussionTopic $group): array
    {
        abort_unless($group->isGroupContainer(), 404);

        $onlineIds = $this->onlineUserIds();
        $group->loadMissing(['members', 'user']);

        $members = collect($group->memberSummaries())
            ->map(fn (array $member) => array_merge($member, [
                'is_online' => in_array((int) $member['id'], $onlineIds, true),
            ]))
            ->values()
            ->all();

        usort($members, function (array $a, array $b): int {
            if ($a['is_online'] !== $b['is_online']) {
                return $a['is_online'] ? -1 : 1;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return $members;
    }

    /**
     * @return list<int>
     */
    private function groupMemberUserIds(DiscussionTopic $group): array
    {
        $group->loadMissing('members');

        return collect([$group->user_id])
            ->merge($group->members->pluck('id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, name: string, initials: string}
     */
    private function summarizeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->authorDisplayName(),
            'initials' => $user->authorInitials(),
        ];
    }
}
