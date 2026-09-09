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
        'meta',
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
            'meta' => 'array',
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

    /**
     * @return array{label: string, icon: string, tone: string}
     */
    public function fileTypeMeta(): array
    {
        $type = strtolower((string) $this->file_type);

        return match (true) {
            str_contains($type, 'pdf') => ['label' => 'PDF', 'icon' => 'fa-file-pdf', 'tone' => 'pdf'],
            str_contains($type, 'doc') => ['label' => 'DOC', 'icon' => 'fa-file-word', 'tone' => 'doc'],
            str_contains($type, 'ppt') => ['label' => 'PPT', 'icon' => 'fa-file-powerpoint', 'tone' => 'ppt'],
            str_contains($type, 'xls') => ['label' => 'XLS', 'icon' => 'fa-file-excel', 'tone' => 'xls'],
            str_contains($type, 'jpg'), str_contains($type, 'jpeg'), str_contains($type, 'png'), str_contains($type, 'webp'), str_contains($type, 'gif') => ['label' => 'IMG', 'icon' => 'fa-file-image', 'tone' => 'img'],
            default => ['label' => strtoupper($type ?: 'FILE'), 'icon' => 'fa-file-lines', 'tone' => 'file'],
        };
    }

    /**
     * @return list<array{index: int, title: string, page_label: string, page_start: int, page_end: int}>
     */
    public function contentPages(): array
    {
        $sections = collect($this->contents ?? [])->filter()->values();
        $totalPages = max((int) $this->pages, $sections->count(), 1);

        if ($sections->isEmpty()) {
            return [[
                'index' => 0,
                'title' => $this->topic_chapter ?: 'Full Document',
                'page_label' => $totalPages > 1 ? 'Page 1-'.$totalPages : 'Page 1',
                'page_start' => 1,
                'page_end' => $totalPages,
            ]];
        }

        $pagesPerSection = max(1, (int) floor($totalPages / $sections->count()));
        $pageStart = 1;

        return $sections->values()->map(function ($title, $index) use ($sections, $totalPages, $pagesPerSection, &$pageStart) {
            $isLast = $index === ($sections->count() - 1);
            $pageEnd = $isLast ? $totalPages : min($pageStart + $pagesPerSection - 1, $totalPages);
            $pageLabel = $pageStart === $pageEnd
                ? 'Page '.$pageStart
                : 'Page '.$pageStart.'-'.$pageEnd;

            $item = [
                'index' => $index,
                'title' => (string) $title,
                'page_label' => $pageLabel,
                'page_start' => $pageStart,
                'page_end' => $pageEnd,
            ];

            $pageStart = $pageEnd + 1;

            return $item;
        })->all();
    }

    /**
     * @return list<array{label: string, url: string|null}>
     */
    public function breadcrumbTrail(): array
    {
        $items = [
            ['label' => 'Home', 'url' => route('frontend.index')],
            ['label' => 'Study Materials Library', 'url' => route('study-materials.library')],
        ];

        if (filled($this->category)) {
            $items[] = [
                'label' => $this->category,
                'url' => route('study-materials.notes', ['category' => $this->category]),
            ];
        }

        if (filled($this->class_course)) {
            $items[] = [
                'label' => $this->class_course,
                'url' => route('study-materials.notes', ['class_course' => $this->class_course]),
            ];
        }

        if (filled($this->subject)) {
            $items[] = [
                'label' => $this->subject,
                'url' => route('study-materials.notes', ['subject' => $this->subject]),
            ];
        }

        $items[] = ['label' => $this->title, 'url' => null];

        return $items;
    }

    public function canPreviewInline(): bool
    {
        $type = strtolower((string) $this->file_type);

        return str_contains($type, 'pdf')
            || str_contains($type, 'jpg')
            || str_contains($type, 'jpeg')
            || str_contains($type, 'png')
            || str_contains($type, 'webp');
    }

    public static function formatCompactCount(int|float|null $value, bool $millionPlus = false): string
    {
        $value = (int) ($value ?? 0);

        if ($value >= 1000000) {
            $formatted = rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.').'M';

            return $millionPlus ? $formatted.'+' : $formatted;
        }

        if ($value >= 1000) {
            return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'K';
        }

        return (string) $value;
    }

    public static function fileTypeGroup(?string $fileType): string
    {
        $type = strtolower((string) $fileType);

        return match (true) {
            str_contains($type, 'pdf') => 'pdf',
            str_contains($type, 'doc') => 'doc',
            str_contains($type, 'ppt') => 'ppt',
            str_contains($type, 'xls') => 'xls',
            str_contains($type, 'jpg'), str_contains($type, 'jpeg'), str_contains($type, 'png'), str_contains($type, 'webp'), str_contains($type, 'gif') => 'image',
            default => 'other',
        };
    }

    /**
     * @return array{label: string, icon: string, tone: string}
     */
    public static function materialTypeMeta(?string $materialType): array
    {
        return match ($materialType) {
            'notes' => ['label' => 'Notes', 'icon' => 'fa-note-sticky', 'tone' => 'notes'],
            'question_papers' => ['label' => 'Question Papers', 'icon' => 'fa-file-circle-question', 'tone' => 'question'],
            'sample_papers' => ['label' => 'Sample Papers', 'icon' => 'fa-file-lines', 'tone' => 'sample'],
            'worksheets' => ['label' => 'Worksheets', 'icon' => 'fa-file-pen', 'tone' => 'worksheet'],
            'assignments' => ['label' => 'Assignments', 'icon' => 'fa-clipboard-list', 'tone' => 'assignment'],
            'reference_books' => ['label' => 'Reference Books', 'icon' => 'fa-book', 'tone' => 'reference'],
            'study_guides' => ['label' => 'Study Guides', 'icon' => 'fa-book-open-reader', 'tone' => 'guide'],
            'videos' => ['label' => 'Videos', 'icon' => 'fa-circle-play', 'tone' => 'video'],
            default => ['label' => ucfirst(str_replace('_', ' ', (string) $materialType ?: 'Other')), 'icon' => 'fa-folder-open', 'tone' => 'other'],
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
