<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPostAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'community_post_id',
        'user_id',
        'action',
        'ip_address',
        'changes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'created' => 'Created',
            'updated' => 'Updated',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
