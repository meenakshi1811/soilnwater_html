<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StudyMaterial extends Model
{
    protected $fillable = [
        'educator_id',
        'user_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'pages',
        'material_type',
        'category',
        'class_course',
        'board_university',
        'subject',
        'topic_chapter',
        'exam_test',
        'language',
        'difficulty',
        'academic_year',
        'medium',
        'is_free',
        'is_trending',
        'is_verified',
        'tags',
        'contents',
        'average_rating',
        'reviews_count',
        'views_count',
        'downloads_count',
        'saves_count',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'contents' => 'array',
            'is_free' => 'boolean',
            'is_trending' => 'boolean',
            'is_verified' => 'boolean',
            'average_rating' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function educator(): BelongsTo
    {
        return $this->belongsTo(Educator::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(StudyMaterialReview::class)->latest();
    }

    public function bookmarkedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'study_material_bookmarks')->withTimestamps();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function fileUrl(): ?string
    {
        return filled($this->file_path) ? asset($this->file_path) : null;
    }

    public function thumbnailUrl(): ?string
    {
        return filled($this->thumbnail) ? asset($this->thumbnail) : null;
    }

    public function publicUrl(): string
    {
        return route('study-materials.show', $this->slug);
    }

    public function fileSizeLabel(): string
    {
        if (! $this->file_size) {
            return '—';
        }

        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function materialTypeLabel(): string
    {
        return match ($this->material_type) {
            'notes' => 'Notes',
            'question_papers' => 'Question Papers',
            'sample_papers' => 'Sample Papers',
            'worksheets' => 'Worksheets',
            'assignments' => 'Assignments',
            'reference_books' => 'Reference Books',
            'study_guides' => 'Study Guides',
            'videos' => 'Videos',
            default => ucfirst(str_replace('_', ' ', (string) $this->material_type)),
        };
    }

    public function recalculateRating(): void
    {
        $stats = StudyMaterialReview::query()
            ->where('study_material_id', $this->id)
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

    public function scopeNotes(Builder $query): Builder
    {
        return $query->where('material_type', 'notes');
    }

    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'study-material';
        $slug = $base;
        $i = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
