<?php

namespace App\Support;

final class AdTemplatePreview
{
    /**
     * Render template HTML for preview cards/tables.
     */
    public static function render(string $layoutHtml, array $fields, ?string $placeholderImageUrl = null): string
    {
        $html = $layoutHtml;

        foreach ($fields as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $replacement = is_string($value) ? e($value) : '';
            $html = str_replace('{{'.$key.'}}', $replacement, $html);
        }

        if ($placeholderImageUrl) {
            $html = preg_replace_callback(
                '/(<img[^>]*data-ad-key="[^"]+"[^>]*)(src="[^"]*")?/i',
                function (array $m) use ($placeholderImageUrl) {
                    $tag = $m[0] ?? '';
                    if (stripos($tag, 'src=') !== false) {
                        // Replace src value
                        $tag = preg_replace('/src="[^"]*"/i', 'src="'.$placeholderImageUrl.'"', $tag) ?? $tag;
                        $tag = preg_replace("/src='[^']*'/i", "src='".$placeholderImageUrl."'", $tag) ?? $tag;
                        return $tag;
                    }

                    // Inject missing src
                    return rtrim(substr($tag, 0, -1)).' src="'.$placeholderImageUrl.'">';
                },
                $html
            ) ?? $html;
        }

        // Remove any unresolved placeholders
        $html = preg_replace('/\{\{[a-zA-Z][a-zA-Z0-9_]*\}\}/', '', $html) ?? $html;

        return $html;
    }

    /**
     * Good-looking sample values for template previews.
     */
    public static function sampleFields(): array
    {
        return [
            'headline' => 'Beauty Clinic',
            'subheadline' => 'Glow up with premium treatments',
            'badge' => '50% OFF',
            'line1' => 'Skin Care',
            'line2' => 'Facial Treatment',
            'line3' => 'Body Treatment',
            'cta' => 'More Info',
            'offer_text' => 'Scholarship up to 40%',
            'date_text' => 'Admissions close: June 30',
            'location_text' => 'Downtown Campus, Springfield',
            'phone' => '123-456-7890',
            'website' => 'www.yourwebsite.com',
        ];
    }


    public static function sampleFieldsForSchema(array $fields, string $templateName = ''): array
    {
        $fallback = self::sampleFieldsForTemplateName($templateName);
        $resolved = [];

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            $key = (string) ($field['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $type = (string) ($field['type'] ?? 'text');
            if (mb_strtolower($type) === 'image') {
                continue;
            }

            $default = array_key_exists('default', $field) ? (string) ($field['default'] ?? '') : '';
            $label = (string) ($field['label'] ?? '');

            if (trim($default) !== '') {
                $resolved[$key] = $default;
                continue;
            }

            if (array_key_exists($key, $fallback) && trim((string) $fallback[$key]) !== '') {
                $resolved[$key] = (string) $fallback[$key];
                continue;
            }

            $resolved[$key] = trim($label) !== '' ? $label : 'Sample';
        }

        if ($resolved === []) {
            return $fallback;
        }

        return $resolved + $fallback;
    }

    public static function sampleFieldsForTemplateName(string $templateName): array
    {
        $name = mb_strtolower($templateName);

        if (str_contains($name, 'grand opening') || str_contains($name, 'opening')) {
            return [
                'headline' => 'Grand Opening',
                'subheadline' => 'Weekend offers & giveaways',
                'badge' => 'NEW',
                'line1' => 'Live Music',
                'line2' => 'Door Prizes',
                'line3' => 'Limited Deals',
                'cta' => 'Visit Now',
                'offer_text' => 'Free tasting on launch day',
                'date_text' => 'Saturday, 10:00 AM',
                'location_text' => 'MG Road, Sector 9',
                'phone' => '987-654-3210',
                'website' => 'www.brand.com',
            ];
        }

        if (str_contains($name, 'furniture')) {
            return [
                'headline' => 'Modern Furniture',
                'subheadline' => 'Minimal designs for living, dining & office',
                'badge' => 'SALE',
                'line1' => 'Sofas & Chairs',
                'line2' => 'Tables',
                'line3' => 'Wardrobes',
                'cta' => 'Shop Now',
                'offer_text' => 'Flat 35% off + free delivery',
                'date_text' => 'Weekend Special',
                'location_text' => 'City Furniture Hub',
                'phone' => '123-000-4567',
                'website' => 'www.furniture.com',
            ];
        }

        if (str_contains($name, 'salon') || str_contains($name, 'beauty')) {
            return self::sampleFields();
        }

        return [
            'headline' => 'Your Brand',
            'subheadline' => 'Add your headline, images and CTA',
            'badge' => 'OFFER',
            'line1' => 'Feature One',
            'line2' => 'Feature Two',
            'line3' => 'Feature Three',
            'cta' => 'Learn More',
            'offer_text' => 'Early-bird offer available',
            'date_text' => 'Limited time campaign',
            'location_text' => 'Main branch & online',
            'phone' => '123-456-7890',
            'website' => 'www.yourwebsite.com',
        ];
    }
}

