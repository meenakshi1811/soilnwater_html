<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;

class CommunityCompetitionsPostSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = $this->user('SoilnWater Competitions Desk', 'competitions-desk@example.com');
        $ngoLead = $this->user('Green Earth Foundation', 'competitions-green@example.com');
        $schoolCoord = $this->user('Delhi Public School Coordinator', 'competitions-school@example.com');
        $startupHub = $this->user('Rural Innovation Hub', 'competitions-startup@example.com');

        foreach ($this->competitionPosts() as $post) {
            $author = match ($post['author'] ?? 'desk') {
                'green' => $ngoLead,
                'school' => $schoolCoord,
                'startup' => $startupHub,
                default => $organizer,
            };

            $this->upsertPost($author, $post);
        }
    }

    private function user(string $name, string $email): User
    {
        return User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt('password')]
        );
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): CommunityPost
    {
        $location = $post['location'];
        $category = $post['category'];
        $competitionType = $post['competition_type'];

        $meta = array_merge($this->baseMeta($competitionType, $category), $post['meta'] ?? []);

        return CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'competitions',
                'category' => $category,
                'writing_purpose' => 'Promote Discussion',
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'body' => $post['body'],
                'featured_image_path' => $post['featured_image'],
                'featured_images' => $post['featured_images'] ?? [$post['featured_image']],
                'tags' => $post['tags'],
                'location_type' => CommunityPost::LOCATION_TYPE_INDIA,
                'location' => $location['label'],
                'location_lat' => $location['lat'],
                'location_lng' => $location['lng'],
                'publish_as' => CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
                'meta' => array_filter($meta, fn ($value) => filled($value) || is_bool($value) || is_array($value)),
                'allow_comments' => true,
                'allow_questions' => true,
                'allow_suggestions' => true,
                'allow_feedback' => true,
                'allow_sharing' => true,
                'allow_poll' => false,
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => now()->subDays($post['days_ago']),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function baseMeta(string $competitionType, string $category): array
    {
        return [
            'author_bio' => 'SoilnWater competition organizer promoting community contests across education, environment, and innovation.',
            'editor_language' => 'en',
            'competitions_competition_type' => $competitionType,
            'competitions_category' => $category,
            'competitions_declaration_original' => true,
            'competitions_declaration_permission' => true,
            'competitions_declaration_ai_disclosed' => true,
            'competitions_declaration_rules' => true,
            'competitions_declaration_display' => true,
            'competitions_comment_settings' => ['Comments', 'Questions', 'Feedback'],
            'competitions_ai_used' => 'No',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function competitionPosts(): array
    {
        return [
            $this->waterConservationPhotographyChallenge(),
            $this->nationalStudentEssayCompetition(),
            $this->agricultureStartupPitchChallenge(),
            $this->childrenTreePlantationDrawingCompetition(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function waterConservationPhotographyChallenge(): array
    {
        return [
            'slug' => 'competition-water-conservation-photography-challenge-2026',
            'author' => 'green',
            'competition_type' => 'Photography Competition',
            'category' => 'Environment',
            'title' => 'Save Water Photography Challenge 2026',
            'excerpt' => 'National photography competition celebrating water conservation stories from villages, schools, and communities across India.',
            'featured_image' => 'https://picsum.photos/seed/comp-water-photo/960/540',
            'tags' => ['Photography', 'Water', 'Environment', 'Competition', 'Innovation'],
            'days_ago' => 5,
            'location' => $this->location('India', 20.5937000, 78.9629000),
            'body' => $this->competitionBody(
                'Capture powerful images that show how communities save water, restore rivers, harvest rainwater, or reduce wastage.',
                'Save Water',
                'Open to students, youth, photographers, farmers, NGOs, and the general public across India.',
                'Submit 1–3 original photographs (JPG/PNG, max 10 MB each) with a short caption describing the location and impact.',
                'Creativity, originality, environmental impact, and presentation quality.',
                'Winner: ₹25,000 + featured homepage showcase. Runner-up and Top 10 receive certificates and badges.',
                'Registration opens 1 July 2026. Submissions close 31 August 2026. Results on 15 September 2026.'
            ),
            'meta' => [
                'competitions_organizer_name' => 'Green Earth Foundation',
                'competitions_organizer_organization' => 'Green Earth Foundation',
                'competitions_organizer_contact_person' => 'Anjali Rao',
                'competitions_organizer_email' => 'competitions@greenearth.example',
                'competitions_organizer_phone' => '+91 98765 43210',
                'competitions_organizer_website' => 'https://example.com/green-earth',
                'competitions_eligibility' => ['Students', 'Youth', 'Open to Everyone', 'NGOs'],
                'competitions_themes' => ['Save Water', 'Green Earth'],
                'competitions_submission_types' => ['Photo', 'Image'],
                'competitions_max_files' => 3,
                'competitions_max_file_size' => '10 MB',
                'competitions_allowed_formats' => 'JPG, PNG',
                'competitions_level' => 'National',
                'competitions_date_announcement' => now()->subDays(5)->toDateString(),
                'competitions_date_registration_opens' => now()->addDays(10)->toDateString(),
                'competitions_date_registration_closes' => now()->addDays(40)->toDateString(),
                'competitions_date_submission_deadline' => now()->addDays(45)->toDateString(),
                'competitions_date_evaluation_period' => now()->addDays(46)->toDateString().' – '.now()->addDays(55)->toDateString(),
                'competitions_date_result' => now()->addDays(60)->toDateString(),
                'competitions_date_award_ceremony' => now()->addDays(65)->toDateString(),
                'competitions_registration_required' => true,
                'competitions_registration_fee' => 'Free',
                'competitions_max_participants' => 5000,
                'competitions_team_allowed' => false,
                'competitions_individual_only' => true,
                'competitions_entry_fields' => ['Title', 'Description', 'Images'],
                'competitions_supporting_documents' => ['Identity Proof (Optional)'],
                'competitions_judging_criteria' => ['Creativity', 'Originality', 'Environmental Impact', 'Presentation'],
                'competitions_judging_weightage' => "Creativity: 25%\nEnvironmental Impact: 35%\nOriginality: 20%\nPresentation: 20%",
                'competitions_jury' => [
                    [
                        'name' => 'Prof. Meera Iyer',
                        'designation' => 'Environmental Photographer',
                        'organization' => 'National Photo Institute',
                        'profile' => 'Documentary photographer focused on water and rural India.',
                    ],
                    [
                        'name' => 'Rahul Verma',
                        'designation' => 'Water Conservation Expert',
                        'organization' => 'Jal Shakti Volunteers',
                        'profile' => 'Community water activist with 15 years of field experience.',
                    ],
                ],
                'competitions_prize_first' => '₹25,000 + Winner Certificate + Featured on Homepage',
                'competitions_prize_second' => '₹15,000 + Runner-Up Certificate',
                'competitions_prize_third' => '₹10,000 + Merit Certificate',
                'competitions_prize_consolation' => 'Top 10 receive Community Choice badges',
                'competitions_prize_certificates' => true,
                'competitions_prize_cash' => true,
                'competitions_prize_featured_homepage' => true,
                'competitions_certificate_participation' => true,
                'competitions_certificate_winner' => true,
                'competitions_certificate_merit' => true,
                'competitions_certificate_digital' => true,
                'competitions_sponsors' => [
                    [
                        'name' => 'AquaLife NGO',
                        'website' => 'https://example.com/aqualife',
                        'contribution' => 'Prize pool sponsor',
                    ],
                ],
                'competitions_voting_system' => 'Judges + Public',
                'competitions_public_voting_methods' => ['Like', 'Vote', 'Rating'],
                'competitions_copyright_options' => ['Participant Retains Copyright', 'Organizer May Display', 'Organizer May Promote'],
                'competitions_enable_multi_section' => true,
                'competitions_origin_sections' => ['Photography', 'Environment', 'Creative Corner'],
                'competitions_primary_origin_section' => 'Environment',
                'competitions_enable_auto_portfolio' => true,
                'competitions_enable_entry_qr_codes' => true,
                'competitions_enable_achievement_badges' => true,
                'competitions_award_badges' => ['Winner', 'Runner-Up', 'Top 10', 'Community Choice', 'Best Photographer', 'Water Warrior'],
                'competitions_enable_leaderboards' => true,
                'competitions_leaderboard_types' => ['States', 'Districts', 'Individual Participants', 'Schools'],
                'competitions_enable_institution_dashboard' => true,
                'competitions_institution_dashboard_notes' => 'Schools can register student batches and download participation certificates from the institution dashboard.',
                'competitions_enable_sponsored_branding' => true,
                'competitions_sponsored_branding_notes' => 'AquaLife NGO branding appears on the competition page, certificates, and result announcements.',
                'competitions_enable_ecommerce' => true,
                'competitions_ecommerce_options' => ['Licensed', 'Printed on merchandise'],
                'competitions_enable_voting_fraud_protection' => true,
                'competitions_voting_fraud_protections' => ['Verified accounts only', 'One vote per user', 'CAPTCHA', 'Duplicate detection', 'Admin override for suspicious activity'],
                'competitions_enable_digital_certificates' => true,
                'competitions_digital_certificate_types' => ['Participation Certificates', 'Winner Certificates', 'Downloadable PDFs', 'Verifiable certificate IDs', 'Certificate QR codes'],
                'competitions_enable_verifiable_certificate_ids' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function nationalStudentEssayCompetition(): array
    {
        return [
            'slug' => 'competition-national-student-essay-water-soil-2026',
            'author' => 'school',
            'competition_type' => 'Essay Competition',
            'category' => 'Education',
            'title' => 'National Student Essay Contest — Water & Soil for Future Generations',
            'excerpt' => 'Essay competition for school and college students on water conservation, soil health, and sustainable agriculture.',
            'featured_image' => 'https://picsum.photos/seed/comp-student-essay/960/540',
            'tags' => ['Essay', 'Students', 'Water', 'Competition', 'Education'],
            'days_ago' => 12,
            'location' => $this->location('New Delhi, India', 28.6139000, 77.2090000),
            'body' => $this->competitionBody(
                'Write an original essay on how young citizens can protect water and soil resources in their community.',
                'Save Water · Save Soil',
                'Students from Class 6 to postgraduate level across India.',
                'Submit one essay (500–1500 words) as PDF or rich text. Plagiarism checks apply.',
                'Originality, clarity, practical ideas, and environmental awareness.',
                'National winner receives scholarship support, internship opportunity, and merit certificate.',
                'Registration until 20 July 2026. Results announced 10 August 2026.'
            ),
            'meta' => [
                'competitions_organizer_name' => 'Delhi Public School Network',
                'competitions_organizer_organization' => 'DPS Education Alliance',
                'competitions_organizer_contact_person' => 'Dr. Sanjay Mehta',
                'competitions_organizer_email' => 'essay-contest@dps-example.edu',
                'competitions_eligibility' => ['Students', 'Children'],
                'competitions_themes' => ['Save Water', 'Save Soil', 'Smart Agriculture'],
                'competitions_submission_types' => ['Article', 'PDF'],
                'competitions_max_files' => 1,
                'competitions_max_file_size' => '5 MB',
                'competitions_allowed_formats' => 'PDF, DOCX',
                'competitions_level' => 'National',
                'competitions_date_registration_opens' => now()->subDays(10)->toDateString(),
                'competitions_date_registration_closes' => now()->addDays(25)->toDateString(),
                'competitions_date_submission_deadline' => now()->addDays(30)->toDateString(),
                'competitions_date_result' => now()->addDays(50)->toDateString(),
                'competitions_registration_required' => true,
                'competitions_registration_fee' => 'Free',
                'competitions_max_participants' => 10000,
                'competitions_team_allowed' => false,
                'competitions_individual_only' => true,
                'competitions_entry_fields' => ['Title', 'Description', 'Documents'],
                'competitions_supporting_documents' => ['Student ID', 'College ID'],
                'competitions_judging_criteria' => ['Originality', 'Presentation', 'Social Impact', 'Practical Utility'],
                'competitions_judging_weightage' => "Originality: 30%\nPractical Utility: 30%\nPresentation: 20%\nSocial Impact: 20%",
                'competitions_prize_first' => '₹50,000 scholarship + internship',
                'competitions_prize_second' => '₹30,000 scholarship',
                'competitions_prize_third' => '₹20,000 scholarship',
                'competitions_prize_scholarship' => true,
                'competitions_prize_internship' => true,
                'competitions_certificate_participation' => true,
                'competitions_certificate_winner' => true,
                'competitions_voting_system' => 'Judges Only',
                'competitions_enable_multi_section' => true,
                'competitions_origin_sections' => ['Students', 'Education', 'Environment', 'Agriculture'],
                'competitions_primary_origin_section' => 'Students',
                'competitions_enable_auto_portfolio' => true,
                'competitions_enable_entry_qr_codes' => true,
                'competitions_enable_achievement_badges' => true,
                'competitions_award_badges' => ['Winner', 'Runner-Up', 'Finalist', 'Top 100', 'Best Writer'],
                'competitions_enable_leaderboards' => true,
                'competitions_leaderboard_types' => ['Schools', 'Colleges', 'States', 'Individual Participants'],
                'competitions_enable_institution_dashboard' => true,
                'competitions_institution_dashboard_notes' => 'Schools and colleges can track submissions, rankings, and download certificates for all participants.',
                'competitions_enable_digital_certificates' => true,
                'competitions_digital_certificate_types' => ['Participation Certificates', 'Winner Certificates', 'Merit Certificates', 'Downloadable PDFs'],
                'competitions_enable_verifiable_certificate_ids' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function agricultureStartupPitchChallenge(): array
    {
        return [
            'slug' => 'competition-agriculture-startup-pitch-challenge-2026',
            'author' => 'startup',
            'competition_type' => 'Startup Pitch Competition',
            'category' => 'Business',
            'title' => 'Smart Agriculture Startup Pitch Challenge 2026',
            'excerpt' => 'Pitch your agri-tech or rural innovation startup to win funding visibility, mentorship, and SoilnWater marketplace listing.',
            'featured_image' => 'https://picsum.photos/seed/comp-agri-startup/960/540',
            'tags' => ['Innovation', 'Agriculture', 'Business', 'Competition', 'Technology'],
            'days_ago' => 3,
            'location' => $this->location('Pune, Maharashtra, India', 18.5204000, 73.8567000),
            'body' => $this->competitionBody(
                'Present a startup idea or prototype that improves farming productivity, soil health, water use, or rural livelihoods.',
                'Smart Agriculture · Innovation',
                'Youth, farmers, professionals, businesses, and student teams.',
                'Submit pitch deck (PDF/PPT), optional prototype video, and 3-minute pitch summary.',
                'Innovation, practical utility, scalability, and social impact.',
                'Winner receives seed funding visibility, bank mentorship, and marketplace listing opportunity.',
                'Semi-finals in August 2026. Grand finale in September 2026.'
            ),
            'meta' => [
                'competitions_organizer_name' => 'Rural Innovation Hub',
                'competitions_organizer_organization' => 'Rural Innovation Hub',
                'competitions_organizer_contact_person' => 'Kavita Deshmukh',
                'competitions_organizer_email' => 'pitch@ruralinnovation.example',
                'competitions_organizer_phone' => '+91 91234 56789',
                'competitions_eligibility' => ['Youth', 'Students', 'Farmers', 'Professionals', 'Businesses'],
                'competitions_themes' => ['Smart Agriculture', 'Innovation', 'Technology for Rural India'],
                'competitions_submission_types' => ['Presentation', 'PDF', 'Video', 'Prototype'],
                'competitions_max_files' => 5,
                'competitions_max_file_size' => '25 MB',
                'competitions_allowed_formats' => 'PDF, PPT, PPTX, MP4',
                'competitions_level' => 'National',
                'competitions_date_registration_opens' => now()->subDays(3)->toDateString(),
                'competitions_date_registration_closes' => now()->addDays(35)->toDateString(),
                'competitions_date_submission_deadline' => now()->addDays(40)->toDateString(),
                'competitions_date_result' => now()->addDays(70)->toDateString(),
                'competitions_registration_required' => true,
                'competitions_registration_fee' => '₹500 per team',
                'competitions_max_participants' => 500,
                'competitions_team_allowed' => true,
                'competitions_team_min_members' => 2,
                'competitions_team_max_members' => 5,
                'competitions_team_details' => ['Team Leader', 'Institution', 'Organization'],
                'competitions_entry_fields' => ['Title', 'Description', 'Files', 'Video', 'Documents'],
                'competitions_judging_criteria' => ['Innovation', 'Practical Utility', 'Social Impact', 'Presentation'],
                'competitions_judging_weightage' => "Innovation: 30%\nImpact: 30%\nPresentation: 20%\nPractical Utility: 20%",
                'competitions_jury' => [
                    [
                        'name' => 'Arun Patil',
                        'designation' => 'Agri-Tech Investor',
                        'organization' => 'Harvest Capital',
                        'profile' => 'Early-stage investor in rural innovation and agri-tech startups.',
                    ],
                ],
                'competitions_prize_first' => '₹2,00,000 seed grant visibility + mentorship',
                'competitions_prize_second' => '₹1,00,000',
                'competitions_prize_third' => '₹50,000',
                'competitions_prize_cash' => true,
                'competitions_prize_internship' => true,
                'competitions_sponsors' => [
                    [
                        'name' => 'AgriGrow Cooperative',
                        'website' => 'https://example.com/agrigrow',
                        'contribution' => 'Smart Farming sponsor',
                    ],
                    [
                        'name' => 'Rural Bank Partners',
                        'website' => 'https://example.com/ruralbank',
                        'contribution' => 'Startup Challenge by Bank',
                    ],
                ],
                'competitions_voting_system' => 'Expert Panel',
                'competitions_enable_multi_section' => true,
                'competitions_origin_sections' => ['Business', 'Agriculture', 'Technology', 'Youth'],
                'competitions_primary_origin_section' => 'Business',
                'competitions_enable_auto_portfolio' => true,
                'competitions_enable_entry_qr_codes' => true,
                'competitions_enable_achievement_badges' => true,
                'competitions_award_badges' => ['Winner', 'Innovation Award', 'Most Creative'],
                'competitions_enable_leaderboards' => true,
                'competitions_leaderboard_types' => ['Colleges', 'Organizations', 'States'],
                'competitions_enable_sponsored_branding' => true,
                'competitions_sponsored_branding_notes' => 'AgriGrow and Rural Bank branding on all pitch materials and finale event.',
                'competitions_enable_ecommerce' => true,
                'competitions_ecommerce_options' => ['Sold through SoilnWater Marketplace', 'Licensed'],
                'competitions_enable_digital_certificates' => true,
                'competitions_digital_certificate_types' => ['Participation Certificates', 'Winner Certificates', 'Jury Appreciation Certificates'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function childrenTreePlantationDrawingCompetition(): array
    {
        return [
            'slug' => 'competition-children-tree-plantation-drawing-2026',
            'author' => 'desk',
            'competition_type' => 'Drawing Competition',
            'category' => 'Children',
            'title' => 'Tree Plantation Drawing Competition for Children',
            'excerpt' => 'A joyful drawing contest for children to imagine greener villages, schools, and neighbourhoods through art.',
            'featured_image' => 'https://picsum.photos/seed/comp-children-drawing/960/540',
            'tags' => ['Children', 'Drawing', 'Environment', 'Competition'],
            'days_ago' => 8,
            'location' => $this->location('Bengaluru, Karnataka, India', 12.9716000, 77.5946000),
            'body' => $this->competitionBody(
                'Children are invited to draw their vision of tree plantation drives, green schools, and clean neighbourhoods.',
                'Green Earth · Tree Plantation',
                'Children aged 6–14 years. Parent or guardian consent required.',
                'Submit one drawing scan or photo (JPG/PNG). Hand-drawn work only.',
                'Creativity, theme relevance, and presentation.',
                'Winners receive art kits, certificates, and school recognition badges.',
                'Submissions close 25 July 2026.'
            ),
            'meta' => [
                'competitions_organizer_name' => 'SoilnWater Competitions Desk',
                'competitions_organizer_organization' => 'SoilnWater Community',
                'competitions_organizer_email' => 'competitions@soilnwater.example',
                'competitions_eligibility' => ['Children', 'Students'],
                'competitions_themes' => ['Green Earth', 'Save Soil'],
                'competitions_submission_types' => ['Drawing', 'Image', 'Photo'],
                'competitions_max_files' => 1,
                'competitions_max_file_size' => '8 MB',
                'competitions_allowed_formats' => 'JPG, PNG',
                'competitions_level' => 'District',
                'competitions_date_submission_deadline' => now()->addDays(28)->toDateString(),
                'competitions_date_result' => now()->addDays(40)->toDateString(),
                'competitions_registration_required' => true,
                'competitions_registration_fee' => 'Free',
                'competitions_max_participants' => 2000,
                'competitions_individual_only' => true,
                'competitions_entry_fields' => ['Title', 'Description', 'Images'],
                'competitions_supporting_documents' => ['Consent Form', 'Student ID'],
                'competitions_judging_criteria' => ['Creativity', 'Originality', 'Presentation'],
                'competitions_prize_first' => 'Art kit + Winner certificate',
                'competitions_prize_certificates' => true,
                'competitions_certificate_participation' => true,
                'competitions_certificate_winner' => true,
                'competitions_voting_system' => 'Public Voting',
                'competitions_public_voting_methods' => ['Like', 'Vote'],
                'competitions_enable_multi_section' => true,
                'competitions_origin_sections' => ['Children', 'Creative Corner', 'Environment'],
                'competitions_primary_origin_section' => 'Children',
                'competitions_enable_auto_portfolio' => true,
                'competitions_enable_entry_qr_codes' => true,
                'competitions_enable_achievement_badges' => true,
                'competitions_award_badges' => ['Winner', 'Most Creative', 'Green Champion'],
                'competitions_enable_leaderboards' => true,
                'competitions_leaderboard_types' => ['Schools', 'Cities', 'Individual Participants'],
                'competitions_enable_institution_dashboard' => true,
                'competitions_enable_voting_fraud_protection' => true,
                'competitions_voting_fraud_protections' => ['Verified accounts only', 'One vote per user', 'CAPTCHA'],
                'competitions_enable_digital_certificates' => true,
                'competitions_digital_certificate_types' => ['Participation Certificates', 'Winner Certificates', 'Digital certificate (automatically generated)'],
            ],
        ];
    }

    /**
     * @return array{label: string, lat: float, lng: float}
     */
    private function location(string $label, float $lat, float $lng): array
    {
        return ['label' => $label, 'lat' => $lat, 'lng' => $lng];
    }

    private function competitionBody(
        string $objective,
        string $theme,
        string $whoCanParticipate,
        string $submissionRequirements,
        string $judgingCriteria,
        string $prizes,
        string $importantDates
    ): string {
        return implode("\n", [
            '<h2>Objective</h2>',
            '<p>'.$objective.'</p>',
            '<h2>Theme</h2>',
            '<p>'.$theme.'</p>',
            '<h2>Who Can Participate</h2>',
            '<p>'.$whoCanParticipate.'</p>',
            '<h2>Submission Requirements</h2>',
            '<p>'.$submissionRequirements.'</p>',
            '<h2>Judging Criteria</h2>',
            '<p>'.$judgingCriteria.'</p>',
            '<h2>Prizes</h2>',
            '<p>'.$prizes.'</p>',
            '<h2>Important Dates</h2>',
            '<p>'.$importantDates.'</p>',
        ]);
    }
}
