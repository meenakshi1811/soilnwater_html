<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DiscussionFileUploader
{
    public const BASE_PATH = 'uploads/discussions';

    /**
     * @return array{path: string, url: string, name: string, type: string, kind: string, extension: string, icon: string}
     */
    public static function storeMedia(UploadedFile $file, string $subfolder): array
    {
        $directory = self::absoluteDirectory($subfolder);
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $mime = (string) $file->getMimeType();
        $kind = DiscussionAttachments::detectKind($mime, $extension);
        $filename = Str::uuid()->toString().'.'.$extension;

        $file->move($directory, $filename);

        $path = self::relativePath($subfolder, $filename);

        return [
            'path' => $path,
            'url' => self::url($path),
            'name' => $originalName,
            'type' => Str::before($mime, '/'),
            'kind' => $kind,
            'extension' => $extension,
            'icon' => DiscussionAttachments::iconForKind($kind, $extension),
        ];
    }

    /**
     * @param  list<UploadedFile>  $files
     * @return list<array{path: string, url: string, name: string, type: string, kind: string}>
     */
    public static function storeMany(array $files, string $subfolder): array
    {
        return collect($files)
            ->map(fn (UploadedFile $file) => self::storeMedia($file, $subfolder))
            ->values()
            ->all();
    }

    public static function url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return asset($path);
    }

    private static function absoluteDirectory(string $subfolder): string
    {
        $directory = public_path(self::BASE_PATH.($subfolder !== '' ? '/'.$subfolder : ''));

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return $directory;
    }

    private static function relativePath(string $subfolder, string $filename): string
    {
        return self::BASE_PATH.($subfolder !== '' ? '/'.$subfolder : '').'/'.$filename;
    }
}
