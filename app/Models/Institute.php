<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Institute extends Model
{
    protected $fillable = [
        'user_id',
        'institution_name',
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
        'place_id',
        'latitude',
        'longitude',
        'institution_type',
        'board_affiliation',
        'grades_offered',
        'facilities',
        'pan_number',
        'gst_number',
        'government_certificate_number',
        'date_of_establishment',
        'tagline',
        'about',
        'description',
        'gallery',
        'website_url',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'is_verified',
        'status',
        'converted_from_user',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'grades_offered' => 'array',
            'facilities' => 'array',
            'gallery' => 'array',
            'is_verified' => 'boolean',
            'converted_from_user' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'date_of_establishment' => 'date',
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

    public function enquiries(): HasMany
    {
        return $this->hasMany(InstituteEnquiry::class)->latest();
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

    public function roleLabel(): string
    {
        return 'School / Institute';
    }

    public function displayName(): string
    {
        return $this->display_name ?: $this->institution_name ?: 'Institute';
    }

    public function logoUrl(): ?string
    {
        return filled($this->logo) ? asset($this->logo) : null;
    }

    public function publicUrl(): string
    {
        return route('institute.show', $this->slug);
    }

    public function locationLabel(): string
    {
        return collect([$this->city, $this->state])->filter()->implode(', ');
    }

    public function institutionTypeLabel(): string
    {
        return match ($this->institution_type) {
            'school' => 'School',
            'college' => 'College',
            'university' => 'University',
            'coaching' => 'Coaching Institute',
            default => $this->institution_type ? ucfirst(str_replace('_', ' ', $this->institution_type)) : 'Institute',
        };
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public static function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'institute';
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
