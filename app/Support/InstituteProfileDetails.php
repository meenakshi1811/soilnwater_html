<?php

namespace App\Support;

use App\Models\Institute;
use Illuminate\Validation\Rule;

/**
 * The details on a school's public page that its profile form edits beyond the main fields, kept
 * in institutes.profile_meta (SchoolProfilePresenter::meta reads them): the header and "At a
 * glance" lines, the numbers strip, quick facts, highlight lists, admission info, faculty, events
 * and reviews. A detail never saved shows the standard wording; once saved, a blank one is hidden.
 * The form's fields are details[...].
 */
final class InstituteProfileDetails
{
    /** One-line details: form field => longest length */
    public const TEXT_FIELDS = [
        'co_educational_label' => 80,
        'school_type' => 60,
        'medium_of_instruction' => 60,
        'campus_area' => 60,
        'school_code' => 40,
        'affiliation_number' => 60,
        'operating_hours' => 120,
        'board_result_percent' => 80,
    ];

    /** Lists of single lines: form field => [most items, longest line] */
    public const LINE_LISTS = [
        'about_highlights' => [12, 200],
        'why_choose_us' => [12, 200],
        'highlights' => [12, 120],
        'admission_highlights' => [12, 300],
    ];

    /** Lists of rows: form field => [most rows, the row's fields] */
    public const ROW_LISTS = [
        'quick_stats' => [12, ['icon', 'value', 'label']],
        'faculty' => [60, ['name', 'role', 'subject']],
        'events' => [30, ['title', 'date', 'time']],
        'reviews' => [30, ['name', 'relation', 'rating', 'comment', 'ago']],
    ];

    /** Icons a quick fact can have: Font Awesome class => name in the form */
    public const STAT_ICONS = [
        'fa-chalkboard' => 'Classroom',
        'fa-bus' => 'Transport',
        'fa-book' => 'Library',
        'fa-flask' => 'Laboratory',
        'fa-futbol' => 'Sports',
        'fa-video' => 'CCTV',
        'fa-laptop' => 'Computers',
        'fa-wifi' => 'Wi-Fi',
        'fa-utensils' => 'Canteen',
        'fa-person-swimming' => 'Swimming',
        'fa-music' => 'Music',
        'fa-palette' => 'Arts',
        'fa-heart-pulse' => 'Medical',
        'fa-shield-halved' => 'Security',
        'fa-tree' => 'Green campus',
        'fa-trophy' => 'Awards',
    ];

    /**
     * The standard wording, shown until a school saves its own (an institute has no school-only
     * lines: no "Co-Educational Day School" or "Day School").
     *
     * @return array<string, mixed>
     */
    public static function defaults(bool $forSchool = true): array
    {
        return [
            'co_educational_label' => $forSchool ? 'Co-Educational Day School' : '',
            'school_type' => $forSchool ? 'Day School' : '',
            'medium_of_instruction' => 'English',
            'campus_area' => '5 Acres',
            'operating_hours' => 'Mon – Sat: 08:00 AM – 04:00 PM',
            'board_result_percent' => '98% Board Result ('.now()->subYear()->year.')',
            'rating' => 4.8,
            'student_count' => 512,
            'faculty_count' => 35,
            'quick_stats' => [
                ['icon' => 'fa-chalkboard', 'label' => 'Smart Classrooms', 'value' => 'Yes'],
                ['icon' => 'fa-bus', 'label' => 'Transport', 'value' => 'Available'],
                ['icon' => 'fa-book', 'label' => 'Library', 'value' => '15,000+ Books'],
                ['icon' => 'fa-flask', 'label' => 'Laboratories', 'value' => '6 Labs'],
                ['icon' => 'fa-futbol', 'label' => 'Sports', 'value' => '20+ Activities'],
                ['icon' => 'fa-video', 'label' => 'CCTV Security', 'value' => '24x7'],
            ],
            'about_highlights' => [
                'Student-centric learning environment',
                'Safe, green and technology-enabled campus',
                'Experienced and dedicated faculty',
                'Strong focus on sports and co-curricular activities',
                'Regular parent engagement and progress tracking',
            ],
            'why_choose_us' => [
                'Quality Education & Academic Excellence',
                'Well-qualified and Experienced Faculty',
                'Modern Infrastructure & Smart Learning',
                'Holistic Development Programmes',
                'Safe & Secure Campus Environment',
                'Strong Board Results Year-on-Year',
            ],
            'admission' => [
                'lead' => 'Admissions are open for select grades. Send an enquiry or contact the admission office during operating hours.',
                'highlights' => [
                    'Online enquiry and campus visit scheduling available',
                    'Document checklist shared after initial enquiry',
                    'Entrance assessment for senior grades where applicable',
                    'Age criteria and grade mapping explained during counselling',
                    'Fee plan, transport, and scholarship options shared after registration',
                ],
                'details' => implode("\n\n", [
                    'How to apply',
                    '1. Submit an online enquiry or visit the admission desk on working days.',
                    '2. Attend a counselling session to confirm grade eligibility and seat availability.',
                    '3. Complete the application form and upload required documents.',
                    '4. Appear for an interaction or assessment if applicable for the selected grade.',
                    '5. Pay the registration fee to confirm provisional admission.',
                    '',
                    'Documents usually required',
                    'Birth certificate, previous report cards, transfer certificate (if applicable), passport-size photographs, parent/guardian ID proof, and address proof.',
                    '',
                    'Need help?',
                    'Contact the admission office during campus operating hours or send an enquiry from this profile page.',
                ]),
            ],
        ];
    }

