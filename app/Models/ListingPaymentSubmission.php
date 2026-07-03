<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingPaymentSubmission extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const TYPE_AD = 'ad';

    public const TYPE_OFFER = 'offer';

    protected $fillable = [
        'user_id',
        'listing_type',
        'listing_id',
        'amount',
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
            'amount' => 'decimal:2',
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

    public function listingTypeLabel(): string
    {
        return match ($this->listing_type) {
            self::TYPE_AD => 'Ad',
            self::TYPE_OFFER => 'Offer',
            default => ucfirst((string) $this->listing_type),
        };
    }

    public function resolveListing(): UserAd|Offer|null
    {
        return match ($this->listing_type) {
            self::TYPE_AD => UserAd::query()->find($this->listing_id),
            self::TYPE_OFFER => Offer::query()->find($this->listing_id),
            default => null,
        };
    }

    public function listingDisplayName(): string
    {
        $listing = $this->resolveListing();

        if (! $listing) {
            return 'Unknown '.strtolower($this->listingTypeLabel());
        }

        return $listing->title ?? 'Untitled';
    }

    public function screenshotUrl(): string
    {
        return asset($this->screenshot_path);
    }
}
