<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiumPaymentSubmission extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'profile_type',
        'profile_id',
        'expected_amount',
        'screenshot_path',
        'transaction_reference',
        'user_note',
        'status',
        'admin_note',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function profileTypeLabel(): string
    {
        return match ($this->profile_type) {
            'vendor' => 'Vendor',
            'consultant' => 'Consultant',
            'service' => 'Service Provider',
            default => ucfirst($this->profile_type),
        };
    }

    public function resolveProfile(): Vendor|Consultant|ServiceProvider|null
    {
        return match ($this->profile_type) {
            'vendor' => Vendor::query()->find($this->profile_id),
            'consultant' => Consultant::query()->find($this->profile_id),
            'service' => ServiceProvider::query()->find($this->profile_id),
            default => null,
        };
    }

    public function profileDisplayName(): string
    {
        $profile = $this->resolveProfile();

        if (! $profile) {
            return 'Unknown profile';
        }

        if (method_exists($profile, 'publicDisplayName')) {
            return $profile->publicDisplayName();
        }

        return $profile->display_name ?? $profile->company_name ?? 'Unknown profile';
    }

    public function screenshotUrl(): string
    {
        return asset($this->screenshot_path);
    }
}
