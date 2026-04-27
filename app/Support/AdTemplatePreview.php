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

        // Remove any unresolved placeholders / accidental Blade output snippets.
        $html = preg_replace('/\{\{\s*[^}]+\s*\}\}/', '', $html) ?? $html;
        $html = preg_replace('/@(?:if|elseif|else|endif|foreach|endforeach|for|endfor|php|endphp|csrf|method|error|enderror)\b[^\n\r<>]*/i', '', $html) ?? $html;

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

        $exact = [
            'school admissions open 2026' => [
                'headline' => 'School Admissions 2026',
                'subheadline' => 'Nurturing classrooms with smart learning and activity-based teaching.',
                'badge' => 'Admissions Open',
                'line1' => 'Early-bird fee benefits',
                'line2' => 'Qualified faculty team',
                'line3' => 'Safe transport facility',
                'cta' => 'Apply Today',
                'offer_text' => 'Registration fee waived',
                'date_text' => 'Apply before July 15',
                'location_text' => 'Green Valley School Campus',
                'phone' => '+1 212 555 0101',
                'website' => 'www.greenvalleyschool.edu',
            ],
            'college admission notice' => [
                'headline' => 'College Admission Notice',
                'subheadline' => 'Industry-ready courses, internships, and modern campus infrastructure.',
                'badge' => 'Enroll Now',
                'line1' => 'Merit scholarships available',
                'line2' => 'Career guidance support',
                'line3' => 'Hostel & transport options',
                'cta' => 'Reserve Seat',
                'offer_text' => 'Flat 30% tuition support',
                'date_text' => 'Deadline: August 5',
                'location_text' => 'City Central College',
                'phone' => '+1 312 555 0112',
                'website' => 'www.citycentralcollege.edu',
            ],
            'university admissions 2026' => [
                'headline' => 'University Intake 2026',
                'subheadline' => 'Top-ranked programs with research labs and global exchange opportunities.',
                'badge' => '2026 Intake',
                'line1' => 'International curriculum',
                'line2' => 'Research grants',
                'line3' => 'Placement-focused programs',
                'cta' => 'Start Application',
                'offer_text' => 'Scholarship up to 50%',
                'date_text' => 'Applications close Sept 1',
                'location_text' => 'Northbridge University',
                'phone' => '+1 646 555 0121',
                'website' => 'www.northbridgeuniversity.edu',
            ],
            'coaching class enrollment' => [
                'headline' => 'Coaching Enrollment',
                'subheadline' => 'Expert mentors, test series, and personal performance tracking.',
                'badge' => 'Limited Seats',
                'line1' => 'Daily practice assignments',
                'line2' => 'Weekly mock exams',
                'line3' => 'Doubt-clearing sessions',
                'cta' => 'Join Batch',
                'offer_text' => '25% early-bird off',
                'date_text' => 'New batch starts Monday',
                'location_text' => 'Focus Academy Center',
                'phone' => '+1 404 555 0133',
                'website' => 'www.focusacademyclasses.com',
            ],
            'hotel grand opening' => [
                'headline' => 'Hotel Grand Opening',
                'subheadline' => 'Luxury stay experience with launch-week complimentary services.',
                'badge' => 'Now Open',
                'line1' => 'Deluxe rooms available',
                'line2' => 'Fine dining restaurant',
                'line3' => 'Introductory room offers',
                'cta' => 'Book Stay',
                'offer_text' => 'Flat 40% opening deal',
                'date_text' => 'Opening Weekend Offer',
                'location_text' => 'Sunrise Avenue, Downtown',
                'phone' => '+1 702 555 0154',
                'website' => 'www.grandhorizonhotel.com',
            ],
            'new shop opening' => [
                'headline' => 'New Shop Opening',
                'subheadline' => 'Premium collection now available with opening day discounts.',
                'badge' => 'Grand Launch',
                'line1' => 'Fresh arrivals',
                'line2' => 'Limited launch stock',
                'line3' => 'Special opening prices',
                'cta' => 'Visit Store',
                'offer_text' => 'Up to 60% off',
                'date_text' => 'Opening this Friday',
                'location_text' => 'Main Market Street',
                'phone' => '+1 213 555 0166',
                'website' => 'www.newshoplaunch.com',
            ],
            'cafe opening launch' => [
                'headline' => 'Cafe Launch Party',
                'subheadline' => 'Fresh brews, artisan desserts, and cozy vibes every day.',
                'badge' => 'Cafe Opening',
                'line1' => 'Buy 1 Get 1 coffee',
                'line2' => 'Live acoustic evening',
                'line3' => 'Complimentary tasting menu',
                'cta' => 'Visit Cafe',
                'offer_text' => 'Free welcome drink',
                'date_text' => 'Saturday · 6 PM onwards',
                'location_text' => 'Baker Street Corner',
                'phone' => '+1 917 555 0177',
                'website' => 'www.moondropcafe.com',
            ],
            'salon opening special' => [
                'headline' => 'Salon Opening Special',
                'subheadline' => 'Makeover, hair spa, and skincare packages at launch offers.',
                'badge' => 'Beauty Launch',
                'line1' => 'Bridal package deals',
                'line2' => 'Hair & skin combo',
                'line3' => 'Premium stylist team',
                'cta' => 'Book Slot',
                'offer_text' => 'Flat 50% opening off',
                'date_text' => 'Offer valid this week',
                'location_text' => 'Rose Garden Plaza',
                'phone' => '+1 305 555 0188',
                'website' => 'www.glowstudio-salon.com',
            ],
            'shop mega sales offer' => [
                'headline' => 'Mega Shop Sale',
                'subheadline' => 'Festival season discounts across fashion, home, and essentials.',
                'badge' => 'Mega Sale',
                'line1' => 'Flash deals every hour',
                'line2' => 'Cashback on checkout',
                'line3' => 'Free home delivery',
                'cta' => 'Shop Now',
                'offer_text' => 'Up to 70% OFF',
                'date_text' => 'Sale ends Sunday',
                'location_text' => 'City Mall Outlet',
                'phone' => '+1 469 555 0199',
                'website' => 'www.megashopoffers.com',
            ],
            'products for sale campaign' => [
                'headline' => 'Products For Sale',
                'subheadline' => 'Top-rated gadgets and essentials with verified seller support.',
                'badge' => 'Best Deals',
                'line1' => 'Warranty-backed products',
                'line2' => 'Fast shipping available',
                'line3' => 'Secure checkout',
                'cta' => 'Buy Today',
                'offer_text' => 'Buy 2 Get 1 Free',
                'date_text' => 'Today only deal',
                'location_text' => 'Online + local pickup',
                'phone' => '+1 510 555 0144',
                'website' => 'www.productsalehub.com',
            ],
            'properties for sale showcase' => [
                'headline' => 'Property Showcase',
                'subheadline' => 'Apartments, villas, and plots in prime growth locations.',
                'badge' => 'For Sale',
                'line1' => 'Ready-to-move units',
                'line2' => 'Loan support available',
                'line3' => 'Verified legal checks',
                'cta' => 'Schedule Visit',
                'offer_text' => 'Pre-launch pricing',
                'date_text' => 'Site visit this weekend',
                'location_text' => 'Riverside Residency Zone',
                'phone' => '+1 408 555 0155',
                'website' => 'www.propertyshowcasepro.com',
            ],
            'products for rent promotion' => [
                'headline' => 'Rent Products Easily',
                'subheadline' => 'Furniture, electronics, and event tools on affordable plans.',
                'badge' => 'For Rent',
                'line1' => 'Daily/weekly/monthly plans',
                'line2' => 'Pickup and delivery options',
                'line3' => 'Zero-hassle returns',
                'cta' => 'Rent Now',
                'offer_text' => 'First week at 20% OFF',
                'date_text' => 'Limited rental stock',
                'location_text' => 'All city zones covered',
                'phone' => '+1 347 555 0162',
                'website' => 'www.rentproductsonline.com',
            ],
        ];

        if (array_key_exists($name, $exact)) {
            return $exact[$name];
        }

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
