@php
    $newsPortal = $newsPortal ?? [];
    $types = $types ?? \App\Support\CommunityContentTaxonomy::formTypes();
    $engagement = $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []];
    $activeCategory = $activeCategory ?? '';
    $activeHub = $activeHub ?? 'knowledge-news';
    $newsType = $types['news'] ?? ['label' => 'News', 'description' => 'Community and local news.', 'categories' => []];
    $sidebarTypes = \App\Support\CommunityContentTaxonomy::newsPortalSidebarTypes();
    $sidebarCategories = \App\Support\CommunityContentTaxonomy::newsPortalSidebarCategories();
    $categoryIcons = \App\Support\CommunityContentTaxonomy::newsCategoryIcons();
    $featured = $newsPortal['featured'] ?? null;
    $sidePosts = collect($newsPortal['sidePosts'] ?? []);
    $listPosts = collect($newsPortal['listPosts'] ?? []);
    $breakingPosts = collect($newsPortal['breakingPosts'] ?? []);
    $trendingPosts = collect($newsPortal['trendingPosts'] ?? []);
    $currentSort = request('sort', 'latest');
    $currentFilter = request('filter', '');

    $newsQuery = fn (array $extra = []) => array_filter(array_merge([
        'type' => 'news',
        'hub' => $activeHub,
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
        <aside class="community-news-sidebar" aria-label="News navigation">
            <div class="community-news-sidebar__card">
                <p class="community-news-sidebar__label">Community</p>
                <nav class="community-news-sidebar__nav">
                    @foreach ($sidebarTypes as $navItem)
                        <a
                            href="{{ route('community.index', ['type' => $navItem['key'], 'hub' => \App\Support\CommunityContentTaxonomy::hubSectionForType($navItem['key'])]) }}"
                            class="community-news-sidebar__link {{ $navItem['key'] === 'news' ? 'is-active' : '' }}"
                        >
                            <i class="fa-solid {{ $navItem['icon'] }}"></i>
                            <span>{{ $navItem['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="community-news-sidebar__card">
                <p class="community-news-sidebar__label">News Categories</p>
                <nav class="community-news-sidebar__categories">
                    @foreach ($sidebarCategories as $categoryName)
                        @php
                            $isAll = $categoryName === 'All News';
                            $isActive = $isAll ? $activeCategory === '' : $activeCategory === $categoryName;
                        @endphp
                        <a
                            href="{{ route('community.index', $newsQuery($isAll ? ['category' => null] : ['category' => $categoryName])) }}"
                            class="community-news-sidebar__category {{ $isActive ? 'is-active' : '' }}"
                        >{{ $categoryName }}</a>
                    @endforeach
                </nav>
                <a href="{{ route('community.index', $newsQuery()) }}" class="btn btn-sm btn-outline-success w-100 mt-2">View All Categories</a>
            </div>

            <div class="community-news-sidebar__card">
                <p class="community-news-sidebar__label">Quick Links</p>
                <nav class="community-news-sidebar__quick">
                    <a href="{{ route('community.index', $newsQuery(['sort' => 'latest', 'filter' => null])) }}">Today&rsquo;s Top News</a>
                    <a href="{{ route('community.index', $newsQuery(['sort' => 'views', 'filter' => null])) }}">Most Viewed</a>
                    <a href="{{ route('community.index', $newsQuery(['filter' => 'editors', 'sort' => null])) }}">Editor&rsquo;s Picks</a>
                    <a href="{{ route('community.index', ['type' => 'reports', 'hub' => 'knowledge-news']) }}">Community Reports</a>
                </nav>
            </div>
        </aside>

        <main class="community-news-main">
            <header class="community-news-main__header">
                <div class="community-news-main__heading">
                    <h1>News</h1>
                    <p>Stay updated with the latest news, updates and happenings in your community and around the world.</p>
                </div>
                @auth
                    <a href="{{ route('community.posts.create', ['type' => 'news']) }}" class="btn btn-success community-news-main__create">
                        <i class="fa-solid fa-pen-to-square me-1"></i>Publish News
                    </a>
                @endauth
            </header>

            <div class="community-news-tabs" role="tablist" aria-label="News categories">
                <a href="{{ route('community.index', $newsQuery(['category' => null])) }}" class="community-news-tabs__item {{ $activeCategory === '' ? 'is-active' : '' }}">
                    <span class="community-news-tabs__icon"><i class="fa-solid fa-layer-group"></i></span>
                    <span>All News</span>
                </a>
                @foreach (array_slice($newsType['categories'], 0, 8) as $categoryName)
                    @php $icon = $categoryIcons[$categoryName] ?? 'fa-tag'; @endphp
                    <a
                        href="{{ route('community.index', $newsQuery(['category' => $categoryName])) }}"
                        class="community-news-tabs__item {{ $activeCategory === $categoryName ? 'is-active' : '' }}"
                    >
                        <span class="community-news-tabs__icon"><i class="fa-solid {{ $icon }}"></i></span>
                        <span>{{ str_replace(' News', '', $categoryName) }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('community.index') }}" class="community-news-filters">
                <input type="hidden" name="type" value="news">
                <input type="hidden" name="hub" value="{{ $activeHub }}">
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
                <section class="community-news-hero" aria-label="Featured news">
                    <a href="{{ route('community.show', $featured) }}" class="community-news-hero__featured">
                        @if ($featured->featuredImageUrl())
                            <img src="{{ $featured->featuredImageUrl() }}" alt="{{ $featured->title }}">
                        @else
                            <div class="community-news-hero__featured-placeholder"><i class="fa-solid fa-newspaper"></i></div>
                        @endif
                        <div class="community-news-hero__featured-overlay">
                            <span class="community-news-hero__badge">TOP NEWS</span>
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
                                $sideCategory = $sidePost->category ?: 'News';
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
                            <div class="community-news-hero__side-empty">More featured stories will appear here.</div>
                        @endforelse
                    </div>
                </section>
            @endif

            <section class="community-news-latest" aria-labelledby="communityNewsLatestTitle">
                <div class="community-news-latest__head">
                    <h2 id="communityNewsLatestTitle">Latest News</h2>
                    <a href="{{ route('community.index', $newsQuery()) }}">View All News <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div id="communityNewsList" class="community-news-list" data-next-page-url="{{ $posts->nextPageUrl() }}">
                    @include('community.partials.news-list-items', [
                        'posts' => $listPosts,
                        'engagement' => $engagement,
                        'emptyMessage' => $emptyMessage ?? 'No news posts found for this filter yet.',
                    ])
                </div>
                @if ($posts->hasMorePages())
                    <button type="button" class="btn btn-outline-success community-news-load-more" id="communityNewsLoadMore">
                        Load More News <i class="fa-solid fa-chevron-down ms-1"></i>
                    </button>
                @endif
            </section>
        </main>

        <aside class="community-news-rail" aria-label="News highlights">
            <div class="community-news-rail__card community-news-rail__card--breaking">
                <div class="community-news-rail__breaking-head">BREAKING NEWS</div>
                <div class="community-news-rail__breaking-list">
                    @forelse ($breakingPosts as $breakingPost)
                        <a href="{{ route('community.show', $breakingPost) }}" class="community-news-breaking-item">
                            <span class="community-news-breaking-item__time">{{ $breakingPost->published_at?->format('g:i A') ?? 'Now' }}</span>
                            <span class="community-news-breaking-item__alert"><i class="fa-solid fa-circle-exclamation"></i></span>
                            <span class="community-news-breaking-item__title">{{ $breakingPost->title }}</span>
                        </a>
                    @empty
                        <p class="community-news-rail__empty">No breaking stories right now.</p>
                    @endforelse
                </div>
                <a href="{{ route('community.index', $newsQuery(['filter' => 'editors'])) }}" class="btn btn-sm btn-outline-danger w-100">View All Breaking News</a>
            </div>

            <div class="community-news-rail__card">
                <h3 class="community-news-rail__title">Trending News</h3>
                <ol class="community-news-trending">
                    @foreach ($trendingPosts as $index => $trendingPost)
                        <li>
                            <a href="{{ route('community.show', $trendingPost) }}">
                                <span class="community-news-trending__rank">{{ $index + 1 }}</span>
                                <span class="community-news-trending__copy">
                                    <strong>{{ $trendingPost->title }}</strong>
                                    <small>{{ $formatViews($trendingPost->views_count) }} Views</small>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
                <a href="{{ route('community.index', $newsQuery(['sort' => 'views'])) }}" class="btn btn-sm btn-outline-secondary w-100">View All Trending News</a>
            </div>

            <div class="community-news-rail__card community-news-rail__card--newsletter">
                <h3 class="community-news-rail__title">Newsletter</h3>
                <p>Get the latest community news delivered to your inbox.</p>
                @auth
                    <a href="{{ route('community.subscriptions.index') }}" class="btn btn-success w-100">Manage subscriptions</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-success w-100">Subscribe</a>
                @endauth
            </div>

            <div class="community-news-rail__card">
                <h3 class="community-news-rail__title">Follow Us</h3>
                <div class="community-news-social">
                    <span class="community-news-social__icon community-news-social__icon--facebook"><i class="fa-brands fa-facebook-f"></i></span>
                    <span class="community-news-social__icon community-news-social__icon--x"><i class="fa-brands fa-x-twitter"></i></span>
                    <span class="community-news-social__icon community-news-social__icon--instagram"><i class="fa-brands fa-instagram"></i></span>
                    <span class="community-news-social__icon community-news-social__icon--youtube"><i class="fa-brands fa-youtube"></i></span>
                    <span class="community-news-social__icon community-news-social__icon--linkedin"><i class="fa-brands fa-linkedin-in"></i></span>
                </div>
            </div>
        </aside>
    </div>

    <div class="community-pagination-wrap d-none" id="communityPaginationState">
        @if ($posts->total() > 0)
            <p class="community-pagination-summary" id="communitySummaryText">
                Showing 1 to {{ $posts->lastItem() }} of {{ $posts->total() }} results
            </p>
        @endif
        <p class="community-pagination-loading d-none" id="communityLoadingText">Loading more news…</p>
    </div>
    <div id="communityScrollSentinel" class="community-scroll-sentinel" aria-hidden="true"></div>
</div>
