<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PremiumPrice extends Model
{
    public const TYPES = ['vendor', 'service', 'consultant'];

    protected $fillable = [
        'profile_type',
        'amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array{label: string, singular: string, icon: string, color: string, description: string}
     */
    public static function typeMeta(string $type): array
    {
        return match ($type) {
            'vendor' => [
                'label' => 'Vendors',
                'singular' => 'Vendor',
                'icon' => 'fa-store',
                'color' => 'green',
                'description' => 'Premium membership price for vendor profiles',
            ],
            'service' => [
                'label' => 'Service Providers',
                'singular' => 'Service Provider',
                'icon' => 'fa-screwdriver-wrench',
                'color' => 'orange',
                'description' => 'Premium membership price for service provider profiles',
            ],
            'consultant' => [
                'label' => 'Consultants',
                'singular' => 'Consultant',
                'icon' => 'fa-user-tie',
                'color' => 'blue',
                'description' => 'Premium membership price for consultant profiles',
            ],
            default => [
                'label' => ucfirst($type),
                'singular' => ucfirst($type),
                'icon' => 'fa-crown',
                'color' => 'green',
                'description' => 'Premium membership price',
            ],
        };
    }

    /**
     * Ensure one row exists for each supported profile type.
     *
     * @return Collection<int, self>
     */
    public static function ensureDefaults(): Collection
    {
        foreach (self::TYPES as $type) {
            self::query()->firstOrCreate(
                ['profile_type' => $type],
                ['amount' => 999.00, 'is_active' => true]
            );
        }

        $order = array_flip(self::TYPES);

        return self::query()
            ->whereIn('profile_type', self::TYPES)
            ->get()
            ->sortBy(fn (self $price) => $order[$price->profile_type] ?? 99)
            ->values();
    }

    public static function amountFor(string $type): float
    {
        $price = self::query()->where('profile_type', $type)->first();

        if (! $price) {
            $price = self::query()->firstOrCreate(
                ['profile_type' => $type],
                ['amount' => 999.00, 'is_active' => true]
            );
        }

        return (float) $price->amount;
    }

    public static function formatAmount(float|string|null $amount): string
    {
        return '₹'.number_format((float) $amount, 2);
    }

    public function getFormattedAmountAttribute(): string
    {
        return self::formatAmount($this->amount);
    }

    public function getMetaAttribute(): array
    {
        return self::typeMeta((string) $this->profile_type);
    }
}
