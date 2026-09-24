<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducatorInstituteAffiliation extends Model
{
    protected $fillable = [
        'educator_id',
        'institute_id',
        'role_title',
        'subject',
        'sort_order',
    ];

    public function educator(): BelongsTo
    {
        return $this->belongsTo(Educator::class);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }
}
