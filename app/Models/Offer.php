<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'subcategory_id',
        'title',
        'discount_tag',
        'coupon_code',
        'valid_until',
        'banner_image',
        'short_description',
        'location',
        'location_lat',
        'location_lng',
        'status',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'location_lat' => 'float',
        'location_lng' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
