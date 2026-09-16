<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteAchievement extends Model
{
    protected $fillable = [
        'institute_id',
        'title',
        'description',
        'category',
        'year',
        'image',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function imageUrl(): ?string
    {
        return filled($this->image) ? asset($this->image) : null;
    }
}
