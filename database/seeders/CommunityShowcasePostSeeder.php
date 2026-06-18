<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use App\Support\CommunityPostFormFields;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityShowcasePostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    private const SAMPLE_AUDIO_URL = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3';

    private const SAMPLE_PDF_URL = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Community Showcase Author',
            'email' => 'community-showcase@example.com',
        ]);

        foreach ($this->showcasePosts() as $post) {
            $this->upsertPost($author, $post);
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): void
    {
        $bookPages = $post['book_pages'] ?? null;
        $meta = array_merge(
            ['author_bio' => $post['author_bio'] ?? 'Community showcase author writing about water, soil, and local life in Rajasthan.'],
            $post['meta'] ?? []
        );

        if (is_array($bookPages)) {
            $meta['book_pages'] = $bookPages;
        }

        foreach (CommunityPostFormFields::fieldsFor($post['content_type']) as $field) {
            if (! array_key_exists($field['name'], $meta)) {
                $meta[$field['name']] = $this->defaultFieldValue($field);
            }
        }

        CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => $post['content_type'],
                'category' => $post['category'],
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'body' => $bookPages
                    ? CommunityPost::bodyFromBookPages($bookPages)
                    : $post['body'],
                'featured_image_path' => $post['featured_image'],
                'featured_images' => $post['featured_images'] ?? [$post['featured_image']],
                'tags' => $post['tags'],
                'location_type' => CommunityPost::LOCATION_TYPE_CITY,
                'location' => self::LOCATION,
                'location_lat' => self::LOCATION_LAT,
                'location_lng' => self::LOCATION_LNG,
                'video' => $post['video'] ?? null,
                'meta' => array_filter($meta, fn ($value) => filled($value) || is_bool($value) || is_array($value)),
                'allow_comments' => true,
                'allow_questions' => $post['allow_questions'] ?? true,
                'allow_suggestions' => $post['allow_suggestions'] ?? false,
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => now()->subDays($post['days_ago']),
            ]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function showcasePosts(): array
    {
        return [
            $this->articlePost(),
            $this->reportPost(),
            $this->newsPost(),
            $this->poetryPost(),
            $this->storyPost(),
            $this->autobiographyPost(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function articlePost(): array
    {
        return [
            'slug' => 'showcase-article-drip-irrigation',
            'content_type' => 'articles',
            'category' => 'Agriculture',
            'title' => 'Drip Irrigation for Small Farms: A Practical Field Guide',
            'excerpt' => 'A step-by-step article on planning, installing, and maintaining drip irrigation for arid and semi-arid smallholdings.',
            'featured_image' => 'https://picsum.photos/seed/showcase-article/960/540',
            'tags' => ['Articles', 'Agriculture', 'Drip Irrigation', 'Showcase'],
            'days_ago' => 1,
            'body' => <<<'HTML'
<p><strong>Why drip irrigation matters</strong></p>
<p>Small farms across Rajasthan lose precious groundwater to evaporation and uneven flooding. Drip systems deliver water directly to the root zone, reducing waste and improving crop consistency.</p>
<h3>Planning your layout</h3>
<p>Start with a simple sketch of beds, main lines, and filter placement. Measure flow rate at the source before buying emitters.</p>
<ul>
    <li>Map crop rows and spacing</li>
    <li>Choose filter and pressure regulator capacity</li>
    <li>Plan a weekly inspection routine</li>
</ul>
<p>Even a modest pilot plot can demonstrate savings within one season and build confidence for expansion.</p>
HTML,
            'meta' => [
                'article_type' => 'Guide',
                'article_subtitle' => 'Save water, protect soil, and improve yields on plots under two acres',
                'reading_time' => '12',
                'key_takeaways' => "Measure source flow before buying equipment.\nStart with one crop block and expand after one season.\nWeekly filter checks prevent costly clogging.",
                'references' => "Rajasthan Horticulture Department drip irrigation bulletin.\nFAO small-scale irrigation handbook.\nLocal krishi vigyan kendra field notes.",
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reportPost(): array
    {
        return [
            'slug' => 'showcase-report-water-pipeline',
            'content_type' => 'reports',
            'category' => 'Water Report',
            'title' => 'Community Report: Broken Pipeline Near Ward 12 Market',
            'excerpt' => 'A field report documenting recurring water supply failures, resident impact, and recommended civic follow-up.',
            'featured_image' => 'https://picsum.photos/seed/showcase-report/960/540',
            'tags' => ['Reports', 'Water', 'Infrastructure', 'Showcase'],
            'days_ago' => 3,
            'body' => <<<'HTML'
<p>This report summarizes observations from Ward 12 market residents regarding a recurring pipeline failure that disrupts morning supply three to four days each week.</p>
<p>Community volunteers documented flow interruption times, photographed valve conditions, and collected statements from shopkeepers and households.</p>
HTML,
            'meta' => [
                'report_status' => CommunityContentTaxonomy::reportStatuses()[0],
                'report_type' => CommunityContentTaxonomy::reportTypes()[0],
                'observation_period_from' => now()->subMonths(2)->toDateString(),
                'observation_period_to' => now()->subWeek()->toDateString(),
                'report_author_name' => 'Ward 12 Water Watch Group',
                'report_author_type' => CommunityContentTaxonomy::reportAuthorTypes()[0],
                'organization_type' => CommunityContentTaxonomy::reportOrganizationTypes()[0],
                'organization_name' => 'Soil & Water Residents Forum',
                'location_country' => 'India',
                'location_state' => 'Rajasthan',
                'location_district' => 'Jaipur',
                'location_city' => 'Jaipur',
                'location_locality' => 'Ward 12 Market',
                'key_findings' => "Supply fails between 6:00 AM and 9:00 AM on multiple weekdays.\nVisible leakage near the junction valve.\nShopkeepers report revenue loss on no-water days.",
                'report_analysis' => 'The failure pattern suggests a partial blockage and aging gasket at the junction rather than city-wide pressure loss.',
                'recommendations' => "Request urgent valve inspection from the municipal water department.\nPublish a public repair timeline.\nInstall a temporary community notice board for daily supply updates.",
                'report_conclusion' => 'Timely maintenance and transparent communication can restore trust and reduce economic impact on local businesses.',
                'action_needed' => 'Yes',
                'action_requested_from' => CommunityContentTaxonomy::reportActionRequestedFrom()[0],
                'suggested_solution' => 'Schedule a joint site visit with ward councillor, residents, and maintenance engineers within seven days.',
                'issue_priority' => 'High',
                'issue_status' => 'Open',
                'reported_to' => 'Municipal Water Department',
                'issue_reference' => 'WD-SHOWCASE-2026-0412',
                'report_subtitle' => 'Field observations and community recommendations',
                'reporting_period' => 'Feb–Apr 2026',
                'report_date' => now()->subWeek()->toDateString(),
                'prepared_by' => 'Ward 12 Water Watch Group',
                'report_scope' => 'Document service disruption patterns and propose actionable follow-up.',
                'methodology' => 'Resident interviews, timed supply checks, and photographic evidence.',
                'data_sources' => 'Household logs, shopkeeper statements, and on-site photos.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsPost(): array
    {
        return [
            'slug' => 'showcase-news-summer-supply',
            'content_type' => 'news',
            'category' => 'Water News',
            'title' => 'Municipal Water Supply Schedule Updated for Summer Demand',
            'excerpt' => 'Official summer rationing plan announced for Jaipur wards with adjusted morning and evening supply windows.',
            'featured_image' => 'https://picsum.photos/seed/showcase-news/960/540',
            'tags' => ['News', 'Water', 'Municipal', 'Showcase'],
            'days_ago' => 2,
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=EngW7tLk6R8',
                'video_id' => 'EngW7tLk6R8',
            ],
            'body' => <<<'HTML'
<p>The municipal water board released an updated summer schedule aimed at balancing reservoir levels with neighbourhood demand across Jaipur.</p>
<p>Residents are advised to store water only for essential use and report illegal booster pump connections through the official helpline.</p>
HTML,
            'meta' => [
                'news_type' => CommunityContentTaxonomy::newsTypes()[0],
                'event_date' => now()->subDays(2)->toDateString(),
                'event_time' => '10:00 AM',
                'news_date' => now()->subDays(2)->format('Y-m-d\TH:i'),
                'news_subtitle' => 'Morning and evening windows shift starting Monday',
                'news_dateline' => 'Jaipur',
                'reporter_name' => 'Community News Desk',
                'news_source_type' => CommunityContentTaxonomy::newsSourceTypes()[1],
                'news_source' => 'Municipal water department bulletin',
                'source_url' => 'https://example.com/municipal-water-bulletin',
                'verification_notes' => 'Cross-checked against the official release and ward notice boards.',
                'news_related_authority' => 'Jaipur Municipal Corporation',
                'news_people_organizations' => 'Municipal Water Board, Ward councillors, Resident welfare associations',
                'news_priority' => 'Important',
                'news_impact_level' => CommunityContentTaxonomy::newsImpactLevels()[1],
                'news_affected_group' => CommunityContentTaxonomy::newsAffectedGroups()[0],
                'impact_area' => 'Households and small businesses across affected Jaipur wards.',
                'quote_attribution' => '"Teams will monitor supply pressure daily," said the department spokesperson.',
                'news_what_happened' => 'The municipal board announced revised summer supply windows to manage reservoir drawdown.',
                'news_where_happened' => 'Jaipur city wards served by the main distribution network.',
                'news_when_happened' => 'Effective from the coming Monday morning cycle.',
                'news_who_involved' => 'Municipal Water Board, ward offices, and resident welfare groups.',
                'news_why_important' => 'Predictable supply windows help families and businesses plan around rationing.',
                'news_current_status' => 'Schedule published; ward teams asked to display local timings by Sunday evening.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function poetryPost(): array
    {
        return [
            'slug' => 'showcase-poem-monsoon-desert',
            'content_type' => 'poetry',
            'category' => CommunityContentTaxonomy::poetryMainCategories()[0],
            'title' => 'Monsoon Over the Desert',
            'excerpt' => 'A nature poem about waiting, rain, and the quiet joy of a thirsty land finally drinking.',
            'featured_image' => 'https://picsum.photos/seed/showcase-poetry/960/540',
            'tags' => ['Poetry', 'Nature', 'Monsoon', 'Showcase'],
            'days_ago' => 4,
            'body' => <<<'HTML'
<p>The sky holds its breath above the dust,<br>
and every leaf becomes a prayer.<br>
We count the hours by the cracked clay cup,<br>
then cheer the first brave drops in the air.</p>
<p>The desert does not ask for much—<br>
only justice from a passing cloud.<br>
When water finds us, we remember touch,<br>
and every voice returns, allowed.</p>
HTML,
            'meta' => [
                'poetry_type' => CommunityContentTaxonomy::poetryTypes()[0],
                'sub_category' => CommunityContentTaxonomy::poetrySubCategories()[2],
                'poem_language' => 'English',
                'poetry_themes' => [CommunityContentTaxonomy::poetryThemes()[2]],
                'poetry_target_audience' => [CommunityContentTaxonomy::poetryTargetAudiences()[0]],
                'poetry_inspiration' => 'Written after an evening storm broke a long dry spell over the Aravalli foothills.',
                'dedication' => 'For every farmer who watched the horizon all summer',
                'reading_time' => '2',
                'location_country' => 'India',
                'location_state' => 'Rajasthan',
                'location_district' => 'Jaipur',
                'location_city' => 'Jaipur',
                'poetry_part_of_series' => 'Yes',
                'poetry_series_name' => 'Desert Seasons',
                'poetry_series_part' => 'Part 2 – Monsoon',
                'poetry_audio' => [
                    'type' => 'upload',
                    'name' => 'monsoon-recitation.mp3',
                    'url' => self::SAMPLE_AUDIO_URL,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function storyPost(): array
    {
        return [
            'slug' => 'showcase-story-neighbourhood-rebuilt',
            'content_type' => 'stories',
            'category' => 'Inspirational Stories',
            'title' => 'How Our Neighbourhood Rebuilt After the Drought',
            'excerpt' => 'A multi-page inspirational story about patience, shared water charts, and a lane that learned to wait together.',
            'featured_image' => 'https://picsum.photos/seed/showcase-story/960/540',
            'tags' => ['Stories', 'Inspirational', 'Community', 'Showcase'],
            'days_ago' => 5,
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=EngW7tLk6R8',
                'video_id' => 'EngW7tLk6R8',
            ],
            'book_pages' => [
                ['content' => '<p>The summer had stretched longer than anyone in Ward 14 remembered. The hand pump near the temple coughed out muddy sips in the morning and fell silent by noon. Children still laughed, but their games ended earlier now.</p><p>On the fourth evening of waiting, Mrs. Kapoor placed a brass plate of water outside her door and invited the lane to do the same. Nobody called it a ritual. They simply wanted to see, together, how little remained.</p>'],
                ['content' => '<p>When the clouds finally gathered, the lane did not rush indoors. Old men brought chairs into the courtyard. Teenagers switched off their music. Even the stray dogs settled beneath the neem tree.</p><p>The first drops were shy. Then the sky opened. People cheered, then laughed at themselves for cheering. Water is never only water in a dry town.</p>'],
                ['content' => '<p>By morning the gutters were running clear. Children sailed paper boats through puddles. The hand pump returned with a steady rhythm. Years later, newcomers asked why the lane kept a shared water chart on the temple wall.</p><p>Elders would point to that night and say: we learned to wait together. That, they agreed, was the real rain.</p>'],
            ],
            'meta' => [
                'story_genre' => 'Inspirational',
                'story_type' => CommunityContentTaxonomy::storyTypes()[0],
                'story_language' => CommunityContentTaxonomy::storyLanguages()[0],
                'story_themes' => [CommunityContentTaxonomy::storyThemes()[0], CommunityContentTaxonomy::storyThemes()[3]],
                'story_target_audience' => [CommunityContentTaxonomy::storyTargetAudiences()[0]],
                'story_moral_takeaway' => 'Communities grow stronger when neighbours share scarcity honestly and celebrate relief together.',
                'story_main_characters' => 'Mrs. Kapoor, lane elders, schoolchildren, temple caretaker',
                'story_character_type' => CommunityContentTaxonomy::storyCharacterTypes()[0],
                'story_place_type' => CommunityContentTaxonomy::storyPlaceTypes()[1],
                'story_place_names' => 'Ward 14, Jaipur, Rajasthan',
                'story_time_period' => CommunityContentTaxonomy::storyTimePeriods()[1],
                'mood_or_theme' => 'Hope and resilience',
                'reading_time' => '8',
                'story_gallery' => [
                    [
                        'path' => 'seed/showcase-story-gallery-1.jpg',
                        'url' => 'https://picsum.photos/seed/showcase-story-g1/800/600',
                        'name' => 'Evening queue at the hand pump',
                    ],
                    [
                        'path' => 'seed/showcase-story-gallery-2.jpg',
                        'url' => 'https://picsum.photos/seed/showcase-story-g2/800/600',
                        'name' => 'First rain over the lane',
                    ],
                ],
                'story_audio' => [
                    'type' => 'upload',
                    'name' => 'story-narration.mp3',
                    'url' => self::SAMPLE_AUDIO_URL,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function autobiographyPost(): array
    {
        return [
            'slug' => 'showcase-autobiography-village-volunteer',
            'content_type' => 'autobiography',
            'category' => "Farmer's Journey",
            'title' => 'From Village Student to Water Conservation Volunteer',
            'excerpt' => 'A complete life-story autobiography with timeline, chapters, lessons, achievements, audio memories, and supporting documents.',
            'featured_image' => 'https://picsum.photos/seed/showcase-autobiography/960/540',
            'tags' => ['Autobiography', 'Education', 'Water', 'Showcase'],
            'days_ago' => 7,
            'author_bio' => 'I grew up in a Rajasthan village, studied in Jaipur, and returned to help neighbours understand water literacy and local action.',
            'book_pages' => [
                [
                    'title' => 'Chapter 1 – Childhood',
                    'summary' => 'Learning responsibility at the village hand pump.',
                    'content' => '<p>My earliest chore was carrying water before school. I hated the weight of the buckets, but I loved the conversations at the pump. Old farmers taught me to listen before offering solutions.</p><p>Those mornings shaped the work I do today: practical, local, and patient.</p>',
                    'language' => 'en',
                ],
                [
                    'title' => 'Chapter 2 – City Lessons',
                    'summary' => 'College, career choices, and missing home.',
                    'content' => '<p>College in Jaipur opened new worlds—libraries, debates, late-night study groups. I learned how to write reports and speak in meetings. I also learned loneliness.</p><p>On one visit home, my mother asked whether I still knew how to fix the hand pump. I did not. That question followed me back to the city.</p>',
                    'language' => 'en',
                ],
                [
                    'title' => 'Chapter 3 – Returning with Purpose',
                    'summary' => 'Volunteering, training neighbours, and staying rooted.',
                    'content' => '<p>I joined a small nonprofit focused on community water literacy. My city skills finally mattered: mapping complaints, translating reports, training volunteers.</p><p>But the work that changed me was simpler—showing up when the tanker was late and teaching children how to read the ward supply chart.</p>',
                    'language' => 'en',
                ],
            ],
            'meta' => [
                'autobiography_type' => CommunityContentTaxonomy::autobiographyTypes()[0],
                'birth_place' => 'Sikar, Rajasthan, India',
                'current_location' => 'Jaipur, Rajasthan, India',
                'places_mentioned' => ['Sikar', 'Jaipur', 'Amer', 'Chomu'],
                'life_timeline' => [
                    [
                        'year' => '1998',
                        'title' => 'Born in Sikar district',
                        'description' => 'Grew up in a farming family that measured seasons by reservoir levels and festival dates.',
                        'photo' => [
                            'url' => 'https://picsum.photos/seed/showcase-auto-timeline-1/400/300',
                        ],
                    ],
                    [
                        'year' => '2016',
                        'title' => 'First year in Jaipur',
                        'description' => 'Moved to the city for college and discovered environmental science as a calling.',
                        'photo' => [
                            'url' => 'https://picsum.photos/seed/showcase-auto-timeline-2/400/300',
                        ],
                    ],
                    [
                        'year' => '2022',
                        'title' => 'Joined water literacy nonprofit',
                        'description' => 'Returned to community work full time, training volunteers and mapping ward supply issues.',
                        'photo' => [
                            'url' => 'https://picsum.photos/seed/showcase-auto-timeline-3/400/300',
                        ],
                    ],
                ],
                'key_lessons_learned' => [
                    'Local work is real work, even when it looks small from a distance.',
                    'Listening builds more trust than arriving with ready answers.',
                    'Teach children to read supply charts and they teach their families.',
                ],
                'autobiography_audio' => [
                    'type' => 'recording',
                    'name' => 'Opening reflection.webm',
                    'url' => self::SAMPLE_AUDIO_URL,
                ],
                'autobiography_achievements' => [
                    [
                        'award_name' => 'Community Water Champion',
                        'year' => '2024',
                        'description' => 'Recognized for training more than 120 volunteers across Jaipur wards.',
                        'image' => [
                            'url' => 'https://picsum.photos/seed/showcase-auto-award/200/200',
                        ],
                    ],
                    [
                        'award_name' => 'District Youth Service Medal',
                        'year' => '2023',
                        'description' => 'Honoured for leading school workshops on rainwater harvesting.',
                        'image' => [
                            'url' => 'https://picsum.photos/seed/showcase-auto-award-2/200/200',
                        ],
                    ],
                ],
                'autobiography_documents' => [
                    [
                        'name' => 'Volunteer training handbook.pdf',
                        'url' => self::SAMPLE_PDF_URL,
                    ],
                    [
                        'name' => 'Ward water literacy outline.pdf',
                        'url' => self::SAMPLE_PDF_URL,
                    ],
                ],
                'related_people' => [
                    ['name' => 'Mrs. Kapoor', 'relationship' => 'Mentor'],
                    ['name' => 'Ramesh Kumar', 'relationship' => 'Village elder'],
                    ['name' => 'Dr. Priya Mehta', 'relationship' => 'College professor'],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function defaultFieldValue(array $field): mixed
    {
        if (($field['type'] ?? '') === 'checkbox') {
            return true;
        }

        if (($field['type'] ?? '') === 'select') {
            return $field['options'][0] ?? 'General';
        }

        if (($field['type'] ?? '') === 'url') {
            return 'https://example.com/'.Str::slug($field['name']);
        }

        if (($field['type'] ?? '') === 'date') {
            return now()->subMonth()->toDateString();
        }

        if (($field['type'] ?? '') === 'datetime-local') {
            return now()->subDay()->format('Y-m-d\TH:i');
        }

        if (($field['type'] ?? '') === 'textarea') {
            return 'Showcase '.$field['label'].' populated by CommunityShowcasePostSeeder.';
        }

        return Str::headline(str_replace('_', ' ', $field['name'])).' showcase value';
    }
}
