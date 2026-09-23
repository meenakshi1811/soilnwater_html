<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        'brochure_path',
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

    public function notices(): HasMany
    {
        return $this->hasMany(InstituteNotice::class)->latest();
    }

    public function diaryHolidays(): HasMany
    {
        return $this->hasMany(InstituteDiaryHoliday::class)->orderBy('start_date');
    }

    public function leaveRules(): HasMany
    {
        return $this->hasMany(InstituteLeaveRule::class)->orderBy('sort_order');
    }

    public function activeNotices(): HasMany
    {
        return $this->notices()->active();
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(InstituteAchievement::class)->orderBy('sort_order')->orderByDesc('year');
    }

    public function topPerformers(): HasMany
    {
        return $this->hasMany(InstituteTopPerformer::class)->orderBy('sort_order')->orderBy('rank');
    }

    public function schoolClasses(): HasMany
    {
        return $this->hasMany(InstituteClass::class)->orderBy('sort_order')->orderBy('name');
    }

    public function books(): HasMany
    {
        return $this->hasMany(InstituteBook::class)->orderBy('sort_order')->orderBy('title');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'institute_followers')->withTimestamps();
    }

    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'institute_bookmarks')->withTimestamps();
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(InstituteEngagement::class)->latest();
    }

    public function profileFeedbacks(): HasMany
    {
        return $this->hasMany(InstituteProfileFeedback::class);
    }

    public function profileReports(): MorphMany
    {
        return $this->morphMany(ProfileReport::class, 'reportable');
    }

    public function brochureUrl(): ?string
    {
        return filled($this->brochure_path) ? asset($this->brochure_path) : null;
    }

    public function galleryUrls(): array
    {
        return collect($this->gallery ?? [])
            ->filter(fn ($path) => filled($path))
            ->map(fn ($path) => asset($path))
            ->values()
            ->all();
    }

    public function establishedYear(): ?int
    {
        return $this->date_of_establishment?->year;
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

    public function publicDisplayName(): string
    {
        return $this->displayName();
    }

    public function formattedAddress(): string
    {
        return collect([$this->address, $this->city, $this->state, $this->pincode])
            ->filter(fn ($part) => filled($part))
            ->implode(', ');
    }

    public function logoUrl(): ?string
    {
        return filled($this->logo) ? asset($this->logo) : null;
    }

    public function publicUrl(): string
    {
        $this->loadMissing('user');

        return $this->user?->isSchool()
            ? route('schools.show', $this->slug)
            : route('institutes.show', $this->slug);
    }

    public function publicDiaryUrl(): string
    {
        $this->loadMissing('user');

        return $this->user?->isSchool()
            ? route('schools.diary', $this->slug)
            : route('institutes.diary', $this->slug);
    }

    public function publicSectionUrl(string $section): string
    {
        $this->loadMissing('user');
        $routePrefix = $this->user?->isSchool() ? 'schools' : 'institutes';

        return route($routePrefix.'.section', ['slug' => $this->slug, 'section' => $section]);
    }

    public function scopeForOwnerRole(Builder $query, string $role): Builder
    {
        return $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('role', $role));
    }

    public function scopeSchools(Builder $query): Builder
    {
        return $query->forOwnerRole('school');
    }

    public function scopeInstitutes(Builder $query): Builder
    {
        return $query->forOwnerRole('institute');
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
