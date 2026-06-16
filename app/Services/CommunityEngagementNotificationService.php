<?php

namespace App\Services;

use App\Mail\CommunityPostSubscriptionMail;
use App\Models\CommunityCategorySubscription;
use App\Models\CommunityPost;
use App\Models\CommunityTopicFollow;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class CommunityEngagementNotificationService
{
    public static function notifySubscribersOfPublishedPost(CommunityPost $post): void
    {
        if (! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        $recipients = self::matchingSubscribers($post)
            ->filter(fn (User $user): bool => $user->id !== $post->user_id)
            ->unique('id');

        foreach ($recipients as $user) {
            self::notifyUser($user, $post);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private static function matchingSubscribers(CommunityPost $post): Collection
    {
        $categorySubscriberIds = CommunityCategorySubscription::query()
            ->where('content_type', $post->content_type)
            ->where('category', $post->category)
            ->pluck('user_id');

        $tags = collect($post->tags ?? [])
            ->map(fn (mixed $tag): string => CommunityTopicFollow::normalizeTopic((string) $tag))
            ->filter()
            ->unique()
            ->values();

        $topicSubscriberIds = $tags->isEmpty()
            ? collect()
            : CommunityTopicFollow::query()
                ->whereIn('topic', $tags)
                ->pluck('user_id');

        $userIds = $categorySubscriberIds
            ->merge($topicSubscriberIds)
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->get();
    }

    private static function notifyUser(User $user, CommunityPost $post): void
    {
        $title = 'New community post for you';
        $message = '"'.$post->title.'" was published in a category or topic you follow.';
        $url = route('community.show', $post);

        PortalNotificationService::notifyUser($user, $title, $message, $url, 'community');

        if (! filled($user->email)) {
            return;
        }

        Mail::to($user->email)->send(new CommunityPostSubscriptionMail($post, $user));
    }
}
