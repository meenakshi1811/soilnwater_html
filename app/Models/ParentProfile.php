<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'is_enabled',
        'bio',
        'languages',
        'location',
        'profile_completion',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'languages' => 'array',
            'profile_completion' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChildProfile::class, 'parent_user_id', 'user_id');
    }

    public function isEnabled(): bool
    {
        return (bool) $this->is_enabled;
    }

    public function recalculateCompletion(): void
    {
        $user = $this->user;
        $score = 0;

        if ($user && filled($user->profile_image)) {
            $score += 20;
        }
        if (filled($this->bio)) {
            $score += 25;
        }
        if (filled($this->location) || filled($user?->city)) {
            $score += 15;
        }
        if (is_array($this->languages) && count($this->languages) > 0) {
            $score += 15;
        }
        if ($user && filled($user->phone_number)) {
            $score += 10;
        }
        if ($this->children()->where('status', 'approved')->exists()) {
            $score += 15;
        }

        $this->profile_completion = min(100, $score);
        $this->saveQuietly();
    }
}
