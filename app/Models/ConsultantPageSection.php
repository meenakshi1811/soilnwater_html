<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantPageSection extends Model
{
    protected $fillable = [
        'consultant_id',
        'title',
        'content',
        'image_path',
        'sort_order',
    ];

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }
}
