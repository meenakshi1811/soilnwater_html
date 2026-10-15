<?php

namespace App\Support;

/**
 * The homepage panel sections an admin can reorder (Admin → Homepage Section Order).
 * Featured Businesses is not listed: it always stays first, as the full-width row.
 */
final class HomepageSectionOrder
{
    /**
     * In their default order. "toggle" is the Page Setting switch that shows/hides the section
     * and "toggle_default" whether it is shown when that switch was never saved.
     *
     * @return array<string, array{label: string, icon: string, toggle: string, toggle_default: bool}>
     */
    public static function sections(): array
    {
        return [
            'offers' => ['label' => 'Latest Offers & Discounts', 'icon' => 'fa-solid fa-tags', 'toggle' => 'offer_discount', 'toggle_default' => false],
            'ads' => ['label' => 'Latest Ads & Listings', 'icon' => 'fa-solid fa-rectangle-ad', 'toggle' => 'recent_ads', 'toggle_default' => false],
            'popular_near_you' => ['label' => 'Popular Near You', 'icon' => 'fa-solid fa-location-dot', 'toggle' => 'top_vendors', 'toggle_default' => false],
            'popular_services' => ['label' => 'Popular Services', 'icon' => 'fa-solid fa-screwdriver-wrench', 'toggle' => 'popular_services', 'toggle_default' => false],
            'education_knowledge' => ['label' => 'Education & Knowledge', 'icon' => 'fa-solid fa-graduation-cap', 'toggle' => 'premium_options', 'toggle_default' => true],
            'study_material' => ['label' => 'Study Material Library', 'icon' => 'fa-solid fa-book', 'toggle' => 'premium_options', 'toggle_default' => true],
            'consultants' => ['label' => 'Expert Consultants', 'icon' => 'fa-solid fa-user-tie', 'toggle' => 'consultants_enquiry', 'toggle_default' => false],
            'community_hub' => ['label' => 'Stories From Your Community', 'icon' => 'fa-solid fa-book-open', 'toggle' => 'community_hub', 'toggle_default' => true],
        ];
    }

    /**
     * The saved order, cleaned up so a bad or partial value can never hide a section: unknown
     * keys and duplicates are dropped, and sections missing from it (e.g. added later) follow
     * in their default order.
     *
     * @param  array<int, mixed>|null  $saved
     * @return list<string>
     */
    public static function keys(?array $saved): array
    {
        $all = array_keys(self::sections());
        $ordered = [];

        foreach ($saved ?? [] as $key) {
            if (is_string($key) && in_array($key, $all, true) && ! in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }

        return array_merge($ordered, array_values(array_diff($all, $ordered)));
    }

    /**
     * Whether the section is switched on in Page Setting.
     *
     * @param  array<string, mixed>|null  $toggles
     */
    public static function isEnabled(string $key, ?array $toggles): bool
    {
        $section = self::sections()[$key] ?? null;

        if ($section === null) {
            return false;
        }

        return (bool) data_get($toggles ?? [], $section['toggle'], $section['toggle_default']);
    }
}
