<?php

namespace App\Support;

class DiscussionAttachments
{
    /** @var list<string> */
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** @var list<string> */
    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'avi'];

    public const MAX_IMAGE_KILOBYTES = 10240;

    public const MAX_VIDEO_KILOBYTES = 51200;

    public const MAX_DOCUMENT_KILOBYTES = 10240;

    public const MAX_ATTACHMENTS = 4;

    /** @var list<string> */
    public const DOCUMENT_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'rar',
    ];

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return array_values(array_unique(array_merge(
            self::IMAGE_EXTENSIONS,
            self::VIDEO_EXTENSIONS,
            self::DOCUMENT_EXTENSIONS,
        )));
    }

    public static function validationMimesRule(): string
    {
        return 'mimes:'.implode(',', self::allowedExtensions());
    }

    public static function acceptImages(): string
    {
        return 'image/*';
    }

    public static function acceptVideos(): string
    {
        return 'video/mp4,video/webm,video/quicktime,video/x-msvideo,.mp4,.webm,.mov,.avi';
    }

    public static function acceptDocuments(): string
    {
        return '.'.implode(',.', self::DOCUMENT_EXTENSIONS);
    }

    public static function detectKind(string $mime, ?string $extension = null): string
    {
        $extension = strtolower((string) $extension);

        if (str_starts_with($mime, 'video/') || in_array($extension, self::VIDEO_EXTENSIONS, true)) {
            return 'video';
        }

        if (str_starts_with($mime, 'image/') || in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return 'image';
        }

        return 'document';
    }

    public static function iconForKind(string $kind, ?string $extension = null): string
    {
        return match ($kind) {
            'video' => 'fa-video',
            'image' => 'fa-image',
            default => self::iconForDocumentExtension($extension),
        };
    }

    public static function iconForDocumentExtension(?string $extension): string
    {
        return match (strtolower((string) $extension)) {
            'pdf' => 'fa-file-pdf',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx', 'csv' => 'fa-file-excel',
            'ppt', 'pptx' => 'fa-file-powerpoint',
            'zip', 'rar' => 'fa-file-zipper',
            'txt' => 'fa-file-lines',
            default => 'fa-file-lines',
        };
    }

    public static function maxKilobytesForKind(string $kind): int
    {
        return match ($kind) {
            'video' => self::MAX_VIDEO_KILOBYTES,
            'image' => self::MAX_IMAGE_KILOBYTES,
            default => self::MAX_DOCUMENT_KILOBYTES,
        };
    }

    public static function maxKilobytesForUploadedFile(?string $mime, ?string $extension = null): int
    {
        return self::maxKilobytesForKind(self::detectKind((string) $mime, $extension));
    }

    /**
     * @return array<string, int>
     */
    public static function clientMaxFileBytes(): array
    {
        $serverLimit = self::serverUploadLimitBytes();

        return [
            'video' => self::capBytes(self::MAX_VIDEO_KILOBYTES * 1024, $serverLimit),
            'image' => self::capBytes(self::MAX_IMAGE_KILOBYTES * 1024, $serverLimit),
            'document' => self::capBytes(self::MAX_DOCUMENT_KILOBYTES * 1024, $serverLimit),
        ];
    }

    public static function serverUploadLimitBytes(): int
    {
        $uploadLimit = self::parseIniSizeToBytes((string) ini_get('upload_max_filesize'));
        $postLimit = self::parseIniSizeToBytes((string) ini_get('post_max_size'));

        if ($uploadLimit <= 0 && $postLimit <= 0) {
            return 0;
        }

        if ($uploadLimit <= 0) {
            return $postLimit;
        }

        if ($postLimit <= 0) {
            return $uploadLimit;
        }

        return min($uploadLimit, $postLimit);
    }

    public static function parseIniSizeToBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private static function capBytes(int $limit, int $serverLimit): int
    {
        if ($serverLimit <= 0) {
            return $limit;
        }

        return min($limit, $serverLimit);
    }

    /**
     * @return array<string, string>
     */
    public static function documentIconMap(): array
    {
        return [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'csv' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint',
            'pptx' => 'fa-file-powerpoint',
            'zip' => 'fa-file-zipper',
            'rar' => 'fa-file-zipper',
            'txt' => 'fa-file-lines',
        ];
    }
}
