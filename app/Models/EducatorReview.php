<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducatorReview extends Model
{
    protected $fillable = [
        'educator_id',
        'user_id',
        'student_name',
        'student_class',
        'rating',
        'review',
    ];

    public function educator(): BelongsTo
    {
        return $this->belongsTo(Educator::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
