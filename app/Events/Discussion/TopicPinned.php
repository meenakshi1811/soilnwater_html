<?php

namespace App\Events\Discussion;

use App\Models\DiscussionTopic;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopicPinned implements ShouldBroadcastNow
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
            new PrivateChannel('discussion.topic.'.$this->topic->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'topic.pinned';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'topic_id' => $this->topic->id,
            'is_pinned' => $this->topic->is_pinned,
        ];
    }
}
