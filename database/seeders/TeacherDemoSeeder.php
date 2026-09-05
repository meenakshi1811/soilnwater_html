<?php

namespace Database\Seeders;

use App\Models\Educator;
use App\Models\EducatorEnquiry;
use App\Models\EducatorReview;
use App\Models\StudyMaterial;
use App\Models\StudyMaterialReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Teacher@123';

        $teacher = User::query()->updateOrCreate(
            ['email' => 'teacher.demo@soilnwater.test'],
            [
                'name' => 'Ananya Sharma',
                'full_name' => 'Ananya Sharma',
                'phone_number' => '9876543210',
                'whatsapp_number' => '9876543210',
                'address' => '12 Green Park Society, Ring Road',
                'city' => 'Surat',
                'pincode' => '395007',
                'latitude' => 21.1702000,
                'longitude' => 72.8311000,
                'role' => 'teacher',
                'date_of_birth' => '1990-05-14',
                'is_active' => true,
                'is_blocked' => false,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        $educator = Educator::query()->updateOrCreate(
            ['user_id' => $teacher->id],
            [
                'type' => 'teacher',
                'display_name' => 'Ananya Sharma',
                'slug' => 'ananya-sharma-demo',
                'professional_headline' => 'Senior Physics & Maths Teacher | CBSE · State Board',
                'tagline' => 'Making science simple, visual and exam-ready for every learner.',
                'associated_institute' => 'Delhi Public School, Surat',
                'institute_latitude' => 21.1755000,
                'institute_longitude' => 72.8342000,
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '395007',
                'residential_address' => 'Flat 402, Orchid Residency, Adajan, Surat',
                'latitude' => 21.1702000,
                'longitude' => 72.8311000,
                'phone' => '9876543210',
                'whatsapp' => '9876543210',
                'email' => 'teacher.demo@soilnwater.test',
                'video_profile_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'about' => "I am Ananya Sharma, a passionate Teacher / Tutor with 12+ years of classroom and tuition experience.\n\nI specialise in Physics and Mathematics for Classes 9–12 (CBSE & State Board). My lessons mix concept clarity, real-life examples, and structured board exam practice so students feel confident before every test.",
                'teaching_method' => 'Concept → Example → Practice → Revision worksheets',
                'languages' => ['English', 'Hindi', 'Gujarati'],
                'subjects' => [
                    ['name' => 'Physics', 'level' => 'primary'],
                    ['name' => 'Mathematics', 'level' => 'primary'],
                    ['name' => 'Science', 'level' => 'secondary'],
                ],
                'classes' => ['Class 9', 'Class 10', 'Class 11', 'Class 12'],
                'boards' => ['CBSE', 'GSEB', 'ICSE'],
                'qualifications' => [
                    [
                        'degree' => 'M.Sc. Physics',
                        'institution' => 'The Maharaja Sayajirao University of Baroda',
                        'year' => '2013',
                    ],
                    [
                        'degree' => 'B.Ed.',
                        'institution' => 'Veer Narmad South Gujarat University',
                        'year' => '2014',
                    ],
                ],
                'experiences' => [
                    [
                        'title' => 'Senior Physics Teacher',
                        'organization' => 'Delhi Public School, Surat',
                        'duration' => '2018 – Present',
                        'description' => 'Teaching Class 11–12 Physics, mentoring olympiad batch, and creating board revision modules.',
                    ],
                    [
                        'title' => 'Science Teacher',
                        'organization' => 'Ryan International School',
                        'duration' => '2014 – 2018',
                        'description' => 'Handled Class 8–10 Science with activity-based learning and weekly assessments.',
                    ],
                ],
                'achievements' => [
                    '100% board pass rate for Physics batch (2023–2025)',
                    'Best Teacher Award – DPS Surat (2022)',
                    'Guided 18 students into JEE Main qualifying ranks',
                ],
                'certifications' => [
                    'CTET Qualified',
                    'Cambridge Teaching Knowledge Test (TKT)',
                    'Google Certified Educator Level 1',
                ],
                'availability' => [
                    ['day' => 'Monday', 'slots' => '5:00 PM – 8:00 PM'],
                    ['day' => 'Tuesday', 'slots' => '5:00 PM – 8:00 PM'],
                    ['day' => 'Wednesday', 'slots' => '5:00 PM – 7:30 PM'],
                    ['day' => 'Thursday', 'slots' => '5:00 PM – 8:00 PM'],
                    ['day' => 'Friday', 'slots' => '5:00 PM – 7:00 PM'],
                    ['day' => 'Saturday', 'slots' => '10:00 AM – 1:00 PM'],
                ],
                'teaching_modes' => ['Online', 'Offline', 'Home tuition'],
                'service_area' => ['Adajan', 'Vesu', 'Piplod', 'Citylight', 'Online (Pan India)'],
                'teaching_stats' => [
                    'batches' => 6,
                    'weekly_hours' => 28,
                    'avg_batch_size' => 12,
                ],
                'take_tuitions' => true,
                'tuition_classes' => ['Class 10', 'Class 11', 'Class 12'],
                'tuition_subjects' => ['Physics', 'Mathematics'],
                'tuition_types' => ['1-on-1', 'Small group', 'Crash course'],
                'tuition_location' => 'Home + Online Zoom/Google Meet',
                'tuition_timings' => 'Weekdays evenings · Saturday mornings',
                'tuition_charges' => '₹450–₹800 / hour (subject & class based)',
                'years_experience' => 12,
                'students_taught' => 860,
                'success_rate' => 96.50,
                'average_rating' => 0,
                'reviews_count' => 0,
                'is_verified' => true,
                'is_available_now' => true,
                'facebook_url' => 'https://facebook.com/soilnwater',
                'instagram_url' => 'https://instagram.com/soilnwater',
                'youtube_url' => 'https://youtube.com/@soilnwater',
                'linkedin_url' => 'https://linkedin.com/in/ananya-sharma-demo',
                'whatsapp_url' => 'https://wa.me/919876543210',
                'status' => 'approved',
                'converted_from_user' => false,
                'approved_at' => now()->subMonths(2),
                'approved_by' => null,
            ]
        );

        $materialsDir = public_path('uploads/educators/materials');
        File::ensureDirectoryExists($materialsDir);

        $materialDefs = [
            [
                'title' => 'Class 12 Physics – Electrostatics Complete Notes',
                'slug' => 'class-12-physics-electrostatics-complete-notes-demo',
                'description' => '<p><strong>Complete Electrostatics notes</strong> for Class 12 CBSE / GSEB with formulae, diagrams, and board-style examples.</p><ul><li>Coulomb’s law</li><li>Electric field &amp; potential</li><li>Capacitance</li></ul>',
                'material_type' => 'notes',
                'category' => 'Higher Secondary',
                'class_course' => 'Class 12',
                'board_university' => 'CBSE',
                'subject' => 'Physics',
                'topic_chapter' => 'Electrostatics',
                'exam_test' => 'Board Exam',
                'language' => 'English',
                'difficulty' => 'Intermediate',
                'academic_year' => '2025-26',
                'medium' => 'English',
                'pages' => 42,
                'tags' => ['physics', 'class-12', 'electrostatics', 'cbse'],
                'contents' => [
                    'Chapter overview & key formulae',
                    'Coulomb’s law with numericals',
                    'Electric field due to point charge',
                    'Gauss’s law applications',
                    'Capacitors & combinations',
                    'Board previous year questions',
                ],
                'is_trending' => true,
            ],
            [
                'title' => 'Class 10 Maths – Quadratic Equations Worksheets',
                'slug' => 'class-10-maths-quadratic-equations-worksheets-demo',
                'description' => '<p>Practice worksheets for quadratic equations with answer key and step-by-step solutions.</p>',
                'material_type' => 'worksheets',
                'category' => 'School',
                'class_course' => 'Class 10',
                'board_university' => 'CBSE',
                'subject' => 'Mathematics',
                'topic_chapter' => 'Quadratic Equations',
                'exam_test' => 'Unit Test',
                'language' => 'English',
                'difficulty' => 'Beginner',
                'academic_year' => '2025-26',
                'medium' => 'English',
                'pages' => 18,
                'tags' => ['maths', 'class-10', 'quadratic', 'worksheet'],
                'contents' => [
                    'Concept checklist',
                    'Level 1 practice set',
                    'Level 2 application set',
                    'Answer key',
                ],
                'is_trending' => false,
            ],
            [
                'title' => 'Class 11 Physics – Motion in a Plane Sample Paper',
                'slug' => 'class-11-physics-motion-in-a-plane-sample-paper-demo',
                'description' => '<p>Full-length sample paper with marking scheme style questions for mid-term revision.</p>',
                'material_type' => 'sample_papers',
                'category' => 'Higher Secondary',
                'class_course' => 'Class 11',
                'board_university' => 'CBSE',
                'subject' => 'Physics',
                'topic_chapter' => 'Motion in a Plane',
                'exam_test' => 'Mid Term',
                'language' => 'English',
                'difficulty' => 'Advanced',
                'academic_year' => '2025-26',
                'medium' => 'English',
                'pages' => 12,
                'tags' => ['physics', 'class-11', 'sample-paper'],
                'contents' => [
                    'Section A – MCQs',
                    'Section B – Short answers',
                    'Section C – Long answers',
                    'Suggested marking points',
                ],
                'is_trending' => true,
            ],
            [
                'title' => 'Class 12 Physics – Current Electricity Study Guide',
                'slug' => 'class-12-physics-current-electricity-study-guide-demo',
                'description' => '<p>Compact study guide covering Ohm’s law, Kirchhoff’s rules, and meter bridge experiments.</p>',
                'material_type' => 'study_guides',
                'category' => 'Higher Secondary',
                'class_course' => 'Class 12',
                'board_university' => 'GSEB',
                'subject' => 'Physics',
                'topic_chapter' => 'Current Electricity',
                'exam_test' => 'Board Exam',
                'language' => 'English',
                'difficulty' => 'Intermediate',
                'academic_year' => '2025-26',
                'medium' => 'English',
                'pages' => 28,
                'tags' => ['physics', 'current-electricity', 'guide'],
                'contents' => [
                    'Core definitions',
                    'Important derivations',
                    'Numerical strategy',
                    'Quick revision sheet',
                ],
                'is_trending' => false,
            ],
            [
                'title' => 'Class 9 Science – Atoms and Molecules Notes',
                'slug' => 'class-9-science-atoms-and-molecules-notes-demo',
                'description' => '<p>Clear notes on laws of chemical combination, mole concept, and molecular mass with examples.</p>',
                'material_type' => 'notes',
                'category' => 'School',
                'class_course' => 'Class 9',
                'board_university' => 'CBSE',
                'subject' => 'Science',
                'topic_chapter' => 'Atoms and Molecules',
                'exam_test' => 'Chapter Test',
                'language' => 'English',
                'difficulty' => 'Beginner',
                'academic_year' => '2025-26',
                'medium' => 'English',
                'pages' => 16,
                'tags' => ['science', 'class-9', 'chemistry'],
                'contents' => [
                    'Laws of chemical combination',
                    'Dalton’s atomic theory',
                    'Mole concept basics',
                    'Practice questions',
                ],
                'is_trending' => false,
            ],
        ];

        $materials = collect();
        foreach ($materialDefs as $index => $def) {
            $fileName = Str::slug($def['slug']).'.txt';
            $relativePath = 'uploads/educators/materials/'.$fileName;
            $absolutePath = public_path($relativePath);
            File::put(
                $absolutePath,
                "SoilNWater Demo Study Material\n\n".
                $def['title']."\n\n".
                "This is a dummy downloadable file for local demo/testing.\n".
                "Replace with a real PDF/DOC in production uploads.\n"
            );

            $material = StudyMaterial::query()->updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'educator_id' => $educator->id,
                    'user_id' => $teacher->id,
                    'title' => $def['title'],
                    'description' => $def['description'],
                    'thumbnail' => null,
                    'file_path' => $relativePath,
                    'file_name' => pathinfo($def['title'], PATHINFO_FILENAME).'.txt',
                    'file_type' => 'txt',
                    'file_size' => File::size($absolutePath),
                    'pages' => $def['pages'],
                    'material_type' => $def['material_type'],
                    'category' => $def['category'],
                    'class_course' => $def['class_course'],
                    'board_university' => $def['board_university'],
                    'subject' => $def['subject'],
                    'topic_chapter' => $def['topic_chapter'],
                    'exam_test' => $def['exam_test'],
                    'language' => $def['language'],
                    'difficulty' => $def['difficulty'],
                    'academic_year' => $def['academic_year'],
                    'medium' => $def['medium'],
                    'is_free' => true,
                    'is_trending' => $def['is_trending'],
                    'is_verified' => true,
                    'tags' => $def['tags'],
                    'contents' => $def['contents'],
                    'average_rating' => 0,
                    'reviews_count' => 0,
                    'views_count' => 120 + ($index * 37),
                    'downloads_count' => 45 + ($index * 11),
                    'saves_count' => 8 + $index,
                    'status' => 'approved',
                    'approved_at' => now()->subDays(20 - $index),
                    'approved_by' => null,
                ]
            );

            $materials->push($material);
        }

        $students = [
            [
                'email' => 'student1.demo@soilnwater.test',
                'name' => 'Rohan Patel',
                'phone' => '9123456780',
            ],
            [
                'email' => 'student2.demo@soilnwater.test',
                'name' => 'Meera Desai',
                'phone' => '9123456781',
            ],
            [
                'email' => 'student3.demo@soilnwater.test',
                'name' => 'Kabir Mehta',
                'phone' => '9123456782',
            ],
        ];

        $studentUsers = collect();
        foreach ($students as $i => $student) {
            $user = User::query()->updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'full_name' => $student['name'],
                    'phone_number' => $student['phone'],
                    'whatsapp_number' => $student['phone'],
                    'address' => 'Demo Student Address '.$i,
                    'city' => 'Surat',
                    'pincode' => '395009',
                    'role' => 'user',
                    'is_active' => true,
                    'password' => Hash::make('Student@123'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                ]
            );
            $studentUsers->push($user);
        }

        foreach ($studentUsers as $index => $student) {
            $educator->followers()->syncWithoutDetaching([$student->id]);

            EducatorReview::query()->updateOrCreate(
                [
                    'educator_id' => $educator->id,
                    'user_id' => $student->id,
                ],
                [
                    'student_name' => $student->name,
                    'student_class' => ['Class 12', 'Class 10', 'Class 11'][$index] ?? 'Class 12',
                    'rating' => [5, 4, 5][$index] ?? 5,
                    'review' => [
                        'Excellent explanations and very patient. My Physics marks improved a lot.',
                        'Notes are clear and worksheets are exam-oriented. Highly recommended.',
                        'Great tuition mentor. Concepts become easy and revision is well planned.',
                    ][$index] ?? 'Great teacher.',
                ]
            );

            EducatorEnquiry::query()->updateOrCreate(
                [
                    'educator_id' => $educator->id,
                    'user_id' => $student->id,
                ],
                [
                    'name' => $student->name,
                    'email' => $student->email,
                    'phone' => $student->phone_number,
                    'subject' => 'Demo enquiry from '.$student->name,
                    'message' => 'Hello Ananya ma’am, I would like to join your Physics tuition batch. Please share available slots and fees.',
                    'status' => $index === 0 ? 'new' : 'read',
                ]
            );
        }

        foreach ($materials->take(3) as $mIndex => $material) {
            foreach ($studentUsers->take(2) as $sIndex => $student) {
                StudyMaterialReview::query()->updateOrCreate(
                    [
                        'study_material_id' => $material->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'rating' => 4 + (($mIndex + $sIndex) % 2),
                        'review' => 'Very useful demo notes for '.$material->subject.'. Helped me revise quickly.',
                    ]
                );
                $material->bookmarkedBy()->syncWithoutDetaching([$student->id]);
            }
            $material->recalculateRating();
            $material->forceFill([
                'saves_count' => $material->bookmarkedBy()->count(),
            ])->save();
        }

        $educator->recalculateRating();

        $this->command?->info('Teacher demo data seeded successfully.');
        $this->command?->newLine();
        $this->command?->table(
            ['Field', 'Value'],
            [
                ['Teacher portal login email', 'teacher.demo@soilnwater.test'],
                ['Password', $password],
                ['Phone', '9876543210'],
                ['Public profile', url('/teachers-tutors/ananya-sharma-demo')],
                ['Educator portal', url('/educator/dashboard')],
                ['Student demo logins', 'student1.demo@soilnwater.test / Student@123'],
                ['Study materials', (string) $materials->count().' approved notes/resources'],
            ]
        );
    }
}
