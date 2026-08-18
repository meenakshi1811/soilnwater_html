<?php

namespace App\Services;

use App\Models\FoulWord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class FoulWordFilter
{
    public const MESSAGE = 'You have used the foul word.';

    public const CACHE_KEY = 'foul_words.active';

    public const CACHE_TTL_SECONDS = 300;

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @param  array<string, string|null>  $fields
     */
    public function assertCleanFields(array $fields): void
    {
        $errors = [];

        foreach ($fields as $field => $text) {
            if ($this->contains((string) $text)) {
                $errors[$field] = [self::MESSAGE];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function assertCleanPayload(array $payload): void
    {
        $errors = [];
        $this->collectPayloadErrors($payload, $errors);

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function contains(string $text): bool
    {
        $normalized = mb_strtolower(trim($this->plainText($text)));

        if ($normalized === '') {
            return false;
        }

        foreach ($this->activeWords() as $word) {
            if ($word === '') {
                continue;
            }

            $quoted = preg_quote($word, '/');

            if (preg_match('/(?<![\p{L}\p{N}_])'.$quoted.'(?![\p{L}\p{N}_])/u', $normalized) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function activeWords(): array
    {
        /** @var list<string> $words */
        $words = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function (): array {
            return FoulWord::query()
                ->where('is_active', true)
                ->orderBy('word')
                ->pluck('word')
                ->map(fn ($word) => mb_strtolower(trim((string) $word)))
                ->filter()
                ->values()
                ->all();
        });

        return $words;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, list<string>>  $errors
     */
    private function collectPayloadErrors(array $payload, array &$errors, string $prefix = ''): void
    {
        foreach ($payload as $key => $value) {
            $field = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $this->collectPayloadErrors($value, $errors, $field);

                continue;
            }

            if (! is_string($value) && ! is_numeric($value)) {
                continue;
            }

            if ($this->shouldSkipKey((string) $key)) {
                continue;
            }

            if ($this->contains((string) $value)) {
                $errors[$field] = [self::MESSAGE];
            }
        }
    }

    private function shouldSkipKey(string $key): bool
    {
        if (in_array($key, [
            'content_type',
            'status',
            'publish_as',
            'location_type',
            'location_lat',
            'location_lng',
            'video_source_type',
            'editor_language',
            'slug',
            'accept_content_responsibility',
            'accept_original_work_indemnity',
        ], true)) {
            return true;
        }

        foreach (['_lat', '_lng', '_url', '_path', '_token', '_at', '_id'] as $suffix) {
            if (str_ends_with($key, $suffix)) {
                return true;
            }
        }

        return str_ends_with($key, '_source_type');
    }

    private function plainText(string $value): string
    {
        return html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
