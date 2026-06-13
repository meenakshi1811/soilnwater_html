<?php

namespace App\Support;

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
                'report_type', 'issue_priority', 'issue_status', 'reported_to', 'issue_reference',
            ]);
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
            'news_subtitle',
            'news_dateline',
            'news_date',
            'reporter_name',
            'news_source',
            'source_url',
            'fact_summary',
            'verification_notes',
            'impact_area',
            'quote_attribution',
            'report_type',
            'issue_priority',
            'issue_status',
            'reported_to',
            'issue_reference',
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
            'key_findings' => 'Key findings',
            'recommendations' => 'Recommendations',
            'news_subtitle' => 'Subtitle / deck',
            'news_dateline' => 'Dateline',
            'news_date' => 'News date',
            'reporter_name' => 'Reporter / byline',
            'news_source' => 'Primary source',
            'source_url' => 'Source URL',
            'fact_summary' => 'Verified facts / 5W summary',
            'verification_notes' => 'Verification notes',
            'impact_area' => 'Impact / affected area',
            'quote_attribution' => 'Quote / attribution',
            'report_type' => 'Report type',
            'issue_priority' => 'Priority',
            'issue_status' => 'Status',
            'reported_to' => 'Reported to',
            'issue_reference' => 'Reference / complaint no.',
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
    private static function legacyFieldsFor(string $contentType): array
    {
        if ($contentType === 'news') {
            return [
                self::text('news_subtitle', 'News subtitle / deck', 255, false),
                self::text('news_dateline', 'Dateline / place', 160, true),
                self::datetime('news_date', 'News date', true),
                self::text('reporter_name', 'Reporter / byline', 160, true),
                self::text('news_source', 'Primary source', 160, true),
                self::url('source_url', 'Source URL', false),
                self::textarea('fact_summary', 'Verified facts / 5W summary', 2000, true),
                self::textarea('verification_notes', 'Verification notes', 2000, true),
                self::textarea('impact_area', 'Impact / affected area', 1000, false),
                self::textarea('quote_attribution', 'Quote / attribution', 1000, false),
            ];
        }

        return [
            self::select('report_type', 'Report type', CommunityContentTaxonomy::myAreaReportTypes(), true),
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
    private static function select(string $name, string $label, array $options, bool $required): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'type' => 'select',
            'options' => $options,
            'required' => $required,
            'col' => 'col-md-4',
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
