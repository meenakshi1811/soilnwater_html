<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProviderPageSection extends Model
{
    protected $fillable = [
        'service_provider_id',
        'title',
        'content',
        'image_path',
        'sort_order',
    ];

    public function service_provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
