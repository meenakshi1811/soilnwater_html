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
        'tuition_batches',
        'tuition_location',
        'tuition_point_address',
        'tuition_place_id',
        'tuition_latitude',
        'tuition_longitude',
        'tuition_timings',
        'tuition_charges',
        'tuition_delivery_options',
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
            'tuition_batches' => 'array',
            'tuition_delivery_options' => 'array',
            'take_tuitions' => 'boolean',
            'is_verified' => 'boolean',
            'is_available_now' => 'boolean',
            'converted_from_user' => 'boolean',
            'institute_latitude' => 'decimal:7',
            'institute_longitude' => 'decimal:7',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'tuition_latitude' => 'decimal:7',
            'tuition_longitude' => 'decimal:7',
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

    public function coursesQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = StudyMaterial::query()
            ->where('educator_id', $this->id)
            ->approved();

        if ((clone $query)->courses()->exists()) {
            return $query->courses();
        }

        return $query->where('material_type', '!=', 'notes');
    }

    public function notesQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return StudyMaterial::query()
            ->where('educator_id', $this->id)
            ->approved()
            ->notes();
    }

    public function questionPapersQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return StudyMaterial::query()
            ->where('educator_id', $this->id)
            ->approved()
            ->questionPapers();
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
        return $this->isTutor() ? 'Tutor' : 'Experienced Teacher';
    }

    public function verifiedBadgeLabel(): string
    {
        if (! $this->isVerified()) {
            return $this->roleLabel();
        }

        return $this->isTutor() ? 'Verified Tutor' : 'Verified Experienced Teacher';
    }

    public function publicProfileMetaDescription(): string
    {
        $fallback = $this->isTutor()
            ? 'Tutor profile on SoilnWater'
            : 'Experienced teacher profile on SoilnWater';

        return $this->publicTagline() ?: ($this->professional_headline ?: $fallback);
    }

    public function publicListingLabel(): string
    {
        return $this->isTutor() ? 'Tutors' : 'Teachers';
    }

    public function publicHeadlineFallback(): string
    {
        return $this->isTutor() ? 'Tutor' : 'Experienced Teacher';
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

    public function tuitionPointAddressLabel(): ?string
    {
        return filled($this->tuition_point_address)
            ? $this->tuition_point_address
            : (filled($this->tuition_location) ? $this->tuition_location : null);
    }

    public function hasTuitionPointMap(): bool
    {
        return filled($this->tuition_latitude) && filled($this->tuition_longitude);
    }

    /**
     * @return array<string, array{enabled: bool, label: string, charges: string, timings: string}>
     */
    public function normalizedTuitionDeliveryOptions(): array
    {
        $defaults = [
            'home' => [
                'enabled' => false,
                'label' => 'Home tuition',
                'charges' => '',
                'timings' => '',
            ],
            'personal' => [
                'enabled' => false,
                'label' => 'Personal tuition',
                'charges' => '',
                'timings' => '',
            ],
        ];

        $stored = is_array($this->tuition_delivery_options) ? $this->tuition_delivery_options : [];

        foreach ($defaults as $key => $default) {
            if (! isset($stored[$key]) || ! is_array($stored[$key])) {
                continue;
            }

            $defaults[$key]['enabled'] = (bool) ($stored[$key]['enabled'] ?? false);
            $defaults[$key]['charges'] = trim((string) ($stored[$key]['charges'] ?? ''));
            $defaults[$key]['timings'] = trim((string) ($stored[$key]['timings'] ?? ''));
        }

        return $defaults;
    }

    /**
     * @return list<array{key: string, label: string, charges: string, timings: string}>
     */
    public function activeTuitionDeliveryOptions(): array
    {
        return collect($this->normalizedTuitionDeliveryOptions())
            ->filter(fn (array $item, string $key) => $item['enabled'] && ($item['charges'] !== '' || $item['timings'] !== ''))
            ->map(fn (array $item, string $key) => [
                'key' => $key,
                'label' => $item['label'],
                'charges' => $item['charges'],
                'timings' => $item['timings'],
            ])
            ->values()
            ->all();
    }

    public function publicTagline(): ?string
    {
        $tagline = trim((string) $this->tagline);
        if ($tagline !== '') {
            return $tagline;
        }

        return static::excerptFromAbout($this->about);
    }

    /**
     * @return list<array{class: string, subject: string, batch_type: string, student_count: string, cost: string}>
     */
    public function normalizedTuitionBatches(): array
    {
        $stored = collect($this->tuition_batches ?? [])
            ->filter(fn ($item) => is_array($item))
            ->map(fn ($item) => $this->formatTuitionBatchRow($item))
            ->filter(fn ($item) => collect($item)->filter()->isNotEmpty())
            ->values();

        if ($stored->isNotEmpty()) {
            return $stored->all();
        }

        $classes = collect($this->tuition_classes ?? [])->values();
        $subjects = collect($this->tuition_subjects ?? [])->values();
        $types = collect($this->tuition_types ?? [])->values();
        $max = max($classes->count(), $subjects->count(), $types->count());

        if ($max === 0) {
            return [];
        }

        $legacy = [];
        for ($i = 0; $i < $max; $i++) {
            $legacy[] = $this->formatTuitionBatchRow([
                'class' => $classes->get($i),
                'subject' => $subjects->get($i),
                'batch_type' => $types->get($i),
            ]);
        }

        return $legacy;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{class: string, subject: string, batch_type: string, student_count: string, cost: string}
     */
    private function formatTuitionBatchRow(array $item): array
    {
        return [
            'class' => trim((string) ($item['class'] ?? '')),
            'subject' => trim((string) ($item['subject'] ?? '')),
            'batch_type' => trim((string) ($item['batch_type'] ?? '')),
            'student_count' => trim((string) ($item['student_count'] ?? '')),
            'cost' => trim((string) ($item['cost'] ?? '')),
        ];
    }

    public static function excerptFromAbout(?string $about, int $max = 255): ?string
    {
        $text = trim((string) $about);
        if ($text === '') {
            return null;
        }

        $firstLine = trim(explode("\n", str_replace(["\r\n", "\r"], "\n", $text), 2)[0]);
        if ($firstLine === '') {
            return null;
        }

        if (mb_strlen($firstLine) <= $max) {
            return $firstLine;
        }

        return rtrim(mb_substr($firstLine, 0, $max - 1)).'…';
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{title: string, organization: string, start_year: string, end_year: string, is_current: bool, description: string}
     */
    public static function formatExperienceForForm(array $item): array
    {
        $startYear = trim((string) ($item['start_year'] ?? ''));
        $endYear = trim((string) ($item['end_year'] ?? ''));
        $isCurrent = filter_var($item['is_current'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($startYear === '' && ! empty($item['duration'])) {
            [$parsedStart, $parsedEnd, $parsedCurrent] = static::parseExperienceDuration((string) $item['duration']);
            $startYear = $parsedStart;
            $endYear = $parsedEnd;
            $isCurrent = $parsedCurrent;
        }

        return [
            'title' => trim((string) ($item['title'] ?? '')),
            'organization' => trim((string) ($item['organization'] ?? '')),
            'start_year' => $startYear,
            'end_year' => $isCurrent ? '' : $endYear,
            'is_current' => $isCurrent,
            'description' => trim((string) ($item['description'] ?? '')),
        ];
    }

    /**
     * @return array{0: string, 1: string, 2: bool}
     */
    public static function parseExperienceDuration(string $duration): array
    {
        $duration = trim($duration);

        if (preg_match('/^(\d{4})\s*[–-]\s*Present$/i', $duration, $matches)) {
            return [$matches[1], '', true];
        }

        if (preg_match('/^(\d{4})\s*[–-]\s*(\d{4})$/', $duration, $matches)) {
            return [$matches[1], $matches[2], false];
        }

        return ['', '', false];
    }

    public static function formatExperienceDuration(string $startYear, string $endYear, bool $isCurrent): string
    {
        if ($startYear === '') {
            return '';
        }

        if ($isCurrent) {
            return $startYear.' – Present';
        }

        if ($endYear !== '') {
            return $startYear.' – '.$endYear;
        }

        return $startYear;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function experienceDurationLabel(array $item): string
    {
        $duration = trim((string) ($item['duration'] ?? ''));
        if ($duration !== '') {
            return $duration;
        }

        $startYear = trim((string) ($item['start_year'] ?? ''));
        $endYear = trim((string) ($item['end_year'] ?? ''));
        $isCurrent = filter_var($item['is_current'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return static::formatExperienceDuration($startYear, $endYear, $isCurrent);
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

    /**
     * @return array<int, array{key: string, label: string, url: string, icon: string, brand: string}>
     */
    public function socialLinks(): array
    {
        $definitions = [
            ['key' => 'facebook', 'label' => 'Facebook', 'field' => 'facebook_url', 'icon' => 'fa-facebook-f', 'brand' => 'facebook'],
            ['key' => 'instagram', 'label' => 'Instagram', 'field' => 'instagram_url', 'icon' => 'fa-instagram', 'brand' => 'instagram'],
            ['key' => 'youtube', 'label' => 'YouTube', 'field' => 'youtube_url', 'icon' => 'fa-youtube', 'brand' => 'youtube'],
            ['key' => 'linkedin', 'label' => 'LinkedIn', 'field' => 'linkedin_url', 'icon' => 'fa-linkedin-in', 'brand' => 'linkedin'],
            ['key' => 'whatsapp', 'label' => 'WhatsApp', 'field' => 'whatsapp_url', 'icon' => 'fa-whatsapp', 'brand' => 'whatsapp'],
        ];

        $links = [];

        foreach ($definitions as $definition) {
            $rawUrl = trim((string) ($this->{$definition['field']} ?? ''));

            if ($rawUrl === '') {
                continue;
            }

            $links[] = [
                'key' => $definition['key'],
                'label' => $definition['label'],
                'url' => static::normalizeSocialUrl($rawUrl),
                'icon' => $definition['icon'],
                'brand' => $definition['brand'],
            ];
        }

        return $links;
    }

    public function hasSocialLinks(): bool
    {
        return $this->socialLinks() !== [];
    }

    public static function normalizeSocialUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return 'https://'.$url;
        }

        return $url;
    }

    public function recalculateRating(): void
    {
        $profile = EducatorReview::query()
            ->where('educator_id', $this->id)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(rating), 0) as rating_sum')
            ->first();

        $materials = StudyMaterialReview::query()
            ->whereHas('studyMaterial', fn ($q) => $q->where('educator_id', $this->id))
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(rating), 0) as rating_sum')
            ->first();

        $total = (int) ($profile->total ?? 0) + (int) ($materials->total ?? 0);
        $sum = (float) ($profile->rating_sum ?? 0) + (float) ($materials->rating_sum ?? 0);

        $this->forceFill([
            'reviews_count' => $total,
            'average_rating' => $total > 0 ? round($sum / $total, 2) : 0,
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
