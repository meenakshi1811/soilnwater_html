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
        return $topic->is_group && $topic->isOwner($user);
    }

    public function pin(User $user, DiscussionTopic $topic): bool
    {
        return $user->isAdmin();
    }
}
