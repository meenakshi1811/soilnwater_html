<?php

namespace App\Support;

use App\Models\Educator;
use Illuminate\Http\UploadedFile;

class EducatorSubjects
{
    /**
     * @param  array<int, mixed>  $items
     * @return list<array{name: string, classes: list<string>, boards: list<string>, years_experience: string}>
     */
    public static function normalizeList(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                if (! is_array($item)) {
                    $name = trim((string) $item);
                    if ($name === '') {
                        return null;
                    }

                    return [
                        'name' => $name,
                        'classes' => [],
                        'boards' => [],
                        'years_experience' => '',
                        'image' => '',
                    ];
                }

                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                return [
                    'name' => $name,
                    'classes' => self::normalizeStringList($item['classes'] ?? null, $item['class'] ?? null),
                    'boards' => self::normalizeStringList($item['boards'] ?? null, $item['board'] ?? null),
                    'years_experience' => trim((string) ($item['years_experience'] ?? $item['years'] ?? $item['experience'] ?? '')),
                    // A picture for the subject's card (a path under public/), or ''
                    'image' => self::cleanImagePath($item['image'] ?? ''),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private static function cleanImagePath(mixed $path): string
    {
        $path = trim((string) $path);

        return ($path !== '' && str_starts_with($path, 'uploads/educators/subjects/') && ! str_contains($path, '..')) ? $path : '';
    }

    /**
     * Subject pictures from the profile form, before fromFormSubmission(): a new upload replaces a
     * subject's picture, "remove" clears it, otherwise it keeps the one it had. Only pictures the
     * teacher's subjects already had are accepted from the form (no other paths).
     *
     * @param  array<int|string, mixed>  $submitted  the form's subjects rows
     * @param  array<int|string, mixed>  $uploads  $request->file('subjects')
     * @param  list<array<string, mixed>>  $previous  the subjects as saved before
     * @return array<int|string, mixed>
     */
    public static function withImages(array $submitted, array $uploads, array $previous): array
    {
        $allowed = array_values(array_filter(array_column(self::normalizeList($previous), 'image')));

        foreach ($submitted as $key => $row) {
            if (! is_array($row)) {
                continue;
            }

            $path = trim((string) ($row['image'] ?? ''));
            if (! in_array($path, $allowed, true)) {
                $path = '';
            }
            if (filter_var($row['remove_image'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $path = '';
            }

            $file = is_array($uploads[$key] ?? null) ? ($uploads[$key]['image_file'] ?? null) : null;
            if ($file instanceof UploadedFile && $file->isValid() && trim((string) ($row['name'] ?? '')) !== '') {
                $path = EducatorFileUploader::storeImage($file, 'subjects');
            }

            $row['image'] = $path;
            unset($row['image_file'], $row['remove_image']);
            $submitted[$key] = $row;
        }

        return $submitted;
    }

    /**
     * Pictures the subjects had before that the new list no longer uses (to delete their files).
     *
     * @param  list<array<string, mixed>>  $previous
     * @param  list<array<string, mixed>>  $current
     * @return list<string>
     */
    public static function unusedImages(array $previous, array $current): array
    {
        $used = array_filter(array_column(self::normalizeList($current), 'image'));

        return array_values(array_diff(array_filter(array_column(self::normalizeList($previous), 'image')), $used));
    }

    /**
     * For a form without picture fields (the public page's inline editor): each subject keeps the
     * picture of the saved subject with the same name.
     *
     * @param  list<array<string, mixed>>  $subjects
     * @param  list<array<string, mixed>>  $previous
     * @return list<array<string, mixed>>
     */
    public static function keepImagesByName(array $subjects, array $previous): array
    {
        $byName = [];
        foreach (self::normalizeList($previous) as $old) {
            if ($old['image'] !== '') {
                $byName[mb_strtolower($old['name'])] = $old['image'];
            }
        }

        foreach ($subjects as $i => $subject) {
            if (($subject['image'] ?? '') === '' && isset($byName[mb_strtolower((string) ($subject['name'] ?? ''))])) {
                $subjects[$i]['image'] = $byName[mb_strtolower((string) $subject['name'])];
            }
        }

        return $subjects;
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<array{name: string, classes: list<string>, boards: list<string>, years_experience: string}>
     */
    public static function fromFormSubmission(array $items): array
    {
        $prepared = collect($items)
            ->map(function ($item) {
                if (! is_array($item)) {
                    return $item;
                }

                $classes = $item['classes'] ?? null;
                if (! is_array($classes) || $classes === []) {
                    $classes = self::normalizeStringList($item['classes_lines'] ?? null);
                } else {
                    $classes = self::normalizeStringList($classes);
                }

                $boards = $item['boards'] ?? null;
                if (! is_array($boards) || $boards === []) {
                    $boards = self::normalizeStringList($item['boards_lines'] ?? null);
                } else {
                    $boards = self::normalizeStringList($boards);
                }

                return [
                    'name' => $item['name'] ?? '',
                    'classes' => $classes,
                    'boards' => $boards,
                    'years_experience' => $item['years_experience'] ?? '',
                    'image' => $item['image'] ?? '',
                ];
            })
            ->all();

        return self::normalizeList($prepared);
    }

    /**
     * Subjects prepared for the public profile, with legacy profile-level fallbacks.
     *
     * @return list<array{name: string, classes: list<string>, boards: list<string>, years_experience: string}>
     */
    public static function forPublicDisplay(Educator $educator): array
    {
        $subjects = self::normalizeList($educator->subjects ?? []);
        if ($subjects === []) {
            return [];
        }

        $profileClasses = self::normalizeStringList($educator->classes ?? null);
        $profileBoards = self::normalizeStringList($educator->boards ?? null);
        $profileYears = trim((string) ($educator->years_experience ?? ''));

        $anySubjectHasClasses = collect($subjects)->contains(fn (array $subject) => ($subject['classes'] ?? []) !== []);
        $anySubjectHasBoards = collect($subjects)->contains(fn (array $subject) => ($subject['boards'] ?? []) !== []);
        $anySubjectHasYears = collect($subjects)->contains(fn (array $subject) => ($subject['years_experience'] ?? '') !== '');

        return collect($subjects)
            ->map(function (array $subject) use (
                $profileClasses,
                $profileBoards,
                $profileYears,
                $anySubjectHasClasses,
                $anySubjectHasBoards,
                $anySubjectHasYears
            ) {
                if (($subject['classes'] ?? []) === [] && ! $anySubjectHasClasses && $profileClasses !== []) {
                    $subject['classes'] = $profileClasses;
                }

                if (($subject['boards'] ?? []) === [] && ! $anySubjectHasBoards && $profileBoards !== []) {
                    $subject['boards'] = $profileBoards;
                }

                if (($subject['years_experience'] ?? '') === '' && ! $anySubjectHasYears && $profileYears !== '') {
                    $subject['years_experience'] = $profileYears;
                }

                return $subject;
            })
            ->all();
    }

    /**
     * @param  list<array{name: string, classes: list<string>, boards: list<string>, years_experience: string}>  $subjects
     * @return list<string>
     */
    public static function aggregateClasses(array $subjects): array
    {
        return self::aggregateUniqueValues($subjects, 'classes');
    }

    /**
     * @param  list<array{name: string, classes: list<string>, boards: list<string>, years_experience: string}>  $subjects
     * @return list<string>
     */
    public static function aggregateBoards(array $subjects): array
    {
        return self::aggregateUniqueValues($subjects, 'boards');
    }

    /**
     * @param  list<array<string, mixed>>  $subjects
     * @return list<string>
     */
    public static function aggregateUniqueValues(array $subjects, string $key): array
    {
        return collect($subjects)
            ->flatMap(fn ($subject) => is_array($subject[$key] ?? null) ? $subject[$key] : [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<array{name: string, classes: list<string>, boards: list<string>, years_experience: string}>
     */
    public static function forForm(mixed $stored, mixed $old = null): array
    {
        if (is_array($old) && $old !== []) {
            $list = self::normalizeList($old);

            return $list !== [] ? $list : [self::emptyRow()];
        }

        $list = self::normalizeList(is_array($stored) ? $stored : []);

        return $list !== [] ? $list : [self::emptyRow()];
    }

    /**
     * @return array{name: string, classes: list<string>, boards: list<string>, years_experience: string}
     */
    public static function emptyRow(): array
    {
        return [
            'name' => '',
            'classes' => [],
            'boards' => [],
            'years_experience' => '',
            'image' => '',
        ];
    }

    /**
     * @param  list<string>  $values
     */
    public static function toLines(array $values): string
    {
        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode("\n");
    }

    /**
     * @param  mixed  $values
     * @param  mixed  $legacySingle
     * @return list<string>
     */
    private static function normalizeStringList(mixed $values, mixed $legacySingle = null): array
    {
        if (is_array($values)) {
            return collect($values)
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $legacy = trim((string) $legacySingle);
        if ($legacy !== '') {
            return collect(preg_split('/\s*,\s*/', $legacy) ?: [])
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $text = trim((string) $values);
        if ($text === '') {
            return [];
        }

        if (str_contains($text, "\n")) {
            return collect(preg_split('/\r?\n/', $text) ?: [])
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return collect(preg_split('/\s*,\s*/', $text) ?: [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
