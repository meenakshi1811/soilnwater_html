<?php

namespace App\Support;

/**
 * The "Courses & Programs Offered" cards on a school's page (and the Class XI & XII streams): what
 * the school saved in its profile (profile_meta), or the standard ones until it saves its own.
 */
final class InstituteCourseWings
{
    /** Icons a card can have: Font Awesome class => name in the form */
    public const ICONS = [
        'fa-child' => 'Young learners',
        'fa-book-open' => 'Book',
        'fa-user-graduate' => 'Student',
        'fa-graduation-cap' => 'Graduation cap',
        'fa-flask' => 'Science',
        'fa-calculator' => 'Maths / commerce',
        'fa-palette' => 'Arts',
        'fa-laptop-code' => 'Computers',
        'fa-language' => 'Languages',
        'fa-music' => 'Music',
        'fa-futbol' => 'Sports',
        'fa-briefcase' => 'Vocational',
    ];

    public const DEFAULT_WINGS = [
        ['icon' => 'fa-child', 'title' => 'Primary Wing', 'grades' => 'Nursery to Class V', 'description' => 'Activity-based learning with language, numeracy, and creative play.', 'image' => ''],
        ['icon' => 'fa-book-open', 'title' => 'Middle Wing', 'grades' => 'Class VI to VIII', 'description' => 'Concept building across science, maths, and humanities with project work.', 'image' => ''],
        ['icon' => 'fa-user-graduate', 'title' => 'Secondary Wing', 'grades' => 'Class IX to X', 'description' => 'Board-focused curriculum with weekly assessments and remedial support.', 'image' => ''],
        ['icon' => 'fa-graduation-cap', 'title' => 'Senior Secondary', 'grades' => 'Class XI to XII', 'description' => 'Specialized streams with career counselling and competitive exam prep.', 'image' => ''],
    ];

    public const DEFAULT_STREAMS = ['Science (PCM)', 'Science (PCB)', 'Commerce', 'Humanities'];

    /**
     * The saved cards, cleaned (a card needs a title; an unknown icon becomes the book), or the
     * standard ones for a school that hasn't saved any yet (a saved empty list stays empty). A card's
     * image is its photo's path ('' for none: the icon shows).
     *
     * @return list<array{icon: string, title: string, grades: string, description: string, image: string}>
     */
    public static function wings(mixed $saved): array
    {
        return is_array($saved) ? self::cleanWings($saved) : self::DEFAULT_WINGS;
    }

    /** @return list<string> */
    public static function streams(mixed $saved): array
    {
        return is_array($saved) ? self::cleanStreams($saved) : self::DEFAULT_STREAMS;
    }

    /** @return list<array{icon: string, title: string, grades: string, description: string, image: string}> */
    public static function cleanWings(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $wings = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $icon = (string) ($row['icon'] ?? '');
            $wings[] = [
                'icon' => array_key_exists($icon, self::ICONS) ? $icon : 'fa-book-open',
                'title' => $title,
                'grades' => trim((string) ($row['grades'] ?? '')),
                'description' => trim((string) ($row['description'] ?? '')),
                'image' => is_string($row['image'] ?? null) ? trim($row['image']) : '',
            ];
        }

        return $wings;
    }

    /** @return list<string> */
    public static function cleanStreams(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn ($stream) => is_string($stream) ? trim($stream) : '', $rows),
            fn (string $stream) => $stream !== ''
        ));
    }

    /**
     * Form rows with anything in them (a blank, just-added card is dropped before checking; a photo
     * alone counts, so a card with only a photo is asked for its title).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function filledRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, fn ($row) => is_array($row)
            && (trim((string) ($row['title'] ?? '').($row['grades'] ?? '').($row['description'] ?? '').($row['image'] ?? '')) !== ''
                || ! empty($row['has_upload']))));
    }
}
