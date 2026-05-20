<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserAd extends Model
{
    protected $fillable = [
        'user_id',
        'ad_template_id',
        'size_type',
        'title',
        'short_description',
        'category_id',
        'subcategory_id',
        'selected_modules',
        'location',
        'location_lat',
        'location_lng',
        'status',
        'fields_json',
        'rendered_html',
        'final_image',
        'valid_until',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'is_sponsored',
        'base_price_per_day',
        'total_days',
        'subtotal',
        'gst_rate',
        'gst_amount',
        'grand_total',
    ];

    protected $casts = [
        'fields_json' => 'array',
        'location_lat' => 'float',
        'location_lng' => 'float',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'valid_until' => 'date',
        'selected_modules' => 'array',
        'is_sponsored' => 'boolean',
        'base_price_per_day' => 'decimal:2',
        'total_days' => 'integer',
        'subtotal' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];


    protected static function booted(): void
    {
        static::updating(function (UserAd $ad): void {
            $editableFields = [
                'title',
                'short_description',
                'category_id',
                'subcategory_id',
                'location',
                'location_lat',
                'location_lng',
                'fields_json',
                'rendered_html',
                'final_image',
                'valid_until',
                'is_sponsored',
                'base_price_per_day',
                'total_days',
                'subtotal',
                'gst_rate',
                'gst_amount',
                'grand_total',
            ];

            $requiresReapproval = $ad->getOriginal('status') === 'approved'
                && collect($editableFields)->contains(fn (string $field) => $ad->isDirty($field));

            if (! $requiresReapproval) {
                return;
            }

            $ad->status = 'pending';
            $ad->reviewed_by = null;
            $ad->reviewed_at = null;
            $ad->review_note = null;
            $ad->submitted_at = now();
        });
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AdTemplate::class, 'ad_template_id');
    }

    public function adSize(): BelongsTo
    {
        return $this->belongsTo(AdSize::class, 'size_type', 'size_key');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(AdReport::class, 'user_ad_id');
    }
}
