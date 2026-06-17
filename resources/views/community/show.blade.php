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
        margin: 0 auto 0.5rem;
        max-width: min(1720px, calc(100vw - 48px));
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
    $engagement = $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []];
    $isSaved = auth()->check() && in_array($post->id, $engagement['saved_post_ids'] ?? [], true);
    $isCategorySubscribed = auth()->check() && collect($engagement['subscribed_categories'] ?? [])->contains(
        fn (array $subscription): bool => ($subscription['content_type'] ?? null) === $post->content_type
            && ($subscription['category'] ?? null) === $post->category
    );
    $followedTopics = collect($engagement['followed_topics'] ?? [])->map(fn ($topic) => \App\Models\CommunityTopicFollow::normalizeTopic((string) $topic))->all();
@endphp
<div class="about-page">
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
    <section class="about-banner">
        <div class="community-post-back-wrap">
            <a href="{{ route('community.index') }}" class="community-post-back">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                Back to Community
            </a>
        </div>
        <div class="community-post-banner-tags">
            <span class="badge bg-light text-dark community-post-banner-tag">{{ $post->typeLabel() }}</span>
            @foreach($post->articleScoreBadges() as $badge)
                <span class="badge bg-light text-dark community-post-banner-tag community-score-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
            @endforeach
            @foreach($post->adminPromotionLabels() as $promotionLabel)
                <span class="badge bg-warning text-dark community-post-banner-tag">{{ $promotionLabel }}</span>
            @endforeach
            @if($post->content_type === 'articles' && filled(data_get($post->meta, 'article_type')))
                <span class="badge bg-light text-dark community-post-banner-tag">{{ data_get($post->meta, 'article_type') }}</span>
            @endif
            @if($post->content_type === 'reports' && filled($post->reportStatus()))
                <span class="badge {{ $post->reportStatusBadgeClass() }} community-post-banner-tag">{{ $post->reportStatus() }}</span>
            @endif
            @if($post->isReportContent())
                <span class="badge bg-success community-post-banner-tag">Trust Score: {{ $post->reportTrustScore() }}%</span>
            @endif
            <span class="badge bg-light text-dark community-post-banner-tag">{{ filled(data_get($post->meta, 'report_type')) ? data_get($post->meta, 'report_type', $post->category) : $post->category }}</span>
        </div>
        <h1>{{ $post->title }}</h1>
        <p>
            By
            @if($post->showsAuthorProfileLink())
                <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="text-white text-decoration-underline">{{ $post->authorDisplayName() }}</a>
            @else
                {{ $post->authorDisplayName() }}
            @endif
            · {{ $post->published_at?->format('M d, Y') ?? 'Draft' }}
        </p>
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-2">
            @if($post->allowsSharing())
                @include('community.partials.share-panel', ['post' => $post, 'showTrigger' => true])
            @endif
            @auth
                @if($post->isPubliclyVisible())
                    <button type="button"
                        class="community-banner-action js-community-save-post {{ $isSaved ? 'is-saved' : '' }}"
                        data-url="{{ route('community.save.toggle', $post) }}">
                        <i class="fa-{{ $isSaved ? 'solid' : 'regular' }} fa-bookmark" aria-hidden="true"></i>
                        {{ $isSaved ? 'Saved' : 'Save' }}
                    </button>
                    @if(auth()->id() !== $post->user_id)
                        <button type="button"
                            class="community-banner-action js-community-subscribe-category {{ $isCategorySubscribed ? 'is-subscribed' : '' }}"
                            data-url="{{ route('community.subscriptions.category.toggle') }}"
                            data-content-type="{{ $post->content_type }}"
                            data-category="{{ $post->category }}">
                            {{ $isCategorySubscribed ? 'Subscribed to category' : 'Subscribe to category' }}
                        </button>
                        <button type="button"
                            class="community-banner-action"
                            data-bs-toggle="modal"
                            data-bs-target="#communityPostReportModal">
                            <i class="fa-solid fa-flag" aria-hidden="true"></i>
                            Report content
                        </button>
                    @endif
                @endif
                @if(auth()->id() === $post->user_id || auth()->user()->isAdmin())
                    <a href="{{ route('community.posts.edit', $post) }}" class="community-banner-action">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        Edit Post
                    </a>
                @endif
            @endauth
        </div>
    </section>

    <div class="about-inner">
        <section class="sec">
            @php
                $featuredImageUrls = $post->featuredImageUrls();
            @endphp
            @if($featuredImageUrls !== [])
                <div class="community-featured-gallery mb-4 {{ count($featuredImageUrls) === 1 ? 'community-featured-gallery--single' : '' }}">
                    @foreach($featuredImageUrls as $index => $imageUrl)
                        <div class="community-featured-gallery-item">
                            <img src="{{ $imageUrl }}" alt="{{ $post->title }} — image {{ $index + 1 }}" class="img-fluid rounded">
                        </div>
                    @endforeach
                </div>
            @endif

            @if($post->excerpt)
                <p class="lead">{{ $post->excerpt }}</p>
            @endif

            @if($post->isReportContent())
                <div class="mb-4">
                    @include('community.partials.report-trust-score', ['post' => $post])
                </div>
                @include('community.partials.report-community-actions', [
                    'post' => $post,
                    'reportEngagement' => $reportEngagement,
                ])
            @endif

            @if($post->hasVideo())
                <div class="community-post-video mb-4">
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

            @if($post->usesBookLayout() && $post->bookPages() !== [])
                @include('community.partials.book-reader', ['post' => $post])
            @else
                <div class="community-post-body" data-community-body-protected lang="{{ data_get($post->meta, 'editor_language', 'en') }}">{!! $post->body !!}</div>
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
                $newsMetaLabels = [
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
                    'location' => 'News location',
                ];
                $myAreaMetaLabels = \App\Support\CommunityPostFormFields::reportDetailMetaOrder() + [
                    'location' => 'GPS issue location',
                ];
                $myVoiceMetaLabels = [
                    'voice_topic' => 'Topic',
                    'voice_perspective' => 'Perspective',
                    'location' => 'Related location',
                ];
                $reportMetaOrder = array_keys($reportMetaLabels);
                $newsMetaOrder = array_keys($newsMetaLabels);
                $myAreaMetaOrder = array_keys($myAreaMetaLabels);
                $myVoiceMetaOrder = array_keys($myVoiceMetaLabels);
                $orderedReportMeta = collect($reportMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $orderedNewsMeta = collect($newsMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $orderedMyAreaMeta = collect($myAreaMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $orderedMyVoiceMeta = collect($myVoiceMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $additionalReportMeta = $visibleMeta->except([...$reportMetaOrder, 'report_format', 'author_bio']);
                $additionalNewsMeta = $visibleMeta->except([...$newsMetaOrder, 'author_bio']);
                $additionalMyAreaMeta = $visibleMeta->except([...$myAreaMetaOrder, 'report_format', 'issue_attachments', 'author_bio']);
                $additionalMyVoiceMeta = $visibleMeta->except([...$myVoiceMetaOrder, 'author_bio']);
            @endphp
            @if($post->content_type === 'reports' && (filled(data_get($post->meta, 'report_type')) || filled(data_get($post->meta, 'report_status'))))
                @include('community.partials.report-meta-details', ['post' => $post, 'includeLocation' => true])
                @php
                    $visibleMeta = $additionalMyAreaMeta;
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
            @if($post->content_type === 'news' && $orderedNewsMeta->isNotEmpty())
                <div class="about-box mt-4">
                    <h4>News details</h4>
                    <div class="row g-3">
                        @foreach($orderedNewsMeta as $key => $value)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <strong class="d-block mb-1">{{ $newsMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                                    @if($key === 'source_url')
                                        <a href="{{ $value }}" target="_blank" rel="noopener">{{ $value }}</a>
                                    @else
                                        <span>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @php
                    $visibleMeta = $additionalNewsMeta;
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
            @if(filled($post->location_type))
                <div class="about-box mt-4">
                    <h4>Location information</h4>
                    <p class="mb-2"><strong>Type:</strong> {{ $post->locationTypeLabel() }}</p>
                    @if($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_GLOBAL)
                        <p class="mb-0 text-muted">This post has global relevance.</p>
                    @elseif($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_INDIA)
                        <p class="mb-0 text-muted">This post applies across India.</p>
                    @elseif($post->usesGpsLocation())
                        <p class="mb-2 text-muted">This report uses an optional GPS location.</p>
                        @if($post->hasMapCoordinates())
                            <p class="mb-2"><strong>Coordinates:</strong> {{ $post->location_lat }}, {{ $post->location_lng }}</p>
                            <div class="ratio ratio-16x9 border rounded overflow-hidden">
                                <iframe
                                    title="Report GPS location map"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $post->location_lng - 0.02 }},{{ $post->location_lat - 0.02 }},{{ $post->location_lng + 0.02 }},{{ $post->location_lat + 0.02 }}&layer=mapnik&marker={{ $post->location_lat }},{{ $post->location_lng }}"
                                ></iframe>
                            </div>
                        @else
                            <p class="mb-0 text-muted">No GPS coordinates were provided for this report.</p>
                        @endif
                    @elseif($post->requiresSpecificLocation() && filled($resolvedLocation))
                        <p class="mb-0">{{ $resolvedLocation }}</p>
                        @if(filled($post->location_lat) && filled($post->location_lng))
                            <small class="text-muted">Coordinates: {{ $post->location_lat }}, {{ $post->location_lng }}</small>
                        @endif
                    @endif
                </div>
            @endif
            @if($visibleMeta->isNotEmpty())
                <div class="about-box mt-4">
                    <h4>Additional details</h4>
                    <ul class="about-list mb-0">
                        @foreach($visibleMeta as $key => $value)
                            @if(blank($value) && $value !== false)
                                @continue
                            @endif
                            @if(is_array($value) || is_object($value))
                                @continue
                            @endif
                            <li><strong>{{ $formFieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}:</strong> {!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($post->tags))
                <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
                    @foreach($post->tags as $tag)
                        @php
                            $normalizedTag = \App\Models\CommunityTopicFollow::normalizeTopic((string) $tag);
                            $isFollowingTopic = auth()->check() && in_array($normalizedTag, $followedTopics, true);
                        @endphp
                        <span class="badge bg-light text-dark border">#{{ $tag }}</span>
                        @auth
                            @if($post->isPubliclyVisible())
                                <button type="button"
                                    class="btn btn-sm {{ $isFollowingTopic ? 'btn-success' : 'btn-outline-success' }} js-community-follow-topic {{ $isFollowingTopic ? 'is-following' : '' }}"
                                    data-url="{{ route('community.subscriptions.topic.toggle') }}"
                                    data-topic="{{ $tag }}">
                                    {{ $isFollowingTopic ? 'Following' : 'Follow topic' }}
                                </button>
                            @endif
                        @endauth
                    @endforeach
                </div>
            @endif

            @if($post->allowsSharing())
                @include('community.partials.share-panel', ['post' => $post, 'showInline' => true])
            @endif

            @if($post->allowsPoll())
                @include('community.partials.poll', ['post' => $post])
            @endif

            <div class="about-box mt-4">
                <h4>Community engagement</h4>
                <ul class="about-list mb-3">
                    <li><strong>Views:</strong> {{ number_format($post->views_count) }}</li>
                    <li><strong>Shares:</strong> {{ number_format($post->shares_count) }}</li>
                    @if($post->article_score > 0)
                        <li><strong>Article score:</strong> {{ number_format((float) $post->article_score, 1) }}/100</li>
                    @endif
                </ul>
                @php
                    $reactionCounts = $post->reactions->groupBy('reaction')->map->count();
                    $userReactions = auth()->check() ? $post->reactions->where('user_id', auth()->id())->pluck('reaction')->all() : [];
                    $reactionOptions = $post->content_type === 'reports' && filled(data_get($post->meta, 'report_type'))
                        ? [
                            'Support' => 'fa-solid fa-hand-holding-heart',
                            'Vote' => 'fa-solid fa-square-poll-vertical',
                            'Helpful' => 'fa-solid fa-circle-info',
                            'Informative' => 'fa-solid fa-lightbulb',
                            'Dislike' => 'fa-solid fa-thumbs-down',
                        ]
                        : [
                            'Helpful' => 'fa-solid fa-hand-holding-heart',
                            'Inspiring' => 'fa-solid fa-lightbulb',
                            'Excellent' => 'fa-solid fa-star',
                            'Informative' => 'fa-solid fa-circle-info',
                            'Dislike' => 'fa-solid fa-thumbs-down',
                        ];
                @endphp
                @auth
                    <div class="d-flex flex-wrap gap-2 mb-3" id="communityReactionButtons">
                        @foreach($reactionOptions as $reaction => $icon)
                            <form method="POST" action="{{ route('community.react', $post) }}" class="js-community-reaction-form">
                                @csrf
                                <input type="hidden" name="reaction" value="{{ $reaction }}">
                                <button type="submit" class="btn {{ in_array($reaction, $userReactions, true) ? 'btn-success' : 'btn-outline-success' }} btn-sm" data-reaction-button="{{ $reaction }}">
                                    <i class="{{ $icon }} me-1" aria-hidden="true"></i><span class="reaction-label">{{ $reaction }}</span> <span class="reaction-count">{{ $reactionCounts[$reaction] ?? 0 }}</span>
                                </button>
                            </form>
                        @endforeach
                        @if($post->showsAuthorProfileLink() && auth()->id() !== $post->user_id)
                            <form method="POST" action="{{ route('community.authors.follow', $post->user) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Follow Author</button>
                            </form>
                        @endif
                    </div>
                @else
                    <p><a href="{{ route('login') }}">Login</a> to react or follow this author.</p>
                @endauth
                <ul class="about-list mb-0">
                    <li>
                        Author:
                        @if($post->showsAuthorProfileLink())
                            <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}">{{ $post->authorDisplayName() }}</a>
                        @else
                            {{ $post->authorDisplayName() }}
                        @endif
                    </li>
                </ul>
            </div>

            @if($post->user_id && $post->resolvedPublishAs() !== \App\Models\CommunityPost::PUBLISH_AS_ANONYMOUS)
                @include('community.partials.author-questions', [
                    'author' => $post->user,
                    'post' => $post,
                    'answeredQuestions' => $answeredAuthorQuestions ?? collect(),
                ])
            @endif

            @include('community.partials.public-participation', [
                'post' => $post,
                'participationSuggestions' => $participationSuggestions ?? collect(),
                'participationFeedback' => $participationFeedback ?? collect(),
                'communityParticipationEvidence' => $communityParticipationEvidence ?? collect(),
            ])
        </section>
    </div>

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


@push('styles')
<style>
    .community-post-body {
        line-height: 1.8;
        overflow: auto;
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
</script>
<script src="{{ asset('assets/js/community-engagement.js') }}?v={{ now()->timestamp }}"></script>
@endpush
