<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;

class CommunityAstroConsultancyPostSeeder extends Seeder
{
    public function run(): void
    {
        $vedicAstrologer = $this->user('Pandit Vijay Sharma', 'vedic-astrologer@example.com');
        $vastuExpert = $this->user('Dr. Anjali Vastu', 'vastu-expert@example.com');
        $numerologist = $this->user('Rakesh Numerology', 'numerologist-rakesh@example.com');
        $spiritualGuide = $this->user('Meera Spiritual Guide', 'spiritual-meera@example.com');
        $horoscopeAuthor = $this->user('Horoscope Desk SoilnWater', 'horoscope-desk@example.com');

        foreach ($this->astroConsultancyPosts() as $post) {
            $author = match ($post['author'] ?? 'vijay') {
                'anjali' => $vastuExpert,
                'rakesh' => $numerologist,
                'meera' => $spiritualGuide,
                'horoscope' => $horoscopeAuthor,
                default => $vedicAstrologer,
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
        $postType = $post['post_type'];

        $meta = array_merge($this->baseMeta($postType, $category), $post['meta'] ?? []);

        if (isset($post['video']['label'])) {
            $meta['astro_consultancy_video_type'] = $post['video']['label'];
        }

        return CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'astro-consultancy',
                'category' => $category,
                'writing_purpose' => $postType,
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'body' => $post['body'],
                'featured_image_path' => $post['featured_image'],
                'featured_images' => $post['featured_images'] ?? [$post['featured_image']],
                'tags' => $post['tags'],
                'location_type' => CommunityPost::LOCATION_TYPE_CITY,
                'location' => $location['label'],
                'location_lat' => $location['lat'],
                'location_lng' => $location['lng'],
                'video' => $post['video'] ?? null,
                'publish_as' => $post['publish_as'] ?? CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
                'pen_name' => $post['pen_name'] ?? null,
                'meta' => array_filter($meta, fn ($value) => filled($value) || is_bool($value) || is_array($value)),
                'allow_comments' => $post['allow_comments'] ?? true,
                'allow_questions' => $post['allow_questions'] ?? true,
                'allow_suggestions' => $post['allow_suggestions'] ?? true,
                'allow_feedback' => $post['allow_feedback'] ?? true,
                'allow_sharing' => $post['allow_sharing'] ?? true,
                'allow_poll' => $post['allow_poll'] ?? (bool) data_get($post['meta'] ?? [], 'astro_consultancy_allow_poll', false),
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => now()->subDays($post['days_ago']),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function baseMeta(string $postType, string $category): array
    {
        return [
            'author_bio' => 'Verified SoilnWater astro consultancy contributor sharing educational and traditional guidance.',
            'editor_language' => 'en',
            'astro_consultancy_post_type' => $postType,
            'astro_consultancy_category' => $category,
            'astro_consultancy_declaration_beliefs' => true,
            'astro_consultancy_declaration_no_false_claims' => true,
            'astro_consultancy_declaration_no_fear' => true,
            'astro_consultancy_declaration_guidelines' => true,
            'astro_consultancy_comment_settings' => CommunityContentTaxonomy::astroConsultancyCommentSettings(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function astroConsultancyPosts(): array
    {
        return [
            $this->vedicCareerEducationalArticle(),
            $this->weeklyAriesHoroscope(),
            $this->vastuHomeOfficeGuide(),
            $this->numerologyLifePathGuide(),
            $this->diwaliMuhuratFestival(),
            $this->gemstoneRubyGuidance(),
            $this->meditationSpiritualWellness(),
            $this->saturnInfluenceDiscussion(),
            $this->marriageGuidanceCaseStudy(),
            $this->verifiedConsultantLeadPost(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function vedicCareerEducationalArticle(): array
    {
        return [
            'slug' => 'astro-vedic-career-education-10th-house',
            'author' => 'vijay',
            'post_type' => 'Educational Article',
            'category' => 'Vedic Astrology',
            'title' => 'Understanding the 10th House for Career — An Educational Vedic Perspective',
            'excerpt' => 'A beginner-friendly guide to how the 10th house is traditionally interpreted for profession, reputation, and public life — without guaranteed predictions.',
            'featured_image' => 'https://picsum.photos/seed/astro-vedic-career/960/540',
            'tags' => ['Astrology', 'Career', 'Vedic', 'Education', '10th House'],
            'days_ago' => 21,
            'location' => $this->location('Ujjain, Madhya Pradesh, India', 'India', 'Madhya Pradesh', 'Ujjain', 'Ujjain', 23.1765000, 75.7885000),
            'video' => $this->youtubeVideo('Educational Lecture'),
            'body' => $this->astroBody(
                'Career questions are among the most common reasons people explore Vedic astrology. This article explains the 10th house as a traditional framework — not a fixed job guarantee.',
                'In classical texts, the 10th house (Karma Bhava) relates to profession, authority, public recognition, and the duties one performs in society. Planets, signs, and aspects modify the interpretation.',
                'The 10th house sits opposite the 4th house of inner comfort, symbolizing how private roots translate into public contribution. Saturn, Sun, and Mercury often receive attention here, but context matters.',
                'Traditional teachers emphasize that dashas, yogas, and the full chart must be read together. A strong 10th lord may suggest leadership aptitude; afflictions may indicate delays or course corrections — always as tendencies.',
                'Readers may journal career goals, seek mentorship, strengthen relevant skills, and use auspicious timing for interviews as supportive practices — alongside practical career planning.',
                'Astrology can inspire reflection on purpose and timing; it should complement — not replace — education, networking, and professional counsel.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['Students', 'Professionals', 'General Public'],
                'astro_consultancy_consultation_topics' => ['Career', 'Education', 'Personal Development'],
                'astro_consultancy_content_language' => 'English',
                'astro_consultancy_knowledge_library_topics' => ['Beginner Guides', 'Birth Chart Basics', 'Planetary Concepts'],
                'astro_consultancy_enable_consultant_linking' => true,
                'astro_consultancy_consultant_profile_url' => 'https://example.com/consultant/pandit-vijay-sharma',
                'astro_consultancy_related_service_actions' => ['Book Consultation', 'View Consultant Profile', 'Online Appointment'],
                'astro_consultancy_ask_community' => 'How do you balance traditional career indicators with modern skill-based planning?',
                'astro_consultancy_document_types' => ['PDF', 'Charts'],
                'astro_consultancy_documents' => [
                    $this->externalDocument('vedic-10th-house-reference-chart.pdf'),
                    $this->externalDocument('career-house-study-notes.pdf'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function weeklyAriesHoroscope(): array
    {
        return [
            'slug' => 'astro-weekly-horoscope-aries-june-2026',
            'author' => 'horoscope',
            'post_type' => 'Daily/Weekly/Monthly Horoscope',
            'category' => 'Horoscope',
            'title' => 'Weekly Horoscope for Aries — June 2026 (Educational Outlook)',
            'excerpt' => 'A reflective weekly overview for Aries rising and Sun sign readers. Presented as traditional opinion, not certainty.',
            'featured_image' => 'https://picsum.photos/seed/astro-aries-horoscope/960/540',
            'tags' => ['Horoscope', 'Aries', 'Weekly', 'Astrology', 'June 2026'],
            'days_ago' => 3,
            'location' => $this->location('Varanasi, Uttar Pradesh, India', 'India', 'Uttar Pradesh', 'Varanasi', 'Varanasi', 25.3176000, 82.9739000),
            'video' => $this->youtubeVideo('Daily Horoscope'),
            'body' => $this->astroBody(
                'This week\'s Aries outlook focuses on initiative, communication, and mindful pacing. Horoscopes are shared as cultural reflection, not fate.',
                'Mars-ruled Aries energy may feel assertive early in the week. Traditional texts associate this with starting projects — paired with the need to listen before acting.',
                'Mercury-related themes may highlight conversations with siblings, neighbours, or teammates. Journaling and clarifying agreements can be helpful practices.',
                'Relationship houses may invite patience. Classical teachers caution against impulsive reactions; breathwork and short walks can ground fiery tendencies.',
                'Consider reviewing budgets before mid-week purchases. Traditional timing favors planning over speculation.',
                'Use this horoscope as a prompt for self-awareness. Important decisions benefit from practical advice and your own judgment.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['General Public', 'Professionals'],
                'astro_consultancy_consultation_topics' => ['Career', 'Finance', 'Relationship'],
                'astro_consultancy_content_language' => 'Hindi',
                'astro_consultancy_zodiac_sign' => 'Aries',
                'astro_consultancy_horoscope_period' => 'Weekly',
                'astro_consultancy_allow_poll' => true,
                'astro_consultancy_poll_question' => 'Do you regularly read your horoscope?',
                'astro_consultancy_poll_options' => CommunityContentTaxonomy::astroConsultancyDefaultPollOptions(),
                'astro_consultancy_ask_community' => 'Fellow Aries — what practices help you channel Mars energy constructively?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function vastuHomeOfficeGuide(): array
    {
        return [
            'slug' => 'astro-vastu-home-office-north-east-workspace',
            'author' => 'anjali',
            'post_type' => 'Vastu Advice',
            'category' => 'Vastu Shastra',
            'title' => 'Vastu Tips for a Home Office — Entrance, Kitchen & Workspace Balance',
            'excerpt' => 'Educational Vastu guidance for freelancers and professionals working from home, with traditional rationale and practical adaptability.',
            'featured_image' => 'https://picsum.photos/seed/astro-vastu-home/960/540',
            'tags' => ['Vastu', 'Home Office', 'Workspace', 'Education', 'Property'],
            'days_ago' => 12,
            'location' => $this->location('Ahmedabad, Gujarat, India', 'India', 'Gujarat', 'Ahmedabad', 'Ahmedabad', 23.0225000, 72.5714000),
            'video' => $this->youtubeVideo('Vastu Tips'),
            'body' => $this->astroBody(
                'Many professionals now blend living and working spaces. Vastu offers traditional spatial principles intended to support clarity, health, and balanced energy flow.',
                'Classical Vastu considers plot shape, entrance direction, and the placement of key zones such as kitchen, bedroom, and workspace relative to the cardinal directions.',
                'The north-east is often associated with clarity and learning in several traditions; south-west with stability. These are symbolic frameworks, not rigid rules for every flat.',
                'For home offices, teachers suggest a dedicated desk, good daylight, and avoiding clutter facing the main door. Adapt principles to your floor plan without fear-based messaging.',
                'Simple practices: keep the workspace organized, separate work screens from sleep areas, and use plants or natural light where possible.',
                'Vastu is cultural guidance. Structural changes should follow building safety codes and professional architectural advice.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['Professionals', 'Business Owners', 'Parents'],
                'astro_consultancy_consultation_topics' => ['Property', 'Business', 'Career'],
                'astro_consultancy_content_language' => 'English',
                'astro_consultancy_vastu_property_types' => ['Home', 'Office', 'Apartment'],
                'astro_consultancy_vastu_areas' => ['Entrance', 'Kitchen', 'Bedroom', 'Living Room'],
                'astro_consultancy_knowledge_library_topics' => ['Vastu Learning', 'Beginner Guides'],
                'astro_consultancy_ask_community' => 'What are your experiences with Vastu planning in rented apartments?',
                'astro_consultancy_document_types' => ['Presentation', 'Charts'],
                'astro_consultancy_documents' => [
                    $this->externalDocument('vastu-home-office-layout-sample.pdf'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function numerologyLifePathGuide(): array
    {
        return [
            'slug' => 'astro-numerology-life-path-number-7',
            'author' => 'rakesh',
            'post_type' => 'Numerology Guidance',
            'category' => 'Numerology',
            'title' => 'Life Path Number 7 — Traits, Learning Style & Compatibility Notes',
            'excerpt' => 'An educational numerology overview for Life Path 7, including name numbers and compatibility themes — shared as reflective tradition.',
            'featured_image' => 'https://picsum.photos/seed/astro-numerology-7/960/540',
            'tags' => ['Numerology', 'Life Path', 'Education', 'Spirituality', 'Compatibility'],
            'days_ago' => 18,
            'location' => $this->location('Pune, Maharashtra, India', 'India', 'Maharashtra', 'Pune', 'Pune', 18.5204000, 73.8567000),
            'body' => $this->astroBody(
                'Numerology assigns symbolic meaning to numbers derived from birth dates and names. Life Path 7 is often linked with introspection, analysis, and spiritual curiosity.',
                'To calculate Life Path, practitioners reduce the birth date digits to a single digit (with master numbers 11, 22 sometimes preserved). This post uses 7 as an illustrative example.',
                'Traditional descriptions associate 7 with research, philosophy, and solitude needs. Strengths may include depth; challenges may include overthinking — always interpreted gently.',
                'Name number and destiny number modify the story. Compatibility discussions compare rhythms between partners or teams — as tendencies, not verdicts.',
                'Journaling, meditation, and structured study periods are commonly suggested reflective practices for 7 energy.',
                'Numerology supports self-understanding; it does not determine medical, legal, or financial outcomes.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['Students', 'Professionals', 'Couples'],
                'astro_consultancy_consultation_topics' => ['Personal Development', 'Relationship', 'Spiritual Growth'],
                'astro_consultancy_content_language' => 'English',
                'astro_consultancy_life_path_number' => '7',
                'astro_consultancy_destiny_number' => '5',
                'astro_consultancy_name_number' => '3',
                'astro_consultancy_lucky_number' => '16',
                'astro_consultancy_compatibility' => '7 with 2 and 9 — traditionally considered harmonious for communication and support',
                'astro_consultancy_knowledge_library_topics' => ['Numerology Learning', 'Beginner Guides'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function diwaliMuhuratFestival(): array
    {
        return [
            'slug' => 'astro-diwali-lakshmi-puja-muhurat-2026',
            'author' => 'vijay',
            'post_type' => 'Festival & Muhurat Information',
            'category' => 'Muhurat',
            'title' => 'Diwali 2026 — Lakshmi Puja Muhurat & Traditional Significance',
            'excerpt' => 'Festival timing notes and cultural background for Diwali Lakshmi Puja, shared for educational and devotional planning.',
            'featured_image' => 'https://picsum.photos/seed/astro-diwali-muhurat/960/540',
            'tags' => ['Festival', 'Diwali', 'Muhurat', 'Lakshmi Puja', 'Tradition'],
            'days_ago' => 45,
            'location' => $this->location('Ayodhya, Uttar Pradesh, India', 'India', 'Uttar Pradesh', 'Ayodhya', 'Ayodhya', 26.7922000, 82.1998000),
            'video' => $this->youtubeVideo('Festival Significance'),
            'body' => $this->astroBody(
                'Diwali celebrates light over darkness and is observed across regions with diverse local customs. Muhurat timings help families align rituals with traditional calendars.',
                'Lakshmi Puja is commonly performed on Amavasya night during Diwali. Regional panchangs may differ slightly — consult your local almanac for precise times.',
                'The festival connects to stories of return, prosperity, and gratitude. Lighting lamps symbolizes inviting clarity and removing stagnation.',
                'Pradosh Kaal and Vrishabha Lagna are among the factors traditional pandits evaluate when publishing muhurat tables.',
                'Families may clean homes, prepare sweets, share with neighbours, and pause for prayer — practices emphasizing generosity and renewal.',
                'Treat muhurat as supportive tradition. Travel and safety always take priority over rigid timing when conditions are difficult.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['Families', 'Parents', 'General Public'],
                'astro_consultancy_consultation_topics' => ['Family', 'Spiritual Growth'],
                'astro_consultancy_content_language' => 'Sanskrit',
                'astro_consultancy_festival_name' => 'Diwali — Lakshmi Puja',
                'astro_consultancy_muhurat_type' => 'Lakshmi Puja',
                'astro_consultancy_muhurat_date' => now()->year.'-11-01',
                'astro_consultancy_muhurat_time' => '6:15 PM – 8:05 PM (illustrative — verify local panchang)',
                'astro_consultancy_festival_significance' => 'Victory of light over darkness; worship of Lakshmi for prosperity and gratitude; renewal of home and community bonds.',
                'astro_consultancy_ask_community' => 'How do you choose an auspicious date for important events in your family tradition?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function gemstoneRubyGuidance(): array
    {
        return [
            'slug' => 'astro-gemstone-ruby-sun-traditional-guidance',
            'author' => 'vijay',
            'post_type' => 'Astrology Guidance',
            'category' => 'Gemstone Guidance',
            'title' => 'Ruby (Manik) & the Sun — Traditional Benefits, Timing & Precautions',
            'excerpt' => 'Educational gemstone guidance with traditional associations, ethical sourcing reminders, and cautions against fear-based selling.',
            'featured_image' => 'https://picsum.photos/seed/astro-ruby-gemstone/960/540',
            'tags' => ['Gemstone', 'Ruby', 'Sun', 'Astrology', 'Precautions'],
            'days_ago' => 9,
            'location' => $this->location('Jaipur, Rajasthan, India', 'India', 'Rajasthan', 'Jaipur', 'Jaipur', 26.9124000, 75.7873000),
            'body' => $this->astroBody(
                'Gemstones appear in several astrological traditions as symbolic supports for planetary themes. Ruby (Manik) is commonly linked with the Sun in North Indian practice.',
                'Classical texts associate the Sun with vitality, confidence, and leadership symbolism. Ruby is discussed as a traditional correspondent — not a medical treatment.',
                'Practitioners may suggest wearing ruby after chart review, metal choice, and carat weight considerations. Independent gemological certification helps avoid treated or synthetic misrepresentation.',
                'Traditional benefits are described as supporting self-esteem and clarity of purpose when the Sun is favourably placed — always as belief, not guarantee.',
                'Precautions: consult a qualified gemologist; avoid purchases driven by fear; do not delay necessary medical care; remove jewelry if skin irritation occurs.',
                'Gemstone use is optional cultural practice. Personal discernment and ethical sellers matter more than pressure tactics.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['Professionals', 'Business Owners', 'General Public'],
                'astro_consultancy_consultation_topics' => ['Career', 'Health', 'Finance'],
                'astro_consultancy_content_language' => 'Hindi',
                'astro_consultancy_gemstone' => 'Ruby (Manik)',
                'astro_consultancy_gemstone_planet' => 'Sun',
                'astro_consultancy_gemstone_benefits' => 'Traditionally associated with confidence, visibility, and leadership symbolism when worn after proper chart consideration.',
                'astro_consultancy_gemstone_precautions' => 'Verify authenticity; avoid fear-based selling; not a substitute for medical treatment; consult a qualified astrologer and gemologist.',
                'astro_consultancy_related_service_actions' => ['Ask Expert', 'Book Consultation'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function meditationSpiritualWellness(): array
    {
        return [
            'slug' => 'astro-meditation-breathwork-saturn-transits',
            'author' => 'meera',
            'post_type' => 'Meditation & Spiritual Wellness',
            'category' => 'Meditation',
            'title' => 'Meditation & Breathwork During Heavy Saturn Periods — A Gentle Guide',
            'excerpt' => 'Spiritual wellness practices for reflection during challenging transits, without fear or deterministic claims.',
            'featured_image' => 'https://picsum.photos/seed/astro-meditation-saturn/960/540',
            'tags' => ['Meditation', 'Spirituality', 'Wellness', 'Saturn', 'Breathwork'],
            'days_ago' => 6,
            'location' => $this->location('Rishikesh, Uttarakhand, India', 'India', 'Uttarakhand', 'Dehradun', 'Rishikesh', 30.0869000, 78.2676000),
            'video' => $this->youtubeVideo('Meditation Session'),
            'body' => $this->astroBody(
                'Astrological language sometimes describes Saturn periods as times of discipline and maturation. Spiritual traditions offer grounding practices that support calm reflection.',
                'Rather than fear, teachers invite steady routines: sleep regularity, honest work, and compassionate self-talk during slow phases.',
                'Breath awareness (pranayama) and seated meditation can help observers notice thoughts without identifying with catastrophic narratives.',
                'Saturn symbolism includes responsibility and boundaries — useful prompts for simplifying commitments and strengthening integrity.',
                'Suggested practices: 10-minute morning silence, evening gratitude list, walking in nature, and community seva (service).',
                'Spiritual wellness complements professional support for anxiety or depression. Seek qualified help when needed.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['Senior Citizens', 'Professionals', 'Parents'],
                'astro_consultancy_consultation_topics' => ['Spiritual Growth', 'Health', 'Personal Development'],
                'astro_consultancy_content_language' => 'Regional Language',
                'astro_consultancy_knowledge_library_topics' => ['Spiritual Practices', 'Planetary Concepts'],
                'astro_consultancy_enable_live_qa' => true,
                'astro_consultancy_private_query_options' => ['Request Consultation', 'Send Private Query'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function saturnInfluenceDiscussion(): array
    {
        return [
            'slug' => 'astro-discussion-saturn-traditions-community',
            'author' => 'vijay',
            'post_type' => 'Discussion',
            'category' => 'Vedic Astrology',
            'title' => 'How Do Different Traditions Interpret Saturn\'s Influence?',
            'excerpt' => 'Opening a community discussion on Saturn across Vedic, Western, and folk traditions — share experiences respectfully.',
            'featured_image' => 'https://picsum.photos/seed/astro-saturn-discussion/960/540',
            'tags' => ['Discussion', 'Saturn', 'Astrology', 'Community', 'Traditions'],
            'days_ago' => 2,
            'allow_poll' => true,
            'location' => $this->location('Chennai, Tamil Nadu, India', 'India', 'Tamil Nadu', 'Chennai', 'Chennai', 13.0827000, 80.2707000),
            'body' => $this->astroBody(
                'Saturn is interpreted differently across lineages. This thread invites comparative learning — not debate about who is "right."',
                'Vedic astrologers often discuss Shani as karmic teacher and disciplinarian. Remedies may include service, humility, and structured effort.',
                'Western astrology may emphasize Saturn as architect of boundaries, maturity, and long-term goals through transits and returns.',
                'Folk traditions sometimes personify Shani Dev with localized festivals and temple practices reflecting regional history.',
                'Share your tradition\'s view and personal practices that helped you navigate slow phases without spreading fear.',
                'Moderators remind contributors: no guaranteed predictions, no exploitation of vulnerable users, and respect for diverse beliefs.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['General Public', 'Students', 'Senior Citizens'],
                'astro_consultancy_consultation_topics' => ['Spiritual Growth', 'Personal Development'],
                'astro_consultancy_content_language' => 'English',
                'astro_consultancy_ask_community' => 'How do different traditions interpret Saturn\'s influence in your family or lineage?',
                'astro_consultancy_allow_poll' => true,
                'astro_consultancy_poll_question' => 'Which tradition do you primarily follow for Saturn remedies?',
                'astro_consultancy_poll_options' => ['Vedic', 'Western', 'Local folk practice', 'I avoid remedial claims'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function marriageGuidanceCaseStudy(): array
    {
        return [
            'slug' => 'astro-case-study-marriage-timing-educational',
            'author' => 'vijay',
            'post_type' => 'Case Study',
            'category' => 'KP Astrology',
            'title' => 'Case Study — Marriage Timing Indicators (Anonymized Educational Example)',
            'excerpt' => 'A anonymized teaching case on how KP astrologers discuss marriage timing — illustrative only, not a template for guaranteed outcomes.',
            'featured_image' => 'https://picsum.photos/seed/astro-marriage-case/960/540',
            'tags' => ['Case Study', 'Marriage', 'KP Astrology', 'Education', 'Timing'],
            'days_ago' => 30,
            'location' => $this->location('Kochi, Kerala, India', 'India', 'Kerala', 'Ernakulam', 'Kochi', 9.9312000, 76.2673000),
            'body' => $this->astroBody(
                'This anonymized case demonstrates how teachers explain marriage timing concepts in KP astrology classrooms.',
                'The native\'s chart showed sub-lord chains involving the 7th and 11th houses. Teachers emphasized ruling planets as discussion tools, not certainties.',
                'Dasha periods were reviewed alongside transits. Students learned to document hypotheses and revisit them after events unfold.',
                'The consultant discussed communication patterns observed in the partnership history — astrology as mirror, not verdict.',
                'Suggested practices included premarital counseling, financial transparency, and family dialogue alongside any chosen remedial prayers.',
                'Outcomes vary. This case is for learning methodology; readers should not expect identical results.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['Couples', 'Parents', 'General Public'],
                'astro_consultancy_consultation_topics' => ['Marriage', 'Relationship', 'Family'],
                'astro_consultancy_content_language' => 'English',
                'astro_consultancy_knowledge_library_topics' => ['Birth Chart Basics', 'Planetary Concepts'],
                'astro_consultancy_document_types' => ['Research Papers', 'Charts'],
                'astro_consultancy_documents' => [
                    $this->externalDocument('kp-marriage-timing-teaching-notes.pdf'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function verifiedConsultantLeadPost(): array
    {
        return [
            'slug' => 'astro-verified-consultant-book-appointment-soilnwater',
            'author' => 'anjali',
            'post_type' => 'Question & Answer',
            'category' => 'Life Guidance',
            'title' => 'Ask an Expert — Booking Consultations Through Verified SoilnWater Astrologers',
            'excerpt' => 'How community readers can move from posts to private consultations securely via the verified consultant directory.',
            'featured_image' => 'https://picsum.photos/seed/astro-consultant-directory/960/540',
            'tags' => ['Consultant', 'Appointment', 'SoilnWater', 'Q&A', 'Guidance'],
            'days_ago' => 1,
            'location' => $this->location('New Delhi, Delhi, India', 'India', 'Delhi', 'New Delhi', 'New Delhi', 28.6139000, 77.2090000),
            'body' => $this->astroBody(
                'SoilnWater connects educational community posts with verified consultants who offer appointments, packages, and secure private queries.',
                'Public posts are not the place for sensitive birth details. Use Request Consultation or Send Private Query to share data securely.',
                'Verified profiles may include QR codes, reviews, articles, and booking links — helping readers evaluate fit before paying.',
                'Consultants explain their tradition (Vedic, KP, Numerology, Vastu, etc.) and set expectations about educational vs predictive framing.',
                'Readers should confirm fees, session length, rescheduling policy, and disclaimer language before booking.',
                'Community guidance is cultural and educational; licensed professionals should be consulted for medical, legal, and financial decisions.'
            ),
            'meta' => [
                'astro_consultancy_target_audience' => ['General Public', 'Couples', 'Business Owners'],
                'astro_consultancy_consultation_topics' => ['Career', 'Marriage', 'Business', 'Property'],
                'astro_consultancy_content_language' => 'English',
                'astro_consultancy_enable_consultant_linking' => true,
                'astro_consultancy_consultant_profile_url' => 'https://example.com/consultant/dr-anjali-vastu',
                'astro_consultancy_related_service_actions' => [
                    'Book Consultation',
                    'Ask Expert',
                    'View Consultant Profile',
                    'Online Appointment',
                    'WhatsApp Consultation',
                ],
                'astro_consultancy_enable_live_qa' => true,
                'astro_consultancy_private_query_options' => [
                    'Request Consultation',
                    'Book Appointment',
                    'Send Private Query',
                ],
                'astro_consultancy_ask_community' => 'What do you look for when choosing an astrologer or Vastu consultant online?',
            ],
        ];
    }

    private function astroBody(
        string $introduction,
        string $traditionalBackground,
        string $astrologicalConcept,
        string $interpretation,
        string $suggestedPractices,
        string $conclusion,
    ): string {
        return <<<HTML
<h2>Introduction</h2>
<p>{$introduction}</p>
<h2>Traditional Background</h2>
<p>{$traditionalBackground}</p>
<h2>Astrological Concept</h2>
<p>{$astrologicalConcept}</p>
<h2>Interpretation</h2>
<p>{$interpretation}</p>
<h2>Suggested Practices</h2>
<p>{$suggestedPractices}</p>
<h2>Conclusion</h2>
<p>{$conclusion}</p>
HTML;
    }

    /**
     * @return array{label: string, country: string, state: string, district: string, city: string, lat: float, lng: float}
     */
    private function location(
        string $label,
        string $country,
        string $state,
        string $district,
        string $city,
        float $lat,
        float $lng,
    ): array {
        return [
            'label' => $label,
            'country' => $country,
            'state' => $state,
            'district' => $district,
            'city' => $city,
            'lat' => $lat,
            'lng' => $lng,
        ];
    }

    /**
     * @return array{type: string, url: string, video_id: string}
     */
    private function youtubeVideo(string $videoTypeLabel): array
    {
        return [
            'type' => 'youtube',
            'url' => 'https://www.youtube.com/watch?v=EngW7tLk6R8',
            'video_id' => 'EngW7tLk6R8',
            'label' => $videoTypeLabel,
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function externalDocument(string $name): array
    {
        return [
            'path' => 'seeders/astro-consultancy/'.$name,
            'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'name' => $name,
            'type' => 'application/pdf',
        ];
    }
}
