<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteEngagement extends Model
{
    public const ACTION_BROCHURE_DOWNLOAD = 'brochure_download';

    public const ACTION_FOLLOW = 'follow';

    public const ACTION_BOOKMARK = 'bookmark';

    public const ACTION_COMPARE_ADD = 'compare_add';

    protected $fillable = [
        'institute_id',
        'user_id',
        'action',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
