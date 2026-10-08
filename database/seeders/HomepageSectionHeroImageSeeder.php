<?php

namespace Database\Seeders;

use App\Models\HomepageSectionHeroImage;
use Illuminate\Database\Seeder;

class HomepageSectionHeroImageSeeder extends Seeder
{
    /** Same city skyline used across desktop section heroes. */
    private const CITY_HERO = 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&w=1920&q=80';

    public function run(): void
    {
        $images = [
            'featured-businesses' => 'https://images.unsplash.com/photo-1441986300917-64676bd600d8?auto=format&fit=crop&w=1920&q=80',
            'offers' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1920&q=80',
            'ads' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1920&q=80',
            'popular-near' => self::CITY_HERO,
            'services' => 'https://images.unsplash.com/photo-1581092918056-0e12f16c08ab?auto=format&fit=crop&w=1920&q=80',
            'education' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1920&q=80',
            'study-material' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=1920&q=80',
            'consultants' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=1920&q=80',
            'community' => 'assets/images/hero-banner-community.jpg',
        ];

        foreach ($images as $sectionKey => $imagePath) {
            if (! array_key_exists($sectionKey, HomepageSectionHeroImage::sectionLabels())) {
                continue;
            }

            HomepageSectionHeroImage::query()->updateOrCreate(
                ['section_key' => $sectionKey],
                ['image_path' => $imagePath]
            );
        }
    }
}
