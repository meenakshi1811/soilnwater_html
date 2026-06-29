<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use App\Support\CommunityPostFormFields;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityPostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    /**
     * @var array<string, string>
     */
    private array $sampleTopics = [
        'articles' => 'Smart irrigation practices for arid regions',
        'reports' => 'Broken water pipeline near community market',
        'news' => 'Municipal water supply schedule updated for summer',
        'stories' => 'How our neighbourhood rebuilt after the drought',
        'poetry' => 'Monsoon over the desert',
        'biography' => 'Dr. Rajesh Mehta, pioneer of watershed management',
        'autobiography' => 'From village student to water conservation volunteer',
        'childrens-corner' => 'The little seed that saved a garden',
        'awareness' => 'Save every drop this summer',
        'business' => 'Building a local water-testing startup',
        'student-corner' => 'Understanding groundwater recharge in school science',
        'career' => 'Career paths in environmental engineering',
        'health-wellness' => 'Hydration and heat safety during summer',
        'womens-world' => 'Women leading rooftop rainwater projects',
        'senior-citizens-forum' => 'Lessons from a lifetime of community service',
        'youth-corner' => 'Young innovators tackling water waste',
        'jobs-employment' => 'Junior field officer opening at water board',
        'opinions-views' => 'Why local water audits should be mandatory',
        'travel-diaries' => 'Exploring stepwell heritage sites in Rajasthan',
        'culture-heritage' => 'Traditional water harvesting at Amber Fort',
        'astro-consultancy' => 'Planning auspicious dates for well inauguration',
        'religion-spirituality' => 'Water as sacred offering in local temples',
        'agriculture' => 'Drip irrigation results on a small wheat farm',
        'environment' => 'Reviving urban lakes through citizen action',
        'science-technology' => 'Smart soil moisture monitoring with IoT sensors for farmers',
        'local-voices' => 'Residents speak up about street flooding',
        'community-issues' => 'Campaign for safer drinking water in ward 12',
        'creative-corner' => 'Photography series on monsoon clouds',
        'competitions' => 'Essay contest on water conservation',
        'discussions' => 'Should rainwater harvesting be compulsory?',
    ];

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Community Demo Author',
            'email' => 'community-demo@example.com',
        ]);

        foreach (CommunityContentTaxonomy::formTypes() as $typeKey => $type) {
            if (in_array($typeKey, ['environment', 'agriculture', 'astro-consultancy'], true)) {
                continue;
            }

            $topic = $this->sampleTopics[$typeKey] ?? $type['label'].' community feature overview';
            $slug = 'sample-'.$typeKey;

            CommunityPost::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $author->id,
                    'content_type' => $typeKey,
                    'category' => $this->categoryFor($typeKey, $type),
                    'title' => $type['label'].' sample: '.$topic,
                    'excerpt' => 'Demo '.$type['label'].' post with complete metadata, location, media, and discussion settings for preview and QA.',
                    'body' => $this->bodyFor($typeKey, $type['label'], $topic),
                    'featured_image_path' => 'https://picsum.photos/seed/soil-water-'.Str::slug($typeKey).'-1/960/540',
                    'featured_images' => [
                        'https://picsum.photos/seed/soil-water-'.Str::slug($typeKey).'-1/960/540',
                        'https://picsum.photos/seed/soil-water-'.Str::slug($typeKey).'-2/960/540',
                    ],
                    'tags' => [$type['label'], 'Sample Data', 'Soil & Water', 'Demo'],
                    'location_type' => CommunityPost::LOCATION_TYPE_CITY,
                    'location' => self::LOCATION,
                    'location_lat' => self::LOCATION_LAT,
                    'location_lng' => self::LOCATION_LNG,
                    'video' => $this->videoFor($typeKey),
                    'meta' => $this->metaFor($typeKey, $type['label'], $topic),
                    'allow_comments' => true,
                    'status' => CommunityPost::STATUS_PUBLISHED,
                    'published_at' => now()->subDays(array_search($typeKey, array_keys(CommunityContentTaxonomy::formTypes()), true) ?: 0),
                ]
            );
        }
    }

    /**
     * @param  array{label: string, description: string, categories: list<string>}  $type
     */
    private function categoryFor(string $typeKey, array $type): string
    {
        if ($typeKey === 'reports') {
            return CommunityContentTaxonomy::reportMainCategories()[0];
        }

        return $type['categories'][0];
    }

    private function bodyFor(string $typeKey, string $typeLabel, string $topic): string
    {
        return <<<HTML
<p><strong>{$typeLabel} preview:</strong> {$topic}. This seeded post demonstrates the full community publishing flow for the {$typeKey} content type.</p>
<p>It includes a rich body, excerpt, tags, featured images, optional video, Google Places location coordinates, type-specific metadata, and an enabled discussion thread.</p>
<p>Use this sample to review listing cards, detail pages, filters, and edit forms across the platform without creating content manually.</p>
<ul>
    <li>Content type: {$typeLabel}</li>
    <li>Location: Jaipur, Rajasthan</li>
    <li>Discussion thread: enabled</li>
</ul>
HTML;
    }

    /**
     * @return array{type: string, url?: string, video_id?: string}|null
     */
    private function videoFor(string $typeKey): ?array
    {
        if (! in_array($typeKey, ['news', 'stories', 'travel-diaries', 'environment', 'agriculture'], true)) {
            return null;
        }

        return [
            'type' => 'youtube',
            'url' => 'https://www.youtube.com/watch?v=EngW7tLk6R8',
            'video_id' => 'EngW7tLk6R8',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metaFor(string $typeKey, string $typeLabel, string $topic): array
    {
        $meta = [
            'author_bio' => 'Community demo author covering '.$typeLabel.' topics across Rajasthan.',
        ];

        foreach (CommunityPostFormFields::fieldsFor($typeKey) as $field) {
            $meta[$field['name']] = $this->dummyValueForField($field, $typeLabel, $topic);
        }

        if ($typeKey === 'poetry') {
            $meta['sub_category'] = CommunityContentTaxonomy::poetrySubCategories()[0];
            $meta['poetry_themes'] = [CommunityContentTaxonomy::poetryThemes()[0]];
            $meta['poetry_target_audience'] = [CommunityContentTaxonomy::poetryTargetAudiences()[0]];
            $meta['poetry_inspiration'] = 'Inspired by a childhood visit to a village pond.';
        }

        if ($typeKey === 'reports') {
            $meta = array_merge($meta, [
                'report_subtitle' => 'Local infrastructure and service delivery review',
                'reporting_period' => 'Q2 2026',
                'report_date' => now()->subWeek()->toDateString(),
                'prepared_by' => 'Soil & Water Community Desk',
                'report_scope' => 'Assess recurring civic issues and recommended follow-up actions.',
                'methodology' => 'Resident interviews, field observation, and department correspondence.',
                'data_sources' => 'Citizen reports, ward office notes, and on-site photos.',
                'key_findings' => "Issue confirmed by multiple residents.\nResponse time from department needs improvement.",
                'report_analysis' => 'The delay appears linked to incomplete handoffs between ward office and maintenance teams.',
                'recommendations' => "Escalate to the relevant department.\nPublish monthly progress updates to the community.",
                'report_conclusion' => 'Community follow-up and transparent reporting can improve resolution timelines.',
                'action_needed' => 'Yes',
                'action_requested_from' => 'Panchayat',
                'suggested_solution' => 'Schedule a ward-level review meeting and assign a visible action owner.',
            ]);
        }

        return array_filter($meta, fn ($value) => filled($value) || is_bool($value));
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function dummyValueForField(array $field, string $typeLabel, string $topic): mixed
    {
        $name = $field['name'];
        $type = $field['type'] ?? 'text';

        if ($type === 'checkbox') {
            return true;
        }

        if ($type === 'select') {
            return $field['options'][0] ?? 'General';
        }

        if ($type === 'url') {
            return 'https://example.com/'.Str::slug($typeLabel).'-resource';
        }

        if ($type === 'date') {
            return now()->addMonth()->toDateString();
        }

        if ($type === 'datetime-local') {
            return now()->subDay()->format('Y-m-d\TH:i');
        }

        if ($type === 'textarea') {
            return match ($name) {
                'fact_summary' => 'Who: local residents. What: service schedule change. When: effective Monday. Where: Jaipur wards. Why: summer demand planning.',
                'verification_notes' => 'Cross-checked with the official release and ward notice boards.',
                'impact_area' => 'Residents in nearby wards and local shopkeepers.',
                'quote_attribution' => '"Teams will monitor supply pressure daily," said the department spokesperson.',
                'job_summary' => 'Support field visits, compile reports, and coordinate with ward volunteers.',
                'learning_outcome' => 'Readers will understand the basics of '.$topic.' and how to apply them locally.',
                'key_takeaways' => "Understand the core issue.\nKnow who is affected.\nLearn practical next steps.",
                'references' => 'Local department bulletin, community survey notes, and public meeting minutes.',
                'key_achievements' => 'Led major conservation projects and mentored youth volunteers across the district.',
                'lessons_learned' => 'Persistence, community trust, and practical action matter more than perfect plans.',
                'call_to_action' => 'Share this post, attend the local meeting, and volunteer for the next drive.',
                'supporting_points' => "Point one supports the main argument.\nPoint two adds local evidence.\nPoint three suggests a practical next step.",
                'travel_tips' => 'Visit early morning, carry water, respect local customs, and support community guides.',
                'cultural_significance' => 'This tradition reflects how local communities managed water long before modern pipelines.',
                'practical_tips' => 'Start small, measure results weekly, and share successes with neighbouring farmers.',
                'environmental_impact' => 'Reduced runoff, cleaner public spaces, and stronger community awareness.',
                'action_steps' => 'Report issues promptly, reduce waste, and join local clean-up drives.',
                'tech_summary' => 'The approach reduced waste and improved response time in pilot wards.',
                'key_findings' => 'Pilot results show measurable improvement in response time and water savings.',
                'community_impact' => 'Families, shopkeepers, and schoolchildren are directly affected every week.',
                'proposed_solution' => 'Form a ward committee, document the issue, and request a fixed timeline from officials.',
                'creative_inspiration' => 'Inspired by monsoon light, neighbourhood resilience, and local colour.',
                'eligibility' => 'Open to residents aged 12 and above with original submissions.',
                'submission_rules' => 'Original work only. Maximum 1,000 words. Submit before the deadline.',
                'discussion_prompt' => 'What has worked best in your area, and what would you do differently?',
                'wellness_summary' => 'General wellness guidance for staying healthy in hot weather.',
                'perspective_summary' => 'A practical community perspective grounded in lived experience.',
                'experience_summary' => 'Decades of local experience distilled into advice younger readers can use.',
                'youth_message' => 'Start with small actions, learn continuously, and collaborate with your community.',
                'spiritual_guidance' => 'A short reflection on gratitude, service, and mindful daily practice.',
                default => 'Sample '.$typeLabel.' detail for '.$topic.'. This field was populated by the community post seeder.',
            };
        }

        return match ($name) {
            'news_subtitle' => 'Local update for summer demand',
            'news_dateline' => 'Jaipur',
            'reporter_name' => 'Community News Desk',
            'news_source' => 'Municipal department release',
            'reading_time', 'child_age_range' => '6',
            'grade_level' => 'Class 5',
            'school_name' => 'Jaipur Public School',
            'consultation_fee' => 'Rs. 500 / 30 minutes',
            'availability' => 'Mon-Sat, 10:00 AM - 6:00 PM',
            'employer_name' => 'Rajasthan Water Board',
            'salary_range' => 'Rs. 18,000 - 22,000 / month',
            'experience_required' => '0-2 years',
            'reported_to' => 'Municipal Water Department',
            'issue_reference' => 'WD-SEED-'.Str::upper(Str::substr(md5($topic), 0, 6)),
            'historical_period' => '19th century',
            'farming_season' => 'Rabi',
            'crop_or_practice' => 'Drip irrigation',
            'timeline_period' => '1998-2026',
            'time_period' => '1920-1980',
            'subject_name' => 'Dr. Rajesh Mehta',
            'subject_field' => 'Water conservation scientist',
            'travel_dates' => 'March 2026',
            'destination' => 'Jaipur, Rajasthan',
            'campaign_topic' => 'Water conservation',
            'target_audience' => 'Residents and school communities',
            'wellness_topic' => 'Hydration and heat safety',
            'life_experience_area' => 'Retirement planning',
            'advice_category' => 'Advice to youth',
            'discussion_topic' => $topic,
            'poll_question' => 'Would you support a local water audit?',
            'prize_details' => 'Certificates, badges, and featured author placement',
            'local_issue_type' => 'Water supply disruption',
            'affected_area' => 'Ward 12 market area',
            'tools_used' => 'DSLR camera and natural light',
            'dedication' => 'For every neighbour who keeps our streets clean',
            'mood_or_theme' => 'Hope and resilience',
            'article_subtitle' => 'Practical guidance for local readers',
            'industry' => 'Water management',
            'subject_area' => 'Environmental science',
            'spiritual_tradition' => 'Local devotional practice',
            'scientific_field', 'tech_domain' => 'Environmental monitoring',
            'environmental_topic' => 'Urban water conservation',
            'topic_context' => 'Public policy and local implementation',
            default => Str::headline(str_replace('_', ' ', $name)).' sample value',
        };
    }
}
