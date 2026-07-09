<?php

namespace App\Events\Discussion;

use App\Models\DiscussionTopic;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopicCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DiscussionTopic $topic) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('discussion'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'topic.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->topic->loadMissing('user');

        return [
            'topic' => $this->topic->toBroadcastArray(),
        ];
    }
}
