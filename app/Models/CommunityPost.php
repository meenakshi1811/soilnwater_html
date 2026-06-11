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
        'tags',
        'meta',
        'allow_comments',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'meta' => 'array',
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

    public function featuredImageUrl(): ?string
    {
        if (! $this->featured_image_path) {
            return null;
        }

        if (Str::startsWith($this->featured_image_path, ['http://', 'https://'])) {
            return $this->featured_image_path;
        }

        if (Str::startsWith($this->featured_image_path, 'uploads/')) {
            return asset($this->featured_image_path);
        }

        return asset('storage/'.$this->featured_image_path);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
