<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CommunityTopicFollow extends Model
{
    protected $fillable = [
        'user_id',
        'topic',
    ];

    public static function normalizeTopic(string $topic): string
    {
        return Str::lower(trim($topic));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function displayTopic(): string
    {
        return $this->topic;
    }
}
