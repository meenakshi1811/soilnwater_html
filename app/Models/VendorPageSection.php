<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPageSection extends Model
{
    protected $fillable = [
        'vendor_id',
        'title',
        'content',
        'image_path',
        'sort_order',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
