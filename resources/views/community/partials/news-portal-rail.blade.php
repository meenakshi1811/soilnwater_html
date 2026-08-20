@php
    $portalType = $portalType ?? 'news';
    $activeHub = $activeHub ?? \App\Support\CommunityContentTaxonomy::hubSectionForType($portalType);
    $activeCategory = $activeCategory ?? '';
    $breakingPosts = collect($breakingPosts ?? $featuredPortalPosts ?? $breakingNewsPosts ?? []);
    $trendingPosts = collect($trendingPosts ?? $trendingPortalPosts ?? $trendingNews ?? []);
    $relatedNews = collect($relatedNews ?? $relatedPortalPosts ?? []);
    $portalCopy = \App\Support\CommunityContentTaxonomy::portalCopy($portalType);

    $portalQuery = fn (array $extra = []) => array_filter(array_merge([
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
@endphp

<aside class="community-news-rail" aria-label="{{ $portalCopy['label_short'] }} highlights">
    @if(isset($post) && ($showPortalDetailRailExtras ?? $moveDetailExtrasToSidebar ?? false))
        @include('community.partials.portal-detail-sidebar-extras', [
            'post' => $post,
            'visibleMeta' => $visibleMeta ?? collect(),
            'formFieldLabels' => $formFieldLabels ?? [],
            'resolvedLocation' => $resolvedLocation ?? null,
            'attachments' => $sidebarAttachments ?? null,
            'railLocationOnly' => $railLocationOnly ?? false,
        ])
    @endif

    <div class="community-news-rail__card community-news-rail__card--breaking">
        <div class="community-news-rail__breaking-head">{{ $portalCopy['breaking_heading'] }}</div>
        <div class="community-news-rail__breaking-list">
            @forelse ($breakingPosts as $breakingPost)
                <a href="{{ route('community.show', $breakingPost) }}" class="community-news-breaking-item">
                    <span class="community-news-breaking-item__time">{{ $breakingPost->published_at?->format('g:i A') ?? 'Now' }}</span>
                    <span class="community-news-breaking-item__alert"><i class="fa-solid fa-circle-exclamation"></i></span>
                    <span class="community-news-breaking-item__title">{{ $breakingPost->title }}</span>
                </a>
            @empty
                <p class="community-news-rail__empty">No featured stories right now.</p>
            @endforelse
        </div>
        <a href="{{ route('community.index', $portalQuery(['filter' => 'editors'])) }}" class="btn btn-sm btn-outline-danger w-100">{{ $portalCopy['breaking_button'] }}</a>
    </div>

    <div class="community-news-rail__card">
        <h3 class="community-news-rail__title">{{ $portalCopy['trending_heading'] }}</h3>
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
        <a href="{{ route('community.index', $portalQuery(['sort' => 'views'])) }}" class="btn btn-sm btn-outline-secondary w-100">{{ $portalCopy['trending_button'] }}</a>
    </div>

    @if($relatedNews->isNotEmpty())
        <div class="community-news-rail__card">
            <h3 class="community-news-rail__title">{{ $portalCopy['related_heading'] }}</h3>
            <div class="community-news-rail__related-list">
                @foreach($relatedNews->take(4) as $relatedPost)
                    <a href="{{ route('community.show', $relatedPost) }}" class="news-detail-related-item">
                        @if($relatedPost->featuredImageUrl())
                            <img src="{{ $relatedPost->featuredImageUrl() }}" alt="" class="news-detail-related-item__thumb">
                        @endif
                        <div>
                            <p class="news-detail-related-item__title">{{ $relatedPost->title }}</p>
                            <div class="news-detail-related-item__meta">{{ $relatedPost->published_at?->diffForHumans() }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
            @if(filled($activeCategory))
                <a href="{{ route('community.index', $portalQuery(['category' => $activeCategory])) }}" class="btn btn-outline-success btn-sm w-100 mt-2">{{ $portalCopy['related_button'] }}</a>
            @endif
        </div>
    @endif

    <div class="community-news-rail__card community-news-rail__card--newsletter">
        <h3 class="community-news-rail__title">Newsletter</h3>
        <p>Get the latest community {{ strtolower($portalCopy['label_short']) }} delivered to your inbox.</p>
        @auth
            <a href="{{ route('community.subscriptions.index') }}" class="btn btn-success w-100">Manage subscriptions</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-success w-100">Subscribe</a>
        @endauth
    </div>

    @if(isset($post) && $post->allowsSharing())
        <div class="community-news-rail__card">
            <h3 class="community-news-rail__title">Share</h3>
            @include('community.partials.news-rail-share', ['post' => $post])
        </div>
    @else
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
    @endif
</aside>
