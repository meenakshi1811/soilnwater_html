<?php

namespace App\Support;

class EducatorTuitionDelivery
{
    /** @var array<string, string> */
    public const MODES = [
        'home' => 'Home tuition',
        'personal' => 'Personal tuition',
        'online' => 'Online tuition',
        'tuition_point' => 'Tuition point',
    ];

    /**
     * @return array<string, array{enabled: bool, label: string, offerings: list<array<string, mixed>>}>
     */
    public static function normalizeStored(?array $stored): array
    {
        $result = [];

        foreach (self::MODES as $key => $label) {
            $row = is_array($stored[$key] ?? null) ? $stored[$key] : [];
            $legacyCharges = trim((string) ($row['charges'] ?? ''));
            $legacyTimings = trim((string) ($row['timings'] ?? ''));
            $offerings = self::normalizeOfferings($row['offerings'] ?? [], $key);

            if ($offerings === [] && ($legacyCharges !== '' || $legacyTimings !== '')) {
                $offerings[] = self::emptyOffering($key, [
                    'fee' => $legacyCharges,
                    'timings' => $legacyTimings,
                ]);
            }

            if ($offerings === []) {
                $offerings = [self::emptyOffering($key)];
            }

            $result[$key] = [
                'enabled' => (bool) ($row['enabled'] ?? false),
                'label' => $label,
                'offerings' => $offerings,
            ];
        }

        return $result;
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<array<string, string|bool>>
     */
    public static function normalizeOfferings(array $items, string $mode): array
    {
        return collect($items)
            ->map(function ($item) use ($mode) {
                if (! is_array($item)) {
                    return null;
                }

                $row = [
                    'class' => trim((string) ($item['class'] ?? '')),
                    'subject' => trim((string) ($item['subject'] ?? '')),
                    'board' => trim((string) ($item['board'] ?? '')),
                    'batch_strength' => $mode === 'online' ? '' : trim((string) ($item['batch_strength'] ?? '')),
                    'fee' => trim((string) ($item['fee'] ?? $item['charges'] ?? '')),
                    'timings' => trim((string) ($item['timings'] ?? '')),
                    'enrolment_open' => filter_var($item['enrolment_open'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'note' => trim((string) ($item['note'] ?? '')),
                ];

                if (collect($row)->except(['enrolment_open'])->filter()->isEmpty()) {
                    return null;
                }

                return $row;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $prefill
     * @return array<string, string|bool>
     */
    public static function emptyOffering(string $mode, array $prefill = []): array
    {
        return [
            'class' => (string) ($prefill['class'] ?? ''),
            'subject' => (string) ($prefill['subject'] ?? ''),
            'board' => (string) ($prefill['board'] ?? ''),
            'batch_strength' => $mode === 'online' ? '' : (string) ($prefill['batch_strength'] ?? ''),
            'fee' => (string) ($prefill['fee'] ?? ''),
            'timings' => (string) ($prefill['timings'] ?? ''),
            'enrolment_open' => (bool) ($prefill['enrolment_open'] ?? true),
            'note' => (string) ($prefill['note'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, array{enabled: bool, label: string, offerings: list<array<string, string|bool>>}>
     */
    public static function fromRequest(array $raw, bool $requestEnabled): array
    {
        $clean = [];

        foreach (self::MODES as $key => $label) {
            $row = is_array($raw[$key] ?? null) ? $raw[$key] : [];
            $enabled = $requestEnabled($key);
            $offerings = self::normalizeOfferings($row['offerings'] ?? [], $key);

            if (! $enabled) {
                continue;
            }

            $clean[$key] = [
                'enabled' => true,
                'label' => $label,
                'offerings' => $offerings !== [] ? $offerings : [self::emptyOffering($key)],
            ];
        }

        return $clean;
    }
}
