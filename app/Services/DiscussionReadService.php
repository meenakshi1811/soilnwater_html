<?php

namespace App\Services;

use App\Models\DiscussionTopic;
use App\Models\DiscussionTopicRead;
use App\Models\User;
use Illuminate\Support\Collection;

class DiscussionReadService
{
    public function markAsRead(User $user, DiscussionTopic $topic): void
    {
        DiscussionTopicRead::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'discussion_topic_id' => $topic->id,
            ],
            [
                'last_read_at' => now(),
            ]
        );
    }

    public function unreadCountForTopic(User $user, DiscussionTopic $topic): int
    {
        $lastRead = DiscussionTopicRead::query()
            ->where('user_id', $user->id)
            ->where('discussion_topic_id', $topic->id)
            ->value('last_read_at');

        $replyQuery = $topic->replies()
            ->where('user_id', '!=', $user->id);

        if ($lastRead) {
            $replyQuery->where('created_at', '>', $lastRead);
        }

        $count = (int) $replyQuery->count();

        if (! $lastRead && $topic->user_id !== $user->id) {
            $count += 1;
        }

        return $count;
    }

    /**
     * @param  Collection<int, DiscussionTopic>  $topics
     * @return array<int, int>
     */
    public function unreadCountsForTopics(User $user, Collection $topics): array
    {
        if ($topics->isEmpty()) {
            return [];
        }

        $reads = DiscussionTopicRead::query()
            ->where('user_id', $user->id)
            ->whereIn('discussion_topic_id', $topics->pluck('id'))
            ->pluck('last_read_at', 'discussion_topic_id');

        $counts = [];

        foreach ($topics as $topic) {
            $lastRead = $reads->get($topic->id);

            $replyQuery = $topic->replies()
                ->where('user_id', '!=', $user->id);

            if ($lastRead) {
                $replyQuery->where('created_at', '>', $lastRead);
            }

            $count = (int) $replyQuery->count();

            if (! $lastRead && $topic->user_id !== $user->id) {
                $count += 1;
            }

            $counts[$topic->id] = $count;
        }

        return $counts;
    }

    /**
     * @param  Collection<int, DiscussionTopic>  $topics
     * @return array<int, int>
     */
    public function unreadCountsForRootTopics(User $user, Collection $topics): array
    {
        if ($topics->isEmpty()) {
            return [];
        }

        $counts = [];

        foreach ($topics as $topic) {
            if ($topic->is_group) {
                $children = $topic->children()->get(['id', 'user_id', 'created_at']);
                $counts[$topic->id] = array_sum($this->unreadCountsForTopics($user, $children));

                continue;
            }

            $counts[$topic->id] = $this->unreadCountForTopic($user, $topic);
        }

        return $counts;
    }

    public function globalUnreadCount(User $user): int
    {
        $topics = DiscussionTopic::query()
            ->visibleTo($user)
            ->where(function ($query): void {
                $query->where(function ($publicQuery): void {
                    $publicQuery->where('is_group', false)
                        ->whereNull('parent_topic_id');
                })->orWhereNotNull('parent_topic_id');
            })
            ->get(['id', 'user_id', 'created_at']);

        return array_sum($this->unreadCountsForTopics($user, $topics));
    }

    /**
     * @return array{global_unread: int, topics: array<int, int>}
     */
    public function unreadSummary(User $user): array
    {
        $roots = DiscussionTopic::query()
            ->visibleTo($user)
            ->rootOnly()
            ->get(['id', 'user_id', 'created_at', 'is_group']);
        $counts = $this->unreadCountsForRootTopics($user, $roots);

        return [
            'global_unread' => $this->globalUnreadCount($user),
            'topics' => $counts,
        ];
    }
}
