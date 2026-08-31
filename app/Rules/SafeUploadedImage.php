<?php

namespace App\Rules;

use App\Support\CommunityPostUploadSanitizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeUploadedImage implements ValidationRule
{
    /**
     * @param  list<string>  $mimes
     */
    public function __construct(
        private int $maxKilobytes = 4096,
        private array $mimes = ['jpg', 'jpeg', 'png', 'webp', 'gif'],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! $value instanceof UploadedFile) {
            $fail('The :attribute must be an image file.');

            return;
        }

        if (! CommunityPostUploadSanitizer::isUsableUpload($value)) {
            $fail('The image upload failed. Please choose the file again and try again.');

            return;
        }

        $path = $value->getRealPath() ?: $value->getPathname();
        $size = $value->getSize();

        if ($size > ($this->maxKilobytes * 1024)) {
            $fail('The :attribute must not be greater than '.$this->maxKilobytes.' kilobytes.');

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $allowedExtensions = array_map('strtolower', $this->mimes);

        if ($extension !== '' && in_array($extension, $allowedExtensions, true)) {
            return;
        }

        $imageInfo = @getimagesize($path);
        if ($imageInfo === false) {
            $fail('The :attribute must be an image.');

            return;
        }

        $detectedMime = strtolower((string) ($imageInfo['mime'] ?? ''));
        $allowedMimes = array_map(
            fn (string $mime): string => 'image/'.($mime === 'jpg' ? 'jpeg' : $mime),
            $this->mimes
        );

        if ($detectedMime !== '' && ! in_array($detectedMime, $allowedMimes, true)) {
            $fail('The :attribute must be an image of type: '.implode(', ', $this->mimes).'.');
        }
    }
}
