<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdReport extends Model
{
    protected $fillable = [
        'user_ad_id',
        'reported_by',
        'reason',
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(UserAd::class, 'user_ad_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
