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
    public const STATUS_PUBLISHED = 'published';

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
        'location',
        'location_lat',
        'location_lng',
        'video',
        'meta',
        'allow_comments',
        'status',
        'published_at',
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
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)->whereNotNull('published_at');
    }

    public function typeLabel(): string
    {
        return CommunityContentTaxonomy::labels()[$this->content_type] ?? Str::headline($this->content_type);
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
}
