<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPostParticipation extends Model
{
    public const TYPE_SUGGESTION = 'suggestion';

    public const TYPE_FEEDBACK = 'feedback';

    protected $fillable = [
        'community_post_id',
        'user_id',
        'type',
        'body',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_SUGGESTION => 'Suggestion',
            self::TYPE_FEEDBACK => 'Feedback',
            default => ucfirst((string) $this->type),
        };
    }
}
