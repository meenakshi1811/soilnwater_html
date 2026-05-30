<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Vendor extends Model
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
        return $this->hasMany(VendorBranch::class)->orderByDesc('is_primary')->orderBy('branch_name');
    }

    public function bannerSlides(): HasMany
    {
        return $this->hasMany(VendorBannerSlide::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(VendorProduct::class);
    }

    public function pageSections(): HasMany
    {
        return $this->hasMany(VendorPageSection::class)->orderBy('sort_order');
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
        $parts = array_filter([
            $this->address ?: $this->user?->address,
            $this->city ?: $this->user?->city,
            $this->state,
            $this->pincode ?: $this->user?->pincode,
        ], fn ($part) => filled($part));

        $uniqueParts = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);
            $normalizedPart = mb_strtolower($part);

            $alreadyIncluded = collect($uniqueParts)->contains(function (string $existingPart) use ($normalizedPart) {
                return str_contains(mb_strtolower($existingPart), $normalizedPart);
            });

            if (! $alreadyIncluded) {
                $uniqueParts[] = $part;
            }
        }

        return implode(', ', $uniqueParts);
    }

    public function storeUrl(): string
    {
        return url('/store/'.$this->slug);
    }

    public static function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'vendor';
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
