<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Educator extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'display_name',
        'slug',
        'profile_photo',
        'professional_headline',
        'tagline',
        'associated_institute',
        'institute_place_id',
        'institute_latitude',
        'institute_longitude',
        'city',
        'state',
        'pincode',
        'residential_address',
        'latitude',
        'longitude',
        'phone',
        'whatsapp',
        'email',
        'video_profile_url',
        'video_profile_path',
        'about',
        'teaching_method',
        'languages',
        'subjects',
        'classes',
        'boards',
        'qualifications',
        'experiences',
        'achievements',
        'certifications',
        'availability',
        'teaching_modes',
        'service_area',
        'teaching_stats',
        'take_tuitions',
        'tuition_classes',
        'tuition_subjects',
        'tuition_types',
        'tuition_location',
        'tuition_timings',
        'tuition_charges',
        'years_experience',
        'students_taught',
        'success_rate',
        'average_rating',
        'reviews_count',
        'is_verified',
        'is_available_now',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'linkedin_url',
        'whatsapp_url',
        'status',
        'converted_from_user',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'subjects' => 'array',
            'classes' => 'array',
            'boards' => 'array',
            'qualifications' => 'array',
            'experiences' => 'array',
            'achievements' => 'array',
            'certifications' => 'array',
            'availability' => 'array',
            'teaching_modes' => 'array',
            'service_area' => 'array',
            'teaching_stats' => 'array',
            'tuition_classes' => 'array',
            'tuition_subjects' => 'array',
            'tuition_types' => 'array',
            'take_tuitions' => 'boolean',
            'is_verified' => 'boolean',
            'is_available_now' => 'boolean',
            'converted_from_user' => 'boolean',
            'institute_latitude' => 'decimal:7',
            'institute_longitude' => 'decimal:7',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'success_rate' => 'decimal:2',
            'average_rating' => 'decimal:2',
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

    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class)->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(EducatorReview::class)->latest();
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(EducatorEnquiry::class)->latest();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'educator_followers')->withTimestamps();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isTeacher(): bool
    {
        return true;
    }

    public function isTutor(): bool
    {
        return (bool) $this->take_tuitions;
    }

    public function roleLabel(): string
    {
        return 'Teacher / Tutor';
    }

    public function verifiedBadgeLabel(): string
    {
        return $this->isVerified() ? 'Verified Teacher / Tutor' : $this->roleLabel();
    }

    public function isVerified(): bool
    {
        return (bool) $this->is_verified && $this->isApproved();
    }

    public function photoUrl(): ?string
    {
        return filled($this->profile_photo) ? asset($this->profile_photo) : $this->user?->authorImageUrl();
    }

    public function publicUrl(): string
    {
        return route('educator.show', $this->slug);
    }

    public function locationLabel(): string
    {
        return collect([$this->city, $this->state])->filter()->implode(', ');
    }

    public function primarySubject(): ?string
    {
        $subjects = collect($this->subjects ?? []);
        $primary = $subjects->first(fn ($item) => is_array($item) && ($item['level'] ?? '') === 'primary');

        if (is_array($primary)) {
            return $primary['name'] ?? null;
        }

        $first = $subjects->first();

        return is_array($first) ? ($first['name'] ?? null) : (is_string($first) ? $first : null);
    }

    public function recalculateRating(): void
    {
        $stats = $this->reviews()
            ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as avg_rating')
            ->first();

        $this->forceFill([
            'reviews_count' => (int) ($stats->total ?? 0),
            'average_rating' => round((float) ($stats->avg_rating ?? 0), 2),
        ])->save();
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'educator';
        $slug = $base;
        $i = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
