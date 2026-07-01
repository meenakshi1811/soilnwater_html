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
            'biography' => self::section('Biography details', 'Biography', 'Use the dedicated life-story flow fields on the form.', []),
            'autobiography' => self::section('Autobiography details', 'Autobiography', 'Use the dedicated autobiography flow fields on the form.', []),
            'childrens-corner' => self::section("Children's Corner details", "Children's Corner", 'Use the dedicated Children\'s Corner flow fields on the form.', []),
            'awareness' => self::section('Awareness details', 'Awareness', 'Use the dedicated awareness flow fields on the form.', []),
            'business' => self::section('Business details', 'Business', 'Use the dedicated business flow fields on the form.', []),
            'student-corner' => self::section('Student Corner details', 'Student Corner', 'Use the dedicated Student Corner flow fields on the form.', []),
            'career' => self::section('Career details', 'Career', 'Career guidance fields used on professional development platforms.', self::professionalTipFields('career_stage', [
                'Entry Level', 'Mid Career', 'Senior Level', 'Career Change', 'Student', 'Other',
            ])),
            'health-wellness' => self::section('Health & wellness details', 'Health & Wellness', 'Wellness content with audience and disclaimer context.', [
                self::text('wellness_topic', 'Wellness topic', 120, true, 'e.g. Nutrition, Mental wellness'),
                self::text('target_audience', 'Target audience', 120, true, 'Who this content is for'),
                self::checkbox('medical_disclaimer_ack', 'I confirm this is general wellness information, not medical advice'),
                self::textarea('wellness_summary', 'Key wellness takeaway', 2000, true),
            ]),
            'womens-world' => self::section("Women's World details", "Women's World", 'Use the dedicated Women\'s World flow fields on the form.', []),
            'senior-citizens-forum' => self::section('Senior Citizens Forum details', 'Senior Citizens Forum', 'Use the dedicated Senior Citizens Forum flow fields on the form.', []),
            'youth-corner' => self::section('Youth Corner details', 'Youth Corner', 'Use the dedicated Youth Corner flow fields on the form.', []),
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
            'astro-consultancy' => self::section('Astro Consultancy details', 'Astro Consultancy', 'Use the dedicated Astro Consultancy flow fields on the form.', []),
            'religion-spirituality' => self::section('Religion & Spirituality details', 'Religion & Spirituality', 'Use the dedicated Religion & Spirituality flow fields on the form.', []),
            'agriculture' => self::section('Agriculture details', 'Agriculture', 'Use the dedicated Agriculture flow fields on the form.', []),
            'environment' => self::section('Environment details', 'Environment', 'Use the dedicated Environment flow fields on the form.', []),
            'science-technology' => self::section('Science & Technology details', 'Science & Technology', 'Use the dedicated Science & Technology flow fields on the form.', []),
            'local-voices' => self::section('Local voice details', 'Local Voices', 'Use the dedicated Local Voices flow fields on the form.', []),
            'community-issues' => self::section('Community issue details', 'Community Issues', 'Use the dedicated Community Issues flow fields on the form.', []),
            'creative-corner' => self::section('Creative work details', 'Creative Corner', 'Use the dedicated Creative Corner flow fields on the form.', []),
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
        if (in_array($contentType, ['news', 'reports', 'stories', 'poetry'], true)) {
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

        if (CommunityPost::mountsStructuredLocationFields($contentType)) {
            foreach (self::structuredLocationFields($contentType) as $field) {
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

        if (CommunityPost::mountsStructuredLocationFields($contentType)) {
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

        if ($contentType === 'stories') {
            $payload['story_target_audience'] = array_values(array_intersect(
                (array) $request->input('story_target_audience', []),
                CommunityContentTaxonomy::storyTargetAudiences()
            ));
            $payload['story_themes'] = array_values(array_intersect(
                (array) $request->input('story_themes', []),
                CommunityContentTaxonomy::storyThemes()
            ));
        }

        if ($contentType === 'poetry' && $request->has('sub_category')) {
            $payload['sub_category'] = $request->input('sub_category');
        }

        if ($contentType === 'poetry') {
            $allowedKeys = array_merge($allowedKeys, CommunityPost::structuredLocationMetaKeys(), [
                'poetry_inspiration',
                'poetry_part_of_series',
                'poetry_series_name',
                'poetry_series_part',
            ]);

            $payload['poetry_themes'] = array_values(array_intersect(
                (array) $request->input('poetry_themes', []),
                CommunityContentTaxonomy::poetryThemes()
            ));
            $payload['poetry_target_audience'] = array_values(array_intersect(
                (array) $request->input('poetry_target_audience', []),
                CommunityContentTaxonomy::poetryTargetAudiences()
            ));
            $payload['poetry_inspiration'] = $request->input('poetry_inspiration');
            $payload['poetry_part_of_series'] = $request->input('poetry_part_of_series');

            if ($request->input('poetry_part_of_series') === 'Yes') {
                $payload['poetry_series_name'] = $request->input('poetry_series_name');
                $payload['poetry_series_part'] = $request->input('poetry_series_part');
            } else {
                unset($payload['poetry_series_name'], $payload['poetry_series_part']);
            }

            foreach (CommunityPost::structuredLocationMetaKeys() as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
        }

        if (CommunityPost::usesAutobiographyFlow($contentType)) {
            $payload['autobiography_type'] = $request->input('autobiography_type');
            $payload['birth_place'] = $request->input('birth_place');
            $payload['current_location'] = $request->input('current_location');
            $payload['places_mentioned'] = collect((array) $request->input('places_mentioned', []))
                ->map(fn (mixed $place): string => trim((string) $place))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $payload['key_lessons_learned'] = collect((array) $request->input('key_lessons_learned', []))
                ->map(fn (mixed $lesson): string => trim((string) $lesson))
                ->filter()
                ->values()
                ->all();
            $payload['related_people'] = collect((array) $request->input('related_people', []))
                ->filter(fn (mixed $person): bool => is_array($person) && filled($person['name'] ?? null))
                ->map(fn (array $person): array => [
                    'name' => trim((string) ($person['name'] ?? '')),
                    'relationship' => trim((string) ($person['relationship'] ?? '')),
                ])
                ->values()
                ->all();
        }

        if (CommunityPost::usesAwarenessFlow($contentType)) {
            $payload['awareness_category'] = $request->input('awareness_category');
            $payload['awareness_type'] = $request->input('awareness_type');
            $payload['awareness_level'] = $request->input('awareness_level');
            $payload['awareness_target_audience'] = array_values(array_intersect(
                (array) $request->input('awareness_target_audience', []),
                CommunityContentTaxonomy::awarenessTargetAudiences()
            ));
            $payload['awareness_posted_by'] = $request->input('awareness_posted_by');
            $payload['awareness_organization_name'] = $request->input('awareness_organization_name');
            $payload['awareness_campaign_start_date'] = $request->input('awareness_campaign_start_date');
            $payload['awareness_campaign_end_date'] = $request->input('awareness_campaign_end_date');
            $payload['awareness_video_type'] = $request->input('awareness_video_type');
            $payload['awareness_call_to_action'] = $request->input('awareness_call_to_action');
            $payload['awareness_action_items'] = array_values(array_intersect(
                (array) $request->input('awareness_action_items', []),
                CommunityContentTaxonomy::awarenessCallToActionExamples()
            ));
            $payload['awareness_allow_campaign_join'] = $request->boolean('awareness_allow_campaign_join');
            $payload['awareness_has_event'] = $request->boolean('awareness_has_event');
            $payload['awareness_event_type'] = $request->input('awareness_event_type');
            $payload['awareness_event_date'] = $request->input('awareness_event_date');
            $payload['awareness_event_venue'] = $request->input('awareness_event_venue');
            $payload['awareness_event_time'] = $request->input('awareness_event_time');
            $payload['awareness_event_organizer'] = $request->input('awareness_event_organizer');
            $payload['awareness_social_impact_categories'] = array_values(array_intersect(
                (array) $request->input('awareness_social_impact_categories', []),
                CommunityContentTaxonomy::awarenessSocialImpactCategories()
            ));
            $payload['awareness_allow_cause_support'] = $request->boolean('awareness_allow_cause_support', true);
            $payload['awareness_allow_pledges'] = $request->boolean('awareness_allow_pledges');
            $payload['awareness_pledge_options'] = collect(preg_split('/\R/', (string) $request->input('awareness_pledge_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            $payload['awareness_poll_question'] = $request->input('awareness_poll_question');
            $payload['awareness_impact_trees_planted'] = $request->input('awareness_impact_trees_planted');
            $payload['awareness_impact_volunteers_joined'] = $request->input('awareness_impact_volunteers_joined');
            $payload['awareness_impact_people_reached'] = $request->input('awareness_impact_people_reached');
        }

        if (CommunityPost::usesBusinessFlow($contentType)) {
            $payload['business_category'] = $request->input('business_category');
            $payload['business_content_type'] = $request->input('business_content_type');
            $payload['business_stage'] = $request->input('business_stage');
            $payload['business_target_audience'] = array_values(array_intersect(
                (array) $request->input('business_target_audience', []),
                CommunityContentTaxonomy::businessTargetAudiences()
            ));
            $payload['business_challenges'] = array_values(array_intersect(
                (array) $request->input('business_challenges', []),
                CommunityContentTaxonomy::businessChallenges()
            ));
            $payload['business_opportunity_type'] = $request->input('business_opportunity_type');
            $payload['business_market_segments'] = array_values(array_intersect(
                (array) $request->input('business_market_segments', []),
                CommunityContentTaxonomy::businessMarketSegments()
            ));
            $payload['business_themes'] = array_values(array_intersect(
                (array) $request->input('business_themes', []),
                CommunityContentTaxonomy::businessThemes()
            ));
            $payload['business_name'] = $request->input('business_name');
            $payload['business_author_designation'] = $request->input('business_author_designation');
            $payload['business_profile_type'] = $request->input('business_profile_type');
            $payload['business_industry'] = $request->input('business_industry');
            $payload['business_video_type'] = $request->input('business_video_type');
            $payload['business_ask_community'] = $request->input('business_ask_community');
            $payload['business_useful_links'] = $request->input('business_useful_links');
            $payload['business_government_schemes'] = $request->input('business_government_schemes');
            $payload['business_training_programs'] = $request->input('business_training_programs');
            $payload['business_industry_resources'] = $request->input('business_industry_resources');
            $payload['business_contact_options'] = array_values(array_intersect(
                (array) $request->input('business_contact_options', []),
                CommunityContentTaxonomy::businessContactOptions()
            ));
            $payload['business_poll_question'] = $request->input('business_poll_question');
            $payload['business_poll_options'] = collect(preg_split('/\R/', (string) $request->input('business_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();

            if (! $request->boolean('allow_poll')) {
                unset($payload['business_poll_question'], $payload['business_poll_options']);
            }
        }

        if (CommunityPost::usesWomensWorldFlow($contentType)) {
            $payload['womens_world_category'] = $request->input('womens_world_category');
            $payload['womens_world_content_type'] = $request->input('womens_world_content_type');
            $payload['womens_world_target_audience'] = array_values(array_intersect(
                (array) $request->input('womens_world_target_audience', []),
                CommunityContentTaxonomy::womensWorldTargetAudiences()
            ));
            $payload['womens_world_featured_topics'] = array_values(array_intersect(
                (array) $request->input('womens_world_featured_topics', []),
                CommunityContentTaxonomy::womensWorldFeaturedTopics()
            ));
            $payload['womens_world_life_stage'] = $request->input('womens_world_life_stage');
            $payload['womens_world_themes'] = array_values(array_intersect(
                (array) $request->input('womens_world_themes', []),
                CommunityContentTaxonomy::womensWorldThemes()
            ));
            $payload['womens_world_video_type'] = $request->input('womens_world_video_type');
            $payload['womens_world_business_name'] = $request->input('womens_world_business_name');
            $payload['womens_world_business_category'] = $request->input('womens_world_business_category');
            $payload['womens_world_website_url'] = $request->input('womens_world_website_url');
            $payload['womens_world_vendor_profile_url'] = $request->input('womens_world_vendor_profile_url');
            $payload['womens_world_ask_community'] = $request->input('womens_world_ask_community');
            $payload['womens_world_poll_question'] = $request->input('womens_world_poll_question');
            $payload['womens_world_poll_options'] = collect(preg_split('/\R/', (string) $request->input('womens_world_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();

            if (! $request->boolean('allow_poll')) {
                unset($payload['womens_world_poll_question'], $payload['womens_world_poll_options']);
            }

            $payload['womens_world_support_requests'] = array_values(array_intersect(
                (array) $request->input('womens_world_support_requests', []),
                CommunityContentTaxonomy::womensWorldSupportRequests()
            ));
            $payload['womens_world_community_groups'] = array_values(array_intersect(
                (array) $request->input('womens_world_community_groups', []),
                CommunityContentTaxonomy::womensWorldCommunityGroups()
            ));
            $payload['womens_world_visibility'] = array_key_exists(
                (string) $request->input('womens_world_visibility'),
                CommunityContentTaxonomy::womensWorldVisibilitySettings()
            )
                ? (string) $request->input('womens_world_visibility')
                : CommunityContentTaxonomy::womensWorldDefaultVisibilitySetting();
            $payload['womens_world_useful_websites'] = $request->input('womens_world_useful_websites');
            $payload['womens_world_government_schemes'] = $request->input('womens_world_government_schemes');
            $payload['womens_world_training_programs'] = $request->input('womens_world_training_programs');
            $payload['womens_world_scholarships'] = $request->input('womens_world_scholarships');
            $payload['womens_world_support_organizations'] = $request->input('womens_world_support_organizations');

            foreach (['location_country', 'location_state', 'location_district', 'location_city'] as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
            unset($payload['location_locality']);
        }

        if (CommunityPost::usesChildrensCornerFlow($contentType)) {
            $payload['child_share_type'] = $request->input('child_share_type');
            $payload['child_first_name'] = $request->input('child_first_name');
            $payload['child_age_group'] = $request->input('child_age_group');
            $payload['child_grade_level'] = $request->input('child_grade_level');
            $payload['child_school_name'] = $request->input('child_school_name');
            $payload['parent_name'] = $request->input('parent_name');
            $payload['parent_mobile'] = $request->input('parent_mobile');
            $payload['parent_email'] = $request->input('parent_email');
            $payload['parent_relationship'] = $request->input('parent_relationship');
            $payload['child_parent_consent_identity'] = $request->boolean('child_parent_consent_identity');
            $payload['child_parent_consent_publication'] = $request->boolean('child_parent_consent_publication');
            $payload['child_parent_consent_original'] = $request->boolean('child_parent_consent_original');
            $payload['parent_approved'] = $request->boolean('child_parent_consent_identity')
                && $request->boolean('child_parent_consent_publication')
                && $request->boolean('child_parent_consent_original');
            $payload['childrens_corner_submitted_through'] = $request->input('childrens_corner_submitted_through');
            $payload['childrens_corner_school_competition_entry'] = $request->input('childrens_corner_school_competition_entry');
            $payload['childrens_corner_city'] = $request->input('childrens_corner_city');
            $payload['childrens_corner_district'] = $request->input('childrens_corner_district');
            $payload['childrens_corner_state'] = $request->input('childrens_corner_state');
            $payload['childrens_corner_talent_categories'] = array_values(array_intersect(
                (array) $request->input('childrens_corner_talent_categories', []),
                CommunityContentTaxonomy::childrensCornerTalentCategories()
            ));
            $payload['childrens_corner_achievement'] = $request->input('childrens_corner_achievement');
            $payload['childrens_corner_comments_moderated'] = $request->boolean('childrens_corner_comments_moderated', true);
            $payload['childrens_corner_child_friendly_reactions'] = true;
            $payload['childrens_corner_privacy_setting'] = array_key_exists(
                (string) $request->input('childrens_corner_privacy_setting'),
                CommunityContentTaxonomy::childrensCornerPrivacySettings()
            )
                ? (string) $request->input('childrens_corner_privacy_setting')
                : CommunityContentTaxonomy::childrensCornerDefaultPrivacySetting();
            foreach (array_keys(CommunityContentTaxonomy::childrensCornerSafetyDeclarations()) as $safetyKey) {
                $payload[$safetyKey] = $request->boolean($safetyKey);
            }
            $payload['childrens_corner_safety_confirmed'] = collect(array_keys(CommunityContentTaxonomy::childrensCornerSafetyDeclarations()))
                ->every(fn (string $key): bool => $request->boolean($key));
            $payload['childrens_corner_project_description'] = $request->input('childrens_corner_project_description');
            $payload['childrens_corner_themes'] = array_values(array_intersect(
                (array) $request->input('childrens_corner_themes', []),
                CommunityContentTaxonomy::childrensCornerThemes()
            ));
        }

        if (CommunityPost::usesSeniorCitizensForumFlow($contentType)) {
            $payload['senior_citizens_forum_category'] = $request->input('senior_citizens_forum_category');
            $payload['senior_citizens_forum_content_type'] = $request->input('senior_citizens_forum_content_type');
            $payload['senior_citizens_forum_age_group'] = $request->input('senior_citizens_forum_age_group');
            $payload['senior_citizens_forum_life_journey_categories'] = array_values(array_intersect(
                (array) $request->input('senior_citizens_forum_life_journey_categories', []),
                CommunityContentTaxonomy::seniorCitizensForumLifeJourneyCategories()
            ));
            $payload['senior_citizens_forum_key_lessons'] = collect((array) $request->input('senior_citizens_forum_key_lessons', []))
                ->map(fn (mixed $lesson): string => trim((string) $lesson))
                ->filter()
                ->values()
                ->all();
            $payload['senior_citizens_forum_themes'] = array_values(array_intersect(
                (array) $request->input('senior_citizens_forum_themes', []),
                CommunityContentTaxonomy::seniorCitizensForumThemes()
            ));
            $payload['senior_citizens_forum_advice_to_youth'] = $request->input('senior_citizens_forum_advice_to_youth');
            $payload['senior_citizens_forum_community_contributions'] = array_values(array_intersect(
                (array) $request->input('senior_citizens_forum_community_contributions', []),
                CommunityContentTaxonomy::seniorCitizensForumCommunityContributions()
            ));
            $payload['senior_citizens_forum_ask_community'] = $request->input('senior_citizens_forum_ask_community');
            $payload['senior_citizens_forum_visibility'] = array_key_exists(
                (string) $request->input('senior_citizens_forum_visibility'),
                CommunityContentTaxonomy::seniorCitizensForumVisibilitySettings()
            )
                ? (string) $request->input('senior_citizens_forum_visibility')
                : CommunityContentTaxonomy::seniorCitizensForumDefaultVisibilitySetting();
            $payload['senior_citizens_forum_intergenerational_connections'] = array_values(array_intersect(
                (array) $request->input('senior_citizens_forum_intergenerational_connections', []),
                CommunityContentTaxonomy::seniorCitizensForumIntergenerationalConnections()
            ));
            $payload['senior_citizens_forum_preserve_digital_legacy'] = $request->boolean('senior_citizens_forum_preserve_digital_legacy');

            foreach (['location_country', 'location_state', 'location_district', 'location_city'] as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
            unset($payload['location_locality']);

            $payload['senior_citizens_forum_video_type'] = $request->input('senior_citizens_forum_video_type');
            foreach (array_keys(CommunityContentTaxonomy::seniorCitizensForumFamilyHeritageFields()) as $heritageKey) {
                $payload[$heritageKey] = $request->input($heritageKey);
            }
        }

        if (CommunityPost::usesStudentCornerFlow($contentType)) {
            $payload['student_corner_category'] = $request->input('student_corner_category');
            $payload['student_corner_content_type'] = $request->input('student_corner_content_type');
            $payload['student_corner_profile_name'] = $request->input('student_corner_profile_name');
            $payload['student_corner_class_course'] = $request->input('student_corner_class_course');
            $payload['student_corner_stream'] = $request->input('student_corner_stream');
            $payload['student_corner_institution_name'] = $request->input('student_corner_institution_name');
            $payload['student_corner_target_audience'] = array_values(array_intersect(
                (array) $request->input('student_corner_target_audience', []),
                CommunityContentTaxonomy::studentCornerTargetAudiences()
            ));
            $payload['student_corner_video_type'] = $request->input('student_corner_video_type');
            $payload['student_corner_study_material_types'] = array_values(array_intersect(
                (array) $request->input('student_corner_study_material_types', []),
                CommunityContentTaxonomy::studentCornerStudyMaterialTypes()
            ));
            $payload['student_corner_career_guidance_topics'] = array_values(array_intersect(
                (array) $request->input('student_corner_career_guidance_topics', []),
                CommunityContentTaxonomy::studentCornerCareerGuidanceTopics()
            ));
            $payload['student_corner_scholarship_name'] = $request->input('student_corner_scholarship_name');
            $payload['student_corner_eligibility'] = $request->input('student_corner_eligibility');
            $payload['student_corner_application_deadline'] = $request->input('student_corner_application_deadline');
            $payload['student_corner_official_website'] = $request->input('student_corner_official_website');
            $payload['student_corner_exam_name'] = $request->input('student_corner_exam_name');
            $payload['student_corner_preparation_strategy'] = $request->input('student_corner_preparation_strategy');
            $payload['student_corner_resources_used'] = $request->input('student_corner_resources_used');
            $payload['student_corner_marks_rank'] = $request->input('student_corner_marks_rank');
            $payload['student_corner_lessons_learned'] = $request->input('student_corner_lessons_learned');
            $payload['student_corner_skills'] = array_values(array_intersect(
                (array) $request->input('student_corner_skills', []),
                CommunityContentTaxonomy::studentCornerSkills()
            ));
            $payload['student_corner_social_impact_categories'] = array_values(array_intersect(
                (array) $request->input('student_corner_social_impact_categories', []),
                CommunityContentTaxonomy::studentCornerSocialImpactCategories()
            ));
            $payload['student_corner_ask_community'] = $request->input('student_corner_ask_community');
            $payload['student_corner_poll_question'] = $request->input('student_corner_poll_question');
            $payload['student_corner_poll_options'] = collect(preg_split('/\R/', (string) $request->input('student_corner_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('allow_poll')) {
                unset($payload['student_corner_poll_question'], $payload['student_corner_poll_options']);
            }
            $payload['student_corner_mentorship_requests'] = array_values(array_intersect(
                (array) $request->input('student_corner_mentorship_requests', []),
                CommunityContentTaxonomy::studentCornerMentorshipRequests()
            ));
            $payload['student_corner_submit_to_competition'] = $request->boolean('student_corner_submit_to_competition');
            $payload['student_corner_competition_categories'] = array_values(array_intersect(
                (array) $request->input('student_corner_competition_categories', []),
                CommunityContentTaxonomy::studentCornerCompetitionCategories()
            ));
            $payload['student_corner_visibility'] = array_key_exists(
                (string) $request->input('student_corner_visibility'),
                CommunityContentTaxonomy::studentCornerVisibilitySettings()
            )
                ? (string) $request->input('student_corner_visibility')
                : CommunityContentTaxonomy::studentCornerDefaultVisibilitySetting();

            foreach (['location_country', 'location_state', 'location_district', 'location_city'] as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
            unset($payload['location_locality']);

            if ($request->input('student_corner_content_type') === CommunityContentTaxonomy::studentCornerProjectContentType()) {
                $payload['student_corner_project_title'] = $request->input('student_corner_project_title');
                $payload['student_corner_project_category'] = $request->input('student_corner_project_category');
                $payload['student_corner_project_description'] = $request->input('student_corner_project_description');
                $payload['student_corner_project_outcome'] = $request->input('student_corner_project_outcome');
            }
        }

        if (CommunityPost::usesLocalVoicesFlow($contentType)) {
            $payload['local_voice_type'] = $request->input('local_voice_type');
            $payload['local_voice_category'] = $request->input('local_voice_category');
            $payload['local_voice_issue_type'] = $request->input('local_voice_issue_type');
            $payload['local_voice_affected_communities'] = array_values(array_intersect(
                (array) $request->input('local_voice_affected_communities', []),
                CommunityContentTaxonomy::localVoiceAffectedCommunities()
            ));
            $payload['local_voice_impact_level'] = $request->input('local_voice_impact_level');
            $payload['local_voice_video_type'] = $request->input('local_voice_video_type');
            $payload['local_voice_suggested_solution'] = $request->input('local_voice_suggested_solution');
            $payload['local_voice_estimated_benefit'] = $request->input('local_voice_estimated_benefit');
            $payload['local_voice_authorities'] = array_values(array_intersect(
                (array) $request->input('local_voice_authorities', []),
                CommunityContentTaxonomy::localVoiceAuthorities()
            ));
            $payload['local_voice_call_for_action'] = array_values(array_intersect(
                (array) $request->input('local_voice_call_for_action', []),
                CommunityContentTaxonomy::localVoiceCallForActionExamples()
            ));
            $payload['local_voice_status_tracker'] = $request->input('local_voice_status_tracker');
            $payload['local_voice_poll_question'] = $request->input('local_voice_poll_question');
            $payload['local_voice_poll_options'] = collect(preg_split('/\R/', (string) $request->input('local_voice_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('allow_poll')) {
                unset($payload['local_voice_poll_question'], $payload['local_voice_poll_options']);
            }
            $payload['local_voice_allow_support'] = $request->boolean('local_voice_allow_support');
            $payload['local_voice_allow_follow'] = $request->boolean('local_voice_allow_follow');
            $payload['local_voice_hero_name'] = $request->input('local_voice_hero_name');
            $payload['local_voice_hero_location'] = $request->input('local_voice_hero_location');
            $payload['local_voice_hero_contribution'] = $request->input('local_voice_hero_contribution');
            $payload['local_voice_hero_achievements'] = $request->input('local_voice_hero_achievements');
            $payload['local_voice_initiatives'] = array_values(array_intersect(
                (array) $request->input('local_voice_initiatives', []),
                CommunityContentTaxonomy::localVoiceInitiativeExamples()
            ));
            $payload['local_voice_event_date'] = $request->input('local_voice_event_date');
            $payload['local_voice_event_time'] = $request->input('local_voice_event_time');
            $payload['local_voice_event_venue'] = $request->input('local_voice_event_venue');
            $payload['local_voice_event_organizer'] = $request->input('local_voice_event_organizer');
            $payload['local_voice_visibility'] = array_key_exists(
                (string) $request->input('local_voice_visibility'),
                CommunityContentTaxonomy::localVoiceVisibilitySettings()
            )
                ? (string) $request->input('local_voice_visibility')
                : CommunityContentTaxonomy::localVoiceDefaultVisibilitySetting();

            foreach (CommunityPost::structuredLocationMetaKeys() as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
        }

        if (CommunityPost::usesMyAreaFlow($contentType)) {
            $payload['my_area_activity_type'] = $request->input('my_area_activity_type');
            $payload['my_area_topic_category'] = $request->input('my_area_topic_category');
            $payload['my_area_impact_level'] = $request->input('my_area_impact_level');
            $payload['my_area_affected_communities'] = array_values(array_intersect(
                (array) $request->input('my_area_affected_communities', []),
                CommunityContentTaxonomy::myAreaAffectedCommunities()
            ));
            $payload['my_area_status_tracker'] = $request->input('my_area_status_tracker');
            $payload['my_area_authorities'] = array_values(array_intersect(
                (array) $request->input('my_area_authorities', []),
                CommunityContentTaxonomy::myAreaAuthorities()
            ));
            $payload['my_area_suggested_solution'] = $request->input('my_area_suggested_solution');
            $payload['my_area_hero_name'] = $request->input('my_area_hero_name');
            $payload['my_area_hero_location'] = $request->input('my_area_hero_location');
            $payload['my_area_hero_contribution'] = $request->input('my_area_hero_contribution');
            $payload['my_area_achievement_title'] = $request->input('my_area_achievement_title');
            $payload['my_area_achievement_description'] = $request->input('my_area_achievement_description');
            $payload['my_area_poll_question'] = $request->input('my_area_poll_question');
            $payload['my_area_poll_options'] = collect(preg_split('/\R/', (string) $request->input('my_area_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('allow_poll')) {
                unset($payload['my_area_poll_question'], $payload['my_area_poll_options']);
            }
            $payload['my_area_visibility'] = array_key_exists(
                (string) $request->input('my_area_visibility'),
                CommunityContentTaxonomy::myAreaVisibilitySettings()
            )
                ? (string) $request->input('my_area_visibility')
                : CommunityContentTaxonomy::myAreaDefaultVisibilitySetting();

            foreach (CommunityPost::structuredLocationMetaKeys() as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
        }

        if (CommunityPost::usesCommunityIssuesFlow($contentType)) {
            $payload['community_issue_category'] = $request->input('community_issue_category');
            $payload['community_issue_type'] = $request->input('community_issue_type');
            $payload['community_issue_severity'] = $request->input('community_issue_severity');
            $payload['community_issue_affected_population'] = $request->input('community_issue_affected_population');
            $payload['community_issue_affected_groups'] = array_values(array_intersect(
                (array) $request->input('community_issue_affected_groups', []),
                CommunityContentTaxonomy::communityIssueAffectedGroups()
            ));
            $payload['location_landmark'] = $request->input('location_landmark');
            $payload['community_issue_first_noticed_on'] = $request->input('community_issue_first_noticed_on');
            $payload['community_issue_is_recurring'] = $request->input('community_issue_is_recurring');
            $payload['community_issue_frequency'] = $request->input('community_issue_frequency');
            $payload['community_issue_authority'] = $request->input('community_issue_authority');
            $payload['community_issue_already_reported'] = $request->input('community_issue_already_reported');
            $payload['community_issue_complaint_number'] = $request->input('community_issue_complaint_number');
            $payload['community_issue_complaint_date'] = $request->input('community_issue_complaint_date');
            $payload['community_issue_department_contacted'] = $request->input('community_issue_department_contacted');
            $payload['community_issue_suggested_solution'] = $request->input('community_issue_suggested_solution');
            $payload['community_issue_support_requests'] = array_values(array_intersect(
                (array) $request->input('community_issue_support_requests', []),
                CommunityContentTaxonomy::communityIssueSupportRequests()
            ));
            $payload['community_issue_status_tracker'] = $request->input('community_issue_status_tracker');
            $payload['community_issue_resolution_timeline'] = $request->input('community_issue_resolution_timeline');
            $payload['community_issue_allow_campaign'] = $request->boolean('community_issue_allow_campaign');
            $payload['community_issue_allow_support'] = $request->boolean('community_issue_allow_support');
            $payload['community_issue_allow_follow'] = $request->boolean('community_issue_allow_follow');
            $payload['community_issue_allow_verification'] = $request->boolean('community_issue_allow_verification');
            $payload['community_issue_escalation_threshold'] = $request->input('community_issue_escalation_threshold');
            $payload['community_issue_poll_question'] = $request->input('community_issue_poll_question');
            $payload['community_issue_poll_options'] = collect(preg_split('/\R/', (string) $request->input('community_issue_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('allow_poll')) {
                unset($payload['community_issue_poll_question'], $payload['community_issue_poll_options']);
            }
            $payload['community_issue_visibility'] = array_key_exists(
                (string) $request->input('community_issue_visibility'),
                CommunityContentTaxonomy::communityIssueVisibilitySettings()
            )
                ? (string) $request->input('community_issue_visibility')
                : CommunityContentTaxonomy::communityIssueDefaultVisibilitySetting();

            foreach (CommunityPost::structuredLocationMetaKeys() as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
        }

        if (CommunityPost::usesAgricultureFlow($contentType)) {
            $payload['agriculture_share_type'] = $request->input('agriculture_share_type');
            $payload['agriculture_category'] = $request->input('agriculture_category');
            $payload['agriculture_crop_name'] = $request->input('agriculture_crop_name');
            $payload['agriculture_crop_variety'] = $request->input('agriculture_crop_variety');
            $payload['agriculture_sowing_date'] = $request->input('agriculture_sowing_date');
            $payload['agriculture_harvest_date'] = $request->input('agriculture_harvest_date');
            $payload['agriculture_growing_season'] = $request->input('agriculture_growing_season');
            $payload['agriculture_climate_zone'] = $request->input('agriculture_climate_zone');
            $payload['agriculture_soil_type'] = $request->input('agriculture_soil_type');
            $payload['agriculture_farm_size'] = $request->input('agriculture_farm_size');
            $payload['agriculture_farming_type'] = $request->input('agriculture_farming_type');
            $payload['agriculture_irrigation_method'] = $request->input('agriculture_irrigation_method');
            $payload['agriculture_water_source'] = $request->input('agriculture_water_source');
            $payload['agriculture_water_conservation_practices'] = array_values(array_intersect(
                (array) $request->input('agriculture_water_conservation_practices', []),
                CommunityContentTaxonomy::agricultureWaterConservationPractices()
            ));
            $payload['agriculture_soil_test_conducted'] = $request->input('agriculture_soil_test_conducted');
            $payload['agriculture_soil_ph'] = $request->input('agriculture_soil_ph');
            $payload['agriculture_soil_organic_carbon'] = $request->input('agriculture_soil_organic_carbon');
            $payload['agriculture_soil_nitrogen'] = $request->input('agriculture_soil_nitrogen');
            $payload['agriculture_soil_phosphorus'] = $request->input('agriculture_soil_phosphorus');
            $payload['agriculture_soil_potassium'] = $request->input('agriculture_soil_potassium');
            $payload['agriculture_soil_recommendations'] = $request->input('agriculture_soil_recommendations');
            $payload['agriculture_problem_type'] = $request->input('agriculture_problem_type');
            $payload['agriculture_expert_assistance'] = $request->input('agriculture_expert_assistance');
            $payload['agriculture_equipment_name'] = $request->input('agriculture_equipment_name');
            $payload['agriculture_equipment_manufacturer'] = $request->input('agriculture_equipment_manufacturer');
            $payload['agriculture_equipment_experience'] = $request->input('agriculture_equipment_experience');
            $payload['agriculture_equipment_cost'] = $request->input('agriculture_equipment_cost');
            $payload['agriculture_equipment_benefits'] = $request->input('agriculture_equipment_benefits');
            $payload['agriculture_scheme_name'] = $request->input('agriculture_scheme_name');
            $payload['agriculture_scheme_department'] = $request->input('agriculture_scheme_department');
            $payload['agriculture_scheme_eligibility'] = $request->input('agriculture_scheme_eligibility');
            $payload['agriculture_scheme_subsidy'] = $request->input('agriculture_scheme_subsidy');
            $payload['agriculture_scheme_application_link'] = $request->input('agriculture_scheme_application_link');
            $payload['agriculture_scheme_last_date'] = $request->input('agriculture_scheme_last_date');
            $payload['agriculture_market_commodity'] = $request->input('agriculture_market_commodity');
            $payload['agriculture_market_name'] = $request->input('agriculture_market_name');
            $payload['agriculture_market_price'] = $request->input('agriculture_market_price');
            $payload['agriculture_market_date'] = $request->input('agriculture_market_date');
            $payload['agriculture_market_price_trend'] = $request->input('agriculture_market_price_trend');
            $payload['agriculture_livestock_types'] = array_values(array_intersect(
                (array) $request->input('agriculture_livestock_types', []),
                CommunityContentTaxonomy::agricultureLivestockTypes()
            ));
            $payload['agriculture_innovation_name'] = $request->input('agriculture_innovation_name');
            $payload['agriculture_innovation_description'] = $request->input('agriculture_innovation_description');
            $payload['agriculture_innovation_benefits'] = $request->input('agriculture_innovation_benefits');
            $payload['agriculture_innovation_results'] = $request->input('agriculture_innovation_results');
            $payload['agriculture_agri_business_type'] = $request->input('agriculture_agri_business_type');
            $payload['agriculture_weather_impact'] = $request->input('agriculture_weather_impact');
            $payload['agriculture_video_type'] = $request->input('agriculture_video_type');
            $payload['agriculture_ask_community'] = $request->input('agriculture_ask_community');
            $payload['agriculture_enable_knowledge_exchange'] = $request->boolean('agriculture_enable_knowledge_exchange');
            $payload['agriculture_enable_crop_doctor'] = $request->boolean('agriculture_enable_crop_doctor');
            $payload['agriculture_target_audiences'] = array_values(array_intersect(
                (array) $request->input('agriculture_target_audiences', []),
                CommunityContentTaxonomy::agricultureTargetAudiences()
            ));
            $payload['agriculture_poll_question'] = $request->input('agriculture_poll_question');
            $payload['agriculture_poll_options'] = collect(preg_split('/\R/', (string) $request->input('agriculture_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('allow_poll')) {
                unset($payload['agriculture_poll_question'], $payload['agriculture_poll_options']);
            }

            foreach (CommunityPost::structuredLocationMetaKeys() as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
            unset($payload['location_locality']);
        }

        if (CommunityPost::usesEnvironmentFlow($contentType)) {
            $payload['environment_post_type'] = $request->input('environment_post_type');
            $payload['environment_category'] = $request->input('environment_category');
            $payload['environment_natural_feature_name'] = $request->input('environment_natural_feature_name');
            $payload['environment_map_pin_type'] = $request->input('environment_map_pin_type');
            $payload['environment_issue_type'] = $request->input('environment_issue_type');
            $payload['environment_initiative_type'] = $request->input('environment_initiative_type');
            $payload['environment_water_source'] = $request->input('environment_water_source');
            $payload['environment_conservation_method'] = $request->input('environment_conservation_method');
            $payload['environment_water_saved'] = $request->input('environment_water_saved');
            $payload['environment_soil_conservation_methods'] = array_values(array_intersect(
                (array) $request->input('environment_soil_conservation_methods', []),
                CommunityContentTaxonomy::environmentSoilConservationMethods()
            ));
            $payload['environment_tree_count'] = $request->input('environment_tree_count');
            $payload['environment_tree_species'] = $request->input('environment_tree_species');
            $payload['environment_tree_plantation_date'] = $request->input('environment_tree_plantation_date');
            $payload['environment_tree_organization'] = $request->input('environment_tree_organization');
            $payload['environment_tree_survival_status'] = $request->input('environment_tree_survival_status');
            $payload['environment_tree_maintenance_plan'] = $request->input('environment_tree_maintenance_plan');
            $payload['environment_waste_types'] = array_values(array_intersect(
                (array) $request->input('environment_waste_types', []),
                CommunityContentTaxonomy::environmentWasteTypes()
            ));
            $payload['environment_biodiversity_types'] = array_values(array_intersect(
                (array) $request->input('environment_biodiversity_types', []),
                CommunityContentTaxonomy::environmentBiodiversityTypes()
            ));
            $payload['environment_climate_impacts'] = array_values(array_intersect(
                (array) $request->input('environment_climate_impacts', []),
                CommunityContentTaxonomy::environmentClimateImpacts()
            ));
            $payload['environment_video_type'] = $request->input('environment_video_type');
            $payload['environment_enable_impact_calculator'] = $request->boolean('environment_enable_impact_calculator');
            $payload['environment_data_trees_planted'] = $request->input('environment_data_trees_planted');
            $payload['environment_data_area_covered'] = $request->input('environment_data_area_covered');
            $payload['environment_data_water_saved'] = $request->input('environment_data_water_saved');
            $payload['environment_data_waste_collected'] = $request->input('environment_data_waste_collected');
            $payload['environment_data_people_participated'] = $request->input('environment_data_people_participated');
            $payload['environment_data_carbon_reduction'] = $request->input('environment_data_carbon_reduction');
            $payload['environment_data_species_recorded'] = $request->input('environment_data_species_recorded');
            $payload['environment_participation_requests'] = array_values(array_intersect(
                (array) $request->input('environment_participation_requests', []),
                CommunityContentTaxonomy::environmentParticipationRequests()
            ));
            $payload['environment_event_campaign_name'] = $request->input('environment_event_campaign_name');
            $payload['environment_event_organizer'] = $request->input('environment_event_organizer');
            $payload['environment_event_venue'] = $request->input('environment_event_venue');
            $payload['environment_event_date'] = $request->input('environment_event_date');
            $payload['environment_event_time'] = $request->input('environment_event_time');
            $payload['environment_event_registration_link'] = $request->input('environment_event_registration_link');
            $payload['environment_scheme_name'] = $request->input('environment_scheme_name');
            $payload['environment_scheme_department'] = $request->input('environment_scheme_department');
            $payload['environment_scheme_eligibility'] = $request->input('environment_scheme_eligibility');
            $payload['environment_scheme_benefits'] = $request->input('environment_scheme_benefits');
            $payload['environment_scheme_official_link'] = $request->input('environment_scheme_official_link');
            $payload['environment_ask_community'] = $request->input('environment_ask_community');
            $payload['environment_poll_question'] = $request->input('environment_poll_question');
            $payload['environment_poll_options'] = collect(preg_split('/\R/', (string) $request->input('environment_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('allow_poll')) {
                unset($payload['environment_poll_question'], $payload['environment_poll_options']);
            }
            $payload['environment_show_on_green_map'] = $request->boolean('environment_show_on_green_map');
            $payload['environment_enable_green_leader'] = $request->boolean('environment_enable_green_leader');
            $payload['environment_allow_join_campaign'] = $request->boolean('environment_allow_join_campaign');
            $payload['environment_allow_volunteer'] = $request->boolean('environment_allow_volunteer');
            $payload['environment_allow_donate'] = $request->boolean('environment_allow_donate');
            $payload['environment_allow_support_initiative'] = $request->boolean('environment_allow_support_initiative');
            $payload['environment_allow_follow_campaign'] = $request->boolean('environment_allow_follow_campaign');
            $payload['environment_allow_volunteer_registration'] = $request->boolean('environment_allow_volunteer_registration');

            foreach (CommunityPost::structuredLocationMetaKeys() as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
        }

        if (CommunityPost::usesScienceTechnologyFlow($contentType)) {
            $payload['science_technology_post_type'] = $request->input('science_technology_post_type');
            $payload['science_technology_category'] = $request->input('science_technology_category');
            $payload['science_technology_target_audience'] = array_values(array_intersect(
                (array) $request->input('science_technology_target_audience', []),
                CommunityContentTaxonomy::scienceTechnologyTargetAudiences()
            ));
            $payload['science_technology_level'] = $request->input('science_technology_level');
            $payload['science_technology_scientific_fields'] = array_values(array_intersect(
                (array) $request->input('science_technology_scientific_fields', []),
                CommunityContentTaxonomy::scienceTechnologyScientificFields()
            ));
            $payload['science_technology_project_name'] = $request->input('science_technology_project_name');
            $payload['science_technology_project_category'] = $request->input('science_technology_project_category');
            $payload['science_technology_project_objective'] = $request->input('science_technology_project_objective');
            $payload['science_technology_project_components'] = $request->input('science_technology_project_components');
            $payload['science_technology_project_working_principle'] = $request->input('science_technology_project_working_principle');
            $payload['science_technology_project_results'] = $request->input('science_technology_project_results');
            $payload['science_technology_project_future_improvements'] = $request->input('science_technology_project_future_improvements');
            $payload['science_technology_research_area'] = $request->input('science_technology_research_area');
            $payload['science_technology_research_institution'] = $request->input('science_technology_research_institution');
            $payload['science_technology_research_duration'] = $request->input('science_technology_research_duration');
            $payload['science_technology_research_abstract'] = $request->input('science_technology_research_abstract');
            $payload['science_technology_research_keywords'] = $request->input('science_technology_research_keywords');
            $payload['science_technology_research_methodology'] = $request->input('science_technology_research_methodology');
            $payload['science_technology_research_results'] = $request->input('science_technology_research_results');
            $payload['science_technology_research_conclusion'] = $request->input('science_technology_research_conclusion');
            $payload['science_technology_research_references'] = $request->input('science_technology_research_references');
            $payload['science_technology_experiment_objective'] = $request->input('science_technology_experiment_objective');
            $payload['science_technology_experiment_materials'] = $request->input('science_technology_experiment_materials');
            $payload['science_technology_experiment_procedure'] = $request->input('science_technology_experiment_procedure');
            $payload['science_technology_experiment_observations'] = $request->input('science_technology_experiment_observations');
            $payload['science_technology_experiment_results'] = $request->input('science_technology_experiment_results');
            $payload['science_technology_experiment_safety'] = $request->input('science_technology_experiment_safety');
            $payload['science_technology_innovation_name'] = $request->input('science_technology_innovation_name');
            $payload['science_technology_patent_filed'] = $request->input('science_technology_patent_filed');
            $payload['science_technology_problem_solved'] = $request->input('science_technology_problem_solved');
            $payload['science_technology_novel_features'] = $request->input('science_technology_novel_features');
            $payload['science_technology_innovation_technology'] = $request->input('science_technology_innovation_technology');
            $payload['science_technology_innovation_benefits'] = $request->input('science_technology_innovation_benefits');
            $payload['science_technology_commercial_potential'] = $request->input('science_technology_commercial_potential');
            $payload['science_technology_technologies_used'] = array_values(array_intersect(
                (array) $request->input('science_technology_technologies_used', []),
                CommunityContentTaxonomy::scienceTechnologyTechnologiesUsed()
            ));
            $payload['science_technology_programming_languages'] = array_values(array_intersect(
                (array) $request->input('science_technology_programming_languages', []),
                CommunityContentTaxonomy::scienceTechnologyProgrammingLanguages()
            ));
            $payload['science_technology_github_repo'] = $request->input('science_technology_github_repo');
            $payload['science_technology_hardware_components'] = $request->input('science_technology_hardware_components');
            $payload['science_technology_bom'] = $request->input('science_technology_bom');
            $payload['science_technology_hardware_cost'] = $request->input('science_technology_hardware_cost');
            $payload['science_technology_water_soil_topics'] = array_values(array_intersect(
                (array) $request->input('science_technology_water_soil_topics', []),
                CommunityContentTaxonomy::scienceTechnologyWaterSoilTopics()
            ));
            $payload['science_technology_renewable_energy'] = array_values(array_intersect(
                (array) $request->input('science_technology_renewable_energy', []),
                CommunityContentTaxonomy::scienceTechnologyRenewableEnergyTypes()
            ));
            $payload['science_technology_patent_number'] = $request->input('science_technology_patent_number');
            $payload['science_technology_application_number'] = $request->input('science_technology_application_number');
            $payload['science_technology_patent_status'] = $request->input('science_technology_patent_status');
            $payload['science_technology_funding_types'] = array_values(array_intersect(
                (array) $request->input('science_technology_funding_types', []),
                CommunityContentTaxonomy::scienceTechnologyFundingTypes()
            ));
            $payload['science_technology_application_areas'] = array_values(array_intersect(
                (array) $request->input('science_technology_application_areas', []),
                CommunityContentTaxonomy::scienceTechnologyApplicationAreas()
            ));
            $payload['science_technology_reference_types'] = array_values(array_intersect(
                (array) $request->input('science_technology_reference_types', []),
                CommunityContentTaxonomy::scienceTechnologyReferenceTypes()
            ));
            $payload['science_technology_references'] = $request->input('science_technology_references');
            $payload['science_technology_license'] = $request->input('science_technology_license');
            $payload['science_technology_video_type'] = $request->input('science_technology_video_type');
            $payload['science_technology_enable_innovation_showcase'] = $request->boolean('science_technology_enable_innovation_showcase');
            $payload['science_technology_enable_expert_review'] = $request->boolean('science_technology_enable_expert_review');
            $payload['science_technology_open_innovation'] = array_values(array_intersect(
                (array) $request->input('science_technology_open_innovation', []),
                CommunityContentTaxonomy::scienceTechnologyOpenInnovationOptions()
            ));
            $payload['science_technology_challenge_themes'] = array_values(array_intersect(
                (array) $request->input('science_technology_challenge_themes', []),
                CommunityContentTaxonomy::scienceTechnologyInnovationChallengeThemes()
            ));
            $payload['science_technology_collaboration_requests'] = array_values(array_intersect(
                (array) $request->input('science_technology_collaboration_requests', []),
                CommunityContentTaxonomy::scienceTechnologyCollaborationRequests()
            ));
            $payload['science_technology_ask_community'] = $request->input('science_technology_ask_community');
            $payload['science_technology_allow_poll'] = $request->boolean('science_technology_allow_poll');
            $payload['science_technology_poll_question'] = $request->input('science_technology_poll_question');
            $payload['science_technology_poll_options'] = collect(preg_split('/\R/', (string) $request->input('science_technology_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('science_technology_allow_poll')) {
                unset($payload['science_technology_poll_question'], $payload['science_technology_poll_options']);
            }
            $payload['science_technology_comment_settings'] = array_values(array_intersect(
                (array) $request->input('science_technology_comment_settings', []),
                CommunityContentTaxonomy::scienceTechnologyCommentSettings()
            ));
            $payload['science_technology_allow_support'] = $request->boolean('science_technology_allow_support');
            $payload['science_technology_allow_follow'] = $request->boolean('science_technology_allow_follow');
            $payload['science_technology_allow_collaborate'] = $request->boolean('science_technology_allow_collaborate');
        }

        if (CommunityPost::usesAstroConsultancyFlow($contentType)) {
            $payload['astro_consultancy_post_type'] = $request->input('astro_consultancy_post_type');
            $payload['astro_consultancy_category'] = $request->input('astro_consultancy_category');
            $payload['astro_consultancy_target_audience'] = array_values(array_intersect(
                (array) $request->input('astro_consultancy_target_audience', []),
                CommunityContentTaxonomy::astroConsultancyTargetAudiences()
            ));
            $payload['astro_consultancy_consultation_topics'] = array_values(array_intersect(
                (array) $request->input('astro_consultancy_consultation_topics', []),
                CommunityContentTaxonomy::astroConsultancyConsultationTopics()
            ));
            $payload['astro_consultancy_content_language'] = $request->input('astro_consultancy_content_language');
            $payload['astro_consultancy_zodiac_sign'] = $request->input('astro_consultancy_zodiac_sign');
            $payload['astro_consultancy_horoscope_period'] = $request->input('astro_consultancy_horoscope_period');
            $payload['astro_consultancy_vastu_property_types'] = array_values(array_intersect(
                (array) $request->input('astro_consultancy_vastu_property_types', []),
                CommunityContentTaxonomy::astroConsultancyVastuPropertyTypes()
            ));
            $payload['astro_consultancy_vastu_areas'] = array_values(array_intersect(
                (array) $request->input('astro_consultancy_vastu_areas', []),
                CommunityContentTaxonomy::astroConsultancyVastuAreas()
            ));
            $payload['astro_consultancy_life_path_number'] = $request->input('astro_consultancy_life_path_number');
            $payload['astro_consultancy_destiny_number'] = $request->input('astro_consultancy_destiny_number');
            $payload['astro_consultancy_name_number'] = $request->input('astro_consultancy_name_number');
            $payload['astro_consultancy_lucky_number'] = $request->input('astro_consultancy_lucky_number');
            $payload['astro_consultancy_compatibility'] = $request->input('astro_consultancy_compatibility');
            $payload['astro_consultancy_gemstone'] = $request->input('astro_consultancy_gemstone');
            $payload['astro_consultancy_gemstone_planet'] = $request->input('astro_consultancy_gemstone_planet');
            $payload['astro_consultancy_gemstone_benefits'] = $request->input('astro_consultancy_gemstone_benefits');
            $payload['astro_consultancy_gemstone_precautions'] = $request->input('astro_consultancy_gemstone_precautions');
            $payload['astro_consultancy_festival_name'] = $request->input('astro_consultancy_festival_name');
            $payload['astro_consultancy_muhurat_type'] = $request->input('astro_consultancy_muhurat_type');
            $payload['astro_consultancy_muhurat_date'] = $request->input('astro_consultancy_muhurat_date');
            $payload['astro_consultancy_muhurat_time'] = $request->input('astro_consultancy_muhurat_time');
            $payload['astro_consultancy_festival_significance'] = $request->input('astro_consultancy_festival_significance');
            $payload['astro_consultancy_document_types'] = array_values(array_intersect(
                (array) $request->input('astro_consultancy_document_types', []),
                CommunityContentTaxonomy::astroConsultancyDocumentTypes()
            ));
            $payload['astro_consultancy_video_type'] = $request->input('astro_consultancy_video_type');
            $payload['astro_consultancy_consultant_profile_url'] = $request->input('astro_consultancy_consultant_profile_url');
            $payload['astro_consultancy_related_service_actions'] = array_values(array_intersect(
                (array) $request->input('astro_consultancy_related_service_actions', []),
                CommunityContentTaxonomy::astroConsultancyRelatedServiceActions()
            ));
            $payload['astro_consultancy_enable_consultant_linking'] = $request->boolean('astro_consultancy_enable_consultant_linking');
            $payload['astro_consultancy_knowledge_library_topics'] = array_values(array_intersect(
                (array) $request->input('astro_consultancy_knowledge_library_topics', []),
                CommunityContentTaxonomy::astroConsultancyKnowledgeLibraryTopics()
            ));
            $payload['astro_consultancy_enable_live_qa'] = $request->boolean('astro_consultancy_enable_live_qa');
            $payload['astro_consultancy_private_query_options'] = array_values(array_intersect(
                (array) $request->input('astro_consultancy_private_query_options', []),
                CommunityContentTaxonomy::astroConsultancyPrivateQueryOptions()
            ));
            $payload['astro_consultancy_ask_community'] = $request->input('astro_consultancy_ask_community');
            $payload['astro_consultancy_allow_poll'] = $request->boolean('astro_consultancy_allow_poll');
            $payload['astro_consultancy_poll_question'] = $request->input('astro_consultancy_poll_question');
            $payload['astro_consultancy_poll_options'] = collect(preg_split('/\R/', (string) $request->input('astro_consultancy_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('astro_consultancy_allow_poll')) {
                unset($payload['astro_consultancy_poll_question'], $payload['astro_consultancy_poll_options']);
            }
            $payload['astro_consultancy_comment_settings'] = array_values(array_intersect(
                (array) $request->input('astro_consultancy_comment_settings', []),
                CommunityContentTaxonomy::astroConsultancyCommentSettings()
            ));
            foreach (CommunityContentTaxonomy::astroConsultancyDeclarationStatements() as $field => $label) {
                $payload[$field] = $request->boolean($field);
            }
        }

        if (CommunityPost::usesReligionSpiritualityFlow($contentType)) {
            $payload['religion_spirituality_post_type'] = $request->input('religion_spirituality_post_type');
            $payload['religion_spirituality_category'] = $request->input('religion_spirituality_category');
            $payload['religion_spirituality_tradition'] = $request->input('religion_spirituality_tradition');
            $payload['religion_spirituality_target_audience'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_target_audience', []),
                CommunityContentTaxonomy::religionSpiritualityTargetAudiences()
            ));
            $payload['religion_spirituality_scripture_name'] = $request->input('religion_spirituality_scripture_name');
            $payload['religion_spirituality_scripture_chapter'] = $request->input('religion_spirituality_scripture_chapter');
            $payload['religion_spirituality_scripture_verse'] = $request->input('religion_spirituality_scripture_verse');
            $payload['religion_spirituality_scripture_reference'] = $request->input('religion_spirituality_scripture_reference');
            $payload['religion_spirituality_moral_messages'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_moral_messages', []),
                CommunityContentTaxonomy::religionSpiritualityMoralValues()
            ));
            $payload['religion_spirituality_festival_name'] = $request->input('religion_spirituality_festival_name');
            $payload['religion_spirituality_festival_date'] = $request->input('religion_spirituality_festival_date');
            $payload['religion_spirituality_festival_historical_significance'] = $request->input('religion_spirituality_festival_historical_significance');
            $payload['religion_spirituality_festival_traditional_practices'] = $request->input('religion_spirituality_festival_traditional_practices');
            $payload['religion_spirituality_festival_celebration_methods'] = $request->input('religion_spirituality_festival_celebration_methods');
            $payload['religion_spirituality_festival_regional_variations'] = $request->input('religion_spirituality_festival_regional_variations');
            $payload['religion_spirituality_pilgrimage_name'] = $request->input('religion_spirituality_pilgrimage_name');
            $payload['religion_spirituality_pilgrimage_location'] = $request->input('religion_spirituality_pilgrimage_location');
            $payload['religion_spirituality_pilgrimage_best_time'] = $request->input('religion_spirituality_pilgrimage_best_time');
            $payload['religion_spirituality_pilgrimage_history'] = $request->input('religion_spirituality_pilgrimage_history');
            $payload['religion_spirituality_pilgrimage_facilities'] = $request->input('religion_spirituality_pilgrimage_facilities');
            $payload['religion_spirituality_pilgrimage_travel_tips'] = $request->input('religion_spirituality_pilgrimage_travel_tips');
            $payload['religion_spirituality_pilgrimage_accommodation'] = $request->input('religion_spirituality_pilgrimage_accommodation');
            $payload['religion_spirituality_place_of_worship_type'] = $request->input('religion_spirituality_place_of_worship_type');
            $payload['religion_spirituality_location_country'] = $request->input('religion_spirituality_location_country');
            $payload['religion_spirituality_location_state'] = $request->input('religion_spirituality_location_state');
            $payload['religion_spirituality_location_district'] = $request->input('religion_spirituality_location_district');
            $payload['religion_spirituality_location_city'] = $request->input('religion_spirituality_location_city');
            $payload['religion_spirituality_location_gps'] = $request->input('religion_spirituality_location_gps');
            $payload['religion_spirituality_meditation_topics'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_meditation_topics', []),
                CommunityContentTaxonomy::religionSpiritualityMeditationTopics()
            ));
            $payload['religion_spirituality_community_service_activities'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_community_service_activities', []),
                CommunityContentTaxonomy::religionSpiritualityCommunityServiceActivities()
            ));
            $payload['religion_spirituality_video_type'] = $request->input('religion_spirituality_video_type');
            $payload['religion_spirituality_audio_type'] = $request->input('religion_spirituality_audio_type');
            $payload['religion_spirituality_document_types'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_document_types', []),
                CommunityContentTaxonomy::religionSpiritualityDocumentTypes()
            ));
            $payload['religion_spirituality_related_service_actions'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_related_service_actions', []),
                CommunityContentTaxonomy::religionSpiritualityRelatedServiceActions()
            ));
            $payload['religion_spirituality_enable_digital_pilgrimage_guide'] = $request->boolean('religion_spirituality_enable_digital_pilgrimage_guide');
            $payload['religion_spirituality_digital_pilgrimage_site_types'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_digital_pilgrimage_site_types', []),
                CommunityContentTaxonomy::religionSpiritualityDigitalPilgrimageSiteTypes()
            ));
            $payload['religion_spirituality_digital_pilgrimage_site_name'] = $request->input('religion_spirituality_digital_pilgrimage_site_name');
            $payload['religion_spirituality_digital_pilgrimage_verified_info'] = $request->input('religion_spirituality_digital_pilgrimage_verified_info');
            $payload['religion_spirituality_digital_pilgrimage_nearby_facilities'] = $request->input('religion_spirituality_digital_pilgrimage_nearby_facilities');
            $payload['religion_spirituality_digital_pilgrimage_accommodation'] = $request->input('religion_spirituality_digital_pilgrimage_accommodation');
            $payload['religion_spirituality_digital_pilgrimage_local_businesses'] = $request->input('religion_spirituality_digital_pilgrimage_local_businesses');
            $payload['religion_spirituality_digital_pilgrimage_map_url'] = $request->input('religion_spirituality_digital_pilgrimage_map_url');
            $payload['religion_spirituality_enable_festival_calendar'] = $request->boolean('religion_spirituality_enable_festival_calendar');
            $payload['religion_spirituality_festival_calendar_event_types'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_festival_calendar_event_types', []),
                CommunityContentTaxonomy::religionSpiritualityFestivalCalendarEventTypes()
            ));
            $payload['religion_spirituality_festival_calendar_event_name'] = $request->input('religion_spirituality_festival_calendar_event_name');
            $payload['religion_spirituality_festival_calendar_event_date'] = $request->input('religion_spirituality_festival_calendar_event_date');
            $payload['religion_spirituality_festival_calendar_description'] = $request->input('religion_spirituality_festival_calendar_description');
            $payload['religion_spirituality_festival_calendar_linked_article_url'] = $request->input('religion_spirituality_festival_calendar_linked_article_url');
            $payload['religion_spirituality_enable_community_service_directory'] = $request->boolean('religion_spirituality_enable_community_service_directory');
            $payload['religion_spirituality_service_directory_opportunities'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_service_directory_opportunities', []),
                CommunityContentTaxonomy::religionSpiritualityServiceDirectoryOpportunities()
            ));
            $payload['religion_spirituality_service_directory_organization'] = $request->input('religion_spirituality_service_directory_organization');
            $payload['religion_spirituality_service_directory_when_where'] = $request->input('religion_spirituality_service_directory_when_where');
            $payload['religion_spirituality_service_directory_volunteer_notes'] = $request->input('religion_spirituality_service_directory_volunteer_notes');
            $payload['religion_spirituality_enable_wisdom_library'] = $request->boolean('religion_spirituality_enable_wisdom_library');
            $payload['religion_spirituality_wisdom_themes'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_wisdom_themes', []),
                CommunityContentTaxonomy::religionSpiritualityWisdomLibraryThemes()
            ));
            $payload['religion_spirituality_wisdom_traditions'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_wisdom_traditions', []),
                CommunityContentTaxonomy::religionSpiritualityTraditions()
            ));
            $payload['religion_spirituality_wisdom_collection_summary'] = $request->input('religion_spirituality_wisdom_collection_summary');
            $payload['religion_spirituality_ask_community'] = $request->input('religion_spirituality_ask_community');
            $payload['religion_spirituality_allow_poll'] = $request->boolean('religion_spirituality_allow_poll');
            $payload['religion_spirituality_poll_question'] = $request->input('religion_spirituality_poll_question');
            $payload['religion_spirituality_poll_options'] = collect(preg_split('/\R/', (string) $request->input('religion_spirituality_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('religion_spirituality_allow_poll')) {
                unset($payload['religion_spirituality_poll_question'], $payload['religion_spirituality_poll_options']);
            }
            $payload['religion_spirituality_comment_settings'] = array_values(array_intersect(
                (array) $request->input('religion_spirituality_comment_settings', []),
                CommunityContentTaxonomy::religionSpiritualityCommentSettings()
            ));
            foreach (CommunityContentTaxonomy::religionSpiritualityDeclarationStatements() as $field => $label) {
                $payload[$field] = $request->boolean($field);
            }
        }

        if (CommunityPost::usesCreativeCornerFlow($contentType)) {
            $payload['creative_corner_post_type'] = $request->input('creative_corner_post_type');
            $payload['creative_corner_category'] = $request->input('creative_corner_category');
            $payload['creative_corner_target_audience'] = array_values(array_intersect(
                (array) $request->input('creative_corner_target_audience', []),
                CommunityContentTaxonomy::creativeCornerTargetAudiences()
            ));
            $payload['creative_corner_creation_type'] = $request->input('creative_corner_creation_type');
            $payload['creative_corner_mediums'] = array_values(array_intersect(
                (array) $request->input('creative_corner_mediums', []),
                CommunityContentTaxonomy::creativeCornerMediums()
            ));
            $payload['creative_corner_software_tools'] = array_values(array_intersect(
                (array) $request->input('creative_corner_software_tools', []),
                CommunityContentTaxonomy::creativeCornerSoftwareTools()
            ));
            $payload['creative_corner_materials'] = array_values(array_intersect(
                (array) $request->input('creative_corner_materials', []),
                CommunityContentTaxonomy::creativeCornerMaterials()
            ));
            $payload['creative_corner_creation_date'] = $request->input('creative_corner_creation_date');
            $payload['creative_corner_time_taken'] = $request->input('creative_corner_time_taken');
            $payload['creative_corner_difficulty_level'] = $request->input('creative_corner_difficulty_level');
            $payload['creative_corner_themes'] = array_values(array_intersect(
                (array) $request->input('creative_corner_themes', []),
                CommunityContentTaxonomy::creativeCornerThemes()
            ));
            $payload['creative_corner_location_country'] = $request->input('creative_corner_location_country');
            $payload['creative_corner_location_state'] = $request->input('creative_corner_location_state');
            $payload['creative_corner_location_district'] = $request->input('creative_corner_location_district');
            $payload['creative_corner_location_city'] = $request->input('creative_corner_location_city');
            $payload['creative_corner_material_cost'] = $request->input('creative_corner_material_cost');
            $payload['creative_corner_equipment_cost'] = $request->input('creative_corner_equipment_cost');
            $payload['creative_corner_total_cost'] = $request->input('creative_corner_total_cost');
            $payload['creative_corner_submit_to_competition'] = $request->boolean('creative_corner_submit_to_competition');
            $payload['creative_corner_competition_categories'] = array_values(array_intersect(
                (array) $request->input('creative_corner_competition_categories', []),
                CommunityContentTaxonomy::creativeCornerCompetitionCategories()
            ));
            if (! $request->boolean('creative_corner_submit_to_competition')) {
                unset($payload['creative_corner_competition_categories']);
            }
            $payload['creative_corner_available_for_sale'] = $request->boolean('creative_corner_available_for_sale');
            $payload['creative_corner_sale_price'] = $request->input('creative_corner_sale_price');
            $payload['creative_corner_custom_orders_accepted'] = $request->boolean('creative_corner_custom_orders_accepted');
            $payload['creative_corner_limited_edition'] = $request->boolean('creative_corner_limited_edition');
            $payload['creative_corner_shipping_available'] = $request->boolean('creative_corner_shipping_available');
            if (! $request->boolean('creative_corner_available_for_sale')) {
                unset(
                    $payload['creative_corner_sale_price'],
                    $payload['creative_corner_custom_orders_accepted'],
                    $payload['creative_corner_limited_edition'],
                    $payload['creative_corner_shipping_available']
                );
            }
            $payload['creative_corner_commission_options'] = array_values(array_intersect(
                (array) $request->input('creative_corner_commission_options', []),
                CommunityContentTaxonomy::creativeCornerCommissionOptions()
            ));
            $payload['creative_corner_copyright'] = $request->input('creative_corner_copyright');
            $payload['creative_corner_social_portfolio'] = $request->input('creative_corner_social_portfolio');
            $payload['creative_corner_social_instagram'] = $request->input('creative_corner_social_instagram');
            $payload['creative_corner_social_youtube'] = $request->input('creative_corner_social_youtube');
            $payload['creative_corner_social_website'] = $request->input('creative_corner_social_website');
            $payload['creative_corner_social_vendor_profile'] = $request->input('creative_corner_social_vendor_profile');
            $payload['creative_corner_video_type'] = $request->input('creative_corner_video_type');
            $payload['creative_corner_audio_type'] = $request->input('creative_corner_audio_type');
            $payload['creative_corner_document_types'] = array_values(array_intersect(
                (array) $request->input('creative_corner_document_types', []),
                CommunityContentTaxonomy::creativeCornerDocumentTypes()
            ));
            $payload['creative_corner_ask_community'] = $request->input('creative_corner_ask_community');
            $payload['creative_corner_allow_poll'] = $request->boolean('creative_corner_allow_poll');
            $payload['creative_corner_poll_question'] = $request->input('creative_corner_poll_question');
            $payload['creative_corner_poll_options'] = collect(preg_split('/\R/', (string) $request->input('creative_corner_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('creative_corner_allow_poll')) {
                unset($payload['creative_corner_poll_question'], $payload['creative_corner_poll_options']);
            }
            $payload['creative_corner_comment_settings'] = array_values(array_intersect(
                (array) $request->input('creative_corner_comment_settings', []),
                CommunityContentTaxonomy::creativeCornerCommentSettings()
            ));
            $payload['creative_corner_creative_licenses'] = array_values(array_intersect(
                (array) $request->input('creative_corner_creative_licenses', []),
                CommunityContentTaxonomy::creativeCornerCreativeLicenses()
            ));
            $payload['creative_corner_collaboration_roles'] = array_values(array_intersect(
                (array) $request->input('creative_corner_collaboration_roles', []),
                CommunityContentTaxonomy::creativeCornerCollaborationRoles()
            ));
            $payload['creative_corner_ai_used'] = $request->input('creative_corner_ai_used');
            $payload['creative_corner_ai_tool'] = $request->input('creative_corner_ai_tool');
            $payload['creative_corner_ai_description'] = $request->input('creative_corner_ai_description');
            if ($request->input('creative_corner_ai_used') === 'No') {
                unset($payload['creative_corner_ai_tool'], $payload['creative_corner_ai_description']);
            }
            foreach (CommunityContentTaxonomy::creativeCornerDeclarationStatements() as $field => $label) {
                $payload[$field] = $request->boolean($field);
            }
        }

        if (CommunityPost::usesYouthCornerFlow($contentType)) {
            $payload['youth_corner_category'] = $request->input('youth_corner_category');
            $payload['youth_corner_content_type'] = $request->input('youth_corner_content_type');
            $payload['youth_corner_age_group'] = $request->input('youth_corner_age_group');
            $payload['youth_corner_occupation'] = $request->input('youth_corner_occupation');
            $payload['youth_corner_education_level'] = $request->input('youth_corner_education_level');
            $payload['youth_corner_target_audience'] = array_values(array_intersect(
                (array) $request->input('youth_corner_target_audience', []),
                CommunityContentTaxonomy::youthCornerTargetAudiences()
            ));
            $payload['youth_corner_video_type'] = $request->input('youth_corner_video_type');
            $payload['youth_corner_opportunity_types'] = array_values(array_intersect(
                (array) $request->input('youth_corner_opportunity_types', []),
                CommunityContentTaxonomy::youthCornerOpportunityTypes()
            ));
            $payload['youth_corner_skills'] = array_values(array_intersect(
                (array) $request->input('youth_corner_skills', []),
                CommunityContentTaxonomy::youthCornerSkills()
            ));
            $payload['youth_corner_career_area'] = $request->input('youth_corner_career_area');
            $payload['youth_corner_startup_name'] = $request->input('youth_corner_startup_name');
            $payload['youth_corner_startup_industry'] = $request->input('youth_corner_startup_industry');
            $payload['youth_corner_business_idea'] = $request->input('youth_corner_business_idea');
            $payload['youth_corner_funding_stage'] = $request->input('youth_corner_funding_stage');
            $payload['youth_corner_startup_challenges'] = $request->input('youth_corner_startup_challenges');
            $payload['youth_corner_startup_lessons'] = $request->input('youth_corner_startup_lessons');
            $payload['youth_corner_themes'] = array_values(array_intersect(
                (array) $request->input('youth_corner_themes', []),
                CommunityContentTaxonomy::youthCornerThemes()
            ));
            $payload['youth_corner_community_service'] = array_values(array_intersect(
                (array) $request->input('youth_corner_community_service', []),
                CommunityContentTaxonomy::youthCornerCommunityServiceActivities()
            ));
            $payload['youth_corner_networking_options'] = array_values(array_intersect(
                (array) $request->input('youth_corner_networking_options', []),
                CommunityContentTaxonomy::youthCornerNetworkingOptions()
            ));
            $payload['youth_corner_ask_community'] = $request->input('youth_corner_ask_community');
            $payload['youth_corner_poll_question'] = $request->input('youth_corner_poll_question');
            $payload['youth_corner_poll_options'] = collect(preg_split('/\R/', (string) $request->input('youth_corner_poll_options', '')))
                ->map(fn (mixed $line): string => trim((string) $line))
                ->filter()
                ->values()
                ->all();
            if (! $request->boolean('allow_poll')) {
                unset($payload['youth_corner_poll_question'], $payload['youth_corner_poll_options']);
            }
            $payload['youth_corner_mentorship_requests'] = array_values(array_intersect(
                (array) $request->input('youth_corner_mentorship_requests', []),
                CommunityContentTaxonomy::youthCornerMentorshipRequests()
            ));
            $payload['youth_corner_visibility'] = array_key_exists(
                (string) $request->input('youth_corner_visibility'),
                CommunityContentTaxonomy::youthCornerVisibilitySettings()
            )
                ? (string) $request->input('youth_corner_visibility')
                : CommunityContentTaxonomy::youthCornerDefaultVisibilitySetting();

            foreach (['location_country', 'location_state', 'location_district', 'location_city'] as $locationKey) {
                if ($request->has($locationKey)) {
                    $payload[$locationKey] = $request->input($locationKey);
                }
            }
            unset($payload['location_locality']);

            if ($request->input('youth_corner_content_type') === CommunityContentTaxonomy::youthCornerProjectContentType()) {
                $payload['youth_corner_project_title'] = $request->input('youth_corner_project_title');
                $payload['youth_corner_project_category'] = $request->input('youth_corner_project_category');
                $payload['youth_corner_project_description'] = $request->input('youth_corner_project_description');
                $payload['youth_corner_project_outcome'] = $request->input('youth_corner_project_outcome');
            }
        }

        return collect($payload)
            ->filter(function (mixed $value, string $key): bool {
                if (in_array($key, [
                    'story_target_audience',
                    'story_themes',
                    'poetry_themes',
                    'poetry_target_audience',
                    'childrens_corner_themes',
                    'childrens_corner_talent_categories',
                    'places_mentioned',
                    'key_lessons_learned',
                    'related_people',
                    'parent_approved',
                    'childrens_corner_comments_moderated',
                    'childrens_corner_child_friendly_reactions',
                    'childrens_corner_privacy_setting',
                    'childrens_corner_safety_no_address',
                    'childrens_corner_safety_no_harmful',
                    'childrens_corner_safety_no_copyright',
                    'childrens_corner_safety_no_inappropriate_media',
                    'childrens_corner_safety_confirmed',
                    'awareness_target_audience',
                    'business_target_audience',
                    'business_challenges',
                    'business_market_segments',
                    'business_themes',
                    'business_contact_options',
                    'business_poll_options',
                    'womens_world_target_audience',
                    'womens_world_featured_topics',
                    'womens_world_themes',
                    'womens_world_poll_options',
                    'womens_world_support_requests',
                    'womens_world_community_groups',
                    'senior_citizens_forum_life_journey_categories',
                    'senior_citizens_forum_key_lessons',
                    'senior_citizens_forum_themes',
                    'senior_citizens_forum_community_contributions',
                    'senior_citizens_forum_intergenerational_connections',
                    'student_corner_target_audience',
                    'student_corner_study_material_types',
                    'student_corner_career_guidance_topics',
                    'student_corner_skills',
                    'student_corner_social_impact_categories',
                    'student_corner_poll_options',
                    'student_corner_mentorship_requests',
                    'student_corner_competition_categories',
                    'youth_corner_target_audience',
                    'youth_corner_opportunity_types',
                    'youth_corner_skills',
                    'youth_corner_themes',
                    'youth_corner_community_service',
                    'youth_corner_networking_options',
                    'youth_corner_poll_options',
                    'youth_corner_mentorship_requests',
                    'local_voice_affected_communities',
                    'local_voice_authorities',
                    'local_voice_call_for_action',
                    'local_voice_poll_options',
                    'local_voice_initiatives',
                    'local_voice_allow_support',
                    'local_voice_allow_follow',
                    'my_area_affected_communities',
                    'my_area_authorities',
                    'my_area_poll_options',
                ], true)) {
                    return true;
                }

                return filled($value) || is_bool($value);
            })
            ->all();
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
     * Ordered story metadata keys and labels for detail views.
     * Moral / takeaway is displayed separately on the public page.
     *
     * @return array<string, string>
     */
    public static function storyDetailMetaOrder(): array
    {
        return [
            'story_time_period' => 'Time period',
            'story_language' => 'Language',
            'story_target_audience' => 'Target audience',
            'story_themes' => 'Story theme',
            'story_main_characters' => 'Main characters',
            'story_character_type' => 'Character type',
            'story_place_type' => 'Story location',
            'story_place_names' => 'Place names',
        ];
    }

    /**
     * Ordered poetry metadata keys and labels for detail views.
     *
     * @return array<string, string>
     */
    public static function poetryDetailMetaOrder(): array
    {
        return [
            'poetry_type' => 'Poetry type',
            'sub_category' => 'Sub category',
            'poem_language' => 'Poem language',
            'poetry_themes' => 'Theme',
            'poetry_target_audience' => 'Target audience',
            'poetry_inspiration' => 'Inspiration',
            'poetry_series_name' => 'Collection',
            'poetry_series_part' => 'Part',
            'dedication' => 'Dedication',
            'reading_time' => 'Reading time',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedPoetryMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::poetryDetailMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if (in_array($key, ['poetry_themes', 'poetry_target_audience'], true) && is_array($value)) {
                    $value = implode(', ', $value);
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value) || is_bool($value));
    }

    /**
     * Regional location keys and labels for poetry detail views.
     *
     * @return array<string, string>
     */
    public static function poetryRegionalLocationOrder(): array
    {
        return [
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City',
        ];
    }

    /**
     * Ordered autobiography metadata keys and labels for detail views.
     *
     * @return array<string, string>
     */
    public static function autobiographyDetailMetaOrder(): array
    {
        return [
            'autobiography_type' => 'Autobiography type',
            'birth_place' => 'Birth place',
            'current_location' => 'Current location',
            'places_mentioned' => 'Places mentioned',
            'key_lessons_learned' => 'Inspirational lessons',
            'related_people' => 'Related people',
        ];
    }

    /**
     * @return list<string>
     */
    public static function autobiographyStructuredMetaKeys(): array
    {
        return [
            'autobiography_type',
            'birth_place',
            'current_location',
            'places_mentioned',
            'key_lessons_learned',
            'life_timeline',
            'autobiography_audio',
            'autobiography_achievements',
            'autobiography_documents',
            'related_people',
            'author_bio',
            'book_pages',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedAutobiographyMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::autobiographyDetailMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'places_mentioned' && is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                if ($key === 'key_lessons_learned' && is_array($value)) {
                    $value = implode('; ', array_values(array_filter($value)));
                }

                if ($key === 'related_people' && is_array($value)) {
                    $value = collect($value)
                        ->filter(fn (mixed $person): bool => filled(data_get($person, 'name')))
                        ->map(function (mixed $person): string {
                            $name = (string) data_get($person, 'name');
                            $relationship = data_get($person, 'relationship');

                            return filled($relationship) ? $name.' ('.$relationship.')' : $name;
                        })
                        ->implode(', ');
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedStoryMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::storyDetailMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if (in_array($key, ['story_target_audience', 'story_themes'], true) && is_array($value)) {
                    $value = implode(', ', $value);
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value) || is_bool($value));
    }

    /**
     * Ordered Children's Corner metadata for public detail views.
     *
     * @return array<string, string>
     */
    public static function childrensCornerPublicMetaOrder(): array
    {
        return [
            'child_share_type' => 'Share type',
            'child_first_name' => "Child's first name",
            'child_age_group' => 'Age group',
            'child_grade_level' => 'Grade / class',
            'child_school_name' => 'School name',
            'childrens_corner_submitted_through' => 'Submitted through',
            'childrens_corner_school_competition_entry' => 'School competition entry',
            'childrens_corner_themes' => 'Themes',
            'childrens_corner_talent_categories' => 'Talent categories',
            'childrens_corner_achievement' => 'Achievement / recognition',
            'childrens_corner_city' => 'City',
            'childrens_corner_district' => 'District',
            'childrens_corner_state' => 'State',
        ];
    }

    /**
     * Parent, consent, and moderation fields — backend / author views only.
     *
     * @return array<string, string>
     */
    public static function childrensCornerAdminMetaOrder(): array
    {
        return [
            'parent_name' => 'Parent / guardian name',
            'parent_relationship' => 'Relationship',
            'parent_mobile' => 'Parent mobile',
            'parent_email' => 'Parent email',
            'parent_approved' => 'All parent consents confirmed',
            'child_parent_consent_identity' => 'Identity consent',
            'child_parent_consent_publication' => 'Publication consent',
            'child_parent_consent_original' => 'Original work consent',
            'childrens_corner_comments_moderated' => 'Comments moderated',
            'childrens_corner_child_friendly_reactions' => 'Child-friendly reactions only',
            'childrens_corner_privacy_setting' => 'Privacy setting',
            'childrens_corner_safety_no_address' => 'Safety: no personal address',
            'childrens_corner_safety_no_harmful' => 'Safety: no harmful content',
            'childrens_corner_safety_no_copyright' => 'Safety: no copyrighted material',
            'childrens_corner_safety_no_inappropriate_media' => 'Safety: no inappropriate media',
            'childrens_corner_safety_confirmed' => 'Safety declaration confirmed',
        ];
    }

    /**
     * @return list<string>
     */
    public static function childrensCornerStructuredMetaKeys(): array
    {
        return array_values(array_unique(array_merge(
            array_keys(self::childrensCornerPublicMetaOrder()),
            array_keys(self::childrensCornerAdminMetaOrder()),
            [
                'childrens_corner_art',
                'childrens_corner_project_files',
                'childrens_corner_project_description',
                'childrens_corner_quiz',
                'childrens_corner_gallery',
                'childrens_corner_video',
                'childrens_corner_audio',
                'childrens_corner_certificate',
            ]
        )));
    }

    /**
     * @return list<string>
     */
    public static function childrensCornerPrivateMetaKeys(): array
    {
        return [
            'parent_name',
            'parent_mobile',
            'parent_email',
            'parent_relationship',
            'child_parent_consent_identity',
            'child_parent_consent_publication',
            'child_parent_consent_original',
            'parent_approved',
            'childrens_corner_comments_moderated',
            'childrens_corner_child_friendly_reactions',
            'childrens_corner_privacy_setting',
            'childrens_corner_safety_no_address',
            'childrens_corner_safety_no_harmful',
            'childrens_corner_safety_no_copyright',
            'childrens_corner_safety_no_inappropriate_media',
            'childrens_corner_safety_confirmed',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedChildrensCornerMetaForDisplay(\App\Models\CommunityPost $post, bool $includeAdmin = false): \Illuminate\Support\Collection
    {
        $order = $includeAdmin
            ? self::childrensCornerPublicMetaOrder() + self::childrensCornerAdminMetaOrder()
            : self::childrensCornerPublicMetaOrder();

        return collect($order)
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if (in_array($key, ['childrens_corner_themes', 'childrens_corner_talent_categories'], true) && is_array($value)) {
                    $value = implode(', ', $value);
                }

                if ($key === 'childrens_corner_privacy_setting' && filled($value)) {
                    $value = CommunityContentTaxonomy::childrensCornerPrivacySettings()[(string) $value] ?? $value;
                }

                if (str_starts_with($key, 'child_parent_consent_')
                    || str_starts_with($key, 'childrens_corner_safety_')
                    || in_array($key, ['parent_approved', 'childrens_corner_comments_moderated', 'childrens_corner_child_friendly_reactions', 'childrens_corner_safety_confirmed'], true)) {
                    $value = (bool) $value;
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value) || is_bool($value));
    }

    /**
     * @return array<string, string>
     */
    public static function awarenessDetailMetaOrder(): array
    {
        return [
            'awareness_category' => 'Main category',
            'awareness_type' => 'Awareness type',
            'awareness_level' => 'Awareness level',
            'awareness_campaign_start_date' => 'Campaign start date',
            'awareness_campaign_end_date' => 'Campaign end date',
            'awareness_video_type' => 'Video type',
            'awareness_posted_by' => 'Posted by',
            'awareness_organization_name' => 'Organization name',
            'awareness_target_audience' => 'Target audience',
        ];
    }

    /**
     * @return list<string>
     */
    public static function awarenessStructuredMetaKeys(): array
    {
        return array_keys(self::awarenessDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function businessDetailMetaOrder(): array
    {
        return [
            'business_category' => 'Main category',
            'business_content_type' => 'Business content type',
            'business_stage' => 'Business stage',
            'business_target_audience' => 'Target audience',
            'business_challenges' => 'Business challenges',
            'business_opportunity_type' => 'Opportunity type',
            'business_market_segments' => 'Market segment',
            'business_themes' => 'Business themes',
            'business_name' => 'Business name',
            'business_author_designation' => 'Designation',
            'business_profile_type' => 'Business type',
            'business_industry' => 'Industry',
            'business_video_type' => 'Video type',
            'business_ask_community' => 'Ask the community',
            'business_useful_links' => 'Useful links',
            'business_government_schemes' => 'Government schemes',
            'business_training_programs' => 'Training programs',
            'business_industry_resources' => 'Industry resources',
            'business_contact_options' => 'Contact options',
            'business_poll_question' => 'Poll question',
            'business_poll_options' => 'Poll options',
        ];
    }

    /**
     * @return list<string>
     */
    public static function businessStructuredMetaKeys(): array
    {
        return array_keys(self::businessDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function womensWorldDetailMetaOrder(): array
    {
        return [
            'womens_world_category' => 'Main category',
            'womens_world_content_type' => 'Content type',
            'womens_world_target_audience' => 'Target audience',
            'womens_world_featured_topics' => 'Featured topics',
            'womens_world_life_stage' => 'Life stage',
            'womens_world_themes' => 'Themes',
            'womens_world_video_type' => 'Video type',
        ];
    }

    /**
     * @return list<string>
     */
    public static function womensWorldStructuredMetaKeys(): array
    {
        return array_keys(self::womensWorldDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function localVoiceDetailMetaOrder(): array
    {
        return [
            'local_voice_type' => 'Voice type',
            'local_voice_category' => 'Main category',
            'local_voice_issue_type' => 'Issue type',
            'local_voice_affected_communities' => 'Affected community',
            'local_voice_impact_level' => 'Impact level',
            'local_voice_video_type' => 'Video type',
            'local_voice_suggested_solution' => 'Suggested solution',
            'local_voice_estimated_benefit' => 'Estimated benefit',
            'local_voice_authorities' => 'Authority concerned',
            'local_voice_call_for_action' => 'Call for action',
            'local_voice_status_tracker' => 'Status tracker',
            'local_voice_poll_question' => 'Poll question',
            'local_voice_poll_options' => 'Poll options',
            'local_voice_allow_support' => 'Community support',
            'local_voice_allow_follow' => 'Follow issue',
            'local_voice_hero_name' => 'Local hero name',
            'local_voice_hero_location' => 'Local hero location',
            'local_voice_hero_contribution' => 'Local hero contribution',
            'local_voice_hero_achievements' => 'Local hero achievements',
            'local_voice_initiatives' => 'Community initiatives',
            'local_voice_event_date' => 'Event date',
            'local_voice_event_time' => 'Event time',
            'local_voice_event_venue' => 'Event venue',
            'local_voice_event_organizer' => 'Event organizer',
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City/Town/Village',
            'location_locality' => 'Locality / Area',
            'local_voice_visibility' => 'Visibility',
        ];
    }

    /**
     * @return list<string>
     */
    public static function localVoiceStructuredMetaKeys(): array
    {
        return array_keys(self::localVoiceDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function communityIssueDetailMetaOrder(): array
    {
        return [
            'community_issue_category' => 'Issue category',
            'community_issue_type' => 'Issue type',
            'community_issue_severity' => 'Issue severity',
            'community_issue_affected_population' => 'Affected population',
            'community_issue_affected_groups' => 'Affected groups',
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City/Town/Village',
            'location_locality' => 'Locality / Area',
            'location_landmark' => 'Landmark',
            'community_issue_first_noticed_on' => 'First noticed on',
            'community_issue_is_recurring' => 'Recurring issue',
            'community_issue_frequency' => 'Frequency',
            'community_issue_authority' => 'Responsible authority',
            'community_issue_already_reported' => 'Already reported',
            'community_issue_complaint_number' => 'Complaint number',
            'community_issue_complaint_date' => 'Complaint date',
            'community_issue_department_contacted' => 'Department contacted',
            'community_issue_suggested_solution' => 'Suggested solution',
            'community_issue_support_requests' => 'Support requests',
            'community_issue_status_tracker' => 'Status',
            'community_issue_resolution_timeline' => 'Resolution timeline',
            'community_issue_allow_campaign' => 'Support campaign',
            'community_issue_allow_support' => 'Community support',
            'community_issue_allow_follow' => 'Follow issue',
            'community_issue_allow_verification' => 'Community verification',
            'community_issue_escalation_threshold' => 'Escalation threshold',
            'community_issue_poll_question' => 'Poll question',
            'community_issue_poll_options' => 'Poll options',
            'community_issue_visibility' => 'Visibility',
        ];
    }

    /**
     * @return list<string>
     */
    public static function communityIssueStructuredMetaKeys(): array
    {
        return array_keys(self::communityIssueDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function agricultureDetailMetaOrder(): array
    {
        return [
            'agriculture_share_type' => 'Share type',
            'agriculture_category' => 'Main category',
            'agriculture_crop_name' => 'Crop name',
            'agriculture_crop_variety' => 'Crop variety',
            'agriculture_sowing_date' => 'Sowing date',
            'agriculture_harvest_date' => 'Harvest date',
            'agriculture_growing_season' => 'Growing season',
            'agriculture_climate_zone' => 'Climate zone',
            'agriculture_soil_type' => 'Soil type',
            'agriculture_farm_size' => 'Farm size',
            'agriculture_farming_type' => 'Farming type',
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City/Town/Village',
            'agriculture_irrigation_method' => 'Irrigation method',
            'agriculture_water_source' => 'Water source',
            'agriculture_water_conservation_practices' => 'Water conservation practices',
            'agriculture_soil_test_conducted' => 'Soil test conducted',
            'agriculture_soil_ph' => 'Soil pH',
            'agriculture_soil_organic_carbon' => 'Organic carbon',
            'agriculture_soil_nitrogen' => 'Nitrogen',
            'agriculture_soil_phosphorus' => 'Phosphorus',
            'agriculture_soil_potassium' => 'Potassium',
            'agriculture_soil_recommendations' => 'Soil recommendations',
            'agriculture_problem_type' => 'Problem type',
            'agriculture_expert_assistance' => 'Expert assistance requested',
            'agriculture_equipment_name' => 'Equipment name',
            'agriculture_equipment_manufacturer' => 'Equipment manufacturer',
            'agriculture_equipment_experience' => 'Equipment experience',
            'agriculture_equipment_cost' => 'Equipment cost',
            'agriculture_equipment_benefits' => 'Equipment benefits',
            'agriculture_scheme_name' => 'Scheme name',
            'agriculture_scheme_department' => 'Scheme department',
            'agriculture_scheme_eligibility' => 'Scheme eligibility',
            'agriculture_scheme_subsidy' => 'Scheme subsidy',
            'agriculture_scheme_application_link' => 'Application link',
            'agriculture_scheme_last_date' => 'Scheme last date',
            'agriculture_market_commodity' => 'Market commodity',
            'agriculture_market_name' => 'Market name',
            'agriculture_market_price' => 'Market price',
            'agriculture_market_date' => 'Market date',
            'agriculture_market_price_trend' => 'Price trend',
            'agriculture_livestock_types' => 'Livestock types',
            'agriculture_innovation_name' => 'Innovation name',
            'agriculture_innovation_description' => 'Innovation description',
            'agriculture_innovation_benefits' => 'Innovation benefits',
            'agriculture_innovation_results' => 'Innovation results',
            'agriculture_agri_business_type' => 'Agri-business type',
            'agriculture_weather_impact' => 'Weather impact',
            'agriculture_video_type' => 'Video type',
            'agriculture_ask_community' => 'Ask the community',
            'agriculture_enable_knowledge_exchange' => 'Farmer knowledge exchange',
            'agriculture_enable_crop_doctor' => 'Crop Doctor',
            'agriculture_target_audiences' => 'Target audiences',
            'agriculture_poll_question' => 'Poll question',
            'agriculture_poll_options' => 'Poll options',
        ];
    }

    /**
     * @return list<string>
     */
    public static function agricultureStructuredMetaKeys(): array
    {
        return array_keys(self::agricultureDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function environmentDetailMetaOrder(): array
    {
        return [
            'environment_post_type' => 'Post type',
            'environment_category' => 'Main category',
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City/Town/Village',
            'location_locality' => 'Locality / Area',
            'environment_natural_feature_name' => 'Forest / River / Lake name',
            'environment_map_pin_type' => 'Map pin type',
            'environment_issue_type' => 'Issue type',
            'environment_initiative_type' => 'Initiative type',
            'environment_water_source' => 'Water source',
            'environment_conservation_method' => 'Conservation method',
            'environment_water_saved' => 'Water saved',
            'environment_soil_conservation_methods' => 'Soil conservation methods',
            'environment_tree_count' => 'Trees planted',
            'environment_tree_species' => 'Tree species',
            'environment_tree_plantation_date' => 'Plantation date',
            'environment_tree_organization' => 'Organization',
            'environment_tree_survival_status' => 'Survival status',
            'environment_tree_maintenance_plan' => 'Maintenance plan',
            'environment_waste_types' => 'Waste types',
            'environment_biodiversity_types' => 'Biodiversity types',
            'environment_climate_impacts' => 'Climate impacts',
            'environment_video_type' => 'Video type',
            'environment_enable_impact_calculator' => 'Impact calculator',
            'environment_data_trees_planted' => 'Impact · trees planted',
            'environment_data_area_covered' => 'Impact · area covered',
            'environment_data_water_saved' => 'Impact · water saved',
            'environment_data_waste_collected' => 'Impact · waste collected',
            'environment_data_people_participated' => 'Impact · people participated',
            'environment_data_carbon_reduction' => 'Impact · carbon reduction',
            'environment_data_species_recorded' => 'Impact · species recorded',
            'environment_participation_requests' => 'Participation requests',
            'environment_event_campaign_name' => 'Event / campaign name',
            'environment_event_organizer' => 'Event organizer',
            'environment_event_venue' => 'Event venue',
            'environment_event_date' => 'Event date',
            'environment_event_time' => 'Event time',
            'environment_event_registration_link' => 'Event registration link',
            'environment_scheme_name' => 'Scheme name',
            'environment_scheme_department' => 'Scheme department',
            'environment_scheme_eligibility' => 'Scheme eligibility',
            'environment_scheme_benefits' => 'Scheme benefits',
            'environment_scheme_official_link' => 'Scheme official link',
            'environment_ask_community' => 'Ask the community',
            'environment_poll_question' => 'Poll question',
            'environment_poll_options' => 'Poll options',
            'environment_show_on_green_map' => 'Show on Green Map',
            'environment_enable_green_leader' => 'Green Leader Program',
            'environment_allow_join_campaign' => 'Allow join campaign',
            'environment_allow_volunteer' => 'Allow volunteer',
            'environment_allow_donate' => 'Allow donate',
            'environment_allow_support_initiative' => 'Allow support initiative',
            'environment_allow_follow_campaign' => 'Allow follow campaign',
            'environment_allow_volunteer_registration' => 'Allow volunteer registration',
        ];
    }

    /**
     * @return list<string>
     */
    public static function environmentStructuredMetaKeys(): array
    {
        return array_keys(self::environmentDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function scienceTechnologyDetailMetaOrder(): array
    {
        return [
            'science_technology_post_type' => 'Post type',
            'science_technology_category' => 'Main category',
            'science_technology_target_audience' => 'Target audience',
            'science_technology_level' => 'Technology level',
            'science_technology_scientific_fields' => 'Scientific field',
            'science_technology_project_name' => 'Project name',
            'science_technology_project_category' => 'Project category',
            'science_technology_project_objective' => 'Project objective',
            'science_technology_project_components' => 'Components used',
            'science_technology_project_working_principle' => 'Working principle',
            'science_technology_project_results' => 'Project results',
            'science_technology_project_future_improvements' => 'Future improvements',
            'science_technology_research_area' => 'Research area',
            'science_technology_research_institution' => 'Institution',
            'science_technology_research_duration' => 'Research duration',
            'science_technology_research_abstract' => 'Abstract',
            'science_technology_research_keywords' => 'Keywords',
            'science_technology_research_methodology' => 'Methodology',
            'science_technology_research_results' => 'Research results',
            'science_technology_research_conclusion' => 'Conclusion',
            'science_technology_research_references' => 'Research references',
            'science_technology_experiment_objective' => 'Experiment objective',
            'science_technology_experiment_materials' => 'Materials required',
            'science_technology_experiment_procedure' => 'Procedure',
            'science_technology_experiment_observations' => 'Observations',
            'science_technology_experiment_results' => 'Experiment results',
            'science_technology_experiment_safety' => 'Safety precautions',
            'science_technology_innovation_name' => 'Innovation name',
            'science_technology_patent_filed' => 'Patent filed',
            'science_technology_problem_solved' => 'Problem solved',
            'science_technology_novel_features' => 'Novel features',
            'science_technology_innovation_technology' => 'Innovation technology',
            'science_technology_innovation_benefits' => 'Benefits',
            'science_technology_commercial_potential' => 'Commercial potential',
            'science_technology_technologies_used' => 'Technologies used',
            'science_technology_programming_languages' => 'Programming languages',
            'science_technology_github_repo' => 'GitHub repository',
            'science_technology_hardware_components' => 'Hardware components',
            'science_technology_bom' => 'Bill of materials',
            'science_technology_hardware_cost' => 'Hardware cost',
            'science_technology_water_soil_topics' => 'Water & soil technology',
            'science_technology_renewable_energy' => 'Renewable energy',
            'science_technology_patent_number' => 'Patent number',
            'science_technology_application_number' => 'Application number',
            'science_technology_patent_status' => 'Patent status',
            'science_technology_funding_types' => 'Funding',
            'science_technology_application_areas' => 'Application areas',
            'science_technology_reference_types' => 'Reference types',
            'science_technology_references' => 'References',
            'science_technology_license' => 'License',
            'science_technology_video_type' => 'Video type',
            'science_technology_enable_innovation_showcase' => 'Innovation showcase',
            'science_technology_enable_expert_review' => 'Expert review panel',
            'science_technology_open_innovation' => 'Open innovation marketplace',
            'science_technology_challenge_themes' => 'Innovation challenges',
            'science_technology_collaboration_requests' => 'Collaboration requests',
            'science_technology_ask_community' => 'Ask the community',
            'science_technology_poll_question' => 'Poll question',
            'science_technology_poll_options' => 'Poll options',
            'science_technology_comment_settings' => 'Comments settings',
            'science_technology_allow_support' => 'Allow support',
            'science_technology_allow_follow' => 'Allow follow',
            'science_technology_allow_collaborate' => 'Allow collaboration',
        ];
    }

    /**
     * @return list<string>
     */
    public static function scienceTechnologyStructuredMetaKeys(): array
    {
        return array_keys(self::scienceTechnologyDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function astroConsultancyDetailMetaOrder(): array
    {
        return [
            'astro_consultancy_post_type' => 'Post type',
            'astro_consultancy_category' => 'Consultancy category',
            'astro_consultancy_target_audience' => 'Target audience',
            'astro_consultancy_consultation_topics' => 'Consultation topic',
            'astro_consultancy_content_language' => 'Language',
            'astro_consultancy_zodiac_sign' => 'Zodiac sign',
            'astro_consultancy_horoscope_period' => 'Horoscope period',
            'astro_consultancy_vastu_property_types' => 'Vastu property type',
            'astro_consultancy_vastu_areas' => 'Vastu area',
            'astro_consultancy_life_path_number' => 'Life path number',
            'astro_consultancy_destiny_number' => 'Destiny number',
            'astro_consultancy_name_number' => 'Name number',
            'astro_consultancy_lucky_number' => 'Lucky number',
            'astro_consultancy_compatibility' => 'Compatibility',
            'astro_consultancy_gemstone' => 'Gemstone',
            'astro_consultancy_gemstone_planet' => 'Planet',
            'astro_consultancy_gemstone_benefits' => 'Traditional benefits',
            'astro_consultancy_gemstone_precautions' => 'Precautions',
            'astro_consultancy_festival_name' => 'Festival name',
            'astro_consultancy_muhurat_type' => 'Muhurat type',
            'astro_consultancy_muhurat_date' => 'Date',
            'astro_consultancy_muhurat_time' => 'Time',
            'astro_consultancy_festival_significance' => 'Traditional significance',
            'astro_consultancy_document_types' => 'Document types',
            'astro_consultancy_video_type' => 'Video type',
            'astro_consultancy_consultant_profile_url' => 'Consultant profile',
            'astro_consultancy_related_service_actions' => 'Related services',
            'astro_consultancy_enable_consultant_linking' => 'Consultant directory linking',
            'astro_consultancy_knowledge_library_topics' => 'Knowledge library topics',
            'astro_consultancy_enable_live_qa' => 'Live Q&A sessions',
            'astro_consultancy_private_query_options' => 'Private query options',
            'astro_consultancy_ask_community' => 'Ask the community',
            'astro_consultancy_poll_question' => 'Poll question',
            'astro_consultancy_poll_options' => 'Poll options',
            'astro_consultancy_comment_settings' => 'Comments settings',
        ];
    }

    /**
     * @return list<string>
     */
    public static function astroConsultancyStructuredMetaKeys(): array
    {
        return array_keys(self::astroConsultancyDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function religionSpiritualityDetailMetaOrder(): array
    {
        return [
            'religion_spirituality_post_type' => 'Post type',
            'religion_spirituality_category' => 'Main category',
            'religion_spirituality_tradition' => 'Religious tradition',
            'religion_spirituality_target_audience' => 'Target audience',
            'religion_spirituality_scripture_name' => 'Scripture name',
            'religion_spirituality_scripture_chapter' => 'Scripture chapter',
            'religion_spirituality_scripture_verse' => 'Scripture verse',
            'religion_spirituality_scripture_reference' => 'Scripture reference',
            'religion_spirituality_moral_messages' => 'Moral message',
            'religion_spirituality_festival_name' => 'Festival name',
            'religion_spirituality_festival_date' => 'Festival date',
            'religion_spirituality_festival_historical_significance' => 'Historical significance',
            'religion_spirituality_festival_traditional_practices' => 'Traditional practices',
            'religion_spirituality_festival_celebration_methods' => 'Celebration methods',
            'religion_spirituality_festival_regional_variations' => 'Regional variations',
            'religion_spirituality_pilgrimage_name' => 'Pilgrimage name',
            'religion_spirituality_pilgrimage_location' => 'Pilgrimage location',
            'religion_spirituality_pilgrimage_best_time' => 'Best time to visit',
            'religion_spirituality_pilgrimage_history' => 'Pilgrimage history',
            'religion_spirituality_pilgrimage_facilities' => 'Pilgrimage facilities',
            'religion_spirituality_pilgrimage_travel_tips' => 'Travel tips',
            'religion_spirituality_pilgrimage_accommodation' => 'Accommodation',
            'religion_spirituality_place_of_worship_type' => 'Place of worship',
            'religion_spirituality_location_country' => 'Country',
            'religion_spirituality_location_state' => 'State',
            'religion_spirituality_location_district' => 'District',
            'religion_spirituality_location_city' => 'City',
            'religion_spirituality_location_gps' => 'GPS / map link',
            'religion_spirituality_meditation_topics' => 'Meditation & wellness topics',
            'religion_spirituality_community_service_activities' => 'Community service activities',
            'religion_spirituality_video_type' => 'Video type',
            'religion_spirituality_audio_type' => 'Audio type',
            'religion_spirituality_document_types' => 'Document types',
            'religion_spirituality_enable_digital_pilgrimage_guide' => 'Digital Pilgrimage Guide',
            'religion_spirituality_digital_pilgrimage_site_name' => 'Pilgrimage site name',
            'religion_spirituality_digital_pilgrimage_site_types' => 'Pilgrimage site types',
            'religion_spirituality_digital_pilgrimage_verified_info' => 'Verified information',
            'religion_spirituality_digital_pilgrimage_nearby_facilities' => 'Nearby facilities',
            'religion_spirituality_digital_pilgrimage_accommodation' => 'Pilgrimage accommodation',
            'religion_spirituality_digital_pilgrimage_local_businesses' => 'Local SoilnWater businesses',
            'religion_spirituality_digital_pilgrimage_map_url' => 'Map URL',
            'religion_spirituality_enable_festival_calendar' => 'Festival Calendar',
            'religion_spirituality_festival_calendar_event_types' => 'Calendar event types',
            'religion_spirituality_festival_calendar_event_name' => 'Calendar event name',
            'religion_spirituality_festival_calendar_event_date' => 'Calendar event date',
            'religion_spirituality_festival_calendar_description' => 'Calendar description',
            'religion_spirituality_festival_calendar_linked_article_url' => 'Linked article URL',
            'religion_spirituality_enable_community_service_directory' => 'Community Service Directory',
            'religion_spirituality_service_directory_opportunities' => 'Volunteer opportunities',
            'religion_spirituality_service_directory_organization' => 'Organization',
            'religion_spirituality_service_directory_when_where' => 'When & where',
            'religion_spirituality_service_directory_volunteer_notes' => 'Volunteer notes',
            'religion_spirituality_enable_wisdom_library' => 'Wisdom Library',
            'religion_spirituality_wisdom_themes' => 'Universal themes',
            'religion_spirituality_wisdom_traditions' => 'Traditions covered',
            'religion_spirituality_wisdom_collection_summary' => 'Collection summary',
            'religion_spirituality_related_service_actions' => 'Related community service',
            'religion_spirituality_ask_community' => 'Ask the community',
            'religion_spirituality_poll_question' => 'Poll question',
            'religion_spirituality_poll_options' => 'Poll options',
            'religion_spirituality_comment_settings' => 'Comments settings',
        ];
    }

    /**
     * @return list<string>
     */
    public static function religionSpiritualityStructuredMetaKeys(): array
    {
        return array_keys(self::religionSpiritualityDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function creativeCornerDetailMetaOrder(): array
    {
        return [
            'creative_corner_post_type' => 'Post type',
            'creative_corner_category' => 'Creative category',
            'creative_corner_target_audience' => 'Target audience',
            'creative_corner_creation_type' => 'Creation type',
            'creative_corner_mediums' => 'Medium used',
            'creative_corner_software_tools' => 'Software / tools used',
            'creative_corner_materials' => 'Materials used',
            'creative_corner_creation_date' => 'Creation date',
            'creative_corner_time_taken' => 'Time taken',
            'creative_corner_difficulty_level' => 'Difficulty level',
            'creative_corner_themes' => 'Theme',
            'creative_corner_location_country' => 'Country',
            'creative_corner_location_state' => 'State',
            'creative_corner_location_district' => 'District',
            'creative_corner_location_city' => 'City',
            'creative_corner_material_cost' => 'Material cost',
            'creative_corner_equipment_cost' => 'Equipment cost',
            'creative_corner_total_cost' => 'Total cost',
            'creative_corner_submit_to_competition' => 'Submit to competition',
            'creative_corner_competition_categories' => 'Competition categories',
            'creative_corner_available_for_sale' => 'Available for sale',
            'creative_corner_sale_price' => 'Price',
            'creative_corner_custom_orders_accepted' => 'Custom orders accepted',
            'creative_corner_limited_edition' => 'Limited edition',
            'creative_corner_shipping_available' => 'Shipping available',
            'creative_corner_commission_options' => 'Commission work',
            'creative_corner_copyright' => 'Copyright',
            'creative_corner_social_portfolio' => 'Portfolio',
            'creative_corner_social_instagram' => 'Instagram',
            'creative_corner_social_youtube' => 'YouTube',
            'creative_corner_social_website' => 'Website',
            'creative_corner_social_vendor_profile' => 'SoilnWater vendor profile',
            'creative_corner_video_type' => 'Video type',
            'creative_corner_audio_type' => 'Audio type',
            'creative_corner_document_types' => 'Document types',
            'creative_corner_ask_community' => 'Ask the community',
            'creative_corner_comment_settings' => 'Comment settings',
            'creative_corner_creative_licenses' => 'Creative license',
            'creative_corner_collaboration_roles' => 'Collaboration requests',
            'creative_corner_ai_used' => 'AI used',
            'creative_corner_ai_tool' => 'AI tool used',
            'creative_corner_ai_description' => 'AI assistance description',
        ];
    }

    /**
     * @return list<string>
     */
    public static function creativeCornerStructuredMetaKeys(): array
    {
        return array_keys(self::creativeCornerDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function myAreaDetailMetaOrder(): array
    {
        return [
            'my_area_activity_type' => 'Activity type',
            'my_area_topic_category' => 'Topic category',
            'my_area_impact_level' => 'Impact level',
            'my_area_affected_communities' => 'Affected community',
            'my_area_status_tracker' => 'Status tracker',
            'my_area_authorities' => 'Authority concerned',
            'my_area_suggested_solution' => 'Suggested solution',
            'my_area_hero_name' => 'Local hero name',
            'my_area_hero_location' => 'Local hero location',
            'my_area_hero_contribution' => 'Local hero contribution',
            'my_area_achievement_title' => 'Achievement title',
            'my_area_achievement_description' => 'Achievement description',
            'my_area_poll_question' => 'Poll question',
            'my_area_poll_options' => 'Poll options',
            'my_area_visibility' => 'Visibility',
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City/Town/Village',
            'location_locality' => 'Locality / Area',
        ];
    }

    /**
     * @return list<string>
     */
    public static function myAreaStructuredMetaKeys(): array
    {
        return array_keys(self::myAreaDetailMetaOrder());
    }

    /**
     * @return array<string, string>
     */
    public static function youthCornerDetailMetaOrder(): array
    {
        return [
            'youth_corner_category' => 'Main category',
            'youth_corner_content_type' => 'Content type',
            'youth_corner_age_group' => 'Age group',
            'youth_corner_occupation' => 'Occupation',
            'youth_corner_education_level' => 'Education level',
            'youth_corner_target_audience' => 'Target audience',
            'youth_corner_video_type' => 'Video type',
            'youth_corner_opportunity_types' => 'Opportunity types',
            'youth_corner_skills' => 'Skills',
            'youth_corner_career_area' => 'Career area',
            'youth_corner_startup_name' => 'Startup name',
            'youth_corner_startup_industry' => 'Industry',
            'youth_corner_business_idea' => 'Business idea',
            'youth_corner_funding_stage' => 'Funding stage',
            'youth_corner_startup_challenges' => 'Challenges faced',
            'youth_corner_startup_lessons' => 'Lessons learned',
            'youth_corner_project_title' => 'Project title',
            'youth_corner_project_category' => 'Project category',
            'youth_corner_project_description' => 'Project description',
            'youth_corner_project_outcome' => 'Project outcome',
            'youth_corner_themes' => 'Themes',
            'youth_corner_community_service' => 'Community service',
            'youth_corner_networking_options' => 'Networking options',
            'youth_corner_mentorship_requests' => 'Mentorship requests',
            'youth_corner_ask_community' => 'Ask the community',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City',
            'youth_corner_visibility' => 'Visibility',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerStructuredMetaKeys(): array
    {
        return array_keys(self::youthCornerDetailMetaOrder());
    }

    /**
     * @return list<string>
     */
    public static function youthCornerIntroMetaKeys(): array
    {
        return [
            'youth_corner_category',
            'youth_corner_content_type',
            'youth_corner_age_group',
            'youth_corner_occupation',
            'youth_corner_education_level',
            'youth_corner_target_audience',
            'youth_corner_video_type',
            'youth_corner_opportunity_types',
            'youth_corner_skills',
            'youth_corner_career_area',
            'youth_corner_startup_name',
            'youth_corner_startup_industry',
            'youth_corner_business_idea',
            'youth_corner_funding_stage',
            'youth_corner_startup_challenges',
            'youth_corner_startup_lessons',
            'youth_corner_project_title',
            'youth_corner_project_category',
            'youth_corner_project_description',
            'youth_corner_project_outcome',
            'youth_corner_themes',
            'youth_corner_community_service',
            'youth_corner_networking_options',
            'youth_corner_mentorship_requests',
            'youth_corner_ask_community',
            'location_state',
            'location_district',
            'location_city',
            'youth_corner_visibility',
        ];
    }

    /**
     * @return list<string>
     */
    public static function youthCornerEngagementStructuredMetaKeys(): array
    {
        return [
            'youth_corner_poll_question',
            'youth_corner_poll_options',
            'youth_corner_gallery',
            'youth_corner_achievements',
            'youth_corner_documents',
            'youth_corner_private_link_token',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function youthCornerAdminMetaOrder(): array
    {
        return array_merge(self::youthCornerDetailMetaOrder(), [
            'youth_corner_poll_question' => 'Poll question',
            'youth_corner_poll_options' => 'Poll options',
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedYouthCornerAdminMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::youthCornerAdminMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'youth_corner_category' && blank($value)) {
                    $value = $post->category;
                }

                if (in_array($key, [
                    'youth_corner_target_audience',
                    'youth_corner_opportunity_types',
                    'youth_corner_skills',
                    'youth_corner_themes',
                    'youth_corner_community_service',
                    'youth_corner_networking_options',
                    'youth_corner_mentorship_requests',
                    'youth_corner_poll_options',
                ], true) && is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                if ($key === 'youth_corner_visibility' && filled($value)) {
                    $value = CommunityContentTaxonomy::youthCornerVisibilitySettings()[(string) $value] ?? $value;
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedYouthCornerMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::youthCornerDetailMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'youth_corner_category' && blank($value)) {
                    $value = $post->category;
                }

                if (in_array($key, [
                    'youth_corner_target_audience',
                    'youth_corner_opportunity_types',
                    'youth_corner_skills',
                    'youth_corner_themes',
                    'youth_corner_community_service',
                    'youth_corner_networking_options',
                    'youth_corner_mentorship_requests',
                ], true) && is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                if ($key === 'youth_corner_visibility' && filled($value)) {
                    $value = CommunityContentTaxonomy::youthCornerVisibilitySettings()[(string) $value] ?? $value;
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
    }

    /**
     * @return list<string>
     */
    public static function studentCornerIntroMetaKeys(): array
    {
        return [
            'student_corner_category',
            'student_corner_content_type',
            'student_corner_profile_name',
            'student_corner_class_course',
            'student_corner_stream',
            'student_corner_institution_name',
            'student_corner_target_audience',
            'student_corner_video_type',
            'student_corner_study_material_types',
            'student_corner_career_guidance_topics',
            'student_corner_skills',
            'student_corner_social_impact_categories',
            'student_corner_mentorship_requests',
            'student_corner_submit_to_competition',
            'student_corner_competition_categories',
            'student_corner_ask_community',
            'location_state',
            'location_district',
            'location_city',
            'student_corner_visibility',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function studentCornerAdminMetaOrder(): array
    {
        return array_merge(self::studentCornerDetailMetaOrder(), [
            'student_corner_poll_question' => 'Poll question',
            'student_corner_poll_options' => 'Poll options',
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedStudentCornerAdminMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::studentCornerAdminMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'student_corner_category' && blank($value)) {
                    $value = $post->category;
                }

                if (in_array($key, [
                    'student_corner_target_audience',
                    'student_corner_study_material_types',
                    'student_corner_career_guidance_topics',
                    'student_corner_skills',
                    'student_corner_social_impact_categories',
                    'student_corner_mentorship_requests',
                    'student_corner_competition_categories',
                    'student_corner_poll_options',
                ], true) && is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                if ($key === 'student_corner_submit_to_competition') {
                    $value = $value ? 'Yes' : null;
                }

                if ($key === 'student_corner_visibility' && filled($value)) {
                    $value = CommunityContentTaxonomy::studentCornerVisibilitySettings()[(string) $value] ?? $value;
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
    }

    /**
     * @return array<string, string>
     */
    public static function studentCornerDetailMetaOrder(): array
    {
        return [
            'student_corner_category' => 'Main category',
            'student_corner_content_type' => 'Content type',
            'student_corner_profile_name' => 'Student name',
            'student_corner_class_course' => 'Class / course',
            'student_corner_stream' => 'Stream',
            'student_corner_institution_name' => 'Institution',
            'student_corner_target_audience' => 'Target audience',
            'student_corner_video_type' => 'Video type',
            'student_corner_study_material_types' => 'Study material type',
            'student_corner_career_guidance_topics' => 'Career guidance topics',
            'student_corner_scholarship_name' => 'Scholarship name',
            'student_corner_eligibility' => 'Eligibility',
            'student_corner_application_deadline' => 'Application deadline',
            'student_corner_official_website' => 'Official website',
            'student_corner_exam_name' => 'Exam name',
            'student_corner_preparation_strategy' => 'Preparation strategy',
            'student_corner_resources_used' => 'Resources used',
            'student_corner_marks_rank' => 'Marks / rank',
            'student_corner_lessons_learned' => 'Lessons learned',
            'student_corner_skills' => 'Skills',
            'student_corner_social_impact_categories' => 'Social impact',
            'student_corner_ask_community' => 'Ask the community',
            'student_corner_mentorship_requests' => 'Mentorship requests',
            'student_corner_submit_to_competition' => 'Competition entry',
            'student_corner_competition_categories' => 'Competition categories',
            'student_corner_project_title' => 'Project title',
            'student_corner_project_category' => 'Project category',
            'student_corner_project_description' => 'Project description',
            'student_corner_project_outcome' => 'Project outcome',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City',
            'student_corner_visibility' => 'Visibility',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerEngagementStructuredMetaKeys(): array
    {
        return [
            'student_corner_poll_question',
            'student_corner_poll_options',
            'student_corner_gallery',
            'student_corner_achievements',
            'student_corner_documents',
            'student_corner_private_link_token',
        ];
    }

    /**
     * @return list<string>
     */
    public static function studentCornerStructuredMetaKeys(): array
    {
        return array_keys(self::studentCornerDetailMetaOrder());
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedStudentCornerMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::studentCornerDetailMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'student_corner_category' && blank($value)) {
                    $value = $post->category;
                }

                if (in_array($key, [
                    'student_corner_target_audience',
                    'student_corner_study_material_types',
                    'student_corner_career_guidance_topics',
                    'student_corner_skills',
                    'student_corner_social_impact_categories',
                    'student_corner_mentorship_requests',
                    'student_corner_competition_categories',
                ], true) && is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                if ($key === 'student_corner_submit_to_competition') {
                    $value = $value ? 'Yes' : null;
                }

                if ($key === 'student_corner_visibility' && filled($value)) {
                    $value = CommunityContentTaxonomy::studentCornerVisibilitySettings()[(string) $value] ?? $value;
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
    }

    /**
     * @return array<string, string>
     */
    public static function seniorCitizensForumDetailMetaOrder(): array
    {
        return [
            'senior_citizens_forum_category' => 'Main category',
            'senior_citizens_forum_content_type' => 'Content type',
            'senior_citizens_forum_age_group' => 'Age group',
            'senior_citizens_forum_life_journey_categories' => 'Life journey category',
            'senior_citizens_forum_key_lessons' => 'Key lessons',
            'senior_citizens_forum_themes' => 'Themes',
            'senior_citizens_forum_advice_to_youth' => 'Advice to youth',
            'senior_citizens_forum_community_contributions' => 'Community contribution',
            'senior_citizens_forum_ask_community' => 'Ask the community',
            'senior_citizens_forum_visibility' => 'Visibility',
            'senior_citizens_forum_intergenerational_connections' => 'Intergenerational connections',
            'senior_citizens_forum_preserve_digital_legacy' => 'Digital legacy',
            'senior_citizens_forum_video_type' => 'Video type',
            'senior_citizens_forum_family_background' => 'Family background',
            'senior_citizens_forum_traditions' => 'Traditions',
            'senior_citizens_forum_cultural_practices' => 'Cultural practices',
            'senior_citizens_forum_family_values' => 'Family values',
            'location_country' => 'Country',
            'location_state' => 'State',
            'location_district' => 'District',
            'location_city' => 'City/Village',
        ];
    }

    /**
     * @return list<string>
     */
    public static function seniorCitizensForumStructuredMetaKeys(): array
    {
        return array_keys(self::seniorCitizensForumDetailMetaOrder());
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedSeniorCitizensForumMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::seniorCitizensForumDetailMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'senior_citizens_forum_category' && blank($value)) {
                    $value = $post->category;
                }

                if ($key === 'senior_citizens_forum_life_journey_categories' && is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                if ($key === 'senior_citizens_forum_key_lessons' && is_array($value)) {
                    $value = implode('; ', array_values(array_filter($value)));
                }

                if (in_array($key, ['senior_citizens_forum_themes', 'senior_citizens_forum_community_contributions', 'senior_citizens_forum_intergenerational_connections'], true) && is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                if ($key === 'senior_citizens_forum_visibility' && filled($value)) {
                    $value = \App\Support\CommunityContentTaxonomy::seniorCitizensForumVisibilitySettings()[(string) $value] ?? $value;
                }

                if ($key === 'senior_citizens_forum_preserve_digital_legacy') {
                    $value = $value ? 'Preserve as Digital Legacy' : null;
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
    }

    /**
     * @return list<string>
     */
    public static function womensWorldEngagementStructuredMetaKeys(): array
    {
        return [
            'womens_world_business_name',
            'womens_world_business_category',
            'womens_world_website_url',
            'womens_world_vendor_profile_url',
            'womens_world_ask_community',
            'womens_world_poll_question',
            'womens_world_poll_options',
            'womens_world_support_requests',
            'womens_world_community_groups',
            'womens_world_visibility',
            'womens_world_private_link_token',
            'womens_world_useful_websites',
            'womens_world_government_schemes',
            'womens_world_training_programs',
            'womens_world_scholarships',
            'womens_world_support_organizations',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function womensWorldAdminMetaOrder(): array
    {
        return array_merge(self::womensWorldDetailMetaOrder(), [
            'womens_world_visibility' => 'Visibility',
            'womens_world_business_name' => 'Business name',
            'womens_world_business_category' => 'Business category',
            'womens_world_website_url' => 'Website / profile',
            'womens_world_vendor_profile_url' => 'Vendor profile',
            'womens_world_ask_community' => 'Ask the community',
            'womens_world_poll_question' => 'Poll question',
            'womens_world_poll_options' => 'Poll options',
            'womens_world_support_requests' => 'Support requests',
            'womens_world_community_groups' => 'Community groups',
            'womens_world_useful_websites' => 'Useful websites',
            'womens_world_government_schemes' => 'Government schemes',
            'womens_world_training_programs' => 'Training programs',
            'womens_world_scholarships' => 'Scholarships',
            'womens_world_support_organizations' => 'Support organizations',
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedWomensWorldAdminMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::womensWorldAdminMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'womens_world_category' && blank($value)) {
                    $value = $post->category;
                }

                if ($key === 'womens_world_visibility' && filled($value)) {
                    $value = \App\Support\CommunityContentTaxonomy::womensWorldVisibilitySettings()[(string) $value] ?? $value;
                }

                if (is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedWomensWorldMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::womensWorldDetailMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'womens_world_category' && blank($value)) {
                    $value = $post->category;
                }

                if (is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
    }

    /**
     * @return list<string>
     */
    public static function businessEngagementStructuredMetaKeys(): array
    {
        return [
            'business_ask_community',
            'business_useful_links',
            'business_government_schemes',
            'business_training_programs',
            'business_industry_resources',
            'business_contact_options',
            'business_poll_question',
            'business_poll_options',
            'business_gallery',
            'business_documents',
            'business_video_type',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedBusinessMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::businessDetailMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'business_category' && blank($value)) {
                    $value = $post->category;
                }

                if (is_array($value)) {
                    $value = implode(', ', array_values(array_filter($value)));
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
    }

    /**
     * @return list<string>
     */
    public static function awarenessEngagementStructuredMetaKeys(): array
    {
        return [
            'awareness_call_to_action',
            'awareness_action_items',
            'awareness_allow_campaign_join',
            'awareness_has_event',
            'awareness_event_type',
            'awareness_event_date',
            'awareness_event_venue',
            'awareness_event_time',
            'awareness_event_organizer',
            'awareness_social_impact_categories',
            'awareness_allow_cause_support',
            'awareness_allow_pledges',
            'awareness_pledge_options',
            'awareness_poll_question',
            'awareness_impact_trees_planted',
            'awareness_impact_volunteers_joined',
            'awareness_impact_people_reached',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public static function orderedAwarenessMetaForDisplay(\App\Models\CommunityPost $post): \Illuminate\Support\Collection
    {
        return collect(self::awarenessDetailMetaOrder())
            ->mapWithKeys(function (string $label, string $key) use ($post): array {
                $value = data_get($post->meta, $key);

                if ($key === 'awareness_category' && blank($value)) {
                    $value = $post->category;
                }

                if ($key === 'awareness_target_audience' && is_array($value)) {
                    $value = implode(', ', $value);
                }

                if (in_array($key, ['awareness_campaign_start_date', 'awareness_campaign_end_date'], true) && filled($value)) {
                    $value = \Illuminate\Support\Carbon::parse($value)->format('j F Y');
                }

                return [$key => $value];
            })
            ->filter(fn (mixed $value): bool => filled($value));
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
            'story_type',
            'poetry_type',
            'sub_category',
            'poetry_themes',
            'poetry_target_audience',
            'poetry_inspiration',
            'poetry_part_of_series',
            'poetry_series_name',
            'poetry_series_part',
            'poetry_audio',
            'poem_language',
            'dedication',
            'reading_time',
            'story_moral_takeaway',
            'story_main_characters',
            'story_character_type',
            'story_place_type',
            'story_place_names',
            'story_time_period',
            'story_language',
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
            'story_type' => 'Story type',
            'poetry_type' => 'Poetry type',
            'sub_category' => 'Sub category',
            'story_moral_takeaway' => 'Moral / takeaway',
            'story_main_characters' => 'Main characters',
            'story_character_type' => 'Character type',
            'story_place_type' => 'Story location',
            'story_place_names' => 'Place names',
            'story_time_period' => 'Time period',
            'story_language' => 'Language',
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
            'dedication' => 'Dedication',
            'subject_name' => 'Subject name',
            'subject_field' => 'Subject field',
            'time_period' => 'Time period',
            'key_achievements' => 'Key achievements',
            'life_stage' => 'Life stage',
            'timeline_period' => 'Timeline',
            'lessons_learned' => 'Lessons learned',
            'autobiography_type' => 'Autobiography type',
            'school_name' => 'School name',
            'child_age_range' => 'Age range',
            'grade_level' => 'Grade / class',
            'child_share_type' => 'Share type',
            'child_first_name' => "Child's first name",
            'child_age_group' => 'Age group',
            'child_grade_level' => 'Grade / class',
            'child_school_name' => 'School name',
            'parent_name' => 'Parent / guardian name',
            'parent_mobile' => 'Parent mobile',
            'parent_email' => 'Parent email',
            'parent_relationship' => 'Relationship',
            'parent_approved' => 'Parent consent confirmed',
            'child_parent_consent_identity' => 'Identity consent',
            'child_parent_consent_publication' => 'Publication consent',
            'child_parent_consent_original' => 'Original work consent',
            'childrens_corner_submitted_through' => 'Submitted through',
            'childrens_corner_school_competition_entry' => 'School competition entry',
            'childrens_corner_city' => 'City',
            'childrens_corner_district' => 'District',
            'childrens_corner_state' => 'State',
            'childrens_corner_talent_categories' => 'Talent categories',
            'childrens_corner_achievement' => 'Achievement / recognition',
            'childrens_corner_comments_moderated' => 'Comments moderated',
            'childrens_corner_child_friendly_reactions' => 'Child-friendly reactions',
            'childrens_corner_privacy_setting' => 'Privacy setting',
            'childrens_corner_safety_no_address' => 'Safety: no personal address',
            'childrens_corner_safety_no_harmful' => 'Safety: no harmful content',
            'childrens_corner_safety_no_copyright' => 'Safety: no copyrighted material',
            'childrens_corner_safety_no_inappropriate_media' => 'Safety: no inappropriate media',
            'childrens_corner_safety_confirmed' => 'Safety declaration confirmed',
            'childrens_corner_project_description' => 'Project description',
            'childrens_corner_themes' => 'Themes',
            'awareness_category' => 'Main category',
            'awareness_type' => 'Awareness type',
            'awareness_level' => 'Awareness level',
            'awareness_campaign_start_date' => 'Campaign start date',
            'awareness_campaign_end_date' => 'Campaign end date',
            'awareness_video_type' => 'Video type',
            'awareness_posted_by' => 'Posted by',
            'awareness_organization_name' => 'Organization name',
            'awareness_target_audience' => 'Target audience',
            'business_category' => 'Main category',
            'business_content_type' => 'Business content type',
            'business_target_audience' => 'Target audience',
            'business_name' => 'Business name',
            'business_profile_type' => 'Business type',
            'business_industry' => 'Industry',
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
            'local_voice_type' => 'Voice type',
            'local_voice_category' => 'Main category',
            'community_issue_category' => 'Issue category',
            'community_issue_type' => 'Issue type',
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
    public static function structuredLocationFields(?string $contentType = null): array
    {
        $requiresArea = in_array($contentType, ['awareness', 'local-voices'], true);
        $cityLabel = $contentType === 'local-voices' ? 'City/Town/Village' : 'City';
        $localityLabel = match ($contentType) {
            'awareness' => 'Area',
            'local-voices' => 'Locality / Area',
            default => 'Locality',
        };

        return [
            self::text('location_country', 'Country', 120, true),
            self::text('location_state', 'State', 120, true),
            self::text('location_district', 'District', 120, true),
            self::text('location_city', $cityLabel, 120, true),
            self::text('location_locality', $localityLabel, 120, $requiresArea),
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

        if ($contentType === 'stories') {
            return [
                self::select('story_type', 'Story type', CommunityContentTaxonomy::storyTypes(), true),
                self::select('story_language', 'Language', CommunityContentTaxonomy::storyLanguages(), true, 'col-md-6'),
                self::textarea('story_moral_takeaway', 'Moral / takeaway', 1000, false, 'Never underestimate the power of community cooperation.'),
                self::textarea('story_main_characters', 'Main characters', 2000, false, 'Ramesh Kumar, Village Head, School Teacher'),
                self::select('story_character_type', 'Character type', CommunityContentTaxonomy::storyCharacterTypes(), false, 'col-md-6'),
                self::select('story_place_type', 'Story location', CommunityContentTaxonomy::storyPlaceTypes(), false, 'col-md-6'),
                self::text('story_place_names', 'Place names', 500, false, 'Dehradun, Uttarakhand, India'),
                self::select('story_time_period', 'Time period', CommunityContentTaxonomy::storyTimePeriods(), false, 'col-md-6'),
            ];
        }

        if ($contentType === 'poetry') {
            return [
                self::select('poetry_type', 'Poetry type', CommunityContentTaxonomy::poetryTypes(), true),
                self::select('poem_language', 'Poem language', ['Hindi', 'English', 'Urdu', 'Regional', 'Multilingual'], true, 'col-md-6'),
                self::text('dedication', 'Dedication', 160, false, 'Optional dedication line'),
                self::text('reading_time', 'Estimated reading time (minutes)', 10, false, 'e.g. 3'),
            ];
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
