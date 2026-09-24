<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class InstituteGallery
{
    public const MAX_ITEMS = 24;

    public const MAX_NEW_UPLOADS = 12;

    public const MAX_IMAGE_KB = 2048;

    public const MAX_VIDEO_KB = 20480;

    /** @var list<string> */
    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov'];

    /**
     * @param  array<int, mixed>|null  $gallery
     * @return Collection<int, array{type: string, path: string, url: string}>
     */
    public static function entries(?array $gallery): Collection
    {
        return collect($gallery ?? [])
            ->map(fn ($entry) => self::normalizeEntry($entry))
            ->filter()
            ->values()
            ->map(function (array $entry): array {
                return [
                    'type' => $entry['type'],
                    'path' => $entry['path'],
                    'url' => asset($entry['path']),
                ];
            });
    }

    /** @return array{type: string, path: string}|null */
    public static function normalizeEntry(mixed $entry): ?array
    {
        if (is_string($entry) && filled($entry)) {
            return [
                'type' => self::mediaTypeForPath($entry),
                'path' => $entry,
            ];
        }

        if (! is_array($entry)) {
            return null;
        }

        $path = trim((string) ($entry['path'] ?? ''));
        if ($path === '') {
            return null;
        }

        $type = (string) ($entry['type'] ?? self::mediaTypeForPath($path));

        return [
            'type' => $type === 'video' ? 'video' : 'image',
            'path' => $path,
        ];
    }

    public static function mediaTypeForPath(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::VIDEO_EXTENSIONS, true) ? 'video' : 'image';
    }

    public static function imageMaxBytes(): int
    {
        return self::MAX_IMAGE_KB * 1024;
    }

    public static function videoMaxBytes(): int
    {
        return self::MAX_VIDEO_KB * 1024;
    }

    public static function imageLimitLabel(): string
    {
        return number_format(self::MAX_IMAGE_KB / 1024, self::MAX_IMAGE_KB >= 1024 ? 0 : 1).' MB';
    }

    public static function videoLimitLabel(): string
    {
        return number_format(self::MAX_VIDEO_KB / 1024, self::MAX_VIDEO_KB >= 1024 ? 0 : 1).' MB';
    }

    /**
     * @throws ValidationException
     */
    public static function assertUploadValid(UploadedFile $file): string
    {
        $mime = strtolower((string) $file->getMimeType());
        $extension = strtolower($file->getClientOriginalExtension() ?: '');

        $isVideo = str_starts_with($mime, 'video/')
            || in_array($extension, self::VIDEO_EXTENSIONS, true);

        if ($isVideo) {
            if ($file->getSize() > self::videoMaxBytes()) {
                throw ValidationException::withMessages([
                    'gallery_uploads' => ['Each video must be '.self::videoLimitLabel().' or smaller.'],
                ]);
            }

            if (! in_array($extension, self::VIDEO_EXTENSIONS, true) && ! str_starts_with($mime, 'video/')) {
                throw ValidationException::withMessages([
                    'gallery_uploads' => ['Videos must be MP4, WebM, or MOV.'],
                ]);
            }

            return 'video';
        }

        if (! str_starts_with($mime, 'image/')) {
            throw ValidationException::withMessages([
                'gallery_uploads' => ['Gallery files must be photos or supported videos.'],
            ]);
        }

        if ($file->getSize() > self::imageMaxBytes()) {
            throw ValidationException::withMessages([
                'gallery_uploads' => ['Each photo must be '.self::imageLimitLabel().' or smaller.'],
            ]);
        }

        return 'image';
    }

    /**
     * @param  array<int, mixed>|null  $gallery
     * @return list<array{type: string, path: string}>
     */
    public static function persistableEntries(?array $gallery): array
    {
        return self::entries($gallery)
            ->map(fn (array $item) => [
                'type' => $item['type'],
                'path' => $item['path'],
            ])
            ->values()
            ->all();
    }
}
