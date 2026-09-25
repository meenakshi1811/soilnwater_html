<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\InstituteJob;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class InstituteJobSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('institute_jobs')) {
            $this->command?->warn('Skipping InstituteJobSeeder: run migrations first (institute_jobs table missing).');
            $this->command?->line('  php artisan migrate --force');

            return;
        }

        $definitions = [
            'green-valley-international-school-demo' => [
                [
                    'title' => 'PGT Mathematics',
                    'department' => 'Senior Secondary',
                    'employment_type' => 'full-time',
                    'location' => 'On campus · New Delhi',
                    'salary_label' => 'As per school norms',
                    'experience_label' => '3+ years teaching CBSE',
                    'description' => "We are looking for an experienced Post Graduate Teacher in Mathematics to lead Classes XI–XII.\n\nYou will plan lessons aligned with CBSE outcomes, mentor students for board exams, and collaborate with the academic team on assessments.",
                    'requirements' => "• M.Sc / M.A in Mathematics with B.Ed\n• Strong classroom communication\n• Comfortable with smart-board teaching tools",
                    'application_deadline' => now()->addMonths(2)->toDateString(),
                ],
                [
                    'title' => 'Primary English Teacher',
                    'department' => 'Primary Wing',
                    'employment_type' => 'full-time',
                    'location' => 'On campus',
                    'salary_label' => '₹28,000 – ₹38,000/month',
                    'experience_label' => '2+ years',
                    'description' => 'Join our primary wing to build strong language foundations through activity-based learning and reading programmes.',
                    'requirements' => "• Graduate with B.Ed\n• Experience with Classes III–V\n• Passion for child-centred pedagogy",
                    'application_deadline' => now()->addMonth()->toDateString(),
                ],
                [
                    'title' => 'School Counsellor',
                    'department' => 'Student Wellness',
                    'employment_type' => 'part-time',
                    'location' => 'Hybrid',
                    'salary_label' => 'Negotiable',
                    'experience_label' => '1+ year in schools',
                    'description' => 'Support students with guidance sessions, parent interactions, and wellness workshops across middle and senior grades.',
                    'requirements' => "• Degree in Psychology / Counselling\n• Empathetic communication skills",
                    'application_deadline' => null,
                ],
            ],
            'excel-academy-coaching-demo' => [
                [
                    'title' => 'JEE Physics Faculty',
                    'department' => 'Science Stream',
                    'employment_type' => 'full-time',
                    'location' => 'Coaching centre · Gurugram',
                    'salary_label' => 'Competitive + performance bonus',
                    'experience_label' => '4+ years JEE coaching',
                    'description' => 'Deliver concept-focused Physics sessions for JEE Main & Advanced batches and contribute to test-paper design.',
                    'requirements' => "• M.Sc Physics or equivalent\n• Proven results in competitive exam coaching",
                    'application_deadline' => now()->addMonths(3)->toDateString(),
                ],
                [
                    'title' => 'Front Desk & Admissions Executive',
                    'department' => 'Operations',
                    'employment_type' => 'full-time',
                    'location' => 'Front office',
                    'salary_label' => '₹18,000 – ₹24,000/month',
                    'experience_label' => 'Freshers welcome',
                    'description' => 'Handle walk-in enquiries, coordinate demo classes, and maintain student admission records.',
                    'requirements' => "• Good spoken English & Hindi\n• Basic MS Office skills",
                    'application_deadline' => now()->addWeeks(6)->toDateString(),
                ],
            ],
        ];

        foreach ($definitions as $slug => $jobs) {
            $institute = Institute::query()->where('slug', $slug)->first();
            if (! $institute) {
                continue;
            }

            foreach ($jobs as $index => $jobData) {
                InstituteJob::query()->updateOrCreate(
                    [
                        'institute_id' => $institute->id,
                        'title' => $jobData['title'],
                    ],
                    [
                        ...$jobData,
                        'status' => InstituteJob::STATUS_OPEN,
                        'published_at' => now()->subDays($index),
                    ]
                );
            }
        }

        $this->command?->info('Institute job openings seeded.');
    }
}
