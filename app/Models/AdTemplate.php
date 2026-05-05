<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdTemplate extends Model
{
    protected $fillable = [
        'size_type',
        'name',
        'description',
        'preview_image',
        'layout_html',
        'schema_json',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ads(): HasMany
    {
        return $this->hasMany(UserAd::class, 'ad_template_id');
    }
}

