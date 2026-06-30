<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PremiumPaymentFileUploader
{
    public static function storeScreenshot(UploadedFile $file): string
    {
        $directory = public_path('uploads/premium-payments');
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/premium-payments/'.$filename;
    }
}
