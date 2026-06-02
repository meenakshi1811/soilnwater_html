<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantService extends Model
{
    protected $fillable = [
        'consultant_id',
        'name',
        'slug',
        'category_id',
        'subcategory_id',
        'category',
        'short_description',
        'description',
        'consultation_type',
        'business_type',
        'service_area',
        'price',
        'consultation_charges',
        'consultation_charge_notes',
        'duration',
        'location',
        'latitude',
        'longitude',
        'is_online',
        'image_path',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_online' => 'boolean',
        'consultation_charges' => 'array',
        'consultation_charge_notes' => 'array',
        'approved_at' => 'datetime',
    ];



    /**
     * @return array<int, array{duration:string, price:string, note:string}>
     */
    public function consultationChargeRows(): array
    {
        $labels = [
            'minute' => 'Per Minute',
            'hour' => 'Per Hour',
            'day' => 'Per Day',
            'month' => 'Per Month',
            'contractual' => 'Contractual',
        ];

        $notes = collect($this->consultation_charge_notes ?: []);

        $rows = collect($this->consultation_charges ?: [])
            ->map(function ($charge, $key) use ($labels, $notes): ?array {
                if (is_array($charge)) {
                    $duration = (string) ($charge['duration'] ?? '');
                    $price = $charge['price'] ?? null;
                    $note = trim((string) ($notes->get($key) ?? ($charge['note'] ?? '')));
                } else {
                    $duration = (string) $key;
                    $price = $charge;
                    $note = '';
                }

                if ($duration === '' || $price === null || $price === '') {
                    return null;
                }

                return [
                    'duration' => $labels[$duration] ?? ucfirst($duration),
                    'price' => '₹'.number_format((float) $price, 2),
                    'note' => $note,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (! empty($rows)) {
            return $rows;
        }

        return [[
            'duration' => $this->duration ?: '-',
            'price' => '₹'.number_format((float) $this->price, 2),
            'note' => '',
        ]];
    }

    public function formattedConsultationCharges(): string
    {
        $labels = [
            'minute' => 'Per Minute',
            'hour' => 'Per Hour',
            'day' => 'Per Day',
            'month' => 'Per Month',
            'contractual' => 'Contractual',
        ];

        $notes = collect($this->consultation_charge_notes ?: []);

        $charges = collect($this->consultation_charges ?: [])
            ->map(function ($charge, $key) use ($labels, $notes): ?string {
                if (is_array($charge)) {
                    $duration = (string) ($charge['duration'] ?? '');
                    $price = $charge['price'] ?? null;
                    $note = trim((string) ($notes->get($key) ?? ($charge['note'] ?? '')));
                } else {
                    $duration = (string) $key;
                    $price = $charge;
                    $note = '';
                }

                if ($duration === '' || $price === null || $price === '') {
                    return null;
                }

                $formatted = ($labels[$duration] ?? ucfirst($duration)).': ₹'.number_format((float) $price, 2);

                return $note !== '' ? $formatted.' ('.$note.')' : $formatted;
            })
            ->filter()
            ->values();

        if ($charges->isNotEmpty()) {
            return $charges->implode(', ');
        }

        return '₹'.number_format((float) $this->price, 2);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategoryModel(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
