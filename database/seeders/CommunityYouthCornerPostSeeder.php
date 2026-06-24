<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityYouthCornerPostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    private const SAMPLE_PDF_URL = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Youth Corner Author',
            'email' => 'youth-corner@example.com',
        ]);

        foreach ($this->youthCornerPosts() as $post) {
            $this->upsertPost($author, $post);
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): void
    {
        $meta = array_merge($this->sharedMetaDefaults(), $post['meta'] ?? []);

        if (($meta['youth_corner_visibility'] ?? 'public') === 'private_link' && blank(data_get($meta, 'youth_corner_private_link_token'))) {
            $meta['youth_corner_private_link_token'] = Str::random(48);
        }

        CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'youth-corner',
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
            'author_bio' => 'Young professional from Jaipur sharing career journeys, startup lessons, and community projects.',
            'location_country' => 'India',
            'location_state' => 'Rajasthan',
            'location_district' => 'Jaipur',
            'location_city' => 'Jaipur',
            'youth_corner_visibility' => CommunityContentTaxonomy::youthCornerDefaultVisibilitySetting(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function youthCornerPosts(): array
    {
        return [
            $this->fullFeaturedProjectPost(),
            $this->startupStoryPost(),
            $this->careerAdvicePost(),
            $this->privateLinkResourcePost(),
            $this->youthCommunityVisibilityPost(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fullFeaturedProjectPost(): array
    {
        return [
            'slug' => 'youth-corner-smart-irrigation-project-showcase',
            'category' => 'Agriculture & Rural',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'Smart Drip Irrigation for Terrace Farming: A Youth-Led Pilot in Jaipur',
            'excerpt' => 'A 24-year-old agri-tech enthusiast documents a low-cost smart irrigation prototype with field trials, mentorship requests, community poll, and lessons for rural youth entrepreneurs.',
            'featured_image' => 'https://picsum.photos/seed/youth-corner-project/1200/630',
            'tags' => CommunityContentTaxonomy::youthCornerTagExamples(),
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
<h2>Problem / Challenge</h2>
<p>Small terrace farms in Jaipur lose 30–40% of irrigation water because schedules are manual and rainfall is unpredictable. Youth farmers needed an affordable way to monitor soil moisture without expensive IoT kits.</p>
<h2>What I Did</h2>
<p>I built a sensor-assisted drip controller using a NodeMCU board, capacitive soil probes, and a solenoid valve salvaged from an old washing machine. The controller sends moisture alerts to a simple mobile dashboard built with MIT App Inventor.</p>
<h2>Results</h2>
<p>Over six weeks we reduced water use by 28% on a 200 sq ft terrace plot while maintaining tomato yield. Three neighbouring households adopted the open-source wiring diagram.</p>
<h2>Advice for Others</h2>
<p>Start with one crop and one sensor zone. Document every failure — our first prototype fried two relays before we added proper flyback diodes. Share your BOM publicly so rural youth can replicate without vendor lock-in.</p>
HTML,
            'meta' => [
                'youth_corner_category' => 'Agriculture & Rural',
                'youth_corner_content_type' => CommunityContentTaxonomy::youthCornerProjectContentType(),
                'youth_corner_age_group' => '19-25',
                'youth_corner_occupation' => 'Entrepreneur',
                'youth_corner_education_level' => 'Undergraduate',
                'youth_corner_target_audience' => [
                    'Young Professionals',
                    'Entrepreneurs',
                    'Farmers',
                    'Students',
                ],
                'youth_corner_project_title' => 'Low-Cost Smart Drip Controller for Terrace Farms',
                'youth_corner_project_category' => 'Water Conservation',
                'youth_corner_project_description' => 'IoT-assisted drip irrigation with soil moisture sensing, rainfall skip logic, and a mobile alert dashboard for small terrace plots.',
                'youth_corner_project_outcome' => '28% water savings over six weeks, three household adoptions, and district youth innovation fair shortlist.',
                'youth_corner_documents' => [
                    $this->seedDocument('Project Report.pdf'),
                    $this->seedDocument('Wiring Diagram.pdf'),
                    $this->seedDocument('Bill of Materials.xlsx', self::SAMPLE_PDF_URL),
                ],
                'youth_corner_gallery' => [
                    $this->seedGalleryImage('yc-project-1', 'Prototype - Sensor node on terrace.jpg'),
                    $this->seedGalleryImage('yc-project-2', 'Field trial - Tomato bed week 3.jpg'),
                    $this->seedGalleryImage('yc-project-3', 'Demo day - Community presentation.jpg'),
                    $this->seedGalleryImage('yc-project-4', 'Certificate - Innovation fair shortlist.jpg'),
                ],
                'youth_corner_video_type' => 'Project Demo',
                'youth_corner_opportunity_types' => [
                    'Internship',
                    'Startup Opportunity',
                    'Training Program',
                    'Scholarship',
                ],
                'youth_corner_skills' => [
                    'Leadership',
                    'Communication',
                    'Programming',
                    'Agriculture',
                    'Writing',
                ],
                'youth_corner_career_area' => 'Agriculture',
                'youth_corner_themes' => [
                    'Innovation',
                    'Environment',
                    'Entrepreneurship',
                    'Community',
                ],
                'youth_corner_community_service' => [
                    'Volunteer Work',
                    'Awareness Campaign',
                    'Community Development',
                ],
                'youth_corner_networking_options' => [
                    'Seek Guidance',
                    'Join Project',
                    'Discuss Opportunities',
                ],
                'youth_corner_ask_community' => 'What low-cost sensors have you used successfully for soil moisture in arid climates?',
                'youth_corner_poll_question' => 'Which area would you like to explore next?',
                'youth_corner_poll_options' => CommunityContentTaxonomy::youthCornerDefaultPollOptions(),
                'youth_corner_mentorship_requests' => CommunityContentTaxonomy::youthCornerMentorshipRequests(),
                'youth_corner_achievements' => [
                    [
                        'achievement_title' => 'District Youth Innovation Fair Shortlist',
                        'year' => '2025',
                        'certificate' => $this->seedCertificate('Innovation Fair Certificate.pdf'),
                    ],
                    [
                        'achievement_title' => 'Agri-Tech Bootcamp Graduate',
                        'year' => '2024',
                        'certificate' => $this->seedCertificate('Bootcamp Certificate.pdf'),
                    ],
                ],
                'youth_corner_visibility' => 'public',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function startupStoryPost(): array
    {
        return [
            'slug' => 'youth-corner-ecobox-startup-journey',
            'category' => 'Startups',
            'writing_purpose' => 'Inspire Others',
            'title' => 'From College Project to EcoBox: Building a Sustainable Packaging Startup',
            'excerpt' => 'A first-time founder shares the EcoBox journey — idea validation, funding stages, early customer wins, and honest lessons from failed pilot batches.',
            'featured_image' => 'https://picsum.photos/seed/youth-corner-startup/1200/630',
            'tags' => ['Startup', 'Entrepreneurship', 'Sustainability', 'Innovation', 'Packaging'],
            'days_ago' => 5,
            'allow_comments' => true,
            'allow_questions' => true,
            'allow_feedback' => true,
            'allow_sharing' => true,
            'body' => <<<'HTML'
<h2>Problem / Challenge</h2>
<p>E-commerce sellers in Jaipur wanted biodegradable mailers but suppliers quoted MOQs we could not afford as students. We saw a gap for compostable packaging tailored to small D2C brands.</p>
<h2>What I Did</h2>
<p>We validated with 15 shop owners, prototyped cassava-starch blends in a college lab, and ran a 500-unit pilot with two local skincare brands. Customer feedback reshaped our sizing and print options.</p>
<h2>Results</h2>
<p>EcoBox now serves 40 recurring clients, crossed ₹8 lakh ARR in year one, and secured angel interest after a Rajasthan startup showcase.</p>
<h2>Advice for Others</h2>
<p>Talk to customers before perfecting the product. Our first batch looked great but tore in monsoon humidity — fixing that taught us more than any business plan competition.</p>
HTML,
            'meta' => [
                'youth_corner_category' => 'Startups',
                'youth_corner_content_type' => 'Startup Story',
                'youth_corner_age_group' => '26-30',
                'youth_corner_occupation' => 'Entrepreneur',
                'youth_corner_education_level' => 'Graduate',
                'youth_corner_target_audience' => ['Entrepreneurs', 'Young Professionals', 'Students'],
                'youth_corner_startup_name' => 'EcoBox Packaging',
                'youth_corner_startup_industry' => 'Sustainable Packaging',
                'youth_corner_business_idea' => 'Compostable mailers and void-fill made from plant starch blends, sold in small MOQs for D2C and artisan brands in Rajasthan.',
                'youth_corner_funding_stage' => 'Angel Investment',
                'youth_corner_startup_challenges' => "Lab-to-factory scale-up took longer than pitch decks suggested.\nMonsoon humidity ruined our first production batch.\nFinding packaging engineers willing to mentor part-time was harder than raising small grants.",
                'youth_corner_startup_lessons' => "Pilot with paying customers, not free samples.\nDocument unit economics weekly — vanity metrics hide margin leaks.\nBuild advisor relationships before you desperately need intros.",
                'youth_corner_opportunity_types' => ['Startup Opportunity', 'Government Scheme', 'Training Program', 'Competition'],
                'youth_corner_skills' => ['Leadership', 'Communication', 'Digital Marketing', 'Finance', 'Sales'],
                'youth_corner_career_area' => 'Business',
                'youth_corner_themes' => ['Entrepreneurship', 'Innovation', 'Environment', 'Financial Literacy'],
                'youth_corner_networking_options' => ['Connect With Me', 'Offer Mentorship', 'Discuss Opportunities'],
                'youth_corner_mentorship_requests' => ['Startup Guidance', 'Business Mentorship', 'Career Guidance'],
                'youth_corner_gallery' => [
                    $this->seedGalleryImage('yc-startup-1', 'Product - Compostable mailer samples.jpg'),
                    $this->seedGalleryImage('yc-startup-2', 'Team - Founders at showcase.jpg'),
                ],
                'youth_corner_video_type' => 'Startup Pitch',
                'youth_corner_visibility' => 'public',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function careerAdvicePost(): array
    {
        return [
            'slug' => 'youth-corner-government-exam-roadmap',
            'category' => 'Competitive Exams',
            'writing_purpose' => 'Help Community',
            'title' => 'Government Exam Roadmap for Rural Youth: RAS, REET, and Banking Paths',
            'excerpt' => 'Structured advice on choosing between state services, teaching eligibility, and banking exams — with timelines, resources, and mentorship channels for first-generation aspirants.',
            'featured_image' => 'https://picsum.photos/seed/youth-corner-career/1200/630',
            'tags' => ['Career', 'Exams', 'Education', 'Government Jobs', 'Guidance'],
            'days_ago' => 8,
            'allow_comments' => true,
            'allow_questions' => true,
            'allow_feedback' => true,
            'body' => <<<'HTML'
<h2>Problem / Challenge</h2>
<p>Many rural youth hear about "government jobs" but lack a clear map of which exam fits their degree, district quota rules, and preparation windows.</p>
<h2>What I Did</h2>
<p>I compiled a one-page decision tree after mentoring 30 aspirants in Sanganer block study circles. We tracked attempt calendars, interview stages, and document checklists for RAS, REET, and IBPS clerical tracks.</p>
<h2>Results</h2>
<p>Twelve mentees cleared REET Level 2, four reached RAS mains, and our shared Google Sheet now has 200+ annotated resource links maintained by volunteers.</p>
<h2>Advice for Others</h2>
<p>Pick one primary exam per year. Parallel prep spreads focus thin. Join a local test series group for accountability — loneliness derails more aspirants than syllabus difficulty.</p>
HTML,
            'meta' => [
                'youth_corner_category' => 'Competitive Exams',
                'youth_corner_content_type' => 'Career Advice',
                'youth_corner_age_group' => '19-25',
                'youth_corner_occupation' => 'Professional',
                'youth_corner_education_level' => 'Graduate',
                'youth_corner_target_audience' => ['Students', 'Job Seekers', 'Young Professionals'],
                'youth_corner_opportunity_types' => ['Government Scheme', 'Training Program', 'Scholarship', 'Job Opportunity'],
                'youth_corner_skills' => ['Writing', 'Communication', 'Public Speaking', 'Management'],
                'youth_corner_career_area' => 'Government Services',
                'youth_corner_themes' => ['Education', 'Development', 'Community'],
                'youth_corner_community_service' => ['Teaching / Tutoring', 'Awareness Campaign'],
                'youth_corner_ask_community' => 'Which government exam preparation strategy worked best for you while working a part-time job?',
                'youth_corner_mentorship_requests' => ['Career Guidance', 'Exam Preparation'],
                'youth_corner_documents' => [
                    $this->seedDocument('Exam Timeline Cheatsheet.pdf'),
                    $this->seedDocument('Document Checklist.pdf'),
                ],
                'youth_corner_visibility' => 'public',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function privateLinkResourcePost(): array
    {
        return [
            'slug' => 'youth-corner-private-interview-prep-notes',
            'category' => 'Career & Jobs',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'Private Interview Prep Notes for My Study Circle',
            'excerpt' => 'Confidential HR and technical interview frameworks shared privately with youth job seekers in our WhatsApp study group.',
            'featured_image' => 'https://picsum.photos/seed/youth-corner-private/1200/630',
            'tags' => ['Interview', 'Career', 'Jobs', 'Preparation'],
            'days_ago' => 4,
            'allow_comments' => true,
            'allow_feedback' => true,
            'allow_sharing' => false,
            'publish_as' => CommunityPost::PUBLISH_AS_PEN_NAME,
            'pen_name' => 'Career Compass',
            'body' => <<<'HTML'
<h2>Introduction</h2>
<p>These are the STAR story templates and salary negotiation scripts I refined while coaching 12 friends through campus placements.</p>
<h2>Main Content</h2>
<p>Each section covers resume bullets, common HR traps, and technical follow-up questions for IT services and product companies.</p>
<h2>Advice for Others</h2>
<p>Record mock interviews on phone — hearing your filler words is uncomfortable but transformative. Negotiate respectfully; many offers have flex bands recruiters forget to mention.</p>
HTML,
            'meta' => [
                'youth_corner_category' => 'Career & Jobs',
                'youth_corner_content_type' => 'Experience Sharing',
                'youth_corner_age_group' => '19-25',
                'youth_corner_occupation' => 'Job Seeker',
                'youth_corner_education_level' => 'Undergraduate',
                'youth_corner_target_audience' => ['Students', 'Job Seekers'],
                'youth_corner_skills' => ['Communication', 'Writing', 'Management'],
                'youth_corner_gallery' => [
                    $this->seedGalleryImage('yc-notes-1', 'Notes - STAR framework.jpg'),
                    $this->seedGalleryImage('yc-notes-2', 'Template - Salary negotiation.jpg'),
                ],
                'youth_corner_video_type' => 'Skill Demonstration',
                'youth_corner_visibility' => 'private_link',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function youthCommunityVisibilityPost(): array
    {
        return [
            'slug' => 'youth-corner-mental-health-peer-support',
            'category' => 'Mental Health',
            'writing_purpose' => 'Help Community',
            'title' => 'Building Peer Support Circles for Exam Stress and Burnout',
            'excerpt' => 'A youth volunteer shares how we run weekly peer listening circles for exam-season stress — open to registered Youth Community members.',
            'featured_image' => 'https://picsum.photos/seed/youth-corner-mental-health/1200/630',
            'tags' => ['Mental Health', 'Wellness', 'Youth', 'Support', 'Community'],
            'days_ago' => 10,
            'allow_comments' => true,
            'allow_questions' => true,
            'allow_feedback' => true,
            'allow_poll' => true,
            'body' => <<<'HTML'
<h2>Problem / Challenge</h2>
<p>Exam seasons spike anxiety in our college hostels, but professional counselling slots are limited and many students hesitate to seek help publicly.</p>
<h2>What I Did</h2>
<p>We trained 8 peer listeners through a NGO workshop, set confidentiality ground rules, and host Friday evening circles with a licensed counsellor on call for escalations.</p>
<h2>Results</h2>
<p>Attendance grew from 6 to 35 students per session. Three participants later trained as listeners themselves.</p>
<h2>Advice for Others</h2>
<p>Peer support complements professional care — it does not replace it. Always publish crisis helplines and never promise confidentiality when someone is at risk of harm.</p>
HTML,
            'meta' => [
                'youth_corner_category' => 'Mental Health',
                'youth_corner_content_type' => 'Awareness Post',
                'youth_corner_age_group' => '19-25',
                'youth_corner_occupation' => 'Social Worker',
                'youth_corner_education_level' => 'Postgraduate',
                'youth_corner_target_audience' => ['Students', 'Young Professionals', 'Youth Leaders'],
                'youth_corner_themes' => ['Mental Health', 'Community', 'Leadership'],
                'youth_corner_community_service' => ['Volunteer Work', 'Awareness Campaign', 'Community Development'],
                'youth_corner_networking_options' => ['Seek Guidance', 'Connect With Me'],
                'youth_corner_ask_community' => 'What wellness routines help you stay balanced during competitive exam preparation?',
                'youth_corner_poll_question' => 'Which support format would you prefer?',
                'youth_corner_poll_options' => ['Peer circles', 'Online webinars', 'One-on-one mentoring', 'Self-help resources'],
                'youth_corner_visibility' => 'youth_community',
            ],
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function seedGalleryImage(string $seed, string $name): array
    {
        return [
            'path' => 'seed/youth-corner/'.$seed.'.jpg',
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
            'path' => 'seed/youth-corner/'.Str::slug(pathinfo($name, PATHINFO_FILENAME)).'.'.pathinfo($name, PATHINFO_EXTENSION),
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
