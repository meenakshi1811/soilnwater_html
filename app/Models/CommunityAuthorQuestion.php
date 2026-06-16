<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityAuthorQuestion extends Model
{
    protected $fillable = [
        'author_id',
        'asked_by',
        'community_post_id',
        'question',
        'answer',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function asker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asked_by');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function scopeAnswered(Builder $query): Builder
    {
        return $query->whereNotNull('answer')->whereNotNull('answered_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('answer');
    }

    public function scopeForAuthor(Builder $query, int $authorId): Builder
    {
        return $query->where('author_id', $authorId);
    }

    public function isAnswered(): bool
    {
        return filled($this->answer) && $this->answered_at !== null;
    }

    public function askerDisplayName(): string
    {
        return $this->asker?->name ?? $this->asker?->full_name ?? 'Community member';
    }

    public function authorDisplayName(): string
    {
        return $this->author?->name ?? $this->author?->full_name ?? 'Community author';
    }

    public function publicUrl(): string
    {
        if ($this->post) {
            return route('community.show', $this->post).'#author-questions';
        }

        return route('community.authors.show', $this->author->authorUniqueName()).'#author-questions';
    }
}
