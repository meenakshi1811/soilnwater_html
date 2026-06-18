<?php

namespace App\Models;

use App\Support\CommunityContentTaxonomy;
use App\Support\CommunityPostFileUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CommunityPost extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_DECLINED = 'declined';

    public const STATUS_ARCHIVED = 'archived';

    /** @var list<string> */
    public const BOOK_CONTENT_TYPES = ['stories', 'biography', 'autobiography'];

    public const LOCATION_TYPE_GLOBAL = 'global';

    public const LOCATION_TYPE_INDIA = 'india';

    public const LOCATION_TYPE_STATE = 'state';

    public const LOCATION_TYPE_DISTRICT = 'district';

    public const LOCATION_TYPE_CITY = 'city';

    public const LOCATION_TYPE_TOWN = 'town';

    public const LOCATION_TYPE_VILLAGE = 'village';

    public const LOCATION_TYPE_GPS = 'gps';

    /** @var array<string, string> */
    public const LOCATION_TYPES = [
        self::LOCATION_TYPE_GLOBAL => 'Global',
        self::LOCATION_TYPE_INDIA => 'India',
        self::LOCATION_TYPE_STATE => 'State Specific',
        self::LOCATION_TYPE_DISTRICT => 'District Specific',
        self::LOCATION_TYPE_CITY => 'City Specific',
        self::LOCATION_TYPE_TOWN => 'Town Specific',
        self::LOCATION_TYPE_VILLAGE => 'Village Specific',
        self::LOCATION_TYPE_GPS => 'GPS Location',
    ];

    public const PUBLISH_AS_PUBLIC_PROFILE = 'public_profile';

    public const PUBLISH_AS_ANONYMOUS = 'anonymous';

    public const PUBLISH_AS_PEN_NAME = 'pen_name';

    /** @var array<string, string> */
    public const PUBLISH_AS_OPTIONS = [
        self::PUBLISH_AS_PUBLIC_PROFILE => 'Public Profile',
        self::PUBLISH_AS_ANONYMOUS => 'Anonymous',
        self::PUBLISH_AS_PEN_NAME => 'Pen Name',
    ];

    public const POLL_OPTION_YES = 'yes';

    public const POLL_OPTION_NO = 'no';

    public const POLL_OPTION_NOT_SURE = 'not_sure';

    public const POLL_OPTIONS = [
        self::POLL_OPTION_YES => 'Yes',
        self::POLL_OPTION_NO => 'No',
        self::POLL_OPTION_NOT_SURE => 'Not Sure',
    ];

    /** @var list<string> */
    public const WRITING_PURPOSE_OPTIONS = [
        'Share Knowledge',
        'Raise Awareness',
        'Personal Experience',
        'Help Community',
        'Promote Discussion',
        'Research Findings',
    ];

    protected $fillable = [
        'user_id',
        'submission_ip',
        'content_responsibility_accepted_at',
        'original_work_accepted_at',
        'content_type',
        'category',
        'writing_purpose',
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image_path',
        'featured_images',
        'tags',
        'location_type',
        'publish_as',
        'pen_name',
        'location',
        'location_lat',
        'location_lng',
        'video',
        'meta',
        'allow_comments',
        'allow_questions',
        'allow_suggestions',
        'allow_feedback',
        'allow_additional_evidence',
        'allow_sharing',
        'allow_poll',
        'poll_subject',
        'views_count',
        'shares_count',
        'quality_score',
        'article_score',
        'article_score_calculated_at',
        'is_featured',
        'is_sponsored',
        'is_highlighted',
        'badge_trending',
        'badge_editors_choice',
        'badge_most_read',
        'badge_most_shared',
        'badge_most_inspiring',
        'badge_community_favorite',
        'status',
        'published_at',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'content_responsibility_accepted_at' => 'datetime',
            'original_work_accepted_at' => 'datetime',
            'tags' => 'array',
            'featured_images' => 'array',
            'video' => 'array',
            'meta' => 'array',
            'location_lat' => 'decimal:7',
            'location_lng' => 'decimal:7',
            'allow_comments' => 'boolean',
            'allow_questions' => 'boolean',
            'allow_suggestions' => 'boolean',
            'allow_feedback' => 'boolean',
            'allow_additional_evidence' => 'boolean',
            'allow_sharing' => 'boolean',
            'allow_poll' => 'boolean',
            'quality_score' => 'decimal:2',
            'article_score' => 'decimal:2',
            'article_score_calculated_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_sponsored' => 'boolean',
            'is_highlighted' => 'boolean',
            'badge_trending' => 'boolean',
            'badge_editors_choice' => 'boolean',
            'badge_most_read' => 'boolean',
            'badge_most_shared' => 'boolean',
            'badge_most_inspiring' => 'boolean',
            'badge_community_favorite' => 'boolean',
            'badge_community_pick' => 'boolean',
            'published_at' => 'datetime',
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

    public function reactions(): HasMany
    {
        return $this->hasMany(CommunityPostReaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityPostComment::class);
    }

    public function discussionComments(): HasMany
    {
        return $this->comments()->whereNull('parent_id')->oldest();
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(CommunityPostPollVote::class);
    }

    public function saves(): HasMany
    {
        return $this->hasMany(CommunityPostSave::class);
    }

    public function reportSupports(): HasMany
    {
        return $this->hasMany(CommunityReportSupport::class);
    }

    public function reportAgreements(): HasMany
    {
        return $this->hasMany(CommunityReportAgreement::class);
    }

    public function reportFollows(): HasMany
    {
        return $this->hasMany(CommunityReportFollow::class);
    }

    public function reportEvidence(): HasMany
    {
        return $this->hasMany(CommunityReportEvidence::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(CommunityPostParticipation::class);
    }

    public function suggestions(): HasMany
    {
        return $this->participations()->where('type', CommunityPostParticipation::TYPE_SUGGESTION);
    }

    public function authorQuestions(): HasMany
    {
        return $this->hasMany(CommunityAuthorQuestion::class, 'community_post_id');
    }

    public function starRatings(): HasMany
    {
        return $this->hasMany(CommunityPostStarRating::class);
    }

    public function averageStarRating(): ?float
    {
        if (! self::supportsStarRating($this->content_type)) {
            return null;
        }

        if (isset($this->star_ratings_avg_rating)) {
            return $this->star_ratings_avg_rating !== null
                ? round((float) $this->star_ratings_avg_rating, 1)
                : null;
        }

        $average = $this->relationLoaded('starRatings')
            ? $this->starRatings->avg('rating')
            : $this->starRatings()->avg('rating');

        return $average !== null ? round((float) $average, 1) : null;
    }

    public function userStarRating(?int $userId): ?int
    {
        if ($userId === null) {
            return null;
        }

        $rating = $this->relationLoaded('starRatings')
            ? $this->starRatings->firstWhere('user_id', $userId)
            : $this->starRatings()->where('user_id', $userId)->first();

        return $rating?->rating;
    }

    /**
     * @return list<array{label: string, class: string}>
     */
    public function storyAchievementBadges(): array
    {
        return \App\Services\CommunityStoryAchievementService::badgesFor($this);
    }

    /**
     * @return array{comments: int, suggestions: int, questions: int, pending_questions: int}
     */
    public function engagementSummary(): array
    {
        return [
            'comments' => $this->comments()->count(),
            'suggestions' => $this->suggestions()->count(),
            'questions' => $this->authorQuestions()->count(),
            'pending_questions' => $this->authorQuestions()->whereNull('answered_at')->count(),
        ];
    }

    public function feedbackEntries(): HasMany
    {
        return $this->participations()->where('type', CommunityPostParticipation::TYPE_FEEDBACK);
    }

    public function allowsPublicParticipation(): bool
    {
        return $this->allow_comments
            || $this->allow_suggestions
            || $this->allow_feedback
            || $this->allow_additional_evidence;
    }

    public function allowsNewsDiscussion(): bool
    {
        if ($this->content_type !== 'news') {
            return false;
        }

        return $this->allow_comments
            || $this->allow_suggestions
            || $this->allow_questions;
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommunityPostReport::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(CommunityPostAuditLog::class)->latest('created_at');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at');
    }

    public function scopePubliclyListed(Builder $query): Builder
    {
        return $query->published();
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeVisibleOnAuthorProfile(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder
                ->where('publish_as', self::PUBLISH_AS_PUBLIC_PROFILE)
                ->orWhereNull('publish_as');
        });
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at !== null;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending approval',
            self::STATUS_DECLINED => 'Rejected',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
            default => 'Draft',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'bg-success',
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_DECLINED => 'bg-danger',
            self::STATUS_ARCHIVED => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * @return list<string>
     */
    public function adminPromotionLabels(): array
    {
        return collect([
            'is_featured' => 'Featured',
            'is_sponsored' => 'Sponsored',
            'is_highlighted' => 'Highlighted',
        ])
            ->filter(fn (string $label, string $field): bool => (bool) $this->{$field})
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, class: string}>
     */
    public function articleScoreBadges(): array
    {
        $badges = [];

        if ($this->badge_trending) {
            $badges[] = ['label' => 'Trending', 'class' => 'community-score-badge--trending'];
        }

        if ($this->badge_editors_choice) {
            $badges[] = ['label' => "Editor's Choice", 'class' => 'community-score-badge--editors-choice'];
        }

        if ($this->badge_most_read) {
            $badges[] = ['label' => 'Most Read', 'class' => 'community-score-badge--most-read'];
        }

        if ($this->is_featured) {
            $badges[] = ['label' => 'Featured', 'class' => 'community-score-badge--featured'];
        }

        if ($this->badge_community_pick) {
            $badges[] = ['label' => 'Community Pick', 'class' => 'community-score-badge--community-pick'];
        }

        return $badges;
    }

    public function articleScoreBadgeLabels(): array
    {
        return collect($this->articleScoreBadges())->pluck('label')->all();
    }

    public function typeLabel(): string
    {
        return CommunityContentTaxonomy::labels()[$this->content_type] ?? Str::headline($this->content_type);
    }

    public function writingPurposeLabel(): ?string
    {
        return filled($this->writing_purpose) ? (string) $this->writing_purpose : null;
    }

    /**
     * @return array<string, string>
     */
    public static function locationTypeOptions(?string $contentType = null): array
    {
        if ($contentType === 'news') {
            return self::newsGeographicCoverageOptions();
        }

        $options = collect(self::LOCATION_TYPES)
            ->except(self::LOCATION_TYPE_GPS)
            ->all();

        if ($contentType === 'reports') {
            $options[self::LOCATION_TYPE_GPS] = 'GPS Location';
        }

        return $options;
    }

    /**
     * Geographic coverage choices shown on news posts.
     *
     * @return array<string, string>
     */
    public static function newsGeographicCoverageOptions(): array
    {
        return [
            self::LOCATION_TYPE_VILLAGE => 'Village',
            self::LOCATION_TYPE_TOWN => 'Town',
            self::LOCATION_TYPE_CITY => 'City',
            self::LOCATION_TYPE_DISTRICT => 'District',
            self::LOCATION_TYPE_STATE => 'State',
            self::LOCATION_TYPE_INDIA => 'National',
            self::LOCATION_TYPE_GLOBAL => 'International',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function newsGeographicCoverageGroups(): array
    {
        return [
            'Local area' => [
                self::LOCATION_TYPE_VILLAGE,
                self::LOCATION_TYPE_TOWN,
                self::LOCATION_TYPE_CITY,
            ],
            'Regional' => [
                self::LOCATION_TYPE_DISTRICT,
                self::LOCATION_TYPE_STATE,
            ],
            'Broader reach' => [
                self::LOCATION_TYPE_INDIA,
                self::LOCATION_TYPE_GLOBAL,
            ],
        ];
    }

    public function geographicCoverageLabel(): string
    {
        if ($this->content_type === 'news') {
            return self::newsGeographicCoverageOptions()[$this->location_type]
                ?? $this->locationTypeLabel();
        }

        return $this->locationTypeLabel();
    }

    public static function usesStructuredLocation(?string $contentType): bool
    {
        return in_array($contentType, ['news', 'reports'], true);
    }

    public static function supportsStarRating(?string $contentType): bool
    {
        return in_array($contentType, ['stories', 'poetry', 'autobiography'], true);
    }

    public static function usesPoetryRegionalLocation(?string $contentType): bool
    {
        return $contentType === 'poetry';
    }

    /**
     * @return list<string>
     */
    public static function structuredLocationMetaKeys(): array
    {
        return [
            'location_country',
            'location_state',
            'location_district',
            'location_city',
            'location_locality',
        ];
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public static function composeStructuredLocation(array $fields): string
    {
        return collect([
            $fields['location_locality'] ?? null,
            $fields['location_city'] ?? null,
            $fields['location_district'] ?? null,
            $fields['location_state'] ?? null,
            $fields['location_country'] ?? null,
        ])
            ->filter(fn (mixed $value): bool => filled($value))
            ->map(fn (mixed $value): string => trim((string) $value))
            ->unique()
            ->implode(', ');
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public static function inferLocationTypeFromStructured(array $fields): string
    {
        if (filled($fields['location_locality'] ?? null)) {
            return self::LOCATION_TYPE_VILLAGE;
        }

        if (filled($fields['location_city'] ?? null)) {
            return self::LOCATION_TYPE_CITY;
        }

        if (filled($fields['location_district'] ?? null)) {
            return self::LOCATION_TYPE_DISTRICT;
        }

        if (filled($fields['location_state'] ?? null)) {
            return self::LOCATION_TYPE_STATE;
        }

        if (filled($fields['location_country'] ?? null)) {
            $country = strtolower(trim((string) $fields['location_country']));

            return in_array($country, ['india', 'bharat'], true)
                ? self::LOCATION_TYPE_INDIA
                : self::LOCATION_TYPE_GLOBAL;
        }

        return self::LOCATION_TYPE_CITY;
    }

    /**
     * @return array<string, string>
     */
    public static function structuredLocationLabels(): array
    {
        return [
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City',
            'location_locality' => 'Locality',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public function structuredLocationForDisplay(): \Illuminate\Support\Collection
    {
        return collect(self::structuredLocationLabels())
            ->mapWithKeys(fn (string $label, string $key): array => [$key => data_get($this->meta, $key)])
            ->filter(fn (mixed $value): bool => filled($value));
    }

    public function usesGpsLocation(): bool
    {
        return $this->location_type === self::LOCATION_TYPE_GPS;
    }

    public function hasMapCoordinates(): bool
    {
        return filled($this->location_lat) && filled($this->location_lng);
    }

    public function locationTypeLabel(): string
    {
        return self::LOCATION_TYPES[$this->location_type] ?? Str::headline((string) $this->location_type);
    }

    public function requiresSpecificLocation(): bool
    {
        return in_array($this->location_type, self::locationTypesRequiringPlace(), true);
    }

    /**
     * @return list<string>
     */
    public static function locationTypesRequiringPlace(): array
    {
        return [
            self::LOCATION_TYPE_STATE,
            self::LOCATION_TYPE_DISTRICT,
            self::LOCATION_TYPE_CITY,
            self::LOCATION_TYPE_TOWN,
            self::LOCATION_TYPE_VILLAGE,
        ];
    }

    /**
     * @return array{location: string, location_lat: float, location_lng: float}
     */
    public static function defaultLocationForType(string $locationType): array
    {
        return match ($locationType) {
            self::LOCATION_TYPE_GLOBAL => [
                'location' => 'Global',
                'location_lat' => 0.0,
                'location_lng' => 0.0,
            ],
            self::LOCATION_TYPE_INDIA => [
                'location' => 'India',
                'location_lat' => 20.5937,
                'location_lng' => 78.9629,
            ],
            self::LOCATION_TYPE_GPS => [
                'location' => 'GPS Location',
                'location_lat' => null,
                'location_lng' => null,
            ],
            default => [
                'location' => '',
                'location_lat' => 0.0,
                'location_lng' => 0.0,
            ],
        };
    }

    public function resolvedPublishAs(): string
    {
        return $this->publish_as ?: self::PUBLISH_AS_PUBLIC_PROFILE;
    }

    public function publishAsLabel(): string
    {
        return self::PUBLISH_AS_OPTIONS[$this->resolvedPublishAs()] ?? Str::headline($this->resolvedPublishAs());
    }

    public function authorDisplayName(): string
    {
        return match ($this->resolvedPublishAs()) {
            self::PUBLISH_AS_ANONYMOUS => 'Anonymous',
            self::PUBLISH_AS_PEN_NAME => filled($this->pen_name) ? (string) $this->pen_name : 'Pen Name',
            default => $this->user?->name ?? $this->user?->full_name ?? 'Community author',
        };
    }

    public function authorInitials(): string
    {
        return collect(preg_split('/\s+/', trim($this->authorDisplayName())) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'CA';
    }

    public function authorAvatarUrl(): ?string
    {
        if (! $this->showsAuthorProfileLink() || ! $this->user) {
            return null;
        }

        return $this->user->authorImageUrl();
    }

    public function showsAuthorProfileLink(): bool
    {
        return $this->resolvedPublishAs() === self::PUBLISH_AS_PUBLIC_PROFILE
            && $this->user !== null;
    }

    public function allowsSharing(): bool
    {
        return (bool) ($this->allow_sharing ?? true);
    }

    public function shareUrl(): string
    {
        return $this->publicUrl();
    }

    public function allowsPoll(): bool
    {
        return (bool) ($this->allow_poll ?? false) && filled($this->poll_subject);
    }

    public function pollQuestion(): ?string
    {
        if (! $this->allowsPoll()) {
            return null;
        }

        return 'Do you support '.$this->poll_subject.'?';
    }

    /**
     * @return array{yes: int, no: int, not_sure: int, total: int}
     */
    public function pollCounts(): array
    {
        $counts = $this->relationLoaded('pollVotes')
            ? $this->pollVotes->groupBy('option')->map->count()
            : $this->pollVotes()
                ->selectRaw('option, count(*) as total')
                ->groupBy('option')
                ->pluck('total', 'option');

        $yes = (int) ($counts[self::POLL_OPTION_YES] ?? 0);
        $no = (int) ($counts[self::POLL_OPTION_NO] ?? 0);
        $notSure = (int) ($counts[self::POLL_OPTION_NOT_SURE] ?? 0);

        return [
            'yes' => $yes,
            'no' => $no,
            'not_sure' => $notSure,
            'total' => $yes + $no + $notSure,
        ];
    }

    public function userPollVote(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        if ($this->relationLoaded('pollVotes')) {
            return $this->pollVotes->firstWhere('user_id', $user->id)?->option;
        }

        return $this->pollVotes()->where('user_id', $user->id)->value('option');
    }

    public static function isBookContentType(?string $contentType): bool
    {
        return in_array($contentType, self::BOOK_CONTENT_TYPES, true);
    }

    public static function usesChapterLayout(?string $contentType = null): bool
    {
        return $contentType === 'autobiography';
    }

    public function usesBookLayout(): bool
    {
        return self::isBookContentType($this->content_type);
    }

    public function usesChapterLayoutForDisplay(): bool
    {
        return self::usesChapterLayout($this->content_type);
    }

    /**
     * @return list<array{content: string, language: string, title?: string, summary?: string}>
     */
    public function bookPages(): array
    {
        $pages = data_get($this->meta, 'book_pages');
        $usesChapters = $this->usesChapterLayoutForDisplay();

        if (is_array($pages) && $pages !== []) {
            return collect($pages)
                ->map(function (mixed $page) use ($usesChapters): array {
                    $normalized = [
                        'content' => is_array($page) ? (string) ($page['content'] ?? '') : (string) $page,
                        'language' => in_array(is_array($page) ? ($page['language'] ?? 'en') : 'en', ['en', 'hi'], true)
                            ? (is_array($page) ? ($page['language'] ?? 'en') : 'en')
                            : 'en',
                    ];

                    if ($usesChapters) {
                        $normalized['title'] = is_array($page) ? trim((string) ($page['title'] ?? '')) : '';
                        $normalized['summary'] = is_array($page) ? trim((string) ($page['summary'] ?? '')) : '';
                    }

                    return $normalized;
                })
                ->filter(function (array $page) use ($usesChapters): bool {
                    if (filled(strip_tags($page['content']))) {
                        return true;
                    }

                    return $usesChapters && filled($page['title'] ?? null);
                })
                ->values()
                ->all();
        }

        if ($this->usesBookLayout() && filled($this->body)) {
            return [['content' => (string) $this->body, 'language' => 'en']];
        }

        return [];
    }

    /**
     * @param  list<array{content: string}>|list<string>  $pages
     */
    public static function bodyFromBookPages(array $pages): string
    {
        return collect($pages)
            ->map(fn (mixed $page): string => is_array($page) ? (string) ($page['content'] ?? '') : (string) $page)
            ->filter(fn (string $content): bool => filled(strip_tags($content)))
            ->map(fn (string $content): string => '<div class="community-book-page-source">'.$content.'</div>')
            ->implode('');
    }

    public function publicUrl(): string
    {
        return route('community.show', $this);
    }

    /**
     * @return list<string>
     */
    public function featuredImages(): array
    {
        if (is_array($this->featured_images) && $this->featured_images !== []) {
            return array_values(array_filter($this->featured_images));
        }

        if (filled($this->featured_image_path)) {
            return [$this->featured_image_path];
        }

        return [];
    }

    /**
     * @return list<string>
     */
    public function featuredImageUrls(): array
    {
        return collect($this->featuredImages())
            ->map(fn (string $path) => self::resolveImageUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    public function featuredImageUrl(): ?string
    {
        $paths = $this->featuredImages();

        return isset($paths[0]) ? self::resolveImageUrl($paths[0]) : null;
    }

    public static function resolveImageUrl(?string $path): ?string
    {
        return CommunityPostFileUploader::url($path);
    }

    /**
     * @return array{type: string, url?: string, video_id?: string, path?: string, name?: string}|null
     */
    public function videoData(): ?array
    {
        return is_array($this->video) && filled($this->video['type'] ?? null)
            ? $this->video
            : null;
    }

    public function hasVideo(): bool
    {
        return $this->videoData() !== null;
    }

    public function youtubeEmbedUrl(): ?string
    {
        $video = $this->videoData();

        if (($video['type'] ?? null) !== 'youtube') {
            return null;
        }

        $videoId = $video['video_id'] ?? self::parseYoutubeVideoId($video['url'] ?? null);

        return $videoId ? 'https://www.youtube.com/embed/'.$videoId : null;
    }

    public function videoFileUrl(): ?string
    {
        $video = $this->videoData();

        if (($video['type'] ?? null) !== 'upload') {
            return null;
        }

        return self::resolveImageUrl($video['path'] ?? null);
    }

    /**
     * @return array{type: string, path?: string, name?: string, url?: string}|null
     */
    public function storyAudioData(): ?array
    {
        $audio = data_get($this->meta, 'story_audio');

        return is_array($audio) && filled($audio['url'] ?? null)
            ? $audio
            : null;
    }

    public function storyAudioUrl(): ?string
    {
        return $this->storyAudioData()['url'] ?? null;
    }

    /**
     * @return array{type: string, path?: string, name?: string, url?: string}|null
     */
    public function poetryAudioData(): ?array
    {
        $audio = data_get($this->meta, 'poetry_audio');

        return is_array($audio) && filled($audio['url'] ?? null)
            ? $audio
            : null;
    }

    public function poetryAudioUrl(): ?string
    {
        return $this->poetryAudioData()['url'] ?? null;
    }

    /**
     * @return array{type: string, path?: string, name?: string, url?: string}|null
     */
    public function autobiographyAudioData(): ?array
    {
        $audio = data_get($this->meta, 'autobiography_audio');

        return is_array($audio) && filled($audio['url'] ?? null)
            ? $audio
            : null;
    }

    public function autobiographyAudioUrl(): ?string
    {
        return $this->autobiographyAudioData()['url'] ?? null;
    }

    public function authorBioForDisplay(): ?string
    {
        $bio = data_get($this->meta, 'author_bio');

        return filled($bio) ? (string) $bio : null;
    }

    public static function parseYoutubeVideoId(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $patterns = [
            '/^https?:\/\/(?:www\.)?youtube\.com\/watch\?(?:.*&)?v=([\w-]{11})(?:&.*)?$/i',
            '/^https?:\/\/(?:www\.)?youtu\.be\/([\w-]{11})(?:\?.*)?$/i',
            '/^https?:\/\/(?:www\.)?youtube\.com\/embed\/([\w-]{11})(?:\?.*)?$/i',
            '/^https?:\/\/(?:www\.)?youtube\.com\/shorts\/([\w-]{11})(?:\?.*)?$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($url), $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function seoTitle(): string
    {
        return $this->title.' | '.$this->typeLabel().' | SoilnWater Community';
    }

    public function seoDescription(): string
    {
        if (filled($this->excerpt)) {
            return Str::limit(trim(strip_tags($this->excerpt)), 160, '...');
        }

        $bodyText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $this->body)) ?? '');

        return Str::limit($bodyText, 160, '...');
    }

    public function reportStatus(): ?string
    {
        $status = data_get($this->meta, 'report_status');

        return filled($status) ? (string) $status : null;
    }

    public function reportStatusBadgeClass(): string
    {
        return match ($this->reportStatus()) {
            'Information Only' => 'bg-secondary',
            'Seeking Support' => 'bg-info text-dark',
            'Awareness Campaign' => 'bg-primary',
            'Request for Action' => 'bg-warning text-dark',
            'Success Story', 'Issue Resolved' => 'bg-success',
            default => 'bg-light text-dark',
        };
    }

    public function listingCategoryLabel(): string
    {
        if ($this->content_type === 'reports') {
            $parts = array_filter([
                filled(data_get($this->meta, 'report_type'))
                    ? (string) data_get($this->meta, 'report_type')
                    : $this->category,
                $this->reportStatus(),
            ]);

            return $parts !== [] ? implode(' · ', $parts) : (string) $this->category;
        }

        return (string) $this->category;
    }

    public function isReportContent(): bool
    {
        return $this->content_type === 'reports';
    }

    public function reportTrustScore(): int
    {
        if (! $this->isReportContent()) {
            return 0;
        }

        return \App\Services\CommunityReportTrustScoreService::score($this);
    }

    /**
     * @return array<string, array{label: string, points: float, max: int, met: bool, detail: string}>
     */
    public function reportTrustBreakdown(): array
    {
        if (! $this->isReportContent()) {
            return [];
        }

        return \App\Services\CommunityReportTrustScoreService::breakdown($this);
    }

    public function reportTrustBadgeClass(): string
    {
        return match (true) {
            $this->reportTrustScore() >= 80 => 'report-trust-score--high',
            $this->reportTrustScore() >= 50 => 'report-trust-score--medium',
            default => 'report-trust-score--low',
        };
    }

    public function seoImageUrl(): string
    {
        return $this->featuredImageUrl() ?? asset('assets/images/logo_soilnwater.webp');
    }

    public function seoKeywords(): string
    {
        return collect([
            $this->typeLabel(),
            $this->locationTypeLabel(),
            $this->category,
            data_get($this->meta, 'report_type'),
            $this->reportStatus(),
            $this->location,
            ...($this->tags ?? []),
        ])
            ->filter(fn (mixed $value): bool => filled($value) && ! is_array($value))
            ->map(fn (mixed $value): string => trim((string) $value))
            ->unique()
            ->implode(', ');
    }

    public function shouldBlockSearchIndexing(): bool
    {
        return $this->status !== self::STATUS_PUBLISHED;
    }

    /**
     * @return array<string, mixed>
     */
    public function structuredData(): array
    {
        $authorName = $this->authorDisplayName();

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->title,
            'description' => $this->seoDescription(),
            'image' => [$this->seoImageUrl()],
            'url' => $this->publicUrl(),
            'mainEntityOfPage' => $this->publicUrl(),
            'articleSection' => $this->typeLabel(),
            'keywords' => $this->seoKeywords(),
            'author' => array_filter([
                '@type' => 'Person',
                'name' => $authorName,
                'url' => $this->showsAuthorProfileLink()
                    ? route('community.authors.show', $this->user->authorUniqueName())
                    : null,
            ]),
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'SoilnWater',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('assets/images/logo_soilnwater.webp'),
                ],
            ],
        ];

        if ($this->published_at) {
            $data['datePublished'] = $this->published_at->toIso8601String();
            $data['dateModified'] = ($this->updated_at ?? $this->published_at)->toIso8601String();
        }

        if (filled($this->location)) {
            $data['contentLocation'] = [
                '@type' => 'Place',
                'name' => $this->location,
            ];
        }

        return array_filter($data, fn (mixed $value): bool => filled($value));
    }
}
