<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A card in the homepage "Study Material Library" section (Admin → Homepage → Study Material Cards).
 */
class HomepageStudyMaterialCard extends Model
{
    public const TONES = ['orange', 'green', 'blue', 'purple', 'red', 'indigo'];

    protected $fillable = [
        'title',
        'subtitle',
        'link_url',
        'image_path',
        'tone',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): string
    {
        $path = trim((string) $this->image_path);
        if ($path === '') {
            return asset('assets/images/vendor-card-placeholder.svg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }

    public function linkUrl(): string
    {
        $link = trim((string) $this->link_url);

        return preg_match('#^(https?:)?//#i', $link) ? $link : url($link);
    }
}
