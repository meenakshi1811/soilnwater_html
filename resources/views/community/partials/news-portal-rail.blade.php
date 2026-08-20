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
            'railHubExtras' => $moveHubExtrasToSidebar ?? false,
        ])
    @endif

    @if(isset($post) && ($showPortalRatingRail ?? false))
        @include('community.partials.story-rating', [
            'post' => $post,
            'railLayout' => true,
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

    <div class="community-news-rail__card community-news-rail__card--trending">
        <div class="community-news-trending-panel__head">
            <span class="community-news-trending-panel__icon" aria-hidden="true">
                <i class="fa-solid fa-fire-flame-curved"></i>
            </span>
            <h3 class="community-news-rail__title mb-0">{{ $portalCopy['trending_heading'] }}</h3>
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
