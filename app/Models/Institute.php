<?php

namespace App\Models;

use App\Models\Concerns\HasProfilePhoneNumbers;
use App\Support\InstituteGrades;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Institute extends Model
{
    use HasProfilePhoneNumbers;

    protected $fillable = [
        'user_id',
        'institution_name',
        'contact_person',
        'slug',
        'display_name',
        'logo',
        'phone',
        'phone_numbers',
        'whatsapp',
        'email',
        'emails',
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
        'facility_images',
        'pan_number',
        'gst_number',
        'government_certificate_number',
        'date_of_establishment',
        'tagline',
        'about',
        'description',
        'gallery',
        'profile_meta',
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
            'phone_numbers' => 'array',
            'emails' => 'array',
            'grades_offered' => 'array',
            'facilities' => 'array',
            'facility_images' => 'array',
            'gallery' => 'array',
            'profile_meta' => 'array',
            'is_verified' => 'boolean',
            'converted_from_user' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'date_of_establishment' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return list<array{class: string, sections: int|null, students_per_section: int|null}>
     */
    public function gradesOfferedEntries(): array
    {
        return InstituteGrades::normalizeList($this->grades_offered);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Reviews visitors wrote on the page */
    public function visitorReviews(): HasMany
    {
        return $this->hasMany(InstituteReview::class)->latest();
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

    public function jobs(): HasMany
    {
        return $this->hasMany(InstituteJob::class)->latest('published_at')->latest('id');
    }

    public function scholarships(): HasMany
    {
        return $this->hasMany(InstituteScholarship::class)->latest('published_at')->latest('id');
    }

    public function affiliatedEducators(): BelongsToMany
    {
        return $this->belongsToMany(Educator::class, 'educator_institute_affiliations')
            ->withPivot(['role_title', 'subject', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('display_name');
    }

    public function educatorAffiliations(): HasMany
    {
        return $this->hasMany(EducatorInstituteAffiliation::class)->orderBy('sort_order');
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

    /**
     * The school's contact emails, the main one first (a school that hasn't listed any: its email).
     *
     * @return list<string>
     */
    public function emailsList(): array
    {
        $stored = $this->emails ?? null;

        return \App\Support\ProfileEmails::normalize(is_array($stored) && $stored !== [] ? $stored : [$this->email]);
    }

    /** The stored photo path for a facility (by its name), if one was uploaded */
    public function facilityImagePath(string $facility): ?string
    {
        $path = ($this->facility_images ?? [])[$facility] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
    }

    public function facilityImageUrl(string $facility): ?string
    {
        $path = $this->facilityImagePath($facility);

        return $path ? asset($path) : null;
    }

    public function galleryUrls(): array
    {
        return \App\Support\InstituteGallery::entries($this->gallery)
            ->where('type', 'image')
            ->pluck('url')
            ->values()
            ->all();
    }

    /** @return list<array{type: string, path: string, url: string}> */
    public function galleryMediaItems(): array
    {
        return \App\Support\InstituteGallery::entries($this->gallery)->all();
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

    /** "School", "College" or "Institute", by the account's role */
    public function roleLabel(): string
    {
        $this->loadMissing('user');

        return match ($this->user?->role) {
            'school', 'college', 'institute' => \App\Support\SchoolInstituteHelper::label($this->user->role),
            default => 'School / Institute',
        };
    }

    /** The public listing it's in ("schools", "colleges" or "institutes"): its route prefix too */
    public function listingContext(): string
    {
        $this->loadMissing('user');

        return \App\Support\SchoolInstituteHelper::listingContext($this->user?->role);
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

        return route($this->listingContext().'.show', $this->slug);
    }

    public function publicDiaryUrl(): string
    {
        $this->loadMissing('user');

        return route($this->listingContext().'.diary', $this->slug);
    }

    /** The fee structure page (schools/{slug}/fee-structure) */
    public function publicFeeStructureUrl(): string
    {
        return route($this->listingContext().'.fee-structure', $this->slug);
    }

    public function publicSectionUrl(string $section): string
    {
        return route($this->listingContext().'.section', ['slug' => $this->slug, 'section' => $section]);
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

    public function scopeColleges(Builder $query): Builder
    {
        return $query->forOwnerRole('college');
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
