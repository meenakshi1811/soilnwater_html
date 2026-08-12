@php
    $activeHub = $activeHub ?? 'knowledge-news';
    $activeCategory = $activeCategory ?? '';
    $sidebarTypes = \App\Support\CommunityContentTaxonomy::newsPortalSidebarTypes();
    $sidebarCategories = \App\Support\CommunityContentTaxonomy::newsPortalSidebarCategories();

    $newsQuery = fn (array $extra = []) => array_filter(array_merge([
        'type' => 'news',
        'hub' => $activeHub,
        'category' => $activeCategory ?: null,
        'sort' => request('sort'),
        'filter' => request('filter'),
    ], $extra));
@endphp

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
