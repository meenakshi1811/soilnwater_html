<?php

namespace App\Support;

use Illuminate\Support\Str;

class CommunityContentTaxonomy
{
    /**
     * Canonical community post types and their category choices.
     *
     * @return array<string, array{label: string, description: string, categories: list<string>, features?: list<string>, monetization?: list<string>, rewards?: list<string>, examples?: list<string>}>
     */
    public static function types(): array
    {
        return [
            'articles' => [
                'label' => 'Articles',
                'description' => 'Knowledge-based articles.',
                'categories' => ['Education', 'Business', 'Technology', 'Agriculture', 'Real Estate', 'Construction', 'Water Management', 'Environment', 'Government Schemes', 'Personal Development'],
            ],
            'reports' => [
                'label' => 'Reports',
                'description' => 'Research, analytical content, and My Area civic problem reports.',
                'categories' => self::reportMainCategories(),
            ],
            'my-area' => [
                'label' => 'My Area',
                'description' => 'Report local civic issues with evidence, GPS location, and community support.',
                'categories' => ['Community Problem Report'],
                'features' => ['Photos', 'Videos', 'Documents', 'GPS Location', 'Support', 'Comments', 'Votes'],
            ],
            'my-voice' => [
                'label' => 'My Voice',
                'description' => 'Personal opinions, lived experiences, and community perspectives.',
                'categories' => ['Personal Opinion', 'Local Experience', 'Public Suggestion', 'Community Concern', 'Personal Story', 'Open Letter'],
            ],
            'news' => [
                'label' => 'News',
                'description' => 'Community and local news.',
                'categories' => [
                    'Local News',
                    'State News',
                    'National News',
                    'International News',
                    'Business News',
                    'Education News',
                    'Agriculture News',
                    'Water News',
                    'Environment News',
                    'Technology News',
                    'Health News',
                    'Sports News',
                    'Entertainment News',
                    'Community News',
                    'Government News',
                    'Infrastructure News',
                ],
            ],
            'stories' => [
                'label' => 'Stories',
                'description' => 'One of the highest engagement sections.',
                'categories' => self::storyMainCategories(),
                'categoryGroups' => self::storyMainCategoryGroups(),
            ],
            'poetry' => [
                'label' => 'Poetry',
                'description' => 'Poetry submissions from the community.',
                'categories' => self::poetryMainCategories(),
            ],
            'biography' => [
                'label' => 'Biography',
                'description' => 'Write about inspiring personalities.',
                'categories' => ['Freedom Fighters', 'Scientists', 'Entrepreneurs', 'Teachers', 'Social Workers', 'Local Heroes'],
            ],
            'autobiography' => [
                'label' => 'Autobiography',
                'description' => 'Personal life journeys with milestones, lessons, and author context.',
                'categories' => [
                    'Personal Journey',
                    'Career Journey',
                    'Business Journey',
                    'Educational Journey',
                    "Women's Journey",
                    'Senior Citizen Journey',
                    "Farmer's Journey",
                    'Social Service Journey',
                    'Professional Journey',
                    'Spiritual Journey',
                ],
            ],
            'childrens-corner' => [
                'label' => "Children's Corner",
                'description' => 'A dedicated space for children.',
                'categories' => self::childrensCornerShareTypes(),
                'features' => ['Parent approval option', 'School participation'],
            ],
            'awareness' => [
                'label' => 'Awareness',
                'description' => 'Public awareness campaigns, advisories, and social cause content.',
                'categories' => self::awarenessCategories(),
            ],
            'business' => [
                'label' => 'Business',
                'description' => 'Business knowledge, stories, and tips.',
                'categories' => self::businessMainCategories(),
            ],
            'student-corner' => [
                'label' => 'Student Corner',
                'description' => 'Educational articles, career guidance, exam preparation, skill development, and student achievements.',
                'categories' => self::studentCornerMainCategories(),
            ],
            'career' => [
                'label' => 'Career',
                'description' => 'Career development and guidance.',
                'categories' => ['Career Guidance', 'Interview Tips', 'Resume Guidance', 'Skill Development', 'Professional Growth', 'Career Journey'],
            ],
            'health-wellness' => [
                'label' => 'Health & Wellness',
                'description' => 'Health, wellness, and lifestyle content.',
                'categories' => ['Health Awareness', 'Fitness', 'Mental Wellness', 'Nutrition', 'Preventive Care', 'Community Health'],
            ],
            'womens-world' => [
                'label' => "Women's World",
                'description' => 'Stories, guidance, and empowerment content for women.',
                'categories' => self::womensWorldMainCategories(),
            ],
            'senior-citizens-forum' => [
                'label' => 'Senior Citizens Forum',
                'description' => 'A highly underserved audience.',
                'categories' => self::seniorCitizensForumMainCategories(),
            ],
            'youth-corner' => [
                'label' => 'Youth Corner',
                'description' => 'Youth-focused career, life, and inspiration posts.',
                'categories' => self::youthCornerMainCategories(),
            ],
            'jobs-employment' => [
                'label' => 'Jobs & Employment',
                'description' => 'Employment and skill-development updates.',
                'categories' => ['Job Alerts', 'Interview Tips', 'Resume Guidance', 'Government Jobs', 'Private Jobs', 'Skill Development'],
            ],
            'opinions-views' => [
                'label' => 'Opinions & Views',
                'description' => 'Personal viewpoints and opinion pieces.',
                'categories' => ['Public Opinion', 'Editorial', 'Community View', 'Policy View', 'Social Commentary'],
            ],
            'travel-diaries' => [
                'label' => 'Travel Diaries',
                'description' => 'Travel experiences and guides.',
                'categories' => ['Local Travel', 'India Travel', 'International Travel', 'Pilgrimage', 'Eco Travel', 'Travel Tips'],
            ],
            'culture-heritage' => [
                'label' => 'Culture & Heritage',
                'description' => 'Culture, history, language, and heritage.',
                'categories' => ['Local Culture', 'Heritage Sites', 'Festivals', 'Traditional Art', 'Language', 'History'],
            ],
            'astro-consultancy' => [
                'label' => 'Astro Consultancy',
                'description' => 'Astrology and spiritual consultation content.',
                'categories' => ['Astrology', 'Numerology', 'Vastu', 'Palmistry', 'Horoscope', 'Spiritual Guidance'],
                'monetization' => ['Paid consultations', 'Premium astrologer profiles'],
            ],
            'religion-spirituality' => [
                'label' => 'Religion & Spirituality',
                'description' => 'Spiritual and religious posts.',
                'categories' => ['Spiritual Articles', 'Meditation', 'Temple Information', 'Festivals', 'Scriptures'],
            ],
            'agriculture' => [
                'label' => 'Agriculture',
                'description' => 'Agriculture and farmer-focused content.',
                'categories' => ['Organic Farming', 'Irrigation', 'Soil Health', 'Crop Management', 'Government Schemes', 'Farmer Stories'],
            ],
            'environment' => [
                'label' => 'Environment',
                'description' => 'Environment and conservation posts.',
                'categories' => ['Water Conservation', 'Climate Change', 'Tree Plantation', 'Renewable Energy', 'Wildlife'],
            ],
            'technology' => [
                'label' => 'Technology',
                'description' => 'Technology, AI, and innovation content.',
                'categories' => ['New Innovations', 'AI', 'Space', 'Electronics', 'Research'],
            ],
            'science' => [
                'label' => 'Science',
                'description' => 'Science and research content.',
                'categories' => ['New Innovations', 'Space', 'Electronics', 'Research', 'Science Experiments'],
            ],
            'local-voices' => [
                'label' => 'Local Voices',
                'description' => 'A unique community section where people discuss local issues.',
                'categories' => ['Road Problems', 'Water Issues', 'Cleanliness', 'Traffic', 'Local Achievements'],
                'examples' => ['Road problems', 'Water issues', 'Cleanliness', 'Traffic', 'Local achievements'],
            ],
            'community-issues' => [
                'label' => 'Community Issues',
                'description' => 'Civic issues, suggestions, and campaigns.',
                'categories' => ['Civic Issues', 'Public Suggestions', 'Public Grievances', 'Community Projects', 'Social Campaigns'],
            ],
            'creative-corner' => [
                'label' => 'Creative Corner',
                'description' => 'A very high engagement section.',
                'categories' => ['Photography', 'Sketches', 'Paintings', 'Crafts', 'DIY Projects'],
            ],
            'competitions' => [
                'label' => 'Competitions',
                'description' => 'Monthly contests.',
                'categories' => ['Essay Writing', 'Poetry Writing', 'Photography', 'Story Writing', 'Drawing'],
                'rewards' => ['Certificates', 'Badges', 'Featured Author'],
            ],
            'discussions' => [
                'label' => 'Discussions',
                'description' => 'Thread-based discussions.',
                'categories' => ['General Discussion', 'Local Issues', 'Education', 'Business', 'Agriculture', 'Real Estate'],
            ],
        ];
    }


