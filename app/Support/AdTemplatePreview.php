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
            'subheadline' => 'Glow up with premium skin care & treatments',
            'badge' => '50% OFF',
            'line1' => 'Skin Care',
            'line2' => 'Facial Treatment',
            'line3' => 'Body Treatment',
            'cta' => 'More Info',
            'phone' => '123-456-7890',
            'website' => 'www.yourwebsite.com',
        ];
    }

    public static function sampleFieldsForTemplateName(string $templateName): array
    {
        $name = mb_strtolower($templateName);

        if (str_contains($name, 'grand opening') || str_contains($name, 'opening')) {
            return [
                'headline' => 'Grand Opening',
                'subheadline' => 'Join us this weekend for offers & giveaways',
                'badge' => 'NEW',
                'line1' => 'Live Music',
                'line2' => 'Door Prizes',
                'line3' => 'Limited Deals',
                'cta' => 'Visit Now',
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
            'phone' => '123-456-7890',
            'website' => 'www.yourwebsite.com',
        ];
    }
}

