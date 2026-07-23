<?php

namespace App\Support;

class StaticSponsoredAds
{
    public const DIRECTORY = 'uploads/sponsered';

    /**
     * @return list<string>
     */
    public static function filenames(): array
    {
        $directory = public_path(self::DIRECTORY);
        if (! is_dir($directory)) {
            return [];
        }

        $paths = [];
        foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $extension) {
            $found = glob($directory.'/*.'.$extension) ?: [];
            $paths = array_merge($paths, $found);
        }

        return collect($paths)
            ->map(fn (string $path) => basename($path))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function imageUrls(): array
    {
        return collect(self::filenames())
            ->map(fn (string $filename) => asset(self::DIRECTORY.'/'.$filename))
            ->values()
            ->all();
    }
}
