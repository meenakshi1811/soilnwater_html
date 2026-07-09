<?php

namespace App\Events\Discussion;

use App\Models\DiscussionReply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReplyCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DiscussionReply $reply) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('discussion'),
            new PrivateChannel('discussion.topic.'.$this->reply->discussion_topic_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reply.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->reply->loadMissing('user');

        return [
            'reply' => $this->reply->toBroadcastArray(),
        ];
    }
}
