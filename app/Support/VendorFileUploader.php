<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class VendorFileUploader
{
    public static function storeImage(UploadedFile $file, string $folder): string
    {
        $directory = public_path('uploads/vendors/'.$folder);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/vendors/'.$folder.'/'.$filename;
    }

    public static function storeImages(array $files, string $folder): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = self::storeImage($file, $folder);
            }
        }

        return $paths;
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
