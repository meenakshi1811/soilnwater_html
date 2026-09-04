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

    /** @var list<string> */
    public const LIFE_STORY_CONTENT_TYPES = ['biography', 'autobiography'];

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

    public const PUBLISH_AS_FIRST_NAME_ONLY = 'first_name_only';

    /** @var array<string, string> */
    public const PUBLISH_AS_OPTIONS = [
        self::PUBLISH_AS_PUBLIC_PROFILE => 'Public Profile',
        self::PUBLISH_AS_ANONYMOUS => 'Anonymous',
        self::PUBLISH_AS_PEN_NAME => 'Pen Name',
        self::PUBLISH_AS_FIRST_NAME_ONLY => 'First Name Only',
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

    public function awarenessSupports(): HasMany
    {
        return $this->hasMany(CommunityAwarenessSupport::class);
    }

    public function awarenessPledges(): HasMany
    {
        return $this->hasMany(CommunityAwarenessPledge::class);
    }

    public function awarenessVolunteers(): HasMany
    {
        return $this->hasMany(CommunityAwarenessVolunteer::class);
    }

    public function environmentSupports(): HasMany
    {
        return $this->hasMany(CommunityEnvironmentSupport::class);
    }

    public function environmentFollows(): HasMany
    {
        return $this->hasMany(CommunityEnvironmentFollow::class);
    }

    public function environmentVolunteers(): HasMany
    {
        return $this->hasMany(CommunityEnvironmentVolunteer::class);
    }

    public function businessQueries(): HasMany
    {
        return $this->hasMany(CommunityBusinessQuery::class);
    }

    public function astroConsultancyPrivateQueries(): HasMany
    {
        return $this->hasMany(CommunityAstroConsultancyPrivateQuery::class);
    }

    public function reportAgreements(): HasMany
    {
        return $this->hasMany(CommunityReportAgreement::class);
    }

    public function reportFollows(): HasMany
    {
        return $this->hasMany(CommunityReportFollow::class);
    }

    public function localVoiceSupports(): HasMany
    {
        return $this->hasMany(CommunityLocalVoiceSupport::class);
    }

    public function localVoiceFollows(): HasMany
    {
        return $this->hasMany(CommunityLocalVoiceFollow::class);
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

    public function scopeVisibleInCommunityListing(Builder $query, ?User $viewer = null): Builder
    {
        return $query->where(function (Builder $builder) use ($viewer): void {
            $builder
                ->where(function (Builder $nonChildrensCorner): void {
                    $nonChildrensCorner
                        ->where('content_type', '!=', 'childrens-corner')
                        ->where('content_type', '!=', 'womens-world')
                        ->where('content_type', '!=', 'senior-citizens-forum')
                        ->where('content_type', '!=', 'student-corner');
                })
                ->orWhere(function (Builder $childrensCorner) use ($viewer): void {
                    $childrensCorner
                        ->where('content_type', 'childrens-corner')
                        ->where(function (Builder $publicPrivacy): void {
                            $publicPrivacy
                                ->whereNull('meta->childrens_corner_privacy_setting')
                                ->orWhereIn('meta->childrens_corner_privacy_setting', ['public', 'public_limited']);
                        });

                    if ($viewer !== null) {
                        $childrensCorner->orWhereIn('meta->childrens_corner_privacy_setting', ['registered_users', 'school_community']);
                    }
                })
                ->orWhere(function (Builder $womensWorld) use ($viewer): void {
                    $womensWorld
                        ->where('content_type', 'womens-world')
                        ->where(function (Builder $publicVisibility): void {
                            $publicVisibility
                                ->whereNull('meta->womens_world_visibility')
                                ->orWhere('meta->womens_world_visibility', 'public');
                        });

                    if ($viewer !== null) {
                        $womensWorld->orWhereIn('meta->womens_world_visibility', ['registered_users', 'women_community_only']);
                    }
                })
                ->orWhere(function (Builder $seniorCitizensForum) use ($viewer): void {
                    $seniorCitizensForum
                        ->where('content_type', 'senior-citizens-forum')
                        ->where(function (Builder $publicVisibility): void {
                            $publicVisibility
                                ->whereNull('meta->senior_citizens_forum_visibility')
                                ->orWhere('meta->senior_citizens_forum_visibility', 'public');
                        });

                    if ($viewer !== null) {
                        $seniorCitizensForum->orWhereIn('meta->senior_citizens_forum_visibility', ['registered_users', 'senior_citizens_community']);
                    }
                })
                ->orWhere(function (Builder $studentCorner) use ($viewer): void {
                    $studentCorner
                        ->where('content_type', 'student-corner')
                        ->where(function (Builder $publicVisibility): void {
                            $publicVisibility
                                ->whereNull('meta->student_corner_visibility')
                                ->orWhere('meta->student_corner_visibility', 'public');
                        });

                    if ($viewer !== null) {
                        $studentCorner->orWhereIn('meta->student_corner_visibility', ['registered_users', 'students_only']);
                    }
                })
                ->orWhere(function (Builder $youthCorner) use ($viewer): void {
                    $youthCorner
                        ->where('content_type', 'youth-corner')
                        ->where(function (Builder $publicVisibility): void {
                            $publicVisibility
                                ->whereNull('meta->youth_corner_visibility')
                                ->orWhere('meta->youth_corner_visibility', 'public');
                        });

                    if ($viewer !== null) {
                        $youthCorner->orWhereIn('meta->youth_corner_visibility', ['registered_users', 'youth_community']);
                    }
                })
                ->orWhere(function (Builder $localVoices) use ($viewer): void {
                    $localVoices
                        ->where('content_type', 'local-voices')
                        ->where(function (Builder $publicVisibility): void {
                            $publicVisibility
                                ->whereNull('meta->local_voice_visibility')
                                ->orWhere('meta->local_voice_visibility', 'public');
                        });

                    if ($viewer !== null) {
                        $localVoices->orWhereIn('meta->local_voice_visibility', ['registered_users', 'local_community']);
                    }
                })
                ->orWhere(function (Builder $myArea) use ($viewer): void {
                    $myArea
                        ->where('content_type', 'my-area')
                        ->where(function (Builder $publicVisibility): void {
                            $publicVisibility
                                ->whereNull('meta->my_area_visibility')
                                ->orWhere('meta->my_area_visibility', 'public');
                        });

                    if ($viewer !== null) {
                        $myArea->orWhereIn('meta->my_area_visibility', ['registered_users', 'local_community']);
                    }
                })
                ->orWhere(function (Builder $communityIssues) use ($viewer): void {
                    $communityIssues
                        ->where('content_type', 'community-issues')
                        ->where(function (Builder $publicVisibility): void {
                            $publicVisibility
                                ->whereNull('meta->community_issue_visibility')
                                ->orWhere('meta->community_issue_visibility', 'public');
                        });

                    if ($viewer !== null) {
                        $communityIssues->orWhereIn('meta->community_issue_visibility', ['registered_users', 'local_community']);
                    }
                });
        });
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
        return in_array($contentType, ['news', 'reports', 'awareness', 'business', 'local-voices', 'my-area', 'community-issues', 'agriculture', 'environment', 'science-technology'], true);
    }

    public static function usesWomensWorldOptionalStructuredLocation(?string $contentType): bool
    {
        return $contentType === 'womens-world';
    }

    public static function usesStudentCornerOptionalStructuredLocation(?string $contentType): bool
    {
        return $contentType === 'student-corner';
    }

    public static function usesYouthCornerOptionalStructuredLocation(?string $contentType): bool
    {
        return $contentType === 'youth-corner';
    }

    public static function mountsStructuredLocationFields(?string $contentType): bool
    {
        return self::usesStructuredLocation($contentType)
            || self::usesWomensWorldOptionalStructuredLocation($contentType)
            || self::usesStudentCornerOptionalStructuredLocation($contentType)
            || self::usesYouthCornerOptionalStructuredLocation($contentType)
            || self::usesSeniorCitizensForumFlow($contentType);
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
        return self::structuredLocationLabelsFor(null);
    }

    /**
     * @return array<string, string>
     */
    public static function structuredLocationLabelsFor(?string $contentType): array
    {
        if ($contentType === 'womens-world') {
            return [
                'location_country' => 'Country',
                'location_state' => 'State',
                'location_district' => 'District',
                'location_city' => 'City',
            ];
        }

        if ($contentType === 'senior-citizens-forum') {
            return [
                'location_country' => 'Country',
                'location_state' => 'State',
                'location_district' => 'District',
                'location_city' => 'City/Village',
            ];
        }

        if ($contentType === 'local-voices') {
            return [
                'location_country' => 'Country',
                'location_state' => 'State',
                'location_district' => 'District',
                'location_city' => 'City/Town/Village',
                'location_locality' => 'Locality / Area',
            ];
        }

        if ($contentType === 'my-area') {
            return [
                'location_country' => 'Country',
                'location_state' => 'State',
                'location_district' => 'District',
                'location_city' => 'City/Town/Village',
                'location_locality' => 'Locality / Area',
            ];
        }

        if ($contentType === 'community-issues') {
            return [
                'location_country' => 'Country',
                'location_state' => 'State',
                'location_district' => 'District',
                'location_city' => 'City/Town/Village',
                'location_locality' => 'Locality / Area',
                'location_landmark' => 'Landmark',
            ];
        }

        if ($contentType === 'agriculture') {
            return [
                'location_country' => 'Country',
                'location_state' => 'State',
                'location_district' => 'District',
                'location_city' => 'City/Town/Village',
            ];
        }

        return [
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City',
            'location_locality' => $contentType === 'awareness' ? 'Area' : 'Locality',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public function structuredLocationForDisplay(): \Illuminate\Support\Collection
    {
        return collect(self::structuredLocationLabelsFor($this->content_type))
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
            self::PUBLISH_AS_FIRST_NAME_ONLY => collect(preg_split('/\s+/', trim((string) ($this->user?->name ?: $this->user?->full_name ?: ''))) ?: [])->filter()->first() ?: 'Student',
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
        if (! ($this->allow_poll ?? false)) {
            return false;
        }

        if ($this->isAwarenessPost()) {
            return filled(data_get($this->meta, 'awareness_poll_question'));
        }

        if ($this->isBusinessPost()) {
            return filled(data_get($this->meta, 'business_poll_question'))
                && $this->businessPollOptionsForDisplay() !== [];
        }

        if ($this->isWomensWorldPost()) {
            return filled(data_get($this->meta, 'womens_world_poll_question'))
                && $this->womensWorldPollOptionsForDisplay() !== [];
        }

        if ($this->isStudentCornerPost()) {
            return filled(data_get($this->meta, 'student_corner_poll_question'))
                && $this->studentCornerPollOptionsForDisplay() !== [];
        }

        if ($this->isYouthCornerPost()) {
            return filled(data_get($this->meta, 'youth_corner_poll_question'))
                && $this->youthCornerPollOptionsForDisplay() !== [];
        }

        if ($this->isLocalVoicesPost()) {
            return filled(data_get($this->meta, 'local_voice_poll_question'))
                && $this->localVoicePollOptionsForDisplay() !== [];
        }

        if ($this->isMyAreaPost()) {
            return filled(data_get($this->meta, 'my_area_poll_question'))
                && $this->myAreaPollOptionsForDisplay() !== [];
        }

        if ($this->isCommunityIssuesPost()) {
            return filled(data_get($this->meta, 'community_issue_poll_question'))
                && $this->communityIssuePollOptionsForDisplay() !== [];
        }

        if ($this->isAgriculturePost()) {
            return filled(data_get($this->meta, 'agriculture_poll_question'))
                && $this->agriculturePollOptionsForDisplay() !== [];
        }

        if ($this->isEnvironmentPost()) {
            return filled(data_get($this->meta, 'environment_poll_question'))
                && $this->environmentPollOptionsForDisplay() !== [];
        }

        if ($this->isAstroConsultancyPost()) {
            return filled(data_get($this->meta, 'astro_consultancy_poll_question'))
                && $this->astroConsultancyPollOptionsForDisplay() !== [];
        }

        if ($this->isReligionSpiritualityPost()) {
            return filled(data_get($this->meta, 'religion_spirituality_poll_question'))
                && $this->religionSpiritualityPollOptionsForDisplay() !== [];
        }

        if ($this->isCreativeCornerPost()) {
            return filled(data_get($this->meta, 'creative_corner_poll_question'))
                && $this->creativeCornerPollOptionsForDisplay() !== [];
        }

        return filled($this->poll_subject);
    }

    public function pollQuestion(): ?string
    {
        if (! $this->allowsPoll()) {
            return null;
        }

        if ($this->isAwarenessPost()) {
            return (string) data_get($this->meta, 'awareness_poll_question');
        }

        if ($this->isBusinessPost()) {
            return (string) data_get($this->meta, 'business_poll_question');
        }

        if ($this->isWomensWorldPost()) {
            return (string) data_get($this->meta, 'womens_world_poll_question');
        }

        if ($this->isStudentCornerPost()) {
            return (string) data_get($this->meta, 'student_corner_poll_question');
        }

        if ($this->isYouthCornerPost()) {
            return (string) data_get($this->meta, 'youth_corner_poll_question');
        }

        if ($this->isLocalVoicesPost()) {
            return (string) data_get($this->meta, 'local_voice_poll_question');
        }

        if ($this->isMyAreaPost()) {
            return (string) data_get($this->meta, 'my_area_poll_question');
        }

        if ($this->isCommunityIssuesPost()) {
            return (string) data_get($this->meta, 'community_issue_poll_question');
        }

        if ($this->isAgriculturePost()) {
            return (string) data_get($this->meta, 'agriculture_poll_question');
        }

        if ($this->isEnvironmentPost()) {
            return (string) data_get($this->meta, 'environment_poll_question');
        }

        if ($this->isAstroConsultancyPost()) {
            return (string) data_get($this->meta, 'astro_consultancy_poll_question');
        }

        if ($this->isReligionSpiritualityPost()) {
            return (string) data_get($this->meta, 'religion_spirituality_poll_question');
        }

        if ($this->isCreativeCornerPost()) {
            return (string) data_get($this->meta, 'creative_corner_poll_question');
        }

        return (string) $this->poll_subject;
    }

    /**
     * @return array<string, string>
     */
    public function businessPollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'business_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::businessDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function womensWorldPollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'womens_world_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::womensWorldDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function studentCornerPollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'student_corner_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::studentCornerDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function youthCornerPollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'youth_corner_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::youthCornerDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function localVoicePollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'local_voice_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::localVoiceDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function myAreaPollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'my_area_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::myAreaDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function communityIssuePollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'community_issue_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::communityIssueDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function agriculturePollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'agriculture_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::agricultureDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function pollOptionsForDisplay(): array
    {
        if ($this->isAwarenessPost()) {
            return [
                self::POLL_OPTION_YES => 'Yes',
                self::POLL_OPTION_NO => 'No',
                self::POLL_OPTION_NOT_SURE => 'Planning To',
            ];
        }

        if ($this->isBusinessPost()) {
            return $this->businessPollOptionsForDisplay();
        }

        if ($this->isWomensWorldPost()) {
            return $this->womensWorldPollOptionsForDisplay();
        }

        if ($this->isStudentCornerPost()) {
            return $this->studentCornerPollOptionsForDisplay();
        }

        if ($this->isYouthCornerPost()) {
            return $this->youthCornerPollOptionsForDisplay();
        }

        if ($this->isLocalVoicesPost()) {
            return $this->localVoicePollOptionsForDisplay();
        }

        if ($this->isMyAreaPost()) {
            return $this->myAreaPollOptionsForDisplay();
        }

        if ($this->isCommunityIssuesPost()) {
            return $this->communityIssuePollOptionsForDisplay();
        }

        if ($this->isAgriculturePost()) {
            return $this->agriculturePollOptionsForDisplay();
        }

        if ($this->isEnvironmentPost()) {
            return $this->environmentPollOptionsForDisplay();
        }

        if ($this->isAstroConsultancyPost()) {
            return $this->astroConsultancyPollOptionsForDisplay();
        }

        if ($this->isReligionSpiritualityPost()) {
            return $this->religionSpiritualityPollOptionsForDisplay();
        }

        if ($this->isCreativeCornerPost()) {
            return $this->creativeCornerPollOptionsForDisplay();
        }

        return self::POLL_OPTIONS;
    }

    public function allowsAwarenessCauseSupport(): bool
    {
        return $this->isAwarenessPost()
            && (bool) data_get($this->meta, 'awareness_allow_cause_support', true);
    }

    public function allowsAwarenessPledges(): bool
    {
        return $this->isAwarenessPost()
            && (bool) data_get($this->meta, 'awareness_allow_pledges', false)
            && $this->awarenessPledgeOptions() !== [];
    }

    public function allowsCampaignJoin(): bool
    {
        return $this->isAwarenessPost()
            && (bool) data_get($this->meta, 'awareness_allow_campaign_join', false);
    }

    /**
     * @return list<string>
     */
    public function awarenessPledgeOptions(): array
    {
        if (! $this->isAwarenessPost()) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $option): string => trim((string) $option),
            (array) data_get($this->meta, 'awareness_pledge_options', [])
        )));
    }

    /**
     * @return list<string>
     */
    public function awarenessCallToActionItems(): array
    {
        if (! $this->isAwarenessPost()) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $item): string => trim((string) $item),
            (array) data_get($this->meta, 'awareness_action_items', [])
        )));
    }

    /**
     * @return list<string>
     */
    public function awarenessSocialImpactCategories(): array
    {
        if (! $this->isAwarenessPost()) {
            return [];
        }

        return array_values(array_filter((array) data_get($this->meta, 'awareness_social_impact_categories', [])));
    }

    public function awarenessHasEventDetails(): bool
    {
        return $this->isAwarenessPost()
            && (bool) data_get($this->meta, 'awareness_has_event', false);
    }

    /**
     * @return array<string, int>
     */
    public function pollCounts(): array
    {
        $counts = $this->relationLoaded('pollVotes')
            ? $this->pollVotes->groupBy('option')->map->count()
            : $this->pollVotes()
                ->selectRaw('option, count(*) as total')
                ->groupBy('option')
                ->pluck('total', 'option');

        $result = ['total' => 0];

        foreach (array_keys($this->pollOptionsForDisplay()) as $optionKey) {
            $count = (int) ($counts[$optionKey] ?? 0);
            $result[$optionKey] = $count;
            $result['total'] += $count;
        }

        return $result;
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

    public static function usesAutobiographyFlow(?string $contentType): bool
    {
        return in_array($contentType, self::LIFE_STORY_CONTENT_TYPES, true);
    }

    public static function usesChildrensCornerFlow(?string $contentType): bool
    {
        return $contentType === 'childrens-corner';
    }

    public static function usesAwarenessFlow(?string $contentType): bool
    {
        return $contentType === 'awareness';
    }

    public static function usesBusinessFlow(?string $contentType): bool
    {
        return $contentType === 'business';
    }

    public static function usesWomensWorldFlow(?string $contentType): bool
    {
        return $contentType === 'womens-world';
    }

    public static function usesSeniorCitizensForumFlow(?string $contentType): bool
    {
        return $contentType === 'senior-citizens-forum';
    }

    public static function usesStudentCornerFlow(?string $contentType): bool
    {
        return $contentType === 'student-corner';
    }

    public static function usesYouthCornerFlow(?string $contentType): bool
    {
        return $contentType === 'youth-corner';
    }

    public static function usesLocalVoicesFlow(?string $contentType): bool
    {
        return $contentType === 'local-voices';
    }

    public static function usesMyAreaFlow(?string $contentType): bool
    {
        return $contentType === 'my-area';
    }

    public static function usesCommunityIssuesFlow(?string $contentType): bool
    {
        return $contentType === 'community-issues';
    }

    public static function usesAgricultureFlow(?string $contentType): bool
    {
        return $contentType === 'agriculture';
    }

    public static function usesEnvironmentFlow(?string $contentType): bool
    {
        return $contentType === 'environment';
    }

    public static function usesScienceTechnologyFlow(?string $contentType): bool
    {
        return $contentType === 'science-technology';
    }

    public static function usesAstroConsultancyFlow(?string $contentType): bool
    {
        return $contentType === 'astro-consultancy';
    }

    public static function usesReligionSpiritualityFlow(?string $contentType): bool
    {
        return $contentType === 'religion-spirituality';
    }

    public static function usesCreativeCornerFlow(?string $contentType): bool
    {
        return $contentType === 'creative-corner';
    }

    public static function usesCompetitionsFlow(?string $contentType): bool
    {
        return $contentType === 'competitions';
    }

    public function isChildrensCornerPost(): bool
    {
        return self::usesChildrensCornerFlow($this->content_type);
    }

    public function isAwarenessPost(): bool
    {
        return self::usesAwarenessFlow($this->content_type);
    }

    public function isBusinessPost(): bool
    {
        return self::usesBusinessFlow($this->content_type);
    }

    public function isWomensWorldPost(): bool
    {
        return self::usesWomensWorldFlow($this->content_type);
    }

    public function isSeniorCitizensForumPost(): bool
    {
        return self::usesSeniorCitizensForumFlow($this->content_type);
    }

    public function isStudentCornerPost(): bool
    {
        return self::usesStudentCornerFlow($this->content_type);
    }

    public function isYouthCornerPost(): bool
    {
        return self::usesYouthCornerFlow($this->content_type);
    }

    public function isLocalVoicesPost(): bool
    {
        return self::usesLocalVoicesFlow($this->content_type);
    }

    public function isMyAreaPost(): bool
    {
        return self::usesMyAreaFlow($this->content_type);
    }

    public function isCommunityIssuesPost(): bool
    {
        return self::usesCommunityIssuesFlow($this->content_type);
    }

    public function isAgriculturePost(): bool
    {
        return self::usesAgricultureFlow($this->content_type);
    }

    public function isEnvironmentPost(): bool
    {
        return self::usesEnvironmentFlow($this->content_type);
    }

    public function isScienceTechnologyPost(): bool
    {
        return self::usesScienceTechnologyFlow($this->content_type);
    }

    public function isAstroConsultancyPost(): bool
    {
        return self::usesAstroConsultancyFlow($this->content_type);
    }

    public function isReligionSpiritualityPost(): bool
    {
        return self::usesReligionSpiritualityFlow($this->content_type);
    }

    public function isCreativeCornerPost(): bool
    {
        return self::usesCreativeCornerFlow($this->content_type);
    }

    public function isCompetitionsPost(): bool
    {
        return self::usesCompetitionsFlow($this->content_type);
    }

    /**
     * @return list<string>
     */
    public function competitionsUniqueFeatureLabels(): array
    {
        if (! $this->isCompetitionsPost()) {
            return [];
        }

        $features = [];

        if (data_get($this->meta, 'competitions_enable_multi_section')) {
            $features[] = 'Multi-Section Competition';
        }
        if (data_get($this->meta, 'competitions_enable_auto_portfolio')) {
            $features[] = 'Auto Portfolio Generation';
        }
        if (data_get($this->meta, 'competitions_enable_entry_qr_codes')) {
            $features[] = 'QR Code for Every Entry';
        }
        if (data_get($this->meta, 'competitions_enable_achievement_badges')) {
            $features[] = 'Achievement & Badge System';
        }
        if (data_get($this->meta, 'competitions_enable_leaderboards')) {
            $features[] = 'Leaderboard';
        }
        if (data_get($this->meta, 'competitions_enable_institution_dashboard')) {
            $features[] = 'School & College Dashboard';
        }
        if (data_get($this->meta, 'competitions_enable_sponsored_branding')) {
            $features[] = 'Sponsored Competition';
        }
        if (data_get($this->meta, 'competitions_enable_ecommerce')) {
            $features[] = 'E-commerce Integration';
        }
        if (data_get($this->meta, 'competitions_enable_voting_fraud_protection')) {
            $features[] = 'Community Voting with Fraud Protection';
        }
        if (data_get($this->meta, 'competitions_enable_digital_certificates')) {
            $features[] = 'Certificates & Digital Awards';
        }

        return $features;
    }

    public function competitionsHasFlagshipFeatures(): bool
    {
        return $this->competitionsUniqueFeatureLabels() !== [];
    }

    public function astroConsultancyPostTypeLabel(): ?string
    {
        if (! $this->isAstroConsultancyPost()) {
            return null;
        }

        $postType = (string) (data_get($this->meta, 'astro_consultancy_post_type') ?: $this->writing_purpose);

        return filled($postType) ? $postType : null;
    }

    public function astroConsultancyCategoryLabel(): ?string
    {
        if (! $this->isAstroConsultancyPost()) {
            return null;
        }

        return data_get($this->meta, 'astro_consultancy_category') ?: $this->category;
    }

    /**
     * @return list<string>
     */
    public function astroConsultancyTargetAudiences(): array
    {
        if (! $this->isAstroConsultancyPost()) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            (array) data_get($this->meta, 'astro_consultancy_target_audience', [])
        )));
    }

    /**
     * @return list<string>
     */
    public function astroConsultancyConsultationTopics(): array
    {
        if (! $this->isAstroConsultancyPost()) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            (array) data_get($this->meta, 'astro_consultancy_consultation_topics', [])
        )));
    }

    /**
     * @return list<string>
     */
    public function astroConsultancyKnowledgeLibraryTopics(): array
    {
        if (! $this->isAstroConsultancyPost()) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            (array) data_get($this->meta, 'astro_consultancy_knowledge_library_topics', [])
        )));
    }

    public function astroEnablesConsultantLinking(): bool
    {
        return $this->isAstroConsultancyPost()
            && (bool) data_get($this->meta, 'astro_consultancy_enable_consultant_linking', false);
    }

    public function astroEnablesLiveQa(): bool
    {
        return $this->isAstroConsultancyPost()
            && (bool) data_get($this->meta, 'astro_consultancy_enable_live_qa', false);
    }

    /**
     * @return list<string>
     */
    public function astroPrivateQueryOptionsForDisplay(): array
    {
        if (! $this->isAstroConsultancyPost()) {
            return [];
        }

        return array_values(array_intersect(
            (array) data_get($this->meta, 'astro_consultancy_private_query_options', []),
            CommunityContentTaxonomy::astroConsultancyPrivateQueryOptions()
        ));
    }

    public function astroHasPrivateQueryActions(): bool
    {
        return $this->astroPrivateQueryOptionsForDisplay() !== [];
    }

    public function astroHasEngagementActions(): bool
    {
        return $this->astroHasPrivateQueryActions()
            || $this->astroEnablesLiveQa()
            || $this->astroEnablesConsultantLinking()
            || filled(data_get($this->meta, 'astro_consultancy_ask_community'));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function astroConsultancyDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'astro_consultancy_documents', []));
    }

    public function astroHasHoroscopeDetails(): bool
    {
        return filled(data_get($this->meta, 'astro_consultancy_zodiac_sign'))
            || filled(data_get($this->meta, 'astro_consultancy_horoscope_period'));
    }

    public function astroHasVastuDetails(): bool
    {
        return $this->astroConsultancyVastuPropertyTypes() !== []
            || $this->astroConsultancyVastuAreas() !== [];
    }

    /**
     * @return list<string>
     */
    public function astroConsultancyVastuPropertyTypes(): array
    {
        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            (array) data_get($this->meta, 'astro_consultancy_vastu_property_types', [])
        )));
    }

    /**
     * @return list<string>
     */
    public function astroConsultancyVastuAreas(): array
    {
        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            (array) data_get($this->meta, 'astro_consultancy_vastu_areas', [])
        )));
    }

    public function astroHasNumerologyDetails(): bool
    {
        return filled(data_get($this->meta, 'astro_consultancy_life_path_number'))
            || filled(data_get($this->meta, 'astro_consultancy_destiny_number'))
            || filled(data_get($this->meta, 'astro_consultancy_name_number'))
            || filled(data_get($this->meta, 'astro_consultancy_lucky_number'))
            || filled(data_get($this->meta, 'astro_consultancy_compatibility'));
    }

    public function astroHasGemstoneDetails(): bool
    {
        return filled(data_get($this->meta, 'astro_consultancy_gemstone'))
            || filled(data_get($this->meta, 'astro_consultancy_gemstone_planet'))
            || filled(data_get($this->meta, 'astro_consultancy_gemstone_benefits'))
            || filled(data_get($this->meta, 'astro_consultancy_gemstone_precautions'));
    }

    public function astroHasFestivalDetails(): bool
    {
        return filled(data_get($this->meta, 'astro_consultancy_festival_name'))
            || filled(data_get($this->meta, 'astro_consultancy_muhurat_type'))
            || filled(data_get($this->meta, 'astro_consultancy_muhurat_date'))
            || filled(data_get($this->meta, 'astro_consultancy_muhurat_time'))
            || filled(data_get($this->meta, 'astro_consultancy_festival_significance'));
    }

    /**
     * @return array<string, string>
     */
    public function astroConsultancyPollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'astro_consultancy_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::astroConsultancyDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    public function religionSpiritualityPostTypeLabel(): ?string
    {
        if (! $this->isReligionSpiritualityPost()) {
            return null;
        }

        $postType = (string) data_get($this->meta, 'religion_spirituality_post_type');

        return filled($postType) ? $postType : null;
    }

    public function religionSpiritualityCategoryLabel(): ?string
    {
        if (! $this->isReligionSpiritualityPost()) {
            return null;
        }

        return data_get($this->meta, 'religion_spirituality_category') ?: $this->category;
    }

    /**
     * @return list<string>
     */
    public function religionSpiritualityTargetAudiences(): array
    {
        if (! $this->isReligionSpiritualityPost()) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            (array) data_get($this->meta, 'religion_spirituality_target_audience', [])
        )));
    }

    public function creativeCornerPostTypeLabel(): ?string
    {
        if (! $this->isCreativeCornerPost()) {
            return null;
        }

        $postType = (string) data_get($this->meta, 'creative_corner_post_type');

        return filled($postType) ? $postType : null;
    }

    public function creativeCornerCategoryLabel(): ?string
    {
        if (! $this->isCreativeCornerPost()) {
            return null;
        }

        return data_get($this->meta, 'creative_corner_category') ?: $this->category;
    }

    /**
     * @return list<string>
     */
    public function creativeCornerTargetAudiences(): array
    {
        if (! $this->isCreativeCornerPost()) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            (array) data_get($this->meta, 'creative_corner_target_audience', [])
        )));
    }

    public function creativeCornerHasCommerceFeatures(): bool
    {
        if (! $this->isCreativeCornerPost()) {
            return false;
        }

        return (bool) data_get($this->meta, 'creative_corner_available_for_sale')
            || count((array) data_get($this->meta, 'creative_corner_commission_options', [])) > 0;
    }

    /**
     * @return list<string>
     */
    public function religionSpiritualityMeditationTopics(): array
    {
        if (! $this->isReligionSpiritualityPost()) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            (array) data_get($this->meta, 'religion_spirituality_meditation_topics', [])
        )));
    }

    /**
     * @return list<string>
     */
    public function religionSpiritualityCommunityServiceActivities(): array
    {
        if (! $this->isReligionSpiritualityPost()) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            (array) data_get($this->meta, 'religion_spirituality_community_service_activities', [])
        )));
    }

    /**
     * @return list<string>
     */
    public function religionSpiritualityUniqueFeatureLabels(): array
    {
        if (! $this->isReligionSpiritualityPost()) {
            return [];
        }

        $features = [];

        if (data_get($this->meta, 'religion_spirituality_enable_digital_pilgrimage_guide')) {
            $features[] = 'Digital Pilgrimage Guide';
        }
        if (data_get($this->meta, 'religion_spirituality_enable_festival_calendar')) {
            $features[] = 'Festival Calendar';
        }
        if (data_get($this->meta, 'religion_spirituality_enable_community_service_directory')) {
            $features[] = 'Community Service Directory';
        }
        if (data_get($this->meta, 'religion_spirituality_enable_wisdom_library')) {
            $features[] = 'Wisdom Library';
        }

        return $features;
    }

    public function religionSpiritualityHasFlagshipFeatures(): bool
    {
        return $this->religionSpiritualityUniqueFeatureLabels() !== [];
    }

    /**
     * @return array<string, string>
     */
    public function religionSpiritualityPollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'religion_spirituality_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::religionSpiritualityDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function creativeCornerPollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'creative_corner_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::creativeCornerDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    public function communityIssueVisibilitySetting(): string
    {
        if (! $this->isCommunityIssuesPost()) {
            return CommunityContentTaxonomy::communityIssueDefaultVisibilitySetting();
        }

        $setting = (string) data_get($this->meta, 'community_issue_visibility', '');

        return array_key_exists($setting, CommunityContentTaxonomy::communityIssueVisibilitySettings())
            ? $setting
            : CommunityContentTaxonomy::communityIssueDefaultVisibilitySetting();
    }

    public function communityIssueVisibilityLabel(): string
    {
        return CommunityContentTaxonomy::communityIssueVisibilitySettings()[$this->communityIssueVisibilitySetting()]
            ?? Str::headline($this->communityIssueVisibilitySetting());
    }

    public function requiresCommunityIssuePrivateLink(): bool
    {
        return $this->isCommunityIssuesPost() && $this->communityIssueVisibilitySetting() === 'private_link';
    }

    public function allowsCommunityIssuePrivateLinkAccess(?string $accessToken): bool
    {
        if (! $this->requiresCommunityIssuePrivateLink()) {
            return false;
        }

        $token = (string) data_get($this->meta, 'community_issue_private_link_token', '');

        return filled($token) && filled($accessToken) && hash_equals($token, $accessToken);
    }

    public function communityIssuePrivateLinkUrl(): ?string
    {
        if (! $this->requiresCommunityIssuePrivateLink()) {
            return null;
        }

        $token = (string) data_get($this->meta, 'community_issue_private_link_token', '');

        if (blank($token)) {
            return null;
        }

        return route('community.show', $this).'?access='.$token;
    }

    public function allowsCommunityIssueSupport(): bool
    {
        return $this->isCommunityIssuesPost()
            && (bool) data_get($this->meta, 'community_issue_allow_support', true);
    }

    public function allowsCommunityIssueFollow(): bool
    {
        return $this->isCommunityIssuesPost()
            && (bool) data_get($this->meta, 'community_issue_allow_follow', true);
    }

    public function allowsCommunityIssueVerification(): bool
    {
        return $this->isCommunityIssuesPost()
            && (bool) data_get($this->meta, 'community_issue_allow_verification', true);
    }

    public function communityIssueEscalationThreshold(): int
    {
        $threshold = (int) data_get($this->meta, 'community_issue_escalation_threshold', CommunityContentTaxonomy::communityIssueDefaultEscalationThreshold());

        return max(10, min(10000, $threshold));
    }

    public function isCommunityIssueEscalated(int $supportCount): bool
    {
        return $this->isCommunityIssuesPost()
            && (bool) data_get($this->meta, 'community_issue_allow_campaign', true)
            && $supportCount >= $this->communityIssueEscalationThreshold();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function communityIssuePhotoEvidence(): array
    {
        return array_values((array) data_get($this->meta, 'community_issue_photo_evidence', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function communityIssueDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'community_issue_documents', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function agricultureProblemPhotos(): array
    {
        return array_values((array) data_get($this->meta, 'agriculture_problem_photos', []));
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function agricultureGallery(): array
    {
        return (array) data_get($this->meta, 'agriculture_gallery', []);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function agricultureDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'agriculture_documents', []));
    }

    /**
     * @return list<array{category: string, label: string, photo: array<string, mixed>}>
     */
    public function agricultureGalleryItemsForDisplay(): array
    {
        if (! $this->isAgriculturePost()) {
            return [];
        }

        $labels = CommunityContentTaxonomy::agricultureGalleryCategories();
        $items = [];

        foreach ($this->agricultureGallery() as $category => $photos) {
            $label = $labels[$category] ?? Str::headline((string) $category);

            foreach ((array) $photos as $photo) {
                if (filled(data_get($photo, 'url'))) {
                    $items[] = [
                        'category' => (string) $category,
                        'label' => $label,
                        'photo' => $photo,
                    ];
                }
            }
        }

        return $items;
    }

    public function enablesAgricultureKnowledgeExchange(): bool
    {
        return $this->isAgriculturePost()
            && (bool) data_get($this->meta, 'agriculture_enable_knowledge_exchange', false);
    }

    public function enablesAgricultureCropDoctor(): bool
    {
        return $this->isAgriculturePost()
            && (bool) data_get($this->meta, 'agriculture_enable_crop_doctor', false);
    }

    public function agricultureNeedsExpertAssistance(): bool
    {
        return $this->isAgriculturePost()
            && data_get($this->meta, 'agriculture_expert_assistance') === 'yes';
    }

    public function agricultureShareTypeLabel(): ?string
    {
        if (! $this->isAgriculturePost()) {
            return null;
        }

        $shareType = (string) (data_get($this->meta, 'agriculture_share_type') ?: $this->writing_purpose);

        return filled($shareType) ? $shareType : null;
    }

    public function agricultureCategoryLabel(): ?string
    {
        if (! $this->isAgriculturePost()) {
            return null;
        }

        $category = (string) (data_get($this->meta, 'agriculture_category') ?: $this->category);

        return filled($category) ? $category : null;
    }

    /**
     * @return list<string>
     */
    public function agricultureTargetAudiences(): array
    {
        if (! $this->isAgriculturePost()) {
            return [];
        }

        return array_values(array_filter((array) data_get($this->meta, 'agriculture_target_audiences', [])));
    }

    /**
     * @return list<string>
     */
    public function agricultureWaterConservationPractices(): array
    {
        if (! $this->isAgriculturePost()) {
            return [];
        }

        return array_values(array_filter((array) data_get($this->meta, 'agriculture_water_conservation_practices', [])));
    }

    /**
     * @return list<string>
     */
    public function agricultureLivestockTypes(): array
    {
        if (! $this->isAgriculturePost()) {
            return [];
        }

        return array_values(array_filter((array) data_get($this->meta, 'agriculture_livestock_types', [])));
    }

    public function agricultureHasWaterManagementDetails(): bool
    {
        if (! $this->isAgriculturePost()) {
            return false;
        }

        return filled(data_get($this->meta, 'agriculture_irrigation_method'))
            || filled(data_get($this->meta, 'agriculture_water_source'))
            || $this->agricultureWaterConservationPractices() !== [];
    }

    /**
     * @return array<string, string>
     */
    public function environmentPollOptionsForDisplay(): array
    {
        $options = collect((array) data_get($this->meta, 'environment_poll_options', []))
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        if ($options->isEmpty()) {
            $options = collect(CommunityContentTaxonomy::environmentDefaultPollOptions());
        }

        return $options
            ->mapWithKeys(fn (string $option): array => [\Illuminate\Support\Str::slug($option) => $option])
            ->all();
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function environmentGallery(): array
    {
        return (array) data_get($this->meta, 'environment_gallery', []);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function environmentDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'environment_documents', []));
    }

    /**
     * @return list<array{category: string, label: string, photo: array<string, mixed>}>
     */
    public function environmentGalleryItemsForDisplay(): array
    {
        if (! $this->isEnvironmentPost()) {
            return [];
        }

        $items = [];

        foreach (CommunityContentTaxonomy::environmentGalleryCategories() as $categoryKey => $categoryLabel) {
            foreach (array_values((array) data_get($this->environmentGallery(), $categoryKey, [])) as $photo) {
                if (filled(data_get($photo, 'url'))) {
                    $items[] = [
                        'category' => $categoryKey,
                        'label' => $categoryLabel,
                        'photo' => $photo,
                    ];
                }
            }
        }

        return $items;
    }

    public function environmentPostTypeLabel(): ?string
    {
        if (! $this->isEnvironmentPost()) {
            return null;
        }

        $postType = (string) (data_get($this->meta, 'environment_post_type') ?: $this->writing_purpose);

        return filled($postType) ? $postType : null;
    }

    public function environmentCategoryLabel(): ?string
    {
        if (! $this->isEnvironmentPost()) {
            return null;
        }

        $category = (string) (data_get($this->meta, 'environment_category') ?: $this->category);

        return filled($category) ? $category : null;
    }

    /**
     * @return list<string>
     */
    public function environmentParticipationRequests(): array
    {
        if (! $this->isEnvironmentPost()) {
            return [];
        }

        return array_values(array_filter((array) data_get($this->meta, 'environment_participation_requests', [])));
    }

    /**
     * @return list<string>
     */
    public function environmentSoilConservationMethods(): array
    {
        if (! $this->isEnvironmentPost()) {
            return [];
        }

        return array_values(array_filter((array) data_get($this->meta, 'environment_soil_conservation_methods', [])));
    }

    /**
     * @return list<string>
     */
    public function environmentWasteTypes(): array
    {
        if (! $this->isEnvironmentPost()) {
            return [];
        }

        return array_values(array_filter((array) data_get($this->meta, 'environment_waste_types', [])));
    }

    /**
     * @return list<string>
     */
    public function environmentBiodiversityTypes(): array
    {
        if (! $this->isEnvironmentPost()) {
            return [];
        }

        return array_values(array_filter((array) data_get($this->meta, 'environment_biodiversity_types', [])));
    }

    /**
     * @return list<string>
     */
    public function environmentClimateImpacts(): array
    {
        if (! $this->isEnvironmentPost()) {
            return [];
        }

        return array_values(array_filter((array) data_get($this->meta, 'environment_climate_impacts', [])));
    }

    public function enablesEnvironmentImpactCalculator(): bool
    {
        return $this->isEnvironmentPost()
            && (bool) data_get($this->meta, 'environment_enable_impact_calculator', false);
    }

    public function showsOnGreenMap(): bool
    {
        return $this->isEnvironmentPost()
            && (bool) data_get($this->meta, 'environment_show_on_green_map', false);
    }

    public function enablesEnvironmentGreenLeader(): bool
    {
        return $this->isEnvironmentPost()
            && (bool) data_get($this->meta, 'environment_enable_green_leader', false);
    }

    public function allowsEnvironmentJoinCampaign(): bool
    {
        return $this->isEnvironmentPost()
            && (bool) data_get($this->meta, 'environment_allow_join_campaign', true);
    }

    public function allowsEnvironmentVolunteer(): bool
    {
        return $this->isEnvironmentPost()
            && (bool) data_get($this->meta, 'environment_allow_volunteer', true);
    }

    public function allowsEnvironmentDonate(): bool
    {
        return $this->isEnvironmentPost()
            && (bool) data_get($this->meta, 'environment_allow_donate', false);
    }

    public function allowsEnvironmentSupportInitiative(): bool
    {
        return $this->isEnvironmentPost()
            && (bool) data_get($this->meta, 'environment_allow_support_initiative', true);
    }

    public function allowsEnvironmentFollowCampaign(): bool
    {
        return $this->isEnvironmentPost()
            && (bool) data_get($this->meta, 'environment_allow_follow_campaign', true);
    }

    public function allowsEnvironmentVolunteerRegistration(): bool
    {
        return $this->isEnvironmentPost()
            && (bool) data_get($this->meta, 'environment_allow_volunteer_registration', true);
    }

    public function environmentHasParticipationActions(): bool
    {
        if (! $this->isEnvironmentPost()) {
            return false;
        }

        return $this->allowsEnvironmentJoinCampaign()
            || $this->allowsEnvironmentVolunteerRegistration()
            || $this->allowsEnvironmentSupportInitiative()
            || $this->allowsEnvironmentFollowCampaign();
    }

    public function environmentHasWaterDetails(): bool
    {
        if (! $this->isEnvironmentPost()) {
            return false;
        }

        return filled(data_get($this->meta, 'environment_water_source'))
            || filled(data_get($this->meta, 'environment_conservation_method'))
            || filled(data_get($this->meta, 'environment_water_saved'));
    }

    public function environmentHasImpactData(): bool
    {
        if (! $this->isEnvironmentPost() || ! $this->enablesEnvironmentImpactCalculator()) {
            return false;
        }

        return filled(data_get($this->meta, 'environment_data_trees_planted'))
            || filled(data_get($this->meta, 'environment_data_water_saved'))
            || filled(data_get($this->meta, 'environment_data_waste_collected'))
            || filled(data_get($this->meta, 'environment_data_people_participated'))
            || filled(data_get($this->meta, 'environment_data_area_covered'))
            || filled(data_get($this->meta, 'environment_data_carbon_reduction'))
            || filled(data_get($this->meta, 'environment_data_species_recorded'));
    }

    public function environmentHasEventDetails(): bool
    {
        if (! $this->isEnvironmentPost()) {
            return false;
        }

        return filled(data_get($this->meta, 'environment_event_campaign_name'))
            || filled(data_get($this->meta, 'environment_event_date'))
            || filled(data_get($this->meta, 'environment_event_venue'));
    }

    public function environmentHasSchemeDetails(): bool
    {
        if (! $this->isEnvironmentPost()) {
            return false;
        }

        return filled(data_get($this->meta, 'environment_scheme_name'));
    }

    /**
     * @return list<string>
     */
    public function communityIssueResolutionTimelineEntries(): array
    {
        return collect(preg_split('/\R/', (string) data_get($this->meta, 'community_issue_resolution_timeline', '')))
            ->map(fn (mixed $line): string => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    public function supportsCivicEngagement(): bool
    {
        return $this->isReportContent() || $this->isMyAreaPost() || $this->isCommunityIssuesPost();
    }

    public function isStudentCornerProjectPost(): bool
    {
        return $this->isStudentCornerPost()
            && data_get($this->meta, 'student_corner_content_type') === \App\Support\CommunityContentTaxonomy::studentCornerProjectContentType();
    }

    public function isYouthCornerProjectPost(): bool
    {
        return $this->isYouthCornerPost()
            && data_get($this->meta, 'youth_corner_content_type') === \App\Support\CommunityContentTaxonomy::youthCornerProjectContentType();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function studentCornerDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'student_corner_documents', []));
    }

    public function studentCornerGallery(): array
    {
        return array_values((array) data_get($this->meta, 'student_corner_gallery', []));
    }

    public function studentCornerAchievements(): array
    {
        return array_values((array) data_get($this->meta, 'student_corner_achievements', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function youthCornerDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'youth_corner_documents', []));
    }

    public function youthCornerGallery(): array
    {
        return array_values((array) data_get($this->meta, 'youth_corner_gallery', []));
    }

    public function youthCornerAchievements(): array
    {
        return array_values((array) data_get($this->meta, 'youth_corner_achievements', []));
    }

    public function studentCornerVisibilitySetting(): string
    {
        if (! $this->isStudentCornerPost()) {
            return CommunityContentTaxonomy::studentCornerDefaultVisibilitySetting();
        }

        $setting = (string) data_get($this->meta, 'student_corner_visibility', '');

        return array_key_exists($setting, CommunityContentTaxonomy::studentCornerVisibilitySettings())
            ? $setting
            : CommunityContentTaxonomy::studentCornerDefaultVisibilitySetting();
    }

    public function studentCornerVisibilityLabel(): string
    {
        return CommunityContentTaxonomy::studentCornerVisibilitySettings()[$this->studentCornerVisibilitySetting()]
            ?? Str::headline($this->studentCornerVisibilitySetting());
    }

    public function requiresStudentCornerPrivateLink(): bool
    {
        return $this->isStudentCornerPost() && $this->studentCornerVisibilitySetting() === 'private_link';
    }

    public function allowsStudentCornerPrivateLinkAccess(?string $accessToken): bool
    {
        if (! $this->requiresStudentCornerPrivateLink()) {
            return false;
        }

        $token = (string) data_get($this->meta, 'student_corner_private_link_token', '');

        return filled($token) && filled($accessToken) && hash_equals($token, $accessToken);
    }

    public function studentCornerPrivateLinkUrl(): ?string
    {
        if (! $this->requiresStudentCornerPrivateLink()) {
            return null;
        }

        $token = (string) data_get($this->meta, 'student_corner_private_link_token', '');

        if (blank($token)) {
            return null;
        }

        return route('community.show', $this).'?access='.$token;
    }

    public function youthCornerVisibilitySetting(): string
    {
        if (! $this->isYouthCornerPost()) {
            return CommunityContentTaxonomy::youthCornerDefaultVisibilitySetting();
        }

        $setting = (string) data_get($this->meta, 'youth_corner_visibility', '');

        return array_key_exists($setting, CommunityContentTaxonomy::youthCornerVisibilitySettings())
            ? $setting
            : CommunityContentTaxonomy::youthCornerDefaultVisibilitySetting();
    }

    public function youthCornerVisibilityLabel(): string
    {
        return CommunityContentTaxonomy::youthCornerVisibilitySettings()[$this->youthCornerVisibilitySetting()]
            ?? Str::headline($this->youthCornerVisibilitySetting());
    }

    public function requiresYouthCornerPrivateLink(): bool
    {
        return $this->isYouthCornerPost() && $this->youthCornerVisibilitySetting() === 'private_link';
    }

    public function allowsYouthCornerPrivateLinkAccess(?string $accessToken): bool
    {
        if (! $this->requiresYouthCornerPrivateLink()) {
            return false;
        }

        $token = (string) data_get($this->meta, 'youth_corner_private_link_token', '');

        return filled($token) && filled($accessToken) && hash_equals($token, $accessToken);
    }

    public function youthCornerPrivateLinkUrl(): ?string
    {
        if (! $this->requiresYouthCornerPrivateLink()) {
            return null;
        }

        $token = (string) data_get($this->meta, 'youth_corner_private_link_token', '');

        if (blank($token)) {
            return null;
        }

        return route('community.show', $this).'?access='.$token;
    }

    public function localVoiceVisibilitySetting(): string
    {
        if (! $this->isLocalVoicesPost()) {
            return CommunityContentTaxonomy::localVoiceDefaultVisibilitySetting();
        }

        $setting = (string) data_get($this->meta, 'local_voice_visibility', '');

        return array_key_exists($setting, CommunityContentTaxonomy::localVoiceVisibilitySettings())
            ? $setting
            : CommunityContentTaxonomy::localVoiceDefaultVisibilitySetting();
    }

    public function localVoiceVisibilityLabel(): string
    {
        return CommunityContentTaxonomy::localVoiceVisibilitySettings()[$this->localVoiceVisibilitySetting()]
            ?? Str::headline($this->localVoiceVisibilitySetting());
    }

    public function requiresLocalVoicePrivateLink(): bool
    {
        return $this->isLocalVoicesPost() && $this->localVoiceVisibilitySetting() === 'private_link';
    }

    public function allowsLocalVoicePrivateLinkAccess(?string $accessToken): bool
    {
        if (! $this->requiresLocalVoicePrivateLink()) {
            return false;
        }

        $token = (string) data_get($this->meta, 'local_voice_private_link_token', '');

        return filled($token) && filled($accessToken) && hash_equals($token, $accessToken);
    }

    public function localVoicePrivateLinkUrl(): ?string
    {
        if (! $this->requiresLocalVoicePrivateLink()) {
            return null;
        }

        $token = (string) data_get($this->meta, 'local_voice_private_link_token', '');

        if (blank($token)) {
            return null;
        }

        return route('community.show', $this).'?access='.$token;
    }

    public function allowsLocalVoiceSupport(): bool
    {
        return $this->isLocalVoicesPost()
            && (bool) data_get($this->meta, 'local_voice_allow_support', true);
    }

    public function allowsLocalVoiceFollow(): bool
    {
        return $this->isLocalVoicesPost()
            && (bool) data_get($this->meta, 'local_voice_allow_follow', true);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function localVoicePhotoEvidence(): array
    {
        return array_values((array) data_get($this->meta, 'local_voice_photo_evidence', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function localVoiceDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'local_voice_documents', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function localVoiceHeroImages(): array
    {
        return array_values((array) data_get($this->meta, 'local_voice_hero_images', []));
    }

    public function myAreaVisibilitySetting(): string
    {
        if (! $this->isMyAreaPost()) {
            return CommunityContentTaxonomy::myAreaDefaultVisibilitySetting();
        }

        $setting = (string) data_get($this->meta, 'my_area_visibility', '');

        return array_key_exists($setting, CommunityContentTaxonomy::myAreaVisibilitySettings())
            ? $setting
            : CommunityContentTaxonomy::myAreaDefaultVisibilitySetting();
    }

    public function myAreaVisibilityLabel(): string
    {
        return CommunityContentTaxonomy::myAreaVisibilitySettings()[$this->myAreaVisibilitySetting()]
            ?? Str::headline($this->myAreaVisibilitySetting());
    }

    public function requiresMyAreaPrivateLink(): bool
    {
        return $this->isMyAreaPost() && $this->myAreaVisibilitySetting() === 'private_link';
    }

    public function allowsMyAreaPrivateLinkAccess(?string $accessToken): bool
    {
        if (! $this->requiresMyAreaPrivateLink()) {
            return false;
        }

        $token = (string) data_get($this->meta, 'my_area_private_link_token', '');

        return filled($token) && filled($accessToken) && hash_equals($token, $accessToken);
    }

    public function myAreaPrivateLinkUrl(): ?string
    {
        if (! $this->requiresMyAreaPrivateLink()) {
            return null;
        }

        $token = (string) data_get($this->meta, 'my_area_private_link_token', '');

        if (blank($token)) {
            return null;
        }

        return route('community.show', $this).'?access='.$token;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function myAreaPhotoEvidence(): array
    {
        return array_values((array) data_get($this->meta, 'my_area_photo_evidence', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function myAreaDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'my_area_documents', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function myAreaHeroImages(): array
    {
        return array_values((array) data_get($this->meta, 'my_area_hero_images', []));
    }

    public function myAreaActivityType(): ?string
    {
        return filled(data_get($this->meta, 'my_area_activity_type'))
            ? (string) data_get($this->meta, 'my_area_activity_type')
            : null;
    }

    public function myAreaTopicCategory(): ?string
    {
        return (string) (data_get($this->meta, 'my_area_topic_category') ?: $this->category ?: '');
    }

    public function isStudentCornerGalleryImage(array $file): bool
    {
        $mime = strtolower((string) data_get($file, 'type', ''));
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $extension = strtolower(pathinfo((string) data_get($file, 'name', ''), PATHINFO_EXTENSION));

        return in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true);
    }

    public function isYouthCornerGalleryImage(array $file): bool
    {
        $mime = strtolower((string) data_get($file, 'type', ''));
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $extension = strtolower(pathinfo((string) data_get($file, 'name', ''), PATHINFO_EXTENSION));

        return in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true);
    }

    public function seniorCitizensForumAudioData(): ?array
    {
        $audio = data_get($this->meta, 'senior_citizens_forum_audio');

        return is_array($audio) && filled($audio['url'] ?? null)
            ? $audio
            : null;
    }

    public function seniorCitizensForumAudioUrl(): ?string
    {
        return $this->seniorCitizensForumAudioData()['url'] ?? null;
    }

    public function womensWorldAudioData(): ?array
    {
        $audio = data_get($this->meta, 'womens_world_audio');

        return is_array($audio) && filled($audio['url'] ?? null)
            ? $audio
            : null;
    }

    public function womensWorldAudioUrl(): ?string
    {
        return $this->womensWorldAudioData()['url'] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function womensWorldGallery(): array
    {
        return array_values((array) data_get($this->meta, 'womens_world_gallery', []));
    }

    public function isWomensWorldGalleryImage(array $file): bool
    {
        $mime = strtolower((string) data_get($file, 'type', ''));
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $extension = strtolower(pathinfo((string) data_get($file, 'name', ''), PATHINFO_EXTENSION));

        return in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true);
    }

    public function businessCategoryLabel(): ?string
    {
        if (! $this->isBusinessPost()) {
            return null;
        }

        return data_get($this->meta, 'business_category') ?: $this->category;
    }

    /**
     * @return list<string>
     */
    public function businessContactOptionsForDisplay(): array
    {
        if (! $this->isBusinessPost()) {
            return [];
        }

        $options = array_values(array_filter((array) data_get($this->meta, 'business_contact_options', [])));

        return $options === [] ? [] : $options;
    }

    public function allowsBusinessContact(): bool
    {
        return $this->isBusinessPost() && $this->businessContactOptionsForDisplay() !== [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function businessGallery(): array
    {
        return array_values((array) data_get($this->meta, 'business_gallery', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function businessDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'business_documents', []));
    }

    public function isBusinessGalleryImage(array $file): bool
    {
        $mime = strtolower((string) data_get($file, 'type', ''));
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $extension = strtolower(pathinfo((string) data_get($file, 'name', ''), PATHINFO_EXTENSION));

        return in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true);
    }

    /**
     * @return list<string>
     */
    public function allowedReactionLabels(): array
    {
        if ($this->usesChildFriendlyReactions()) {
            return \App\Support\CommunityContentTaxonomy::childrensCornerReactionLabels();
        }

        if ($this->isBusinessPost()) {
            return \App\Support\CommunityContentTaxonomy::businessReactionLabels();
        }

        if ($this->isWomensWorldPost()) {
            return \App\Support\CommunityContentTaxonomy::womensWorldReactionLabels();
        }

        if ($this->isSeniorCitizensForumPost()) {
            return \App\Support\CommunityContentTaxonomy::seniorCitizensForumReactionLabels();
        }

        if ($this->isStudentCornerPost()) {
            return \App\Support\CommunityContentTaxonomy::studentCornerReactionLabels();
        }

        if ($this->isYouthCornerPost()) {
            return \App\Support\CommunityContentTaxonomy::youthCornerReactionLabels();
        }

        if ($this->isLocalVoicesPost()) {
            return \App\Support\CommunityContentTaxonomy::localVoiceReactionLabels();
        }

        if ($this->isMyAreaPost()) {
            return \App\Support\CommunityContentTaxonomy::myAreaReactionLabels();
        }

        if ($this->isCommunityIssuesPost()) {
            return \App\Support\CommunityContentTaxonomy::communityIssueReactionLabels();
        }

        if ($this->isAgriculturePost()) {
            return \App\Support\CommunityContentTaxonomy::agricultureReactionLabels();
        }

        if ($this->isEnvironmentPost()) {
            return \App\Support\CommunityContentTaxonomy::environmentReactionLabels();
        }

        if ($this->isAstroConsultancyPost()) {
            return \App\Support\CommunityContentTaxonomy::astroConsultancyReactionLabels();
        }

        if ($this->isReligionSpiritualityPost()) {
            return \App\Support\CommunityContentTaxonomy::religionSpiritualityReactionLabels();
        }

        if ($this->isCreativeCornerPost()) {
            return \App\Support\CommunityContentTaxonomy::creativeCornerReactionLabels();
        }

        return ['Helpful', 'Inspiring', 'Excellent', 'Informative', 'Support', 'Vote', 'Dislike'];
    }

    /**
     * @return array<string, string>
     */
    public function reactionOptionsForDisplay(): array
    {
        if ($this->usesChildFriendlyReactions()) {
            return \App\Support\CommunityContentTaxonomy::childrensCornerReactionOptions();
        }

        if ($this->isBusinessPost()) {
            return \App\Support\CommunityContentTaxonomy::businessReactionOptions();
        }

        if ($this->isWomensWorldPost()) {
            return \App\Support\CommunityContentTaxonomy::womensWorldReactionOptions();
        }

        if ($this->isSeniorCitizensForumPost()) {
            return \App\Support\CommunityContentTaxonomy::seniorCitizensForumReactionOptions();
        }

        if ($this->isStudentCornerPost()) {
            return \App\Support\CommunityContentTaxonomy::studentCornerReactionOptions();
        }

        if ($this->isYouthCornerPost()) {
            return \App\Support\CommunityContentTaxonomy::youthCornerReactionOptions();
        }

        if ($this->isLocalVoicesPost()) {
            return \App\Support\CommunityContentTaxonomy::localVoiceReactionOptions();
        }

        if ($this->isMyAreaPost()) {
            return \App\Support\CommunityContentTaxonomy::myAreaReactionOptions();
        }

        if ($this->isCommunityIssuesPost()) {
            return \App\Support\CommunityContentTaxonomy::communityIssueReactionOptions();
        }

        if ($this->isAgriculturePost()) {
            return \App\Support\CommunityContentTaxonomy::agricultureReactionOptions();
        }

        if ($this->isEnvironmentPost()) {
            return \App\Support\CommunityContentTaxonomy::environmentReactionOptions();
        }

        if ($this->isAstroConsultancyPost()) {
            return \App\Support\CommunityContentTaxonomy::astroConsultancyReactionOptions();
        }

        if ($this->isReligionSpiritualityPost()) {
            return \App\Support\CommunityContentTaxonomy::religionSpiritualityReactionOptions();
        }

        if ($this->isCreativeCornerPost()) {
            return \App\Support\CommunityContentTaxonomy::creativeCornerReactionOptions();
        }

        if ($this->content_type === 'reports' && filled(data_get($this->meta, 'report_type'))) {
            return [
                'Support' => 'fa-solid fa-hand-holding-heart',
                'Vote' => 'fa-solid fa-square-poll-vertical',
                'Helpful' => 'fa-solid fa-circle-info',
                'Informative' => 'fa-solid fa-lightbulb',
                'Dislike' => 'fa-solid fa-thumbs-down',
            ];
        }

        return [
            'Helpful' => 'fa-solid fa-hand-holding-heart',
            'Inspiring' => 'fa-solid fa-lightbulb',
            'Excellent' => 'fa-solid fa-star',
            'Informative' => 'fa-solid fa-circle-info',
            'Dislike' => 'fa-solid fa-thumbs-down',
        ];
    }

    public function awarenessCategoryLabel(): ?string
    {
        if (! $this->isAwarenessPost()) {
            return null;
        }

        return data_get($this->meta, 'awareness_category') ?: $this->category;
    }

    public function subscriptionContentType(): string
    {
        if ($this->isMyAreaPost()) {
            return 'my-area';
        }

        if ($this->content_type === 'reports' && $this->category === 'Community Problem Report') {
            return 'my-area';
        }

        return (string) $this->content_type;
    }

    public function subscriptionCategory(): string
    {
        if ($this->isAwarenessPost()) {
            return (string) ($this->awarenessCategoryLabel() ?: $this->category);
        }

        if ($this->isBusinessPost()) {
            $category = (string) ($this->businessCategoryLabel() ?: $this->category);

            return match ($category) {
                'Startups' => 'Startup',
                default => $category,
            };
        }

        if ($this->isChildrensCornerPost()) {
            $category = (string) ($this->childrensCornerShareType() ?: $this->category);

            return match ($category) {
                'Stories' => 'Story',
                default => $category,
            };
        }

        return (string) $this->category;
    }

    public function awarenessCampaignPeriodForDisplay(): ?string
    {
        if (! $this->isAwarenessPost()) {
            return null;
        }

        $start = data_get($this->meta, 'awareness_campaign_start_date');
        $end = data_get($this->meta, 'awareness_campaign_end_date');

        if (filled($start) && filled($end)) {
            $startDate = \Illuminate\Support\Carbon::parse($start);
            $endDate = \Illuminate\Support\Carbon::parse($end);

            if ($startDate->isSameDay($endDate)) {
                return $startDate->format('j F Y');
            }

            if ($startDate->year === $endDate->year && $startDate->month === $endDate->month) {
                return $startDate->format('j').' – '.$endDate->format('j F Y');
            }

            if ($startDate->year === $endDate->year) {
                return $startDate->format('j F').' – '.$endDate->format('j F Y');
            }

            return $startDate->format('j F Y').' – '.$endDate->format('j F Y');
        }

        if (filled($start)) {
            return 'From '.\Illuminate\Support\Carbon::parse($start)->format('j F Y');
        }

        if (filled($end)) {
            return 'Until '.\Illuminate\Support\Carbon::parse($end)->format('j F Y');
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function awarenessInfographics(): array
    {
        return array_values((array) data_get($this->meta, 'awareness_infographics', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function awarenessDocuments(): array
    {
        return array_values((array) data_get($this->meta, 'awareness_documents', []));
    }

    public function isAwarenessInfographicImage(array $file): bool
    {
        $mime = strtolower((string) data_get($file, 'type', ''));
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $extension = strtolower(pathinfo((string) data_get($file, 'name', ''), PATHINFO_EXTENSION));

        return in_array($extension, ['png', 'jpg', 'jpeg'], true);
    }

    public function commentsModerated(): bool
    {
        if (! $this->isChildrensCornerPost()) {
            return false;
        }

        return (bool) data_get($this->meta, 'childrens_corner_comments_moderated', true);
    }

    public function usesChildFriendlyReactions(): bool
    {
        return $this->isChildrensCornerPost();
    }

    public function childrensCornerPrivacySetting(): string
    {
        if (! $this->isChildrensCornerPost()) {
            return \App\Support\CommunityContentTaxonomy::childrensCornerDefaultPrivacySetting();
        }

        $setting = (string) data_get($this->meta, 'childrens_corner_privacy_setting', '');

        return array_key_exists($setting, \App\Support\CommunityContentTaxonomy::childrensCornerPrivacySettings())
            ? $setting
            : \App\Support\CommunityContentTaxonomy::childrensCornerDefaultPrivacySetting();
    }

    public function childrensCornerPrivacyLabel(): string
    {
        return \App\Support\CommunityContentTaxonomy::childrensCornerPrivacySettings()[$this->childrensCornerPrivacySetting()]
            ?? 'Public with limited child information';
    }

    public function showsLimitedChildInformationTo(?User $viewer): bool
    {
        if (! $this->isChildrensCornerPost()) {
            return false;
        }

        if ($viewer !== null && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
            return false;
        }

        return $this->childrensCornerPrivacySetting() === 'public_limited';
    }

    public function isVisibleInCommunityTo(?User $viewer): bool
    {
        if ($this->isWomensWorldPost()) {
            if ($viewer !== null && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
                return true;
            }

            return match ($this->womensWorldVisibilitySetting()) {
                'public' => true,
                'registered_users', 'women_community_only' => $viewer !== null,
                'private_link' => false,
                default => true,
            };
        }

        if ($this->isSeniorCitizensForumPost()) {
            if ($viewer !== null && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
                return true;
            }

            return match ($this->seniorCitizensForumVisibilitySetting()) {
                'public' => true,
                'registered_users', 'senior_citizens_community' => $viewer !== null,
                'private_link' => false,
                default => true,
            };
        }

        if ($this->isStudentCornerPost()) {
            if ($viewer !== null && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
                return true;
            }

            return match ($this->studentCornerVisibilitySetting()) {
                'public' => true,
                'registered_users', 'students_only' => $viewer !== null,
                'private_link' => false,
                default => true,
            };
        }

        if ($this->isYouthCornerPost()) {
            if ($viewer !== null && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
                return true;
            }

            return match ($this->youthCornerVisibilitySetting()) {
                'public' => true,
                'registered_users', 'youth_community' => $viewer !== null,
                'private_link' => false,
                default => true,
            };
        }

        if ($this->isLocalVoicesPost()) {
            if ($viewer !== null && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
                return true;
            }

            return match ($this->localVoiceVisibilitySetting()) {
                'public' => true,
                'registered_users', 'local_community' => $viewer !== null,
                'private_link' => false,
                default => true,
            };
        }

        if ($this->isMyAreaPost()) {
            if ($viewer !== null && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
                return true;
            }

            return match ($this->myAreaVisibilitySetting()) {
                'public' => true,
                'registered_users', 'local_community' => $viewer !== null,
                'private_link' => false,
                default => true,
            };
        }

        if ($this->isCommunityIssuesPost()) {
            if ($viewer !== null && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
                return true;
            }

            return match ($this->communityIssueVisibilitySetting()) {
                'public' => true,
                'registered_users', 'local_community' => $viewer !== null,
                'private_link' => false,
                default => true,
            };
        }

        if (! $this->isChildrensCornerPost()) {
            return true;
        }

        if ($viewer !== null && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
            return true;
        }

        return match ($this->childrensCornerPrivacySetting()) {
            'public', 'public_limited' => true,
            'registered_users' => $viewer !== null,
            'school_community' => $viewer !== null,
            default => true,
        };
    }

    public function requiresAuthenticationForCommunityView(): bool
    {
        if ($this->isWomensWorldPost()) {
            return in_array($this->womensWorldVisibilitySetting(), ['registered_users', 'women_community_only'], true);
        }

        if ($this->isSeniorCitizensForumPost()) {
            return in_array($this->seniorCitizensForumVisibilitySetting(), ['registered_users', 'senior_citizens_community'], true);
        }

        if ($this->isStudentCornerPost()) {
            return in_array($this->studentCornerVisibilitySetting(), ['registered_users', 'students_only'], true);
        }

        if ($this->isYouthCornerPost()) {
            return in_array($this->youthCornerVisibilitySetting(), ['registered_users', 'youth_community'], true);
        }

        if ($this->isLocalVoicesPost()) {
            return in_array($this->localVoiceVisibilitySetting(), ['registered_users', 'local_community'], true);
        }

        if ($this->isMyAreaPost()) {
            return in_array($this->myAreaVisibilitySetting(), ['registered_users', 'local_community'], true);
        }

        if ($this->isCommunityIssuesPost()) {
            return in_array($this->communityIssueVisibilitySetting(), ['registered_users', 'local_community'], true);
        }

        if (! $this->isChildrensCornerPost()) {
            return false;
        }

        return in_array($this->childrensCornerPrivacySetting(), ['registered_users', 'school_community'], true);
    }

    public function requiresWomensWorldPrivateLink(): bool
    {
        return $this->isWomensWorldPost() && $this->womensWorldVisibilitySetting() === 'private_link';
    }

    public function womensWorldVisibilitySetting(): string
    {
        if (! $this->isWomensWorldPost()) {
            return \App\Support\CommunityContentTaxonomy::womensWorldDefaultVisibilitySetting();
        }

        $setting = (string) data_get($this->meta, 'womens_world_visibility', '');

        return array_key_exists($setting, \App\Support\CommunityContentTaxonomy::womensWorldVisibilitySettings())
            ? $setting
            : \App\Support\CommunityContentTaxonomy::womensWorldDefaultVisibilitySetting();
    }

    public function womensWorldVisibilityLabel(): string
    {
        return \App\Support\CommunityContentTaxonomy::womensWorldVisibilitySettings()[$this->womensWorldVisibilitySetting()]
            ?? 'Public';
    }

    public function allowsWomensWorldPrivateLinkAccess(?string $accessToken): bool
    {
        if (! $this->requiresWomensWorldPrivateLink()) {
            return false;
        }

        $token = (string) data_get($this->meta, 'womens_world_private_link_token', '');

        return filled($token) && filled($accessToken) && hash_equals($token, $accessToken);
    }

    public function womensWorldPrivateLinkUrl(): ?string
    {
        if (! $this->requiresWomensWorldPrivateLink()) {
            return null;
        }

        $token = (string) data_get($this->meta, 'womens_world_private_link_token', '');

        if (blank($token)) {
            return null;
        }

        return route('community.show', $this).'?access='.$token;
    }

    public function seniorCitizensForumVisibilitySetting(): string
    {
        if (! $this->isSeniorCitizensForumPost()) {
            return \App\Support\CommunityContentTaxonomy::seniorCitizensForumDefaultVisibilitySetting();
        }

        $setting = (string) data_get($this->meta, 'senior_citizens_forum_visibility', '');

        return array_key_exists($setting, \App\Support\CommunityContentTaxonomy::seniorCitizensForumVisibilitySettings())
            ? $setting
            : \App\Support\CommunityContentTaxonomy::seniorCitizensForumDefaultVisibilitySetting();
    }

    public function seniorCitizensForumVisibilityLabel(): string
    {
        return \App\Support\CommunityContentTaxonomy::seniorCitizensForumVisibilitySettings()[$this->seniorCitizensForumVisibilitySetting()]
            ?? 'Public';
    }

    public function requiresSeniorCitizensForumPrivateLink(): bool
    {
        return $this->isSeniorCitizensForumPost() && $this->seniorCitizensForumVisibilitySetting() === 'private_link';
    }

    public function allowsSeniorCitizensForumPrivateLinkAccess(?string $accessToken): bool
    {
        if (! $this->requiresSeniorCitizensForumPrivateLink()) {
            return false;
        }

        $token = (string) data_get($this->meta, 'senior_citizens_forum_private_link_token', '');

        return filled($token) && filled($accessToken) && hash_equals($token, $accessToken);
    }

    public function seniorCitizensForumPrivateLinkUrl(): ?string
    {
        if (! $this->requiresSeniorCitizensForumPrivateLink()) {
            return null;
        }

        $token = (string) data_get($this->meta, 'senior_citizens_forum_private_link_token', '');

        if (blank($token)) {
            return null;
        }

        return route('community.show', $this).'?access='.$token;
    }

    public function commentIsVisibleTo(?User $viewer, CommunityPostComment $comment): bool
    {
        if ($comment->is_approved) {
            return true;
        }

        return $viewer !== null && $viewer->id === $this->user_id;
    }

    public function usesLifeStoryFlow(): bool
    {
        return self::usesAutobiographyFlow($this->content_type);
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
                        'language' => \App\Support\CommunityContentTaxonomy::bookPageLanguageCode(is_array($page) ? ($page['language'] ?? 'en') : 'en'),
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
     * @return array{type: string, url?: string, video_id?: string, path?: string, name?: string}|null
     */
    public function childrensCornerVideoData(): ?array
    {
        $video = data_get($this->meta, 'childrens_corner_video');

        return is_array($video) && filled($video['type'] ?? null)
            ? $video
            : null;
    }

    public function childrensCornerYoutubeEmbedUrl(): ?string
    {
        $video = $this->childrensCornerVideoData();

        if (($video['type'] ?? null) !== 'youtube') {
            return null;
        }

        $videoId = $video['video_id'] ?? self::parseYoutubeVideoId($video['url'] ?? null);

        return $videoId ? 'https://www.youtube.com/embed/'.$videoId : null;
    }

    public function childrensCornerVideoFileUrl(): ?string
    {
        $video = $this->childrensCornerVideoData();

        if (($video['type'] ?? null) !== 'upload') {
            return null;
        }

        return self::resolveImageUrl($video['path'] ?? null);
    }

    /**
     * @return array{type: string, path?: string, name?: string, url?: string}|null
     */
    public function childrensCornerAudioData(): ?array
    {
        $audio = data_get($this->meta, 'childrens_corner_audio');

        return is_array($audio) && filled($audio['url'] ?? null)
            ? $audio
            : null;
    }

    public function childrensCornerAudioUrl(): ?string
    {
        return $this->childrensCornerAudioData()['url'] ?? null;
    }

    public function childrensCornerShareType(): ?string
    {
        $shareType = data_get($this->meta, 'child_share_type');

        return filled($shareType) ? (string) $shareType : ($this->category ?: null);
    }

    public function childrensCornerContentMode(): ?string
    {
        if (! $this->isChildrensCornerPost()) {
            return null;
        }

        return \App\Support\CommunityContentTaxonomy::childrensCornerContentMode($this->childrensCornerShareType());
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function childrensCornerGalleryImages(): \Illuminate\Support\Collection
    {
        return collect((array) data_get($this->meta, 'childrens_corner_gallery', []))
            ->filter(fn (mixed $image): bool => filled(data_get($image, 'url')))
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function childrensCornerProjectFiles(): \Illuminate\Support\Collection
    {
        return collect((array) data_get($this->meta, 'childrens_corner_project_files', []))
            ->filter(fn (mixed $file): bool => filled(data_get($file, 'url')) || filled(data_get($file, 'path')))
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function childrensCornerQuizQuestions(): \Illuminate\Support\Collection
    {
        return collect((array) data_get($this->meta, 'childrens_corner_quiz', []))
            ->filter(fn (mixed $question): bool => is_array($question) && filled(data_get($question, 'question')))
            ->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function childrensCornerArtData(): ?array
    {
        $art = data_get($this->meta, 'childrens_corner_art');

        return is_array($art) && filled($art['url'] ?? null) ? $art : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function childrensCornerCertificateData(): ?array
    {
        $certificate = data_get($this->meta, 'childrens_corner_certificate');

        return is_array($certificate) && (filled($certificate['url'] ?? null) || filled($certificate['path'] ?? null))
            ? $certificate
            : null;
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
        return $this->plainTextExcerpt(160) ?? '';
    }

    public function plainTextExcerpt(int $limit = 140): ?string
    {
        $source = filled($this->excerpt) ? (string) $this->excerpt : (string) $this->body;

        if (! filled(trim($source))) {
            return null;
        }

        $plainText = html_entity_decode(strip_tags($source), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plainText = trim((string) preg_replace('/\s+/u', ' ', $plainText));

        if ($plainText === '') {
            return null;
        }

        return Str::limit($plainText, $limit);
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

        if ($this->isMyAreaPost()) {
            $parts = array_filter([
                $this->myAreaActivityType(),
                data_get($this->meta, 'my_area_status_tracker'),
                $this->myAreaTopicCategory(),
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
