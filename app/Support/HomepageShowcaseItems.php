<?php

namespace App\Support;

use App\Models\HomepageShowcaseItem;
use Illuminate\Support\Collection;

final class HomepageShowcaseItems
{
    /**
     * @param  Collection<int, HomepageShowcaseItem>  $items
     * @return list<array<string, mixed>>
     */
    public static function toFeaturedBusinessArrays(Collection $items): array
    {
        return $items
            ->map(function (HomepageShowcaseItem $item) {
                $category = filled($item->category_label) ? $item->category_label : ($item->description ?? 'Business');

                return [
                    'name' => $item->title,
                    'category' => $category,
                    'location' => $item->location ?? '',
                    'rating' => number_format((float) ($item->rating ?? 4.5), 1),
                    'reviews' => (int) ($item->review_count ?? 0),
                    'image' => $item->imageUrl(),
                    'url' => $item->link_url,
                    'theme' => $item->category_tone ?: 'slate',
                    'headline' => $item->headline ?: $item->title,
                    'subheadline' => $item->subheadline ?: $category,
                    'promo_badge' => $item->promo_badge ?: 'Featured',
                    'promo_title' => $item->title,
                    'promo_sub' => $item->promo_sub ?: trim($category.' · '.($item->location ?? '')),
                    'strip_primary' => $item->strip_primary ?? '',
                    'strip_secondary' => $item->strip_secondary ?? '',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, HomepageShowcaseItem>  $items
     * @return list<array<string, mixed>>
     */
    public static function toOfferPromoArrays(Collection $items): array
    {
        return $items
            ->map(fn (HomepageShowcaseItem $item) => [
                'url' => $item->link_url,
                'image' => $item->imageUrl(),
                'discount_badge' => strtoupper(trim((string) ($item->discount_badge ?: 'SPECIAL OFFER'))),
                'category_label' => $item->category_label ?: 'Local offer',
                'category_tone' => $item->category_tone ?: 'slate',
                'category_icon' => $item->category_icon ?: 'fa-store',
                'title' => $item->title,
                'description' => $item->description ?: $item->title,
                'location' => $item->location ?: 'Near you',
                'valid_till' => $item->valid_until?->format('d M Y') ?? 'Limited time',
            ])
            ->values()
            ->all();
    }
}
