<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\InstituteDiaryHoliday;
use App\Models\InstituteLeaveRule;
use App\Support\InstituteDiaryConfig;
use Illuminate\Database\Seeder;

class InstituteDiaryDemoSeeder extends Seeder
{
    public function run(): void
    {
        $targets = [
            'green-valley-international-school-demo' => 'school',
            'excel-academy-coaching-demo' => 'institute',
        ];

        $seeded = 0;

        foreach ($targets as $slug => $profileType) {
            $institute = Institute::query()->where('slug', $slug)->first();
            if (! $institute) {
                $this->command?->warn("Skipping diary demo: institute slug [{$slug}] not found. Run InstituteDemoSeeder first.");

                continue;
            }

            $this->seedHolidays($institute);
            $this->seedLeaveRules($institute, $profileType);
            $seeded++;
        }

        if ($seeded === 0) {
            $this->command?->warn('No diary demo data seeded.');

            return;
        }

        $this->command?->info("Institute diary demo data seeded for {$seeded} profile(s).");
    }

    private function seedHolidays(Institute $institute): void
    {
        $academicYear = InstituteDiaryConfig::defaultAcademicYear();
        $yearStart = (int) explode('-', $academicYear)[0];

        $holidays = [
            [
                'name' => 'Republic Day',
                'holiday_type' => 'national',
                'start_date' => sprintf('%d-01-26', $yearStart + 1),
                'end_date' => sprintf('%d-01-26', $yearStart + 1),
                'description' => 'National holiday — campus closed.',
                'is_recurring' => true,
            ],
            [
                'name' => 'Holi',
                'holiday_type' => 'religious',
                'start_date' => sprintf('%d-03-14', $yearStart + 1),
                'end_date' => sprintf('%d-03-15', $yearStart + 1),
                'description' => 'Festival break for students and staff.',
                'is_recurring' => true,
            ],
            [
                'name' => 'Summer vacation',
                'holiday_type' => 'academic',
                'start_date' => sprintf('%d-05-01', $yearStart + 1),
                'end_date' => sprintf('%d-06-15', $yearStart + 1),
                'description' => 'Regular summer break. Admin office open on select days.',
                'is_recurring' => false,
            ],
            [
                'name' => 'Independence Day',
                'holiday_type' => 'national',
                'start_date' => sprintf('%d-08-15', $yearStart + 1),
                'end_date' => sprintf('%d-08-15', $yearStart + 1),
                'description' => 'Flag ceremony in the morning; no regular classes.',
                'is_recurring' => true,
            ],
            [
                'name' => 'Gandhi Jayanti',
                'holiday_type' => 'national',
                'start_date' => sprintf('%d-10-02', $yearStart + 1),
                'end_date' => sprintf('%d-10-02', $yearStart + 1),
                'description' => 'National holiday.',
                'is_recurring' => true,
            ],
            [
                'name' => 'Diwali break',
                'holiday_type' => 'religious',
                'start_date' => sprintf('%d-10-20', $yearStart + 1),
                'end_date' => sprintf('%d-10-25', $yearStart + 1),
                'description' => 'Festival holidays — dates adjusted each year by academic calendar.',
                'is_recurring' => false,
            ],
            [
                'name' => 'Winter break',
                'holiday_type' => 'academic',
                'start_date' => sprintf('%d-12-25', $yearStart),
                'end_date' => sprintf('%d-01-01', $yearStart + 1),
                'description' => 'Year-end break spanning Christmas and New Year.',
                'is_recurring' => false,
            ],
        ];

        foreach ($holidays as $holiday) {
            InstituteDiaryHoliday::query()->updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'name' => $holiday['name'],
                    'academic_year' => $academicYear,
                ],
                array_merge($holiday, [
                    'academic_year' => $academicYear,
                    'is_active' => true,
                ])
            );
        }
    }

    private function seedLeaveRules(Institute $institute, string $profileType): void
    {
        $teacherKey = $profileType === 'school' ? 'teachers' : 'faculty';

        $rules = [
            [
                'leave_type' => 'casual',
                'allowed_days' => 12,
                'applicable_to' => [$teacherKey, 'staff'],
                'is_paid' => true,
                'description' => 'Short personal leave; apply at least one day in advance when possible.',
                'sort_order' => 1,
            ],
            [
                'leave_type' => 'sick',
                'allowed_days' => 10,
                'applicable_to' => ['students', $teacherKey, 'staff'],
                'is_paid' => true,
                'description' => 'Medical certificate required for absences longer than three consecutive days.',
                'sort_order' => 2,
            ],
            [
                'leave_type' => 'earned',
                'allowed_days' => 15,
                'applicable_to' => [$teacherKey, 'staff'],
                'is_paid' => true,
                'description' => 'Accrued annually; maximum five days may be carried forward.',
                'sort_order' => 3,
            ],
            [
                'leave_type' => 'academic',
                'allowed_days' => 5,
                'applicable_to' => ['students'],
                'is_paid' => true,
                'description' => 'Competition, olympiad, or approved educational travel.',
                'sort_order' => 4,
            ],
            [
                'leave_type' => 'emergency',
                'allowed_days' => 3,
                'applicable_to' => ['students', $teacherKey, 'staff'],
                'is_paid' => false,
                'description' => 'Unplanned family or medical emergency; inform the office within 24 hours.',
                'sort_order' => 5,
            ],
        ];

        foreach ($rules as $rule) {
            InstituteLeaveRule::query()->updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'leave_type' => $rule['leave_type'],
                ],
                array_merge($rule, ['is_active' => true])
            );
        }
    }
}
