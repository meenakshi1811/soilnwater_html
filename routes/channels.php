<?php

use App\Models\DiscussionTopic;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('discussion', function ($user) {
    return $user !== null;
});

Broadcast::channel('discussion.topic.{topicId}', function ($user, $topicId) {
    if ($user === null) {
        return false;
    }

    $topic = DiscussionTopic::query()->find($topicId);

    return $topic?->canAccess($user) ?? false;
});
