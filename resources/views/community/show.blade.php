@extends('frontend.layouts.app')

@section('meta_title', $post->seoTitle())
@section('meta_description', $post->seoDescription())
@section('meta_url', $post->publicUrl())
@section('meta_canonical', $post->publicUrl())
@section('meta_keywords', $post->seoKeywords())
@section('meta_image', $post->seoImageUrl())
@section('meta_type', 'article')
@if($post->shouldBlockSearchIndexing())
@section('meta_robots', 'noindex, nofollow')
@endif

@if($post->status === \App\Models\CommunityPost::STATUS_PUBLISHED)
@push('head')
@if($post->published_at)
<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
@endif
@if($post->updated_at)
<meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
@endif
<meta property="article:section" content="{{ $post->typeLabel() }}">
<meta property="article:tag" content="{{ $post->category }}">
<meta property="article:author" content="{{ $post->authorDisplayName() }}">
<script type="application/ld+json">{!! json_encode($post->structuredData(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
@endif

@push('styles')
<style>
    .community-featured-gallery {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .community-featured-gallery--single {
        grid-template-columns: 1fr;
    }
    .community-featured-gallery-item img {
        display: block;
        height: 100%;
        max-height: 420px;
        object-fit: cover;
        width: 100%;
    }
    @@media (max-width: 767.98px) {
        .community-featured-gallery {
            grid-template-columns: 1fr;
        }
    }

    .community-post-back-wrap {
        box-sizing: border-box;
        margin: 0 0 0.5rem;
        max-width: none;
        padding: 0 clamp(16px, 2vw, 32px);
        text-align: left;
        width: 100%;
    }

    .community-post-back {
        align-items: center;
        color: rgba(255, 255, 255, 0.92);
        display: inline-flex;
        font-size: 0.9rem;
        font-weight: 600;
        gap: 0.4rem;
        margin-bottom: 1rem;
        text-decoration: none;
    }

    .community-post-back:hover {
        color: #fff;
        text-decoration: underline;
    }

    .community-post-banner-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
        margin-bottom: 0.25rem;
    }

    .community-post-banner-tag {
        border-radius: 999px;
        font-size: 0.95rem;
        font-weight: 700;
        padding: 0.5rem 1rem;
    }

    .report-trust-score {
        background: linear-gradient(135deg, #f8fafc 0%, #eef6ff 100%);
        border: 1px solid #cfe0f5;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
    }

    .report-trust-score--high {
        border-color: #86efac;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    }

    .report-trust-score--medium {
        border-color: #fcd34d;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }

    .report-trust-score--low {
        border-color: #cbd5e1;
    }

    .community-detail-card {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
        border: 1px solid rgba(15, 47, 85, 0.1);
        border-radius: 1.15rem;
        box-shadow: 0 8px 24px rgba(15, 47, 85, 0.05);
        overflow: hidden;
        padding: 1.15rem 1.25rem 1.25rem;
    }

    .community-detail-card__head {
        align-items: flex-start;
        border-bottom: 1px solid rgba(15, 47, 85, 0.08);
        display: flex;
        gap: 0.85rem;
        margin-bottom: 1rem;
        padding-bottom: 0.95rem;
    }

    .community-detail-card__icon {
        align-items: center;
        background: linear-gradient(135deg, #e8f3fb 0%, #e7f6ef 100%);
        border: 1px solid rgba(27, 108, 168, 0.18);
        border-radius: 0.85rem;
        color: #1b6ca8;
        display: inline-flex;
        flex-shrink: 0;
        font-size: 0.95rem;
        height: 2.35rem;
        justify-content: center;
        width: 2.35rem;
    }

    .community-detail-card__title {
        color: #0f2744;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.25;
        margin: 0;
    }

    .community-detail-card__subtitle {
        color: #64748b;
        font-size: 0.84rem;
        margin: 0.2rem 0 0;
    }

    .community-detail-grid {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }

    .community-detail-item {
        background: #fff;
        border: 1px solid rgba(15, 47, 85, 0.08);
        border-radius: 0.85rem;
        padding: 0.8rem 0.9rem;
    }

    .community-detail-item__label {
        color: #64748b;
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }

    .community-detail-item__value {
        color: #1e293b;
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.45;
    }

    .community-detail-location-note {
        color: #475569;
        font-size: 0.92rem;
        margin: 0;
    }

    .community-detail-location-type {
        align-items: center;
        background: rgba(27, 108, 168, 0.08);
        border-radius: 999px;
        color: #0f4c75;
        display: inline-flex;
        font-size: 0.82rem;
        font-weight: 700;
        gap: 0.35rem;
        margin-bottom: 0.75rem;
        padding: 0.35rem 0.75rem;
    }

    .community-detail-map {
        border: 1px solid rgba(15, 47, 85, 0.1);
        border-radius: 0.9rem;
        margin-top: 1rem;
        overflow: hidden;
    }

    .community-detail-list {
        display: grid;
        gap: 0.65rem;
        margin: 0;
    }

    .community-detail-list__row {
        background: #fff;
        border: 1px solid rgba(15, 47, 85, 0.08);
        border-radius: 0.85rem;
        display: grid;
        gap: 0.2rem 1rem;
        grid-template-columns: minmax(120px, 34%) 1fr;
        padding: 0.8rem 0.95rem;
    }

    .community-detail-list__row dt {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        margin: 0;
        text-transform: uppercase;
    }

    .community-detail-list__row dd {
        color: #1e293b;
        font-size: 0.94rem;
        line-height: 1.5;
        margin: 0;
    }

    @@media (max-width: 575.98px) {
        .community-detail-list__row {
            grid-template-columns: 1fr;
        }
    }

    .report-trust-score__header {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .report-trust-score__kicker {
        color: #0f766e;
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .report-trust-score__title {
        font-size: 1.15rem;
        font-weight: 700;
    }

    .report-trust-score__value-wrap {
        align-items: center;
        background: #fff;
        border: 2px solid currentColor;
        border-radius: 999px;
        color: #0f766e;
        display: inline-flex;
        min-width: 5.5rem;
        justify-content: center;
        padding: 0.35rem 1rem;
    }

    .report-trust-score--high .report-trust-score__value-wrap {
        color: #15803d;
    }

    .report-trust-score--medium .report-trust-score__value-wrap {
        color: #b45309;
    }

    .report-trust-score__value {
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1;
    }

    .report-trust-score__factor {
        align-items: flex-start;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
        display: grid;
        gap: 0.75rem;
        grid-template-columns: auto 1fr auto;
        padding: 0.75rem 0;
    }

    .report-trust-score__factor-icon {
        color: #94a3b8;
        padding-top: 0.1rem;
    }

    .report-trust-score__factor.is-met .report-trust-score__factor-icon {
        color: #16a34a;
    }

    .report-trust-score__factor-points {
        color: #475569;
        font-size: 0.85rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .report-trust-score--compact {
        padding: 0.9rem 1rem;
    }

    .report-trust-score--compact .report-trust-score__title {
        font-size: 1rem;
    }

    .report-trust-score--compact .report-trust-score__value {
        font-size: 1.1rem;
    }

    .community-post-card__badge--trust-score {
        background: rgba(15, 118, 110, 0.92);
        color: #fff;
    }

    .report-community-panel {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #d7e6f5;
        border-radius: 18px;
        padding: 1.5rem;
    }

    .my-area-community-panel {
        background: linear-gradient(180deg, #f8fdf9 0%, #ffffff 100%);
        border-color: rgba(27, 67, 50, 0.14);
    }

    .my-area-community-panel .report-community-panel__kicker {
        color: #1b4332;
    }

    @include('community.partials.community-issues-styles')
    @include('community.partials.agriculture-styles')
    @include('community.partials.environment-styles')

    .report-community-panel__header {
        display: flex;
        gap: 1.25rem;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .report-community-panel__kicker {
        color: #0f766e;
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
    }

    .report-community-panel__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .report-community-panel__stat {
        background: #fff;
        border: 1px solid #dbe7f3;
        border-radius: 999px;
        color: #475569;
        font-size: 0.82rem;
        padding: 0.35rem 0.8rem;
    }

    .report-community-panel__grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .report-community-action-card {
        background: #fff;
        border: 1px solid #e2ebf5;
        border-radius: 16px;
        display: flex;
        gap: 1rem;
        min-height: 100%;
        padding: 1.1rem;
    }

    .report-community-action-card--wide {
        grid-column: 1 / -1;
    }

    .report-community-action-card__icon {
        align-items: center;
        border-radius: 14px;
        color: #fff;
        display: inline-flex;
        flex-shrink: 0;
        font-size: 1.1rem;
        height: 48px;
        justify-content: center;
        width: 48px;
    }

    .report-community-action-card__icon--support { background: linear-gradient(135deg, #0f766e, #14b8a6); }
    .report-community-action-card__icon--agree { background: linear-gradient(135deg, #166534, #22c55e); }
    .report-community-action-card__icon--follow { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
    .report-community-action-card__icon--evidence { background: linear-gradient(135deg, #b45309, #f59e0b); }

    .report-community-action-card__body {
        flex: 1;
        min-width: 0;
    }

    @@media (max-width: 991.98px) {
        .report-community-panel__grid {
            grid-template-columns: 1fr;
        }
    }

    [data-community-body-protected] {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
</style>
@endpush

@section('content')
@php
    $engagement = $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => [], 'followed_author_ids' => []];
    $isSaved = auth()->check() && in_array($post->id, $engagement['saved_post_ids'] ?? [], true);
    $subscriptionContentType = $post->subscriptionContentType();
    $subscriptionCategory = $post->subscriptionCategory();
    $isCategorySubscribed = auth()->check() && collect($engagement['subscribed_categories'] ?? [])->contains(
        fn (array $subscription): bool => ($subscription['content_type'] ?? null) === $subscriptionContentType
            && ($subscription['category'] ?? null) === $subscriptionCategory
    );
    $isFollowingAuthor = auth()->check()
        && $post->user_id
        && auth()->id() !== $post->user_id
        && in_array($post->user_id, $engagement['followed_author_ids'] ?? [], true);
    $followedTopics = collect($engagement['followed_topics'] ?? [])->map(fn ($topic) => \App\Models\CommunityTopicFollow::normalizeTopic((string) $topic))->all();
    $isArticlePost = $post->content_type === 'articles';
    $isNewsPost = $post->content_type === 'news';
    $isPortalPost = \App\Support\CommunityContentTaxonomy::usesContentPortal($post->content_type);
    $moveDetailExtrasToSidebar = $isPortalPost && ($isArticlePost || $post->content_type === 'reports');
    $coverUrl = $post->featuredImageUrl();
@endphp
<div
    class="about-page about-page--community-post{{ $isPortalPost ? ' about-page--news-detail' : '' }}"
    data-article-font-root
    data-article-font-step="2"
>
    @if(!empty($preview) || $post->isPendingApproval())
        <div class="alert alert-warning text-center rounded-0 mb-0 border-0">
            @if(!empty($preview))
                Admin preview — this is how the post will appear on the frontend after approval.
            @else
                This post is awaiting admin approval and is not visible on the public community hub yet.
            @endif
        </div>
    @elseif($post->status === \App\Models\CommunityPost::STATUS_DECLINED)
        <div class="alert alert-danger text-center rounded-0 mb-0 border-0">
            This post was rejected by admin.
            @if(filled($post->review_note))
                <span class="d-block small mt-1">Note: {{ $post->review_note }}</span>
            @endif
        </div>
    @elseif($post->isArchived())
        <div class="alert alert-secondary text-center rounded-0 mb-0 border-0">
            This post has been archived and is no longer visible on the public community hub.
        </div>
    @elseif($post->status === \App\Models\CommunityPost::STATUS_DRAFT)
        <div class="alert alert-secondary text-center rounded-0 mb-0 border-0">
            This post is saved as a draft and is not visible on the public community hub yet.
        </div>
    @endif
    @if($isPortalPost)
        @php
            $activeHub = $activeHub ?? \App\Support\CommunityContentTaxonomy::hubSectionForType($post->content_type);
            $activeCategory = $activeCategory ?? ($post->category ?? '');
            $portalType = $post->content_type;
        @endphp
        <div class="community-hub community-hub--news">
            <div class="community-news-portal">
                <div class="community-news-portal__layout">
                    @include('community.partials.news-portal-sidebar', [
                        'activeHub' => $activeHub,
                        'activeCategory' => $activeCategory,
                        'portalType' => $portalType,
                    ])
                    <main class="community-news-main community-news-main--detail">
                        @include('community.partials.news-show-header', [
                            'post' => $post,
                            'activeHub' => $activeHub,
                            'portalType' => $portalType,
                            'isFollowingAuthor' => $isFollowingAuthor,
                            'isSaved' => $isSaved,
                            'isCategorySubscribed' => $isCategorySubscribed,
                            'subscriptionContentType' => $subscriptionContentType,
                            'subscriptionCategory' => $subscriptionCategory,
                        ])
                        <section class="community-news-detail__body sec">
                        @if($post->content_type === 'autobiography')
                            @include('community.partials.autobiography-show-sections', ['post' => $post, 'portalLayout' => true])
                        @endif
                        @include('community.partials.life-learning-portal-sections', ['post' => $post, 'placement' => 'before'])
                        @include('community.partials.community-hub-portal-sections', [
                            'post' => $post,
                            'placement' => 'before',
                            'awarenessEngagement' => $awarenessEngagement ?? null,
                            'awarenessPledgeCounts' => $awarenessPledgeCounts ?? [],
                            'businessEngagement' => $businessEngagement ?? null,
                            'environmentEngagement' => $environmentEngagement ?? null,
                            'astroConsultancyEngagement' => $astroConsultancyEngagement ?? null,
                            'localVoiceEngagement' => $localVoiceEngagement ?? null,
                            'reportEngagement' => $reportEngagement ?? null,
                        ])
                        @include('community.partials.news-show-main', [
                            'post' => $post,
                            'isSaved' => $isSaved,
                            'followedTopics' => $followedTopics,
                        ])
                        @if($post->content_type === 'poetry')
                            @include('community.partials.poetry-show-sections', ['post' => $post, 'portalLayout' => true])
                        @endif
                        @if($post->isScienceTechnologyPost())
                            @include('community.partials.science-technology-show-sections', ['post' => $post, 'portalLayout' => true])
                        @endif
                        @if($isArticlePost)
                            @include('community.partials.articles-show-sections', ['post' => $post, 'portalLayout' => true])
                        @endif
                        @if($post->content_type === 'stories')
                            @php
                                $storyBadges = $post->storyAchievementBadges();
                            @endphp
                            @if($storyBadges !== [])
                                <div class="community-article-score-row mb-4">
                                    @foreach($storyBadges as $badge)
                                        <span class="badge bg-light text-dark community-story-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($post->storyAudioUrl())
                                <div class="story-audio-player about-box mb-4">
                                    <h4 class="mb-2">Audio story</h4>
                                    <audio controls class="w-100" preload="metadata" src="{{ $post->storyAudioUrl() }}"></audio>
                                </div>
                            @endif
                            @if(filled(data_get($post->meta, 'story_moral_takeaway')))
                                <div class="story-moral-takeaway news-detail-highlights mb-4">
                                    <h4>Moral / takeaway</h4>
                                    <blockquote class="story-moral-takeaway__quote mb-0">{!! nl2br(e(data_get($post->meta, 'story_moral_takeaway'))) !!}</blockquote>
                                </div>
                            @endif
                        @endif
                        @if($post->content_type === 'poetry' && filled(data_get($post->meta, 'poetry_inspiration')))
                            <div class="poetry-inspiration-panel news-detail-quote mb-4">
                                <h4>Inspiration</h4>
                                <blockquote class="poetry-inspiration-panel__quote mb-0">{!! nl2br(e(data_get($post->meta, 'poetry_inspiration'))) !!}</blockquote>
                            </div>
                        @endif
                        @if($post->content_type === 'autobiography')
                            @include('community.partials.autobiography-after-content', ['post' => $post])
                        @endif
                        @include('community.partials.life-learning-portal-sections', ['post' => $post, 'placement' => 'after'])
                        @include('community.partials.community-hub-portal-sections', [
                            'post' => $post,
                            'placement' => 'after',
                            'awarenessEngagement' => $awarenessEngagement ?? null,
                            'awarenessPledgeCounts' => $awarenessPledgeCounts ?? [],
                            'businessEngagement' => $businessEngagement ?? null,
                            'environmentEngagement' => $environmentEngagement ?? null,
                            'astroConsultancyEngagement' => $astroConsultancyEngagement ?? null,
                            'localVoiceEngagement' => $localVoiceEngagement ?? null,
                            'reportEngagement' => $reportEngagement ?? null,
                        ])
    @else
    <section
        class="about-banner community-article-hero{{ $coverUrl ? ' has-cover' : '' }}"
        @if($coverUrl) style="--article-cover: url('{{ $coverUrl }}')" @endif
    >
        <div class="community-article-hero__inner">
        @include('community.partials.community-portal-nav', [
            'navContext' => 'detail',
            'portalType' => $post->content_type,
            'activeHub' => \App\Support\CommunityContentTaxonomy::hubSectionForType($post->content_type),
            'currentLabel' => $post->title,
            'navTheme' => 'dark',
        ])
        <div class="community-article-hero__kicker">
            SoilnWater Community <span>·</span> {{ $post->typeLabel() }}
            @if(filled($post->category))
                <span>·</span> {{ $post->category }}
            @endif
        </div>
        <h1>{{ $post->title }}</h1>
        @if(filled($post->excerpt))
            <p class="community-article-hero__deck">{{ $post->excerpt }}</p>
        @elseif($post->content_type === 'news' && filled(data_get($post->meta, 'news_subtitle')))
            <p class="community-article-hero__deck">{{ data_get($post->meta, 'news_subtitle') }}</p>
        @endif
        <p class="community-article-hero__byline">
            By
            @if($post->showsAuthorProfileLink())
                <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}">{{ $post->authorDisplayName() }}</a>
            @else
                {{ $post->authorDisplayName() }}
            @endif
            <span class="community-article-hero__byline-sep" aria-hidden="true">·</span>
            {{ $post->published_at?->format('M d, Y') ?? 'Draft' }}
        </p>
        <div class="community-article-hero__actions">
            @if($post->allowsSharing())
                @include('community.partials.share-panel', ['post' => $post, 'showTrigger' => true, 'iconOnly' => true])
            @endif
            @auth
                @if($post->isPubliclyVisible())
                    <button type="button"
                        class="community-banner-action community-banner-action--icon js-community-save-post {{ $isSaved ? 'is-saved' : '' }}"
                        data-url="{{ route('community.save.toggle', $post) }}"
                        data-label-saved="Saved"
                        data-label-unsaved="Save"
                        title="{{ $isSaved ? 'Saved' : 'Save' }}"
                        aria-label="{{ $isSaved ? 'Saved' : 'Save' }}">
                        <i class="fa-{{ $isSaved ? 'solid' : 'regular' }} fa-bookmark" aria-hidden="true"></i>
                    </button>
                    @if(auth()->id() !== $post->user_id)
                        <button type="button"
                            class="community-banner-action community-banner-action--icon js-community-subscribe-category {{ $isCategorySubscribed ? 'is-subscribed' : '' }}"
                            data-url="{{ route('community.subscriptions.category.toggle') }}"
                            data-content-type="{{ $subscriptionContentType }}"
                            data-category="{{ $subscriptionCategory }}"
                            data-label-subscribed="Subscribed to category"
                            data-label-unsubscribed="Subscribe to category"
                            title="{{ $isCategorySubscribed ? 'Subscribed to category' : 'Subscribe to category' }}"
                            aria-label="{{ $isCategorySubscribed ? 'Subscribed to category' : 'Subscribe to category' }}">
                            <i class="fa-solid fa-bell" aria-hidden="true"></i>
                        </button>
                        <button type="button"
                            class="community-banner-action community-banner-action--icon"
                            data-bs-toggle="modal"
                            data-bs-target="#communityPostReportModal"
                            title="Report content"
                            aria-label="Report content">
                            <i class="fa-solid fa-flag" aria-hidden="true"></i>
                        </button>
                    @endif
                @endif
                @if(auth()->id() === $post->user_id || auth()->user()->isAdmin())
                    <a href="{{ route('community.posts.edit', $post) }}" class="community-banner-action community-banner-action--icon" title="Edit post" aria-label="Edit post">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    </a>
                @endif
            @endauth
        </div>
        </div>
    </section>
    @endif

    @if(! $isPortalPost)
    <div class="about-inner">
        <section class="sec">
            @include('community.partials.post-shell-start', ['post' => $post])
    @endif

            @if(! $isArticlePost && ! $isPortalPost)
                @php
                    $shellScoreBadges = $post->articleScoreBadges();
                    $shellStoryBadges = $post->content_type === 'stories' ? $post->storyAchievementBadges() : [];
                    $shellPromotions = $post->adminPromotionLabels();
                @endphp
                @if($shellScoreBadges !== [] || $shellStoryBadges !== [] || $shellPromotions !== [])
                    <div class="community-article-score-row">
                        @foreach($shellScoreBadges as $badge)
                            <span class="badge bg-light text-dark community-score-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        @endforeach
                        @foreach($shellStoryBadges as $badge)
                            <span class="badge bg-light text-dark community-story-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        @endforeach
                        @foreach($shellPromotions as $promotionLabel)
                            <span class="badge bg-warning text-dark">{{ $promotionLabel }}</span>
                        @endforeach
                    </div>
                @endif
            @endif

            @if($isArticlePost && ! $isPortalPost)
                @include('community.partials.articles-show-sections', ['post' => $post])
            @endif

            @if($post->content_type === 'poetry' && ! $isPortalPost)
                @include('community.partials.poetry-show-sections', ['post' => $post])
            @endif

            @if($post->content_type === 'autobiography' && ! $isPortalPost)
                @include('community.partials.autobiography-show-sections', ['post' => $post])
            @endif

            @if($post->isChildrensCornerPost() && ! $isPortalPost)
                @include('community.partials.childrens-corner-show-sections', ['post' => $post, 'placement' => 'intro'])
            @endif

            @if($post->isAwarenessPost() && ! $isPortalPost)
                @include('community.partials.awareness-show-sections', [
                    'post' => $post,
                    'awarenessEngagement' => $awarenessEngagement ?? null,
                    'awarenessPledgeCounts' => $awarenessPledgeCounts ?? [],
                ])
            @endif

            @if($post->isBusinessPost() && ! $isPortalPost)
                @include('community.partials.business-show-sections', [
                    'post' => $post,
                    'businessEngagement' => $businessEngagement ?? null,
                ])
            @endif

            @if($post->isWomensWorldPost() && ! $isPortalPost)
                @include('community.partials.womens-world-show-sections', ['post' => $post])
            @endif

            @if($post->isSeniorCitizensForumPost() && ! $isPortalPost)
                @include('community.partials.senior-citizens-forum-show-sections', ['post' => $post])
            @endif

            @if($post->isStudentCornerPost() && ! $isPortalPost)
                @include('community.partials.student-corner-show-sections', ['post' => $post])
            @endif

            @if($post->isYouthCornerPost() && ! $isPortalPost)
                @include('community.partials.youth-corner-show-sections', ['post' => $post])
            @endif

            @if($post->isMyAreaPost())
                @include('community.partials.my-area-show-sections', ['post' => $post])
                @include('community.partials.my-area-community-actions', [
                    'post' => $post,
                    'reportEngagement' => $reportEngagement,
                ])
            @endif

            @if($post->isCommunityIssuesPost() && ! $isPortalPost)
                @include('community.partials.community-issues-show-sections', [
                    'post' => $post,
                    'reportEngagement' => $reportEngagement,
                ])
                @include('community.partials.community-issues-community-actions', [
                    'post' => $post,
                    'reportEngagement' => $reportEngagement,
                ])
            @endif

            @if($post->isAgriculturePost() && ! $isPortalPost)
                @include('community.partials.agriculture-show-sections', ['post' => $post])
                @include('community.partials.agriculture-community-actions', ['post' => $post])
            @endif

            @if($post->isEnvironmentPost() && ! $isPortalPost)
                @include('community.partials.environment-show-sections', ['post' => $post])
                @include('community.partials.environment-community-actions', [
                    'post' => $post,
                    'environmentEngagement' => $environmentEngagement ?? null,
                ])
            @endif

            @if($post->isScienceTechnologyPost() && ! $isPortalPost)
                @include('community.partials.science-technology-show-sections', ['post' => $post])
            @endif

            @if($post->isAstroConsultancyPost() && ! $isPortalPost)
                @include('community.partials.astro-consultancy-show-sections', ['post' => $post])
                @include('community.partials.astro-consultancy-community-actions', [
                    'post' => $post,
                    'astroConsultancyEngagement' => $astroConsultancyEngagement ?? null,
                ])
            @endif

            @if($post->isReligionSpiritualityPost() && ! $isPortalPost)
                @include('community.partials.religion-spirituality-show-sections', ['post' => $post])
            @endif

            @if($post->isCreativeCornerPost() && ! $isPortalPost)
                @include('community.partials.creative-corner-show-sections', ['post' => $post])
            @endif

            @if($post->isCompetitionsPost() && ! $isPortalPost)
                @include('community.partials.competitions-show-sections', ['post' => $post])
            @endif

            @if($post->isLocalVoicesPost() && ! $isPortalPost)
                @include('community.partials.local-voices-show-sections', ['post' => $post])
                @include('community.partials.local-voices-community-actions', [
                    'post' => $post,
                    'localVoiceEngagement' => $localVoiceEngagement,
                ])
            @endif

            @if($post->isReportContent() && ! $moveDetailExtrasToSidebar)
                <div class="mb-4">
                    @include('community.partials.report-trust-score', ['post' => $post])
                </div>
                @include('community.partials.report-community-actions', [
                    'post' => $post,
                    'reportEngagement' => $reportEngagement,
                ])
            @endif

            @if($post->content_type === 'stories' && $post->storyAudioUrl() && ! $isPortalPost)
                <div class="story-audio-player about-box mb-4">
                    <h4 class="mb-2">Audio story</h4>
                    <p class="text-muted small mb-3">
                        {{ data_get($post->storyAudioData(), 'type') === 'recording' ? 'Voice recording' : 'Uploaded audio' }}
                        @if(filled(data_get($post->storyAudioData(), 'name')))
                            — {{ data_get($post->storyAudioData(), 'name') }}
                        @endif
                    </p>
                    <audio controls class="w-100" preload="metadata" src="{{ $post->storyAudioUrl() }}">
                        Your browser does not support embedded audio playback.
                    </audio>
                </div>
            @endif

            @if($post->hasVideo() && ! $isArticlePost && ! $isNewsPost && ! $post->isAwarenessPost() && ! $post->isBusinessPost() && ! $post->isWomensWorldPost() && ! $post->isSeniorCitizensForumPost() && ! $post->isStudentCornerPost() && ! $post->isYouthCornerPost() && ! $post->isLocalVoicesPost() && ! $post->isMyAreaPost() && ! $post->isCommunityIssuesPost() && ! $post->isAgriculturePost() && ! $post->isEnvironmentPost() && ! $post->isScienceTechnologyPost() && ! $post->isAstroConsultancyPost() && ! $post->isReligionSpiritualityPost() && ! $post->isCreativeCornerPost())
                <div class="community-post-video mb-4">
                    @if($post->content_type === 'stories')
                        <h4 class="mb-3">Video story</h4>
                    @endif
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                            <iframe
                                src="{{ $post->youtubeEmbedUrl() }}"
                                title="Video for {{ $post->title }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded shadow-sm" preload="metadata">
                            <source src="{{ $post->videoFileUrl() }}">
                            Your browser does not support embedded video playback.
                        </video>
                        @if(filled(data_get($post->videoData(), 'name')))
                            <small class="text-muted d-block mt-2">{{ data_get($post->videoData(), 'name') }}</small>
                        @endif
                    @endif
                </div>
            @endif

            @if($post->usesBookLayout() && $post->bookPages() !== [] && ! $isPortalPost)
                @include('community.partials.book-reader', ['post' => $post])
            @elseif(! $isArticlePost && ! $isPortalPost)
                @php
                    $editorLanguage = data_get($post->meta, 'editor_language', 'en');
                    $editorHtmlLang = \App\Support\CommunityContentTaxonomy::editorLanguageHtmlLang($editorLanguage);
                    $bodyClasses = 'community-post-body community-post-body--scalable';
                    if ($post->content_type === 'poetry' || ($post->isChildrensCornerPost() && $post->childrensCornerContentMode() === 'poem')) {
                        $bodyClasses .= ' community-post-body--poetry';
                    }
                @endphp
                @if($post->content_type === 'poetry' || ($post->isChildrensCornerPost() && $post->childrensCornerContentMode() === 'poem'))
                    <div class="poetry-reading-card mb-4">
                        <div class="poetry-reading-card__kicker">Poem</div>
                        <div class="{{ $bodyClasses }}" data-community-font-target data-community-body-protected lang="{{ $editorHtmlLang }}" @if($editorLanguage === 'ur') dir="rtl" @endif>{!! $post->body !!}</div>
                    </div>
                @else
                    <div class="{{ $bodyClasses }}" data-community-font-target data-community-body-protected lang="{{ $editorHtmlLang }}" @if($editorLanguage === 'ur') dir="rtl" @endif>{!! $post->body !!}</div>
                @endif
            @endif

            @if($post->content_type === 'poetry' && filled(data_get($post->meta, 'poetry_inspiration')) && ! $isPortalPost)
                <div class="poetry-inspiration-panel about-box mt-4 mb-0">
                    <h4 class="mb-2">Inspiration</h4>
                    <blockquote class="poetry-inspiration-panel__quote mb-0">
                        {!! nl2br(e(data_get($post->meta, 'poetry_inspiration'))) !!}
                    </blockquote>
                </div>
            @endif

            @if($post->content_type === 'stories' && filled(data_get($post->meta, 'story_moral_takeaway')) && ! $isPortalPost)
                <div class="story-moral-takeaway about-box mt-4 mb-0">
                    <h4 class="mb-2">Moral / takeaway</h4>
                    <blockquote class="story-moral-takeaway__quote mb-0">
                        {!! nl2br(e(data_get($post->meta, 'story_moral_takeaway'))) !!}
                    </blockquote>
                </div>
            @endif

            @if($post->content_type === 'autobiography' && ! $isPortalPost)
                @include('community.partials.autobiography-after-content', ['post' => $post])
            @endif

            @if($post->isSeniorCitizensForumPost() && ! $isPortalPost)
                @include('community.partials.senior-citizens-forum-after-content', ['post' => $post])
            @endif

            @if($post->isStudentCornerPost() && ! $isPortalPost)
                @include('community.partials.student-corner-meta-details', ['post' => $post])
            @endif

            @if($post->isYouthCornerPost() && ! $isPortalPost)
                @include('community.partials.youth-corner-meta-details', ['post' => $post])
            @endif

            @if($post->isChildrensCornerPost() && ! $isPortalPost)
                @include('community.partials.childrens-corner-show-sections', ['post' => $post, 'placement' => 'media'])
            @endif

            @php
                $resolvedLocation = $post->location ?? data_get($post->meta, 'location');
                $formFieldLabels = \App\Support\CommunityPostFormFields::labels();
                $visibleMeta = collect($post->meta ?? [])->except(['location', 'location_lat', 'location_lng', 'book_pages', 'editor_language']);
                $reportMetaLabels = [
                    'report_subtitle' => 'Subtitle',
                    'reporting_period' => 'Reporting period',
                    'report_date' => 'Report date',
                    'prepared_by' => 'Prepared by',
                    'report_scope' => 'Scope / objective',
                    'methodology' => 'Methodology',
                    'data_sources' => 'Data sources',
                    'key_findings' => 'Key findings',
                    'recommendations' => 'Recommendations',
                    'location' => 'Coverage / study area',
                ];
                $myAreaMetaLabels = \App\Support\CommunityPostFormFields::myAreaDetailMetaOrder();
                $myVoiceMetaLabels = [
                    'voice_topic' => 'Topic',
                    'voice_perspective' => 'Perspective',
                    'location' => 'Related location',
                ];
                $newsMetaOrder = array_keys(\App\Support\CommunityPostFormFields::newsDetailMetaOrder());
                $storyMetaOrder = array_keys(\App\Support\CommunityPostFormFields::storyDetailMetaOrder());
                $reportMetaOrder = array_keys($reportMetaLabels);
                $myAreaMetaOrder = array_keys($myAreaMetaLabels);
                $myVoiceMetaOrder = array_keys($myVoiceMetaLabels);
                $orderedReportMeta = collect($reportMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $orderedMyAreaMeta = collect($myAreaMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $orderedMyVoiceMeta = collect($myVoiceMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $additionalReportMeta = $visibleMeta->except([...$reportMetaOrder, 'report_format', 'author_bio']);
                $additionalNewsMeta = $visibleMeta->except([
                    ...$newsMetaOrder,
                    ...\App\Models\CommunityPost::structuredLocationMetaKeys(),
                    'author_bio',
                    'news_documents',
                    'fact_summary',
                ]);
                $additionalStoryMeta = $visibleMeta->except([
                    ...$storyMetaOrder,
                    'story_type',
                    'story_moral_takeaway',
                    'story_gallery',
                    'story_audio',
                    'story_genre',
                    'mood_or_theme',
                    'reading_time',
                    'author_bio',
                ]);
                $poetryMetaOrder = array_keys(\App\Support\CommunityPostFormFields::poetryDetailMetaOrder());
                $additionalPoetryMeta = $visibleMeta->except([
                    ...$poetryMetaOrder,
                    ...array_keys(\App\Support\CommunityPostFormFields::poetryRegionalLocationOrder()),
                    'poetry_audio',
                    'poetry_part_of_series',
                    'author_bio',
                ]);
                $additionalMyAreaMeta = $visibleMeta->except([
                    ...\App\Support\CommunityPostFormFields::myAreaStructuredMetaKeys(),
                    'my_area_photo_evidence',
                    'my_area_documents',
                    'my_area_hero_images',
                    'my_area_private_link_token',
                    'author_bio',
                ]);
                $additionalMyVoiceMeta = $visibleMeta->except([...$myVoiceMetaOrder, 'author_bio']);
                $childrensCornerMetaOrder = array_keys(\App\Support\CommunityPostFormFields::childrensCornerPublicMetaOrder());
                $additionalChildrensCornerMeta = $visibleMeta->except([
                    ...\App\Support\CommunityPostFormFields::childrensCornerStructuredMetaKeys(),
                    ...\App\Support\CommunityPostFormFields::childrensCornerPrivateMetaKeys(),
                    'author_bio',
                ]);
                $additionalAwarenessMeta = $visibleMeta->except([
                    ...\App\Support\CommunityPostFormFields::awarenessStructuredMetaKeys(),
                    ...\App\Support\CommunityPostFormFields::awarenessEngagementStructuredMetaKeys(),
                    ...\App\Models\CommunityPost::structuredLocationMetaKeys(),
                    'awareness_video_type',
                    'author_bio',
                    'campaign_topic',
                    'target_audience',
                    'call_to_action',
                    'related_resource_url',
                ]);
                $additionalBusinessMeta = $visibleMeta->except([
                    ...\App\Support\CommunityPostFormFields::businessStructuredMetaKeys(),
                    ...\App\Support\CommunityPostFormFields::businessEngagementStructuredMetaKeys(),
                    ...\App\Models\CommunityPost::structuredLocationMetaKeys(),
                    'business_video_type',
                    'author_bio',
                ]);
                $additionalWomensWorldMeta = $visibleMeta->except([
                    ...\App\Support\CommunityPostFormFields::womensWorldStructuredMetaKeys(),
                    ...\App\Support\CommunityPostFormFields::womensWorldEngagementStructuredMetaKeys(),
                    'womens_world_gallery',
                    'womens_world_video_type',
                    'womens_world_audio',
                    'womens_world_visibility',
                    'womens_world_private_link_token',
                    'location_country',
                    'location_state',
                    'location_district',
                    'location_city',
                    'location_locality',
                    'author_bio',
                    'focus_area',
                    'perspective_summary',
                ]);
                $additionalSeniorCitizensForumMeta = $visibleMeta->except([
                    ...\App\Support\CommunityPostFormFields::seniorCitizensForumStructuredMetaKeys(),
                    'senior_citizens_forum_achievements',
                    'senior_citizens_forum_audio',
                    'senior_citizens_forum_private_link_token',
                    'senior_citizens_forum_video_type',
                    'location_country',
                    'location_state',
                    'location_district',
                    'location_city',
                    'location_locality',
                    'author_bio',
                ]);
                $additionalStudentCornerMeta = $visibleMeta->except([
                    ...\App\Support\CommunityPostFormFields::studentCornerStructuredMetaKeys(),
                    ...\App\Support\CommunityPostFormFields::studentCornerEngagementStructuredMetaKeys(),
                    'student_corner_video_type',
                    'student_corner_private_link_token',
                    'location_country',
                    'location_state',
                    'location_district',
                    'location_city',
                    'location_locality',
                    'author_bio',
                ]);
                $additionalYouthCornerMeta = $visibleMeta->except([
                    ...\App\Support\CommunityPostFormFields::youthCornerStructuredMetaKeys(),
                    ...\App\Support\CommunityPostFormFields::youthCornerEngagementStructuredMetaKeys(),
                    'youth_corner_video_type',
                    'youth_corner_private_link_token',
                    'location_country',
                    'location_state',
                    'location_district',
                    'location_city',
                    'location_locality',
                    'author_bio',
                ]);
                $additionalLocalVoicesMeta = $visibleMeta->except([
                    ...\App\Support\CommunityPostFormFields::localVoiceStructuredMetaKeys(),
                    'location_country',
                    'location_state',
                    'location_district',
                    'location_city',
                    'location_locality',
                    'author_bio',
                ]);
            @endphp
            @if($post->content_type === 'reports' && (filled(data_get($post->meta, 'report_type')) || filled(data_get($post->meta, 'report_status'))))
                @include('community.partials.report-meta-details', [
                    'post' => $post,
                    'includeLocation' => true,
                    'hideAttachments' => $moveDetailExtrasToSidebar,
                ])
                @php
                    $visibleMeta = $additionalReportMeta;
                @endphp
            @elseif($post->content_type === 'reports' && blank(data_get($post->meta, 'report_type')) && $orderedReportMeta->isNotEmpty())
                <div class="about-box mt-4">
                    <h4>Report details</h4>
                    <div class="row g-3">
                        @foreach($orderedReportMeta as $key => $value)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <strong class="d-block mb-1">{{ $reportMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                                    <span>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @php
                    $visibleMeta = $additionalReportMeta;
                @endphp
            @endif
            @if($post->content_type === 'news' && ! $isPortalPost)
                @include('community.partials.news-meta-details', ['post' => $post])
                @php
                    $visibleMeta = $additionalNewsMeta;
                @endphp
            @endif
            @if($post->content_type === 'stories')
                @include('community.partials.story-meta-details', ['post' => $post])
                @include('community.partials.story-rating', ['post' => $post])
                @php
                    $visibleMeta = $additionalStoryMeta;
                @endphp
            @endif
            @if($post->content_type === 'poetry')
                @include('community.partials.poetry-meta-details', ['post' => $post])
                @include('community.partials.story-rating', ['post' => $post])
                @php
                    $visibleMeta = $additionalPoetryMeta;
                @endphp
            @endif
            @if($post->content_type === 'autobiography')
                @include('community.partials.story-rating', ['post' => $post])
                @php
                    $visibleMeta = $visibleMeta->except(\App\Support\CommunityPostFormFields::autobiographyStructuredMetaKeys());
                @endphp
            @endif
            @if($post->isChildrensCornerPost() && ! $isPortalPost)
                @include('community.partials.childrens-corner-meta-details', ['post' => $post])
                @php
                    $visibleMeta = $additionalChildrensCornerMeta;
                @endphp
            @endif
            @if($post->isAwarenessPost() && ! $isPortalPost)
                @include('community.partials.awareness-meta-details', ['post' => $post])
                @php
                    $visibleMeta = $additionalAwarenessMeta;
                @endphp
            @endif
            @if($post->isBusinessPost() && ! $isPortalPost)
                @include('community.partials.business-meta-details', ['post' => $post])
                @php
                    $visibleMeta = $additionalBusinessMeta;
                @endphp
            @endif
            @if($post->isWomensWorldPost())
                @php
                    $visibleMeta = $additionalWomensWorldMeta;
                @endphp
            @endif
            @if($post->isSeniorCitizensForumPost())
                @php
                    $visibleMeta = $additionalSeniorCitizensForumMeta;
                @endphp
            @endif
            @if($post->isStudentCornerPost())
                @php
                    $visibleMeta = $additionalStudentCornerMeta;
                @endphp
            @endif
            @if($post->isYouthCornerPost())
                @php
                    $visibleMeta = $additionalYouthCornerMeta;
                @endphp
            @endif
            @if($post->isLocalVoicesPost())
                @php
                    $visibleMeta = $additionalLocalVoicesMeta;
                @endphp
            @endif
            @if($post->isMyAreaPost())
                @php
                    $visibleMeta = $additionalMyAreaMeta;
                @endphp
            @endif
            @if($post->content_type === 'my-voice' && $orderedMyVoiceMeta->isNotEmpty())
                <div class="about-box mt-4">
                    <h4>My Voice details</h4>
                    <div class="row g-3">
                        @foreach($orderedMyVoiceMeta as $key => $value)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <strong class="d-block mb-1">{{ $myVoiceMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                                    <span>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @php
                    $visibleMeta = $additionalMyVoiceMeta;
                @endphp
            @endif
            @if($post->isWomensWorldPost() && $post->structuredLocationForDisplay()->isNotEmpty())
                @php
                    $visibleMeta = $additionalWomensWorldMeta;
                @endphp
            @elseif(! $moveDetailExtrasToSidebar && \App\Models\CommunityPost::usesStructuredLocation($post->content_type) && ! in_array($post->content_type, ['news', 'awareness', 'business', 'womens-world', 'senior-citizens-forum', 'student-corner', 'youth-corner', 'local-voices', 'my-area', 'community-issues', 'agriculture', 'environment'], true) && $post->structuredLocationForDisplay()->isNotEmpty())
                <div class="community-detail-card community-detail-card--location mt-4">
                    <div class="community-detail-card__head">
                        <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                        <div>
                            <h4 class="community-detail-card__title">Location information</h4>
                            <p class="community-detail-card__subtitle">Where this content is relevant</p>
                        </div>
                    </div>
                    <div class="community-detail-grid">
                        @foreach($post->structuredLocationForDisplay() as $key => $value)
                            <div class="community-detail-item">
                                <span class="community-detail-item__label">{{ \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type)[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                                <span class="community-detail-item__value">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($post->hasMapCoordinates())
                        <div class="community-detail-map ratio ratio-16x9">
                            <iframe
                                title="Post location map"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $post->location_lng - 0.02 }},{{ $post->location_lat - 0.02 }},{{ $post->location_lng + 0.02 }},{{ $post->location_lat + 0.02 }}&layer=mapnik&marker={{ $post->location_lat }},{{ $post->location_lng }}"
                            ></iframe>
                        </div>
                    @endif
                </div>
            @elseif(! $moveDetailExtrasToSidebar && $post->content_type !== 'poetry' && filled($post->location_type))
                <div class="community-detail-card community-detail-card--location mt-4">
                    <div class="community-detail-card__head">
                        <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                        <div>
                            <h4 class="community-detail-card__title">Location information</h4>
                            <p class="community-detail-card__subtitle">Where this content is relevant</p>
                        </div>
                    </div>
                    <span class="community-detail-location-type">
                        <i class="fa-solid fa-map-pin" aria-hidden="true"></i>
                        {{ $post->locationTypeLabel() }}
                    </span>
                    @if($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_GLOBAL)
                        <p class="community-detail-location-note mb-0">This post has global relevance.</p>
                    @elseif($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_INDIA)
                        <p class="community-detail-location-note mb-0">This post applies across India.</p>
                    @elseif($post->usesGpsLocation())
                        <p class="community-detail-location-note mb-0">This report uses an optional GPS location.</p>
                        @if($post->hasMapCoordinates())
                            <div class="community-detail-map ratio ratio-16x9">
                                <iframe
                                    title="Report location map"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $post->location_lng - 0.02 }},{{ $post->location_lat - 0.02 }},{{ $post->location_lng + 0.02 }},{{ $post->location_lat + 0.02 }}&layer=mapnik&marker={{ $post->location_lat }},{{ $post->location_lng }}"
                                ></iframe>
                            </div>
                        @else
                            <p class="community-detail-location-note mb-0 mt-2">No map location was provided for this report.</p>
                        @endif
                    @elseif($post->requiresSpecificLocation() && filled($resolvedLocation))
                        <p class="community-detail-location-note mb-0">{{ $resolvedLocation }}</p>
                    @endif
                </div>
            @endif
            @if(! $moveDetailExtrasToSidebar && $visibleMeta->isNotEmpty())
                <div class="community-detail-card community-detail-card--meta mt-4">
                    <div class="community-detail-card__head">
                        <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-circle-info"></i></span>
                        <div>
                            <h4 class="community-detail-card__title">Additional details</h4>
                            <p class="community-detail-card__subtitle">Extra context about this post</p>
                        </div>
                    </div>
                    <dl class="community-detail-list mb-0">
                        @foreach($visibleMeta as $key => $value)
                            @if(blank($value) && $value !== false)
                                @continue
                            @endif
                            @if(is_array($value) || is_object($value))
                                @continue
                            @endif
                            <div class="community-detail-list__row">
                                <dt>{{ $formFieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                                <dd>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            @unless($isPortalPost)
            @include('community.partials.post-shell-end', ['post' => $post, 'followedTopics' => $followedTopics])
            @endunless

            @if($post->allowsPoll())
                @include('community.partials.poll', ['post' => $post])
            @endif

        <div class="about-box mt-3 community-engagement-panel community-engagement-panel--article">
                <div class="community-engagement-stats" role="list">
                    <div class="community-engagement-stat" role="listitem" title="Views">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        <span class="community-engagement-stat__value">{{ number_format($post->views_count) }}</span>
                        <span class="visually-hidden">Views</span>
                    </div>
                    <div class="community-engagement-stat" role="listitem" title="Shares">
                        <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
                        <span class="community-engagement-stat__value">{{ number_format($post->shares_count) }}</span>
                        <span class="visually-hidden">Shares</span>
                    </div>
                    @if($post->article_score > 0)
                        <div class="community-engagement-stat" role="listitem" title="Article score">
                            <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
                            <span class="community-engagement-stat__value">{{ number_format((float) $post->article_score, 1) }}</span>
                            <span class="visually-hidden">Article score out of 100</span>
                        </div>
                    @endif
                </div>
                @php
                    $reactionCounts = $post->reactions->groupBy('reaction')->map->count();
                    $userReactions = auth()->check() ? $post->reactions->where('user_id', auth()->id())->pluck('reaction')->all() : [];
                    $reactionOptions = $post->reactionOptionsForDisplay();
                @endphp
                @auth
                    <div class="community-engagement-actions d-flex flex-wrap gap-2 mb-3" id="communityReactionButtons">
                        @foreach($reactionOptions as $reaction => $icon)
                            <form method="POST" action="{{ route('community.react', $post) }}" class="js-community-reaction-form">
                                @csrf
                                <input type="hidden" name="reaction" value="{{ $reaction }}">
                                <button type="submit" class="btn {{ in_array($reaction, $userReactions, true) ? 'btn-success' : 'btn-outline-success' }} btn-sm community-engagement-icon-btn" data-reaction-button="{{ $reaction }}" title="{{ $reaction }}" aria-label="{{ $reaction }} ({{ $reactionCounts[$reaction] ?? 0 }})">
                                    <i class="{{ $icon }}" aria-hidden="true"></i>
                                    <span class="reaction-count">{{ $reactionCounts[$reaction] ?? 0 }}</span>
                                </button>
                            </form>
                        @endforeach
                        @if($post->showsAuthorProfileLink() && auth()->id() !== $post->user_id)
                            <button type="button"
                                class="btn btn-sm community-engagement-icon-btn js-community-follow-author {{ $isFollowingAuthor ? 'btn-success is-following' : 'btn-outline-success' }}"
                                data-url="{{ route('community.authors.follow', $post->user) }}"
                                data-label-following="Unfollow"
                                data-label-unfollowed="Follow Author"
                                title="{{ $isFollowingAuthor ? 'Unfollow author' : 'Follow author' }}"
                                aria-label="{{ $isFollowingAuthor ? 'Unfollow author' : 'Follow author' }}">
                                <i class="fa-solid {{ $isFollowingAuthor ? 'fa-user-check' : 'fa-user-plus' }}" aria-hidden="true"></i>
                            </button>
                        @endif
                    </div>
                @else
                    <p class="small text-muted mb-3"><a href="{{ route('login') }}">Login</a> to react or follow this author.</p>
                @endauth
            </div>

            @if($post->content_type === 'news' && $post->allowsNewsDiscussion())
                <section class="about-box mt-4" id="comments-discussion">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                        <div>
                            <h4 class="mb-1">Comments &amp; Discussion</h4>
                            <p class="text-muted mb-0">Readers can comment, ask questions, or share suggestions. Authors are notified in the portal and by email; readers are notified when questions are answered.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if($post->allow_comments)
                                <span class="badge bg-success">Comments open</span>
                            @endif
                            @if($post->allow_questions)
                                <span class="badge bg-success">Questions open</span>
                            @endif
                            @if($post->allow_suggestions)
                                <span class="badge bg-success">Suggestions open</span>
                            @endif
                        </div>
                    </div>
            @endif

            @if($post->user_id && $post->resolvedPublishAs() !== \App\Models\CommunityPost::PUBLISH_AS_ANONYMOUS && $post->allow_questions)
                @include('community.partials.author-questions', [
                    'author' => $post->user,
                    'post' => $post,
                    'answeredQuestions' => $answeredAuthorQuestions ?? collect(),
                    'sectionTitle' => $post->content_type === 'news' ? 'Questions' : null,
                    'compactSection' => $post->content_type === 'news',
                ])
            @endif

            @include('community.partials.public-participation', [
                'post' => $post,
                'participationSuggestions' => $participationSuggestions ?? collect(),
                'participationFeedback' => $participationFeedback ?? collect(),
                'communityParticipationEvidence' => $communityParticipationEvidence ?? collect(),
                'hideSectionHeader' => $post->content_type === 'news' && $post->allowsNewsDiscussion(),
            ])

            @if($post->content_type === 'news' && $post->allowsNewsDiscussion())
                </section>
            @endif
    @if($isPortalPost)
                        </section>
                    </main>
                    @php
                        $sidebarAttachments = match ($post->content_type) {
                            'reports' => data_get($post->meta, 'issue_attachments', []),
                            'articles' => data_get($post->meta, 'news_documents', []),
                            default => [],
                        };
                    @endphp
                    @include('community.partials.news-portal-rail', [
                        'activeHub' => $activeHub,
                        'activeCategory' => $activeCategory,
                        'portalType' => $portalType,
                        'breakingPosts' => $featuredPortalPosts ?? $breakingNewsPosts ?? collect(),
                        'trendingPosts' => $trendingPortalPosts ?? $trendingNews ?? collect(),
                        'relatedNews' => $relatedPortalPosts ?? $relatedNews ?? collect(),
                        'post' => $post,
                        'moveDetailExtrasToSidebar' => $moveDetailExtrasToSidebar,
                        'visibleMeta' => $visibleMeta ?? collect(),
                        'formFieldLabels' => $formFieldLabels ?? [],
                        'resolvedLocation' => $resolvedLocation ?? null,
                        'sidebarAttachments' => $sidebarAttachments,
                    ])
                </div>
            </div>
        </div>
    @else
        </section>
    </div>
    @endif

    @if($post->allowsSharing())
        @include('community.partials.share-modal')
    @endif

    @auth
        @if($post->isPubliclyVisible() && auth()->id() !== $post->user_id)
            @include('frontend.partials.profile-report-modal', [
                'reportModalId' => 'communityPostReportModal',
                'reportFormId' => 'communityPostReportForm',
                'reportLabel' => 'post',
                'reportAction' => route('community.report', $post),
            ])
        @endif
    @endauth
    </div>
@endsection

@include('community.partials.toastr-assets')

@push('styles')
<style>
    @include('community.partials.community-portal-nav-styles')
</style>
@include('community.partials.story-styles')
@include('community.partials.articles-styles')
@if(\App\Support\CommunityContentTaxonomy::usesContentPortal($post->content_type))
<link rel="stylesheet" href="{{ asset('assets/css/community-news-portal.css') }}?v={{ file_exists(public_path('assets/css/community-news-portal.css')) ? filemtime(public_path('assets/css/community-news-portal.css')) : time() }}">
@include('community.partials.news-show-styles')
@endif
@if($post->content_type === 'poetry')
@include('community.partials.poetry-styles')
@endif
@if($post->content_type === 'autobiography')
@include('community.partials.autobiography-styles')
@endif
@if($post->isChildrensCornerPost())
@include('community.partials.childrens-corner-styles')
@endif
@if($post->isAwarenessPost())
@include('community.partials.awareness-styles')
@endif
@if($post->isBusinessPost() || $post->isWomensWorldPost() || $post->isStudentCornerPost() || $post->isYouthCornerPost())
@include('community.partials.business-styles')
@endif
@if($post->isSeniorCitizensForumPost())
@include('community.partials.senior-citizens-forum-styles')
@endif
@if($post->isAstroConsultancyPost())
@include('community.partials.astro-consultancy-styles')
@endif
@if($post->isReligionSpiritualityPost())
@include('community.partials.religion-spirituality-styles')
@endif
@if($post->isCreativeCornerPost())
@include('community.partials.creative-corner-styles')
@endif
@if($post->isCompetitionsPost())
@include('community.partials.competitions-styles')
@endif
<style>
    .community-post-body {
        line-height: 1.8;
        overflow: auto;
    }
    .community-post-body--poetry {
        font-family: Georgia, "Times New Roman", serif;
        line-height: 1.85;
        white-space: normal;
    }
    .community-post-body--poetry > p {
        margin-bottom: 0.35rem;
    }
    .community-post-body--poetry > p:last-child {
        margin-bottom: 0;
    }
    .community-post-body--poetry[lang="hi"],
    .community-post-body--poetry[lang="mr"] {
        font-family: "Noto Sans Devanagari", "Nirmala UI", "Mangal", Georgia, serif;
    }
    .community-post-body--poetry[lang="ur"] {
        direction: rtl;
        font-family: "Noto Nastaliq Urdu", "Jameel Noori Nastaleeq", "Urdu Typesetting", serif;
        text-align: right;
    }
    .community-post-body--poetry[lang="pa"] {
        font-family: "Noto Sans Gurmukhi", "Raavi", Georgia, serif;
    }
    .community-post-body--poetry[lang="bn"] {
        font-family: "Noto Sans Bengali", "Vrinda", Georgia, serif;
    }
    .community-post-body--poetry[lang="gu"] {
        font-family: "Noto Sans Gujarati", "Shruti", Georgia, serif;
    }
    .community-post-body--poetry[lang="ta"] {
        font-family: "Noto Sans Tamil", "Latha", Georgia, serif;
    }
    .community-post-body--poetry[lang="te"] {
        font-family: "Noto Sans Telugu", "Gautami", Georgia, serif;
    }
    .community-post-body > p,
    .community-post-body > h2,
    .community-post-body > h3,
    .community-post-body > h4,
    .community-post-body > blockquote,
    .community-post-body > ul,
    .community-post-body > ol {
        clear: none !important;
    }
    .community-post-body .image {
        clear: none !important;
        display: block;
        margin: 0.75rem auto;
        max-width: 100%;
    }
    .community-post-body .image img {
        display: block;
        height: auto;
        max-width: 100%;
    }
    .community-post-body .image img[style*="width"] {
        max-width: 100%;
    }
    .community-post-body .image.image-style-align-left,
    .community-post-body .image.image-style-side {
        clear: none !important;
        display: block !important;
        float: left !important;
        margin: 0.35rem 1.25rem 0.75rem 0 !important;
        max-width: 50%;
    }
    .community-post-body .image.image-style-align-right {
        clear: none !important;
        display: block !important;
        float: right !important;
        margin: 0.35rem 0 0.75rem 1.25rem !important;
        max-width: 50%;
    }
    .community-post-body .image.image-style-align-center,
    .community-post-body .image.image-style-block {
        clear: both;
        display: table;
        float: none;
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
    }
    .community-post-body .image.image-style-inline,
    .community-post-body .image-inline {
        display: inline-block;
        float: none;
        margin: 0.15em 0.35em;
        max-width: 50%;
        vertical-align: top;
    }
    .discussion-comment { background: #fff; }
    .discussion-reply { background: #f8faf9; border-color: #badbcc !important; padding-bottom: .5rem; padding-top: .5rem; }
    .public-participation__block {
        border-top: 1px solid #e8f0ea;
        padding-top: 1.25rem;
    }
    .public-participation__block:first-of-type {
        border-top: 0;
        padding-top: 0;
    }
    @@media (max-width: 767.98px) {
        .community-post-body .image.image-style-align-left,
        .community-post-body .image.image-style-align-right,
        .community-post-body .image.image-style-side,
        .community-post-body .image.image-style-inline,
        .community-post-body .image-inline {
            display: block !important;
            float: none !important;
            margin: 1rem auto !important;
            max-width: 100% !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const protectedSelector = '[data-community-body-protected]';

        const fontRoot = document.querySelector('[data-article-font-root]');
        if (fontRoot) {
            const zoomSteps = [0.85, 0.92, 1, 1.12, 1.25, 1.4, 1.55, 1.7, 1.85, 2];
            const defaultStep = 2;
            const controls = fontRoot.querySelector('[data-article-font-controls]');

            function getScaleTargets() {
                return fontRoot.querySelectorAll('[data-community-font-target], .community-post-body--scalable, .community-post-body--article');
            }

            function applyStep(step) {
                const index = Math.max(0, Math.min(zoomSteps.length - 1, Number(step)));
                const scale = zoomSteps[index];

                fontRoot.setAttribute('data-article-font-step', String(index));
                fontRoot.style.setProperty('--article-text-scale', String(scale));
                fontRoot.classList.toggle('is-text-scaled', index !== defaultStep);

                getScaleTargets().forEach(function (target) {
                    target.style.setProperty('--article-text-scale', String(scale));
                    target.style.removeProperty('zoom');
                    target.style.removeProperty('-webkit-text-size-adjust');
                });

                if (controls) {
                    controls.querySelectorAll('[data-article-font-action]').forEach(function (button) {
                        const action = button.getAttribute('data-article-font-action');

                        if (action === 'reset') {
                            const isDefault = index === defaultStep;
                            button.classList.toggle('is-active', isDefault);
                            button.setAttribute('aria-pressed', isDefault ? 'true' : 'false');
                        }

                        if (action === 'decrease') {
                            button.disabled = index === 0;
                        }

                        if (action === 'increase') {
                            button.disabled = index === zoomSteps.length - 1;
                        }
                    });
                }
            }

            function currentStep() {
                const value = Number(fontRoot.getAttribute('data-article-font-step'));
                return Number.isFinite(value) ? value : defaultStep;
            }

            if (controls) {
                controls.addEventListener('click', function (event) {
                    const button = event.target.closest('[data-article-font-action]');
                    if (!button || !controls.contains(button)) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    const action = button.getAttribute('data-article-font-action');

                    if (action === 'decrease') {
                        applyStep(currentStep() - 1);
                    } else if (action === 'increase') {
                        applyStep(currentStep() + 1);
                    } else if (action === 'reset') {
                        applyStep(defaultStep);
                    }
                });
            }

            applyStep(defaultStep);
        }

        function isInsideProtectedContent(node) {
            if (!node) {
                return false;
            }

            const element = node.nodeType === Node.TEXT_NODE ? node.parentElement : node;

            return Boolean(element && element.closest(protectedSelector));
        }

        document.querySelectorAll(protectedSelector).forEach(function (element) {
            ['copy', 'cut', 'paste', 'contextmenu', 'dragstart'].forEach(function (eventName) {
                element.addEventListener(eventName, function (event) {
                    event.preventDefault();
                });
            });
        });

        document.addEventListener('copy', function (event) {
            const selection = window.getSelection();

            if (!selection || selection.isCollapsed) {
                return;
            }

            if (isInsideProtectedContent(selection.anchorNode) || isInsideProtectedContent(selection.focusNode)) {
                event.preventDefault();
            }
        }, true);

        document.addEventListener('cut', function (event) {
            const selection = window.getSelection();

            if (!selection || selection.isCollapsed) {
                return;
            }

            if (isInsideProtectedContent(selection.anchorNode) || isInsideProtectedContent(selection.focusNode)) {
                event.preventDefault();
            }
        }, true);
    })();

    document.querySelectorAll('.js-community-reaction-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = form.querySelector('[data-reaction-button]');
            const originalHtml = button.innerHTML;
            button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to add reaction.');
                }

                document.querySelectorAll('[data-reaction-button] .reaction-count').forEach((countEl) => {
                    countEl.textContent = '0';
                });

                Object.entries(payload.counts || {}).forEach(([reaction, count]) => {
                    const countEl = document.querySelector(`[data-reaction-button="${reaction}"] .reaction-count`);
                    if (countEl) countEl.textContent = count;
                });

                if (payload.reaction) {
                    const reactionButton = document.querySelector(`[data-reaction-button="${payload.reaction}"]`);
                    if (reactionButton) {
                        reactionButton.classList.toggle('btn-success', Boolean(payload.active));
                        reactionButton.classList.toggle('btn-outline-success', !payload.active);
                    }
                }
            } catch (error) {
                alert(error.message || 'Unable to add reaction.');
                button.innerHTML = originalHtml;
            } finally {
                button.disabled = false;
            }
        });
    });

    document.querySelectorAll('.js-story-rating-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = event.submitter;
            if (!button || button.disabled) {
                return;
            }

            const originalHtml = button.innerHTML;
            form.querySelectorAll('button[type="submit"]').forEach((starButton) => {
                starButton.disabled = true;
            });

            try {
                const formData = new FormData(form);
                formData.set('rating', button.value);

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to save rating.');
                }

                const panel = form.closest('.story-rating-panel');
                const rating = Number(payload.rating || button.value);

                form.querySelectorAll('[data-story-rating-star]').forEach((starButton) => {
                    const starValue = Number(starButton.dataset.storyRatingStar);
                    starButton.classList.toggle('btn-warning', starValue <= rating);
                    starButton.classList.toggle('btn-outline-warning', starValue > rating);
                });

                if (panel && payload.average_rating !== undefined) {
                    let summary = panel.querySelector('.story-rating-summary__score')?.closest('.text-end');
                    if (!summary && payload.average_rating) {
                        const header = panel.querySelector('.d-flex.flex-wrap.align-items-start');
                        if (header) {
                            summary = document.createElement('div');
                            summary.className = 'text-end';
                            summary.innerHTML = `
                                <div class="story-rating-summary__score"></div>
                                <div class="story-rating-summary__stars" aria-hidden="true"></div>
                                <small class="text-muted"></small>
                            `;
                            header.appendChild(summary);
                        }
                    }

                    if (summary) {
                        const scoreEl = summary.querySelector('.story-rating-summary__score');
                        const starsEl = summary.querySelector('.story-rating-summary__stars');
                        const countEl = summary.querySelector('small.text-muted');
                        const average = Number(payload.average_rating);
                        const count = Number(payload.ratings_count || 0);

                        if (scoreEl) {
                            scoreEl.textContent = average.toFixed(1);
                        }
                        if (starsEl) {
                            starsEl.innerHTML = Array.from({ length: 5 }, (_, index) => {
                                const filled = index + 1 <= Math.round(average);
                                return `<i class="fa-solid fa-star${filled ? '' : '-o'}" aria-hidden="true"></i>`;
                            }).join('');
                        }
                        if (countEl) {
                            countEl.textContent = `${count} rating${count === 1 ? '' : 's'}`;
                        }
                    }
                }

                if (Array.isArray(payload.achievement_badges)) {
                    const banner = document.querySelector('.community-post-banner-tags');
                    if (banner) {
                        banner.querySelectorAll('.community-story-badge').forEach((badge) => badge.remove());
                        const anchor = banner.querySelector('.community-score-badge')?.nextElementSibling
                            || banner.querySelector('.community-post-banner-tag');

                        payload.achievement_badges.forEach((badge) => {
                            const span = document.createElement('span');
                            span.className = `badge bg-light text-dark community-post-banner-tag community-story-badge ${badge.class}`;
                            span.textContent = badge.label;
                            if (anchor) {
                                banner.insertBefore(span, anchor);
                            } else {
                                banner.appendChild(span);
                            }
                        });
                    }
                }
            } catch (error) {
                alert(error.message || 'Unable to save rating.');
                button.innerHTML = originalHtml;
            } finally {
                form.querySelectorAll('button[type="submit"]').forEach((starButton) => {
                    starButton.disabled = false;
                });
            }
        });
    });

    document.getElementById('ccQuizCheckBtn')?.addEventListener('click', function () {
        const cards = document.querySelectorAll('[data-cc-quiz-card]');
        let answered = 0;
        let correct = 0;

        cards.forEach(function (card) {
            const selected = card.querySelector('input[type="radio"]:checked');
            if (!selected) {
                return;
            }

            answered += 1;
            const optionLabel = selected.closest('.cc-quiz-option');
            if (selected.dataset.correct === '1') {
                correct += 1;
                optionLabel?.classList.add('is-correct');
            } else {
                optionLabel?.classList.add('is-incorrect');
            }
        });

        const result = document.getElementById('ccQuizResult');
        if (!result) {
            return;
        }

        if (answered === 0) {
            result.hidden = false;
            result.textContent = 'Please select at least one answer before checking.';
            return;
        }

        result.hidden = false;
        result.textContent = 'You got ' + correct + ' out of ' + cards.length + ' correct. Great effort!';
    });
</script>
<script src="{{ asset('assets/js/community-engagement.js') }}?v={{ now()->timestamp }}"></script>
@endpush
