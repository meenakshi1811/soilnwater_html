<?php

namespace App\Support;

use App\Models\CommunityPost;
use Illuminate\Validation\Rule;

class CommunityPostFormFields
{
    /**
     * @return array<string, array{title: string, badge: string, description: string, fields: list<array<string, mixed>>}>
     */
    public static function sections(): array
    {
        return [
            'articles' => self::section('Article details', 'Articles', 'Standard editorial fields used on professional publishing sites.', self::editorialFields()),
            'stories' => self::section('Story details', 'Stories', 'Help readers discover genre, mood, and reading time.', self::narrativeFields('story_genre', [
                'Inspirational', 'Motivational', 'Fiction', 'Real Life', 'Family', 'Social', 'Short Story',
            ])),
            'poetry' => self::section('Poetry details', 'Poetry', 'Poetry metadata similar to literary magazines and anthologies.', [
                self::select('poem_language', 'Poem language', ['Hindi', 'English', 'Urdu', 'Regional', 'Multilingual'], true),
                self::select('poem_form', 'Poem form / style', ['Free Verse', 'Ghazal', 'Sonnet', 'Haiku', 'Nazm', 'Other'], false),
                self::text('dedication', 'Dedication', 160, false, 'Optional dedication line'),
                self::text('reading_time', 'Estimated reading time (minutes)', 10, false, 'e.g. 3'),
            ]),
            'biography' => self::section('Biography details', 'Biography', 'Profile the subject with structured biography metadata.', self::profileBioFields()),
            'autobiography' => self::section('Autobiography details', 'Autobiography', 'Frame your personal journey with timeline and lessons.', [
                self::select('life_stage', 'Life stage / chapter', ['Student Life', 'Early Career', 'Mid Career', 'Retirement', 'Life Transition', 'Other'], true),
                self::text('timeline_period', 'Timeline / period covered', 120, true, 'e.g. 1998–2024'),
                self::textarea('lessons_learned', 'Key lessons learned', 2000, true, 'What readers should take away from your journey'),
            ]),
            'childrens-corner' => self::section("Children's Corner details", "Children's Corner", 'Safeguard child submissions with school and guardian context.', [
                self::checkbox('parent_approved', 'Parent / guardian approved'),
                self::text('school_name', 'School name', 160, false),
                self::select('child_age_range', 'Age range', ['3-5', '6-8', '9-12', '13-15'], false),
                self::text('grade_level', 'Grade / class', 40, false, 'e.g. Class 5'),
            ]),
            'awareness' => self::section('Awareness campaign details', 'Awareness', 'Campaign-style fields for public awareness content.', self::campaignFields()),
            'business' => self::section('Business details', 'Business', 'Business publishing fields for stage, industry, and insight.', self::professionalTipFields('business_stage', [
                'Startup', 'Small Business', 'SME', 'Enterprise', 'Freelancer', 'Other',
            ])),
            'education' => self::section('Education details', 'Education', 'Learning content metadata for courses, guides, and study material.', [
                self::select('education_level', 'Education level', ['School', 'College', 'Competitive Exams', 'Professional', 'Lifelong Learning'], true),
                self::text('subject_area', 'Subject / topic area', 120, true, 'e.g. Mathematics, UPSC, Coding'),
                self::textarea('learning_outcome', 'Learning outcome', 2000, true, 'What the reader will learn'),
                self::text('reading_time', 'Estimated reading time (minutes)', 10, false),
            ]),
            'career' => self::section('Career details', 'Career', 'Career guidance fields used on professional development platforms.', self::professionalTipFields('career_stage', [
                'Entry Level', 'Mid Career', 'Senior Level', 'Career Change', 'Student', 'Other',
            ])),
            'health-wellness' => self::section('Health & wellness details', 'Health & Wellness', 'Wellness content with audience and disclaimer context.', [
                self::text('wellness_topic', 'Wellness topic', 120, true, 'e.g. Nutrition, Mental wellness'),
                self::text('target_audience', 'Target audience', 120, true, 'Who this content is for'),
                self::checkbox('medical_disclaimer_ack', 'I confirm this is general wellness information, not medical advice'),
                self::textarea('wellness_summary', 'Key wellness takeaway', 2000, true),
            ]),
            'womens-world' => self::section("Women's World details", "Women's World", 'Audience-focused context for women-centric stories and advice.', [
                self::select('focus_area', 'Focus area', ['Working Women', 'Homemakers', 'Health', 'Entrepreneurship', 'Parenting', 'Self Development'], true),
                self::textarea('perspective_summary', 'Perspective / angle', 2000, true),
            ]),
            'senior-citizens-forum' => self::section('Senior Citizens details', 'Senior Citizens', 'Share life experience context for senior readers.', [
                self::text('life_experience_area', 'Life experience area', 120, true, 'e.g. Retirement planning, Health'),
                self::text('advice_category', 'Advice category', 120, false, 'e.g. Advice to youth'),
                self::textarea('experience_summary', 'Experience summary', 2000, true),
            ]),
            'youth-corner' => self::section('Youth Corner details', 'Youth Corner', 'Youth-focused metadata for age group and topic.', [
                self::select('youth_topic', 'Youth topic', ['Career', 'Startups', 'Technology', 'Relationships', 'Motivation', 'Fitness', 'Education'], true),
                self::select('age_group', 'Target age group', ['13-17', '18-21', '22-25', '26-30'], true),
                self::textarea('youth_message', 'Core message for youth', 2000, true),
            ]),
            'jobs-employment' => self::section('Job & employment details', 'Jobs & Employment', 'Job board style fields for alerts and opportunities.', [
                self::select('job_type', 'Employment type', ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance', 'Government'], true),
                self::text('experience_required', 'Experience required', 120, true, 'e.g. 0-2 years, Fresher'),
                self::text('employer_name', 'Employer / organization', 160, false),
                self::text('salary_range', 'Salary / stipend range', 120, false, 'Optional'),
                self::url('application_link', 'Application / details URL', false),
                self::textarea('job_summary', 'Role summary', 2000, true),
            ]),
            'opinions-views' => self::section('Opinion details', 'Opinions & Views', 'Editorial opinion structure with stance and supporting points.', self::opinionFields()),
            'travel-diaries' => self::section('Travel diary details', 'Travel Diaries', 'Travel blog fields for destination, dates, and tips.', [
                self::text('destination', 'Destination', 160, true, 'City, region, or landmark'),
                self::text('travel_dates', 'Travel dates / season', 120, true, 'e.g. March 2026 or Monsoon season'),
                self::select('trip_type', 'Trip type', ['Leisure', 'Pilgrimage', 'Business', 'Adventure', 'Eco Travel', 'Family'], true),
                self::textarea('travel_tips', 'Travel tips & highlights', 2000, true),
            ]),
            'culture-heritage' => self::section('Culture & heritage details', 'Culture & Heritage', 'Heritage publishing fields for festivals, sites, and traditions.', [
                self::select('heritage_type', 'Heritage type', ['Festival', 'Heritage Site', 'Traditional Art', 'Language', 'History', 'Customs'], true),
                self::text('historical_period', 'Historical period / era', 120, false),
                self::textarea('cultural_significance', 'Cultural significance', 2000, true),
            ]),
            'astro-consultancy' => self::section('Consultation details', 'Astro Consultancy', 'Consultation listing fields used on astrology platforms.', [
                self::text('consultation_fee', 'Consultation fee / pricing', 120, true, 'e.g. ₹500 / 30 min'),
                self::select('expertise_area', 'Expertise area', ['Astrology', 'Numerology', 'Vastu', 'Palmistry', 'Horoscope', 'Spiritual Guidance'], true),
                self::select('consultation_mode', 'Consultation mode', ['Online', 'In-person', 'Both'], true),
                self::text('availability', 'Availability', 160, false, 'Days / time slots'),
            ]),
            'religion-spirituality' => self::section('Spiritual details', 'Religion & Spirituality', 'Spiritual article metadata for tradition and practice.', [
                self::text('spiritual_tradition', 'Tradition / faith context', 120, true),
                self::select('practice_type', 'Practice type', ['Meditation', 'Temple Information', 'Festival', 'Scripture', 'Devotional', 'Guidance'], true),
                self::textarea('spiritual_guidance', 'Guidance summary', 2000, false),
            ]),
            'agriculture' => self::section('Agriculture details', 'Agriculture', 'Farmer-focused practical content metadata.', [
                self::text('crop_or_practice', 'Crop / farming practice', 120, true, 'e.g. Wheat, drip irrigation'),
                self::text('farming_season', 'Season / timing', 120, false, 'e.g. Rabi, Kharif'),
                self::textarea('practical_tips', 'Practical tips / guidance', 2000, true),
            ]),
            'environment' => self::section('Environment details', 'Environment', 'Environmental reporting and action content fields.', self::environmentFields()),
            'technology' => self::section('Technology details', 'Technology', 'Tech article metadata for domain and innovation stage.', self::techScienceFields('tech_domain', 'tech_summary')),
            'science' => self::section('Science details', 'Science', 'Science communication fields for field and findings.', self::techScienceFields('scientific_field', 'key_findings')),
            'local-voices' => self::section('Local voice details', 'Local Voices', 'Neighbourhood issue context for local discussions.', [
                self::text('local_issue_type', 'Local issue type', 120, true, 'e.g. Road problems, water issues'),
                self::text('affected_area', 'Affected area / neighbourhood', 160, true),
                self::textarea('community_impact', 'Community impact', 2000, true),
            ]),
            'community-issues' => self::section('Community issue details', 'Community Issues', 'Civic issue tracking with urgency and proposed solutions.', [
                self::select('issue_category', 'Issue category', ['Civic Issue', 'Public Suggestion', 'Public Grievance', 'Community Project', 'Social Campaign'], true),
                self::select('issue_urgency', 'Urgency', ['Low', 'Medium', 'High', 'Critical'], true),
                self::textarea('proposed_solution', 'Proposed solution / action', 2000, true),
            ]),
            'creative-corner' => self::section('Creative work details', 'Creative Corner', 'Portfolio-style metadata for creative submissions.', [
                self::select('creative_medium', 'Creative medium', ['Photography', 'Sketch', 'Painting', 'Craft', 'DIY Project', 'Digital Art'], true),
                self::text('tools_used', 'Tools / materials used', 160, false),
                self::textarea('creative_inspiration', 'Inspiration / artist statement', 2000, false),
            ]),
            'competitions' => self::section('Competition details', 'Competitions', 'Contest listing fields for deadlines, prizes, and rules.', [
                self::date('competition_deadline', 'Submission deadline', true),
                self::text('prize_details', 'Prizes / rewards', 255, true, 'Certificates, badges, cash, etc.'),
                self::textarea('eligibility', 'Eligibility criteria', 2000, true),
                self::textarea('submission_rules', 'Submission rules', 2000, true),
            ]),
            'discussions' => self::section('Discussion details', 'Discussions', 'Thread starter fields used on community discussion boards.', [
                self::text('discussion_topic', 'Discussion topic', 160, true),
                self::textarea('discussion_prompt', 'Opening question / prompt', 2000, true),
                self::text('poll_question', 'Optional poll question', 255, false),
            ]),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function fieldsFor(string $contentType): array
    {
        if (in_array($contentType, ['news', 'reports'], true)) {
            return self::legacyFieldsFor($contentType);
        }

        return self::sections()[$contentType]['fields'] ?? [];
    }

    /**
     * @return array<string, list<string|object>>
     */
    public static function validationRules(string $contentType): array
    {
        $rules = [];

        foreach (self::fieldsFor($contentType) as $field) {
            $rules[$field['name']] = self::rulesForField($field, $contentType);
        }

        if (CommunityPost::usesStructuredLocation($contentType)) {
            foreach (self::structuredLocationFields() as $field) {
                $rules[$field['name']] = self::rulesForField($field, $contentType);
            }
        }

        if ($contentType === 'news') {
            foreach (self::newsContentFields() as $field) {
                $rules[$field['name']] = self::rulesForField($field, $contentType);
            }
        }

        return $rules;
    }

    /**
     * @return list<string>
     */
    public static function metaKeys(): array
    {
        $keys = [];

        foreach (self::sections() as $section) {
            foreach ($section['fields'] as $field) {
                $keys[] = $field['name'];
            }
        }

        return array_values(array_unique(array_merge($keys, self::legacyMetaKeys())));
    }

    /**
     * @return array<string, mixed>
     */
    public static function metaPayloadFromRequest(\Illuminate\Http\Request $request, string $contentType): array
    {
        $payload = [];
        $allowedKeys = array_column(self::fieldsFor($contentType), 'name');

        if ($contentType === 'reports') {
            $allowedKeys = array_merge($allowedKeys, [
                'report_status', 'report_type', 'issue_priority', 'issue_status', 'reported_to', 'issue_reference',
                'observation_period_from', 'observation_period_to', 'report_author_name', 'report_author_type',
                'organization_type', 'organization_name',
                'action_needed', 'action_requested_from', 'suggested_solution',
            ]);
        }

        if (CommunityPost::usesStructuredLocation($contentType)) {
            $allowedKeys = array_merge($allowedKeys, CommunityPost::structuredLocationMetaKeys());
        }

        foreach (array_unique($allowedKeys) as $key) {
            if (in_array($key, self::checkboxKeys(), true)) {
                $payload[$key] = $request->boolean($key);

                continue;
            }

            if (! $request->has($key)) {
                continue;
            }

            $payload[$key] = $request->input($key);
        }

        if ($contentType === 'reports') {
            $payload['issue_status'] = $request->input('issue_status', 'Open');

            if ($request->input('action_needed') !== 'Yes') {
                unset($payload['action_requested_from'], $payload['suggested_solution']);
            }
        }

        return array_filter($payload, fn ($value) => filled($value) || is_bool($value));
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::sections() as $section) {
            foreach ($section['fields'] as $field) {
                $labels[$field['name']] = $field['label'];
            }
        }

        return array_merge($labels, self::legacyMetaLabels());
    }

    /**
     * Ordered report metadata keys and labels for detail views.
     *
     * @return array<string, string>
     */
    public static function reportDetailMetaOrder(): array
    {
        return [
            'report_status' => 'Report status',
            'report_type' => 'Report type',
            'observation_period_from' => 'Observation from',
            'observation_period_to' => 'Observation to',
            'report_author_name' => 'Author name',
            'report_author_type' => 'Author type',
            'organization_type' => 'Organization type',
            'organization_name' => 'Organization name',
            'key_findings' => 'Findings',
            'report_analysis' => 'Analysis',
            'recommendations' => 'Recommendations',
            'report_conclusion' => 'Conclusion',
            'action_needed' => 'Is action needed?',
            'action_requested_from' => 'Action requested from',
            'suggested_solution' => 'Suggested solution',
            'issue_priority' => 'Priority',
            'issue_status' => 'Issue status',
            'reported_to' => 'Reported to',
            'issue_reference' => 'Reference / complaint no.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function narrativeReportMetaKeys(): array
    {
        return ['key_findings', 'report_analysis', 'recommendations', 'report_conclusion', 'suggested_solution'];
    }

    public static function formatReportMetaValue(string $key, mixed $value): string
    {
        if (in_array($key, ['observation_period_from', 'observation_period_to'], true) && filled($value)) {
            return \Illuminate\Support\Carbon::parse($value)->format('d-M-Y');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }

    public static function formatNewsMetaValue(string $key, mixed $value): string
    {
        if (in_array($key, ['event_date', 'news_date'], true) && filled($value)) {
            return \Illuminate\Support\Carbon::parse($value)->format('j F Y'.($key === 'news_date' ? ', g:i A' : ''));
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }

    /**
     * Ordered news metadata keys and labels for detail views.
     *
     * @return array<string, string>
     */
    public static function newsDetailMetaOrder(): array
    {
        return [
            'news_type' => 'News type',
            'event_date' => 'Event date',
            'event_time' => 'Event time',
            'news_date' => 'News date',
            'news_dateline' => 'Dateline',
            'reporter_name' => 'Reporter / byline',
            'news_subtitle' => 'Subtitle / deck',
            ...self::newsContentMetaOrder(),
            'news_people_organizations' => 'People & organizations mentioned',
            'news_priority' => 'News priority',
            'news_impact_level' => 'Impact level',
            'news_affected_group' => 'Affected group',
            'impact_area' => 'Impact / affected area',
            'quote_attribution' => 'Quote / attribution',
            'news_source_type' => 'Source type',
            'news_source' => 'Source',
            'source_url' => 'Source URL',
            'verification_notes' => 'Verification notes',
            'news_related_authority' => 'Related authority',
        ];
    }

    /**
     * @return list<string>
     */
    public static function narrativeNewsMetaKeys(): array
    {
        return [
            'news_what_happened',
            'news_where_happened',
            'news_when_happened',
            'news_who_involved',
            'news_why_important',
            'news_current_status',
            'news_people_organizations',
            'verification_notes',
            'impact_area',
            'quote_attribution',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedNewsMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::newsDetailMetaOrder())
            ->mapWithKeys(fn (string $label, string $key): array => [$key => data_get($post->meta, $key)])
            ->filter(fn (mixed $value): bool => filled($value) || is_bool($value));
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedReportMetaForDisplay(\App\Models\CommunityPost $post, bool $includeLocation = false): \Illuminate\Support\Collection
    {
        $order = self::reportDetailMetaOrder();

        if ($includeLocation && filled($post->location)) {
            $order['location'] = 'GPS issue location';
        }

        return collect($order)
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                if ($key === 'location') {
                    return [$key => $post->location];
                }

                return [$key => data_get($post->meta, $key)];
            })
            ->filter(fn (mixed $value): bool => filled($value) || is_bool($value));
    }

    /**
     * @return list<string>
     */
    private static function checkboxKeys(): array
    {
        return ['parent_approved', 'medical_disclaimer_ack'];
    }

    /**
     * @return list<string>
     */
    private static function legacyMetaKeys(): array
    {
        return [
            'author_bio',
            'report_format',
            'report_subtitle',
            'reporting_period',
            'report_date',
            'prepared_by',
            'report_scope',
            'methodology',
            'data_sources',
            'key_findings',
            'recommendations',
            'news_type',
            'event_date',
            'event_time',
            'news_subtitle',
            'news_dateline',
            'news_date',
            'reporter_name',
            'news_source_type',
            'news_source',
            'source_url',
            'news_what_happened',
            'news_where_happened',
            'news_when_happened',
            'news_who_involved',
            'news_why_important',
            'news_current_status',
            'fact_summary',
            'verification_notes',
            'news_related_authority',
            'news_people_organizations',
            'news_priority',
            'news_impact_level',
            'news_affected_group',
            'impact_area',
            'quote_attribution',
            'report_status',
            'report_type',
            'issue_priority',
            'issue_status',
            'reported_to',
            'issue_reference',
            'observation_period_from',
            'observation_period_to',
            'report_author_name',
            'report_author_type',
            'organization_type',
            'organization_name',
            'report_analysis',
            'report_conclusion',
            'action_needed',
            'action_requested_from',
            'suggested_solution',
            'location_country',
            'location_state',
            'location_district',
            'location_city',
            'location_locality',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function legacyMetaLabels(): array
    {
        return [
            'author_bio' => 'Author bio',
            'report_subtitle' => 'Subtitle',
            'reporting_period' => 'Reporting period',
            'report_date' => 'Report date',
            'prepared_by' => 'Prepared by',
            'report_scope' => 'Scope / objective',
            'methodology' => 'Methodology',
            'data_sources' => 'Data sources',
            'key_findings' => 'Findings',
            'recommendations' => 'Recommendations',
            'news_type' => 'News type',
            'event_date' => 'Event date',
            'event_time' => 'Event time',
            'news_subtitle' => 'Subtitle / deck',
            'news_dateline' => 'Dateline',
            'news_date' => 'News date',
            'reporter_name' => 'Reporter / byline',
            'news_source_type' => 'Source type',
            'news_source' => 'Source',
            'source_url' => 'Source URL',
            'news_what_happened' => 'What happened?',
            'news_where_happened' => 'Where did it happen?',
            'news_when_happened' => 'When did it happen?',
            'news_who_involved' => 'Who was involved?',
            'news_why_important' => 'Why is it important?',
            'news_current_status' => 'Current status',
            'fact_summary' => 'Verified facts / 5W summary',
            'verification_notes' => 'Verification notes',
            'news_related_authority' => 'Related authority',
            'news_people_organizations' => 'People & organizations mentioned',
            'news_priority' => 'News priority',
            'news_impact_level' => 'Impact level',
            'news_affected_group' => 'Affected group',
            'impact_area' => 'Impact / affected area',
            'quote_attribution' => 'Quote / attribution',
            'report_status' => 'Report status',
            'report_type' => 'Report type',
            'issue_priority' => 'Priority',
            'issue_status' => 'Issue status',
            'reported_to' => 'Reported to',
            'issue_reference' => 'Reference / complaint no.',
            'observation_period_from' => 'Observation from',
            'observation_period_to' => 'Observation to',
            'report_author_name' => 'Author name',
            'report_author_type' => 'Author type',
            'organization_type' => 'Organization type',
            'organization_name' => 'Organization name',
            'report_analysis' => 'Analysis',
            'report_conclusion' => 'Conclusion',
            'action_needed' => 'Is action needed?',
            'action_requested_from' => 'Action requested from',
            'suggested_solution' => 'Suggested solution',
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City',
            'location_locality' => 'Locality',
            'article_subtitle' => 'Subtitle / deck',
            'reading_time' => 'Reading time',
            'key_takeaways' => 'Key takeaways',
            'references' => 'References',
            'story_genre' => 'Genre',
            'mood_or_theme' => 'Mood / theme',
            'poem_language' => 'Poem language',
            'poem_form' => 'Poem form',
            'dedication' => 'Dedication',
            'subject_name' => 'Subject name',
            'subject_field' => 'Subject field',
            'time_period' => 'Time period',
            'key_achievements' => 'Key achievements',
            'life_stage' => 'Life stage',
            'timeline_period' => 'Timeline',
            'lessons_learned' => 'Lessons learned',
            'school_name' => 'School name',
            'child_age_range' => 'Age range',
            'grade_level' => 'Grade / class',
            'campaign_topic' => 'Campaign topic',
            'target_audience' => 'Target audience',
            'call_to_action' => 'Call to action',
            'related_resource_url' => 'Related resource URL',
            'business_stage' => 'Business stage',
            'industry' => 'Industry',
            'key_insight' => 'Key insight',
            'education_level' => 'Education level',
            'subject_area' => 'Subject area',
            'learning_outcome' => 'Learning outcome',
            'career_stage' => 'Career stage',
            'wellness_topic' => 'Wellness topic',
            'medical_disclaimer_ack' => 'Medical disclaimer acknowledged',
            'wellness_summary' => 'Wellness summary',
            'focus_area' => 'Focus area',
            'perspective_summary' => 'Perspective',
            'life_experience_area' => 'Life experience area',
            'advice_category' => 'Advice category',
            'experience_summary' => 'Experience summary',
            'youth_topic' => 'Youth topic',
            'age_group' => 'Age group',
            'youth_message' => 'Youth message',
            'job_type' => 'Employment type',
            'experience_required' => 'Experience required',
            'employer_name' => 'Employer',
            'salary_range' => 'Salary range',
            'application_link' => 'Application URL',
            'job_summary' => 'Role summary',
            'opinion_stance' => 'Stance',
            'topic_context' => 'Topic context',
            'supporting_points' => 'Supporting points',
            'destination' => 'Destination',
            'travel_dates' => 'Travel dates',
            'trip_type' => 'Trip type',
            'travel_tips' => 'Travel tips',
            'heritage_type' => 'Heritage type',
            'historical_period' => 'Historical period',
            'cultural_significance' => 'Cultural significance',
            'consultation_fee' => 'Consultation fee',
            'expertise_area' => 'Expertise area',
            'consultation_mode' => 'Consultation mode',
            'availability' => 'Availability',
            'spiritual_tradition' => 'Tradition',
            'practice_type' => 'Practice type',
            'spiritual_guidance' => 'Guidance summary',
            'crop_or_practice' => 'Crop / practice',
            'farming_season' => 'Season',
            'practical_tips' => 'Practical tips',
            'environmental_topic' => 'Environmental topic',
            'environmental_impact' => 'Environmental impact',
            'action_steps' => 'Action steps',
            'tech_domain' => 'Technology domain',
            'innovation_level' => 'Innovation level',
            'tech_summary' => 'Technology summary',
            'scientific_field' => 'Scientific field',
            'research_type' => 'Research type',
            'local_issue_type' => 'Local issue type',
            'affected_area' => 'Affected area',
            'community_impact' => 'Community impact',
            'issue_category' => 'Issue category',
            'issue_urgency' => 'Urgency',
            'proposed_solution' => 'Proposed solution',
            'creative_medium' => 'Creative medium',
            'tools_used' => 'Tools used',
            'creative_inspiration' => 'Inspiration',
            'competition_deadline' => 'Submission deadline',
            'prize_details' => 'Prizes',
            'eligibility' => 'Eligibility',
            'submission_rules' => 'Submission rules',
            'discussion_topic' => 'Discussion topic',
            'discussion_prompt' => 'Discussion prompt',
            'poll_question' => 'Poll question',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function newsContentFields(): array
    {
        return [
            self::textarea('news_what_happened', 'What happened?', 2000, true, 'Describe the core event or development.'),
            self::textarea('news_where_happened', 'Where did it happen?', 1000, true, 'City, district, landmark, or venue.'),
            self::textarea('news_when_happened', 'When did it happen?', 500, true, 'Date, time, or period readers should know.'),
            self::textarea('news_who_involved', 'Who was involved?', 1000, true, 'People, departments, organizations, or groups.'),
            self::textarea('news_why_important', 'Why is it important?', 1000, true, 'Explain the impact or significance for readers.'),
            self::textarea('news_current_status', 'Current status', 1000, true, 'Latest update, ongoing action, or resolution status.'),
        ];
    }

    /**
     * Ordered news content metadata keys for detail views.
     *
     * @return array<string, string>
     */
    public static function newsContentMetaOrder(): array
    {
        return [
            'news_what_happened' => 'What happened?',
            'news_where_happened' => 'Where did it happen?',
            'news_when_happened' => 'When did it happen?',
            'news_who_involved' => 'Who was involved?',
            'news_why_important' => 'Why is it important?',
            'news_current_status' => 'Current status',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function structuredLocationFields(): array
    {
        return [
            self::text('location_country', 'Country', 120, true),
            self::text('location_state', 'State', 120, true),
            self::text('location_district', 'District', 120, true),
            self::text('location_city', 'City', 120, true),
            self::text('location_locality', 'Locality', 120, false),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function legacyFieldsFor(string $contentType): array
    {
        if ($contentType === 'news') {
            return array_merge([
                self::select('news_type', 'News type', CommunityContentTaxonomy::newsTypes(), true),
                self::date('event_date', 'Event date', true),
                self::text('event_time', 'Event time', 40, false, '7:30 PM'),
                self::text('news_subtitle', 'News subtitle / deck', 255, false),
                self::text('news_dateline', 'Dateline / place', 160, true),
                self::datetime('news_date', 'News date', true),
                self::text('reporter_name', 'Reporter / byline', 160, true),
                self::select('news_source_type', 'Source type', CommunityContentTaxonomy::newsSourceTypes(), true),
                self::text('news_source', 'Source', 160, true),
                self::url('source_url', 'Source URL', false),
                self::textarea('verification_notes', 'Verification notes', 2000, true),
                self::text('news_related_authority', 'Related authority', 160, false, 'Municipal Corporation'),
                self::textarea('news_people_organizations', 'People & organizations mentioned', 2000, false),
                self::select('news_priority', 'News priority', CommunityContentTaxonomy::newsPriorities(), false),
                self::select('news_impact_level', 'Impact level', CommunityContentTaxonomy::newsImpactLevels(), true),
                self::select('news_affected_group', 'Affected group', CommunityContentTaxonomy::newsAffectedGroups(), true),
                self::textarea('impact_area', 'Impact / affected area', 1000, false),
                self::textarea('quote_attribution', 'Quote / attribution', 1000, false),
            ], self::newsContentFields());
        }

        return [
            self::select('report_status', 'Report status', CommunityContentTaxonomy::reportStatuses(), true),
            self::select('report_type', 'Report type', CommunityContentTaxonomy::reportTypes(), true),
            self::date('observation_period_from', 'Observation from', false),
            self::date('observation_period_to', 'Observation to', false),
            self::text('report_author_name', 'Author name', 160, false),
            self::select('report_author_type', 'Author type', CommunityContentTaxonomy::reportAuthorTypes(), true),
            self::select('organization_type', 'Organization type', CommunityContentTaxonomy::reportOrganizationTypes(), false),
            self::text('organization_name', 'Organization name', 160, false),
            self::textarea('key_findings', 'Findings', 3000, false, 'Main observations'),
            self::textarea('report_analysis', 'Analysis', 3000, false, 'Interpretation'),
            self::textarea('recommendations', 'Recommendations', 3000, false, 'Suggested solutions'),
            self::textarea('report_conclusion', 'Conclusion', 3000, false, 'Summary'),
            self::select('action_needed', 'Is action needed?', ['Yes', 'No'], false),
            self::select('action_requested_from', 'Action requested from', CommunityContentTaxonomy::reportActionRequestedFrom(), false),
            self::textarea('suggested_solution', 'Suggested solution', 2000, false),
            self::select('issue_priority', 'Priority', ['Low', 'Medium', 'High', 'Urgent'], true),
            self::select('issue_status', 'Issue status', ['Open', 'Under Review', 'Resolved'], false),
            self::text('reported_to', 'Reported to', 160, false),
            self::text('issue_reference', 'Reference / complaint no.', 160, false),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return array{title: string, badge: string, description: string, fields: list<array<string, mixed>>}
     */
    private static function section(string $title, string $badge, string $description, array $fields): array
    {
        return compact('title', 'badge', 'description', 'fields');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function editorialFields(): array
    {
        return [
            self::select('article_type', 'Article type', [
                'Opinion',
                'Experience',
                'Guide',
                'Tutorial',
                'Research',
                'Awareness',
                'News',
                'Story',
                'Interview',
                'Review',
                'Report',
            ], true, 'col-md-6'),
            self::text('article_subtitle', 'Subtitle / deck', 255, false, 'Optional second line below the headline'),
            self::text('reading_time', 'Estimated reading time (minutes)', 10, false, 'e.g. 8'),
            self::textarea('key_takeaways', 'Key takeaways', 2000, false, 'Bullet-style summary of main points'),
            self::textarea('references', 'References / sources', 2000, false, 'Books, papers, links, or citations'),
        ];
    }

    /**
     * @param  list<string>  $genres
     * @return list<array<string, mixed>>
     */
    private static function narrativeFields(string $genreName, array $genres): array
    {
        return [
            self::select($genreName, 'Genre', $genres, true),
            self::text('mood_or_theme', 'Mood / theme', 120, false, 'e.g. Hope, resilience, family'),
            self::text('reading_time', 'Estimated reading time (minutes)', 10, false),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function profileBioFields(): array
    {
        return [
            self::text('subject_name', 'Subject name', 160, true),
            self::text('subject_field', 'Subject field / profession', 120, true, 'e.g. Scientist, Social worker'),
            self::text('time_period', 'Time period covered', 120, true, 'e.g. 1920–1970'),
            self::textarea('key_achievements', 'Key achievements / highlights', 2000, true),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function campaignFields(): array
    {
        return [
            self::text('campaign_topic', 'Campaign topic', 120, true),
            self::text('target_audience', 'Target audience', 120, true),
            self::textarea('call_to_action', 'Call to action', 2000, true, 'What should readers do next?'),
            self::url('related_resource_url', 'Related resource URL', false),
        ];
    }

    /**
     * @param  list<string>  $stages
     * @return list<array<string, mixed>>
     */
    private static function professionalTipFields(string $stageName, array $stages): array
    {
        return [
            self::select($stageName, 'Stage', $stages, true),
            self::text('industry', 'Industry / sector', 120, true),
            self::textarea('key_insight', 'Key insight / takeaway', 2000, true),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function opinionFields(): array
    {
        return [
            self::select('opinion_stance', 'Opinion stance', ['Support', 'Oppose', 'Neutral', 'Mixed'], true),
            self::text('topic_context', 'Topic context', 160, true, 'What issue or policy this addresses'),
            self::textarea('supporting_points', 'Supporting points', 2000, true),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function environmentFields(): array
    {
        return [
            self::text('environmental_topic', 'Environmental topic', 120, true),
            self::textarea('environmental_impact', 'Environmental impact', 2000, true),
            self::textarea('action_steps', 'Recommended action steps', 2000, true),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function techScienceFields(string $domainName, string $summaryName): array
    {
        return [
            self::text($domainName, 'Domain / field', 120, true),
            self::select('innovation_level', 'Content type', ['Concept', 'Product', 'Research', 'Review', 'Discovery'], true),
            self::textarea($summaryName, 'Summary / key findings', 2000, true),
        ];
    }

    /**
     * @param  list<string>  $options
     * @return array<string, mixed>
     */
    private static function text(string $name, string $label, int $max, bool $required, ?string $placeholder = null): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'type' => 'text',
            'max' => $max,
            'required' => $required,
            'placeholder' => $placeholder,
            'col' => 'col-md-6',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function textarea(string $name, string $label, int $max, bool $required, ?string $placeholder = null): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'type' => 'textarea',
            'max' => $max,
            'required' => $required,
            'placeholder' => $placeholder,
            'rows' => 3,
            'col' => 'col-md-6',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function url(string $name, string $label, bool $required): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'type' => 'url',
            'max' => 255,
            'required' => $required,
            'col' => 'col-md-6',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function date(string $name, string $label, bool $required): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'type' => 'date',
            'required' => $required,
            'col' => 'col-md-4',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function datetime(string $name, string $label, bool $required): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'type' => 'datetime-local',
            'required' => $required,
            'col' => 'col-md-4',
        ];
    }

    /**
     * @param  list<string>  $options
     * @return array<string, mixed>
     */
    private static function select(string $name, string $label, array $options, bool $required, string $col = 'col-md-4'): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'type' => 'select',
            'options' => $options,
            'required' => $required,
            'col' => $col,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function checkbox(string $name, string $label): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'type' => 'checkbox',
            'required' => false,
            'col' => 'col-md-6',
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     * @return list<string|object>
     */
    private static function rulesForField(array $field, string $contentType): array
    {
        $required = (bool) ($field['required'] ?? false);
        $rules = [$required ? 'required' : 'nullable'];

        return match ($field['type']) {
            'textarea' => [...$rules, 'string', 'max:'.($field['max'] ?? 2000)],
            'url' => [...$rules, 'url', 'max:255'],
            'date', 'datetime-local' => [...$rules, 'date'],
            'select' => [...$rules, 'string', Rule::in($field['options'] ?? [])],
            'checkbox' => ['nullable', 'boolean'],
            default => [...$rules, 'string', 'max:'.($field['max'] ?? 255)],
        };
    }
}
