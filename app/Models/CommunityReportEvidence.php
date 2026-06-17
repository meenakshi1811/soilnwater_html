<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityReportEvidence extends Model
{
    protected $table = 'community_report_evidence';

    protected $fillable = [
        'community_post_id',
        'user_id',
        'path',
        'url',
        'name',
        'type',
        'note',
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
