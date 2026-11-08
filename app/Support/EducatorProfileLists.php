<?php

namespace App\Support;

use App\Models\Educator;

/**
 * Cleans the list fields of a teacher / tutor's profile (as the profile form and the public page's
 * inline editor submit them) into what is stored: blank entries dropped, text trimmed.
 */
final class EducatorProfileLists
{
    /**
     * Single lines (languages, teaching modes, achievements, certifications).
     *
     * @param  array<int, mixed>|string|null  $items  a list, or text with one item per line
     * @return list<string>
     */
    public static function strings(array|string|null $items): array
    {
        if (is_string($items)) {
            $items = preg_split('/\R/', $items) ?: [];
        }

        return collect($items ?? [])
            ->map(fn ($item) => is_string($item) ? trim($item) : '')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Experience rows, each with its duration worked out.
     *
     * @param  array<int, mixed>  $items
     * @return list<array{title: string, organization: string, start_year: string, end_year: string, is_current: bool, duration: string, description: string}>
     */
    public static function experiences(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                if (! is_array($item)) {
                    return null;
                }

                $title = trim((string) ($item['title'] ?? ''));
                $organization = trim((string) ($item['organization'] ?? ''));
                $description = trim((string) ($item['description'] ?? ''));
                $startYear = trim((string) ($item['start_year'] ?? ''));
                $isCurrent = filter_var($item['is_current'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $endYear = $isCurrent ? '' : trim((string) ($item['end_year'] ?? ''));

                if ($title === '' && $organization === '' && $startYear === '' && $endYear === '' && $description === '') {
                    return null;
                }

                return [
                    'title' => $title,
                    'organization' => $organization,
                    'start_year' => $startYear,
                    'end_year' => $endYear,
                    'is_current' => $isCurrent,
                    'duration' => Educator::formatExperienceDuration($startYear, $endYear, $isCurrent),
                    'description' => $description,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Rows of the given text fields (e.g. qualifications: degree, institution, year); empty rows dropped.
     *
     * @param  array<int, mixed>  $items
     * @param  list<string>  $keys
     * @return list<array<string, string>>
     */
    public static function rows(array $items, array $keys): array
    {
        return collect($items)
            ->map(function ($item) use ($keys) {
                if (! is_array($item)) {
                    return null;
                }
                $row = [];
                foreach ($keys as $key) {
                    $row[$key] = trim((string) ($item[$key] ?? ''));
                }
                if (collect($row)->filter()->isEmpty()) {
                    return null;
                }

                return $row;
            })
            ->filter()
            ->values()
            ->all();
    }
}
