<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityStudentCornerPostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    private const SAMPLE_PDF_URL = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Student Corner Author',
            'email' => 'student-corner@example.com',
        ]);

        foreach ($this->studentCornerPosts() as $post) {
            $this->upsertPost($author, $post);
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): void
    {
        $meta = array_merge($this->sharedMetaDefaults(), $post['meta'] ?? []);

        if (($meta['student_corner_visibility'] ?? 'public') === 'private_link' && blank(data_get($meta, 'student_corner_private_link_token'))) {
            $meta['student_corner_private_link_token'] = Str::random(48);
        }

        CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'student-corner',
                'category' => $post['category'],
                'writing_purpose' => $post['writing_purpose'] ?? 'Share Knowledge',
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'body' => $post['body'],
                'featured_image_path' => $post['featured_image'],
                'featured_images' => $post['featured_images'] ?? [$post['featured_image']],
                'tags' => $post['tags'],
                'location_type' => CommunityPost::LOCATION_TYPE_CITY,
                'location' => self::LOCATION,
                'location_lat' => self::LOCATION_LAT,
                'location_lng' => self::LOCATION_LNG,
                'video' => $post['video'] ?? null,
                'publish_as' => $post['publish_as'] ?? CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
                'pen_name' => $post['pen_name'] ?? null,
                'meta' => array_filter($meta, fn ($value) => filled($value) || is_bool($value) || is_array($value)),
                'allow_comments' => $post['allow_comments'] ?? true,
                'allow_questions' => $post['allow_questions'] ?? true,
                'allow_feedback' => $post['allow_feedback'] ?? true,
                'allow_suggestions' => $post['allow_suggestions'] ?? false,
                'allow_sharing' => $post['allow_sharing'] ?? true,
                'allow_poll' => $post['allow_poll'] ?? false,
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => now()->subDays($post['days_ago']),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function sharedMetaDefaults(): array
    {
        return [
            'author_bio' => 'Class 12 science student from Jaipur sharing projects, exam strategies, and campus learning experiences.',
            'location_country' => 'India',
            'location_state' => 'Rajasthan',
            'location_district' => 'Jaipur',
            'location_city' => 'Jaipur',
            'student_corner_visibility' => CommunityContentTaxonomy::studentCornerDefaultVisibilitySetting(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function studentCornerPosts(): array
    {
        return [
            $this->fullFeaturedScienceProjectPost(),
            $this->scholarshipAndCareerGuidancePost(),
            $this->jeeExamStrategyPost(),
            $this->internshipExperiencePost(),
            $this->privateLinkStudyNotesPost(),
        ];
    }

    /**
     * Full public post with every Student Corner field populated.
     *
     * @return array<string, mixed>
     */
    private function fullFeaturedScienceProjectPost(): array
    {
        return [
            'slug' => 'student-corner-rainwater-harvesting-science-project',
            'category' => 'Projects',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'School Rainwater Harvesting Model: From Lab Experiment to Community Demo',
            'excerpt' => 'A Class 12 science student documents a rainwater harvesting project with field visits, lab work, competition entry, scholarship research, and lessons for younger students.',
            'featured_image' => 'https://picsum.photos/seed/student-corner-project/1200/630',
            'tags' => ['JEE', 'Scholarship', 'Science', 'Project', 'Career', 'Education', 'Water Conservation', 'Innovation', 'Research', 'Agriculture'],
            'days_ago' => 2,
            'allow_poll' => true,
            'allow_comments' => true,
            'allow_questions' => true,
            'allow_feedback' => true,
            'allow_sharing' => true,
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=EngW7tLk6R8',
                'video_id' => 'EngW7tLk6R8',
            ],
            'body' => <<<'HTML'
<h2>Introduction</h2>
<p>Our school science club wanted to prove that a low-cost rainwater harvesting model could work on the terrace of a government school building in Jaipur. This post documents the full journey from hypothesis to community demonstration.</p>
<h2>Objective</h2>
<p>Design a portable harvesting unit using locally available materials, measure collection efficiency during monsoon weeks, and prepare a presentation for district-level science fair judges.</p>
<h2>Main Content</h2>
<p>We built a two-tier filtration system using mesh, sand, and charcoal layers. A 200-litre drum stored filtered water for gardening use. During three weeks of monitoring we recorded daily rainfall, runoff volume, and turbidity before and after filtration.</p>
<p>Field visits to a nearby farm helped us compare rooftop collection with contour bunding. Lab sessions focused on pH testing, TDS readings, and bacterial colony counts using prepared agar plates.</p>
<h2>Learnings</h2>
<ul>
    <li>First-flush diverters dramatically improve stored water quality.</li>
    <li>Community adoption depends more on maintenance training than on hardware cost.</li>
    <li>Documenting every reading in a shared logbook prevented data loss during busy exam weeks.</li>
</ul>
<h2>Tips / Recommendations</h2>
<p>Start with a small prototype, photograph each assembly step, and invite one local expert for a 30-minute review before the fair. Teachers appreciate students who can explain both science and social impact clearly.</p>
<h2>Conclusion</h2>
<p>Science projects become memorable when they solve a real neighbourhood problem. We are now mentoring Class 9 students to scale the design for their block.</p>
HTML,
            'meta' => [
                'student_corner_category' => 'Projects',
                'student_corner_content_type' => CommunityContentTaxonomy::studentCornerProjectContentType(),
                'student_corner_profile_name' => 'Aarav Sharma',
                'student_corner_class_course' => 'Class 11-12',
                'student_corner_stream' => 'Science',
                'student_corner_institution_name' => 'Govt. Senior Secondary School, Sanganer, Jaipur',
                'student_corner_target_audience' => [
                    'School Students',
                    'Class 10 Students',
                    'Class 12 Students',
                    'Engineering Students',
                    'Teachers',
                    'Parents',
                ],
                'student_corner_project_title' => 'Low-Cost Terrace Rainwater Harvesting and Filtration Unit',
                'student_corner_project_category' => 'Environmental Project',
                'student_corner_project_description' => 'A modular harvesting prototype with first-flush diversion, three-layer filtration, and storage monitoring for school gardening use.',
                'student_corner_project_outcome' => 'Collected 420 litres across three monsoon weeks, reduced post-filtration turbidity by 78%, and won district science fair commendation.',
                'student_corner_documents' => [
                    $this->seedDocument('Project Report.pdf'),
                    $this->seedDocument('Presentation Slides.pptx', self::SAMPLE_PDF_URL),
                    $this->seedDocument('Data Logbook.xlsx', self::SAMPLE_PDF_URL),
                ],
                'student_corner_gallery' => [
                    $this->seedGalleryImage('sc-project-photo', 'Project Photos - Terrace model.jpg'),
                    $this->seedGalleryImage('sc-lab-work', 'Lab Work - Turbidity testing.jpg'),
                    $this->seedGalleryImage('sc-field-visit', 'Field Visits - Farm comparison.jpg'),
                    $this->seedGalleryImage('sc-competition', 'Competition Photos - Science fair booth.jpg'),
                    $this->seedGalleryImage('sc-certificate', 'Certificates - School commendation.jpg'),
                ],
                'student_corner_video_type' => 'Project Demonstration',
                'student_corner_study_material_types' => CommunityContentTaxonomy::studentCornerStudyMaterialTypes(),
                'student_corner_career_guidance_topics' => CommunityContentTaxonomy::studentCornerCareerGuidanceTopics(),
                'student_corner_scholarship_name' => 'INSPIRE Scholarship (DST)',
                'student_corner_eligibility' => "Top 1% in Class 12 board exams within school board.\nEnrollment in basic science or integrated M.Sc. programmes.\nAge within DST INSPIRE guidelines.",
                'student_corner_application_deadline' => '31 October 2026',
                'student_corner_official_website' => 'https://www.online-inspire.gov.in/',
                'student_corner_exam_name' => 'JEE Main & Advanced',
                'student_corner_preparation_strategy' => "Daily: 2 physics numerical sets, 1 chemistry mechanism map, 1 biology diagram revision.\nWeekly: one full-length mock on alternate Sundays.\nMonthly: analyse weak chapters using error logbook.",
                'student_corner_resources_used' => "NCERT textbooks (complete twice)\nPW / Unacademy revision modules\nPrevious 10 years JEE Main papers\nLocal library reference corner for agriculture-water case studies",
                'student_corner_marks_rank' => 'Mock JEE Main: 96 percentile (January session practice test)',
                'student_corner_lessons_learned' => "Consistency beats cramming in the final 60 days.\nProject work improved my physics application speed.\nTeaching juniors forced me to simplify concepts I thought I knew.",
                'student_corner_skills' => CommunityContentTaxonomy::studentCornerSkills(),
                'student_corner_social_impact_categories' => CommunityContentTaxonomy::studentCornerSocialImpactCategories(),
                'student_corner_ask_community' => 'What study techniques helped you score better in board exams while preparing for competitive exams?',
                'student_corner_poll_question' => 'Which competitive exam are you preparing for?',
                'student_corner_poll_options' => CommunityContentTaxonomy::studentCornerDefaultPollOptions(),
                'student_corner_mentorship_requests' => CommunityContentTaxonomy::studentCornerMentorshipRequests(),
                'student_corner_submit_to_competition' => true,
                'student_corner_competition_categories' => [
                    'Science Project',
                    'Innovation Challenge',
                    'Essay Writing',
                    'Photography',
                ],
                'student_corner_achievements' => [
                    [
                        'achievement_title' => 'District Science Fair Commendation',
                        'year' => '2025',
                        'certificate' => $this->seedCertificate('District Science Fair Certificate.pdf'),
                    ],
                    [
                        'achievement_title' => 'School Water Ambassador Badge',
                        'year' => '2024',
                        'certificate' => $this->seedCertificate('Water Ambassador Certificate.pdf'),
                    ],
                ],
                'student_corner_visibility' => 'public',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scholarshipAndCareerGuidancePost(): array
    {
        return [
            'slug' => 'student-corner-national-scholarship-roadmap-class-12',
            'category' => 'Scholarships',
            'writing_purpose' => 'Help Community',
            'title' => 'National Scholarship Roadmap for Class 12 Science Students',
            'excerpt' => 'A practical guide to INSPIRE, KVPY successor pathways, state merit scholarships, and timelines Class 12 students should track.',
            'featured_image' => 'https://picsum.photos/seed/student-corner-scholarship/1200/630',
            'tags' => ['Scholarship', 'Education', 'Career', 'Science', 'Higher Education'],
            'days_ago' => 6,
            'body' => <<<'HTML'
<h2>Introduction</h2>
<p>Scholarship hunting can feel overwhelming when board exams and entrance tests overlap. I compiled the deadlines and eligibility notes I wish someone had shared with me in Class 11.</p>
<h2>Objective</h2>
<p>Help Rajasthan students identify national and state scholarships that reward science projects, merit ranks, and research interest.</p>
<h2>Main Content</h2>
<p>Start with DST INSPIRE, track state higher education department notifications, and maintain a single folder for income certificates, bonafide letters, and recommendation drafts.</p>
<h2>Learnings</h2>
<p>Most rejections happen because of missing documents, not because of low marks. Begin paperwork one month before portals open.</p>
<h2>Tips / Recommendations</h2>
<p>Ask your school office for transcript templates early. Keep scanned PDFs under 500 KB where portals require uploads.</p>
<h2>Conclusion</h2>
<p>Scholarships reward preparation and clarity — treat the application like a mini research project.</p>
HTML,
            'meta' => [
                'student_corner_category' => 'Scholarships',
                'student_corner_content_type' => 'Career Guidance',
                'student_corner_profile_name' => 'Meera Khandelwal',
                'student_corner_class_course' => 'Class 11-12',
                'student_corner_stream' => 'Science',
                'student_corner_institution_name' => 'Maharani Gayatri Devi Girls School, Jaipur',
                'student_corner_target_audience' => ['Class 12 Students', 'College Students', 'Parents', 'Teachers'],
                'student_corner_career_guidance_topics' => [
                    'Higher Education',
                    'Study Abroad',
                    'Career Planning',
                    'Skill Development',
                ],
                'student_corner_scholarship_name' => 'Rajasthan State Merit Scholarship',
                'student_corner_eligibility' => 'Minimum 75% in Class 12 from Rajasthan board schools. Family income certificate required for fee reimbursement categories.',
                'student_corner_application_deadline' => '15 August 2026',
                'student_corner_official_website' => 'https://scholarship.rajasthan.gov.in/',
                'student_corner_skills' => ['Communication', 'Writing', 'Research'],
                'student_corner_social_impact_categories' => ['Education'],
                'student_corner_ask_community' => 'Which scholarship programs would you recommend for first-generation college students?',
                'student_corner_mentorship_requests' => ['Need Career Guidance'],
                'student_corner_visibility' => 'public',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function jeeExamStrategyPost(): array
    {
        return [
            'slug' => 'student-corner-jee-main-90-day-revision-plan',
            'category' => 'Competitive Exams',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'My 90-Day JEE Main Revision Plan After Board Exams',
            'excerpt' => 'An exam strategy post with chapter priorities, mock test analysis routine, and honest lessons from two practice sessions.',
            'featured_image' => 'https://picsum.photos/seed/student-corner-jee/1200/630',
            'tags' => ['JEE', 'Education', 'Study Tips', 'Science', 'Career'],
            'days_ago' => 10,
            'allow_poll' => true,
            'body' => <<<'HTML'
<h2>Introduction</h2>
<p>After boards I had exactly twelve weeks before my first JEE Main attempt. This is the structured plan that helped me recover confidence after a low mock score.</p>
<h2>Objective</h2>
<p>Improve accuracy in mechanics and organic chemistry while protecting sleep and school lab commitments.</p>
<h2>Main Content</h2>
<p>Weeks 1–4 focused on NCERT gaps. Weeks 5–8 mixed chapter tests with timed numerical drills. Weeks 9–12 were full mocks with same-day error analysis.</p>
<h2>Learnings</h2>
<p>Skipping error logs repeats the same mistakes. Short naps after long numerical sessions helped retention more than late-night cramming.</p>
<h2>Conclusion</h2>
<p>Exam preparation is a skill — document what works and adjust weekly.</p>
HTML,
            'meta' => [
                'student_corner_category' => 'Competitive Exams',
                'student_corner_content_type' => 'Exam Strategy',
                'student_corner_profile_name' => 'Rohan Meena',
                'student_corner_class_course' => 'Class 11-12',
                'student_corner_stream' => 'Science',
                'student_corner_institution_name' => 'Vidya Bhawan Senior Secondary School, Udaipur',
                'student_corner_target_audience' => ['Class 12 Students', 'Engineering Students', 'Medical Aspirants'],
                'student_corner_study_material_types' => [
                    'Notes',
                    'Formula Sheet',
                    'Question Bank',
                    'Previous Year Papers',
                    'Solved Examples',
                ],
                'student_corner_exam_name' => 'JEE Main',
                'student_corner_preparation_strategy' => 'NCERT-first revision, alternate-day mocks, Sunday-only new chapter discovery.',
                'student_corner_resources_used' => 'NCERT, Arihant previous years, local test series, free NTA abhyas app drills.',
                'student_corner_marks_rank' => 'Practice mock: 182/300 (March)',
                'student_corner_lessons_learned' => 'Speed without accuracy costs ranks. Sleep is not optional during intensive blocks.',
                'student_corner_skills' => ['Programming', 'Research', 'AI & Technology'],
                'student_corner_poll_question' => 'Which competitive exam are you preparing for?',
                'student_corner_poll_options' => CommunityContentTaxonomy::studentCornerDefaultPollOptions(),
                'student_corner_ask_community' => 'Which mock test series gave you the most realistic JEE Main difficulty?',
                'student_corner_mentorship_requests' => ['Need Exam Preparation Advice'],
                'student_corner_visibility' => 'registered_users',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function internshipExperiencePost(): array
    {
        return [
            'slug' => 'student-corner-summer-internship-agritech-startup',
            'category' => 'Internships',
            'writing_purpose' => 'Personal Experience',
            'title' => 'What I Learned During a Summer Internship at an Agritech Startup',
            'excerpt' => 'A B.Sc. Agriculture student shares internship tasks, mentor feedback, and skills that transferred back to campus projects.',
            'featured_image' => 'https://picsum.photos/seed/student-corner-internship/1200/630',
            'tags' => ['Career', 'Agriculture', 'Internship', 'Skill Development', 'Education'],
            'days_ago' => 18,
            'publish_as' => CommunityPost::PUBLISH_AS_FIRST_NAME_ONLY,
            'body' => <<<'HTML'
<h2>Introduction</h2>
<p>My first startup internship was messy, exciting, and far more practical than any single textbook chapter.</p>
<h2>Experience</h2>
<p>I mapped farmer onboarding calls, cleaned soil moisture datasets, and presented a one-page brief on drip irrigation adoption barriers.</p>
<h2>Advice</h2>
<p>Ask for weekly feedback. Volunteer for the boring data tasks — they teach you how real products ship.</p>
<h2>Conclusion</h2>
<p>Internships connect classroom theory to field reality. Start applying one semester before you think you are ready.</p>
HTML,
            'meta' => [
                'student_corner_category' => 'Internships',
                'student_corner_content_type' => 'Internship Experience',
                'student_corner_profile_name' => 'Priya Singh',
                'student_corner_class_course' => 'Undergraduate',
                'student_corner_stream' => 'Agriculture',
                'student_corner_institution_name' => 'Swami Keshwanand Rajasthan Agricultural University, Bikaner',
                'student_corner_target_audience' => ['College Students', 'Job Aspirants', 'Researchers'],
                'student_corner_career_guidance_topics' => [
                    'Career Planning',
                    'Job Preparation',
                    'Skill Development',
                    'Interview Preparation',
                ],
                'student_corner_skills' => ['Agriculture', 'Communication', 'Research', 'Marketing'],
                'student_corner_social_impact_categories' => ['Agriculture', 'Technology', 'Community Service'],
                'student_corner_ask_community' => 'How did you find your first internship outside campus placement cells?',
                'student_corner_mentorship_requests' => ['Need Internship Guidance', 'Need Career Guidance'],
                'student_corner_submit_to_competition' => false,
                'student_corner_visibility' => 'students_only',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function privateLinkStudyNotesPost(): array
    {
        return [
            'slug' => 'student-corner-organic-chemistry-reaction-map-notes',
            'category' => 'Study Tips',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'Organic Chemistry Reaction Map Notes (Shared Privately)',
            'excerpt' => 'Hand-drawn reaction maps and mnemonic sheets for Class 12 organic chemistry — shared via private link with study group members.',
            'featured_image' => 'https://picsum.photos/seed/student-corner-notes/1200/630',
            'tags' => ['Education', 'Science', 'Study Tips', 'JEE'],
            'days_ago' => 21,
            'allow_poll' => false,
            'allow_comments' => true,
            'allow_questions' => false,
            'allow_feedback' => true,
            'allow_sharing' => false,
            'publish_as' => CommunityPost::PUBLISH_AS_PEN_NAME,
            'pen_name' => 'ChemMap Mentor',
            'body' => <<<'HTML'
<h2>Introduction</h2>
<p>These are the reaction maps I used while preparing for boards and JEE Main organic sections.</p>
<h2>Main Content</h2>
<p>Each map groups reactions by functional group interconversions with colour-coded reagents and common exceptions noted in the margin.</p>
<h2>Tips</h2>
<p>Redraw maps from memory every Sunday. Mark reagents you confuse with a red dot and drill only those edges the next day.</p>
<h2>Conclusion</h2>
<p>Shared privately with classmates who requested the sheets — ask for the link if you are in our study circle.</p>
HTML,
            'meta' => [
                'student_corner_category' => 'Study Tips',
                'student_corner_content_type' => 'Study Notes',
                'student_corner_class_course' => 'Class 11-12',
                'student_corner_stream' => 'Science',
                'student_corner_institution_name' => 'Private study group — Jaipur',
                'student_corner_target_audience' => ['Class 12 Students', 'Engineering Students'],
                'student_corner_study_material_types' => [
                    'Notes',
                    'Formula Sheet',
                    'Solved Examples',
                    'Reference Material',
                ],
                'student_corner_gallery' => [
                    $this->seedGalleryImage('sc-notes-1', 'Notes - Aldehyde ketone map.jpg'),
                    $this->seedGalleryImage('sc-notes-2', 'Formula Sheet - Named reactions.jpg'),
                ],
                'student_corner_video_type' => 'Skill Demonstration',
                'student_corner_skills' => ['Writing', 'Research'],
                'student_corner_visibility' => 'private_link',
            ],
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function seedGalleryImage(string $seed, string $name): array
    {
        return [
            'path' => 'seed/student-corner/'.$seed.'.jpg',
            'url' => 'https://picsum.photos/seed/'.$seed.'/800/600',
            'name' => $name,
            'type' => 'image/jpeg',
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function seedDocument(string $name, ?string $url = null): array
    {
        $url ??= self::SAMPLE_PDF_URL;

        return [
            'path' => 'seed/student-corner/'.Str::slug(pathinfo($name, PATHINFO_FILENAME)).'.'.pathinfo($name, PATHINFO_EXTENSION),
            'url' => $url,
            'name' => $name,
            'type' => str_ends_with(strtolower($name), '.pdf') ? 'application/pdf' : 'application/octet-stream',
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function seedCertificate(string $name): array
    {
        return $this->seedDocument($name, self::SAMPLE_PDF_URL);
    }
}