    /**
     * Post types available in the create/edit form.
     *
     * @return array<string, array{label: string, description: string, categories: list<string>, features?: list<string>, monetization?: list<string>, rewards?: list<string>, examples?: list<string>}>
     */
    public static function formTypes(): array
    {
        return collect(self::types())
            ->except(['my-area', 'my-voice'])
            ->all();
    }

    /**
     * Legacy report format options kept so stale compiled views or older callers do not fail.
     *
     * The create/edit form no longer renders a report format field.
     *
     * @return array<string, string>
     */
    public static function reportFormats(): array
    {
        return [
            'my_area' => 'My Area problem report',
        ];
    }

    /**
     * Main category choices for story posts, grouped for the create/edit form.
     *
     * @return array<string, list<string>>
     */
    public static function storyMainCategoryGroups(): array
    {
        return [
            'Personal & emotional' => [
                'Inspirational Stories',
                'Life Experiences',
                'Motivational Stories',
                'Short Stories',
                'Social Stories',
                'Family Stories',
            ],
            'Thematic' => [
                "Children's Stories",
                'Educational Stories',
                'Travel Stories',
                'Historical Stories',
                'Business Stories',
                'Village Stories',
            ],
            'Audience & style' => [
                "Women's Stories",
                'Senior Citizen Stories',
                'Student Stories',
                'Success Stories',
                'Humor Stories',
                'Fiction Stories',
            ],
        ];
    }

    /**
     * Main category choices for story posts.
     *
     * @return list<string>
     */
    public static function storyMainCategories(): array
    {
        return array_values(array_merge(...array_values(self::storyMainCategoryGroups())));
    }

    /**
     * Story type choices grouped for the create/edit form.
     *
     * @return array<string, list<string>>
     */
    public static function storyTypeGroups(): array
    {
        return [
            'Narrative format' => [
                'True Story',
                'Personal Experience',
                'Fiction',
                'Historical',
            ],
            'Purpose & audience' => [
                'Educational',
                'Motivational',
                "Children's Story",
                'Folklore',
            ],
            'Community & travel' => [
                'Community Story',
                'Travel Diary',
            ],
        ];
    }

    /**
     * Story type choices for story posts.
     *
     * @return list<string>
     */
    public static function storyTypes(): array
    {
        return array_values(array_merge(...array_values(self::storyTypeGroups())));
    }

    /**
     * Autobiography type choices for autobiography posts.
     *
     * @return list<string>
     */
    public static function autobiographyTypes(): array
    {
        return [
            'Complete Life Story',
            'Selected Life Events',
            'Career Journey',
            'Success Story',
            'Inspirational Journey',
            'Memoir',
            'Travel Memoir',
            'Professional Experience',
        ];
    }

    /**
     * Share-type choices for Children's Corner posts.
     *
     * @return list<string>
     */
    public static function childrensCornerShareTypes(): array
    {
        return array_values(array_merge(...array_values(self::childrensCornerShareTypeGroups())));
    }

