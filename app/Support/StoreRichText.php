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

        if (preg_match('/^<div class="(vendor-section-(?:title|content)-block)" style="([^"]*)">([\s\S]*)<\/div>$/i', trim($normalized), $matches)) {
            $class = $matches[1];
            $style = self::normalizeBlockStyle($matches[2]);
            $inner = self::stripInlineTypography($matches[3]);

            return '<div class="'.$class.'"'.($style ? ' style="'.$style.'"' : '').'>'.$inner.'</div>';
        }

        return self::stripInlineTypography($normalized);
    }

    private static function normalizeBlockStyle(string $style): string
    {
        $allowed = ['color', 'background-color', 'font-size', 'font-weight'];
        $declarations = [];

        foreach (explode(';', $style) as $declaration) {
            $declaration = trim($declaration);
            if ($declaration === '' || ! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);

            if (in_array($property, $allowed, true) && $value !== '') {
                $declarations[] = $property.':'.$value;
            }
        }

        return implode('; ', $declarations);
    }

    private static function stripInlineTypography(string $html): string
    {
        $normalized = preg_replace('/<\/?font\b[^>]*>/i', '', $html) ?? $html;
        $normalized = preg_replace('/\sface="[^"]*"/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/font-family\s*:\s*[^;"}]+;?/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/font-weight\s*:\s*[^;"}]+;?/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/font-size\s*:\s*[^;"}]+;?/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\sstyle="\s*"/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\sstyle=\'\s*\'/i', '', $normalized) ?? $normalized;

        return trim($normalized);
    }
}
