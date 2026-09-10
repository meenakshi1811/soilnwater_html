<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildProfile extends Model
{
    protected $fillable = [
        'parent_user_id',
        'child_user_id',
        'full_name',
        'email',
        'phone_number',
        'gender',
        'class_grade',
        'board',
        'school_name',
        'subjects',
        'profile_image',
        'is_primary',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'subjects' => 'array',
            'is_primary' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function childUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

    public function genderIcon(): string
    {
        return match ($this->gender) {
            'male' => 'fa-mars text-primary',
            'female' => 'fa-venus text-danger',
            default => 'fa-user text-secondary',
        };
    }

    public function displaySubjects(): array
    {
        return is_array($this->subjects) ? array_values(array_filter($this->subjects)) : [];
    }
}
