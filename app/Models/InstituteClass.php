<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteClass extends Model
{
    protected $fillable = [
        'institute_id',
        'name',
        'section',
        'class_teacher',
        'strength',
        'room',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'strength' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function displayLabel(): string
    {
        return trim($this->name.($this->section ? ' · '.$this->section : ''));
    }
}
