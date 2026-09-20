<?php

namespace App\Models;

use App\Support\InstituteDiaryConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteLeaveRule extends Model
{
    protected $fillable = [
        'institute_id',
        'leave_type',
        'allowed_days',
        'applicable_to',
        'is_paid',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'applicable_to' => 'array',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
            'allowed_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function leaveTypeLabel(): string
    {
        return InstituteDiaryConfig::leaveTypes()[$this->leave_type] ?? ucfirst($this->leave_type);
    }

    /** @param  array<string, string>  $audienceLabels */
    public function applicableToLabels(array $audienceLabels): string
    {
        $keys = $this->applicable_to ?? [];

        if ($keys === []) {
            return '—';
        }

        return collect($keys)
            ->map(fn (string $key): string => $audienceLabels[$key] ?? $key)
            ->implode(', ');
    }
}
