<?php

namespace App\Support;

final class SocialShare
{
    public const OG_IMAGE_MIN_WIDTH = 200;

    public const OG_IMAGE_MIN_HEIGHT = 200;

    public const OG_IMAGE_MAX_BYTES = 8_000_000;

    public const DEFAULT_OG_IMAGE = 'assets/images/soilandwater_logo.png';

    public static function normalizeUrl(?string $url): string
    {
        if (! filled($url)) {
            return self::normalizeUrl(url('/'));
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return self::normalizeUrl(url($url));
        }

        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['host'])) {
            return $url;
        }

        $host = strtolower((string) $parts['host']);
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test');

        if (! $isLocalHost) {
            $parts['scheme'] = 'https';
        }

        $normalized = self::buildUrl($parts);

        return $normalized !== '' ? $normalized : $url;
    }

    public static function assetUrl(string $path): string
    {
        return self::normalizeUrl(asset($path));
    }

    public static function facebookShareUrl(?string $url): string
    {
        return 'https://www.facebook.com/sharer/sharer.php?display=popup&u='.urlencode(self::normalizeUrl($url));
    }

    /**
     * @return array{url: string, width: ?int, height: ?int, path: ?string}
     */
    public static function openGraphImageFromPublicPath(?string $relativePath, ?string $fallbackRelativePath = null): array
    {
        $fallbackPath = $fallbackRelativePath ?: self::DEFAULT_OG_IMAGE;
        $candidatePath = filled($relativePath) ? ltrim(str_replace('\\', '/', $relativePath), '/') : '';

        if ($candidatePath !== '' && self::isValidOpenGraphImage(public_path($candidatePath))) {
            $size = @getimagesize(public_path($candidatePath)) ?: null;

            return [
                'url' => self::assetUrl($candidatePath),
                'width' => is_array($size) ? (int) ($size[0] ?? 0) : null,
                'height' => is_array($size) ? (int) ($size[1] ?? 0) : null,
                'path' => $candidatePath,
            ];
        }

        $fallbackAbsolutePath = public_path($fallbackPath);
        $size = is_file($fallbackAbsolutePath) ? (@getimagesize($fallbackAbsolutePath) ?: null) : null;

        return [
            'url' => self::assetUrl($fallbackPath),
            'width' => is_array($size) ? (int) ($size[0] ?? 0) : null,
            'height' => is_array($size) ? (int) ($size[1] ?? 0) : null,
            'path' => $fallbackPath,
        ];
    }

    public static function isValidOpenGraphImage(string $absolutePath): bool
    {
        if (! is_file($absolutePath)) {
            return false;
        }

        if (filesize($absolutePath) > self::OG_IMAGE_MAX_BYTES) {
            return false;
        }

        $size = @getimagesize($absolutePath);

        if (! is_array($size)) {
            return false;
        }

        $width = (int) ($size[0] ?? 0);
        $height = (int) ($size[1] ?? 0);

        return $width >= self::OG_IMAGE_MIN_WIDTH && $height >= self::OG_IMAGE_MIN_HEIGHT;
    }

    public static function mimeTypeForPublicPath(?string $relativePath): ?string
    {
        if (! filled($relativePath)) {
            return null;
        }

        $absolutePath = public_path(ltrim(str_replace('\\', '/', $relativePath), '/'));

        if (! is_file($absolutePath)) {
            return null;
        }

        $mime = @mime_content_type($absolutePath);

        return is_string($mime) && str_starts_with($mime, 'image/') ? $mime : null;
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    private static function buildUrl(array $parts): string
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'].'://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $user = $parts['user'] ?? '';
        $pass = isset($parts['pass']) ? ':'.$parts['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return $scheme.$user.$pass.$host.$port.$path.$query.$fragment;
    }
}
