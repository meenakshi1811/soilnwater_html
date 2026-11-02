<?php

namespace App\Support;

use App\Models\Institute;
use Illuminate\Support\Carbon;

/**
 * A school's fee structure, kept in institutes.profile_meta['fee_structure'] and shown on its own
 * page (schools/{slug}/fee-structure): a title, session and intro, any number of fee tables (its
 * own columns and rows, e.g. fee heads down the side and classes across, with an optional total
 * row), text sections (payment schedule, late fee, bank details…) and an optional PDF or image.
 */
final class InstituteFeeStructure
{
    public const MAX_TABLES = 10;

    public const MAX_COLUMNS = 12;

    public const MAX_ROWS = 60;

    public const MAX_SECTIONS = 12;

    /** The saved fee structure, tidied, or null when the school hasn't added one */
    public static function fromMeta(mixed $saved): ?array
    {
        if (! is_array($saved)) {
            return null;
        }

        $fee = self::clean($saved);

        return self::isEmpty($fee) ? null : $fee;
    }

    public static function isEmpty(array $fee): bool
    {
        return $fee['tables'] === [] && $fee['sections'] === [] && $fee['file'] === null && $fee['intro'] === '';
    }

    /**
     * Tidies a fee structure: text trimmed, empty columns, rows, tables and sections dropped, each
     * row as many values as its table has columns.
     *
     * @return array{title: string, session: string, effective_from: ?string, intro: string, tables: list<array>, sections: list<array{heading: string, text: string}>, file: ?string}
     */
    public static function clean(array $fee): array
    {
        $text = fn ($value) => is_scalar($value) ? trim((string) $value) : '';

        $tables = [];
        foreach (array_slice((array) ($fee['tables'] ?? []), 0, self::MAX_TABLES) as $table) {
            if (! is_array($table)) {
                continue;
            }

            $columns = array_map($text, array_slice(array_values((array) ($table['columns'] ?? [])), 0, self::MAX_COLUMNS));
            $rows = [];
            foreach (array_slice((array) ($table['rows'] ?? []), 0, self::MAX_ROWS) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $values = array_map($text, array_values((array) ($row['values'] ?? [])));
                $values = array_pad(array_slice($values, 0, count($columns)), count($columns), '');
                $rows[] = ['label' => $text($row['label'] ?? ''), 'values' => $values];
            }

            // (a column with no heading and nothing in it is dropped)
            foreach (array_reverse(array_keys($columns)) as $c) {
                if ($columns[$c] === '' && array_filter(array_column(array_column($rows, 'values'), $c)) === []) {
                    array_splice($columns, $c, 1);
                    foreach ($rows as &$row) {
                        array_splice($row['values'], $c, 1);
                    }
                    unset($row);
                }
            }

            $rows = array_values(array_filter($rows, fn (array $row) => $row['label'] !== '' || implode('', $row['values']) !== ''));
            if ($columns === [] || $rows === []) {
                continue;
            }

            $tables[] = [
                'title' => $text($table['title'] ?? ''),
                'row_heading' => $text($table['row_heading'] ?? ''),
                'columns' => $columns,
                'rows' => $rows,
                'show_total' => filter_var($table['show_total'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'note' => $text($table['note'] ?? ''),
            ];
        }

        $sections = [];
        foreach (array_slice((array) ($fee['sections'] ?? []), 0, self::MAX_SECTIONS) as $section) {
            if (is_array($section) && ($text($section['heading'] ?? '') !== '' || $text($section['text'] ?? '') !== '')) {
                $sections[] = ['heading' => $text($section['heading'] ?? ''), 'text' => $text($section['text'] ?? '')];
            }
        }

        $effective = $text($fee['effective_from'] ?? '');

        return [
            'title' => $text($fee['title'] ?? ''),
            'session' => $text($fee['session'] ?? ''),
            'effective_from' => $effective !== '' && strtotime($effective) !== false ? Carbon::parse($effective)->toDateString() : null,
            'intro' => $text($fee['intro'] ?? ''),
            'tables' => $tables,
            'sections' => $sections,
            'file' => filled($fee['file'] ?? null) ? (string) $fee['file'] : null,
        ];
    }

    /**
     * The rules for the submitted fee structure (tables come as JSON from the editor, decoded).
     *
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'session' => ['nullable', 'string', 'max:40'],
            'effective_from' => ['nullable', 'date'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'tables' => ['nullable', 'array', 'max:'.self::MAX_TABLES],
            'tables.*.title' => ['nullable', 'string', 'max:120'],
            'tables.*.row_heading' => ['nullable', 'string', 'max:60'],
            'tables.*.columns' => ['nullable', 'array', 'max:'.self::MAX_COLUMNS],
            'tables.*.columns.*' => ['nullable', 'string', 'max:60'],
            'tables.*.rows' => ['nullable', 'array', 'max:'.self::MAX_ROWS],
            'tables.*.rows.*.label' => ['nullable', 'string', 'max:120'],
            'tables.*.rows.*.values' => ['nullable', 'array', 'max:'.self::MAX_COLUMNS],
            'tables.*.rows.*.values.*' => ['nullable', 'string', 'max:60'],
            'tables.*.show_total' => ['nullable', 'boolean'],
            'tables.*.note' => ['nullable', 'string', 'max:1000'],
            'sections' => ['nullable', 'array', 'max:'.self::MAX_SECTIONS],
            'sections.*.heading' => ['nullable', 'string', 'max:120'],
            'sections.*.text' => ['nullable', 'string', 'max:5000'],
            'fee_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'remove_fee_file' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'tables.max' => 'You can add up to '.self::MAX_TABLES.' fee tables.',
            'tables.*.columns.max' => 'A fee table can have up to '.self::MAX_COLUMNS.' columns.',
            'tables.*.rows.max' => 'A fee table can have up to '.self::MAX_ROWS.' rows.',
            'tables.*.columns.*.max' => 'A column heading can be up to 60 characters.',
            'tables.*.rows.*.label.max' => 'A row name can be up to 120 characters.',
            'tables.*.rows.*.values.*.max' => 'A fee cell can be up to 60 characters.',
            'sections.max' => 'You can add up to '.self::MAX_SECTIONS.' sections.',
            'fee_file.mimes' => 'The fee structure file must be a PDF or an image (JPG, PNG, WebP).',
            'fee_file.max' => 'The fee structure file can be up to 10 MB.',
        ];
    }

    /** An amount in a cell ("2,500", "Rs. 1100/-", "₹ 4,500"), or null for text like "NA" */
    public static function amount(string $value): ?float
    {
        $clean = preg_replace('/(rs\.?|inr|₹|\/-|,|\s)/iu', '', $value) ?? '';

        return $clean !== '' && is_numeric($clean) ? (float) $clean : null;
    }

    /**
     * The total row: each column's amounts added up (null for a column without any).
     *
     * @return list<?string>
     */
    public static function totals(array $table): array
    {
        $totals = [];
        foreach (array_keys($table['columns']) as $c) {
            $amounts = array_filter(array_map(fn (array $row) => self::amount($row['values'][$c] ?? ''), $table['rows']), fn ($amount) => $amount !== null);
            $totals[] = $amounts === [] ? null : self::formatAmount(array_sum($amounts));
        }

        return $totals;
    }

    public static function formatAmount(float $amount): string
    {
        return number_format($amount, floor($amount) == $amount ? 0 : 2);
    }

    /**
     * Ready-made tables to start from, filled with the school's own classes.
     *
     * @return array<string, array{label: string, table: array}>
     */
    public static function presets(Institute $institute): array
    {
        $stages = InstituteGrades::byStage($institute->gradesOfferedEntries());
        $classes = array_column(array_merge(...array_column($stages, 'entries') ?: [[]]), 'class');
        if ($classes === []) {
            $classes = ['Nursery', 'LKG', 'UKG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12'];
            $stages = InstituteGrades::byStage(array_map(fn ($class) => ['class' => $class, 'sections' => null, 'students_per_section' => null], $classes));
        }

        // (one column per school stage, e.g. "LKG – UKG", "Class 1 – Class 5")
        $groups = array_values(array_map(function (array $stage) {
            $names = array_column($stage['entries'], 'class');

            return count($names) > 1 ? $names[0].' – '.$names[count($names) - 1] : $names[0];
        }, array_slice($stages, 0, self::MAX_COLUMNS)));
        $blank = fn (int $count) => array_fill(0, $count, '');

        return [
            'chart' => [
                'label' => 'Fee chart (fee heads × classes)',
                'table' => [
                    'title' => 'Fee chart',
                    'row_heading' => 'Fee head',
                    'columns' => $groups,
                    'rows' => array_map(fn ($head) => ['label' => $head, 'values' => $blank(count($groups))], ['Admission fee', 'Security deposit (refundable)', 'Monthly tuition fee', 'Annual charges', 'Development fee']),
                    'show_total' => true,
                    'note' => '',
                ],
            ],
            'monthly' => [
                'label' => 'Monthly fee by class',
                'table' => [
                    'title' => 'Monthly fee',
                    'row_heading' => 'Class',
                    'columns' => ['Monthly fee (₹)'],
                    'rows' => array_map(fn ($class) => ['label' => $class, 'values' => ['']], array_slice($classes, 0, self::MAX_ROWS)),
                    'show_total' => false,
                    'note' => '',
                ],
            ],
            'admission' => [
                'label' => 'One-time admission charges',
                'table' => [
                    'title' => 'At the time of admission',
                    'row_heading' => 'Item',
                    'columns' => ['Amount (₹)'],
                    'rows' => array_map(fn ($head) => ['label' => $head, 'values' => ['']], ['Registration fee', 'Prospectus', 'Admission fee', 'Security deposit (refundable)']),
                    'show_total' => true,
                    'note' => '',
                ],
            ],
            'annual' => [
                'label' => 'Annual charges (with month)',
                'table' => [
                    'title' => 'Annual charges',
                    'row_heading' => 'Charge',
                    'columns' => ['Amount (₹)', 'Paid in'],
                    'rows' => array_map(fn ($head) => ['label' => $head, 'values' => ['', '']], ['Annual function', 'Examination fee', 'Development fund']),
                    'show_total' => false,
                    'note' => '',
                ],
            ],
            'blank' => [
                'label' => 'Blank table',
                'table' => ['title' => '', 'row_heading' => '', 'columns' => ['Amount (₹)'], 'rows' => [['label' => '', 'values' => ['']]], 'show_total' => false, 'note' => ''],
            ],
        ];
    }
}