    public static function default(string $key, bool $forSchool = true): mixed
    {
        return data_get(self::defaults($forSchool), $key);
    }

    /**
     * The form's values: what the page shows now (so the school edits what visitors see). The
     * school code, affiliation number, review count and the lists that fall back to other data
     * (highlights, faculty, events) show only what was saved.
     *
     * @return array<string, mixed>
     */
    public static function forForm(Institute $institute): array
    {
        $profile = new SchoolProfilePresenter($institute);
        $saved = fn (string $key) => $profile->meta($key);
        $savedList = fn (string $key) => is_array($saved($key)) ? array_values($saved($key)) : [];

        $values = [
            'co_educational_label' => (string) $profile->meta('co_educational_label', self::default('co_educational_label', $institute->user?->isSchool() ?? true)),
            'school_type' => $profile->schoolType(),
            'medium_of_instruction' => (string) $profile->meta('medium_of_instruction', self::default('medium_of_instruction')),
            'campus_area' => (string) $profile->meta('campus_area', self::default('campus_area')),
            'school_code' => (string) $saved('school_code'),
            'affiliation_number' => (string) $saved('affiliation_number'),
            'operating_hours' => $profile->operatingHours(),
            'board_result_percent' => $profile->boardResultPercent(),
            'student_count' => $profile->studentCount() ?: '',
            'faculty_count' => $profile->facultyCount() ?: '',
            'rating' => $profile->rating() ?? '',
            'review_count' => is_numeric($saved('review_count')) ? (int) $saved('review_count') : '',
            'is_premium' => $profile->isPremium(),
            'use_admin_reviews' => $profile->useAdminReviews(),
            'quick_stats' => $profile->quickStats(),
            'about_highlights' => $profile->aboutHighlights(),
            'why_choose_us' => $profile->whyChooseUs(),
            'highlights' => $savedList('highlights'),
            'admission_lead' => $profile->admissionLead(),
            'admission_highlights' => $profile->admissionHighlights(),
            'admission_details' => $profile->admissionDetailsText(),
            'faculty' => $savedList('faculty'),
            // (events saved before had no date; they can't be edited here)
            'events' => array_values(array_filter($savedList('events'), fn ($event) => is_array($event) && filled($event['date'] ?? null))),
            'reviews' => $savedList('reviews'),
        ];

        $old = old('details');

        return is_array($old) ? array_replace($values, $old) : $values;
    }

