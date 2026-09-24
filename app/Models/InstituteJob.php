<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstituteJob extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    /** @var list<string> */
    public const EMPLOYMENT_TYPES = ['full-time', 'part-time', 'contract', 'internship'];

    protected $fillable = [
        'institute_id',
        'title',
        'department',
        'employment_type',
        'location',
        'salary_label',
        'experience_label',
        'description',
        'requirements',
        'application_deadline',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'application_deadline' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(InstituteJobApplication::class)->latest();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_OPEN)
            ->where(function (Builder $inner): void {
                $inner->whereNull('application_deadline')
                    ->orWhereDate('application_deadline', '>=', now()->toDateString());
            });
    }

    public function isAcceptingApplications(): bool
    {
        if ($this->status !== self::STATUS_OPEN) {
            return false;
        }

        if ($this->application_deadline && $this->application_deadline->isPast()) {
            return false;
        }

        return true;
    }

    public function employmentTypeLabel(): string
    {
        return match ($this->employment_type) {
            'part-time' => 'Part-time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            default => 'Full-time',
        };
    }

    /** @return array<string, mixed> */
    public function toPublicPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'department' => $this->department,
            'employment_type' => $this->employment_type,
            'employment_type_label' => $this->employmentTypeLabel(),
            'location' => $this->location,
            'salary_label' => $this->salary_label,
            'experience_label' => $this->experience_label,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'application_deadline' => $this->application_deadline?->format('M j, Y'),
            'published_at' => $this->published_at?->format('M j, Y') ?? $this->created_at?->format('M j, Y'),
            'is_open' => $this->isAcceptingApplications(),
        ];
    }
}
