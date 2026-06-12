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
                'description' => 'Research and analytical content.',
                'categories' => ['Industry Reports', 'Market Analysis', 'Survey Reports', 'Economic Reports', 'Infrastructure Reports', 'Local Development Reports', 'Water & Environment Reports'],
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
                'categories' => ['National News', 'State News', 'Local News', 'Education News', 'Business News', 'Agriculture News', 'Environment News'],
            ],
            'stories' => [
                'label' => 'Stories',
                'description' => 'One of the highest engagement sections.',
                'categories' => ['Inspirational Stories', 'Motivational Stories', 'Social Stories', 'Family Stories', 'Short Stories', 'Fiction', 'Real Life Experiences'],
            ],
            'poetry' => [
                'label' => 'Poetry',
                'description' => 'Poetry submissions from the community.',
                'categories' => ['Hindi Poetry', 'English Poetry', 'Urdu Poetry', 'Inspirational Poetry', "Children's Poetry", 'Patriotic Poetry', 'Love Poetry', 'Social Poetry'],
            ],
            'biography' => [
                'label' => 'Biography',
                'description' => 'Write about inspiring personalities.',
                'categories' => ['Freedom Fighters', 'Scientists', 'Entrepreneurs', 'Teachers', 'Social Workers', 'Local Heroes'],
            ],
            'autobiography' => [
                'label' => 'Autobiography',
                'description' => 'Personal life journeys.',
                'categories' => ['Student Life', 'Entrepreneur Journey', 'Retirement Journey', "Women's Journey", 'Career Journey', 'Life Lessons'],
            ],
            'childrens-corner' => [
                'label' => "Children's Corner",
                'description' => 'A dedicated space for children.',
                'categories' => ['Stories', 'Drawings', 'Poems', 'School Projects', 'Quiz', 'Moral Stories', 'Science Experiments'],
                'features' => ['Parent approval option', 'School participation'],
            ],
            'awareness' => [
                'label' => 'Awareness',
                'description' => 'Public awareness campaigns.',
                'categories' => ['Health Awareness', 'Water Conservation', 'Environment Protection', 'Road Safety', 'Cyber Security', 'Women Safety', 'Financial Literacy', 'Drug Awareness'],
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
     * Report type choices for My Area civic issue posts.
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
        return in_array($category, self::categoriesFor($type), true);
    }

    public static function slugFor(string $label): string
    {
        return Str::slug($label);
    }
}
