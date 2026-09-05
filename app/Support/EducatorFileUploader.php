<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EducatorFileUploader
{
    public static function storeImage(UploadedFile $file, string $folder = 'photos'): string
    {
        $directory = public_path('uploads/educators/'.$folder);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/educators/'.$folder.'/'.$filename;
    }

    public static function storeDocument(UploadedFile $file, string $folder = 'materials'): string
    {
        $directory = public_path('uploads/educators/'.$folder);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = Str::uuid()->toString().'.'.$extension;

        // Copy binary bytes as-is (no encoding conversion).
        File::put($directory.DIRECTORY_SEPARATOR.$filename, file_get_contents($file->getRealPath()));

        return 'uploads/educators/'.$folder.'/'.$filename;
    }

    public static function deleteIfExists(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
