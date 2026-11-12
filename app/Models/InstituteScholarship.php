<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A scholarship a school, college or institute offers: what it gives, who can get it (the
 * criteria), the documents needed, how and by when to apply.
 */
class InstituteScholarship extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    /** Kinds of scholarship: key => [label, icon] */
    public const TYPES = [
        'merit' => ['Merit-based', 'fa-medal'],
        'need' => ['Need-based', 'fa-hand-holding-heart'],
        'sports' => ['Sports', 'fa-futbol'],
        'girls' => ['For girls', 'fa-person-dress'],
        'reserved' => ['SC / ST / OBC', 'fa-scale-balanced'],
        'minority' => ['Minority', 'fa-people-group'],
        'talent' => ['Arts & talent', 'fa-palette'],
        'fee-waiver' => ['Fee waiver', 'fa-percent'],
        'other' => ['Other', 'fa-award'],
    ];

    protected $fillable = [
        'institute_id',
        'title',
        'scholarship_type',
        'amount_label',
        'awards_count',
        'eligible_for',
        'min_marks',
        'income_limit',
        'description',
        'eligibility',
        'documents',
        'how_to_apply',
        'apply_url',
        'application_start',
        'application_deadline',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'awards_count' => 'integer',
            'application_start' => 'date',
            'application_deadline' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    /** Open, and its last date (if any) not passed */
    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_OPEN)
            ->where(function (Builder $inner): void {
                $inner->whereNull('application_deadline')
                    ->orWhereDate('application_deadline', '>=', now()->toDateString());
            });
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN
            && ! ($this->application_deadline && $this->application_deadline->endOfDay()->isPast());
    }

    /** Applications start later (a start date still to come) */
    public function opensLater(): bool
    {
        return $this->application_start !== null && $this->application_start->isFuture();
    }

    public function typeLabel(): string
    {
        return (self::TYPES[$this->scholarship_type] ?? self::TYPES['other'])[0];
    }

    public function typeIcon(): string
    {
        return (self::TYPES[$this->scholarship_type] ?? self::TYPES['other'])[1];
    }

    /**
     * A text field written one point per line (criteria, documents), as a list; bullets or
     * numbers someone typed at the start of a line are dropped.
     *
     * @return list<string>
     */
    public function lines(string $field): array
    {
        return array_values(array_filter(array_map(
            fn (string $line) => trim(preg_replace('/^\s*(?:[-*•·]|\d+[.)])\s*/u', '', $line) ?? ''),
            preg_split('/\R/u', (string) $this->getAttribute($field)) ?: []
        ), fn (string $line) => $line !== ''));
    }

    /** The external application link, when it is a web address */
    public function applyUrl(): ?string
    {
        $url = trim((string) $this->apply_url);

        return preg_match('#^https?://#i', $url) === 1 ? $url : null;
    }

    /** What the form needs to edit it (the dashboard's Edit button) */
    public function toFormPayload(): array
    {
        return [
            'title' => $this->title,
            'scholarship_type' => $this->scholarship_type,
            'amount_label' => $this->amount_label,
            'awards_count' => $this->awards_count,
            'eligible_for' => $this->eligible_for,
            'min_marks' => $this->min_marks,
            'income_limit' => $this->income_limit,
            'description' => $this->description,
            'eligibility' => $this->eligibility,
            'documents' => $this->documents,
            'how_to_apply' => $this->how_to_apply,
            'apply_url' => $this->apply_url,
            'application_start' => $this->application_start?->toDateString(),
            'application_deadline' => $this->application_deadline?->toDateString(),
            'status' => $this->status,
        ];
    }
}
