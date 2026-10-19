<?php

namespace App\Support;

use App\Models\CategoryHomepageImage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class HomepageConsultantsCards
{
    /**
     * @return list<array{
     *     url: string,
     *     image: string,
     *     title: string,
     *     description: string,
     *     location: string,
     *     rating_score: float,
     *     rating_count: int,
     *     category_label: string,
     *     category_tone: string,
     *     category_icon: string
     * }>
     */
    public static function build(Collection $consultants, bool $hasLocation, int $limit = 4, ?HomepageImageRegistry $images = null): array
    {
        unset($consultants, $hasLocation);

        return HomepageCategoryListingCards::adminCategoryEntityCards(
            CategoryHomepageImage::CONTEXT_CONSULTANTS,
            $limit,
            fn (string $name): array => self::categoryMeta($name),
            $images
        );
    }

    /**
     * @return array{label: string, tone: string, icon: string}
     */
    private static function categoryMeta(string $rawName): array
    {
        $name = strtolower($rawName);

        if (str_contains($name, 'legal') || str_contains($name, 'law')) {
            return ['label' => 'Legal Advisory', 'tone' => 'indigo', 'icon' => 'fa-scale-balanced'];
        }

        if (str_contains($name, 'tax') || str_contains($name, 'account') || str_contains($name, 'finance')) {
            return ['label' => 'Tax & Finance', 'tone' => 'teal', 'icon' => 'fa-calculator'];
        }

        if (str_contains($name, 'business') || str_contains($name, 'startup')) {
            return ['label' => 'Business Consulting', 'tone' => 'blue', 'icon' => 'fa-briefcase'];
        }

        if (str_contains($name, 'career') || str_contains($name, 'hr')) {
            return ['label' => 'Career Guidance', 'tone' => 'purple', 'icon' => 'fa-user-tie'];
        }

        if (str_contains($name, 'real estate') || str_contains($name, 'property')) {
            return ['label' => 'Property Advisory', 'tone' => 'orange', 'icon' => 'fa-building'];
        }

        if (str_contains($name, 'health') || str_contains($name, 'medical')) {
            return ['label' => 'Health Consulting', 'tone' => 'pink', 'icon' => 'fa-heart-pulse'];
        }

        if (str_contains($name, 'astro') || str_contains($name, 'vastu') || str_contains($name, 'jyotish')) {
            return ['label' => 'Astrology & Vastu', 'tone' => 'pink', 'icon' => 'fa-om'];
        }

        if (str_contains($name, 'education') || str_contains($name, 'coaching')) {
            return ['label' => 'Education Consulting', 'tone' => 'blue', 'icon' => 'fa-graduation-cap'];
        }

        if (str_contains($name, 'organic') || str_contains($name, 'agri') || str_contains($name, 'farm')) {
            return ['label' => 'Agriculture Consulting', 'tone' => 'green', 'icon' => 'fa-seedling'];
        }

        return [
            'label' => Str::limit($rawName, 22, ''),
            'tone' => 'slate',
            'icon' => 'fa-user-tie',
        ];
    }
}
