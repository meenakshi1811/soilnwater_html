<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DiscussionReply extends Model
{
    protected $fillable = [
        'discussion_topic_id',
        'user_id',
        'parent_id',
        'body',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(DiscussionTopic::class, 'discussion_topic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(DiscussionReaction::class, 'reactable');
    }

    public function displayAuthorName(): string
    {
        return $this->user?->name
            ?? $this->user?->full_name
            ?? 'Community member';
    }

    /**
     * @return array<string, int>
     */
    public function reactionCounts(): array
    {
        return $this->reactions()
            ->selectRaw('reaction, count(*) as total')
            ->groupBy('reaction')
            ->pluck('total', 'reaction')
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function toBroadcastArray(): array
    {
        return [
            'id' => $this->id,
            'discussion_topic_id' => $this->discussion_topic_id,
            'body' => $this->body,
            'attachments' => $this->attachments ?? [],
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
            'created_at_date' => $this->created_at?->format('d M Y'),
            'created_at_time' => $this->created_at?->format('h:i A'),
            'author' => [
                'id' => $this->user_id,
                'name' => $this->displayAuthorName(),
            ],
            'reaction_counts' => $this->reactionCounts(),
        ];
    }
}
