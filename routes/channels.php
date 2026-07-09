<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('discussion', function ($user) {
    return $user !== null;
});

Broadcast::channel('discussion.topic.{topicId}', function ($user) {
    return $user !== null;
});
