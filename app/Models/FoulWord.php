<?php

namespace App\Models;

use App\Services\FoulWordFilter;
use Illuminate\Database\Eloquent\Model;

class FoulWord extends Model
{
    protected $fillable = [
        'word',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $foulWord): void {
            $foulWord->word = mb_strtolower(trim((string) $foulWord->word));
        });

        static::saved(fn () => FoulWordFilter::forgetCache());
        static::deleted(fn () => FoulWordFilter::forgetCache());
    }
}
