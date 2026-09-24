<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstituteFileUploader
{
    public static function storeDocument(UploadedFile $file, string $folder = 'brochures'): string
    {
        $directory = public_path('uploads/institutes/'.$folder);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $filename = Str::uuid()->toString().'.'.$extension;
        $file->move($directory, $filename);

        return 'uploads/institutes/'.$folder.'/'.$filename;
    }

    public static function storeImage(UploadedFile $file, string $folder = 'logos'): string
    {
        $directory = public_path('uploads/institutes/'.$folder);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/institutes/'.$folder.'/'.$filename;
    }

    public static function storeVideo(UploadedFile $file, string $folder = 'gallery/videos'): string
    {
        $directory = public_path('uploads/institutes/'.$folder);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'mp4');
        $filename = Str::uuid()->toString().'.'.$extension;
        $file->move($directory, $filename);

        return 'uploads/institutes/'.$folder.'/'.$filename;
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
