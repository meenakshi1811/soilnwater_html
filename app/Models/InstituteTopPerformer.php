<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteTopPerformer extends Model
{
    protected $fillable = [
        'institute_id',
        'student_name',
        'class_name',
        'achievement_title',
        'score',
        'rank',
        'academic_year',
        'photo',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function photoUrl(): ?string
    {
        return filled($this->photo) ? asset($this->photo) : null;
    }
}
