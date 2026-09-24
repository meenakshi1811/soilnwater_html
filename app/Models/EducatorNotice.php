<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducatorNotice extends Model
{
    protected $fillable = [
        'educator_id',
        'title',
        'message',
        'image',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
        ];
    }

    public function educator(): BelongsTo
    {
        return $this->belongsTo(Educator::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereDate('expires_at', '>=', now()->toDateString());
    }

    public function displayTitle(): string
    {
        $title = trim((string) $this->title);

        if ($title !== '') {
            return $title;
        }

        return 'Notice';
    }

    public function imageUrl(): ?string
    {
        return filled($this->image) ? asset($this->image) : null;
    }

    public function hasImage(): bool
    {
        return filled($this->image);
    }

    public function excerpt(int $limit = 180): string
    {
        $message = trim(strip_tags((string) $this->message));

        if (mb_strlen($message) <= $limit) {
            return $message;
        }

        return rtrim(mb_substr($message, 0, $limit)).'…';
    }

    public function needsReadMore(int $limit = 180): bool
    {
        return mb_strlen(trim(strip_tags((string) $this->message))) > $limit;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lt(now()->startOfDay());
    }
}
