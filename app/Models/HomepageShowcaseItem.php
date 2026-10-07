<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomepageShowcaseItem extends Model
{
    public const TYPE_FEATURED_BUSINESS = 'featured_business';

    public const TYPE_OFFER_PROMO = 'offer_promo';

    protected $fillable = [
        'type',
        'title',
        'description',
        'image_path',
        'link_url',
        'location',
        'rating',
        'review_count',
        'category_label',
        'category_tone',
        'category_icon',
        'discount_badge',
        'valid_until',
        'headline',
        'subheadline',
        'promo_badge',
        'promo_sub',
        'strip_primary',
        'strip_secondary',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'float',
            'review_count' => 'integer',
            'valid_until' => 'date',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function imageUrl(): string
    {
        $path = trim((string) $this->image_path);
        if ($path === '') {
            return asset('assets/images/vendor-card-placeholder.svg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
