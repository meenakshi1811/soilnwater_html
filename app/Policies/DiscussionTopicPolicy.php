<?php

namespace App\Policies;

use App\Models\DiscussionTopic;
use App\Models\User;

class DiscussionTopicPolicy
{
    public function view(User $user, DiscussionTopic $topic): bool
    {
        return $topic->canAccess($user);
    }

    public function reply(User $user, DiscussionTopic $topic): bool
    {
        return $topic->canAccess($user);
    }

    public function manageMembers(User $user, DiscussionTopic $topic): bool
    {
        return $topic->isGroupContainer() && $topic->isOwner($user);
    }

    public function pin(User $user, DiscussionTopic $topic): bool
    {
        return $user->isAdmin();
    }

    public function createInGroup(User $user, DiscussionTopic $group): bool
    {
        return $group->is_group
            && $group->parent_topic_id === null
            && $group->canAccess($user);
    }
}
