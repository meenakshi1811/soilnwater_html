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
                'categories' => self::reportMainCategories(),
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
                'description' => 'Educational astrology, spiritual guidance, and traditional knowledge — presented as belief, opinion, or cultural practice.',
                'categories' => self::astroConsultancyMainCategories(),
                'monetization' => ['Paid consultations', 'Premium astrologer profiles', 'Verified consultant directory'],
            ],
            'religion-spirituality' => [
                'label' => 'Religion & Spirituality',
                'description' => 'To inspire peace, understanding, compassion, and respect for all faiths while preserving cultural and spiritual heritage.',
                'categories' => self::religionSpiritualityMainCategories(),
            ],
            'agriculture' => [
                'label' => 'Agriculture',
                'description' => 'Agriculture and farmer-focused content.',
                'categories' => self::agricultureMainCategories(),
            ],
            'environment' => [
                'label' => 'Environment',
                'description' => 'Environmental reporting, conservation action, and geo-tagged community content.',
                'categories' => self::environmentMainCategories(),
            ],
            'science-technology' => [
                'label' => 'Science & Technology',
                'description' => 'Research, innovation, engineering, and technology content for the SoilnWater community.',
                'categories' => self::scienceTechnologyMainCategories(),
            ],
            'local-voices' => [
                'label' => 'Local Voices',
                'description' => 'A location-centric community section where residents share opinions, concerns, and achievements.',
                'categories' => self::localVoiceMainCategories(),
                'examples' => ['Water issues in Prem Nagar', 'Road repair suggestion', 'Local hero story', 'Civic complaint'],
            ],
            'community-issues' => [
                'label' => 'Community Issues',
                'description' => 'Report civic issues, public concerns, and community problems with clear categories and action requests.',
                'categories' => self::communityIssueMainCategories(),
            ],
            'creative-corner' => [
                'label' => 'Creative Corner',
                'description' => 'Share artwork, photography, crafts, music, design, and creative projects with the SoilnWater community.',
                'categories' => self::creativeCornerMainCategories(),
            ],
            'competitions' => [
                'label' => 'Competitions',
                'description' => 'Create and manage contests with registration, judging, prizes, and participant submissions.',
                'categories' => self::competitionsMainCategories(),
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
            ->except(['my-voice'])
            ->all();
    }

    /**
     * Eight top-level hub sections for the /community landing experience.
     * Each section groups related content types (subsections).
     *
     * @return array<string, array{label: string, tagline: string, description: string, icon: string, accent: string, types: list<string>}>
     */
    public static function hubSections(): array
    {
        return [
            'knowledge-news' => [
                'label' => 'Knowledge & News',
                'tagline' => 'Learn, research, and stay informed',
                'description' => 'Articles, research reports, news updates, and science & technology insights.',
                'icon' => 'fa-book-open',
                'accent' => '#1f66b4',
                'types' => ['articles', 'reports', 'news', 'science-technology'],
            ],
            'stories-literature' => [
                'label' => 'Stories & Literature',
                'tagline' => 'Share voices that inspire',
                'description' => 'Stories, poetry, biographies, and personal life journeys.',
                'icon' => 'fa-feather-pointed',
                'accent' => '#e65100',
                'types' => ['stories', 'poetry', 'biography', 'autobiography'],
            ],
            'life-learning' => [
                'label' => 'Life & Learning',
                'tagline' => 'Every age, every journey',
                'description' => 'Children, students, youth, seniors, women, and wellness-focused community content.',
                'icon' => 'fa-graduation-cap',
                'accent' => '#00838f',
                'types' => ['childrens-corner', 'student-corner', 'youth-corner', 'senior-citizens-forum', 'womens-world', 'health-wellness'],
            ],
            'environment-agriculture' => [
                'label' => 'Environment & Agriculture',
                'tagline' => 'Planet and soil matters',
                'description' => 'Farming, environment, conservation, and community awareness campaigns.',
                'icon' => 'fa-seedling',
                'accent' => '#2e7d32',
                'types' => ['agriculture', 'environment', 'awareness'],
            ],
            'career-business' => [
                'label' => 'Career & Business',
                'tagline' => 'Grow professionally',
                'description' => 'Career guidance, jobs, employment opportunities, and business stories.',
                'icon' => 'fa-briefcase',
                'accent' => '#3949ab',
                'types' => ['career', 'jobs-employment', 'business'],
            ],
            'culture-spirituality' => [
                'label' => 'Culture & Spirituality',
                'tagline' => 'Heritage, faith, and travel',
                'description' => 'Culture, travel diaries, religion, spirituality, and astro consultancy.',
                'icon' => 'fa-om',
                'accent' => '#7e57c2',
                'types' => ['culture-heritage', 'travel-diaries', 'religion-spirituality', 'astro-consultancy'],
            ],
            'local-civic' => [
                'label' => 'Local Voices & Civic',
                'tagline' => 'Speak up for your community',
                'description' => 'Local issues, civic reports, community concerns, and public opinions.',
                'icon' => 'fa-bullhorn',
                'accent' => '#c62828',
                'types' => ['local-voices', 'community-issues', 'opinions-views'],
            ],
            'creative-community' => [
                'label' => 'Creative & Community',
                'tagline' => 'Create, compete, and connect',
                'description' => 'Creative works, competitions, and open community discussions.',
                'icon' => 'fa-heart',
                'accent' => '#d81b60',
                'types' => ['creative-corner', 'competitions', 'discussions'],
            ],
        ];
    }

    public static function hubSectionForType(string $type): ?string
    {
        foreach (self::hubSections() as $key => $section) {
            if (in_array($type, $section['types'], true)) {
                return $key;
            }
        }

        return null;
    }

    public static function typeIcon(string $type): string
    {
        return match ($type) {
            'articles' => 'fa-file-lines',
            'reports' => 'fa-chart-column',
            'news' => 'fa-newspaper',
            'science-technology' => 'fa-flask',
            'stories' => 'fa-book',
            'poetry' => 'fa-feather',
            'biography' => 'fa-user-pen',
            'autobiography' => 'fa-book-open-reader',
            'childrens-corner' => 'fa-child',
            'student-corner' => 'fa-graduation-cap',
            'youth-corner' => 'fa-bolt',
            'senior-citizens-forum' => 'fa-person-cane',
            'womens-world' => 'fa-venus',
            'health-wellness' => 'fa-heart-pulse',
            'career' => 'fa-user-tie',
            'jobs-employment' => 'fa-briefcase',
            'business' => 'fa-building',
            'agriculture' => 'fa-wheat-awn',
            'environment' => 'fa-leaf',
            'awareness' => 'fa-bullhorn',
            'culture-heritage' => 'fa-landmark',
            'travel-diaries' => 'fa-plane',
            'religion-spirituality' => 'fa-om',
            'astro-consultancy' => 'fa-star',
            'local-voices' => 'fa-microphone-lines',
            'community-issues' => 'fa-triangle-exclamation',
            'opinions-views' => 'fa-comments',
            'creative-corner' => 'fa-palette',
            'competitions' => 'fa-trophy',
            'discussions' => 'fa-people-group',
            default => 'fa-folder-open',
        };
    }

    public static function resolveActiveHubSection(?string $hubParam, ?string $typeParam): ?string
    {
        if ($hubParam !== null && $hubParam !== '' && isset(self::hubSections()[$hubParam])) {
            return $hubParam;
        }

        if ($typeParam !== null && $typeParam !== '') {
            return self::hubSectionForType($typeParam);
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function hubSectionTypeKeys(?string $hubKey): array
    {
        if ($hubKey === null || $hubKey === '' || ! isset(self::hubSections()[$hubKey])) {
            return [];
        }

        return self::hubSections()[$hubKey]['types'];
    }

    /**
     * Sidebar navigation for the news portal layout.
     *
     * @return list<array{key: string, label: string, icon: string}>
     */
    public static function newsPortalSidebarTypes(): array
    {
        return [
            ['key' => 'discussions', 'label' => 'Discussions', 'icon' => 'fa-comments'],
            ['key' => 'news', 'label' => 'News', 'icon' => 'fa-newspaper'],
            ['key' => 'science-technology', 'label' => 'Science & Tech', 'icon' => 'fa-flask'],
            ['key' => 'articles', 'label' => 'Articles', 'icon' => 'fa-file-lines'],
            ['key' => 'stories', 'label' => 'Stories', 'icon' => 'fa-book-open'],
            ['key' => 'poetry', 'label' => 'Poetry', 'icon' => 'fa-feather-pointed'],
            ['key' => 'competitions', 'label' => 'Events', 'icon' => 'fa-calendar-days'],
            ['key' => 'creative-corner', 'label' => 'Photos', 'icon' => 'fa-image'],
            ['key' => 'reports', 'label' => 'Reports', 'icon' => 'fa-chart-column'],
        ];
    }

    /**
     * Content types that use the news-style portal layout.
     *
     * @return list<string>
     */
    public static function contentPortalTypes(): array
    {
        return [
            'news',
            'articles',
            'reports',
            'science-technology',
            'stories',
            'poetry',
            'biography',
            'autobiography',
            'childrens-corner',
            'student-corner',
            'youth-corner',
            'senior-citizens-forum',
            'womens-world',
            'health-wellness',
            'agriculture',
            'environment',
            'awareness',
            'career',
            'jobs-employment',
            'business',
            'culture-heritage',
            'travel-diaries',
            'religion-spirituality',
            'astro-consultancy',
            'local-voices',
            'community-issues',
            'opinions-views',
            'creative-corner',
            'competitions',
            'discussions',
        ];
    }

    /**
     * @return list<string>
     */
    public static function hubPortalKeys(): array
    {
        return [
            self::knowledgeNewsHubKey(),
            self::storiesLiteratureHubKey(),
            self::lifeLearningHubKey(),
            'environment-agriculture',
            'career-business',
            'culture-spirituality',
            'local-civic',
            'creative-community',
        ];
    }

    public static function knowledgeNewsHubKey(): string
    {
        return 'knowledge-news';
    }

    public static function storiesLiteratureHubKey(): string
    {
        return 'stories-literature';
    }

    public static function lifeLearningHubKey(): string
    {
        return 'life-learning';
    }

    /**
     * @return list<string>
     */
    public static function storiesLiteratureTypes(): array
    {
        return self::hubSectionTypeKeys(self::storiesLiteratureHubKey());
    }

    /**
     * @return list<string>
     */
    public static function lifeLearningTypes(): array
    {
        return self::hubSectionTypeKeys(self::lifeLearningHubKey());
    }

    /**
     * @return list<string>
     */
    public static function environmentAgricultureTypes(): array
    {
        return self::hubSectionTypeKeys('environment-agriculture');
    }

    /**
     * @return list<string>
     */
    public static function careerBusinessTypes(): array
    {
        return self::hubSectionTypeKeys('career-business');
    }

    /**
     * @return list<string>
     */
    public static function cultureSpiritualityTypes(): array
    {
        return self::hubSectionTypeKeys('culture-spirituality');
    }

    /**
     * @return list<string>
     */
    public static function localCivicTypes(): array
    {
        return self::hubSectionTypeKeys('local-civic');
    }

    /**
     * @return list<string>
     */
    public static function creativeCommunityTypes(): array
    {
        return self::hubSectionTypeKeys('creative-community');
    }

    public static function isHubPortalKey(?string $hubKey): bool
    {
        return in_array((string) $hubKey, self::hubPortalKeys(), true);
    }

    public static function resolveHubPortalKey(?string $portalKey, ?string $activeHub = null): ?string
    {
        if (self::isHubPortalKey($portalKey)) {
            return $portalKey;
        }

        if (self::isHubPortalKey($activeHub)) {
            return $activeHub;
        }

        $typeHub = self::hubSectionForType((string) $portalKey);

        return self::isHubPortalKey($typeHub) ? $typeHub : null;
    }

    public static function hubPortalDefaultCreateType(string $hubKey): string
    {
        return self::hubSectionTypeKeys($hubKey)[0] ?? 'news';
    }

    /**
     * @return list<array{key: string, label: string, icon: string}>
     */
    public static function hubPortalTypeTabs(string $hubKey): array
    {
        if (! self::isHubPortalKey($hubKey)) {
            return [];
        }

        return collect(self::hubSectionTypeKeys($hubKey))
            ->map(fn (string $typeKey): array => [
                'key' => $typeKey,
                'label' => self::types()[$typeKey]['label'] ?? \Illuminate\Support\Str::headline($typeKey),
                'icon' => self::typeIcon($typeKey),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function hubPortalTypeFilterLabels(string $hubKey): array
    {
        return collect(self::hubPortalTypeTabs($hubKey))
            ->mapWithKeys(fn (array $item): array => [$item['label'] => $item['key']])
            ->all();
    }

    public static function usesContentPortal(?string $type): bool
    {
        return in_array((string) $type, self::contentPortalTypes(), true);
    }

    public static function shouldUsePortalListing(?string $type, ?string $hub, bool $isAuthorPage = false): bool
    {
        if ($isAuthorPage) {
            return false;
        }

        if ($type !== null && $type !== '' && self::usesContentPortal($type)) {
            return true;
        }

        return self::isHubPortalKey($hub);
    }

    /**
     * @return array{content_types: list<string>, portal_key: string}
     */
    public static function resolvePortalScope(?string $type, ?string $hub): array
    {
        if ($type !== null && $type !== '' && self::usesContentPortal($type)) {
            return [
                'content_types' => [$type],
                'portal_key' => $type,
            ];
        }

        if (self::isHubPortalKey($hub)) {
            return [
                'content_types' => self::hubSectionTypeKeys($hub),
                'portal_key' => $hub,
            ];
        }

        return [
            'content_types' => [$type ?: 'news'],
            'portal_key' => $type ?: 'news',
        ];
    }

    /**
     * @return list<array{key: string, label: string, icon: string}>
     */
    public static function portalSidebarTypes(?string $portalKey, ?string $activeHub = null): array
    {
        $hubKey = self::resolveHubPortalKey($portalKey, $activeHub);
        if ($hubKey !== null) {
            return self::hubPortalTypeTabs($hubKey);
        }

        return self::newsPortalSidebarTypes();
    }

    /**
     * @return list<array{key: string, label: string, icon: string}>
     */
    public static function literaturePortalSidebarTypes(): array
    {
        return [
            ['key' => 'stories', 'label' => 'Stories', 'icon' => 'fa-book-open'],
            ['key' => 'poetry', 'label' => 'Poetry', 'icon' => 'fa-feather-pointed'],
            ['key' => 'biography', 'label' => 'Biography', 'icon' => 'fa-user-pen'],
            ['key' => 'autobiography', 'label' => 'Autobiography', 'icon' => 'fa-book-open-reader'],
        ];
    }

    /**
     * @return list<array{key: string, label: string, icon: string}>
     */
    public static function lifeLearningPortalSidebarTypes(): array
    {
        return [
            ['key' => 'childrens-corner', 'label' => "Children's Corner", 'icon' => 'fa-child'],
            ['key' => 'student-corner', 'label' => 'Student Corner', 'icon' => 'fa-graduation-cap'],
            ['key' => 'youth-corner', 'label' => 'Youth Corner', 'icon' => 'fa-user-group'],
            ['key' => 'senior-citizens-forum', 'label' => 'Senior Citizens Forum', 'icon' => 'fa-person-cane'],
            ['key' => 'womens-world', 'label' => "Women's World", 'icon' => 'fa-venus'],
            ['key' => 'health-wellness', 'label' => 'Health & Wellness', 'icon' => 'fa-heart-pulse'],
        ];
    }

    public static function portalSidebarUsesTypeFilters(string $portalKey): bool
    {
        return self::isHubPortalKey($portalKey);
    }

    /**
     * @return array{
     *     label_short: string,
     *     featured_badge: string,
     *     latest_heading: string,
     *     create_label: string,
     *     load_more_label: string,
     *     featured_icon: string,
     *     breaking_heading: string,
     *     breaking_button: string,
     *     trending_heading: string,
     *     trending_button: string,
     *     related_heading: string,
     *     related_button: string,
     *     top_badge: string,
     *     breaking_badge: string,
     *     back_label: string,
     *     categories_heading: string,
     *     empty_create_label: string
     * }
     */
    public static function hubSectionPortalCopy(string $hubKey): array
    {
        $section = self::hubSections()[$hubKey] ?? null;
        $label = $section['label'] ?? 'Community';
        $icon = $section['icon'] ?? 'fa-folder-open';

        return [
            'label_short' => $label,
            'featured_badge' => 'TOP POST',
            'latest_heading' => 'Latest '.$label,
            'create_label' => 'Publish Post',
            'load_more_label' => 'Load More Posts',
            'featured_icon' => $icon,
            'breaking_heading' => 'FEATURED POSTS',
            'breaking_button' => 'View All Featured',
            'trending_heading' => 'Trending Posts',
            'trending_button' => 'View All Trending Posts',
            'related_heading' => 'Related Posts',
            'related_button' => 'View More Related Posts',
            'top_badge' => 'Featured Post',
            'breaking_badge' => 'Featured',
            'back_label' => 'Back to '.$label,
            'categories_heading' => 'Browse by type',
            'empty_create_label' => 'Publish post',
        ];
    }

    /**
     * @return array{
     *     label_short: string,
     *     featured_badge: string,
     *     latest_heading: string,
     *     create_label: string,
     *     load_more_label: string,
     *     featured_icon: string,
     *     breaking_heading: string,
     *     breaking_button: string,
     *     trending_heading: string,
     *     trending_button: string,
     *     related_heading: string,
     *     related_button: string,
     *     top_badge: string,
     *     breaking_badge: string,
     *     back_label: string,
     *     categories_heading: string,
     *     empty_create_label: string
     * }
     */
    public static function typeStandardPortalCopy(string $typeKey): array
    {
        $label = self::types()[$typeKey]['label'] ?? 'News';
        $icon = self::typeIcon($typeKey);

        return [
            'label_short' => $label,
            'featured_badge' => 'TOP POST',
            'latest_heading' => 'Latest '.$label,
            'create_label' => 'Publish Post',
            'load_more_label' => 'Load More Posts',
            'featured_icon' => $icon,
            'breaking_heading' => 'FEATURED POSTS',
            'breaking_button' => 'View All Featured',
            'trending_heading' => 'Trending Posts',
            'trending_button' => 'View All Trending Posts',
            'related_heading' => 'Related Posts',
            'related_button' => 'View More Related Posts',
            'top_badge' => 'Featured Post',
            'breaking_badge' => 'Featured',
            'back_label' => 'Back to '.$label,
            'categories_heading' => $label.' Categories',
            'empty_create_label' => 'Publish post',
        ];
    }

    /**
     * @return array{
     *     label_short: string,
     *     featured_badge: string,
     *     latest_heading: string,
     *     create_label: string,
     *     load_more_label: string,
     *     featured_icon: string,
     *     breaking_heading: string,
     *     breaking_button: string,
     *     trending_heading: string,
     *     trending_button: string,
     *     related_heading: string,
     *     related_button: string,
     *     top_badge: string,
     *     breaking_badge: string,
     *     back_label: string,
     *     categories_heading: string,
     *     empty_create_label: string
     * }
     */
    public static function defaultNewsPortalCopy(): array
    {
        return [
            'label_short' => 'News',
            'featured_badge' => 'TOP NEWS',
            'latest_heading' => 'Latest News',
            'create_label' => 'Publish News',
            'load_more_label' => 'Load More News',
            'featured_icon' => 'fa-newspaper',
            'breaking_heading' => 'BREAKING NEWS',
            'breaking_button' => 'View All Breaking News',
            'trending_heading' => 'Trending News',
            'trending_button' => 'View All Trending News',
            'related_heading' => 'Related News',
            'related_button' => 'View More Related News',
            'top_badge' => 'Top News',
            'breaking_badge' => 'Breaking News',
            'back_label' => 'Back to News',
            'categories_heading' => 'News Categories',
            'empty_create_label' => 'Publish news',
        ];
    }

    /**
     * @return list<string>
     */
    public static function hubPortalSidebarCategories(string $hubKey): array
    {
        $section = self::hubSections()[$hubKey] ?? null;
        if ($section === null) {
            return self::newsPortalSidebarCategories();
        }

        return array_merge(
            ['All '.$section['label']],
            collect(self::hubSectionTypeKeys($hubKey))
                ->map(fn (string $typeKey): string => self::types()[$typeKey]['label'] ?? \Illuminate\Support\Str::headline($typeKey))
                ->all()
        );
    }

    /**
     * @return list<string>
     */
    public static function typePortalSidebarCategories(string $typeKey, int $limit = 11): array
    {
        $type = self::types()[$typeKey] ?? null;
        if ($type === null) {
            return self::newsPortalSidebarCategories();
        }

        return array_merge(
            ['All '.$type['label']],
            array_slice($type['categories'], 0, $limit)
        );
    }

    /**
     * @return array<string, string>
     */
    public static function genericTypeCategoryIcons(string $typeKey): array
    {
        $categories = self::types()[$typeKey]['categories'] ?? [];
        if ($categories === []) {
            return self::newsCategoryIcons();
        }

        return collect($categories)
            ->mapWithKeys(fn (string $category): array => [$category => 'fa-tag'])
            ->all();
    }

    /**
     * UI copy for the shared news-style portal layout.
     *
     * @return array{
     *     label_short: string,
     *     featured_badge: string,
     *     latest_heading: string,
     *     create_label: string,
     *     load_more_label: string,
     *     featured_icon: string,
     *     breaking_heading: string,
     *     breaking_button: string,
     *     trending_heading: string,
     *     trending_button: string,
     *     related_heading: string,
     *     related_button: string,
     *     top_badge: string,
     *     breaking_badge: string,
     *     back_label: string,
     *     categories_heading: string,
     *     empty_create_label: string
     * }
     */
    public static function portalCopy(string $contentType): array
    {
        $label = self::types()[$contentType]['label'] ?? 'News';

        return match ($contentType) {
            'articles' => [
                'label_short' => 'Articles',
                'featured_badge' => 'TOP ARTICLE',
                'latest_heading' => 'Latest Articles',
                'create_label' => 'Publish Article',
                'load_more_label' => 'Load More Articles',
                'featured_icon' => 'fa-file-lines',
                'breaking_heading' => 'FEATURED ARTICLES',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Articles',
                'trending_button' => 'View All Trending Articles',
                'related_heading' => 'Related Articles',
                'related_button' => 'View More Related Articles',
                'top_badge' => 'Top Article',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Articles',
                'categories_heading' => 'Article Categories',
                'empty_create_label' => 'Publish article',
            ],
            'reports' => [
                'label_short' => 'Reports',
                'featured_badge' => 'TOP REPORT',
                'latest_heading' => 'Latest Reports',
                'create_label' => 'Publish Report',
                'load_more_label' => 'Load More Reports',
                'featured_icon' => 'fa-chart-column',
                'breaking_heading' => 'FEATURED REPORTS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Reports',
                'trending_button' => 'View All Trending Reports',
                'related_heading' => 'Related Reports',
                'related_button' => 'View More Related Reports',
                'top_badge' => 'Top Report',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Reports',
                'categories_heading' => 'Report Categories',
                'empty_create_label' => 'Publish report',
            ],
            'science-technology' => [
                'label_short' => 'Science & Tech',
                'featured_badge' => 'TOP STORY',
                'latest_heading' => 'Latest Science & Technology',
                'create_label' => 'Publish Project',
                'load_more_label' => 'Load More Projects',
                'featured_icon' => 'fa-flask',
                'breaking_heading' => 'FEATURED PROJECTS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Projects',
                'trending_button' => 'View All Trending Projects',
                'related_heading' => 'Related Projects',
                'related_button' => 'View More Related Projects',
                'top_badge' => 'Featured Project',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Science & Tech',
                'categories_heading' => 'Science & Tech Categories',
                'empty_create_label' => 'Publish project',
            ],
            'stories-literature' => [
                'label_short' => 'Stories & Literature',
                'featured_badge' => 'TOP READ',
                'latest_heading' => 'Latest Stories & Literature',
                'create_label' => 'Publish Story',
                'load_more_label' => 'Load More Reads',
                'featured_icon' => 'fa-feather-pointed',
                'breaking_heading' => 'FEATURED READS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Reads',
                'trending_button' => 'View All Trending Reads',
                'related_heading' => 'Related Reads',
                'related_button' => 'View More Related Reads',
                'top_badge' => 'Featured Read',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Stories & Literature',
                'categories_heading' => 'Browse by type',
                'empty_create_label' => 'Publish story',
            ],
            'stories' => [
                'label_short' => 'Stories',
                'featured_badge' => 'TOP STORY',
                'latest_heading' => 'Latest Stories',
                'create_label' => 'Publish Story',
                'load_more_label' => 'Load More Stories',
                'featured_icon' => 'fa-book-open',
                'breaking_heading' => 'FEATURED STORIES',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Stories',
                'trending_button' => 'View All Trending Stories',
                'related_heading' => 'Related Stories',
                'related_button' => 'View More Related Stories',
                'top_badge' => 'Top Story',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Stories',
                'categories_heading' => 'Story Categories',
                'empty_create_label' => 'Publish story',
            ],
            'poetry' => [
                'label_short' => 'Poetry',
                'featured_badge' => 'TOP POEM',
                'latest_heading' => 'Latest Poetry',
                'create_label' => 'Publish Poem',
                'load_more_label' => 'Load More Poetry',
                'featured_icon' => 'fa-feather-pointed',
                'breaking_heading' => 'FEATURED POETRY',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Poetry',
                'trending_button' => 'View All Trending Poetry',
                'related_heading' => 'Related Poetry',
                'related_button' => 'View More Related Poetry',
                'top_badge' => 'Featured Poem',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Poetry',
                'categories_heading' => 'Poetry Categories',
                'empty_create_label' => 'Publish poem',
            ],
            'biography' => [
                'label_short' => 'Biography',
                'featured_badge' => 'TOP BIOGRAPHY',
                'latest_heading' => 'Latest Biographies',
                'create_label' => 'Publish Biography',
                'load_more_label' => 'Load More Biographies',
                'featured_icon' => 'fa-user-pen',
                'breaking_heading' => 'FEATURED BIOGRAPHIES',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Biographies',
                'trending_button' => 'View All Trending Biographies',
                'related_heading' => 'Related Biographies',
                'related_button' => 'View More Related Biographies',
                'top_badge' => 'Featured Biography',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Biography',
                'categories_heading' => 'Biography Categories',
                'empty_create_label' => 'Publish biography',
            ],
            'autobiography' => [
                'label_short' => 'Autobiography',
                'featured_badge' => 'TOP JOURNEY',
                'latest_heading' => 'Latest Autobiographies',
                'create_label' => 'Publish Autobiography',
                'load_more_label' => 'Load More Autobiographies',
                'featured_icon' => 'fa-book-open-reader',
                'breaking_heading' => 'FEATURED JOURNEYS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Autobiographies',
                'trending_button' => 'View All Trending Autobiographies',
                'related_heading' => 'Related Autobiographies',
                'related_button' => 'View More Related Autobiographies',
                'top_badge' => 'Featured Journey',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Autobiography',
                'categories_heading' => 'Autobiography Categories',
                'empty_create_label' => 'Publish autobiography',
            ],
            'life-learning' => [
                'label_short' => 'Life & Learning',
                'featured_badge' => 'TOP POST',
                'latest_heading' => 'Latest Life & Learning',
                'create_label' => 'Publish Post',
                'load_more_label' => 'Load More Posts',
                'featured_icon' => 'fa-graduation-cap',
                'breaking_heading' => 'FEATURED POSTS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Posts',
                'trending_button' => 'View All Trending Posts',
                'related_heading' => 'Related Posts',
                'related_button' => 'View More Related Posts',
                'top_badge' => 'Featured Post',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Life & Learning',
                'categories_heading' => 'Browse by type',
                'empty_create_label' => 'Publish post',
            ],
            'childrens-corner' => [
                'label_short' => "Children's Corner",
                'featured_badge' => 'TOP POST',
                'latest_heading' => "Latest Children's Corner",
                'create_label' => 'Publish Post',
                'load_more_label' => 'Load More Posts',
                'featured_icon' => 'fa-child',
                'breaking_heading' => 'FEATURED POSTS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Posts',
                'trending_button' => 'View All Trending Posts',
                'related_heading' => 'Related Posts',
                'related_button' => 'View More Related Posts',
                'top_badge' => 'Featured Post',
                'breaking_badge' => 'Featured',
                'back_label' => "Back to Children's Corner",
                'categories_heading' => "Children's Corner Categories",
                'empty_create_label' => 'Publish post',
            ],
            'student-corner' => [
                'label_short' => 'Student Corner',
                'featured_badge' => 'TOP POST',
                'latest_heading' => 'Latest Student Corner',
                'create_label' => 'Publish Post',
                'load_more_label' => 'Load More Posts',
                'featured_icon' => 'fa-graduation-cap',
                'breaking_heading' => 'FEATURED POSTS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Posts',
                'trending_button' => 'View All Trending Posts',
                'related_heading' => 'Related Posts',
                'related_button' => 'View More Related Posts',
                'top_badge' => 'Featured Post',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Student Corner',
                'categories_heading' => 'Student Corner Categories',
                'empty_create_label' => 'Publish post',
            ],
            'youth-corner' => [
                'label_short' => 'Youth Corner',
                'featured_badge' => 'TOP POST',
                'latest_heading' => 'Latest Youth Corner',
                'create_label' => 'Publish Post',
                'load_more_label' => 'Load More Posts',
                'featured_icon' => 'fa-user-group',
                'breaking_heading' => 'FEATURED POSTS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Posts',
                'trending_button' => 'View All Trending Posts',
                'related_heading' => 'Related Posts',
                'related_button' => 'View More Related Posts',
                'top_badge' => 'Featured Post',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Youth Corner',
                'categories_heading' => 'Youth Corner Categories',
                'empty_create_label' => 'Publish post',
            ],
            'senior-citizens-forum' => [
                'label_short' => 'Senior Citizens Forum',
                'featured_badge' => 'TOP POST',
                'latest_heading' => 'Latest Senior Citizens Forum',
                'create_label' => 'Publish Post',
                'load_more_label' => 'Load More Posts',
                'featured_icon' => 'fa-person-cane',
                'breaking_heading' => 'FEATURED POSTS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Posts',
                'trending_button' => 'View All Trending Posts',
                'related_heading' => 'Related Posts',
                'related_button' => 'View More Related Posts',
                'top_badge' => 'Featured Post',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Senior Citizens Forum',
                'categories_heading' => 'Senior Citizens Categories',
                'empty_create_label' => 'Publish post',
            ],
            'womens-world' => [
                'label_short' => "Women's World",
                'featured_badge' => 'TOP POST',
                'latest_heading' => "Latest Women's World",
                'create_label' => 'Publish Post',
                'load_more_label' => 'Load More Posts',
                'featured_icon' => 'fa-venus',
                'breaking_heading' => 'FEATURED POSTS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Posts',
                'trending_button' => 'View All Trending Posts',
                'related_heading' => 'Related Posts',
                'related_button' => 'View More Related Posts',
                'top_badge' => 'Featured Post',
                'breaking_badge' => 'Featured',
                'back_label' => "Back to Women's World",
                'categories_heading' => "Women's World Categories",
                'empty_create_label' => 'Publish post',
            ],
            'health-wellness' => [
                'label_short' => 'Health & Wellness',
                'featured_badge' => 'TOP POST',
                'latest_heading' => 'Latest Health & Wellness',
                'create_label' => 'Publish Post',
                'load_more_label' => 'Load More Posts',
                'featured_icon' => 'fa-heart-pulse',
                'breaking_heading' => 'FEATURED POSTS',
                'breaking_button' => 'View All Featured',
                'trending_heading' => 'Trending Posts',
                'trending_button' => 'View All Trending Posts',
                'related_heading' => 'Related Posts',
                'related_button' => 'View More Related Posts',
                'top_badge' => 'Featured Post',
                'breaking_badge' => 'Featured',
                'back_label' => 'Back to Health & Wellness',
                'categories_heading' => 'Health & Wellness Categories',
                'empty_create_label' => 'Publish post',
            ],
            'environment-agriculture' => self::hubSectionPortalCopy('environment-agriculture'),
            'knowledge-news' => self::hubSectionPortalCopy('knowledge-news'),
            'career-business' => self::hubSectionPortalCopy('career-business'),
            'culture-spirituality' => self::hubSectionPortalCopy('culture-spirituality'),
            'local-civic' => self::hubSectionPortalCopy('local-civic'),
            'creative-community' => self::hubSectionPortalCopy('creative-community'),
            default => self::usesContentPortal($contentType) && isset(self::types()[$contentType])
                ? self::typeStandardPortalCopy($contentType)
                : self::defaultNewsPortalCopy(),
        };
    }

    /**
     * @return list<string>
     */
    public static function portalSidebarCategories(string $contentType): array
    {
        return match ($contentType) {
            'science-technology' => self::scienceTechnologyPortalSidebarCategories(),
            'articles' => self::articlesPortalSidebarCategories(),
            'reports' => self::reportsPortalSidebarCategories(),
            'stories-literature' => self::storiesLiteraturePortalSidebarCategories(),
            'life-learning' => self::lifeLearningPortalSidebarCategories(),
            'stories' => self::storiesPortalSidebarCategories(),
            'poetry' => self::poetryPortalSidebarCategories(),
            'biography' => self::biographyPortalSidebarCategories(),
            'autobiography' => self::autobiographyPortalSidebarCategories(),
            'childrens-corner' => self::childrensCornerPortalSidebarCategories(),
            'student-corner' => self::studentCornerPortalSidebarCategories(),
            'youth-corner' => self::youthCornerPortalSidebarCategories(),
            'senior-citizens-forum' => self::seniorCitizensForumPortalSidebarCategories(),
            'womens-world' => self::womensWorldPortalSidebarCategories(),
            'health-wellness' => self::healthWellnessPortalSidebarCategories(),
            'environment-agriculture' => self::hubPortalSidebarCategories('environment-agriculture'),
            'knowledge-news' => self::hubPortalSidebarCategories('knowledge-news'),
            'career-business' => self::hubPortalSidebarCategories('career-business'),
            'culture-spirituality' => self::hubPortalSidebarCategories('culture-spirituality'),
            'local-civic' => self::hubPortalSidebarCategories('local-civic'),
            'creative-community' => self::hubPortalSidebarCategories('creative-community'),
            default => self::usesContentPortal($contentType) && isset(self::types()[$contentType])
                ? self::typePortalSidebarCategories($contentType)
                : self::newsPortalSidebarCategories(),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function portalCategoryIcons(string $contentType): array
    {
        return match ($contentType) {
            'news' => self::newsCategoryIcons(),
            'science-technology' => self::scienceTechnologyCategoryIcons(),
            'articles' => self::articlesCategoryIcons(),
            'reports' => self::reportsCategoryIcons(),
            'stories' => self::storiesCategoryIcons(),
            'poetry' => self::poetryCategoryIcons(),
            'biography' => self::biographyCategoryIcons(),
            'autobiography' => self::autobiographyCategoryIcons(),
            'childrens-corner' => self::childrensCornerCategoryIcons(),
            'student-corner' => self::studentCornerCategoryIcons(),
            'youth-corner' => self::youthCornerCategoryIcons(),
            'senior-citizens-forum' => self::seniorCitizensForumCategoryIcons(),
            'womens-world' => self::womensWorldCategoryIcons(),
            'health-wellness' => self::healthWellnessCategoryIcons(),
            default => self::genericTypeCategoryIcons($contentType),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function newsCategoryIcons(): array
    {
        return [
            'Local News' => 'fa-location-dot',
            'State News' => 'fa-map',
            'National News' => 'fa-flag',
            'International News' => 'fa-globe',
            'Business News' => 'fa-briefcase',
            'Education News' => 'fa-graduation-cap',
            'Agriculture News' => 'fa-seedling',
            'Water News' => 'fa-droplet',
            'Environment News' => 'fa-leaf',
            'Technology News' => 'fa-microchip',
            'Health News' => 'fa-heart-pulse',
            'Sports News' => 'fa-futbol',
            'Entertainment News' => 'fa-film',
            'Community News' => 'fa-people-group',
            'Government News' => 'fa-landmark',
            'Infrastructure News' => 'fa-road',
        ];
    }

    /**
     * Curated news sidebar categories (display labels).
     *
     * @return list<string>
     */
    public static function newsPortalSidebarCategories(): array
    {
        return [
            'All News',
            'Local News',
            'Agriculture News',
            'Environment News',
            'Business News',
            'Education News',
            'Health News',
            'Technology News',
            'Government News',
            'Infrastructure News',
            'Community News',
            'Sports News',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyPortalSidebarCategories(): array
    {
        return [
            'All Science & Technology',
            'Agricultural Technology',
            'Artificial Intelligence',
            'Environmental Science',
            'Water Technology',
            'Renewable Energy',
            'Robotics',
            'Software Development',
            'Internet of Things (IoT)',
            'Medical Technology',
            'Research & Innovation',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function scienceTechnologyCategoryIcons(): array
    {
        return [
            'Agricultural Technology' => 'fa-seedling',
            'Artificial Intelligence' => 'fa-brain',
            'Electronics' => 'fa-microchip',
            'Electrical Engineering' => 'fa-bolt',
            'Mechanical Engineering' => 'fa-gears',
            'Civil Engineering' => 'fa-bridge',
            'Environmental Science' => 'fa-leaf',
            'Water Technology' => 'fa-droplet',
            'Renewable Energy' => 'fa-solar-panel',
            'Robotics' => 'fa-robot',
            'Automation' => 'fa-sliders',
            'Software Development' => 'fa-code',
            'Mobile Applications' => 'fa-mobile-screen',
            'Internet of Things (IoT)' => 'fa-wifi',
            'Cyber Security' => 'fa-shield-halved',
            'Space Science' => 'fa-rocket',
            'Physics' => 'fa-atom',
            'Chemistry' => 'fa-flask',
            'Biology' => 'fa-dna',
            'Medical Technology' => 'fa-heart-pulse',
            'Nanotechnology' => 'fa-circle-nodes',
            'Materials Science' => 'fa-cubes',
            'Biotechnology' => 'fa-vial',
            '3D Printing' => 'fa-cube',
            'GIS & Remote Sensing' => 'fa-satellite',
            'Climate Science' => 'fa-cloud-sun',
            'Research & Innovation' => 'fa-lightbulb',
        ];
    }

    /**
     * @return list<string>
     */
    public static function articlesPortalSidebarCategories(): array
    {
        return [
            'All Articles',
            'Education',
            'Business',
            'Technology',
            'Agriculture',
            'Real Estate',
            'Construction',
            'Water Management',
            'Environment',
            'Government Schemes',
            'Personal Development',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function articlesCategoryIcons(): array
    {
        return [
            'Education' => 'fa-graduation-cap',
            'Business' => 'fa-briefcase',
            'Technology' => 'fa-microchip',
            'Agriculture' => 'fa-seedling',
            'Real Estate' => 'fa-building',
            'Construction' => 'fa-hard-hat',
            'Water Management' => 'fa-droplet',
            'Environment' => 'fa-leaf',
            'Government Schemes' => 'fa-landmark',
            'Personal Development' => 'fa-user-graduate',
        ];
    }

    /**
     * @return list<string>
     */
    public static function reportsPortalSidebarCategories(): array
    {
        return [
            'All Reports',
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
            'Social Impact Report',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function reportsCategoryIcons(): array
    {
        return [
            'Community Report' => 'fa-people-group',
            'Research Report' => 'fa-magnifying-glass-chart',
            'Survey Report' => 'fa-square-poll-vertical',
            'Infrastructure Report' => 'fa-road',
            'Environment Report' => 'fa-leaf',
            'Water Report' => 'fa-droplet',
            'Agriculture Report' => 'fa-seedling',
            'Education Report' => 'fa-graduation-cap',
            'Health Report' => 'fa-heart-pulse',
            'Market Report' => 'fa-chart-line',
            'Government Scheme Report' => 'fa-landmark',
            'Social Impact Report' => 'fa-hands-holding-circle',
        ];
    }

    /**
     * @return list<string>
     */
    public static function storiesLiteraturePortalSidebarCategories(): array
    {
        return [
            'All Stories & Literature',
            'Stories',
            'Poetry',
            'Biography',
            'Autobiography',
        ];
    }

    /**
     * @return list<string>
     */
    public static function storiesPortalSidebarCategories(): array
    {
        return array_merge(['All Stories'], array_slice(self::storyMainCategories(), 0, 11));
    }

    /**
     * @return list<string>
     */
    public static function poetryPortalSidebarCategories(): array
    {
        return array_merge(['All Poetry'], self::poetryMainCategories());
    }

    /**
     * @return list<string>
     */
    public static function biographyPortalSidebarCategories(): array
    {
        return array_merge(['All Biographies'], self::types()['biography']['categories']);
    }

    /**
     * @return list<string>
     */
    public static function autobiographyPortalSidebarCategories(): array
    {
        return array_merge(['All Autobiographies'], self::types()['autobiography']['categories']);
    }

    /**
     * @return array<string, string>
     */
    public static function storiesCategoryIcons(): array
    {
        return [
            'True Story' => 'fa-book-open',
            'Personal Experience' => 'fa-heart',
            'Fiction' => 'fa-wand-magic-sparkles',
            'Historical' => 'fa-landmark',
            'Educational' => 'fa-graduation-cap',
            'Motivational' => 'fa-bolt',
            "Children's Story" => 'fa-child',
            'Folklore' => 'fa-mountain-sun',
            'Community Story' => 'fa-people-group',
            'Travel Diary' => 'fa-route',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function poetryCategoryIcons(): array
    {
        return [
            'Poetry' => 'fa-feather-pointed',
            'Shayari' => 'fa-quote-left',
            'Ghazal' => 'fa-music',
            'Nazm' => 'fa-pen-nib',
            'Geet (Song)' => 'fa-microphone',
            'Haiku' => 'fa-leaf',
            'Doha' => 'fa-scroll',
            'Free Verse' => 'fa-align-left',
            "Children's Poetry" => 'fa-child',
            'Spiritual Poetry' => 'fa-om',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function biographyCategoryIcons(): array
    {
        return [
            'Freedom Fighters' => 'fa-flag',
            'Scientists' => 'fa-flask',
            'Entrepreneurs' => 'fa-briefcase',
            'Teachers' => 'fa-chalkboard-user',
            'Social Workers' => 'fa-hands-holding-heart',
            'Local Heroes' => 'fa-star',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function autobiographyCategoryIcons(): array
    {
        return [
            'Personal Journey' => 'fa-road',
            'Career Journey' => 'fa-briefcase',
            'Business Journey' => 'fa-chart-line',
            'Educational Journey' => 'fa-graduation-cap',
            "Women's Journey" => 'fa-venus',
            'Senior Citizen Journey' => 'fa-person-cane',
            "Farmer's Journey" => 'fa-seedling',
            'Social Service Journey' => 'fa-hand-holding-heart',
            'Professional Journey' => 'fa-user-tie',
            'Spiritual Journey' => 'fa-om',
        ];
    }

    /**
     * @return list<string>
     */
    public static function lifeLearningPortalSidebarCategories(): array
    {
        return [
            'All Life & Learning',
            "Children's Corner",
            'Student Corner',
            'Youth Corner',
            'Senior Citizens Forum',
            "Women's World",
            'Health & Wellness',
        ];
    }

    /**
     * @return list<string>
     */
    public static function childrensCornerPortalSidebarCategories(): array
    {
        return array_merge(["All Children's Corner"], array_slice(self::childrensCornerShareTypes(), 0, 11));
    }

    /**
     * @return list<string>
     */
    public static function studentCornerPortalSidebarCategories(): array
    {
        return array_merge(['All Student Corner'], array_slice(self::studentCornerMainCategories(), 0, 11));
    }

    /**
     * @return list<string>
     */
    public static function youthCornerPortalSidebarCategories(): array
    {
        return array_merge(['All Youth Corner'], array_slice(self::youthCornerMainCategories(), 0, 11));
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumPortalSidebarCategories(): array
    {
        return array_merge(['All Senior Citizens Forum'], array_slice(self::seniorCitizensForumMainCategories(), 0, 11));
    }

    /**
     * @return list<string>
     */
    public static function womensWorldPortalSidebarCategories(): array
    {
        return array_merge(["All Women's World"], array_slice(self::womensWorldMainCategories(), 0, 11));
    }

    /**
     * @return list<string>
     */
    public static function healthWellnessPortalSidebarCategories(): array
    {
        return array_merge(['All Health & Wellness'], self::types()['health-wellness']['categories']);
    }

    /**
     * @return array<string, string>
     */
    public static function childrensCornerCategoryIcons(): array
    {
        return [
            'Story' => 'fa-book-open',
            'Poem' => 'fa-feather-pointed',
            'Drawing' => 'fa-palette',
            'Artwork' => 'fa-paintbrush',
            'Craft' => 'fa-scissors',
            'School Project' => 'fa-school',
            'Achievement' => 'fa-trophy',
            'Quiz' => 'fa-circle-question',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function studentCornerCategoryIcons(): array
    {
        return [
            'Academic Help' => 'fa-book',
            'Career Guidance' => 'fa-compass',
            'Exam Preparation' => 'fa-file-lines',
            'Skill Development' => 'fa-lightbulb',
            'Student Achievement' => 'fa-trophy',
            'Study Tips' => 'fa-graduation-cap',
            'College Admission' => 'fa-building-columns',
            'Scholarship' => 'fa-award',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function youthCornerCategoryIcons(): array
    {
        return [
            'Career Guidance' => 'fa-briefcase',
            'Skill Development' => 'fa-lightbulb',
            'Motivation' => 'fa-bolt',
            'Education' => 'fa-graduation-cap',
            'Entrepreneurship' => 'fa-rocket',
            'Social Impact' => 'fa-hands-holding-heart',
            'Personal Growth' => 'fa-seedling',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function seniorCitizensForumCategoryIcons(): array
    {
        return [
            'Health & Wellness' => 'fa-heart-pulse',
            'Retirement Life' => 'fa-person-cane',
            'Family & Relationships' => 'fa-people-roof',
            'Hobbies & Interests' => 'fa-palette',
            'Community Support' => 'fa-hands-helping',
            'Financial Planning' => 'fa-piggy-bank',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function womensWorldCategoryIcons(): array
    {
        return [
            'Health & Wellness' => 'fa-heart-pulse',
            'Career & Business' => 'fa-briefcase',
            'Parenting & Family' => 'fa-people-roof',
            'Empowerment' => 'fa-venus',
            'Education' => 'fa-graduation-cap',
            'Safety & Rights' => 'fa-shield-heart',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function healthWellnessCategoryIcons(): array
    {
        return [
            'Health Awareness' => 'fa-heart-pulse',
            'Fitness' => 'fa-dumbbell',
            'Mental Wellness' => 'fa-brain',
            'Nutrition' => 'fa-apple-whole',
            'Preventive Care' => 'fa-shield-virus',
            'Community Health' => 'fa-people-group',
        ];
    }

    /**
     * Deprecated content types kept for editing existing posts.
     *
     * @return array<string, array{label: string, description: string, categories: list<string>}>
     */
    public static function legacyContentTypes(): array
    {
        return [
            'my-area' => [
                'label' => 'My Area (legacy)',
                'description' => 'Deprecated. Use Local Voices for new local posts.',
                'categories' => self::myAreaTopicCategories(),
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, description: string, categories: list<string>, features?: list<string>, monetization?: list<string>, rewards?: list<string>, examples?: list<string>}>
     */
    public static function editableTypes(?\App\Models\CommunityPost $post = null): array
    {
        $types = self::formTypes();

        if ($post?->content_type && array_key_exists($post->content_type, self::legacyContentTypes())) {
            $types[$post->content_type] = self::legacyContentTypes()[$post->content_type];
        }

        return $types;
    }

    /**
     * @return list<string>
     */
    public static function allowedContentTypeKeys(?\App\Models\CommunityPost $post = null): array
    {
        $keys = array_keys(self::formTypes());

        if ($post?->content_type && array_key_exists($post->content_type, self::legacyContentTypes())) {
            $keys[] = $post->content_type;
        }

        return array_values(array_unique($keys));
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
            'hi' => 'Hinglish',
            'hindi' => 'Hindi',
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
            'hi' => 'Hinglish',
            'hindi' => 'Hindi',
        ];
    }

    /**
     * HTML lang attribute for a stored editor_language code.
     */
    public static function editorLanguageHtmlLang(?string $code): string
    {
        return match ($code) {
            'hindi', 'hi' => 'hi',
            'ur' => 'ur',
            'pa' => 'pa',
            'bn' => 'bn',
            'mr' => 'mr',
            'gu' => 'gu',
            'ta' => 'ta',
            'te' => 'te',
            default => 'en',
        };
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
     * Main category choices for Community Issues posts.
     *
     * @return list<string>
     */
    public static function communityIssueMainCategories(): array
    {
        return [
            'Water Issues',
            'Roads & Transport',
            'Electricity',
            'Drainage & Sewage',
            'Waste Management',
            'Pollution',
            'Public Safety',
            'Healthcare',
            'Education Infrastructure',
            'Government Services',
            'Agriculture Issues',
            'Environmental Issues',
            'Public Property Damage',
            'Animal & Wildlife Issues',
            "Women's Safety",
            'Senior Citizen Issues',
            'Public Transport',
            'Internet & Communication',
            'Tourism Infrastructure',
            'Other',
        ];
    }

    /**
     * Issue type choices for Community Issues posts.
     *
     * @return list<string>
     */
    public static function communityIssueTypes(): array
    {
        return [
            'Complaint',
            'Public Concern',
            'Urgent Problem',
            'Safety Hazard',
            'Environmental Concern',
            'Suggestion for Improvement',
            'Request for Action',
            'Community Alert',
        ];
    }

    /**
     * Recommended Community Issues content sections for the rich text editor.
     *
     * @return array<string, string>
     */
    public static function communityIssueContentStructure(): array
    {
        return [
            'What is the Issue?' => 'Describe the problem clearly.',
            'When Did It Start?' => 'When did you first notice it?',
            'Who Is Affected?' => 'Residents, students, businesses, or other groups.',
            'What Is the Impact?' => 'Explain how this affects daily life or safety.',
            'What Action Has Been Taken So Far?' => 'Complaints filed, authorities contacted, or community steps.',
            'Suggested Solution' => 'Practical ideas or requests for resolution.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueSeverityLevels(): array
    {
        return ['Low', 'Medium', 'High', 'Critical', 'Emergency'];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueAffectedPopulationRanges(): array
    {
        return ['1-10 People', '10-100 People', '100-500 People', '500+', 'Entire Community'];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueAffectedGroups(): array
    {
        return [
            'Residents',
            'Students',
            'Women',
            'Children',
            'Senior Citizens',
            'Farmers',
            'Businesses',
            'Tourists',
            'General Public',
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssuePhotoEvidenceExamples(): array
    {
        return ['Road Damage', 'Water Leakage', 'Garbage Dump', 'Broken Street Light'];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueDocumentExamples(): array
    {
        return ['Complaint Letter', 'Government Notice', 'RTI Response', 'Survey Report'];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueRecurringFrequencies(): array
    {
        return ['Daily', 'Weekly', 'Monthly', 'Seasonal', 'Continuous'];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueAuthorities(): array
    {
        return [
            'Municipal Corporation',
            'Panchayat',
            'Water Department',
            'Electricity Department',
            'PWD',
            'Police',
            'District Administration',
            'Health Department',
            'School Authority',
            'Forest Department',
            'Other',
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueSupportRequests(): array
    {
        return [
            'Awareness',
            'Community Feedback',
            'Authority Attention',
            'Volunteers',
            'Funding Support',
            'Technical Guidance',
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueStatusSteps(): array
    {
        return [
            'Reported',
            'Pending Verification',
            'Verified',
            'Forwarded to Authority',
            'Acknowledged',
            'Work Started',
            'Partially Resolved',
            'Resolved',
            'Closed',
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueDefaultPollOptions(): array
    {
        return ['Yes', 'No', 'Needs Further Review'];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueTagExamples(): array
    {
        return ['Water Leakage', 'Prem Nagar', 'Infrastructure', 'Municipality'];
    }

    /**
     * @return array<string, string>
     */
    public static function communityIssuePublishAsOptions(): array
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
    public static function communityIssueVisibilitySettings(): array
    {
        return [
            'public' => 'Public',
            'local_community' => 'Local Community',
            'registered_users' => 'Registered Users',
            'private_link' => 'Private Link',
        ];
    }

    public static function communityIssueDefaultVisibilitySetting(): string
    {
        return 'public';
    }

    /**
     * @return array<string, string>
     */
    public static function communityIssueReactionOptions(): array
    {
        return [
            'I Support' => 'fa-solid fa-thumbs-up',
            'Serious Issue' => 'fa-solid fa-triangle-exclamation',
            'Needs Attention' => 'fa-solid fa-bullhorn',
            'Good Initiative' => 'fa-solid fa-lightbulb',
            'Good Solution' => 'fa-solid fa-check-circle',
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueReactionLabels(): array
    {
        return array_keys(self::communityIssueReactionOptions());
    }

    public static function communityIssueDefaultEscalationThreshold(): int
    {
        return 100;
    }

    /**
     * Geographic heat map presets for the Community Issues hub.
     *
     * @return array<string, array{label: string, categories: list<string>, color: string}>
     */
    public static function communityIssueHeatMapPresets(): array
    {
        return [
            'all' => [
                'label' => 'All Issues',
                'categories' => [],
                'color' => '#dc3545',
            ],
            'water' => [
                'label' => 'Water Issues Map',
                'categories' => ['Water Issues', 'Drainage & Sewage'],
                'color' => '#0d6efd',
            ],
            'roads' => [
                'label' => 'Road Issues Map',
                'categories' => ['Roads & Transport', 'Public Transport'],
                'color' => '#fd7e14',
            ],
            'pollution' => [
                'label' => 'Pollution Map',
                'categories' => ['Pollution', 'Environmental Issues', 'Waste Management'],
                'color' => '#198754',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueResolvedStatuses(): array
    {
        return ['Partially Resolved', 'Resolved', 'Closed'];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueUnderReviewStatuses(): array
    {
        return [
            'Pending Verification',
            'Verified',
            'Forwarded to Authority',
            'Acknowledged',
            'Work Started',
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueAuthorityRespondedStatuses(): array
    {
        return ['Acknowledged', 'Work Started', 'Partially Resolved', 'Resolved', 'Closed'];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueAuthorityEligibleStatuses(): array
    {
        return ['Forwarded to Authority', 'Acknowledged', 'Work Started', 'Partially Resolved', 'Resolved', 'Closed'];
    }

    /**
     * @return array<string, array{icon: string, color: string, description: string, threshold: int}>
     */
    public static function communityIssueChampionBadgeDefinitions(): array
    {
        return [
            'Issue Reporter' => [
                'icon' => 'fa-solid fa-flag',
                'color' => 'primary',
                'description' => 'Published civic issues that help the community stay informed.',
                'threshold' => 1,
            ],
            'Community Volunteer' => [
                'icon' => 'fa-solid fa-hand-holding-heart',
                'color' => 'success',
                'description' => 'Supported and verified neighbours\' community issues.',
                'threshold' => 5,
            ],
            'Problem Solver' => [
                'icon' => 'fa-solid fa-screwdriver-wrench',
                'color' => 'warning',
                'description' => 'Helped drive issues toward resolution.',
                'threshold' => 1,
            ],
            'Water Warrior' => [
                'icon' => 'fa-solid fa-droplet',
                'color' => 'info',
                'description' => 'Champion for water and drainage-related civic issues.',
                'threshold' => 2,
            ],
            'Green Champion' => [
                'icon' => 'fa-solid fa-leaf',
                'color' => 'success',
                'description' => 'Advocate for pollution and environmental community action.',
                'threshold' => 2,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueWaterCategories(): array
    {
        return ['Water Issues', 'Drainage & Sewage'];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueGreenCategories(): array
    {
        return ['Pollution', 'Environmental Issues', 'Waste Management'];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceTypes(): array
    {
        return [
            'Opinion',
            'Suggestion',
            'Community Concern',
            'Community Achievement',
            'Local Success Story',
            'Local Hero',
            'Civic Issue',
            'Public Feedback',
            'Community Initiative',
            'Open Letter',
            'Awareness Message',
            'Question to Community',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceMainCategories(): array
    {
        return [
            'Water Issues',
            'Roads & Transport',
            'Cleanliness & Waste',
            'Environment',
            'Education',
            'Healthcare',
            'Public Safety',
            'Government Services',
            'Agriculture',
            'Business & Markets',
            'Tourism',
            'Culture & Heritage',
            'Sports',
            'Youth Development',
            'Women Empowerment',
            'Senior Citizen Issues',
            'Community Development',
            'Local Events',
            'Infrastructure',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceIssueTypes(): array
    {
        return [
            'Complaint',
            'Suggestion',
            'Appreciation',
            'Discussion',
            'Awareness',
            'Request for Action',
            'Success Story',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceAffectedCommunities(): array
    {
        return [
            'Residents',
            'Students',
            'Farmers',
            'Women',
            'Senior Citizens',
            'Businesses',
            'Tourists',
            'General Public',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceImpactLevels(): array
    {
        return ['Low', 'Medium', 'High', 'Critical'];
    }

    /**
     * @return array<string, string>
     */
    public static function localVoiceContentStructure(): array
    {
        return [
            'Issue / Topic' => 'State the main issue or topic clearly.',
            'Background' => 'Provide context and history.',
            'Current Situation' => 'Describe what is happening now.',
            'Impact on Community' => 'Explain who is affected and how.',
            'Suggested Solution' => 'Share practical ideas or requests.',
            'Call for Action' => 'Tell readers what they can do next.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceAuthorities(): array
    {
        return [
            'Municipality',
            'Panchayat',
            'PWD',
            'Water Department',
            'Electricity Department',
            'School Authority',
            'Police',
            'District Administration',
            'Others',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceCallForActionExamples(): array
    {
        return [
            'Support This Initiative',
            'Join Community Cleanup',
            'Attend Public Meeting',
            'Sign Petition',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceStatusTrackerSteps(): array
    {
        return [
            'Reported',
            'Under Discussion',
            'Forwarded to Authority',
            'Action Taken',
            'Resolved',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceInitiativeExamples(): array
    {
        return [
            'Tree Plantation',
            'Cleanliness Drive',
            'Blood Donation Camp',
            'Water Conservation Project',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceVideoTypes(): array
    {
        return [
            'Traffic Problems',
            'Flooding',
            'Public Meetings',
            'Community Activities',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoicePhotoEvidenceExamples(): array
    {
        return [
            'Road Damage',
            'Water Leakage',
            'Garbage Dump',
            'Community Event',
            'Tree Plantation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceDefaultPollOptions(): array
    {
        return ['Yes', 'No', 'Not Sure'];
    }

    /**
     * @return array<string, string>
     */
    public static function localVoicePublishAsOptions(): array
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
    public static function localVoiceVisibilitySettings(): array
    {
        return [
            'public' => 'Public',
            'registered_users' => 'Registered Users',
            'local_community' => 'Local Community Only',
            'private_link' => 'Private Link',
        ];
    }

    public static function localVoiceDefaultVisibilitySetting(): string
    {
        return 'public';
    }

    /**
     * @return array<string, string>
     */
    public static function localVoiceReactionOptions(): array
    {
        return [
            'I Agree' => 'fa-solid fa-thumbs-up',
            'Good Suggestion' => 'fa-solid fa-lightbulb',
            'Community Hero' => 'fa-solid fa-medal',
            'Support' => 'fa-solid fa-hand-holding-heart',
            'Positive Change' => 'fa-solid fa-seedling',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceReactionLabels(): array
    {
        return array_keys(self::localVoiceReactionOptions());
    }

    /**
     * @return list<string>
     */
    public static function localVoiceTagExamples(): array
    {
        return ['Water', 'Dehradun', 'Community', 'Roads', 'Environment'];
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
     * @return array<string, string>
     */
    public static function businessReactionOptions(): array
    {
        return [
            'Informative' => 'fa-solid fa-circle-info',
            'Excellent' => 'fa-solid fa-star',
            'Inspiring' => 'fa-solid fa-lightbulb',
            'Helpful' => 'fa-solid fa-hand-holding-heart',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessReactionLabels(): array
    {
        return array_keys(self::businessReactionOptions());
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

    /**
     * Primary activity types for My Area civic posts.
     *
     * @return list<string>
     */
    public static function myAreaActivityTypes(): array
    {
        return [
            'Report Issues',
            'Suggest Improvements',
            'Recognize Heroes',
            'Share Local Achievements',
            'Raise Awareness',
            'Track Resolutions',
        ];
    }

    /**
     * @return list<string>
     */
    public static function myAreaTopicCategories(): array
    {
        return self::localVoiceMainCategories();
    }

    /**
     * @return list<string>
     */
    public static function myAreaImpactLevels(): array
    {
        return self::localVoiceImpactLevels();
    }

    /**
     * @return list<string>
     */
    public static function myAreaAffectedCommunities(): array
    {
        return self::localVoiceAffectedCommunities();
    }

    /**
     * @return list<string>
     */
    public static function myAreaAuthorities(): array
    {
        return self::localVoiceAuthorities();
    }

    /**
     * @return list<string>
     */
    public static function myAreaStatusTrackerSteps(): array
    {
        return self::localVoiceStatusTrackerSteps();
    }

    /**
     * @return array<string, string>
     */
    public static function myAreaContentStructure(): array
    {
        return [
            'Issue / Topic' => 'What is happening in your area?',
            'Background' => 'Provide local context.',
            'Current Situation' => 'Describe the present condition.',
            'Impact on Community' => 'Who is affected and how?',
            'Suggested Solution' => 'Practical ideas or requests.',
            'Call for Action' => 'What should neighbours or authorities do?',
        ];
    }

    /**
     * @return list<string>
     */
    public static function myAreaDefaultPollOptions(): array
    {
        return self::localVoiceDefaultPollOptions();
    }

    /**
     * @return array<string, string>
     */
    public static function myAreaPublishAsOptions(): array
    {
        return self::localVoicePublishAsOptions();
    }

    /**
     * @return array<string, string>
     */
    public static function myAreaVisibilitySettings(): array
    {
        return self::localVoiceVisibilitySettings();
    }

    public static function myAreaDefaultVisibilitySetting(): string
    {
        return self::localVoiceDefaultVisibilitySetting();
    }

    /**
     * @return array<string, string>
     */
    public static function myAreaReactionOptions(): array
    {
        return self::localVoiceReactionOptions();
    }

    /**
     * @return list<string>
     */
    public static function myAreaReactionLabels(): array
    {
        return array_keys(self::myAreaReactionOptions());
    }

    public static function labels(): array
    {
        return collect(self::types())->mapWithKeys(fn (array $type, string $key) => [$key => $type['label']])->all();
    }

    /**
     * Accent color per content type (used for filter pills and post-card borders).
     *
     * @return array<string, string>
     */
    public static function pillColors(): array
    {
        return [
            'all' => '#546e7a',
            'articles' => '#1976d2',
            'reports' => '#5c6bc0',
            'my-voice' => '#8e24aa',
            'news' => '#e53935',
            'stories' => '#fb8c00',
            'poetry' => '#ec407a',
            'biography' => '#795548',
            'autobiography' => '#f4511e',
            'childrens-corner' => '#29b6f6',
            'awareness' => '#ffb300',
            'business' => '#3949ab',
            'student-corner' => '#00acc1',
            'career' => '#455a64',
            'health-wellness' => '#43a047',
            'womens-world' => '#d81b60',
            'senior-citizens-forum' => '#7cb342',
            'youth-corner' => '#c0ca33',
            'jobs-employment' => '#1565c0',
            'opinions-views' => '#673ab7',
            'travel-diaries' => '#039be5',
            'culture-heritage' => '#ff9800',
            'astro-consultancy' => '#7e57c2',
            'religion-spirituality' => '#ff7043',
            'agriculture' => '#558b2f',
            'environment' => '#009688',
            'science-technology' => '#3d5afe',
            'local-voices' => '#ff5722',
            'community-issues' => '#c62828',
            'creative-corner' => '#e91e63',
            'competitions' => '#f9a825',
            'discussions' => '#607d8b',
        ];
    }

    public static function pillColorFallback(): string
    {
        return '#78909c';
    }

    public static function pillColorFor(?string $type): string
    {
        return self::pillColors()[$type] ?? self::pillColorFallback();
    }

    public static function categoriesFor(string $type): array
    {
        return self::types()[$type]['categories'] ?? [];
    }

    /**
     * @return list<string>
     */
    public static function agricultureShareTypes(): array
    {
        return [
            'Farming Experience',
            'Crop Advisory',
            'Agriculture Article',
            'Success Story',
            'Problem & Solution',
            'Question & Discussion',
            'Government Scheme',
            'Market Information',
            'Research Findings',
            'Agricultural Innovation',
            'Organic Farming',
            'Livestock Management',
            'Water Management',
            'Equipment Review',
            'Agri-Business Opportunity',
            'Training Program',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureMainCategories(): array
    {
        return [
            'Crop Farming',
            'Horticulture',
            'Organic Farming',
            'Livestock',
            'Dairy Farming',
            'Poultry Farming',
            'Fish Farming',
            'Beekeeping',
            'Agri-Business',
            'Farm Machinery',
            'Irrigation & Water Management',
            'Soil Health',
            'Agricultural Technology',
            'Government Schemes',
            'Farmer Success Stories',
            'Climate & Weather',
            'Agricultural Research',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureGrowingSeasons(): array
    {
        return ['Kharif', 'Rabi', 'Zaid', 'Perennial'];
    }

    /**
     * @return list<string>
     */
    public static function agricultureSoilTypes(): array
    {
        return [
            'Sandy',
            'Clay',
            'Loamy',
            'Black Soil',
            'Red Soil',
            'Alluvial Soil',
            'Laterite Soil',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureFarmSizes(): array
    {
        return [
            'Less than 1 Acre',
            '1-5 Acres',
            '5-10 Acres',
            '10+ Acres',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureFarmingTypes(): array
    {
        return [
            'Organic',
            'Natural Farming',
            'Conventional',
            'Integrated Farming',
            'Hydroponics',
            'Protected Cultivation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureCropRelevantCategories(): array
    {
        return [
            'Crop Farming',
            'Horticulture',
            'Organic Farming',
            'Soil Health',
            'Irrigation & Water Management',
            'Agricultural Research',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function agricultureContentStructure(): array
    {
        return [
            'Background' => 'Set the context for your farming topic or experience.',
            'Problem' => 'Describe the challenge, pest, weather, or market issue.',
            'Method Used' => 'Explain practices, inputs, or techniques applied.',
            'Results' => 'Share outcomes, yields, savings, or improvements.',
            'Challenges' => 'Note difficulties faced during the process.',
            'Recommendations' => 'Offer advice for other farmers or readers.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureIrrigationMethods(): array
    {
        return [
            'Flood Irrigation',
            'Drip Irrigation',
            'Sprinkler',
            'Rainfed',
            'Micro Irrigation',
            'Manual',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureWaterSources(): array
    {
        return [
            'Canal',
            'Borewell',
            'River',
            'Rainwater Harvesting',
            'Pond',
            'Municipal Supply',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureWaterConservationPractices(): array
    {
        return [
            'Rainwater Harvesting',
            'Mulching',
            'Drip Irrigation',
            'Farm Pond',
            'Contour Bunding',
            'Check Dam',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureProblemTypes(): array
    {
        return [
            'Pest Attack',
            'Disease',
            'Low Yield',
            'Water Shortage',
            'Soil Issue',
            'Market Problem',
            'Weather Damage',
            'Equipment Failure',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agriculturePriceTrends(): array
    {
        return ['Increasing', 'Stable', 'Decreasing'];
    }

    /**
     * @return list<string>
     */
    public static function agricultureLivestockTypes(): array
    {
        return [
            'Dairy',
            'Poultry',
            'Goat Farming',
            'Sheep Farming',
            'Fish Farming',
            'Beekeeping',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureGalleryCategories(): array
    {
        return [
            'farm_photos' => 'Farm Photos',
            'crop_growth_stages' => 'Crop Growth Stages',
            'equipment' => 'Equipment',
            'irrigation_systems' => 'Irrigation Systems',
            'harvest' => 'Harvest',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureVideoExamples(): array
    {
        return [
            'Field Demonstration',
            'Farm Tour',
            'Irrigation Setup',
            'Organic Farming Techniques',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureDocumentExamples(): array
    {
        return [
            'Research Reports',
            'Soil Test Reports',
            'Government Notifications',
            'Crop Calendars',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureWeatherImpacts(): array
    {
        return [
            'Drought',
            'Excess Rainfall',
            'Frost',
            'Heat Wave',
            'Storm',
            'Normal Conditions',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureTagExamples(): array
    {
        return [
            'Wheat',
            'Organic Farming',
            'Water Conservation',
            'Drip Irrigation',
            'Agriculture',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureAgriBusinessTypes(): array
    {
        return [
            'Seed Supplier',
            'Fertilizer Dealer',
            'Farm Machinery',
            'Food Processing',
            'Organic Products',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureTargetAudiences(): array
    {
        return [
            'Farmers',
            'Agriculture Students',
            'Researchers',
            'Agri-Entrepreneurs',
            'Consultants',
            'Government Officials',
            'NGOs',
            'General Public',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function agricultureReactionOptions(): array
    {
        return [
            'Helpful' => 'fa-solid fa-thumbs-up',
            'Practical' => 'fa-solid fa-screwdriver-wrench',
            'Water Saving' => 'fa-solid fa-droplet',
            'Innovative' => 'fa-solid fa-lightbulb',
            'Excellent' => 'fa-solid fa-star',
            'Recommended' => 'fa-solid fa-check-circle',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureReactionLabels(): array
    {
        return array_keys(self::agricultureReactionOptions());
    }

    /**
     * @return list<string>
     */
    public static function agricultureDefaultPollOptions(): array
    {
        return ['Drip', 'Sprinkler', 'Flood', 'Rainfed'];
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

        if ($type === 'local-voices') {
            return in_array($category, self::localVoiceMainCategories(), true);
        }

        if ($type === 'community-issues') {
            return in_array($category, self::communityIssueMainCategories(), true);
        }

        if ($type === 'agriculture') {
            return in_array($category, self::agricultureMainCategories(), true);
        }

        if ($type === 'environment') {
            return in_array($category, self::environmentMainCategories(), true);
        }

        if ($type === 'astro-consultancy' && in_array($category, [
            'Astrology', 'Numerology', 'Vastu', 'Palmistry', 'Horoscope', 'Spiritual Guidance',
        ], true)) {
            return true;
        }

        if ($type === 'astro-consultancy') {
            return in_array($category, self::astroConsultancyMainCategories(), true);
        }

        if ($type === 'competitions') {
            return in_array($category, self::competitionsMainCategories(), true);
        }

        return in_array($category, self::categoriesFor($type), true);
    }

    /**
     * @return list<string>
     */
    public static function environmentPostTypes(): array
    {
        return [
            'Awareness Article',
            'Success Story',
            'Community Initiative',
            'Environmental Issue',
            'Problem & Solution',
            'Research Findings',
            'Case Study',
            'Question & Discussion',
            'Government Scheme',
            'Tree Plantation Drive',
            'Water Conservation Activity',
            'Waste Management Initiative',
            'Climate Awareness',
            'Biodiversity Documentation',
            'Environmental Innovation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentMainCategories(): array
    {
        return [
            'Water Conservation',
            'Soil Conservation',
            'Climate Change',
            'Air Pollution',
            'Water Pollution',
            'Waste Management',
            'Plastic-Free Campaign',
            'Tree Plantation',
            'Biodiversity',
            'Wildlife Conservation',
            'Forests',
            'Renewable Energy',
            'Sustainable Agriculture',
            'Environmental Education',
            'Eco Tourism',
            'River Conservation',
            'Wetlands',
            'Urban Environment',
            'Green Technology',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function environmentContentStructure(): array
    {
        return [
            'Background' => 'Set the environmental context and why this topic matters.',
            'Current Situation' => 'Describe the present condition on the ground.',
            'Environmental Impact' => 'Explain effects on water, soil, air, wildlife, or communities.',
            'Actions Taken' => 'Document interventions, initiatives, or responses so far.',
            'Results' => 'Share measurable or observed outcomes.',
            'Future Recommendations' => 'Suggest next steps for the community or authorities.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentIssueTypes(): array
    {
        return [
            'Water Pollution',
            'Air Pollution',
            'Illegal Tree Cutting',
            'Garbage Dumping',
            'Plastic Pollution',
            'River Encroachment',
            'Forest Fire',
            'Illegal Mining',
            'Soil Erosion',
            'Groundwater Depletion',
            'Noise Pollution',
            'Wetland Destruction',
            'Wildlife Threat',
            'Industrial Pollution',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentInitiativeTypes(): array
    {
        return [
            'Tree Plantation',
            'Cleanliness Drive',
            'River Cleaning',
            'Plastic Collection',
            'School Awareness Program',
            'Rainwater Harvesting',
            'Lake Restoration',
            'Biodiversity Survey',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentWaterSources(): array
    {
        return [
            'River',
            'Lake',
            'Canal',
            'Borewell',
            'Rainwater',
            'Spring',
            'Pond',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentConservationMethods(): array
    {
        return [
            'Rainwater Harvesting',
            'Recharge Pit',
            'Farm Pond',
            'Contour Bunding',
            'Check Dam',
            'Percolation Tank',
            'Drip Irrigation',
            'Mulching',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentMapPinTypes(): array
    {
        return [
            'Pond',
            'Lake',
            'River',
            'Forest',
            'Dump Site',
            'Plantation Area',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentSoilConservationMethods(): array
    {
        return [
            'Mulching',
            'Terracing',
            'Contour Farming',
            'Green Cover',
            'Organic Farming',
            'Erosion Control',
            'Agroforestry',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentTreeSurvivalStatuses(): array
    {
        return [
            'Excellent',
            'Good',
            'Moderate',
            'Needs Attention',
            'Poor',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentWasteTypes(): array
    {
        return [
            'Plastic Waste',
            'Organic Waste',
            'E-Waste',
            'Construction Waste',
            'Biomedical Waste',
            'Hazardous Waste',
            'Recycling',
            'Composting',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentBiodiversityTypes(): array
    {
        return [
            'Birds',
            'Animals',
            'Butterflies',
            'Medicinal Plants',
            'Native Trees',
            'Rare Species',
            'Wetland Species',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentClimateImpacts(): array
    {
        return [
            'Heat Wave',
            'Flood',
            'Drought',
            'Heavy Rainfall',
            'Landslide',
            'Forest Fire',
            'Cyclone',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function environmentGalleryCategories(): array
    {
        return [
            'before_after' => 'Before & After Photos',
            'plantation' => 'Plantation Photos',
            'river' => 'River Photos',
            'wildlife' => 'Wildlife',
            'pollution_evidence' => 'Pollution Evidence',
            'community_activities' => 'Community Activities',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentVideoExamples(): array
    {
        return [
            'Tree Plantation',
            'River Cleanup',
            'Awareness Campaign',
            'Expert Interview',
            'Environmental Documentary',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentDocumentExamples(): array
    {
        return [
            'Research Papers',
            'Water Reports',
            'Environmental Surveys',
            'Government Notifications',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentTagExamples(): array
    {
        return [
            'Environment',
            'Trees',
            'Water',
            'Climate',
            'River',
            'Plastic',
            'Soil',
            'Conservation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentParticipationRequests(): array
    {
        return [
            'Volunteers Required',
            'Join Campaign',
            'Donate Plants',
            'Donate Equipment',
            'Become Volunteer',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentCommunityActions(): array
    {
        return [
            'Join Campaign',
            'Volunteer',
            'Donate',
            'Support Initiative',
            'Share',
            'Follow Campaign',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentImpactTrackerMetrics(): array
    {
        return [
            'People Reached',
            'Trees Planted',
            'Volunteers Joined',
            'Water Conserved',
            'Waste Collected',
            'Campaign Duration',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentGreenLeaderBadges(): array
    {
        return [
            'Water Warrior',
            'Green Champion',
            'Tree Guardian',
            'Eco Volunteer',
            'River Protector',
            'Soil Saver',
            'Climate Advocate',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentGreenMapCategories(): array
    {
        return [
            'Rainwater harvesting sites',
            'Tree plantations',
            'Clean-up drives',
            'Community composting units',
            'Biodiversity hotspots',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentEventPostTypes(): array
    {
        return [
            'Community Initiative',
            'Tree Plantation Drive',
            'Water Conservation Activity',
            'Waste Management Initiative',
            'Climate Awareness',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function environmentReactionOptions(): array
    {
        return [
            'Eco Friendly' => 'fa-solid fa-leaf',
            'Water Saver' => 'fa-solid fa-droplet',
            'Green Initiative' => 'fa-solid fa-seedling',
            'Sustainable' => 'fa-solid fa-recycle',
            'Inspiring' => 'fa-solid fa-lightbulb',
            'Positive Impact' => 'fa-solid fa-heart',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentReactionLabels(): array
    {
        return array_keys(self::environmentReactionOptions());
    }

    /**
     * @return list<string>
     */
    public static function environmentDefaultPollOptions(): array
    {
        return ['Yes', 'No', 'Maybe'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyPostTypes(): array
    {
        return [
            'Research Article',
            'Technical Article',
            'Innovation',
            'Project Showcase',
            'Experiment',
            'Science News',
            'Technology News',
            'Question & Discussion',
            'Tutorial',
            'Case Study',
            'Product Review',
            'Research Summary',
            'Scientific Discovery',
            'Engineering Solution',
            'Software Development',
            'Hardware Project',
            'Agricultural Technology',
            'Environmental Technology',
            'Patent / Innovation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyMainCategories(): array
    {
        return [
            'Agricultural Technology',
            'Artificial Intelligence',
            'Electronics',
            'Electrical Engineering',
            'Mechanical Engineering',
            'Civil Engineering',
            'Environmental Science',
            'Water Technology',
            'Renewable Energy',
            'Robotics',
            'Automation',
            'Software Development',
            'Mobile Applications',
            'Internet of Things (IoT)',
            'Cyber Security',
            'Space Science',
            'Physics',
            'Chemistry',
            'Biology',
            'Medical Technology',
            'Nanotechnology',
            'Materials Science',
            'Biotechnology',
            '3D Printing',
            'GIS & Remote Sensing',
            'Climate Science',
            'Research & Innovation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyTargetAudiences(): array
    {
        return [
            'Students',
            'Teachers',
            'Researchers',
            'Scientists',
            'Engineers',
            'Developers',
            'Startups',
            'Farmers',
            'Businesses',
            'Government Departments',
            'General Public',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyLevels(): array
    {
        return ['Beginner', 'Intermediate', 'Advanced', 'Research Level'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyScientificFields(): array
    {
        return [
            'Engineering',
            'Science',
            'Agriculture',
            'Environment',
            'Medicine',
            'Education',
            'Manufacturing',
            'Energy',
            'Transportation',
            'Construction',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyProjectCategories(): array
    {
        return ['Electronics', 'Software', 'Robotics', 'AI', 'Agriculture', 'Renewable Energy', 'Automation'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyResearchPostTypes(): array
    {
        return ['Research Article', 'Research Summary', 'Scientific Discovery'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyInnovationPostTypes(): array
    {
        return ['Innovation', 'Patent / Innovation'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyTechnologiesUsed(): array
    {
        return [
            'AI',
            'Machine Learning',
            'IoT',
            'Blockchain',
            'Cloud Computing',
            'Big Data',
            'GIS',
            'Remote Sensing',
            'Embedded Systems',
            'Microcontrollers',
            'PLC',
            'SCADA',
            'Automation',
            'Solar',
            'Wind Energy',
            'Drones',
            'Sensors',
            'GPS',
            'Arduino',
            'Raspberry Pi',
            'PIC Microcontroller',
            'ESP32',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyProgrammingLanguages(): array
    {
        return ['Python', 'C', 'C++', 'Java', 'PHP', 'Laravel', 'JavaScript', 'MATLAB', 'R', 'SQL'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyPatentStatuses(): array
    {
        return ['Yes', 'No', 'Under Process'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyWaterSoilTopics(): array
    {
        return [
            'Smart Irrigation',
            'Water Quality Monitoring',
            'Rainwater Harvesting',
            'Groundwater Recharge',
            'Soil Moisture Monitoring',
            'Precision Agriculture',
            'Water Sensors',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function scienceTechnologyGalleryCategories(): array
    {
        return [
            'prototype' => 'Prototype',
            'circuit' => 'Circuit',
            'graphs' => 'Graphs',
            'models' => 'Models',
            'laboratory' => 'Laboratory',
            'testing' => 'Testing',
            'environmental_monitoring' => 'Environmental Monitoring',
            'smart_farming' => 'Smart Farming',
            'wastewater_treatment' => 'Wastewater Treatment',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyRenewableEnergyTypes(): array
    {
        return ['Solar', 'Wind', 'Biogas', 'Hydrogen', 'Micro Hydro', 'Energy Storage', 'Electric Vehicles'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyVideoExamples(): array
    {
        return [
            'Project Demonstration',
            'Tutorial',
            'Experiment',
            'Product Demo',
            'Research Presentation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyFundingTypes(): array
    {
        return ['Government Funded', 'Self Funded', 'Industry Sponsored', 'Academic Research'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyApplicationAreas(): array
    {
        return [
            'Agriculture',
            'Healthcare',
            'Education',
            'Industry',
            'Construction',
            'Environment',
            'Water Management',
            'Transportation',
            'Defence',
            'Consumer Electronics',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyCollaborationRequests(): array
    {
        return [
            'Looking for Research Partner',
            'Looking for Startup Partner',
            'Looking for Investor',
            'Looking for Mentor',
            'Looking for Students',
            'Looking for Developers',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyOpenInnovationOptions(): array
    {
        return [
            'Looking for collaborators',
            'Looking for funding',
            'Licensing available',
            'Manufacturing partner required',
            'Research partner required',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyInnovationChallengeThemes(): array
    {
        return [
            'Smart Water Management',
            'Soil Conservation Technologies',
            'Affordable Healthcare Devices',
            'Sustainable Agriculture',
            'Renewable Energy',
            'Waste Management',
            'Rural Innovation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyCommentSettings(): array
    {
        return ['Questions', 'Technical Discussions', 'Suggestions', 'Collaboration Requests'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyReferenceTypes(): array
    {
        return ['Research Papers', 'DOI', 'Books', 'Government Reports', 'Standards', 'Web References'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyLicenseOptions(): array
    {
        return ['Public Domain', 'Creative Commons', 'Open Source', 'Proprietary', 'Patent Pending'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyPatentIprStatuses(): array
    {
        return ['Patent', 'Copyright', 'Trademark', 'Pending', 'Granted', 'Rejected'];
    }

    /**
     * @return array<string, string>
     */
    public static function scienceTechnologyReactionOptions(): array
    {
        return [
            'Innovative' => 'fa-solid fa-lightbulb',
            'Breakthrough' => 'fa-solid fa-rocket',
            'Scientific' => 'fa-solid fa-flask',
            'Practical' => 'fa-solid fa-screwdriver-wrench',
            'Excellent' => 'fa-solid fa-star',
            'Recommended' => 'fa-solid fa-thumbs-up',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyTagExamples(): array
    {
        return ['AI', 'Electronics', 'IoT', 'Automation', 'Solar', 'Agriculture', 'Water', 'Innovation'];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyDefaultPollOptions(): array
    {
        return ['Yes', 'No', 'Depends on Cost'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyPostTypes(): array
    {
        return [
            'Educational Article',
            'Astrology Guidance',
            'Daily/Weekly/Monthly Horoscope',
            'Question & Answer',
            'Spiritual Guidance',
            'Vastu Advice',
            'Numerology Guidance',
            'Palmistry Knowledge',
            'Tarot Insights',
            'Festival & Muhurat Information',
            'Meditation & Spiritual Wellness',
            'Success Story',
            'Case Study',
            'Discussion',
            'Awareness Post',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyMainCategories(): array
    {
        return [
            'Vedic Astrology',
            'KP Astrology',
            'Western Astrology',
            'Numerology',
            'Vastu Shastra',
            'Palmistry',
            'Tarot Reading',
            'Face Reading',
            'Gemstone Guidance',
            'Horoscope',
            'Muhurat',
            'Feng Shui',
            'Spiritual Healing',
            'Meditation',
            'Yoga Philosophy',
            'Mantras',
            'Life Guidance',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyTargetAudiences(): array
    {
        return [
            'Students',
            'Professionals',
            'Business Owners',
            'Couples',
            'Parents',
            'Senior Citizens',
            'General Public',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyConsultationTopics(): array
    {
        return [
            'Career',
            'Education',
            'Business',
            'Marriage',
            'Relationship',
            'Family',
            'Finance',
            'Health',
            'Property',
            'Travel',
            'Children',
            'Spiritual Growth',
            'Personal Development',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function astroConsultancyContentStructure(): array
    {
        return [
            'Introduction' => 'Introduce the topic and what readers will learn.',
            'Traditional Background' => 'Share cultural or scriptural context where relevant.',
            'Astrological Concept' => 'Explain the principle, planet, sign, or tradition involved.',
            'Interpretation' => 'Offer perspective as belief, tradition, or professional opinion — not guaranteed outcomes.',
            'Suggested Practices' => 'Recommend rituals, remedies, or reflections readers may consider.',
            'Conclusion' => 'Summarize key takeaways and encourage thoughtful judgment.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyContentLanguages(): array
    {
        return ['English', 'Hindi', 'Sanskrit', 'Regional Language'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyImageExamples(): array
    {
        return [
            'Zodiac charts',
            'Horoscope diagrams',
            'Vastu layouts',
            'Yantras',
            'Educational illustrations',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyVideoExamples(): array
    {
        return [
            'Educational Lecture',
            'Daily Horoscope',
            'Meditation Session',
            'Vastu Tips',
            'Festival Significance',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyDocumentTypes(): array
    {
        return ['PDF', 'Research Papers', 'Books', 'Presentation', 'Charts'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyZodiacSigns(): array
    {
        return [
            'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo',
            'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyHoroscopePeriods(): array
    {
        return ['Daily', 'Weekly', 'Monthly', 'Yearly'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyHoroscopePostTypes(): array
    {
        return ['Daily/Weekly/Monthly Horoscope'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyHoroscopeCategories(): array
    {
        return ['Horoscope'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyVastuPostTypes(): array
    {
        return ['Vastu Advice'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyVastuCategories(): array
    {
        return ['Vastu Shastra', 'Feng Shui'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyVastuPropertyTypes(): array
    {
        return [
            'Home',
            'Office',
            'Factory',
            'Shop',
            'Farm House',
            'Apartment',
            'Temple',
            'Commercial Building',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyVastuAreas(): array
    {
        return [
            'Entrance',
            'Kitchen',
            'Bedroom',
            'Living Room',
            'Toilet',
            'Garden',
            'Parking',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyNumerologyPostTypes(): array
    {
        return ['Numerology Guidance'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyNumerologyCategories(): array
    {
        return ['Numerology'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyFestivalPostTypes(): array
    {
        return ['Festival & Muhurat Information'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyRelatedServiceActions(): array
    {
        return [
            'Book Consultation',
            'Ask Expert',
            'View Consultant Profile',
            'Online Appointment',
            'WhatsApp Consultation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyKnowledgeLibraryTopics(): array
    {
        return [
            'Beginner Guides',
            'Birth Chart Basics',
            'Planetary Concepts',
            'Vastu Learning',
            'Numerology Learning',
            'Spiritual Practices',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyPrivateQueryOptions(): array
    {
        return [
            'Request Consultation',
            'Book Appointment',
            'Send Private Query',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyCommentSettings(): array
    {
        return ['Questions', 'Discussion', 'Experiences', 'Suggestions'];
    }

    /**
     * @return array<string, string>
     */
    public static function astroConsultancyReactionOptions(): array
    {
        return [
            'Informative' => 'fa-solid fa-circle-info',
            'Insightful' => 'fa-solid fa-lightbulb',
            'Educational' => 'fa-solid fa-book-open',
            'Helpful' => 'fa-solid fa-hand-holding-heart',
            'Thought Provoking' => 'fa-solid fa-brain',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyReactionLabels(): array
    {
        return array_keys(self::astroConsultancyReactionOptions());
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyTagExamples(): array
    {
        return ['Astrology', 'Career', 'Vastu', 'Numerology', 'Meditation', 'Spirituality'];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyDefaultPollOptions(): array
    {
        return ['Daily', 'Weekly', 'Occasionally', 'Never'];
    }

    /**
     * @return array<string, string>
     */
    public static function astroConsultancyDeclarationStatements(): array
    {
        return [
            'astro_consultancy_declaration_beliefs' => 'The information shared represents traditional beliefs, educational content, or my professional opinion.',
            'astro_consultancy_declaration_no_false_claims' => 'I will not make false, misleading, or guaranteed claims.',
            'astro_consultancy_declaration_no_fear' => 'I will not promote fear or exploit users through superstitious practices.',
            'astro_consultancy_declaration_guidelines' => 'The content follows SoilnWater Community Guidelines.',
        ];
    }

    public static function astroConsultancyDisclaimerText(): string
    {
        return 'The information provided in this section is intended for educational, cultural, and personal guidance purposes only. '
            .'Astrological, numerological, Vastu, tarot, or other spiritual guidance should not be considered a substitute for '
            .'professional medical, legal, financial, or psychological advice. Users are encouraged to exercise their own judgment '
            .'before making important life decisions.';
    }

    public static function religionSpiritualityObjective(): string
    {
        return 'To inspire peace, understanding, compassion, and respect for all faiths while preserving cultural and spiritual heritage.';
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityPostTypes(): array
    {
        return [
            'Religious Article',
            'Spiritual Article',
            'Sacred Scripture Explanation',
            'Festival Information',
            'Temple / Mosque / Church / Gurudwara / Monastery Information',
            'Pilgrimage Guide',
            'Meditation Practice',
            'Prayer & Devotional Content',
            'Inspirational Story',
            'Moral Story',
            'Life Lessons',
            'Question & Discussion',
            'Historical Article',
            'Community Service Activity',
            'Book Review',
            'Cultural Tradition',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityMainCategories(): array
    {
        return [
            'Spirituality',
            'Religious Teachings',
            'Sacred Scriptures',
            'Meditation & Mindfulness',
            'Yoga Philosophy',
            'Moral Values',
            'Pilgrimage',
            'Festivals',
            'Religious History',
            'Places of Worship',
            'Community Service',
            'Inspirational Stories',
            'Interfaith Understanding',
            'Philosophy',
            'Devotional Music',
            'Traditional Practices',
            'Religious Architecture',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityTraditions(): array
    {
        return [
            'Hinduism',
            'Islam',
            'Christianity',
            'Sikhism',
            'Buddhism',
            'Jainism',
            'Judaism',
            'Baháʼí',
            'Zoroastrianism',
            'Indigenous Traditions',
            'Interfaith',
            'Other',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityTargetAudiences(): array
    {
        return [
            'General Public',
            'Students',
            'Youth',
            'Senior Citizens',
            'Researchers',
            'Pilgrims',
            'Families',
            'Teachers',
            'Spiritual Seekers',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function religionSpiritualityContentStructure(): array
    {
        return [
            'Introduction' => 'Introduce the topic and its spiritual or cultural significance.',
            'Historical Background' => 'Share relevant history, origins, or context.',
            'Teachings' => 'Explain core teachings, practices, or beliefs respectfully.',
            'Practical Relevance' => 'Describe how readers can apply or appreciate this today.',
            'Conclusion' => 'Summarize key takeaways with a message of unity and respect.',
            'References' => 'Cite scriptures, scholars, or reliable sources where applicable.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityMoralValues(): array
    {
        return [
            'Kindness',
            'Truthfulness',
            'Compassion',
            'Service',
            'Forgiveness',
            'Respect',
            'Unity',
            'Honesty',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityPlaceOfWorshipTypes(): array
    {
        return [
            'Temple',
            'Mosque',
            'Church',
            'Gurudwara',
            'Monastery',
            'Jain Temple',
            'Ashram',
            'Meditation Centre',
            'Other',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityMeditationTopics(): array
    {
        return [
            'Meditation',
            'Breathing Exercises',
            'Mindfulness',
            'Prayer',
            'Yoga',
            'Silence',
            'Stress Relief',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityCommunityServiceActivities(): array
    {
        return [
            'Food Distribution',
            'Blood Donation',
            'Tree Plantation',
            'Free Education',
            'Health Camp',
            'Cleanliness Drive',
            'Water Conservation',
            'Disaster Relief',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityRelatedServiceActions(): array
    {
        return [
            'Blood Donation',
            'Tree Plantation',
            'Water Conservation',
            'Food Distribution',
            'Education Camps',
            'Volunteer Programs',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityDigitalPilgrimageSiteTypes(): array
    {
        return [
            'Temple',
            'Mosque',
            'Church',
            'Gurudwara',
            'Monastery',
            'Ashram',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityFestivalCalendarEventTypes(): array
    {
        return [
            'Religious Festival',
            'Public Holiday',
            'Community Celebration',
            'Spiritual Event',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityServiceDirectoryOpportunities(): array
    {
        return [
            'Blood Donation Camp',
            'Food Distribution',
            'Tree Plantation',
            'Water Conservation Drive',
            'Disaster Relief',
            'Education Program',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityWisdomLibraryThemes(): array
    {
        return [
            'Compassion',
            'Honesty',
            'Forgiveness',
            'Service',
            'Respect',
            'Environmental Responsibility',
            'Family Values',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function religionSpiritualityUniqueFeatureLabels(): array
    {
        return [
            'digital_pilgrimage_guide' => 'Digital Pilgrimage Guide',
            'festival_calendar' => 'Festival Calendar',
            'community_service_directory' => 'Community Service Directory',
            'wisdom_library' => 'Wisdom Library',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityImageExamples(): array
    {
        return [
            'Festivals',
            'Architecture',
            'Community Service',
            'Pilgrimage',
            'Cultural Events',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityVideoExamples(): array
    {
        return [
            'Religious Lecture',
            'Meditation Session',
            'Festival Celebration',
            'Historical Documentary',
            'Cultural Program',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityAudioExamples(): array
    {
        return [
            'Bhajans',
            'Kirtan',
            'Qawwali',
            'Chants',
            'Meditation Music',
            'Inspirational Talks',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityDocumentTypes(): array
    {
        return [
            'PDF',
            'Books',
            'Research Papers',
            'Historical Documents',
            'Presentations',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityCommentSettings(): array
    {
        return [
            'Questions',
            'Experiences',
            'Respectful Discussion',
            'Suggestions',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function religionSpiritualityReactionOptions(): array
    {
        return [
            'Inspiring' => 'fa-solid fa-star',
            'Peaceful' => 'fa-solid fa-dove',
            'Informative' => 'fa-solid fa-circle-info',
            'Spiritual' => 'fa-solid fa-hands-praying',
            'Promotes Harmony' => 'fa-solid fa-handshake',
            'Meaningful' => 'fa-solid fa-heart',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityReactionLabels(): array
    {
        return array_keys(self::religionSpiritualityReactionOptions());
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityTagExamples(): array
    {
        return ['Meditation', 'Peace', 'Festival', 'Temple', 'Service', 'Compassion'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityDefaultPollOptions(): array
    {
        return ['Meditation', 'Festivals', 'Sacred Texts', 'Pilgrimages', 'Moral Values'];
    }

    /**
     * @return array<string, string>
     */
    public static function religionSpiritualityDeclarationStatements(): array
    {
        return [
            'religion_spirituality_declaration_respectful' => 'The content is shared respectfully and in good faith.',
            'religion_spirituality_declaration_accurate' => 'Any quotations or references are accurate to the best of my knowledge.',
            'religion_spirituality_declaration_no_hatred' => 'The content does not promote hatred, discrimination, violence, or disrespect toward any religion, belief, or community.',
            'religion_spirituality_declaration_educational' => 'The information is intended for educational, cultural, or spiritual purposes.',
            'religion_spirituality_declaration_guidelines' => 'The content follows SoilnWater Community Guidelines.',
        ];
    }

    public static function religionSpiritualityGuidelinesText(): string
    {
        return 'Religion & Spirituality is intended to promote knowledge, cultural understanding, moral values, and respectful dialogue. '
            .'The following content is prohibited: hate speech or discriminatory remarks; personal attacks on individuals or communities; '
            .'calls for violence; forced conversion or coercive content; politically inflammatory religious content; deliberately misleading '
            .'or fabricated religious claims; content that insults or mocks any faith, scripture, or place of worship. '
            .'Posts violating these rules may be removed and user accounts may be suspended.';
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityFestivalPostTypes(): array
    {
        return ['Festival Information'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityPilgrimagePostTypes(): array
    {
        return ['Pilgrimage Guide'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityPlaceOfWorshipPostTypes(): array
    {
        return ['Temple / Mosque / Church / Gurudwara / Monastery Information'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityMeditationPostTypes(): array
    {
        return ['Meditation Practice', 'Prayer & Devotional Content'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityCommunityServicePostTypes(): array
    {
        return ['Community Service Activity'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityScripturePostTypes(): array
    {
        return ['Sacred Scripture Explanation'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityScriptureCategories(): array
    {
        return ['Sacred Scriptures', 'Religious Teachings'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityFestivalCategories(): array
    {
        return ['Festivals'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityPilgrimageCategories(): array
    {
        return ['Pilgrimage'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityPlaceOfWorshipCategories(): array
    {
        return ['Places of Worship', 'Religious Architecture'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityMeditationCategories(): array
    {
        return ['Meditation & Mindfulness', 'Yoga Philosophy', 'Devotional Music'];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityCommunityServiceCategories(): array
    {
        return ['Community Service'];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerPostTypes(): array
    {
        return [
            'Artwork',
            'Photography',
            'Digital Art',
            'Painting',
            'Sketch',
            'Handicraft',
            'Sculpture',
            'Craft Work',
            'DIY Project',
            'Music Composition',
            'Song Performance',
            'Dance Performance',
            'Short Film',
            'Animation',
            'Graphic Design',
            'Logo Design',
            'Fashion Design',
            'Architecture Design',
            'Interior Design',
            'Creative Writing',
            'Innovation',
            'Mixed Media',
            'Creative Tutorial',
            'Question & Discussion',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerMainCategories(): array
    {
        return [
            'Visual Arts',
            'Photography',
            'Painting',
            'Sketching',
            'Digital Art',
            'Graphic Design',
            'Animation',
            'Film Making',
            'Music',
            'Dance',
            'Craft & DIY',
            'Architecture',
            'Interior Design',
            'Fashion Design',
            'Jewellery Design',
            'Calligraphy',
            'Woodwork',
            'Pottery',
            'Paper Craft',
            'Creative Technology',
            'Innovation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerTargetAudiences(): array
    {
        return [
            'General Public',
            'Students',
            'Artists',
            'Photographers',
            'Designers',
            'Businesses',
            'Schools',
            'Architects',
            'Content Creators',
            'Art Collectors',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function creativeCornerContentStructure(): array
    {
        return [
            'Concept' => 'Describe the core idea behind your creative work.',
            'Inspiration' => 'What inspired you to create this?',
            'Materials Used' => 'List materials, tools, or resources used.',
            'Creative Process' => 'Walk through how you created this work.',
            'Challenges' => 'Share obstacles you faced and how you overcame them.',
            'Final Outcome' => 'Describe the finished result.',
            'Future Improvements' => 'What would you do differently next time?',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerCreationTypes(): array
    {
        return [
            'Original Work',
            'Inspired Work',
            'Educational Project',
            'Commissioned Work',
            'Competition Entry',
            'Collaborative Project',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerMediums(): array
    {
        return [
            'Watercolor',
            'Oil',
            'Acrylic',
            'Charcoal',
            'Digital Tablet',
            'Photography',
            'Wood',
            'Clay',
            'Metal',
            'Fabric',
            'Recycled Material',
            '3D Printing',
            'Mixed Media',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerSoftwareTools(): array
    {
        return [
            'Photoshop',
            'Illustrator',
            'Canva',
            'Blender',
            'AutoCAD',
            'SketchUp',
            'Premiere Pro',
            'DaVinci Resolve',
            'Lightroom',
            'Procreate',
            'Figma',
            'Laravel',
            'Arduino',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerMaterials(): array
    {
        return [
            'Canvas',
            'Paper',
            'Wood',
            'Metal',
            'Plastic',
            'Clay',
            'Fabric',
            'Bamboo',
            'Glass',
            'Stone',
            'Natural Materials',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerGalleryExamples(): array
    {
        return [
            'Work in Progress',
            'Final Work',
            'Different Angles',
            'Behind the Scenes',
            'Making Process',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerVideoExamples(): array
    {
        return [
            'Creative Process',
            'Time-lapse',
            'Performance',
            'Tutorial',
            'Behind the Scenes',
            'Workshop',
            'Presentation',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerAudioExamples(): array
    {
        return [
            'Songs',
            'Instrumental Music',
            'Voice Performance',
            'Background Music',
            'Sound Design',
            'Recycled Materials',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerDocumentTypes(): array
    {
        return [
            'PDF',
            'PPT',
            'DOC',
            'Design Files',
            'CAD Drawings',
            'Project Report',
            'Creative Portfolio',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerDifficultyLevels(): array
    {
        return [
            'Beginner',
            'Intermediate',
            'Advanced',
            'Professional',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerThemes(): array
    {
        return [
            'Nature',
            'Water',
            'Environment',
            'Culture',
            'Wildlife',
            'Technology',
            'Architecture',
            'Agriculture',
            'Heritage',
            'Travel',
            'People',
            'Spirituality',
            'Innovation',
            'Sustainability',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerCompetitionCategories(): array
    {
        return [
            'Photography',
            'Painting',
            'Music',
            'Dance',
            'Film',
            'Innovation',
            'Craft',
            'Digital Art',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerCommissionOptions(): array
    {
        return [
            'Available for Custom Orders',
            'Available for Freelance Projects',
            'Available for Workshops',
            'Available for Collaboration',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerCopyrightOptions(): array
    {
        return [
            'Original Work',
            'Creative Commons',
            'Commercial License',
            'Personal Use Only',
            'All Rights Reserved',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerCreativeLicenses(): array
    {
        return [
            'Free to Share',
            'Commercial Use Allowed',
            'No Derivatives',
            'Educational Use Only',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerCollaborationRoles(): array
    {
        return [
            'Photographer',
            'Designer',
            'Video Editor',
            'Musician',
            'Actor',
            'Animator',
            'Artist',
            'Writer',
            'Architect',
            'Craft Expert',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerCommentSettings(): array
    {
        return [
            'Comments',
            'Suggestions',
            'Appreciation',
            'Collaboration Requests',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function creativeCornerReactionOptions(): array
    {
        return [
            'Beautiful' => 'fa-solid fa-palette',
            'Excellent' => 'fa-solid fa-star',
            'Inspiring' => 'fa-solid fa-lightbulb',
            'Creative' => 'fa-solid fa-wand-magic-sparkles',
            'Loved It' => 'fa-solid fa-heart',
            'Outstanding' => 'fa-solid fa-trophy',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerReactionLabels(): array
    {
        return array_keys(self::creativeCornerReactionOptions());
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerTagExamples(): array
    {
        return ['Photography', 'Nature', 'Painting', 'Art', 'DIY', 'Innovation', 'Water', 'Creative'];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerDefaultPollOptions(): array
    {
        return ['Version A', 'Version B', 'Version C'];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerAiUsageOptions(): array
    {
        return ['No', 'Partially Assisted', 'Fully AI Generated'];
    }

    /**
     * @return array<string, string>
     */
    public static function creativeCornerDeclarationStatements(): array
    {
        return [
            'creative_corner_declaration_original' => 'This creative work is my original creation or I have the legal right to publish it.',
            'creative_corner_declaration_no_infringement' => 'The uploaded images, videos, music, and documents do not infringe any copyright, trademark, or intellectual property rights.',
            'creative_corner_declaration_ai_disclosed' => 'If AI-assisted tools were used, I have disclosed this where applicable.',
            'creative_corner_declaration_guidelines' => 'I understand that SoilnWater may remove content that violates copyright, community guidelines, or applicable laws.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsCompetitionTypes(): array
    {
        return [
            'Photography Competition',
            'Story Writing Competition',
            'Poetry Competition',
            'Essay Competition',
            'Drawing Competition',
            'Painting Competition',
            'Innovation Challenge',
            'Science Project Competition',
            'Agriculture Innovation Contest',
            'Business Idea Competition',
            'Startup Pitch Competition',
            'Environment Challenge',
            'Water Conservation Challenge',
            'Soil Conservation Challenge',
            'Tree Plantation Challenge',
            'Quiz Competition',
            'Coding Challenge',
            'Robotics Competition',
            'Dance Competition',
            'Singing Competition',
            'Cooking Competition',
            'Handicraft Competition',
            'Video Making Competition',
            'Reel Competition',
            'Short Film Competition',
            'Logo Design Competition',
            'Poster Design Competition',
            'Meme Competition',
            'Public Speaking Competition',
            'Debate Competition',
            'Community Service Challenge',
            'Fitness Challenge',
            'Open Competition',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsMainCategories(): array
    {
        return [
            'Education',
            'Environment',
            'Agriculture',
            'Technology',
            'Innovation',
            'Photography',
            'Writing',
            'Creative Arts',
            'Business',
            'Health',
            'Sports',
            'Community Service',
            'Children',
            'Students',
            'Women',
            'Youth',
            'Senior Citizens',
            'Open Category',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsEligibilityGroups(): array
    {
        return [
            'Children',
            'Students',
            'Women',
            'Youth',
            'Senior Citizens',
            'Farmers',
            'Teachers',
            'Professionals',
            'Businesses',
            'NGOs',
            'Open to Everyone',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function competitionsContentStructure(): array
    {
        return [
            'Objective' => 'What is the purpose of this competition?',
            'Theme' => 'Central theme or focus area participants should follow.',
            'Who Can Participate' => 'Eligibility, age groups, and any restrictions.',
            'Submission Requirements' => 'Format, file types, word limits, and delivery instructions.',
            'Judging Criteria' => 'How entries will be evaluated.',
            'Prizes' => 'Awards, recognition, and benefits for winners.',
            'Important Dates' => 'Registration, submission, and result timelines.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsThemes(): array
    {
        return [
            'Save Water',
            'Save Soil',
            'Green Earth',
            'Digital India',
            'Healthy Living',
            'Innovation',
            'Clean City',
            'Women Empowerment',
            'Smart Agriculture',
            'Technology for Rural India',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsSubmissionTypes(): array
    {
        return [
            'Article',
            'Story',
            'Poetry',
            'Photo',
            'Image',
            'Video',
            'Audio',
            'Presentation',
            'PDF',
            'Project',
            'Prototype',
            'Software',
            'Research Paper',
            'Drawing',
            'Painting',
            'ZIP File',
            'External Link',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsLevels(): array
    {
        return [
            'School',
            'College',
            'Village',
            'District',
            'State',
            'National',
            'International',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsJudgingCriteriaOptions(): array
    {
        return [
            'Creativity',
            'Originality',
            'Innovation',
            'Presentation',
            'Practical Utility',
            'Social Impact',
            'Technical Quality',
            'Environmental Impact',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsVotingSystems(): array
    {
        return [
            'Judges Only',
            'Public Voting',
            'Judges + Public',
            'Expert Panel',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsPublicVotingMethods(): array
    {
        return [
            'Like',
            'Vote',
            'Rating',
            'Share Score',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsCommentSettings(): array
    {
        return [
            'Comments',
            'Feedback',
            'Suggestions',
            'Questions',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsCopyrightOptions(): array
    {
        return [
            'Participant Retains Copyright',
            'Organizer May Display',
            'Organizer May Promote',
            'Commercial Rights Reserved',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsAiUsageOptions(): array
    {
        return [
            'No',
            'Partially',
            'Fully AI Generated',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsSupportingDocumentTypes(): array
    {
        return [
            'Identity Proof (Optional)',
            'Student ID',
            'College ID',
            'Participation Certificate',
            'Consent Form',
            'Authorization Letter',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsEntryFields(): array
    {
        return [
            'Title',
            'Description',
            'Files',
            'Video',
            'Images',
            'Documents',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsTeamDetailOptions(): array
    {
        return [
            'Team Leader',
            'Institution',
            'Organization',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function competitionsDeclarationStatements(): array
    {
        return [
            'competitions_declaration_original' => 'This submission is my original work.',
            'competitions_declaration_permission' => 'I own or have permission to use all uploaded content.',
            'competitions_declaration_ai_disclosed' => 'AI assistance, if any, has been disclosed.',
            'competitions_declaration_rules' => 'I agree to the competition rules and judging process.',
            'competitions_declaration_display' => 'SoilnWater may display my submission as part of the competition.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsTagExamples(): array
    {
        return [
            'Photography',
            'Environment',
            'Water',
            'Competition',
            'Innovation',
        ];
    }

    /**
     * Community sections that can originate competitions (one engine, many entry points).
     *
     * @return list<string>
     */
    public static function competitionsOriginSections(): array
    {
        return [
            'Poetry',
            'Photography',
            'Science',
            'Environment',
            'Agriculture',
            'Children',
            'Women',
            'Students',
            'Creative Corner',
            'Business',
            'Technology',
            'Community Service',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsAwardBadges(): array
    {
        return [
            'Winner',
            'Runner-Up',
            'Finalist',
            'Top 10',
            'Top 100',
            'Community Choice',
            'Most Creative',
            'Innovation Award',
            'Green Champion',
            'Water Warrior',
            'Young Scientist',
            'Best Photographer',
            'Best Writer',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsLeaderboardTypes(): array
    {
        return [
            'Schools',
            'Colleges',
            'Cities',
            'Districts',
            'States',
            'Individual Participants',
            'Organizations',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsVotingFraudProtections(): array
    {
        return [
            'Verified accounts only',
            'One vote per user',
            'Email/OTP verification',
            'CAPTCHA',
            'Duplicate detection',
            'IP/device monitoring',
            'AI-based anomaly detection',
            'Admin override for suspicious activity',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsEcommerceOptions(): array
    {
        return [
            'Sold through SoilnWater Marketplace',
            'Licensed',
            'Auctioned',
            'Printed on merchandise',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsDigitalCertificateTypes(): array
    {
        return [
            'Participation Certificates',
            'Winner Certificates',
            'Jury Appreciation Certificates',
            'Downloadable PDFs',
            'Verifiable certificate IDs',
            'Certificate QR codes',
        ];
    }

    /**
     * @return list<string>
     */
    public static function competitionsSponsorExamples(): array
    {
        return [
            'Photography by Camera Store',
            'Smart Farming by Agri Company',
            'Startup Challenge by Bank',
            'Coding Challenge by IT Company',
            'Water Conservation by NGO',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function competitionsUniqueFeatureLabels(): array
    {
        return [
            'multi_section' => 'Multi-Section Competition',
            'auto_portfolio' => 'Auto Portfolio Generation',
            'entry_qr_codes' => 'QR Code for Every Entry',
            'achievement_badges' => 'Achievement & Badge System',
            'leaderboards' => 'Leaderboard',
            'institution_dashboard' => 'School & College Dashboard',
            'sponsored_branding' => 'Sponsored Competition',
            'ecommerce' => 'E-commerce Integration',
            'voting_fraud_protection' => 'Community Voting with Fraud Protection',
            'digital_certificates' => 'Certificates & Digital Awards',
        ];
    }

    public static function slugFor(string $label): string
    {
        return Str::slug($label);
    }
}
