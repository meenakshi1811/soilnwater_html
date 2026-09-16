<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteEnquiry extends Model
{
    protected $fillable = [
        'institute_id',
        'user_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
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
