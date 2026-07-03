<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ListingPaymentFileUploader
{
    public static function storeScreenshot(UploadedFile $file): string
    {
        $directory = public_path('uploads/listing-payments');
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/listing-payments/'.$filename;
    }
}
