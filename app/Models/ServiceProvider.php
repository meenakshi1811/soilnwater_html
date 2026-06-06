<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class ServiceProvider extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'contact_person',
        'slug',
        'display_name',
        'logo',
        'phone',
        'whatsapp',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'pan_number',
        'gst_number',
        'government_certificate_number',
        'description',
        'gallery',
        'hero_main_heading',
        'hero_main_style',
        'hero_sub_heading',
        'hero_sub_style',
        'facebook_url',
        'instagram_url',
        'is_premium',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $profile): void {
            $profile->reports()->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'hero_main_style' => 'array',
            'hero_sub_style' => 'array',
            'is_premium' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(ServiceProviderBranch::class)->orderByDesc('is_primary')->orderBy('branch_name');
    }

    public function bannerSlides(): HasMany
    {
        return $this->hasMany(ServiceProviderBannerSlide::class)->orderBy('sort_order');
    }

    public function pageSections(): HasMany
    {
        return $this->hasMany(ServiceProviderPageSection::class)->orderBy('sort_order');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(ProfileReport::class, 'reportable');
    }

    public function services(): HasMany
    {
        return $this->hasMany(ServiceProviderService::class)->latest('updated_at');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function publicDisplayName(): string
    {
        return $this->display_name ?: $this->company_name;
    }

    public function formattedAddress(): string
    {
        $branch = $this->relationLoaded('branches')
            ? ($this->branches->firstWhere('is_primary', true) ?: $this->branches->first())
            : $this->branches()->first();

        $parts = array_filter([
            $branch?->address ?: $this->address ?: $this->user?->address,
            $branch?->city ?: $this->city ?: $this->user?->city,
            $branch?->state ?: $this->state,
            $branch?->pincode ?: $this->pincode ?: $this->user?->pincode,
        ], fn ($part) => filled($part));

        $uniqueParts = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);
            $normalizedPart = Str::lower($part);

            $alreadyIncluded = collect($uniqueParts)->contains(function (string $existingPart) use ($normalizedPart) {
                return str_contains(Str::lower($existingPart), $normalizedPart);
            });

            if (! $alreadyIncluded) {
                $uniqueParts[] = $part;
            }
        }

        return implode(', ', $uniqueParts);
    }

    public function serviceProviderUrl(): string
    {
        return url('/service/'.$this->slug);
    }

    public function service_providerUrl(): string
    {
        return $this->serviceProviderUrl();
    }

    public static function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'service-provider';
        $slug = $base;
        $counter = 1;

        while (static::query()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
