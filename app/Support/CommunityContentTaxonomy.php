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
                'categories' => ['Startups', 'Small Business', 'Marketing', 'Finance', 'Success Stories', 'Business Tips'],
            ],
            'education' => [
                'label' => 'Education',
                'description' => 'Learning and education-focused posts.',
                'categories' => ['Career Guidance', 'Study Tips', 'Competitive Exams', 'Scholarships', 'Projects', 'Student Experiences'],
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
                'description' => 'A powerful community section.',
                'categories' => ['Working Women', 'Homemakers', 'Health', 'Entrepreneurship', 'Parenting', 'Self Development'],
            ],
            'senior-citizens-forum' => [
                'label' => 'Senior Citizens Forum',
                'description' => 'A highly underserved audience.',
                'categories' => ['Life Experiences', 'Health', 'Retirement Planning', 'Memoirs', 'Advice to Youth', 'Social Activities'],
            ],
            'youth-corner' => [
                'label' => 'Youth Corner',
                'description' => 'Youth-focused career, life, and inspiration posts.',
                'categories' => ['Career', 'Startups', 'Technology', 'Relationships', 'Motivation', 'Fitness'],
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

        return in_array($category, self::categoriesFor($type), true);
    }

    public static function slugFor(string $label): string
    {
        return Str::slug($label);
    }
}
