<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CommunityPostFileUploader
{
    public const BASE_PATH = 'uploads/community-posts';

    public static function storeImage(UploadedFile $file, string $subfolder = ''): string
    {
        return self::storeFile($file, $subfolder);
    }

    public static function storeFile(UploadedFile $file, string $subfolder = ''): string
    {
        $directory = self::absoluteDirectory($subfolder);
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return self::relativePath($subfolder, $filename);
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    public static function storeAttachment(UploadedFile $file, string $subfolder = 'issues'): array
    {
        $originalName = $file->getClientOriginalName();
        $mimeType = (string) ($file->getClientMimeType() ?: $file->getMimeType());
        $path = self::storeFile($file, $subfolder);

        return [
            'path' => $path,
            'url' => self::url($path),
            'name' => $originalName,
            'type' => Str::before($mimeType !== '' ? $mimeType : 'application/octet-stream', '/'),
        ];
    }

    /**
     * @return array{type: string, path: string, name: string, url: string}
     */
    public static function storeVideo(UploadedFile $file): array
    {
        $path = self::storeFile($file, 'videos');

        return [
            'type' => 'upload',
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'url' => self::url($path),
        ];
    }

    /**
     * @return array{type: string, path: string, name: string, url: string}
     */
    public static function storeAudio(UploadedFile $file, string $source = 'upload'): array
    {
        $path = self::storeFile($file, 'audio');

        return [
            'type' => $source,
            'path' => $path,
            'name' => $file->getClientOriginalName() ?: ($source === 'recording' ? 'Voice recording' : 'Audio story'),
            'url' => self::url($path),
        ];
    }

    public static function storeInlineImage(UploadedFile $file): string
    {
        return self::url(self::storeFile($file, 'inline'));
    }

    public static function url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = Str::after($normalized, 'storage/');
        }

        if (File::exists(public_path($normalized))) {
            return asset($normalized);
        }

        if (Str::startsWith($normalized, 'uploads/')) {
            return asset($normalized);
        }

        $publicPath = self::BASE_PATH.'/'.basename($normalized);

        return asset($publicPath);
    }

    public static function deleteIfExists(?string $path): void
    {
        if (! filled($path) || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = Str::after($normalized, 'storage/');
        }

        $fullPath = public_path($normalized);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    private static function absoluteDirectory(string $subfolder = ''): string
    {
        $directory = filled($subfolder)
            ? public_path(self::BASE_PATH.'/'.trim($subfolder, '/'))
            : public_path(self::BASE_PATH);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return $directory;
    }

    private static function relativePath(string $subfolder, string $filename): string
    {
        return filled($subfolder)
            ? self::BASE_PATH.'/'.trim($subfolder, '/').'/'.$filename
            : self::BASE_PATH.'/'.$filename;
    }
}
