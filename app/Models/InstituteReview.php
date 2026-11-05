<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A visitor's review of a school or institute, written on its page */
class InstituteReview extends Model
{
    /** Who the reviewer is to the school */
    public const RELATIONS = ['Parent', 'Student', 'Alumni', 'Other'];

    protected $fillable = [
        'institute_id',
        'user_id',
        'rating',
        'relation',
        'review',
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
