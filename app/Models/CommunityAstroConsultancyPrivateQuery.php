<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityAstroConsultancyPrivateQuery extends Model
{
    protected $fillable = [
        'community_post_id',
        'user_id',
        'query_type',
        'name',
        'email',
        'mobile',
        'message',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
