<?php

namespace Database\Seeders;

use App\Models\Educator;
use App\Models\EducatorInstituteAffiliation;
use App\Models\Institute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EducatorInstituteAffiliationSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('educator_institute_affiliations')) {
            $this->command?->warn('Skipping EducatorInstituteAffiliationSeeder: run migrations first (educator_institute_affiliations table missing).');
            $this->command?->line('  php artisan migrate --force');

            return;
        }

        $educator = Educator::query()->where('slug', 'ananya-sharma-demo')->first();
        $school = Institute::query()->where('slug', 'green-valley-international-school-demo')->first();
        $coaching = Institute::query()->where('slug', 'excel-academy-coaching-demo')->first();

        if ($educator && $school) {
            EducatorInstituteAffiliation::query()->updateOrCreate(
                [
                    'educator_id' => $educator->id,
                    'institute_id' => $school->id,
                ],
                [
                    'role_title' => 'Senior Physics Teacher',
                    'subject' => 'Physics & Mathematics',
                    'sort_order' => 0,
                ]
            );
        }

        if ($educator && $coaching) {
            EducatorInstituteAffiliation::query()->updateOrCreate(
                [
                    'educator_id' => $educator->id,
                    'institute_id' => $coaching->id,
                ],
                [
                    'role_title' => 'Guest Faculty · JEE Physics',
                    'subject' => 'Physics',
                    'sort_order' => 1,
                ]
            );
        }

        $this->command?->info('Educator ↔ institute affiliations seeded.');
    }
}
