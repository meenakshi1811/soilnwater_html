<?php

namespace App\Support;

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
                    'years_experience' => trim((string) ($item['years_experience'] ?? '')),
                ];
            })
            ->filter()
            ->values()
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
