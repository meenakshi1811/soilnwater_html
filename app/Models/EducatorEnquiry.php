<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducatorEnquiry extends Model
{
    protected $fillable = [
        'educator_id',
        'user_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
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
