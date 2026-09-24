<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteJobApplication extends Model
{
    /** @var list<string> */
    public const STATUSES = ['pending', 'reviewed', 'shortlisted', 'rejected', 'accepted'];

    protected $fillable = [
        'institute_job_id',
        'user_id',
        'cover_message',
        'status',
        'institute_note',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(InstituteJob::class, 'institute_job_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'reviewed' => 'Under review',
            'shortlisted' => 'Shortlisted',
            'rejected' => 'Not selected',
            'accepted' => 'Accepted',
            default => 'Pending',
        };
    }
}
