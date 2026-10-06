<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class InstituteGrades
{
    /**
     * @return list<array{class: string, sections: int|null, students_per_section: int|null}>
     */
    public static function normalizeList(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $entries = [];

        foreach ($raw as $item) {
            if (is_string($item)) {
                $class = trim($item);
                if ($class === '') {
                    continue;
                }
                $entries[] = [
                    'class' => $class,
                    'sections' => null,
                    'students_per_section' => null,
                ];

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $class = trim((string) ($item['class'] ?? $item['name'] ?? $item['grade'] ?? ''));
            if ($class === '') {
                continue;
            }

            $entries[] = [
                'class' => $class,
                'sections' => self::nullableInt($item['sections'] ?? null),
                'students_per_section' => self::nullableInt($item['students_per_section'] ?? null),
            ];
        }

        return $entries;
    }

    /**
     * @return list<array{class: string, sections: int|string|null, students_per_section: int|string|null}>
     */
    public static function forForm(mixed $stored, mixed $old = null): array
    {
        if (is_array($old) && $old !== []) {
            $rows = [];
            foreach ($old as $item) {
                if (is_string($item)) {
                    $rows[] = [
                        'class' => $item,
                        'sections' => '',
                        'students_per_section' => '',
                    ];

                    continue;
                }
                if (! is_array($item)) {
                    continue;
                }
                $rows[] = [
                    'class' => (string) ($item['class'] ?? $item['name'] ?? ''),
                    'sections' => self::formValue($item['sections'] ?? null),
                    'students_per_section' => self::formValue($item['students_per_section'] ?? null),
                ];
            }

            $rows = array_values(array_filter($rows, fn (array $row) => $row['class'] !== ''
                || $row['sections'] !== ''
                || $row['students_per_section'] !== ''));

            return $rows !== [] ? $rows : [self::emptyFormRow()];
        }

        $normalized = self::normalizeList($stored);

        if ($normalized === []) {
            return [self::emptyFormRow()];
        }

        return array_map(fn (array $entry) => [
            'class' => $entry['class'],
            'sections' => self::formValue($entry['sections']),
            'students_per_section' => self::formValue($entry['students_per_section']),
        ], $normalized);
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $rows
     * @return list<array{class: string, sections: int|null, students_per_section: int|null}>
     */
    public static function fromValidated(?array $rows): array
    {
        return self::normalizeList($rows ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(bool $sectionEnabled): array
    {
        return [
            'grades_offered' => [
                Rule::requiredIf($sectionEnabled),
                'array',
                Rule::when($sectionEnabled, ['min:1']),
            ],
            'grades_offered.*.class' => [
                Rule::requiredIf($sectionEnabled),
                'string',
                'max:80',
            ],
            'grades_offered.*.sections' => ['nullable', 'integer', 'min:0', 'max:999'],
            'grades_offered.*.students_per_section' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'grades_offered.required' => 'Add at least one grade or class when this section is enabled.',
            'grades_offered.min' => 'Add at least one grade or class when this section is enabled.',
            'grades_offered.*.class.required' => 'Each class name is required.',
            'grades_offered.*.class.max' => 'Class name cannot exceed 80 characters.',
            'grades_offered.*.sections.integer' => 'Number of sections must be a whole number.',
            'grades_offered.*.sections.min' => 'Number of sections cannot be negative.',
            'grades_offered.*.sections.max' => 'Number of sections cannot exceed 999.',
            'grades_offered.*.students_per_section.integer' => 'Students per section must be a whole number.',
            'grades_offered.*.students_per_section.min' => 'Students per section cannot be negative.',
            'grades_offered.*.students_per_section.max' => 'Students per section cannot exceed 9999.',
        ];
    }

    public static function totalStudents(array $entry): ?int
    {
        $sections = $entry['sections'] ?? null;
        $perSection = $entry['students_per_section'] ?? null;
        if ($sections === null || $perSection === null || $sections <= 0 || $perSection <= 0) {
            return null;
        }

        return $sections * $perSection;
    }

    /**
     * @return array{class: string, sections: int|string|null, students_per_section: int|string|null}
     */
    private static function emptyFormRow(): array
    {
        return [
            'class' => '',
            'sections' => '',
            'students_per_section' => '',
        ];
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private static function formValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (string) $value;
    }
}
