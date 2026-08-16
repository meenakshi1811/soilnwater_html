@php
    $portalKey = $portalKey ?? $portalType ?? 'news';
    $activeType = $activeType ?? request('type', '');
    $contentPortal = $contentPortal ?? $newsPortal ?? [];
    $types = $types ?? \App\Support\CommunityContentTaxonomy::formTypes();
    $hubSections = $hubSections ?? \App\Support\CommunityContentTaxonomy::hubSections();
    $engagement = $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []];
    $activeCategory = $activeCategory ?? '';
    $activeHub = $activeHub ?? (\App\Support\CommunityContentTaxonomy::isHubPortalKey($portalKey)
        ? $portalKey
        : \App\Support\CommunityContentTaxonomy::hubSectionForType($activeType ?: $portalKey));
    $isHubPortalView = \App\Support\CommunityContentTaxonomy::isHubPortalKey($portalKey);
    $resolvedType = $activeType ?: ($isHubPortalView ? '' : $portalKey);
    $portalCopy = \App\Support\CommunityContentTaxonomy::portalCopy($portalKey);
    $portalTypeConfig = $isHubPortalView && $resolvedType === ''
        ? [
            'label' => $hubSections[$portalKey]['label'] ?? $portalCopy['label_short'] ?? 'Community',
            'description' => $hubSections[$portalKey]['description'] ?? '',
            'categories' => [],
        ]
        : ($types[$resolvedType] ?? ['label' => 'News', 'description' => 'Community updates.', 'categories' => []]);
    $sidebarCategories = \App\Support\CommunityContentTaxonomy::portalSidebarCategories($portalKey);
    $categoryIcons = \App\Support\CommunityContentTaxonomy::portalCategoryIcons($resolvedType ?: $portalKey);
    $featured = $contentPortal['featured'] ?? null;
    $sidePosts = collect($contentPortal['sidePosts'] ?? []);
    $listPosts = collect($contentPortal['listPosts'] ?? []);
    $breakingPosts = collect($contentPortal['breakingPosts'] ?? []);
    $trendingPosts = collect($contentPortal['trendingPosts'] ?? []);
    $currentSort = request('sort', 'latest');
    $currentFilter = request('filter', '');
    $portalLabel = $portalTypeConfig['label'];
    $portalLabelShort = $portalCopy['label_short'];
    $allCategoryLabel = $sidebarCategories[0] ?? ('All '.$portalLabel);
    $featuredBadge = $portalCopy['featured_badge'];
    $latestHeading = $portalCopy['latest_heading'];
    $createLabel = $portalCopy['create_label'];
    $loadMoreLabel = $portalCopy['load_more_label'];
    $emptyMessage = $emptyMessage ?? ('No '.$portalLabelShort.' posts found for this filter yet.');
    $featuredIcon = $portalCopy['featured_icon'];
    $hubTypeTabs = \App\Support\CommunityContentTaxonomy::hubPortalTypeTabs($portalKey);
    $createType = $resolvedType ?: \App\Support\CommunityContentTaxonomy::hubPortalDefaultCreateType($portalKey);

    $portalQuery = fn (array $extra = []) => array_filter(array_merge([
        'hub' => $activeHub,
        'type' => $resolvedType ?: null,
        'category' => $activeCategory ?: null,
        'sort' => request('sort'),
        'filter' => request('filter'),
    ], $extra));

    $formatViews = function (?int $count): string {
        $count = (int) ($count ?? 0);
        return $count >= 1000 ? number_format($count / 1000, 1).'K' : number_format($count);
    };
@endphp

