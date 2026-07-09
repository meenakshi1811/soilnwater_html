<?php

namespace App\Policies;

use App\Models\DiscussionTopic;
use App\Models\User;

class DiscussionTopicPolicy
{
    public function pin(User $user, DiscussionTopic $topic): bool
    {
        return $user->isAdmin();
    }
}
