<?php

namespace App\Support;

use Illuminate\Support\Str;

class VendorCategoryIcon
{
    /** @var array<string, string> */
    private const KEYWORD_ICONS = [
        'electronic' => 'fa-microchip',
        'computer' => 'fa-laptop',
        'mobile' => 'fa-mobile-screen-button',
        'hobby' => 'fa-puzzle-piece',
        'hotel' => 'fa-hotel',
        'hospitality' => 'fa-hotel',
        'home' => 'fa-house',
        'furniture' => 'fa-couch',
        'construction' => 'fa-helmet-safety',
        'building' => 'fa-building',
        'education' => 'fa-graduation-cap',
        'school' => 'fa-graduation-cap',
        'equipment' => 'fa-toolbox',
        'tool' => 'fa-screwdriver-wrench',
        'fashion' => 'fa-shirt',
        'cloth' => 'fa-shirt',
        'apparel' => 'fa-shirt',
        'garden' => 'fa-seedling',
        'gardening' => 'fa-seedling',
        'plant' => 'fa-leaf',
        'machine' => 'fa-industry',
        'industrial' => 'fa-industry',
        'matrimon' => 'fa-ring',
        'wedding' => 'fa-ring',
        'event' => 'fa-calendar-days',
        'pet' => 'fa-paw',
        'animal' => 'fa-paw',
        'food' => 'fa-utensils',
        'grocery' => 'fa-basket-shopping',
        'product' => 'fa-box',
        'retail' => 'fa-store',
        'sport' => 'fa-futbol',
        'fitness' => 'fa-dumbbell',
        'transport' => 'fa-truck',
        'logistic' => 'fa-truck',
        'vehicle' => 'fa-car',
        'auto' => 'fa-car',
        'water' => 'fa-droplet',
        'plumb' => 'fa-faucet-drip',
        'health' => 'fa-heart-pulse',
        'medical' => 'fa-stethoscope',
        'beauty' => 'fa-spa',
        'salon' => 'fa-scissors',
        'agriculture' => 'fa-tractor',
        'farm' => 'fa-tractor',
        'service' => 'fa-briefcase',
        'finance' => 'fa-coins',
        'legal' => 'fa-scale-balanced',
        'travel' => 'fa-plane',
        'book' => 'fa-book',
        'art' => 'fa-palette',
        'music' => 'fa-music',
        'gift' => 'fa-gift',
        'jewel' => 'fa-gem',
        'energy' => 'fa-bolt',
        'security' => 'fa-shield-halved',
        'clean' => 'fa-broom',
    ];

    public static function iconClass(?string $name): string
    {
        $normalized = self::normalize($name);

        if ($normalized === '') {
            return 'fa-store';
        }

        foreach (self::KEYWORD_ICONS as $keyword => $icon) {
            if (str_contains($normalized, $keyword)) {
                return $icon;
            }
        }

        return 'fa-tags';
    }

    public static function toneIndex(?string $name): int
    {
        $normalized = self::normalize($name);

        if ($normalized === '') {
            return 1;
        }

        return (int) (crc32($normalized) % 6) + 1;
    }

    private static function normalize(?string $name): string
    {
        return Str::lower(Str::ascii(trim((string) $name)));
    }
}
