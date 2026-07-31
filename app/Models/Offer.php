<?php

namespace App\Models;

use App\Support\SocialShare;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'approval_status',
        'approval_reviewed_at',
        'approval_reviewed_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'location_lat' => 'float',
        'location_lng' => 'float',
        'approval_reviewed_at' => 'datetime',
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

    public function reports(): HasMany
    {
        return $this->hasMany(OfferReport::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function shareUrl(): string
    {
        return SocialShare::normalizeUrl(route('frontend.offers.show', $this));
    }

    public function seoTitle(): string
    {
        return $this->title.' | SoilnWater Offers Market';
    }

    public function seoDescription(): string
    {
        return filled($this->short_description)
            ? (string) $this->short_description
            : 'Special limited-time offer available now.';
    }

    /**
     * @return array{url: string, width: ?int, height: ?int, path: ?string}
     */
    public function openGraphImage(): array
    {
        return SocialShare::openGraphImageFromPublicPath($this->banner_image);
    }

    public function seoImageUrl(): string
    {
        return $this->openGraphImage()['url'];
    }
}