    /**
     * @return array<string, list<string>>
     */
    public static function childrensCornerShareTypeGroups(): array
    {
        return [
            'Writing' => ['Story', 'Poem', 'Essay', 'Article', 'Moral Story', 'Speech', 'Joke'],
            'Creative' => ['Drawing', 'Painting', 'Photography', 'Craft Work', 'Model Making', 'Talent Showcase'],
            'School work' => ['School Project', 'Science Project'],
            'Fun & learning' => ['Quiz', 'Puzzle', 'General Knowledge'],
            'Other' => ['Other'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function childrensCornerAgeGroups(): array
    {
        return [
            '3-5 Years',
            '6-8 Years',
            '9-12 Years',
            '13-15 Years',
            '16-18 Years',
        ];
    }

    /**
     * @return list<string>
     */
    public static function childrensCornerGradeLevels(): array
    {
        return [
            'Class 1',
            'Class 2',
            'Class 3',
            'Class 4',
            'Class 5',
            'Class 6',
            'Class 7',
            'Class 8',
            'Class 9',
            'Class 10',
            'Class 11',
            'Class 12',
        ];
    }

    /**
     * Theme choices for Children's Corner posts.
     *
     * @return list<string>
     */
    public static function childrensCornerThemes(): array
    {
        return [
            'Environment',
            'Water Conservation',
            'Animals',
            'Nature',
            'Education',
            'Science',
            'Technology',
            'Patriotism',
            'Family',
            'Friendship',
            'Kindness',
            'Health',
            'Sports',
            'Culture',
            'Festivals',
            'Space',
            'Agriculture',
        ];
    }

    /**
     * Share types that may include an optional featured image.
     *
     * @return list<string>
     */
    public static function childrensCornerShareTypesWithFeaturedImage(): array
    {
        return ['Story', 'Essay'];
    }

    /**
     * @return list<string>
     */
    public static function childrensCornerParentRelationships(): array
    {
        return ['Father', 'Mother', 'Guardian', 'Teacher'];
    }

    /**
     * @return list<string>
     */
    public static function childrensCornerSubmittedThroughOptions(): array
    {
        return ['Parent', 'Teacher', 'School', 'Child'];
    }

    /**
     * @return list<string>
     */
    public static function childrensCornerTalentCategories(): array
    {
        return [
            'Art',
            'Poetry',
            'Story Writing',
            'Public Speaking',
            'Science',
            'Craft',
            'Photography',
            'Music',
            'Dance',
            'Innovation',
        ];
    }

    /**
     * Child-friendly reaction options for Children's Corner posts.
     *
     * @return array<string, string> reaction label => icon class
     */
    public static function childrensCornerReactionOptions(): array
    {
        return [
            'Excellent' => 'fa-solid fa-star',
            'Great Job' => 'fa-solid fa-thumbs-up',
            'Wonderful' => 'fa-solid fa-face-smile',
            'Creative' => 'fa-solid fa-palette',
            'Smart Idea' => 'fa-solid fa-lightbulb',
            'Inspiring' => 'fa-solid fa-heart',
        ];
    }

    /**
     * @return list<string>
     */
    public static function childrensCornerReactionLabels(): array
    {
        return array_keys(self::childrensCornerReactionOptions());
    }

    /**
     * Children's Corner privacy setting values and labels.
     *
     * @return array<string, string>
     */
    public static function childrensCornerPrivacySettings(): array
    {
        return [
            'public_limited' => 'Public with limited child information',
            'public' => 'Public',
            'registered_users' => 'Registered users only',
            'school_community' => 'School community only',
        ];
    }

    public static function childrensCornerDefaultPrivacySetting(): string
    {
        return 'public_limited';
    }

    /**
     * @return array<string, string>
     */
    public static function childrensCornerSafetyDeclarations(): array
    {
        return [
            'childrens_corner_safety_no_address' => 'No personal address included',
            'childrens_corner_safety_no_harmful' => 'No harmful content included',
            'childrens_corner_safety_no_copyright' => 'No copyrighted material copied',
            'childrens_corner_safety_no_inappropriate_media' => 'No inappropriate images or videos uploaded',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function childrensCornerShareTypesByContentMode(): array
    {
        return [
            'rich_text' => ['Story', 'Essay', 'Article', 'Moral Story', 'Speech', 'General Knowledge', 'Joke', 'Other'],
            'poem' => ['Poem'],
            'image' => ['Drawing', 'Painting', 'Photography', 'Craft Work', 'Model Making', 'Talent Showcase'],
            'project' => ['School Project', 'Science Project'],
            'quiz' => ['Quiz', 'Puzzle'],
        ];
    }

    public static function childrensCornerContentMode(?string $shareType): ?string
    {
        if (! is_string($shareType) || $shareType === '') {
            return null;
        }

        foreach (self::childrensCornerShareTypesByContentMode() as $mode => $types) {
            if (in_array($shareType, $types, true)) {
                return $mode;
            }
        }

        return 'rich_text';
    }

    /**
     * Awareness main category groups for the create/edit form.
     *
     * @return array<string, list<string>>
     */
    public static function awarenessCategoryGroups(): array
    {
        return [
            'Environment & health' => [
                'Water Conservation',
                'Environment',
                'Health Awareness',
                'Public Health',
                'Agriculture Awareness',
            ],
            'Safety & welfare' => [
                "Women's Safety",
                'Child Welfare',
                'Road Safety',
                'Cyber Security',
                'Drug Awareness',
                'Disaster Management',
                'Senior Citizen Welfare',
            ],
            'Education & society' => [
                'Education',
                'Social Awareness',
                'Consumer Rights',
                'Animal Welfare',
                'Financial Literacy',
            ],
            'Programs & employment' => [
                'Government Schemes',
                'Employment Awareness',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessCategories(): array
    {
        return array_values(array_merge(...array_values(self::awarenessCategoryGroups())));
    }

    /**
     * @return list<string>
     */
    public static function awarenessTypes(): array
    {
        return [
            'Campaign',
            'Educational Post',
            'Public Advisory',
            'Safety Alert',
            'Government Initiative',
            'NGO Initiative',
            'Social Cause',
            'Event Awareness',
            'Training Program',
            'Volunteer Appeal',
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessTargetAudiences(): array
    {
        return [
            'General Public',
            'Students',
            'Youth',
            'Women',
            'Senior Citizens',
            'Farmers',
            'Professionals',
            'Businesses',
            'Parents',
            'Children',
            'Teachers',
            'Rural Communities',
            'Urban Communities',
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessLevels(): array
    {
        return [
            'Local',
            'District',
            'State',
            'National',
            'Global',
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessPostedByOptions(): array
    {
        return [
            'Individual',
            'NGO',
            'School',
            'College',
            'Business',
            'Government Department',
            'Community Group',
            'Consultant',
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessVideoTypes(): array
    {
        return [
            'Awareness Video',
            'Public Service Message',
            'Training Video',
            'Expert Talk',
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessCallToActionExamples(): array
    {
        return [
            'Save Water Daily',
            'Plant One Tree',
            'Use Helmets',
            'Avoid Plastic',
            'Get Health Checkups',
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessEventTypes(): array
    {
        return [
            'Seminar',
            'Workshop',
            'Webinar',
            'Campaign Drive',
            'Tree Plantation',
            'Blood Donation Camp',
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessSocialImpactCategories(): array
    {
        return [
            'Health',
            'Education',
            'Environment',
            'Women Empowerment',
            'Agriculture',
            'Water Conservation',
            'Community Development',
            'Animal Welfare',
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessPledgeExamples(): array
    {
        return [
            'I Pledge to Save Water',
            'I Pledge to Plant Trees',
            'I Pledge to Follow Road Safety Rules',
        ];
    }

    /**
     * Recommended awareness content sections for the rich text editor.
     *
     * @return array<string, string>
     */
    public static function awarenessContentStructure(): array
    {
        return [
            'Problem' => 'What issue exists?',
            'Why It Matters' => 'Why should people care?',
            'Facts & Statistics' => 'Supporting information.',
            'Solutions' => 'Practical recommendations.',
            'Call To Action' => 'What should people do?',
        ];
    }

    /**
     * Character type choices for story posts.
     *
     * @return list<string>
     */
    public static function storyCharacterTypes(): array
    {
        return [
            'Real Person',
            'Fictional Character',
            'Historical Figure',
            'Anonymous Person',
        ];
    }

    /**
     * Story setting location scope choices for story posts.
     *
     * @return list<string>
     */
    public static function storyPlaceTypes(): array
    {
        return [
            'Village',
            'Town',
            'City',
            'District',
            'State',
            'Country',
        ];
    }

    /**
     * Time period choices for story posts.
     *
     * @return list<string>
     */
    public static function storyTimePeriods(): array
    {
        return [
            'Childhood',
            'Present Day',
            '1980s',
            'Historical Period',
            'Future',
        ];
    }

    /**
     * Story language choices for story posts.
     *
     * @return list<string>
     */
    public static function storyLanguages(): array
    {
        return [
            'English',
            'Hindi',
            'Punjabi',
            'Bengali',
            'Tamil',
            'Marathi',
            'Gujarati',
            'Other',
        ];
    }

    /**
     * Target audience choices for story posts.
     *
     * @return list<string>
     */
    public static function storyTargetAudiences(): array
    {
        return [
            'Children',
            'Students',
            'Women',
            'Senior Citizens',
            'Youth',
            'Professionals',
            'General Public',
        ];
    }

    /**
     * Story theme choices for story posts.
     *
     * @return list<string>
     */
    public static function storyThemes(): array
    {
        return [
            'Friendship',
            'Love',
            'Family',
            'Success',
            'Education',
            'Environment',
            'Community',
            "Women's Empowerment",
            'Agriculture',
            'Business',
            'Patriotism',
            'Spirituality',
            'Adventure',
        ];
    }

    /**
     * Main category choices for poetry posts.
     *
     * @return list<string>
     */
    public static function poetryMainCategories(): array
    {
        return [
            'Poetry',
            'Shayari',
            'Ghazal',
            'Nazm',
            'Geet (Song)',
            'Haiku',
            'Doha',
            'Free Verse',
            "Children's Poetry",
            'Spiritual Poetry',
        ];
    }

    /**
     * Sub category choices for poetry posts.
     *
     * @return list<string>
     */
    public static function poetrySubCategories(): array
    {
        return [
            'Love Poetry',
            'Inspirational Poetry',
            'Nature Poetry',
            'Patriotic Poetry',
            'Social Poetry',
            'Humor Poetry',
            'Spiritual Poetry',
            "Women's Poetry",
            'Student Poetry',
            'Environmental Poetry',
            'Village Poetry',
        ];
    }

    /**
     * Poetry type choices grouped for the create/edit form.
     *
     * @return array<string, list<string>>
     */
    public static function poetryTypeGroups(): array
    {
        return [
            'Original & contemporary' => [
                'Original Poetry',
                'Rhyming Poem',
                'Free Verse',
                'Song Lyrics',
                "Children's Poem",
                'Motivational Poetry',
                'Narrative Poetry',
            ],
            'Classical & regional' => [
                'Shayari',
                'Ghazal',
                'Classical Poetry',
            ],
        ];
    }

    /**
     * Poetry type choices for poetry posts.
     *
     * @return list<string>
     */
    public static function poetryTypes(): array
    {
        return array_values(array_merge(...array_values(self::poetryTypeGroups())));
    }

    /**
     * Editor script options for poetry body content.
     *
     * @return array<string, string>
     */
    public static function poetryEditorLanguages(): array
    {
        return [
            'hi' => 'Hindi',
            'en' => 'English',
            'ur' => 'Urdu',
            'pa' => 'Punjabi',
            'bn' => 'Bengali',
            'mr' => 'Marathi',
            'gu' => 'Gujarati',
            'ta' => 'Tamil',
            'te' => 'Telugu',
        ];
    }

    /**
     * Editor language options for standard (non-poetry) posts.
     *
     * @return array<string, string>
     */
    public static function standardEditorLanguages(): array
    {
        return [
            'en' => 'English',
            'hi' => 'Hindi',
        ];
    }

    /**
     * @return list<string>
     */
    public static function editorLanguageCodesFor(?string $contentType): array
    {
        if ($contentType === 'poetry') {
            return array_keys(self::poetryEditorLanguages());
        }

        return array_keys(self::standardEditorLanguages());
    }

    public static function normalizeEditorLanguage(?string $contentType, mixed $code): string
    {
        $normalized = is_string($code) ? $code : 'en';

        return in_array($normalized, self::editorLanguageCodesFor($contentType), true) ? $normalized : 'en';
    }

    /**
     * Theme choices for poetry posts.
     *
     * @return list<string>
     */
    public static function poetryThemes(): array
    {
        return [
            'Love',
            'Nature',
            'Water',
            'Environment',
            'Friendship',
            'Family',
            'Mother',
            'Father',
            'Patriotism',
            'Life',
            'Spirituality',
            'Motivation',
            "Women's Empowerment",
            'Village Life',
            'Agriculture',
            'Education',
            'Social Awareness',
            'Humor',
        ];
    }

    /**
     * Target audience choices for poetry posts.
     *
     * @return list<string>
     */
    public static function poetryTargetAudiences(): array
    {
        return [
            'Children',
            'Students',
            'Women',
            'Youth',
            'Senior Citizens',
            'General Public',
        ];
    }

    /**
     * Main category choices for report posts.
     *
     * @return list<string>
     */
    public static function reportMainCategories(): array
    {
        return [
            'Community Report',
            'Research Report',
            'Survey Report',
            'Infrastructure Report',
            'Environment Report',
            'Water Report',
            'Agriculture Report',
            'Education Report',
            'Health Report',
            'Market Report',
            'Government Scheme Report',
            'Social Impact Report',
        ];
    }

    /**
     * Source type choices for news posts.
     *
     * @return list<string>
     */
    public static function newsSourceTypes(): array
    {
        return [
            'Self Witnessed',
            'Official Source',
            'Government Source',
            'Press Release',
            'Media Source',
            'Community Source',
            'Survey',
            'Other',
        ];
    }

    /**
     * News type choices grouped for the create/edit form.
     *
     * @return array<string, list<string>>
     */
    public static function newsTypeGroups(): array
    {
        return [
            'News format' => [
                'Breaking News',
                'General News',
                'Community News',
                'Press Release',
            ],
            'Events & official' => [
                'Event Coverage',
                'Government',
                'Announcement',
                'Public Notice',
            ],
            'Community impact' => [
                'Development Update',
                'Awareness News',
                'Success Story',
                'Citizen Report',
            ],
        ];
    }

    /**
     * News type choices for news posts.
     *
     * @return list<string>
     */
    public static function newsTypes(): array
    {
        return array_values(array_merge(...array_values(self::newsTypeGroups())));
    }

    /**
     * News priority suggestions from contributors.
     *
     * @return list<string>
     */
    public static function newsPriorities(): array
    {
        return [
            'Normal',
            'Important',
            'Urgent',
            'Breaking',
        ];
    }

    /**
     * Community impact level choices for news posts.
     *
     * @return list<string>
     */
    public static function newsImpactLevels(): array
    {
        return [
            'Low',
            'Medium',
            'High',
            'Critical',
        ];
    }

    /**
     * Affected community groups for news impact tracking.
     *
     * @return list<string>
     */
    public static function newsAffectedGroups(): array
    {
        return [
            'Residents',
            'Students',
            'Farmers',
            'Businesses',
            'Women',
            'Senior Citizens',
            'General Public',
        ];
    }

    /**
     * Report type choices for report posts.
     *
     * @return list<string>
     */
    public static function reportTypes(): array
    {
        return [
            'Observation Report',
            'Survey Report',
            'Research Report',
            'Field Report',
            'Investigation Report',
            'Community Report',
            'Technical Report',
            'Market Analysis',
            'Case Study',
            'Progress Report',
            'Impact Assessment',
        ];
    }

    /**
     * Author type choices for report posts.
     *
     * @return list<string>
     */
    public static function reportAuthorTypes(): array
    {
        return [
            'Citizen Reporter',
            'Student',
            'Researcher',
            'Teacher',
            'NGO',
            'Government Officer',
            'Business',
            'Consultant',
            'Professional',
        ];
    }

    /**
     * Organization type choices for report posts.
     *
     * @return list<string>
     */
    public static function reportOrganizationTypes(): array
    {
        return [
            'Institution',
            'School / College',
            'NGO',
            'Government Department',
        ];
    }

    /**
     * Report status choices describing the intent or outcome of a report.
     *
     * @return list<string>
     */
    public static function reportStatuses(): array
    {
        return [
            'Information Only',
            'Seeking Support',
            'Awareness Campaign',
            'Request for Action',
            'Success Story',
            'Issue Resolved',
        ];
    }

    /**
     * Action audience choices for community report action requests.
     *
     * @return list<string>
     */
    public static function reportActionRequestedFrom(): array
    {
        return [
            'Municipality',
            'Panchayat',
            'State Government',
            'NGO',
            'Community',
            'Business',
            'General Public',
        ];
    }

    /**
     * Women's World main category groups for the create/edit form.
     *
     * @return array<string, list<string>>
     */
    public static function womensWorldCategoryGroups(): array
    {
        return [
            'Life & growth' => [
                'Personal Experiences',
                'Career & Professional Growth',
                'Women Entrepreneurship',
                'Health & Wellness',
                'Motherhood & Parenting',
                'Education',
            ],
            'Empowerment & skills' => [
                'Financial Independence',
                'Self Development',
                'Relationships & Family',
                'Women Empowerment',
                'Success Stories',
                'Life Skills',
            ],
            'Rights & safety' => [
                'Social Issues',
                'Legal Awareness',
                'Safety & Security',
                "Senior Women's Corner",
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldMainCategories(): array
    {
        return array_values(array_merge(...array_values(self::womensWorldCategoryGroups())));
    }

    /**
     * Women's World content type groups for the create/edit form.
     *
     * @return array<string, list<string>>
     */
    public static function womensWorldContentTypeGroups(): array
    {
        return [
            'Stories & guidance' => [
                'Personal Story',
                'Advice & Guidance',
                'Success Story',
                'Awareness Post',
            ],
            'Articles & analysis' => [
                'Educational Article',
                'Opinion Piece',
                'Motivational Content',
                'Case Study',
            ],
            'Community' => [
                'Interview',
                'Question & Discussion',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldContentTypes(): array
    {
        return array_values(array_merge(...array_values(self::womensWorldContentTypeGroups())));
    }

    /**
     * @return list<string>
     */
    public static function womensWorldTargetAudiences(): array
    {
        return [
            'Students',
            'Working Women',
            'Homemakers',
            'Entrepreneurs',
            'Mothers',
            'Young Women',
            'Senior Women',
            'Professionals',
            'General Public',
        ];
    }

    /**
     * Featured topic groups for Women's World posts (optional multi-select).
     *
     * @return array<string, list<string>>
     */
    public static function womensWorldFeaturedTopicGroups(): array
    {
        return [
            'Career & wellbeing' => [
                'Career Growth',
                'Work-Life Balance',
                'Mental Health',
            ],
            'Family & finance' => [
                'Parenting',
                'Financial Planning',
                'Self Confidence',
            ],
            'Growth & leadership' => [
                'Leadership',
                'Skill Development',
            ],
            'Rights & health' => [
                "Women's Rights",
                'Health Awareness',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldFeaturedTopics(): array
    {
        return array_values(array_merge(...array_values(self::womensWorldFeaturedTopicGroups())));
    }

    /**
     * Women's World video type groups for the create/edit form.
     *
     * @return array<string, list<string>>
     */
    public static function womensWorldVideoTypeGroups(): array
    {
        return [
            'Talks & introductions' => [
                'Motivational Talk',
                'Business Introduction',
                'Workshop Recording',
            ],
            'Awareness & interviews' => [
                'Awareness Video',
                'Interview',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldVideoTypes(): array
    {
        return array_values(array_merge(...array_values(self::womensWorldVideoTypeGroups())));
    }

    /**
     * Life stage options for Women's World posts.
     *
     * @return array<string, list<string>>
     */
    public static function womensWorldLifeStageGroups(): array
    {
        return [
            'Early life' => [
                'Student',
                'Young Professional',
                'Newly Married',
            ],
            'Mid life' => [
                'Mother',
                'Entrepreneur',
            ],
            'Later life' => [
                'Retired',
                'Senior Citizen',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldLifeStages(): array
    {
        return array_values(array_merge(...array_values(self::womensWorldLifeStageGroups())));
    }

    /**
     * Theme groups for Women's World posts.
     *
     * @return array<string, list<string>>
     */
    public static function womensWorldThemeGroups(): array
    {
        return [
            'Empowerment & career' => [
                'Women Empowerment',
                'Education',
                'Leadership',
                'Business',
                'Career',
            ],
            'Wellness & family' => [
                'Health',
                'Fitness',
                'Mental Wellness',
                'Parenting',
                'Relationships',
            ],
            'Community & growth' => [
                'Community Service',
                'Environment',
                'Financial Independence',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldThemes(): array
    {
        return array_values(array_merge(...array_values(self::womensWorldThemeGroups())));
    }

    /**
     * Business category choices for Women's World entrepreneur posts.
     *
     * @return list<string>
     */
    public static function womensWorldBusinessCategories(): array
    {
        return self::businessMainCategories();
    }

    /**
     * Default poll options for Women's World posts.
     *
     * @return list<string>
     */
    public static function womensWorldDefaultPollOptions(): array
    {
        return [
            'Work-Life Balance',
            'Child Care',
            'Career Growth',
            'Financial Independence',
        ];
    }

    /**
     * Support request choices for Women's World posts.
     *
     * @return array<string, list<string>>
     */
    public static function womensWorldSupportRequestGroups(): array
    {
        return [
            'Guidance' => [
                'Looking for Advice',
                'Looking for Mentorship',
            ],
            'Professional support' => [
                'Looking for Business Guidance',
                'Looking for Career Guidance',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldSupportRequests(): array
    {
        return array_values(array_merge(...array_values(self::womensWorldSupportRequestGroups())));
    }

    /**
     * Community group tags for Women's World posts.
     *
     * @return list<string>
     */
    public static function womensWorldCommunityGroups(): array
    {
        return [
            'Working Women',
            'Women Entrepreneurs',
            'Homemakers',
            'Mothers',
            'Teachers',
            'Students',
            'Senior Women',
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldTagExamples(): array
    {
        return [
            'Women',
            'Leadership',
            'Entrepreneurship',
            'Parenting',
            'Career',
            'Health',
        ];
    }

    /**
     * Publish-as labels for Women's World posts.
     *
     * @return array<string, string>
     */
    public static function womensWorldPublishAsOptions(): array
    {
        return [
            \App\Models\CommunityPost::PUBLISH_AS_PUBLIC_PROFILE => 'Real Name',
            \App\Models\CommunityPost::PUBLISH_AS_PEN_NAME => 'Pen Name',
            \App\Models\CommunityPost::PUBLISH_AS_ANONYMOUS => 'Anonymous',
        ];
    }

    /**
     * Visibility settings for Women's World posts.
     *
     * @return array<string, string>
     */
    public static function womensWorldVisibilitySettings(): array
    {
        return [
            'public' => 'Public',
            'registered_users' => 'Registered Users',
            'women_community_only' => 'Women Community Only',
            'private_link' => 'Private Link',
        ];
    }

    public static function womensWorldDefaultVisibilitySetting(): string
    {
        return 'public';
    }

    /**
     * Positive reaction options for Women's World posts.
     *
     * @return array<string, string>
     */
    public static function womensWorldReactionOptions(): array
    {
        return [
            'Inspiring' => 'fa-solid fa-lightbulb',
            'Strong' => 'fa-solid fa-dumbbell',
            'Message' => 'fa-solid fa-message',
            'Empowering' => 'fa-solid fa-venus',
            'Helpful' => 'fa-solid fa-hand-holding-heart',
            'Respect' => 'fa-solid fa-hands-praying',
            'Excellent' => 'fa-solid fa-star',
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldReactionLabels(): array
    {
        return array_keys(self::womensWorldReactionOptions());
    }

    /**
     * Main category choices for Student Corner posts.
     *
     * @return list<string>
     */
    public static function studentCornerMainCategories(): array
    {
        return [
            'Education',
            'Career Guidance',
            'Competitive Exams',
            'Scholarships',
            'Projects',
            'Science & Technology',
            'Student Experiences',
            'Study Tips',
            'Internships',
            'Higher Education',
            'Skill Development',
            'Innovation',
            'Research',
            'Entrepreneurship',
            'Campus Life',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerContentTypes(): array
    {
        return [
            'Article',
            'Experience Sharing',
            'Project Submission',
            'Research Summary',
            'Study Notes',
            'Career Guidance',
            'Question & Discussion',
            'Success Story',
            'Exam Strategy',
            'Internship Experience',
            'Book Review',
            'Event Report',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerClassCourses(): array
    {
        return [
            'Class 6-8',
            'Class 9-10',
            'Class 11-12',
            'Diploma',
            'ITI',
            'Undergraduate',
            'Postgraduate',
            'Research Scholar',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerStreams(): array
    {
        return [
            'Science',
            'Commerce',
            'Arts',
            'Engineering',
            'Medical',
            'Agriculture',
            'Management',
            'Law',
            'Other',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerTargetAudiences(): array
    {
        return [
            'School Students',
            'Class 10 Students',
            'Class 12 Students',
            'Engineering Students',
            'Medical Aspirants',
            'College Students',
            'Job Aspirants',
            'Researchers',
            'Teachers',
            'Parents',
        ];
    }

    public static function studentCornerProjectContentType(): string
    {
        return 'Project Submission';
    }

    /**
     * @return list<string>
     */
    public static function studentCornerProjectCategories(): array
    {
        return [
            'Science Project',
            'Engineering Project',
            'Agriculture Project',
            'Software Project',
            'Research Project',
            'Environmental Project',
            'Social Project',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerDocumentExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip'];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerGalleryExamples(): array
    {
        return [
            'Project Photos',
            'Lab Work',
            'Field Visits',
            'Competition Photos',
            'Certificates',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerVideoTypes(): array
    {
        return [
            'Project Demonstration',
            'Presentation',
            'Seminar Recording',
            'Experiment Video',
            'Skill Demonstration',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerStudyMaterialTypes(): array
    {
        return [
            'Notes',
            'Formula Sheet',
            'Question Bank',
            'Previous Year Papers',
            'Solved Examples',
            'Reference Material',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerCareerGuidanceTopics(): array
    {
        return [
            'Higher Education',
            'Study Abroad',
            'Career Planning',
            'Job Preparation',
            'Skill Development',
            'Interview Preparation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerSkills(): array
    {
        return [
            'Programming',
            'Communication',
            'Public Speaking',
            'Design',
            'Leadership',
            'Writing',
            'Research',
            'Marketing',
            'AI & Technology',
            'Agriculture',
            'Electronics',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerMentorshipRequests(): array
    {
        return [
            'Need Career Guidance',
            'Need Project Guidance',
            'Need Exam Preparation Advice',
            'Need Internship Guidance',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerCompetitionCategories(): array
    {
        return [
            'Essay Writing',
            'Science Project',
            'Innovation Challenge',
            'Coding Competition',
            'Photography',
            'Poetry',
            'Story Writing',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerSocialImpactCategories(): array
    {
        return [
            'Environment',
            'Water Conservation',
            'Education',
            'Agriculture',
            'Community Service',
            'Technology',
            'Health',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerTagExamples(): array
    {
        return ['JEE', 'Scholarship', 'Science', 'Project', 'Career', 'Education'];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerDefaultPollOptions(): array
    {
        return ['JEE', 'NEET', 'NDA', 'CUET', 'Other'];
    }

    /**
     * @return array<string, string>
     */
    public static function studentCornerPublishAsOptions(): array
    {
        return [
            \App\Models\CommunityPost::PUBLISH_AS_PUBLIC_PROFILE => 'Real Name',
            \App\Models\CommunityPost::PUBLISH_AS_FIRST_NAME_ONLY => 'First Name Only',
            \App\Models\CommunityPost::PUBLISH_AS_PEN_NAME => 'Pen Name',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function studentCornerVisibilitySettings(): array
    {
        return [
            'public' => 'Public',
            'registered_users' => 'Registered Users',
            'students_only' => 'Students Only',
            'private_link' => 'Private Link',
        ];
    }

    public static function studentCornerDefaultVisibilitySetting(): string
    {
        return 'public';
    }

    /**
     * @return array<string, string>
     */
    public static function studentCornerReactionOptions(): array
    {
        return [
            'Informative' => 'fa-solid fa-circle-info',
            'Excellent' => 'fa-solid fa-star',
            'Helpful' => 'fa-solid fa-hand-holding-heart',
            'Inspiring' => 'fa-solid fa-lightbulb',
            'Outstanding' => 'fa-solid fa-trophy',
            'Great Work' => 'fa-solid fa-thumbs-up',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerReactionLabels(): array
    {
        return array_keys(self::studentCornerReactionOptions());
    }

    /**
     * Main category choices for Youth Corner posts.
     *
     * @return list<string>
     */
    public static function youthCornerMainCategories(): array
    {
        return [
            'Career & Jobs',
            'Skill Development',
            'Entrepreneurship',
            'Startups',
            'Technology',
            'Innovation',
            'Youth Leadership',
            'Motivation',
            'Education',
            'Competitive Exams',
            'Financial Literacy',
            'Sports & Fitness',
            'Mental Health',
            'Social Issues',
            'Travel & Exploration',
            'Community Service',
            'Agriculture & Rural',
            'Development',
            'Digital Creator Corner',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerContentTypes(): array
    {
        return [
            'Experience Sharing',
            'Success Story',
            'Failure Story',
            'Career Advice',
            'Motivational Article',
            'Question & Discussion',
            'Startup Story',
            'Project Showcase',
            'Opinion Piece',
            'Research Summary',
            'Event Report',
            'Awareness Post',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerAgeGroups(): array
    {
        return [
            '13-18',
            '19-25',
            '26-30',
            '31-35',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerOccupations(): array
    {
        return [
            'Student',
            'Job Seeker',
            'Professional',
            'Entrepreneur',
            'Farmer',
            'Freelancer',
            'Content Creator',
            'Social Worker',
            'Other',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerEducationLevels(): array
    {
        return [
            'School',
            'Diploma',
            'ITI',
            'Undergraduate',
            'Graduate',
            'Postgraduate',
            'Professional Course',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerTargetAudiences(): array
    {
        return [
            'Students',
            'Job Seekers',
            'Young Professionals',
            'Entrepreneurs',
            'Freelancers',
            'Youth Leaders',
            'Farmers',
            'General Public',
        ];
    }

    public static function youthCornerProjectContentType(): string
    {
        return 'Project Showcase';
    }

    /**
     * Recommended Youth Corner content sections for the rich text editor.
     *
     * @return array<string, string>
     */
    public static function youthCornerContentStructure(): array
    {
        return [
            'Problem/Challenge' => 'Describe the challenge or situation you faced.',
            'Experience/Story' => 'Share your personal experience or journey.',
            'Actions Taken' => 'Explain the steps you took to address it.',
            'Results' => 'Highlight outcomes and impact.',
            'Lessons Learned' => 'Key takeaways from your experience.',
            'Advice for Others' => 'Practical guidance for fellow youth.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerOpportunityTypes(): array
    {
        return [
            'Job Opportunity',
            'Internship',
            'Scholarship',
            'Training Program',
            'Startup Opportunity',
            'Freelance Work',
            'Volunteer Opportunity',
            'Government Scheme',
            'Competition',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerSkills(): array
    {
        return [
            'Communication',
            'Leadership',
            'Programming',
            'Digital Marketing',
            'Public Speaking',
            'AI & Technology',
            'Agriculture',
            'Design',
            'Writing',
            'Finance',
            'Sales',
            'Management',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerCareerAreas(): array
    {
        return [
            'Engineering',
            'Medical',
            'Government Services',
            'Business',
            'Agriculture',
            'Technology',
            'Education',
            'Arts',
            'Commerce',
            'Law',
            'Defense',
            'Merchant Navy',
            'Others',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerFundingStages(): array
    {
        return [
            'Idea Stage',
            'Bootstrapped',
            'Seed Funding',
            'Angel Investment',
            'Venture Capital',
            'Government Grant',
            'Crowdfunding',
            'Revenue Generating',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerProjectCategories(): array
    {
        return [
            'Technology',
            'Agriculture',
            'Environment',
            'Business',
            'Education',
            'Social Impact',
            'Water Conservation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerDocumentExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip'];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerGalleryExamples(): array
    {
        return [
            'Event Photos',
            'Project Photos',
            'Workshop Images',
            'Achievement Photos',
            'Community Activity',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerVideoTypes(): array
    {
        return [
            'Motivational Talk',
            'Project Demo',
            'Interview',
            'Workshop Recording',
            'Startup Pitch',
            'Skill Demonstration',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerMentorshipRequests(): array
    {
        return [
            'Career Guidance',
            'Startup Guidance',
            'Skill Development',
            'Interview Preparation',
            'Exam Preparation',
            'Business Mentorship',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerCommunityServiceActivities(): array
    {
        return [
            'Volunteer Work',
            'Tree Plantation',
            'Blood Donation Camp',
            'Teaching / Tutoring',
            'Clean-up Drive',
            'Fundraising',
            'Awareness Campaign',
            'Community Development',
            'Disaster Relief',
            'Elder Care Support',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerCommunityServiceExamples(): array
    {
        return [
            'Beach Clean-up',
            'Food Distribution',
            'Digital Literacy Camp',
            'Water Conservation Drive',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerThemes(): array
    {
        return [
            'Leadership',
            'Innovation',
            'Entrepreneurship',
            'Motivation',
            'Technology',
            'Environment',
            'Agriculture',
            'Education',
            'Fitness',
            'Mental Health',
            'Financial Literacy',
            'Community',
            'Development',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerNetworkingOptions(): array
    {
        return [
            'Connect With Me',
            'Discuss Opportunities',
            'Join Project',
            'Seek Guidance',
            'Offer Mentorship',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerTagExamples(): array
    {
        return ['Career', 'Startup', 'Internship', 'Skills', 'Motivation', 'Youth'];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerDefaultPollOptions(): array
    {
        return ['Employment', 'Skills', 'Finance', 'Mental Health', 'Education'];
    }

    /**
     * @return array<string, string>
     */
    public static function youthCornerPublishAsOptions(): array
    {
        return [
            \App\Models\CommunityPost::PUBLISH_AS_PUBLIC_PROFILE => 'Real Name',
            \App\Models\CommunityPost::PUBLISH_AS_FIRST_NAME_ONLY => 'First Name Only',
            \App\Models\CommunityPost::PUBLISH_AS_PEN_NAME => 'Pen Name',
            \App\Models\CommunityPost::PUBLISH_AS_ANONYMOUS => 'Anonymous',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function youthCornerVisibilitySettings(): array
    {
        return [
            'public' => 'Public',
            'registered_users' => 'Registered Users',
            'youth_community' => 'Youth Community',
            'private_link' => 'Private Link',
        ];
    }

    public static function youthCornerDefaultVisibilitySetting(): string
    {
        return 'public';
    }

    /**
     * @return array<string, string>
     */
    public static function youthCornerReactionOptions(): array
    {
        return [
            'Inspiring' => 'fa-solid fa-lightbulb',
            'Innovative' => 'fa-solid fa-rocket',
            'Excellent' => 'fa-solid fa-star',
            'Outstanding' => 'fa-solid fa-trophy',
            'Helpful' => 'fa-solid fa-hand-holding-heart',
            'Motivating' => 'fa-solid fa-fire',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerReactionLabels(): array
    {
        return array_keys(self::youthCornerReactionOptions());
    }

    /**
     * Main category choices for Senior Citizens Forum posts.
     *
     * @return list<string>
     */
    public static function seniorCitizensForumMainCategories(): array
    {
        return [
            'Life Experiences',
            'Advice to Youth',
            'Retirement Life',
            'Health & Wellness',
            'Family Values',
            'Memoirs',
            'Village Memories',
            'Career Experiences',
            'Social Issues',
            'Spirituality',
            'Culture & Heritage',
            'Community Service',
            'Agriculture Experiences',
            'Water & Environment',
            'Inspirational Stories',
        ];
    }

    /**
     * Content type groups for Senior Citizens Forum posts.
     *
     * @return array<string, list<string>>
     */
    public static function seniorCitizensForumContentTypeGroups(): array
    {
        return [
            'Personal & reflective' => [
                'Personal Experience',
                'Memoir',
                'Advice',
                'Opinion',
                'Story',
                'Awareness',
            ],
            'Documentation' => [
                'Historical Account',
                'Cultural Documentation',
            ],
            'Community' => [
                'Success Story',
                'Question & Discussion',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumContentTypes(): array
    {
        return array_values(array_merge(...array_values(self::seniorCitizensForumContentTypeGroups())));
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumAgeGroups(): array
    {
        return [
            '60–65 Years',
            '66–70 Years',
            '71–75 Years',
            '76–80 Years',
            '80+ Years',
        ];
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumLifeJourneyCategories(): array
    {
        return [
            'Teacher',
            'Farmer',
            'Government Employee',
            'Business Owner',
            'Homemaker',
            'Military Personnel',
            'Engineer',
            'Doctor',
            'Social Worker',
            'Entrepreneur',
            'Retired Professional',
        ];
    }

    /**
     * Example key lessons shown on the create/edit form.
     *
     * @return list<string>
     */
    public static function seniorCitizensForumKeyLessonExamples(): array
    {
        return [
            'Respect your parents.',
            'Save regularly.',
            'Education is the best investment.',
            'Health is more important than wealth.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumAudioMemoryExamples(): array
    {
        return [
            'Life stories',
            'Memories',
            'Advice',
        ];
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumVideoTypes(): array
    {
        return [
            'Personal Interview',
            'Life Story Recording',
            'Family Message',
            'Community History',
        ];
    }

    /**
     * Family heritage field labels for Senior Citizens Forum posts.
     *
     * @return array<string, string>
     */
    public static function seniorCitizensForumFamilyHeritageFields(): array
    {
        return [
            'senior_citizens_forum_family_background' => 'Family background',
            'senior_citizens_forum_traditions' => 'Traditions',
            'senior_citizens_forum_cultural_practices' => 'Cultural practices',
            'senior_citizens_forum_family_values' => 'Family values',
        ];
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumThemes(): array
    {
        return [
            'Family Values',
            'Education',
            'Health',
            'Agriculture',
            'Patriotism',
            'Community Service',
            'Water Conservation',
            'Environment',
            'Leadership',
            'Culture',
            'Spirituality',
            'Retirement',
        ];
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumCommunityContributions(): array
    {
        return [
            'Teacher',
            'Volunteer',
            'Social Worker',
            'Community Leader',
            'Farmer',
            'Environmental Activist',
        ];
    }

    /**
     * Visibility settings for Senior Citizens Forum posts.
     *
     * @return array<string, string>
     */
    public static function seniorCitizensForumVisibilitySettings(): array
    {
        return [
            'public' => 'Public',
            'registered_users' => 'Registered Users',
            'senior_citizens_community' => 'Senior Citizens Community',
            'private_link' => 'Private Link',
        ];
    }

    public static function seniorCitizensForumDefaultVisibilitySetting(): string
    {
        return 'public';
    }

    /**
     * Positive reaction options for Senior Citizens Forum posts.
     *
     * @return array<string, string>
     */
    public static function seniorCitizensForumReactionOptions(): array
    {
        return [
            'Respect' => 'fa-solid fa-hands-praying',
            'Inspiring' => 'fa-solid fa-lightbulb',
            'Valuable Wisdom' => 'fa-solid fa-book-open',
            'Remarkable Journey' => 'fa-solid fa-road',
            'Helpful Advice' => 'fa-solid fa-hand-holding-heart',
        ];
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumReactionLabels(): array
    {
        return array_keys(self::seniorCitizensForumReactionOptions());
    }

    /**
     * Intergenerational connection tags for Senior Citizens Forum posts.
     *
     * @return list<string>
     */
    public static function seniorCitizensForumIntergenerationalConnections(): array
    {
        return [
            'Advice for Students',
            'Advice for Entrepreneurs',
            'Advice for Parents',
            'Advice for Society',
        ];
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumDigitalLegacyBenefits(): array
    {
        return [
            'Permanent archive',
            'Family access',
            'Printable PDF',
            'eBook generation',
        ];
    }

    /**
     * Main category choices for business posts.
     *
     * @return list<string>
     */
    public static function businessMainCategories(): array
    {
        return [
            'Entrepreneurship',
            'Startup',
            'Small Business',
            'Retail Business',
            'Manufacturing',
            'Service Business',
            'Home-Based Business',
            'Women Entrepreneurship',
            'Agriculture Business',
            'Construction Business',
            'Real Estate Business',
            'E-Commerce',
            'Marketing',
            'Finance',
            'Business Technology',
            'Leadership',
        ];
    }

    /**
     * Business content type choices grouped for the create/edit form.
     *
     * @return array<string, list<string>>
     */
    public static function businessContentTypeGroups(): array
    {
        return [
            'Type' => [
                'Business Article',
                'Success Story',
                'Case Study',
                'Business Idea',
            ],
            'Business Opportunity' => [
                'Market Analysis',
                'Business Guide',
                'Expert Advice',
                'Business News',
                'Industry Insight',
                'Business Report',
                'Investment Insight',
                'Customer Experience',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessContentTypes(): array
    {
        return array_values(array_merge(...array_values(self::businessContentTypeGroups())));
    }

    /**
     * @return list<string>
     */
    public static function businessTargetAudiences(): array
    {
        return [
            'Students',
            'Startup Founders',
            'Small Business Owners',
            'Retailers',
            'Manufacturers',
            'Women Entrepreneurs',
            'Freelancers',
            'Consultants',
            'Professionals',
            'Investors',
            'Farmers',
            'General Public',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessProfileTypes(): array
    {
        return [
            'Proprietorship',
            'Partnership',
            'LLP',
            'Private Limited',
            'OPC',
            'Freelancer',
            'Startup',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessIndustries(): array
    {
        return [
            'Construction',
            'Agriculture',
            'Technology',
            'Manufacturing',
            'Retail',
            'Services',
            'Real Estate',
            'Education',
            'Healthcare',
            'Others',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessStages(): array
    {
        return [
            'Idea Stage',
            'Startup',
            'Growing Business',
            'Established Business',
            'Expansion Phase',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessChallenges(): array
    {
        return [
            'Funding',
            'Marketing',
            'Customer Acquisition',
            'Hiring',
            'Operations',
            'Technology',
            'Competition',
            'Regulations',
            'Supply Chain',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessOpportunityTypes(): array
    {
        return [
            'Franchise',
            'Partnership',
            'Dealer Network',
            'Distributor Opportunity',
            'Investment Opportunity',
            'Collaboration',
            'Employment',
            'Training',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessMarketSegments(): array
    {
        return [
            'B2B',
            'B2C',
            'B2B2C',
            'Government',
            'Export',
            'Local Market',
            'National Market',
            'International Market',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessThemes(): array
    {
        return [
            'Innovation',
            'Leadership',
            'Marketing',
            'Sales',
            'Digital Transformation',
            'Customer Service',
            'Sustainability',
            'Women Empowerment',
            'Skill Development',
            'Technology',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessVideoTypes(): array
    {
        return [
            'Business Introduction',
            'Founder Interview',
            'Factory Tour',
            'Customer Testimonial',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessTagExamples(): array
    {
        return [
            'Startup',
            'Business Growth',
            'Marketing',
            'Entrepreneurship',
            'Retail',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessDefaultPollOptions(): array
    {
        return [
            'Marketing',
            'Finance',
            'Staff',
            'Technology',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessReactionLabels(): array
    {
        return [
            'Informative',
            'Excellent',
            'Inspiring',
            'Helpful',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessContactOptions(): array
    {
        return [
            'Contact Author',
            'Send Business Query',
            'Request Guidance',
        ];
    }

    /**
     * Recommended business content sections for the rich text editor.
     *
     * @return array<string, string>
     */
    public static function businessContentStructure(): array
    {
        return [
            'Business Problem' => 'What issue are you addressing?',
            'Background' => 'Context',
            'Solution' => 'Approach used',
            'Results' => 'Outcome',
            'Lessons Learned' => 'Key takeaways',
            'Recommendations' => 'Advice to others',
        ];
    }

    /**
     * Recommended Women's World content sections for the rich text editor.
     *
     * @return array<string, string>
     */
    public static function womensWorldContentStructure(): array
    {
        return [
            'Background' => 'Set the context for your story or topic.',
            'Challenge' => 'Describe the difficulty, situation, or issue faced.',
            'Experience' => 'Share what happened and how you navigated it.',
            'Lessons Learned' => 'Key takeaways from your journey.',
            'Advice to Others' => 'Practical guidance for readers in similar situations.',
            'Conclusion' => 'Closing message, reflection, or call to action.',
        ];
    }

    /**
     * Recommended Senior Citizens Forum content sections for the rich text editor.
     *
     * @return array<string, string>
     */
    public static function seniorCitizensForumContentStructure(): array
    {
        return [
            'Background' => 'Set the context — time, place, and situation.',
            'Experience' => 'Share what happened and how you lived through it.',
            'Lessons Learned' => 'Key takeaways from your journey.',
            'Advice' => 'Practical guidance for younger readers or the community.',
            'Conclusion' => 'Closing reflection or message.',
        ];
    }

    /**
     * Recommended Student Corner content sections for the rich text editor.
     *
     * @return array<string, string>
     */
    public static function studentCornerContentStructure(): array
    {
        return [
            'Introduction' => 'Introduce the topic and why it matters to students.',
            'Objective' => 'State what readers will learn or achieve.',
            'Main Content' => 'Share the core information, explanation, or experience.',
            'Learnings' => 'Highlight key takeaways from your study, project, or journey.',
            'Tips / Recommendations' => 'Practical advice for fellow students.',
            'Conclusion' => 'Closing summary, reflection, or call to action.',
        ];
    }

    /**
     * Legacy civic issue report types for older My Area posts.
     *
     * @return list<string>
     */
    public static function myAreaReportTypes(): array
    {
        return ['Water Issue', 'Road Damage', 'Garbage Problem', 'Street Light Problem', 'Flooding', 'Illegal Dumping'];
    }

    public static function labels(): array
    {
        return collect(self::types())->mapWithKeys(fn (array $type, string $key) => [$key => $type['label']])->all();
    }

    public static function categoriesFor(string $type): array
    {
        return self::types()[$type]['categories'] ?? [];
    }

    public static function isValidCategory(string $type, string $category): bool
    {
        if ($type === 'stories' && in_array($category, ['Real Life Experiences', 'Fiction'], true)) {
            return true;
        }

        if ($type === 'poetry' && in_array($category, [
            'Hindi Poetry', 'English Poetry', 'Urdu Poetry', 'Inspirational Poetry',
            "Children's Poetry", 'Patriotic Poetry', 'Love Poetry', 'Social Poetry',
        ], true)) {
            return true;
        }

        if ($type === 'childrens-corner') {
            return in_array($category, self::childrensCornerShareTypes(), true);
        }

        if ($type === 'awareness') {
            return in_array($category, self::awarenessCategories(), true);
        }

        if ($type === 'business') {
            return in_array($category, self::businessMainCategories(), true);
        }

        if ($type === 'womens-world') {
            return in_array($category, self::womensWorldMainCategories(), true);
        }

        if ($type === 'senior-citizens-forum') {
            return in_array($category, self::seniorCitizensForumMainCategories(), true);
        }

        if ($type === 'student-corner') {
            return in_array($category, self::studentCornerMainCategories(), true);
        }

        if ($type === 'youth-corner') {
            return in_array($category, self::youthCornerMainCategories(), true);
        }

        return in_array($category, self::categoriesFor($type), true);
    }

    public static function slugFor(string $label): string
    {
        return Str::slug($label);
    }
}
