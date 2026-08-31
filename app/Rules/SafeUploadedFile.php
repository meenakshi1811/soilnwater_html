<?php

namespace App\Rules;

use App\Support\CommunityPostUploadSanitizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeUploadedFile implements ValidationRule
{
    /**
     * @param  list<string>  $mimes
     * @param  list<string>  $mimetypes
     */
    public function __construct(
        private int $maxKilobytes = 20480,
        private array $mimes = [],
        private array $mimetypes = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! $value instanceof UploadedFile) {
            $fail('The :attribute must be a file.');

            return;
        }

        if (! CommunityPostUploadSanitizer::isUsableUpload($value)) {
            $fail('The file upload failed. Please choose the file again and try again.');

            return;
        }

        if ($value->getSize() > ($this->maxKilobytes * 1024)) {
            $fail('The :attribute must not be greater than '.$this->maxKilobytes.' kilobytes.');

            return;
        }

        if ($this->mimes !== []) {
            $extension = strtolower($value->getClientOriginalExtension());
            if ($extension !== '' && ! in_array($extension, array_map('strtolower', $this->mimes), true)) {
                $fail('The :attribute must be a file of type: '.implode(', ', $this->mimes).'.');

                return;
            }
        }

        if ($this->mimetypes !== []) {
            $mimeType = strtolower((string) ($value->getClientMimeType() ?: $value->getMimeType()));
            $allowed = array_map('strtolower', $this->mimetypes);

            if ($mimeType !== '' && ! in_array($mimeType, $allowed, true)) {
                $fail('The :attribute must be a file of type: '.implode(', ', $this->mimetypes).'.');
            }
        }
    }
}
