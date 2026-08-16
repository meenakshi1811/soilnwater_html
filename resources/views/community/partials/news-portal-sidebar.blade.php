@php
    $portalKey = $portalKey ?? $portalType ?? 'news';
    $activeType = $activeType ?? request('type', '');
    $activeHub = $activeHub ?? (\App\Support\CommunityContentTaxonomy::isHubPortalKey($portalKey)
        ? $portalKey
        : \App\Support\CommunityContentTaxonomy::hubSectionForType($activeType ?: $portalKey));
    $activeCategory = $activeCategory ?? '';
    $sidebarTypes = \App\Support\CommunityContentTaxonomy::portalSidebarTypes($portalKey, $activeHub);
    $sidebarCategories = \App\Support\CommunityContentTaxonomy::portalSidebarCategories($portalKey);
    $types = \App\Support\CommunityContentTaxonomy::formTypes();
    $portalCopy = \App\Support\CommunityContentTaxonomy::portalCopy($portalKey);
    $allCategoryLabel = $sidebarCategories[0] ?? ('All '.($types[$portalKey]['label'] ?? 'News'));
    $categoriesHeading = $portalCopy['categories_heading'];
    $usesTypeFilters = \App\Support\CommunityContentTaxonomy::portalSidebarUsesTypeFilters($portalKey);
    $hubTypeFilterLabels = \App\Support\CommunityContentTaxonomy::hubPortalTypeFilterLabels(
        \App\Support\CommunityContentTaxonomy::isHubPortalKey($portalKey) ? $portalKey : $activeHub
    );
    $isHubPortalView = \App\Support\CommunityContentTaxonomy::isHubPortalKey($portalKey);
    $resolvedType = $activeType ?: ($isHubPortalView ? '' : $portalKey);
    $portalLabel = $usesTypeFilters
        ? ($portalCopy['label_short'])
        : ($types[$resolvedType]['label'] ?? 'News');

    $portalQuery = fn (array $extra = []) => array_filter(array_merge([
        'hub' => $activeHub,
        'type' => $resolvedType ?: null,
        'category' => $activeCategory ?: null,
        'sort' => request('sort'),
        'filter' => request('filter'),
    ], $extra));
@endphp

<aside class="community-news-sidebar" aria-label="{{ $portalLabel }} navigation">
    <div class="community-news-sidebar__card">
        <p class="community-news-sidebar__label">Community</p>
        <nav class="community-news-sidebar__nav">
            @foreach ($sidebarTypes as $navItem)
                <a
                    href="{{ route('community.index', ['type' => $navItem['key'], 'hub' => \App\Support\CommunityContentTaxonomy::hubSectionForType($navItem['key'])]) }}"
                    class="community-news-sidebar__link {{ ($usesTypeFilters && $activeType === $navItem['key']) || (! $usesTypeFilters && $navItem['key'] === $resolvedType) ? 'is-active' : '' }}"
                >
                    <i class="fa-solid {{ $navItem['icon'] }}"></i>
                    <span>{{ $navItem['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="community-news-sidebar__card">
        <p class="community-news-sidebar__label">{{ $categoriesHeading }}</p>
        <nav class="community-news-sidebar__categories">
            @foreach ($sidebarCategories as $categoryName)
                @php
                    $isAll = $categoryName === $allCategoryLabel;
                    if ($usesTypeFilters) {
                        $typeFilter = $hubTypeFilterLabels[$categoryName] ?? null;
                        $isActive = $isAll ? $activeType === '' : $activeType === $typeFilter;
                        $categoryHref = $isAll
                            ? route('community.index', ['hub' => $activeHub])
                            : route('community.index', ['hub' => $activeHub, 'type' => $typeFilter]);
                    } else {
                        $isActive = $isAll ? $activeCategory === '' : $activeCategory === $categoryName;
                        $categoryHref = route('community.index', $portalQuery($isAll ? ['category' => null] : ['category' => $categoryName]));
                    }
                @endphp
                <a
                    href="{{ $categoryHref }}"
                    class="community-news-sidebar__category {{ $isActive ? 'is-active' : '' }}"
                >{{ $categoryName }}</a>
            @endforeach
        </nav>
        <a href="{{ route('community.index', $usesTypeFilters ? ['hub' => $activeHub] : $portalQuery()) }}" class="btn btn-sm btn-outline-success w-100 mt-2">View All Categories</a>
    </div>

    <div class="community-news-sidebar__card">
        <p class="community-news-sidebar__label">Quick Links</p>
        <nav class="community-news-sidebar__quick">
            <a href="{{ route('community.index', $portalQuery(['sort' => 'latest', 'filter' => null])) }}">Today&rsquo;s Top {{ $portalLabel }}</a>
            <a href="{{ route('community.index', $portalQuery(['sort' => 'views', 'filter' => null])) }}">Most Viewed</a>
            <a href="{{ route('community.index', $portalQuery(['filter' => 'editors', 'sort' => null])) }}">Editor&rsquo;s Picks</a>
            @if($portalKey === 'news')
                <a href="{{ route('community.index', ['type' => 'reports', 'hub' => 'knowledge-news']) }}">Community Reports</a>
            @elseif($portalKey === 'reports')
                <a href="{{ route('community.index', ['type' => 'articles', 'hub' => 'knowledge-news']) }}">Community Articles</a>
            @elseif($portalKey === 'articles')
                <a href="{{ route('community.index', ['type' => 'reports', 'hub' => 'knowledge-news']) }}">Community Reports</a>
            @elseif(\App\Support\CommunityContentTaxonomy::resolveHubPortalKey($portalKey, $activeHub))
                <a href="{{ route('community.index', ['hub' => 'knowledge-news', 'type' => 'news']) }}">Community News</a>
            @else
                <a href="{{ route('community.index', ['type' => 'news', 'hub' => 'knowledge-news']) }}">Community News</a>
            @endif
        </nav>
    </div>
</aside>
