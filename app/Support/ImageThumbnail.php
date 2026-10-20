<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Small WebP copies of local images for places that show them small (homepage cards): the
 * originals are often 1600px PNGs of 1–2 MB shown at about 300px. A copy is made once, the
 * first time it is asked for, and kept in public/thumbs; a new upload gets a new copy.
 */
final class ImageThumbnail
{
    /**
     * New copies made in one request, at most: the first visit after new images doesn't do
     * them all at once (the rest show the original this time and get their copy next visit).
     */
    private const MAX_NEW_PER_REQUEST = 4;

    /** Images larger than this (in pixels) are left alone: too much memory to resize. */
    private const MAX_SOURCE_PIXELS = 25_000_000;

    private static int $made = 0;

    /**
     * The address of a copy about $width pixels wide (twice the size it is shown at, for sharp
     * screens). The original address when there is no copy: an external image, a missing file,
     * an image already that small, or no WebP support.
     */
    public static function url(string $url, int $width = 640): string
    {
        $path = self::publicPath($url);
        if ($path === null) {
            return $url;
        }

        $source = public_path($path);
        if (! is_file($source)) {
            return $url;
        }

        $target = self::targetPath($path, $source, $width);
        if (is_file(public_path($target))) {
            return asset($target);
        }

        // Checked before the limit: an image that needs no copy shouldn't use up a turn
        if (! self::needsCopy($source, $width) || self::$made >= self::MAX_NEW_PER_REQUEST) {
            return $url;
        }
        self::$made++;

        return self::make($source, public_path($target), $width) ? asset($target) : $url;
    }

    /**
     * Makes the copy of an image under public/ now, if it isn't there yet: right after an upload,
     * and for the images:thumbnails command. False when the original is used instead.
     */
    public static function prepare(string $path, int $width = 640): bool
    {
        $source = public_path($path);
        if (! is_file($source)) {
            return false;
        }

        $target = public_path(self::targetPath($path, $source, $width));

        return is_file($target) || (self::needsCopy($source, $width) && self::make($source, $target, $width));
    }

    /**
     * Whether a smaller copy is worth making: not for an image already about that small, nor
     * one too large to resize safely, nor without WebP support. Reads only the file's header.
     */
    private static function needsCopy(string $source, int $width): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }

        $info = @getimagesize($source);
        if ($info === false || $info[0] * $info[1] > self::MAX_SOURCE_PIXELS) {
            return false;
        }

        return $info[0] > $width || filesize($source) >= 150_000;
    }

    /**
     * Named after the file and its last change, so a replaced image gets a new copy.
     */
    private static function targetPath(string $path, string $source, int $width): string
    {
        return 'thumbs/'.$width.'/'.sha1($path.'|'.filemtime($source)).'.webp';
    }

    /**
     * The path under public/ of a local image address, or null for anything else.
     */
    private static function publicPath(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $base = rtrim(asset(''), '/');
        if (str_starts_with($url, $base.'/')) {
            $path = substr($url, strlen($base) + 1);
        } elseif (! preg_match('#^[a-z][a-z0-9+.-]*:#i', $url) && ! str_starts_with($url, '//')) {
            $path = ltrim($url, '/');
        } else {
            return null;
        }

        $path = (string) strtok($path, '?#');
        if ($path === '' || str_contains($path, '..') || ! preg_match('/\.(png|jpe?g|webp|gif)$/i', $path)) {
            return null;
        }

        return $path;
    }

    private static function make(string $source, string $target, int $width): bool
    {
        [$sourceWidth, $sourceHeight, $type] = getimagesize($source);

        $image = match ($type) {
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            IMAGETYPE_GIF => @imagecreatefromgif($source),
            default => false,
        };
        if (! $image) {
            return false;
        }

        imagepalettetotruecolor($image);

        if ($sourceWidth > $width) {
            // Onto a transparent canvas (imagescale() would lose a PNG logo's transparency)
            $height = max(1, (int) round($sourceHeight * $width / $sourceWidth));
            $scaled = imagecreatetruecolor($width, $height);
            imagealphablending($scaled, false);
            imagefill($scaled, 0, 0, imagecolorallocatealpha($scaled, 0, 0, 0, 127));
            imagecopyresampled($scaled, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
            imagedestroy($image);
            $image = $scaled;
        }

        imagesavealpha($image, true);

        File::ensureDirectoryExists(dirname($target));
        $temporary = $target.'.'.getmypid().'.tmp';
        $saved = @imagewebp($image, $temporary, 80);
        imagedestroy($image);

        if (! $saved || ! is_file($temporary)) {
            @unlink($temporary);

            return false;
        }

        // Written under a temporary name first, so a half-written copy is never served
        return @rename($temporary, $target) || is_file($target);
    }
}
