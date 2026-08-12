<?php

namespace App\Models;

use App\Models\Concerns\HasDefaultListingLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasDefaultListingLocation;

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
        'date_of_incorporation',
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
        'converted_from_user',
        'public_page_status',
        'pending_page_data',
        'published_page_data',
        'public_page_submitted_at',
        'public_page_approved_at',
        'public_page_approved_by',
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
            'converted_from_user' => 'boolean',
            'date_of_incorporation' => 'date',
            'approved_at' => 'datetime',
            'pending_page_data' => 'array',
            'published_page_data' => 'array',
            'public_page_submitted_at' => 'datetime',
            'public_page_approved_at' => 'datetime',
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

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNotNull('published_page_data')
                ->orWhere('public_page_status', 'approved');
        });
    }

    public function isPublicProfileLive(): bool
    {
        return $this->published_page_data !== null || $this->public_page_status === 'approved';
    }

    public function publicPageSnapshot(): array
    {
        $this->loadMissing(['bannerSlides', 'pageSections']);

        return [
            'profile' => $this->only([
                'slug', 'display_name', 'logo', 'phone', 'email', 'city', 'address',
                'facebook_url', 'instagram_url', 'description', 'hero_main_heading',
                'hero_main_style', 'hero_sub_heading', 'hero_sub_style',
            ]),
            'banner_slides' => $this->bannerSlides->map->only(['id', 'image_path', 'sort_order'])->values()->all(),
            'page_sections' => $this->pageSections->map->only(['id', 'title', 'content', 'image_path', 'sort_order'])->values()->all(),
        ];
    }

    public function usePublishedPage(): self
    {
        return $this->applyPageSnapshot($this->published_page_data);
    }

    public function usePendingPage(): self
    {
        return $this->applyPageSnapshot($this->pending_page_data);
    }

    private function applyPageSnapshot(mixed $snapshot): self
    {
        if (! is_array($snapshot)) {
            return $this;
        }

        $this->forceFill($snapshot['profile'] ?? []);
        $this->setRelation('bannerSlides', $this->snapshotModels($snapshot['banner_slides'] ?? [], VendorBannerSlide::class));
        $this->setRelation('pageSections', $this->snapshotModels($snapshot['page_sections'] ?? [], VendorPageSection::class));

        return $this;
    }

    private function snapshotModels(array $items, string $modelClass): Collection
    {
        return collect($items)->map(function (array $attributes) use ($modelClass) {
            $model = new $modelClass;
            $model->setRawAttributes($attributes, true);
            $model->exists = true;

            return $model;
        });
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
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
