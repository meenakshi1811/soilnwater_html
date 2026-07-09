<?php

namespace App\Events\Discussion;

use App\Models\DiscussionReply;
use App\Models\DiscussionTopic;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, int>  $counts
     */
    public function __construct(
        public Model $reactable,
        public string $reaction,
        public bool $active,
        public array $counts,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('discussion')];

        if ($this->reactable instanceof DiscussionReply) {
            $channels[] = new PrivateChannel('discussion.topic.'.$this->reactable->discussion_topic_id);
        } elseif ($this->reactable instanceof DiscussionTopic) {
            $channels[] = new PrivateChannel('discussion.topic.'.$this->reactable->id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'reaction.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'reactable_type' => class_basename($this->reactable),
            'reactable_id' => $this->reactable->getKey(),
            'topic_id' => $this->reactable instanceof DiscussionReply
                ? $this->reactable->discussion_topic_id
                : $this->reactable->getKey(),
            'reaction' => $this->reaction,
            'active' => $this->active,
            'counts' => $this->counts,
        ];
    }
}
