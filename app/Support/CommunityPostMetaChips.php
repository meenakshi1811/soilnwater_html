<?php

namespace App\Support;

use App\Models\CommunityPost;

class CommunityPostMetaChips
{
    /**
     * @return list<array{icon: string, value: string, title: string}>
     */
    public static function forDisplay(CommunityPost $post): array
    {
        $languageLabels = [
            'en' => 'English',
            'hi' => 'Hinglish',
            'hindi' => 'Hindi',
            'ur' => 'Urdu',
            'pa' => 'Punjabi',
            'bn' => 'Bengali',
            'mr' => 'Marathi',
            'gu' => 'Gujarati',
            'ta' => 'Tamil',
            'te' => 'Telugu',
        ];

        $chips = [];
        $push = static function (string $icon, ?string $value, string $title) use (&$chips): void {
            if (filled($value)) {
                $chips[] = ['icon' => $icon, 'value' => $value, 'title' => $title];
            }
        };

        $editorLanguage = data_get($post->meta, 'editor_language', 'en');
        $wordCount = str_word_count(trim(strip_tags((string) $post->body)));
        $readingMinutes = $wordCount > 0 ? max(1, (int) ceil($wordCount / 220)) : null;

        $push('fa-layer-group', $post->typeLabel(), 'Post type');

        match ($post->content_type) {
            'articles' => self::articleChips($post, $push),
            'poetry' => self::poetryChips($post, $push),
            'stories' => self::storyChips($post, $push),
            'news' => self::newsChips($post, $push),
            'autobiography' => self::autobiographyChips($post, $push),
            default => null,
        };

        if ($post->isAwarenessPost()) {
            $push('fa-bullhorn', $post->awarenessCategoryLabel(), 'Category');
            $push('fa-lightbulb', data_get($post->meta, 'awareness_type'), 'Awareness type');
            $push('fa-signal', data_get($post->meta, 'awareness_level'), 'Awareness level');
            $push('fa-calendar-days', $post->awarenessCampaignPeriodForDisplay(), 'Campaign period');
        }

        if ($post->isBusinessPost()) {
            $push('fa-briefcase', $post->businessCategoryLabel(), 'Category');
            $push('fa-file-lines', data_get($post->meta, 'business_content_type'), 'Content type');
            $push('fa-chart-line', data_get($post->meta, 'business_stage'), 'Business stage');
            $push('fa-industry', data_get($post->meta, 'business_industry'), 'Industry');
        }

        if ($post->isWomensWorldPost()) {
            $push('fa-folder-open', $post->category, 'Category');
            $push('fa-file-lines', data_get($post->meta, 'womens_world_content_type'), 'Content type');
        }

        if ($post->isSeniorCitizensForumPost()) {
            $push('fa-folder-open', $post->category, 'Category');
            $push('fa-file-lines', data_get($post->meta, 'senior_citizens_forum_content_type'), 'Content type');
        }

        if ($post->isStudentCornerPost()) {
            $push('fa-graduation-cap', data_get($post->meta, 'student_corner_category'), 'Category');
            $push('fa-file-lines', data_get($post->meta, 'student_corner_content_type'), 'Content type');
        }

        if ($post->isYouthCornerPost()) {
            $push('fa-user-group', data_get($post->meta, 'youth_corner_category'), 'Category');
            $push('fa-file-lines', data_get($post->meta, 'youth_corner_content_type'), 'Content type');
        }

        if ($post->isLocalVoicesPost()) {
            $push('fa-microphone', data_get($post->meta, 'local_voice_type'), 'Voice type');
            $push('fa-folder-open', data_get($post->meta, 'local_voice_category'), 'Category');
        }

        if ($post->isMyAreaPost()) {
            $push('fa-map-location-dot', $post->myAreaActivityType(), 'Activity type');
            $push('fa-folder-open', $post->myAreaTopicCategory(), 'Topic');
            $push('fa-gauge-high', data_get($post->meta, 'my_area_impact_level'), 'Impact level');
            $push('fa-circle-check', data_get($post->meta, 'my_area_status_tracker'), 'Status');
        }

        if ($post->isCommunityIssuesPost()) {
            $push('fa-folder-open', data_get($post->meta, 'community_issue_category'), 'Category');
            $push('fa-triangle-exclamation', data_get($post->meta, 'community_issue_type'), 'Issue type');
            $push('fa-gauge-high', data_get($post->meta, 'community_issue_severity'), 'Severity');
            $push('fa-circle-check', data_get($post->meta, 'community_issue_status_tracker'), 'Status');
        }

        if ($post->isAgriculturePost()) {
            $push('fa-seedling', $post->agricultureShareTypeLabel(), 'Share type');
            $push('fa-folder-open', $post->agricultureCategoryLabel(), 'Category');
            $push('fa-leaf', data_get($post->meta, 'agriculture_crop_name'), 'Crop');
            $push('fa-droplet', data_get($post->meta, 'agriculture_irrigation_method'), 'Irrigation');
        }

        if ($post->isEnvironmentPost()) {
            $push('fa-earth-americas', $post->environmentPostTypeLabel(), 'Post type');
            $push('fa-folder-open', $post->environmentCategoryLabel(), 'Category');
            $push('fa-mountain', data_get($post->meta, 'environment_natural_feature_name'), 'Feature');
            $push('fa-triangle-exclamation', data_get($post->meta, 'environment_issue_type'), 'Issue type');
        }

        if ($post->isScienceTechnologyPost()) {
            $push('fa-flask', data_get($post->meta, 'science_technology_post_type'), 'Post type');
            $push('fa-folder-open', data_get($post->meta, 'science_technology_category', $post->category), 'Category');
        }

        if ($post->isAstroConsultancyPost()) {
            $push('fa-star', $post->astroConsultancyPostTypeLabel(), 'Post type');
            $push('fa-folder-open', $post->astroConsultancyCategoryLabel(), 'Category');
            $push('fa-moon', data_get($post->meta, 'astro_consultancy_zodiac_sign'), 'Zodiac sign');
            $push('fa-calendar', data_get($post->meta, 'astro_consultancy_horoscope_period'), 'Horoscope period');
        }

        if ($post->isReligionSpiritualityPost()) {
            $push('fa-hands-praying', data_get($post->meta, 'religion_spirituality_post_type'), 'Post type');
            $push('fa-folder-open', data_get($post->meta, 'religion_spirituality_category', $post->category), 'Category');
            $push('fa-book', data_get($post->meta, 'religion_spirituality_tradition'), 'Tradition');
        }

        if ($post->isCreativeCornerPost()) {
            $push('fa-palette', data_get($post->meta, 'creative_corner_post_type'), 'Post type');
            $push('fa-folder-open', data_get($post->meta, 'creative_corner_category', $post->category), 'Category');
        }

        if ($post->isCompetitionsPost()) {
            $push('fa-trophy', data_get($post->meta, 'competition_type'), 'Competition type');
            $push('fa-folder-open', data_get($post->meta, 'competition_category', $post->category), 'Category');
        }

        if ($post->isChildrensCornerPost()) {
            $push('fa-child', $post->childrensCornerShareType(), 'Share type');
            $push('fa-shield-halved', $post->childrensCornerPrivacyLabel(), 'Privacy');
            $push('fa-folder-open', data_get($post->meta, 'child_age_group'), 'Age group');
        }

        if ($post->isReportContent()) {
            $push('fa-file-lines', data_get($post->meta, 'report_type', $post->category), 'Report type');
            $push('fa-circle-check', $post->reportStatus(), 'Status');
            $push('fa-shield-halved', $post->reportTrustScore() . '% trust', 'Trust score');
        }

        if (! in_array($post->content_type, ['articles', 'poetry', 'stories', 'news', 'autobiography'], true)
            && ! $post->isAwarenessPost()
            && ! $post->isBusinessPost()
            && filled($post->category)
        ) {
            $push('fa-folder-open', $post->category, 'Category');
        }

        if ($readingMinutes !== null) {
            $push('fa-clock', $readingMinutes . ' min', 'Reading time');
        }

        if (filled($post->published_at)) {
            $push('fa-calendar', $post->published_at->format('M j, Y'), 'Published');
        }

        $poemLanguage = data_get($post->meta, 'poem_language');
        $storyLanguage = data_get($post->meta, 'story_language');
        $displayLanguage = $poemLanguage
            ?: $storyLanguage
            ?: ($languageLabels[$editorLanguage] ?? null);

        if (filled($displayLanguage)) {
            $push('fa-language', $displayLanguage, 'Language');
        }

        return $chips;
    }

