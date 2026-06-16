<?php

namespace App\Models;

use App\Support\CommunityContentTaxonomy;
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

    public const LOCATION_TYPE_VILLAGE = 'village';

    /** @var array<string, string> */
    public const LOCATION_TYPES = [
        self::LOCATION_TYPE_GLOBAL => 'Global',
        self::LOCATION_TYPE_INDIA => 'India',
        self::LOCATION_TYPE_STATE => 'State Specific',
        self::LOCATION_TYPE_DISTRICT => 'District Specific',
        self::LOCATION_TYPE_CITY => 'City Specific',
        self::LOCATION_TYPE_VILLAGE => 'Village Specific',
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

    protected $fillable = [
        'user_id',
        'content_type',
        'category',
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
        'allow_sharing',
        'allow_poll',
        'poll_subject',
        'is_featured',
        'is_sponsored',
        'is_highlighted',
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
            'tags' => 'array',
            'featured_images' => 'array',
            'video' => 'array',
            'meta' => 'array',
            'location_lat' => 'decimal:7',
            'location_lng' => 'decimal:7',
            'allow_comments' => 'boolean',
            'allow_sharing' => 'boolean',
            'allow_poll' => 'boolean',
            'is_featured' => 'boolean',
            'is_sponsored' => 'boolean',
            'is_highlighted' => 'boolean',
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

    public function typeLabel(): string
    {
        return CommunityContentTaxonomy::labels()[$this->content_type] ?? Str::headline($this->content_type);
    }

    /**
     * @return array<string, string>
     */
    public static function locationTypeOptions(): array
    {
        return self::LOCATION_TYPES;
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

    public function usesBookLayout(): bool
    {
        return self::isBookContentType($this->content_type);
    }

    /**
     * @return list<array{content: string, language: string}>
     */
    public function bookPages(): array
    {
        $pages = data_get($this->meta, 'book_pages');

        if (is_array($pages) && $pages !== []) {
            return collect($pages)
                ->map(fn (mixed $page): array => [
                    'content' => is_array($page) ? (string) ($page['content'] ?? '') : (string) $page,
                    'language' => in_array(is_array($page) ? ($page['language'] ?? 'en') : 'en', ['en', 'hi'], true)
                        ? (is_array($page) ? ($page['language'] ?? 'en') : 'en')
                        : 'en',
                ])
                ->filter(fn (array $page): bool => filled(strip_tags($page['content'])))
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
        if (! filled($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, 'uploads/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
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
