<?php

namespace App\Support;

class EducatorSubjects
{
    /**
     * @param  array<int, mixed>  $items
     * @return list<array{name: string, class: string, board: string, years_experience: string}>
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
                        'class' => '',
                        'board' => '',
                        'years_experience' => '',
                    ];
                }

                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                return [
                    'name' => $name,
                    'class' => trim((string) ($item['class'] ?? '')),
                    'board' => trim((string) ($item['board'] ?? '')),
                    'years_experience' => trim((string) ($item['years_experience'] ?? '')),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<array{name: string, class: string, board: string, years_experience: string}>
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
     * @return array{name: string, class: string, board: string, years_experience: string}
     */
    public static function emptyRow(): array
    {
        return [
            'name' => '',
            'class' => '',
            'board' => '',
            'years_experience' => '',
        ];
    }
}
