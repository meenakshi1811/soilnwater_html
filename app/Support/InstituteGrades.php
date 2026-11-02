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

    /** School stages in order, with the class ranks each covers (pre-primary is below Class 1) */
    private const STAGES = [
        'pre-primary' => ['label' => 'Pre-primary', 'from' => -9, 'to' => 0],
        'primary' => ['label' => 'Primary', 'from' => 1, 'to' => 5],
        'middle' => ['label' => 'Middle', 'from' => 6, 'to' => 8],
        'secondary' => ['label' => 'Secondary', 'from' => 9, 'to' => 10],
        'senior-secondary' => ['label' => 'Senior secondary', 'from' => 11, 'to' => 12],
    ];

    private const ROMAN = ['i' => 1, 'ii' => 2, 'iii' => 3, 'iv' => 4, 'v' => 5, 'vi' => 6, 'vii' => 7, 'viii' => 8, 'ix' => 9, 'x' => 10, 'xi' => 11, 'xii' => 12];

    /**
     * The classes grouped into school stages, each in class order (Nursery, LKG, UKG, then Class 1
     * to 12); names it can't place (e.g. a college course) keep their order under "Other".
     *
     * @param  list<array{class: string, sections: int|null, students_per_section: int|null}>  $entries
     * @return list<array{key: string, label: string, entries: list<array{class: string, sections: int|null, students_per_section: int|null, total: int|null}>}>
     */
    public static function byStage(array $entries): array
    {
        $grouped = [];
        foreach (array_values($entries) as $index => $entry) {
            $rank = self::classRank($entry['class']);
            $key = 'other';
            foreach (self::STAGES as $stageKey => $stage) {
                if ($rank !== null && $rank >= $stage['from'] && $rank <= $stage['to']) {
                    $key = $stageKey;
                    break;
                }
            }
            $grouped[$key][] = ['rank' => $rank ?? 99, 'index' => $index, 'entry' => $entry + ['total' => self::totalStudents($entry)]];
        }

        $stages = [];
        foreach ([...array_keys(self::STAGES), 'other'] as $key) {
            if (empty($grouped[$key])) {
                continue;
            }
            usort($grouped[$key], fn (array $a, array $b) => [$a['rank'], $a['index']] <=> [$b['rank'], $b['index']]);
            $stages[] = [
                'key' => $key,
                'label' => self::STAGES[$key]['label'] ?? 'Other',
                'entries' => array_column($grouped[$key], 'entry'),
            ];
        }

        return $stages;
    }

    /**
     * The lowest and highest class in school order, e.g. ['LKG', 'Class 12'] (classes it can't
     * place count only when there are no others, then in the order entered); null for none.
     *
     * @param  list<array{class: string, sections: int|null, students_per_section: int|null}>  $entries
     * @return array{0: string, 1: string}|null
     */
    public static function range(array $entries): ?array
    {
        $stages = self::byStage($entries);
        $placed = array_filter($stages, fn (array $stage) => $stage['key'] !== 'other');
        $classes = array_column(array_merge(...array_column($placed ?: $stages, 'entries')), 'class');

        return $classes === [] ? null : [$classes[0], $classes[count($classes) - 1]];
    }

    /**
     * Totals for the classes: how many, and the sections and students where they're given.
     *
     * @return array{classes: int, sections: int|null, students: int|null}
     */
    public static function totals(array $entries): array
    {
        $sections = array_filter(array_column($entries, 'sections'), fn ($value) => $value !== null);
        $students = array_filter(array_map(fn (array $entry) => self::totalStudents($entry), $entries), fn ($value) => $value !== null);

        return [
            'classes' => count($entries),
            'sections' => $sections !== [] ? array_sum($sections) : null,
            'students' => $students !== [] ? array_sum($students) : null,
        ];
    }

    /**
     * Where a class name sits in school order: Play group -4, Nursery -3, LKG -2, UKG -1, then
     * Class 1 to 12 ("Class 6", "Grade VI", "Std 10", "6th"); null when it isn't a school class.
     */
    public static function classRank(string $name): ?float
    {
        $name = strtolower($name);
        $name = trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/[^a-z0-9]+/', ' ', $name)));

        $preprimary = [
            '/^(play ?group|play ?school|pre ?nursery|toddler)/' => -4,
            '/^nursery/' => -3,
            '/^(lkg|l ?k ?g|lower kg|lower kindergarten|junior kg|jr kg|kg ?1|kg i)\b/' => -2,
            '/^(ukg|u ?k ?g|upper kg|upper kindergarten|senior kg|sr kg|kg ?2|kg ii)\b/' => -1,
            '/^(kg|kindergarten)$/' => -1.5,
        ];
        foreach ($preprimary as $pattern => $rank) {
            if (preg_match($pattern, $name)) {
                return $rank;
            }
        }

        $number = null;
        if (preg_match('/^(?:(?:class|grade|std|standard) ?)?(\d{1,2})(?:st|nd|rd|th)?\b/', $name, $match)) {
            $number = (int) $match[1];
        } elseif (preg_match('/^(?:class|grade|std|standard) (xii|xi|x|ix|viii|vii|vi|v|iv|iii|ii|i)\b/', $name, $match)
            || preg_match('/^(xii|xi|x|ix|viii|vii|vi|v|iv|iii|ii|i)$/', $name, $match)) {
            $number = self::ROMAN[$match[1]];
        }

        return $number !== null && $number >= 1 && $number <= 12 ? $number : null;
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
