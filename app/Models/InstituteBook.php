<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteBook extends Model
{
    protected $fillable = [
        'institute_id',
        'title',
        'author',
        'class_name',
        'subject',
        'publisher',
        'isbn',
        'cover_image',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function coverUrl(): ?string
    {
        return filled($this->cover_image) ? asset($this->cover_image) : null;
    }
}
