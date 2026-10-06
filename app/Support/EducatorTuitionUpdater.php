<?php

namespace App\Support;

use App\Models\Educator;
use Illuminate\Http\Request;

final class EducatorTuitionUpdater
{
    /**
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        return [
            'take_tuitions' => ['nullable', 'boolean'],
            'is_available_now' => ['nullable', 'boolean'],
            'tuition_batches' => ['nullable', 'array'],
            'tuition_batches.*.class' => ['nullable', 'string', 'max:80'],
            'tuition_batches.*.subject' => ['nullable', 'string', 'max:80'],
            'tuition_batches.*.batch_type' => ['nullable', 'string', 'max:80'],
            'tuition_batches.*.student_count' => ['nullable', 'string', 'max:20'],
            'tuition_batches.*.cost' => ['nullable', 'string', 'max:120'],
            'tuition_batches.*.seats_status' => ['nullable', 'in:available,full'],
            'tuition_point_address' => ['nullable', 'string', 'max:500'],
            'tuition_place_id' => ['nullable', 'string', 'max:255'],
            'tuition_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'tuition_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'tuition_timings' => ['nullable', 'string', 'max:255'],
            'tuition_charges' => ['nullable', 'string', 'max:255'],
            'tuition_delivery_options' => ['nullable', 'array'],
            'availability' => ['nullable', 'array'],
            'availability.*.day' => ['nullable', 'string', 'max:40'],
            'availability.*.slots' => ['nullable', 'string', 'max:255'],
        ];
    }

    public static function apply(Educator $educator, Request $request, array $validated): void
    {
        $validated['tuition_batches'] = self::cleanTuitionBatches($validated['tuition_batches'] ?? []);
        $validated['tuition_delivery_options'] = EducatorTuitionDelivery::fromRequest(
            $validated['tuition_delivery_options'] ?? [],
            fn (string $key) => $request->boolean('tuition_delivery_options.'.$key.'.enabled')
        );
        $validated['tuition_classes'] = collect($validated['tuition_batches'])->pluck('class')->filter()->unique()->values()->all();
        $validated['tuition_subjects'] = collect($validated['tuition_batches'])->pluck('subject')->filter()->unique()->values()->all();
        $validated['tuition_types'] = collect($validated['tuition_batches'])->pluck('batch_type')->filter()->unique()->values()->all();
        $validated['availability'] = self::cleanObjectList($validated['availability'] ?? [], ['day', 'slots']);
        $validated['take_tuitions'] = $request->boolean('take_tuitions');
        $validated['is_available_now'] = $request->boolean('is_available_now');
        $validated['tuition_point_address'] = trim((string) ($validated['tuition_point_address'] ?? ''));

        if ($validated['tuition_point_address'] === '') {
            $validated['tuition_place_id'] = null;
            $validated['tuition_latitude'] = null;
            $validated['tuition_longitude'] = null;
        }

        $validated['tuition_location'] = $validated['tuition_point_address'] ?: null;

        $educator->update(collect($validated)->only([
            'take_tuitions',
            'is_available_now',
            'tuition_batches',
            'tuition_delivery_options',
            'tuition_classes',
            'tuition_subjects',
            'tuition_types',
            'tuition_point_address',
            'tuition_place_id',
            'tuition_latitude',
            'tuition_longitude',
            'tuition_timings',
            'tuition_charges',
            'tuition_location',
            'availability',
        ])->all());
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<array{class: string, subject: string, batch_type: string, student_count: string, cost: string, seats_status: string}>
     */
    private static function cleanTuitionBatches(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                if (! is_array($item)) {
                    return null;
                }

                $seats = trim((string) ($item['seats_status'] ?? 'available'));
                if (! in_array($seats, ['available', 'full'], true)) {
                    $seats = 'available';
                }

                $row = [
                    'class' => trim((string) ($item['class'] ?? '')),
                    'subject' => trim((string) ($item['subject'] ?? '')),
                    'batch_type' => trim((string) ($item['batch_type'] ?? '')),
                    'student_count' => trim((string) ($item['student_count'] ?? '')),
                    'cost' => trim((string) ($item['cost'] ?? '')),
                    'seats_status' => $seats,
                ];

                if (collect($row)->except(['seats_status'])->filter()->isEmpty()) {
                    return null;
                }

                return $row;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $items
     * @param  list<string>  $keys
     * @return list<array<string, string>>
     */
    private static function cleanObjectList(array $items, array $keys): array
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