    /**
     * The submitted details tidied: lines trimmed, blank lines and empty rows dropped (a just-added
     * row left empty), lists numbered from 0.
     *
     * @return array<string, mixed>
     */
    public static function cleanInput(mixed $details): array
    {
        $details = is_array($details) ? $details : [];

        foreach (self::LINE_LISTS as $key => $_) {
            $details[$key] = array_values(array_filter(
                array_map(fn ($line) => is_scalar($line) ? trim((string) $line) : '', (array) ($details[$key] ?? [])),
                fn (string $line) => $line !== ''
            ));
        }

        foreach (self::ROW_LISTS as $key => [, $fields]) {
            $rows = [];
            foreach ((array) ($details[$key] ?? []) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $row = array_map(fn ($value) => is_scalar($value) ? trim((string) $value) : '', array_intersect_key($row, array_flip($fields)));
                // (a picked icon or star rating alone doesn't make a row)
                $content = array_diff_key($row, array_flip(['icon', 'rating']));
                if (implode('', $content) !== '') {
                    $rows[] = $row;
                }
            }
            $details[$key] = $rows;
        }

        return $details;
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(bool $byAdmin): array
    {
        $rules = [
            'details' => ['nullable', 'array'],
            'details.student_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'details.faculty_count' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'details.admission_lead' => ['nullable', 'string', 'max:500'],
            'details.admission_details' => ['nullable', 'string', 'max:5000'],
            'details.quick_stats.*.icon' => ['nullable', 'string', Rule::in(array_keys(self::STAT_ICONS))],
            'details.quick_stats.*.value' => ['required', 'string', 'max:60'],
            'details.quick_stats.*.label' => ['required', 'string', 'max:60'],
            'details.faculty.*.name' => ['required', 'string', 'max:120'],
            'details.faculty.*.role' => ['nullable', 'string', 'max:120'],
            'details.faculty.*.subject' => ['nullable', 'string', 'max:120'],
            'details.events.*.title' => ['required', 'string', 'max:160'],
            'details.events.*.date' => ['required', 'date'],
            'details.events.*.time' => ['nullable', 'string', 'max:40'],
            'details.reviews.*.name' => ['required', 'string', 'max:120'],
            'details.reviews.*.relation' => ['nullable', 'string', 'max:60'],
            'details.reviews.*.rating' => ['required', 'integer', 'between:1,5'],
            'details.reviews.*.comment' => ['required', 'string', 'max:1000'],
            'details.reviews.*.ago' => ['nullable', 'string', 'max:40'],
        ];

        foreach (self::TEXT_FIELDS as $key => $max) {
            $rules['details.'.$key] = ['nullable', 'string', 'max:'.$max];
        }

        foreach (self::LINE_LISTS as $key => [$items, $max]) {
            $rules['details.'.$key] = ['nullable', 'array', 'max:'.$items];
            $rules['details.'.$key.'.*'] = ['string', 'max:'.$max];
        }

        foreach (self::ROW_LISTS as $key => [$items]) {
            $rules['details.'.$key] = ['nullable', 'array', 'max:'.$items];
        }

        if ($byAdmin) {
            $rules['details.rating'] = ['nullable', 'numeric', 'min:0', 'max:5'];
            $rules['details.review_count'] = ['nullable', 'integer', 'min:0', 'max:1000000'];
            $rules['details.is_premium'] = ['nullable', 'boolean'];
            $rules['details.use_admin_reviews'] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'details.student_count.integer' => 'The number of students must be a whole number.',
            'details.faculty_count.integer' => 'The number of faculty must be a whole number.',
            'details.rating.max' => 'The rating can be up to 5.',
            'details.quick_stats.*.value.required' => 'Each quick fact needs a value (e.g. "6 Labs").',
            'details.quick_stats.*.label.required' => 'Each quick fact needs a label (e.g. "Laboratories").',
            'details.faculty.*.name.required' => 'Each faculty member needs a name.',
            'details.events.*.title.required' => 'Each event needs a title.',
            'details.events.*.date.required' => 'Each event needs a date.',
            'details.events.*.date.date' => 'Each event needs a valid date.',
            'details.reviews.*.name.required' => 'Each review needs the reviewer\'s name.',
            'details.reviews.*.rating.required' => 'Each review needs a star rating.',
            'details.reviews.*.rating.between' => 'A review\'s rating is 1 to 5 stars.',
            'details.reviews.*.comment.required' => 'Each review needs its text.',
        ];
    }

    /**
     * The profile settings with the submitted details in (other settings stay). The rating,
     * review count, premium badge and which reviews show change only from the admin panel.
     *
     * @param  array<string, mixed>  $details  validated details
     * @param  array<string, mixed>  $meta  the settings saved now
     * @return array<string, mixed>
     */
    public static function toMeta(array $details, bool $byAdmin, array $meta): array
    {
        foreach (array_keys(self::TEXT_FIELDS) as $key) {
            $meta[$key] = trim((string) ($details[$key] ?? ''));
        }

        foreach (['student_count', 'faculty_count'] as $key) {
            $meta[$key] = is_numeric($details[$key] ?? null) ? (int) $details[$key] : '';
        }

        foreach (['about_highlights', 'why_choose_us', 'highlights'] as $key) {
            $meta[$key] = $details[$key] ?? [];
        }

        $meta['admission'] = array_merge((array) ($meta['admission'] ?? []), [
            'lead' => trim((string) ($details['admission_lead'] ?? '')),
            'highlights' => $details['admission_highlights'] ?? [],
            'details' => trim((string) ($details['admission_details'] ?? '')),
        ]);

        $meta['quick_stats'] = array_map(fn (array $row) => [
            'icon' => array_key_exists($row['icon'] ?? '', self::STAT_ICONS) ? $row['icon'] : 'fa-chalkboard',
            'label' => $row['label'] ?? '',
            'value' => $row['value'] ?? '',
        ], $details['quick_stats'] ?? []);

        $meta['faculty'] = array_map(fn (array $row) => [
            'name' => $row['name'] ?? '',
            'role' => $row['role'] ?? '',
            'subject' => $row['subject'] ?? '',
        ], $details['faculty'] ?? []);

        $meta['events'] = array_map(fn (array $row) => [
            'title' => $row['title'] ?? '',
            'date' => \Illuminate\Support\Carbon::parse($row['date'])->toDateString(),
            'time' => $row['time'] ?? '',
        ], $details['events'] ?? []);

        $meta['reviews'] = array_map(fn (array $row) => [
            'name' => $row['name'] ?? '',
            'relation' => $row['relation'] ?? '',
            'rating' => (int) ($row['rating'] ?? 5),
            'comment' => $row['comment'] ?? '',
            'ago' => $row['ago'] ?? '',
        ], $details['reviews'] ?? []);

        if ($byAdmin) {
            $meta['rating'] = is_numeric($details['rating'] ?? null) ? round((float) $details['rating'], 1) : '';
            $meta['review_count'] = is_numeric($details['review_count'] ?? null) ? (int) $details['review_count'] : '';
            $meta['is_premium'] = filter_var($details['is_premium'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $meta['use_admin_reviews'] = filter_var($details['use_admin_reviews'] ?? true, FILTER_VALIDATE_BOOLEAN);
        }

        return $meta;
    }
}
