<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\InstituteAchievement;
use App\Models\InstituteBook;
use App\Models\InstituteClass;
use App\Models\InstituteEnquiry;
use App\Models\InstituteNotice;
use App\Models\InstituteTopPerformer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstituteDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Institute@123';

        $definitions = [
            $this->schoolDefinition(),
            $this->coachingDefinition(),
        ];

        foreach ($definitions as $definition) {
            $this->seedInstitute($definition, $password);
        }

        $this->command?->info('Institute demo data seeded successfully.');
        $this->command?->newLine();
        $this->command?->table(
            ['Field', 'Value'],
            [
                ['School login', 'school.demo@soilnwater.test / '.$password],
                ['School public page', url('/schools/green-valley-international-school-demo')],
                ['School portal', url('/school/dashboard')],
                ['Coaching login', 'coaching.demo@soilnwater.test / '.$password],
                ['Coaching public page', url('/institutes/excel-academy-coaching-demo')],
                ['Institute portal', url('/institute/dashboard')],
            ]
        );
    }

    private function seedInstitute(array $definition, string $password): Institute
    {
        $userData = $definition['user'];
        $instituteData = $definition['institute'];
        $portalRole = $userData['portal_role'] ?? 'institute';
        unset($userData['portal_role']);

        $user = User::query()->updateOrCreate(
            ['email' => $userData['email']],
            array_merge($userData, [
                'password' => Hash::make($password),
                'role' => $portalRole,
                'is_active' => true,
                'is_blocked' => false,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ])
        );

        $logoPath = $this->copyDemoAsset(
            $instituteData['logo_source'] ?? 'assets/images/soilandwater_logo.png',
            'logos',
            $instituteData['slug'].'-logo'
        );

        $gallery = collect($instituteData['gallery_sources'] ?? [])
            ->map(fn (string $source, int $index) => $this->copyDemoAsset(
                $source,
                'gallery',
                $instituteData['slug'].'-gallery-'.$index
            ))
            ->filter()
            ->values()
            ->all();

        if ($logoPath) {
            $gallery = array_values(array_unique(array_merge([$logoPath], $gallery)));
        }

        $instituteAttributes = collect($instituteData)
            ->except(['logo_source', 'gallery_sources'])
            ->merge([
                'contact_person' => $user->name,
                'display_name' => $instituteData['institution_name'],
                'logo' => $logoPath,
                'phone' => $user->phone_number,
                'whatsapp' => $user->whatsapp_number,
                'email' => $user->email,
                'gallery' => $gallery ?: null,
                'status' => 'approved',
                'is_verified' => true,
                'approved_at' => now()->subMonths(2),
                'approved_by' => null,
            ])
            ->all();

        $institute = Institute::query()->updateOrCreate(
            ['user_id' => $user->id],
            $instituteAttributes
        );

        $this->seedNotices($institute, $definition['notices'] ?? []);
        $this->seedClasses($institute, $definition['classes'] ?? []);
        $this->seedPerformers($institute, $definition['performers'] ?? []);
        $this->seedAchievements($institute, $definition['achievements'] ?? []);
        $this->seedBooks($institute, $definition['books'] ?? []);
        $this->seedEnquiries($institute, $definition['enquiries'] ?? []);

        return $institute;
    }

    private function seedNotices(Institute $institute, array $notices): void
    {
        foreach ($notices as $notice) {
            InstituteNotice::query()->updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'title' => $notice['title'],
                ],
                [
                    'message' => $notice['message'],
                    'expires_at' => $notice['expires_at'],
                ]
            );
        }
    }

    private function seedClasses(Institute $institute, array $classes): void
    {
        foreach ($classes as $index => $class) {
            InstituteClass::query()->updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'name' => $class['name'],
                    'section' => $class['section'] ?? null,
                ],
                array_merge($class, ['sort_order' => $index])
            );
        }
    }

    private function seedPerformers(Institute $institute, array $performers): void
    {
        foreach ($performers as $index => $performer) {
            InstituteTopPerformer::query()->updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'student_name' => $performer['student_name'],
                    'achievement_title' => $performer['achievement_title'],
                ],
                array_merge($performer, ['sort_order' => $index])
            );
        }
    }

    private function seedAchievements(Institute $institute, array $achievements): void
    {
        foreach ($achievements as $index => $achievement) {
            $imagePath = null;
            if (! empty($achievement['image_source'])) {
                $imagePath = $this->copyDemoAsset(
                    $achievement['image_source'],
                    'achievements',
                    $institute->slug.'-achievement-'.$index
                );
            }

            InstituteAchievement::query()->updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'title' => $achievement['title'],
                ],
                [
                    'description' => $achievement['description'] ?? null,
                    'category' => $achievement['category'] ?? null,
                    'year' => $achievement['year'] ?? null,
                    'image' => $imagePath,
                    'sort_order' => $index,
                ]
            );
        }
    }

    private function seedBooks(Institute $institute, array $books): void
    {
        foreach ($books as $index => $book) {
            InstituteBook::query()->updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'title' => $book['title'],
                    'author' => $book['author'],
                ],
                array_merge($book, ['sort_order' => $index])
            );
        }
    }

    private function seedEnquiries(Institute $institute, array $enquiries): void
    {
        foreach ($enquiries as $enquiry) {
            $student = null;
            if (! empty($enquiry['student_email'])) {
                $student = User::query()->where('email', $enquiry['student_email'])->first();
            }

            InstituteEnquiry::query()->updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'email' => $enquiry['email'] ?? $student?->email,
                    'subject' => $enquiry['subject'],
                ],
                [
                    'user_id' => $student?->id,
                    'name' => $enquiry['name'] ?? $student?->name ?? 'Demo Parent',
                    'phone' => $enquiry['phone'] ?? $student?->phone_number,
                    'message' => $enquiry['message'],
                    'status' => $enquiry['status'] ?? 'new',
                ]
            );
        }
    }

    private function copyDemoAsset(string $sourceRelativePath, string $folder, string $basename): ?string
    {
        $source = public_path($sourceRelativePath);
        if (! File::exists($source)) {
            return str_starts_with($sourceRelativePath, 'uploads/') || str_starts_with($sourceRelativePath, 'assets/')
                ? $sourceRelativePath
                : null;
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION) ?: 'jpg';
        $directory = public_path('uploads/institutes/'.$folder);
        File::ensureDirectoryExists($directory);

        $filename = Str::slug($basename).'.'.$extension;
        $destination = $directory.DIRECTORY_SEPARATOR.$filename;

        if (! File::exists($destination)) {
            File::copy($source, $destination);
        }

        return 'uploads/institutes/'.$folder.'/'.$filename;
    }

    private function schoolDefinition(): array
    {
        return [
            'user' => [
                'portal_role' => 'school',
                'name' => 'Dr. Priya Mehta',
                'full_name' => 'Dr. Priya Mehta',
                'email' => 'school.demo@soilnwater.test',
                'phone_number' => '9876500101',
                'whatsapp_number' => '9876500101',
                'address' => 'Green Valley Campus, Vesu-Bhimrad Road',
                'city' => 'Surat',
                'pincode' => '395007',
                'latitude' => 21.1415000,
                'longitude' => 72.7759000,
            ],
            'institute' => [
                'institution_name' => 'Green Valley International School',
                'slug' => 'green-valley-international-school-demo',
                'institution_type' => 'school',
                'board_affiliation' => 'CBSE',
                'tagline' => 'Nurturing curious minds with values, innovation, and excellence since 1998.',
                'about' => "Green Valley International School is a CBSE-affiliated co-educational institution committed to holistic development from Nursery to Class 12.\n\nOur campus blends academic rigour with sports, arts, and community service. We focus on STEM labs, experiential learning, and character building so every child grows into a confident, compassionate learner.\n\nAdmissions for 2026–27 are open for select grades. Visit our campus or send an enquiry to schedule a guided tour.",
                'grades_offered' => ['Nursery', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12'],
                'facilities' => [
                    'Smart classrooms',
                    'Science & robotics lab',
                    'Library & reading room',
                    'Sports complex',
                    'Swimming pool',
                    'Music & art studio',
                    'Counselling centre',
                    'Transport facility',
                    'CCTV campus',
                    'Medical room',
                ],
                'pan_number' => 'AAACG1234A',
                'gst_number' => '24AAACG1234A1Z5',
                'government_certificate_number' => 'GJ-SCH-1998-04521',
                'date_of_establishment' => '1998-06-15',
                'website_url' => 'https://example.com/green-valley-school',
                'facebook_url' => 'https://facebook.com/soilnwater',
                'instagram_url' => 'https://instagram.com/soilnwater',
                'youtube_url' => 'https://youtube.com/@soilnwater',
                'address' => 'Green Valley Campus, Vesu-Bhimrad Road',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '395007',
                'latitude' => 21.1415000,
                'longitude' => 72.7759000,
                'logo_source' => 'assets/images/soilandwater_logo.png',
                'gallery_sources' => [
                    'uploads/service_providers/sections/content-images/011b7096-09cf-4ce4-9d72-4b50ed79f3d4.jpg',
                    'uploads/service_providers/sections/content-images/29486551-05c7-43af-ae6a-dad74b5499c0.jpg',
                    'uploads/vendors/sections/content-images/c766182c-e1df-4101-8361-0e03338c917f.png',
                ],
            ],
            'notices' => [
                [
                    'title' => 'Admission open 2026–27',
                    'message' => 'Applications are open for Nursery to Class 8. Limited seats available. Submit enquiry online or visit the admission office between 9:00 AM and 2:00 PM on working days.',
                    'expires_at' => now()->addDays(60)->toDateString(),
                ],
                [
                    'title' => 'Annual day celebration',
                    'message' => 'Our annual cultural programme will be held on 28 November at the school auditorium. Parents of participating students will receive passes from class teachers by 20 November.',
                    'expires_at' => now()->addDays(45)->toDateString(),
                ],
                [
                    'title' => 'PTM schedule – Term 1',
                    'message' => 'Parent–teacher meetings for Classes 6–12 are scheduled on 12 and 13 October from 8:30 AM to 12:30 PM. Please carry the student diary and term report card.',
                    'expires_at' => now()->addDays(21)->toDateString(),
                ],
            ],
            'classes' => [
                ['name' => 'Class 10', 'section' => 'A', 'class_teacher' => 'Mrs. Anjali Shah', 'strength' => 38, 'room' => '301', 'description' => 'Science stream foundation batch with board exam focus.'],
                ['name' => 'Class 10', 'section' => 'B', 'class_teacher' => 'Mr. Rajesh Kumar', 'strength' => 36, 'room' => '302', 'description' => 'Balanced academic programme with weekly assessments.'],
                ['name' => 'Class 12', 'section' => 'A', 'class_teacher' => 'Dr. Neha Desai', 'strength' => 32, 'room' => '401', 'description' => 'PCM group with JEE foundation support.'],
                ['name' => 'Class 8', 'section' => 'C', 'class_teacher' => 'Ms. Kiran Patel', 'strength' => 40, 'room' => '208', 'description' => 'Activity-based learning with STEM projects.'],
            ],
            'performers' => [
                [
                    'student_name' => 'Aarav Shah',
                    'class_name' => 'Class 12',
                    'achievement_title' => 'CBSE Board Exam – School Topper',
                    'score' => '97.8%',
                    'rank' => 1,
                    'academic_year' => '2024-25',
                ],
                [
                    'student_name' => 'Isha Desai',
                    'class_name' => 'Class 10',
                    'achievement_title' => 'National Science Olympiad – Gold Medal',
                    'score' => 'State Rank 2',
                    'rank' => 2,
                    'academic_year' => '2024-25',
                ],
                [
                    'student_name' => 'Vihaan Mehta',
                    'class_name' => 'Class 8',
                    'achievement_title' => 'Inter-school Debate Championship Winner',
                    'score' => 'Best Speaker',
                    'rank' => 1,
                    'academic_year' => '2025-26',
                ],
            ],
            'achievements' => [
                [
                    'title' => '100% Class 10 board pass rate',
                    'description' => 'All Class 10 students cleared CBSE board exams with 86% scoring above 75%.',
                    'category' => 'Academics',
                    'year' => 2025,
                    'image_source' => 'uploads/service_providers/sections/content-images/e4a2f206-3378-48a2-9590-64c663206f85.jpg',
                ],
                [
                    'title' => 'State-level cricket championship',
                    'description' => 'Under-17 school team won the Gujarat inter-school cricket tournament.',
                    'category' => 'Sports',
                    'year' => 2024,
                    'image_source' => 'uploads/vendors/sections/content-images/4bbb681a-7c55-44dc-b306-7857ecf3746e.jpg',
                ],
                [
                    'title' => 'Green Campus Award',
                    'description' => 'Recognised for rainwater harvesting, solar power, and eco-club initiatives.',
                    'category' => 'Environment',
                    'year' => 2023,
                ],
            ],
            'books' => [
                ['title' => 'Mathematics – Class 10', 'author' => 'R.D. Sharma', 'class_name' => 'Class 10', 'subject' => 'Mathematics', 'publisher' => 'Dhanpat Rai Publications'],
                ['title' => 'Science – Class 10', 'author' => 'Lakhmir Singh & Manjit Kaur', 'class_name' => 'Class 10', 'subject' => 'Science', 'publisher' => 'S. Chand'],
                ['title' => 'First Flight (English)', 'author' => 'NCERT', 'class_name' => 'Class 10', 'subject' => 'English', 'publisher' => 'NCERT'],
                ['title' => 'Physics Part I – Class 12', 'author' => 'NCERT', 'class_name' => 'Class 12', 'subject' => 'Physics', 'publisher' => 'NCERT'],
                ['title' => 'Chemistry Part I – Class 12', 'author' => 'NCERT', 'class_name' => 'Class 12', 'subject' => 'Chemistry', 'publisher' => 'NCERT'],
                ['title' => 'Mathematics – Class 12', 'author' => 'NCERT', 'class_name' => 'Class 12', 'subject' => 'Mathematics', 'publisher' => 'NCERT'],
            ],
            'enquiries' => [
                [
                    'student_email' => 'student1.demo@soilnwater.test',
                    'subject' => 'Admission enquiry for Class 6',
                    'message' => 'Hello, we are looking for admission for our daughter in Class 6 for the 2026–27 session. Please share the fee structure and document checklist.',
                    'status' => 'new',
                ],
                [
                    'student_email' => 'student2.demo@soilnwater.test',
                    'subject' => 'Transport route information',
                    'message' => 'Could you please confirm if school transport is available for Adajan area?',
                    'status' => 'read',
                ],
            ],
        ];
    }

    private function coachingDefinition(): array
    {
        return [
            'user' => [
                'portal_role' => 'institute',
                'name' => 'Prof. Vikram Singh',
                'full_name' => 'Prof. Vikram Singh',
                'email' => 'coaching.demo@soilnwater.test',
                'phone_number' => '9876500202',
                'whatsapp_number' => '9876500202',
                'address' => 'Excel Academy Tower, Adajan Main Road',
                'city' => 'Surat',
                'pincode' => '395009',
                'latitude' => 21.1959000,
                'longitude' => 72.7935000,
            ],
            'institute' => [
                'institution_name' => 'Excel Academy Coaching Institute',
                'slug' => 'excel-academy-coaching-demo',
                'institution_type' => 'coaching',
                'board_affiliation' => 'CBSE · GSEB · JEE · NEET',
                'tagline' => 'Result-oriented coaching for board exams, JEE, and NEET with expert faculty.',
                'about' => "Excel Academy is a leading coaching institute in Surat offering structured programmes for Classes 9–12, JEE Main/Advanced, and NEET.\n\nWe run small batches, weekly tests, doubt sessions, and digital study material. Our faculty includes IIT/NIT alumni and experienced board examiners.\n\nNew batches for 2025–26 start every month. Book a free counselling session through the enquiry form.",
                'grades_offered' => ['Class 9', 'Class 10', 'Class 11', 'Class 12', 'JEE Dropper', 'NEET Dropper'],
                'facilities' => [
                    'Air-conditioned classrooms',
                    'Digital boards',
                    'Library & study room',
                    'Weekly test series',
                    'Doubt clearing desk',
                    'Online backup classes',
                    'Performance analytics',
                    'Parent progress app',
                ],
                'pan_number' => 'AAACE5678B',
                'gst_number' => '24AAACE5678B1Z8',
                'government_certificate_number' => 'GJ-CCH-2010-11209',
                'date_of_establishment' => '2010-04-01',
                'website_url' => 'https://example.com/excel-academy',
                'facebook_url' => 'https://facebook.com/soilnwater',
                'instagram_url' => 'https://instagram.com/soilnwater',
                'address' => 'Excel Academy Tower, Adajan Main Road',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '395009',
                'latitude' => 21.1959000,
                'longitude' => 72.7935000,
                'logo_source' => 'assets/images/soilandwater_logo.png',
                'gallery_sources' => [
                    'uploads/service_providers/sections/content-images/bd464841-1f56-49c2-a7e2-33218b5b9e37.jpg',
                    'uploads/vendors/sections/content-images/d5000b15-302a-4df2-8f77-b0e0086a558d.jpg',
                ],
            ],
            'notices' => [
                [
                    'title' => 'JEE 2026 batch registration',
                    'message' => 'New JEE Main + Advanced integrated batch starts 5 October. Early-bird fee discount available till 25 September.',
                    'expires_at' => now()->addDays(30)->toDateString(),
                ],
                [
                    'title' => 'NEET mock test series',
                    'message' => 'Full-length NEET mock tests every Sunday at 10:00 AM. All enrolled NEET batches must register with the front desk.',
                    'expires_at' => now()->addDays(40)->toDateString(),
                ],
            ],
            'classes' => [
                ['name' => 'JEE Main Batch', 'section' => 'A', 'class_teacher' => 'Prof. Vikram Singh', 'strength' => 25, 'room' => '501', 'description' => 'Physics, Chemistry, Maths – 6 days/week.'],
                ['name' => 'NEET Batch', 'section' => 'B', 'class_teacher' => 'Dr. Sunita Rao', 'strength' => 28, 'room' => '502', 'description' => 'Biology-focused programme with daily tests.'],
                ['name' => 'Class 12 Board', 'section' => 'C', 'class_teacher' => 'Mr. Amit Joshi', 'strength' => 30, 'room' => '401', 'description' => 'CBSE board crash course – Jan to Mar.'],
            ],
            'performers' => [
                [
                    'student_name' => 'Rohan Patel',
                    'class_name' => 'JEE Batch',
                    'achievement_title' => 'JEE Main – 99.2 percentile',
                    'score' => 'AIR 1840',
                    'rank' => 1,
                    'academic_year' => '2024-25',
                ],
                [
                    'student_name' => 'Meera Desai',
                    'class_name' => 'NEET Batch',
                    'achievement_title' => 'NEET – State Rank 45',
                    'score' => '685/720',
                    'rank' => 2,
                    'academic_year' => '2024-25',
                ],
            ],
            'achievements' => [
                [
                    'title' => '42 JEE Main selections in 2025',
                    'description' => 'Highest number of 95+ percentile scorers in Surat zone from a single coaching centre.',
                    'category' => 'Academics',
                    'year' => 2025,
                ],
                [
                    'title' => 'Best Coaching Institute Award',
                    'description' => 'Awarded by Gujarat Education Summit for consistent NEET results.',
                    'category' => 'Recognition',
                    'year' => 2024,
                    'image_source' => 'uploads/service_providers/sections/content-images/011b7096-09cf-4ce4-9d72-4b50ed79f3d4.jpg',
                ],
            ],
            'books' => [
                ['title' => 'Concepts of Physics Vol. 1', 'author' => 'H.C. Verma', 'class_name' => 'JEE Batch', 'subject' => 'Physics', 'publisher' => 'Bharati Bhawan'],
                ['title' => 'Organic Chemistry', 'author' => 'Morrison & Boyd', 'class_name' => 'JEE Batch', 'subject' => 'Chemistry', 'publisher' => 'Pearson'],
                ['title' => 'Objective Biology', 'author' => 'Dr. Ali', 'class_name' => 'NEET Batch', 'subject' => 'Biology', 'publisher' => 'Universal Books'],
                ['title' => 'Cengage Mathematics', 'author' => 'G. Tewani', 'class_name' => 'JEE Batch', 'subject' => 'Mathematics', 'publisher' => 'Cengage'],
            ],
            'enquiries' => [
                [
                    'student_email' => 'student3.demo@soilnwater.test',
                    'subject' => 'JEE batch fees and timing',
                    'message' => 'Please share batch timings, monthly fees, and demo class schedule for Class 12 JEE preparation.',
                    'status' => 'new',
                ],
            ],
        ];
    }
}
