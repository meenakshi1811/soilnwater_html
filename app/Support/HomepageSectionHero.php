<?php

namespace App\Support;

use App\Models\HomepageSectionHeroImage;

final class HomepageSectionHero
{
    private const DEFAULT_HERO = 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&w=1920&q=80';

    /** @var array<string, string>|null */
    private static ?array $cache = null;

    public static function backgroundUrl(string $sectionKey, ?string $fallback = null): string
    {
        self::warmCache();

        $path = self::$cache[$sectionKey] ?? '';
        if ($path !== '') {
            return self::formatPath($path);
        }

        if ($fallback !== null && $fallback !== '') {
            return $fallback;
        }

        if ($sectionKey === 'study-material') {
            return 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=1920&q=80';
        }

        return self::DEFAULT_HERO;
    }

    public static function forgetCache(): void
    {
        self::$cache = null;
    }

    private static function warmCache(): void
    {
        if (self::$cache !== null) {
            return;
        }

        self::$cache = [];
        if (! class_exists(HomepageSectionHeroImage::class)) {
            return;
        }

        try {
            foreach (HomepageSectionHeroImage::query()->get(['section_key', 'image_path']) as $row) {
                $path = trim((string) $row->image_path);
                if ($path !== '') {
                    self::$cache[$row->section_key] = $path;
                }
            }
        } catch (\Throwable) {
            self::$cache = [];
        }
    }

    private static function formatPath(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }
}
