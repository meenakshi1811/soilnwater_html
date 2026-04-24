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
                        // Keep existing image URL if present; only fill when src is empty.
                        $tag = preg_replace('/src=""/i', 'src="'.$placeholderImageUrl.'"', $tag) ?? $tag;
                        $tag = preg_replace("/src=''/i", "src='".$placeholderImageUrl."'", $tag) ?? $tag;
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

        if (str_contains($name, 'school') || str_contains($name, 'college') || str_contains($name, 'university') || str_contains($name, 'admission')) {
            return [
                'headline' => 'Admissions Open 2026',
                'subheadline' => 'Apply now for top faculty, modern labs and scholarships.',
                'badge' => 'Apply Now',
                'line1' => 'Scholarship up to 40%',
                'line2' => 'Smart classrooms & labs',
                'line3' => 'Limited seats available',
                'cta' => 'Enroll Today',
                'offer_text' => 'Admission fee waiver',
                'date_text' => 'Last date: June 30',
                'location_text' => 'City Campus, Sector 12',
                'phone' => '+1 212 555 0190',
                'website' => 'www.campusadmissions.com',
            ];
        }

        if (str_contains($name, 'coaching')) {
            return [
                'headline' => 'Coaching Batch Enrollment',
                'subheadline' => 'Expert mentors for competitive exams with weekly mock tests.',
                'badge' => 'Limited Seats',
                'line1' => 'Daily doubt sessions',
                'line2' => 'Rank booster material',
                'line3' => 'Weekend revision classes',
                'cta' => 'Join Batch',
                'offer_text' => 'Flat 25% early-bird off',
                'date_text' => 'Batch starts Monday',
                'location_text' => 'Main Road Learning Hub',
                'phone' => '+1 646 555 0122',
                'website' => 'www.topcoachinghub.com',
            ];
        }

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
            'headline' => 'Mega Sale Campaign',
            'subheadline' => 'Modern and professional ad design for real business promotions.',
            'badge' => '50% OFF',
            'line1' => 'Weekend flash discount',
            'line2' => 'Limited stock available',
            'line3' => 'Free delivery today',
            'cta' => 'Claim Offer',
            'offer_text' => 'Buy 1 Get 1 Free',
            'date_text' => 'Offer valid till Sunday',
            'location_text' => 'Downtown store and online',
            'phone' => '+1 310 555 0147',
            'website' => 'www.brandoffers.com',
        ];
    }
}
