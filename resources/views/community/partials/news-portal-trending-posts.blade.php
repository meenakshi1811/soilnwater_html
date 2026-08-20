@php
    $layout = $layout ?? 'rail';
    $trendingPosts = collect($trendingPosts ?? $trendingPortalPosts ?? $trendingNews ?? []);
    $portalType = $portalType ?? $portalKey ?? 'news';
    $activeHub = $activeHub ?? \App\Support\CommunityContentTaxonomy::hubSectionForType($portalType);
    $activeCategory = $activeCategory ?? '';
    $portalCopy = $portalCopy ?? \App\Support\CommunityContentTaxonomy::portalCopy($portalType);

    $portalQuery = $portalQuery ?? fn (array $extra = []) => array_filter(array_merge([
        'type' => $portalType,
        'hub' => $activeHub,
        'category' => $activeCategory ?: null,
        'sort' => request('sort'),
        'filter' => request('filter'),
    ], $extra));

    $formatViews = function (?int $count): string {
        $count = (int) ($count ?? 0);

        return $count >= 1000 ? number_format($count / 1000, 1).'K' : number_format($count);
    };

    $wrapperClass = $layout === 'sidebar'
        ? 'community-news-sidebar__card community-news-sidebar__card--trending'
        : 'community-news-rail__card community-news-rail__card--trending';
@endphp

<div class="{{ $wrapperClass }}">
    <div class="community-news-trending-panel__head">
        <span class="community-news-trending-panel__icon" aria-hidden="true">
            <i class="fa-solid fa-fire-flame-curved"></i>
        </span>
        @if($layout === 'sidebar')
            <p class="community-news-sidebar__label mb-0">{{ $portalCopy['trending_heading'] }}</p>
        @else
            <h3 class="community-news-rail__title mb-0">{{ $portalCopy['trending_heading'] }}</h3>
        @endif
    </div>
    @if($trendingPosts->isNotEmpty())
        <ol class="community-news-trending community-news-trending--cards">
            @foreach ($trendingPosts as $index => $trendingPost)
                <li>
                    <a href="{{ route('community.show', $trendingPost) }}" class="community-news-trending-card">
                        <span class="community-news-trending-card__rank">{{ $index + 1 }}</span>
                        @if($trendingPost->featuredImageUrl())
                            <span class="community-news-trending-card__thumb">
                                <img src="{{ $trendingPost->featuredImageUrl() }}" alt="" loading="lazy">
                            </span>
                        @else
                            <span class="community-news-trending-card__thumb community-news-trending-card__thumb--placeholder" aria-hidden="true">
                                <i class="fa-solid fa-newspaper"></i>
                            </span>
                        @endif
                        <span class="community-news-trending-card__copy">
                            <strong>{{ $trendingPost->title }}</strong>
                            <span class="community-news-trending-card__meta">
                                <span><i class="fa-solid fa-eye" aria-hidden="true"></i>{{ $formatViews($trendingPost->views_count) }} views</span>
                                @if($trendingPost->published_at)
                                    <span><i class="fa-regular fa-clock" aria-hidden="true"></i>{{ $trendingPost->published_at->diffForHumans(short: true) }}</span>
                                @endif
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ol>
    @else
        <p class="community-news-rail__empty">No trending posts yet.</p>
    @endif
    <a href="{{ route('community.index', $portalQuery(['sort' => 'views'])) }}" class="btn btn-sm btn-outline-success w-100 community-news-trending-panel__cta">{{ $portalCopy['trending_button'] }}</a>
</div>
