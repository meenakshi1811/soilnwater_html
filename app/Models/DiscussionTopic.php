<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionTopic extends Model
{
    /** @use HasFactory<DiscussionTopicFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'attachments',
        'is_pinned',
        'pinned_by',
        'pinned_at',
        'replies_count',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
            'replies_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pinnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class)->oldest();
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(DiscussionReaction::class, 'reactable');
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

    public function displayAuthorName(): string
    {
        return $this->user?->name
            ?? $this->user?->full_name
            ?? 'Community member';
    }

    /**
     * @return array<string, mixed>
     */
    public function toBroadcastArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'attachments' => $this->attachments ?? [],
            'is_pinned' => $this->is_pinned,
            'replies_count' => $this->replies_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
            'author' => [
                'id' => $this->user_id,
                'name' => $this->displayAuthorName(),
            ],
            'reaction_counts' => $this->reactionCounts(),
            'url' => route('discussions.show', $this),
        ];
    }
}
