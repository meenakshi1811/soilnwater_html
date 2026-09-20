<?php

namespace App\Models;

use App\Support\InstituteDiaryConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteDiaryHoliday extends Model
{
    protected $table = 'institute_diary_holidays';

    protected $fillable = [
        'institute_id',
        'name',
        'holiday_type',
        'start_date',
        'end_date',
        'description',
        'academic_year',
        'is_recurring',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function holidayTypeLabel(): string
    {
        return InstituteDiaryConfig::holidayTypes()[$this->holiday_type] ?? ucfirst($this->holiday_type);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function durationDays(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 1;
        }

        return max(1, $this->start_date->diffInDays($this->end_date) + 1);
    }
}
