<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class CommunityPostUploadSanitizer
{
    /**
     * Drop empty, invalid, or unreadable PHP temp uploads so validation does not fail
     * with "file does not exist or is not readable".
     */
    public static function pruneUnreadableUploads(Request $request): void
    {
        $cleaned = self::filterReadableUploadedFiles($request->allFiles());
        $request->files->replace(is_array($cleaned) ? $cleaned : []);
    }

    public static function isUsableUpload(UploadedFile $file): bool
    {
        if (! $file->isValid()) {
            return false;
        }

        try {
            $path = $file->getRealPath() ?: $file->getPathname();
        } catch (\Throwable) {
            return false;
        }

        if (! is_string($path) || $path === '') {
            return false;
        }

        return is_file($path) && is_readable($path) && $file->getSize() > 0;
    }

    /**
     * @return list<UploadedFile>
     */
    public static function usableUploads(mixed $files): array
    {
        if ($files instanceof UploadedFile) {
            return self::isUsableUpload($files) ? [$files] : [];
        }

        if (! is_array($files)) {
            return [];
        }

        $usable = [];
        foreach ($files as $file) {
            foreach (self::usableUploads($file) as $usableFile) {
                $usable[] = $usableFile;
            }
        }

        return $usable;
    }

    private static function filterReadableUploadedFiles(mixed $files): mixed
    {
        if ($files instanceof UploadedFile) {
            return self::isUsableUpload($files) ? $files : null;
        }

        if (! is_array($files)) {
            return $files;
        }

        $filtered = [];
        foreach ($files as $key => $file) {
            $kept = self::filterReadableUploadedFiles($file);
            if ($kept === null || $kept === []) {
                continue;
            }

            $filtered[$key] = is_array($kept) && self::isListArray($kept)
                ? array_values($kept)
                : $kept;
        }

        return $filtered;
    }

    /**
     * @param  array<mixed>  $array
     */
    private static function isListArray(array $array): bool
    {
        if ($array === []) {
            return true;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }
}
