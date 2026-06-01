<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantBannerSlide extends Model
{
    protected $fillable = [
        'consultant_id',
        'image_path',
        'sort_order',
    ];

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }
}
