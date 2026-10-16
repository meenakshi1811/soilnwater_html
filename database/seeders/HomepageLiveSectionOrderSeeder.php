<?php

namespace Database\Seeders;

use App\Models\HomepageSetting;
use App\Support\HomepageSectionOrder;
use Illuminate\Database\Seeder;

/**
 * The homepage section order and visibility as on the live homepage (October 2026).
 * Only the eight panel sections are touched; other Page Setting switches keep their values.
 */
class HomepageLiveSectionOrderSeeder extends Seeder
{
    public function run(): void
    {
        $order = [
            'popular_near_you',
            'study_material',
            'popular_services',
            'education_knowledge',
            'consultants',
            'offers',
            'ads',
            'community_hub',
        ];

        $setting = HomepageSetting::query()->firstOrCreate(['id' => 1]);

        // All eight are shown on live: switch on the Page Setting toggle behind each of them
        $toggles = $setting->section_toggles ?? [];
        foreach (HomepageSectionOrder::sections() as $section) {
            $toggles[$section['toggle']] = true;
        }

        $setting->section_toggles = $toggles;
        $setting->section_order = HomepageSectionOrder::keys($order);
        $setting->save();
    }
}