    /**
     * @param  callable(string, ?string, string): void  $push
     */
    private static function articleChips(CommunityPost $post, callable $push): void
    {
        $push('fa-file-lines', data_get($post->meta, 'article_type'), 'Article type');
        $push('fa-folder-open', $post->category, 'Category');
    }

    /**
     * @param  callable(string, ?string, string): void  $push
     */
    private static function poetryChips(CommunityPost $post, callable $push): void
    {
        $push('fa-feather', data_get($post->meta, 'poetry_type'), 'Poetry type');
        $push('fa-folder-open', $post->category, 'Category');
        $push('fa-tag', data_get($post->meta, 'sub_category'), 'Sub-category');
    }

    /**
     * @param  callable(string, ?string, string): void  $push
     */
    private static function storyChips(CommunityPost $post, callable $push): void
    {
        $push('fa-book-open', data_get($post->meta, 'story_type'), 'Story type');
        $push('fa-folder-open', $post->category, 'Category');
        $push('fa-hourglass-half', data_get($post->meta, 'story_time_period'), 'Time period');
    }

    /**
     * @param  callable(string, ?string, string): void  $push
     */
    private static function newsChips(CommunityPost $post, callable $push): void
    {
        $push('fa-newspaper', data_get($post->meta, 'news_type'), 'News type');
        $push('fa-folder-open', $post->category, 'Category');
        $push('fa-flag', data_get($post->meta, 'news_priority'), 'Priority');
        $push('fa-bolt', data_get($post->meta, 'news_impact_level'), 'Impact level');
    }

    /**
     * @param  callable(string, ?string, string): void  $push
     */
    private static function autobiographyChips(CommunityPost $post, callable $push): void
    {
        $push('fa-user-pen', data_get($post->meta, 'autobiography_type'), 'Autobiography type');
        $push('fa-folder-open', $post->category, 'Category');
    }
}
