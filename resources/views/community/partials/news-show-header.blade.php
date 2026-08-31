@php
    $portalType = $portalType ?? $post->content_type ?? 'news';
    $activeHub = $activeHub ?? \App\Support\CommunityContentTaxonomy::hubSectionForType($portalType);
    $portalCopy = \App\Support\CommunityContentTaxonomy::portalCopy($portalType);
    $types = \App\Support\CommunityContentTaxonomy::formTypes();
    $portalLabel = $types[$portalType]['label'] ?? 'News';
    $newsPriority = data_get($post->meta, 'news_priority');
    $newsSource = data_get($post->meta, 'news_source') ?: data_get($post->meta, 'reporter_name');
    $locationParts = $post->structuredLocationForDisplay()->only(['location_city', 'location_state', 'location_district', 'location_country'])->filter()->values();
    $locationLabel = $locationParts->isNotEmpty() ? $locationParts->implode(', ') : data_get($post->meta, 'news_dateline');
    $deck = $post->excerpt ?: data_get($post->meta, 'news_subtitle');
    $isTopNews = $post->is_featured || $post->is_highlighted;
    $isBreaking = $newsPriority === 'Breaking';
    $authorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($post->authorDisplayName(), 0, 1));
    $coverUrl = $post->featuredImageUrl();
    $showsFeaturedInDetailBody = filled($coverUrl) && in_array($post->content_type, [
        'religion-spirituality',
        'astro-consultancy',
        'agriculture',
        'environment',
        'creative-corner',
        'community-issues',
    ], true);
    $portalIndexUrl = route('community.index', array_filter(['type' => $portalType, 'hub' => $activeHub]));
    $topBadge = $portalCopy['top_badge'];
    $breakingBadge = $portalCopy['breaking_badge'];
    $backLabel = $portalCopy['back_label'];
@endphp

<header class="community-news-main__header community-news-main__header--detail">
    <div class="community-news-detail__toolbar">
        <div class="community-news-detail__toolbar-main">
            @include('community.partials.community-portal-nav', [
                'navContext' => 'detail',
                'portalType' => $portalType,
                'activeHub' => $activeHub,
                'currentLabel' => $post->title,
            ])
        </div>
        <div class="community-news-detail__actions">
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
</header>

<article class="community-news-detail">
    @if($post->isReligionSpiritualityPost())
        <div class="community-news-detail__lead">
            @include('community.partials.religion-spirituality-show-overview', [
                'post' => $post,
                'detailLeadLayout' => true,
            ])
        </div>
    @endif

    @if($coverUrl && ! $showsFeaturedInDetailBody)
        <figure class="community-news-detail__hero">
            <img src="{{ $coverUrl }}" alt="{{ $post->title }}">
        </figure>
    @endif

    <div class="community-news-detail__content">
        @if($isBreaking && $portalType === 'news')
            <span class="news-detail-badge news-detail-badge--breaking">{{ $breakingBadge }}</span>
        @elseif($isTopNews)
            <span class="news-detail-badge">{{ $topBadge }}</span>
        @elseif(filled($post->category))
            <span class="news-detail-badge">{{ $post->category }}</span>
        @endif

        <h1 class="news-detail-title">{{ $post->title }}</h1>

        @if(filled($deck))
            <p class="news-detail-deck">{{ $deck }}</p>
        @endif

        <div class="news-detail-byline">
            <div class="news-detail-author">
                @if(filled($post->authorAvatarUrl()))
                    <span class="news-detail-author__avatar"><img src="{{ $post->authorAvatarUrl() }}" alt=""></span>
                @else
                    <span class="news-detail-author__initials" aria-hidden="true">{{ $authorInitial }}</span>
                @endif
                <div>
                    <p class="news-detail-author__name">
                        @if($post->showsAuthorProfileLink())
                            <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}">{{ $post->authorDisplayName() }}</a>
                        @else
                            {{ $post->authorDisplayName() }}
                        @endif
                    </p>
                    @if(filled($newsSource))
                        <small class="text-muted">{{ $newsSource }}</small>
                    @endif
                </div>
            </div>

            @auth
                @if($post->showsAuthorProfileLink() && auth()->id() !== $post->user_id)
                    <button type="button"
                        class="btn btn-sm {{ $isFollowingAuthor ? 'btn-success' : 'btn-outline-success' }} js-community-follow-author {{ $isFollowingAuthor ? 'is-following' : '' }}"
                        data-url="{{ route('community.authors.follow', $post->user) }}"
                        data-label-following="Following"
                        data-label-unfollowed="Follow">
                        {{ $isFollowingAuthor ? 'Following' : 'Follow' }}
                    </button>
                @endif
            @endauth

            <div class="news-detail-meta">
                @if($post->published_at)
                    <span><i class="fa-regular fa-clock"></i>{{ $post->published_at->diffForHumans() }}</span>
                @endif
                @if(filled($locationLabel))
                    <span><i class="fa-solid fa-location-dot"></i>{{ $locationLabel }}</span>
                @endif
                <span><i class="fa-regular fa-eye"></i>{{ number_format($post->views_count) }} Views</span>
            </div>
        </div>
    </div>
</article>
