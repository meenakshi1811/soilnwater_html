<?php

namespace App\Support;

class StoreRichText
{
    /**
     * Normalize vendor/consultant/service-provider rich text so store pages
     * use the project typography instead of pasted editor inline styles.
     */
    public static function normalizeTypography(?string $html): string
    {
        if (! filled($html)) {
            return '';
        }

        $normalized = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = preg_replace('/<\/?font\b[^>]*>/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\sface="[^"]*"/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/font-family\s*:\s*[^;"}]+;?/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/font-weight\s*:\s*[^;"}]+;?/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/font-size\s*:\s*[^;"}]+;?/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\sstyle="\s*"/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\sstyle=\'\s*\'/i', '', $normalized) ?? $normalized;

        return trim($normalized);
    }
}
