<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSectionHeroImage extends Model
{
    protected $fillable = [
        'section_key',
        'image_path',
    ];

    public function imageUrl(): string
    {
        $path = trim((string) $this->image_path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }

    /**
     * @return array<string, string>
     */
    public static function sectionLabels(): array
    {
        return [
            'featured-businesses' => 'Featured Businesses',
            'offers' => 'Latest Offers & Discounts',
            'ads' => 'Latest Ads & Listings',
            'popular-near' => 'Popular Near You',
            'services' => 'Popular Services',
            'education' => 'Education & Knowledge',
            'study-material' => 'Study Material Library',
            'consultants' => 'Consultants & Professionals',
            'community' => 'SoilnWater Community',
        ];
    }

    /**
     * @return list<string>
     */
    public static function sectionKeys(): array
    {
        return array_keys(self::sectionLabels());
    }
}