<div class="community-news-portal">
    <div class="community-news-portal__layout">
        @include('community.partials.news-portal-sidebar', [
            'activeHub' => $activeHub,
            'activeCategory' => $activeCategory,
            'portalKey' => $portalKey,
            'activeType' => $activeType,
        ])

        <main class="community-news-main">
            <header class="community-news-main__header community-news-main__header--listing">
                <div class="community-news-main__header-row">
                <div class="community-news-main__heading">
                    <h1>{{ $portalLabel }}</h1>
                    <p>{{ $portalTypeConfig['description'] }}</p>
                </div>
                @auth
                    <a href="{{ route('community.posts.create', ['type' => $createType]) }}" class="btn btn-success community-news-main__create">
                        <i class="fa-solid fa-pen-to-square me-1"></i>{{ $createLabel }}
                    </a>
                @endauth
                </div>
            </header>

            @if ($isHubPortalView)
                <div class="community-news-type-grid" role="tablist" aria-label="{{ $portalLabel }} post types">
                    <div class="community-news-type-card {{ $activeType === '' ? 'is-active' : '' }}">
                        <a href="{{ route('community.index', ['hub' => $activeHub]) }}" class="community-news-type-card__browse">
                            <span class="community-news-type-card__icon"><i class="fa-solid fa-layer-group"></i></span>
                            <span class="community-news-type-card__label">{{ $allCategoryLabel }}</span>
                        </a>
                        @auth
                            <a href="{{ route('community.posts.create', ['type' => $createType]) }}" class="community-news-type-card__create">
                                {{ $createLabel }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="community-news-type-card__create">{{ $createLabel }}</a>
                        @endauth
                    </div>
                    @foreach ($hubTypeTabs as $typeTab)
                        <div class="community-news-type-card {{ $activeType === $typeTab['key'] ? 'is-active' : '' }}">
                            <a
                                href="{{ route('community.index', ['hub' => $activeHub, 'type' => $typeTab['key']]) }}"
                                class="community-news-type-card__browse"
                            >
                                <span class="community-news-type-card__icon"><i class="fa-solid {{ $typeTab['icon'] }}"></i></span>
                                <span class="community-news-type-card__label">{{ $typeTab['label'] }}</span>
                            </a>
                            @auth
                                <a href="{{ route('community.posts.create', ['type' => $typeTab['key']]) }}" class="community-news-type-card__create">
                                    Create {{ $typeTab['label'] }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="community-news-type-card__create">Create {{ $typeTab['label'] }}</a>
                            @endauth
                        </div>
                    @endforeach
                </div>
            @endif

            @if (! $isHubPortalView || $resolvedType !== '')
                <div class="community-news-tabs" role="tablist" aria-label="{{ $portalLabel }} categories">
                    <a href="{{ route('community.index', $portalQuery(['category' => null])) }}" class="community-news-tabs__item {{ $activeCategory === '' ? 'is-active' : '' }}">
                        <span class="community-news-tabs__icon"><i class="fa-solid fa-layer-group"></i></span>
                        <span>{{ $allCategoryLabel }}</span>
                    </a>
                    @foreach (array_slice($portalTypeConfig['categories'], 0, 8) as $categoryName)
                        @php $icon = $categoryIcons[$categoryName] ?? 'fa-tag'; @endphp
                        <a
                            href="{{ route('community.index', $portalQuery(['category' => $categoryName])) }}"
                            class="community-news-tabs__item {{ $activeCategory === $categoryName ? 'is-active' : '' }}"
                        >
                            <span class="community-news-tabs__icon"><i class="fa-solid {{ $icon }}"></i></span>
                            <span>{{ $portalKey === 'news' ? str_replace(' News', '', $categoryName) : $categoryName }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('community.index') }}" class="community-news-filters">
                <input type="hidden" name="hub" value="{{ $activeHub }}">
                @if ($resolvedType)
                    <input type="hidden" name="type" value="{{ $resolvedType }}">
                @endif
                @if ($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                @endif
                <div class="community-news-filters__group">
                    <span class="community-news-filters__label">Filter by:</span>
                    <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All coverage</option>
                        <option value="editors" @selected($currentFilter === 'editors')>Editor&rsquo;s picks</option>
                    </select>
                </div>
                <div class="community-news-filters__group community-news-filters__group--end">
                    <span class="community-news-filters__label">Sort by:</span>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="latest" @selected($currentSort === 'latest')>Latest</option>
                        <option value="views" @selected($currentSort === 'views')>Most viewed</option>
                    </select>
                </div>
            </form>

            @if ($featured)
                <section class="community-news-hero" aria-label="Featured {{ $portalLabelShort }}">
                    <a href="{{ route('community.show', $featured) }}" class="community-news-hero__featured">
                        @if ($featured->featuredImageUrl())
                            <img src="{{ $featured->featuredImageUrl() }}" alt="{{ $featured->title }}">
                        @else
                            <div class="community-news-hero__featured-placeholder"><i class="fa-solid {{ $featuredIcon }}"></i></div>
                        @endif
                        <div class="community-news-hero__featured-overlay">
                            <span class="community-news-hero__badge">{{ $featuredBadge }}</span>
                            <h2>{{ $featured->title }}</h2>
                            <p>{{ $featured->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($featured->body), 140) }}</p>
                            <div class="community-news-hero__meta">
                                <span>{{ $featured->authorDisplayName() }}</span>
                                <span>{{ $featured->published_at?->diffForHumans() }}</span>
                                <span>{{ $formatViews($featured->views_count) }} Views</span>
                            </div>
                        </div>
                    </a>
                    <div class="community-news-hero__side">
                        @forelse ($sidePosts as $sidePost)
                            @php
                                $sideCategory = $sidePost->category ?: ($types[$sidePost->content_type]['label'] ?? $portalLabelShort);
                                $sideCategorySlug = \Illuminate\Support\Str::slug($sideCategory);
                            @endphp
                            <a href="{{ route('community.show', $sidePost) }}" class="community-news-hero__side-card">
                                <div class="community-news-hero__side-thumb">
                                    @if ($sidePost->featuredImageUrl())
                                        <img src="{{ $sidePost->featuredImageUrl() }}" alt="{{ $sidePost->title }}" loading="lazy">
                                    @else
                                        <div class="community-news-hero__side-thumb-placeholder"><i class="fa-solid fa-image"></i></div>
                                    @endif
                                </div>
                                <div class="community-news-hero__side-body">
                                    <span class="community-news-hero__side-category community-news-list-item__category--{{ $sideCategorySlug }}">{{ strtoupper($sideCategory) }}</span>
                                    <h3>{{ $sidePost->title }}</h3>
                                    <p>{{ $sidePost->published_at?->diffForHumans() }} &bull; {{ $formatViews($sidePost->views_count) }} Views</p>
                                </div>
                            </a>
                        @empty
                            <div class="community-news-hero__side-empty">More featured reads will appear here.</div>
                        @endforelse
                    </div>
                </section>
            @endif

            <section class="community-news-latest" aria-labelledby="communityNewsLatestTitle">
                <div class="community-news-latest__head">
                    <h2 id="communityNewsLatestTitle">{{ $latestHeading }}</h2>
                    <a href="{{ route('community.index', $portalQuery()) }}">View All {{ $portalLabelShort }} <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div id="communityNewsList" class="community-news-list" data-next-page-url="{{ $posts->nextPageUrl() }}">
                    @include('community.partials.news-list-items', [
                        'posts' => $listPosts,
                        'engagement' => $engagement,
                        'emptyMessage' => $emptyMessage,
                        'portalType' => $portalKey,
                        'activeHub' => $activeHub,
                        'resolvedType' => $resolvedType,
                    ])
                </div>
                @if ($posts->hasMorePages())
                    <button type="button" class="btn btn-outline-success community-news-load-more" id="communityNewsLoadMore">
                        {{ $loadMoreLabel }} <i class="fa-solid fa-chevron-down ms-1"></i>
                    </button>
                @endif
            </section>
        </main>

        @include('community.partials.news-portal-rail', [
            'activeHub' => $activeHub,
            'activeCategory' => $activeCategory,
            'breakingPosts' => $breakingPosts,
            'trendingPosts' => $trendingPosts,
            'portalType' => $portalKey,
        ])
    </div>

    <div class="community-pagination-wrap d-none" id="communityPaginationState">
        @if ($posts->total() > 0)
            <p class="community-pagination-summary" id="communitySummaryText">
                Showing 1 to {{ $posts->lastItem() }} of {{ $posts->total() }} results
            </p>
        @endif
        <p class="community-pagination-loading d-none" id="communityLoadingText">Loading more {{ strtolower($portalLabelShort) }}…</p>
    </div>
    <div id="communityScrollSentinel" class="community-scroll-sentinel" aria-hidden="true"></div>
</div>
