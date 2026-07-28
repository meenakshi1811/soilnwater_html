<?php

namespace App\Support;

class DiscussionAttachments
{
    /** @var list<string> */
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** @var list<string> */
    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'avi'];

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
