@php
    $structuredLocation = $post->structuredLocationForDisplay();
    $locationLabel = $structuredLocation->isNotEmpty()
        ? $structuredLocation->values()->implode(', ')
        : data_get($post->meta, 'news_dateline');
    $authorName = $post->authorDisplayName();
    $authorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authorName, 0, 1));
    $newsType = data_get($post->meta, 'news_type');
@endphp

<aside class="news-detail-sidebar">
    <div class="news-detail-card">
        <div class="news-detail-card__head">News Details</div>
        <div class="news-detail-card__body">
            <div class="news-detail-kv">
                @if(filled($post->category))
                    <div class="news-detail-kv__row">
                        <span class="news-detail-kv__label">Category</span>
                        <span class="news-detail-kv__value">{{ $post->category }}</span>
                    </div>
                @endif
                @if(filled($newsType))
                    <div class="news-detail-kv__row">
                        <span class="news-detail-kv__label">Type</span>
                        <span class="news-detail-kv__value">{{ $newsType }}</span>
                    </div>
                @endif
                @if(filled($locationLabel))
                    <div class="news-detail-kv__row">
                        <span class="news-detail-kv__label">Location</span>
                        <span class="news-detail-kv__value">{{ $locationLabel }}</span>
                    </div>
                @endif
                @if($post->published_at)
                    <div class="news-detail-kv__row">
                        <span class="news-detail-kv__label">Published</span>
                        <span class="news-detail-kv__value">{{ $post->published_at->format('M d, Y g:i A') }}</span>
                    </div>
                @endif
                <div class="news-detail-kv__row">
                    <span class="news-detail-kv__label">Views</span>
                    <span class="news-detail-kv__value">{{ number_format($post->views_count) }}</span>
                </div>
                <div class="news-detail-kv__row">
                    <span class="news-detail-kv__label">Shares</span>
                    <span class="news-detail-kv__value">{{ number_format($post->shares_count) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="news-detail-card">
        <div class="news-detail-card__head">Author</div>
        <div class="news-detail-card__body news-detail-author-card">
            @if(filled($post->authorAvatarUrl()))
                <img src="{{ $post->authorAvatarUrl() }}" alt="" class="news-detail-author-card__avatar">
            @else
                <span class="news-detail-author-card__initials" aria-hidden="true">{{ $authorInitial }}</span>
            @endif
            <div class="news-detail-author-card__name">
                @if($post->showsAuthorProfileLink())
                    <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="text-decoration-none text-dark">{{ $authorName }}</a>
                @else
                    {{ $authorName }}
                @endif
            </div>
            <div class="news-detail-author-card__stats">
                <span>{{ number_format($authorPostCount ?? 0) }} Articles</span>
            </div>
            @auth
                @if($post->showsAuthorProfileLink() && auth()->id() !== $post->user_id)
                    <button type="button"
                        class="btn btn-success btn-sm w-100 mb-2 js-community-follow-author {{ $isFollowingAuthor ? 'is-following' : '' }}"
                        data-url="{{ route('community.authors.follow', $post->user) }}"
                        data-label-following="Following"
                        data-label-unfollowed="Follow Author">
                        {{ $isFollowingAuthor ? 'Following' : 'Follow' }}
                    </button>
                @endif
            @endauth
            @if($post->showsAuthorProfileLink())
                <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="btn btn-outline-secondary btn-sm w-100">View Profile</a>
            @endif
        </div>
    </div>

    @if(($relatedNews ?? collect())->isNotEmpty())
        <div class="news-detail-card">
            <div class="news-detail-card__head">Related News</div>
            <div class="news-detail-card__body">
                @foreach($relatedNews->take(3) as $related)
                    <a href="{{ $related->publicUrl() }}" class="news-detail-related-item">
                        @if($related->featuredImageUrl())
                            <img src="{{ $related->featuredImageUrl() }}" alt="" class="news-detail-related-item__thumb">
                        @endif
                        <div>
                            <p class="news-detail-related-item__title">{{ $related->title }}</p>
                            <div class="news-detail-related-item__meta">{{ $related->published_at?->diffForHumans() }}</div>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('community.index', ['type' => 'news', 'category' => $post->category]) }}" class="btn btn-outline-success btn-sm w-100 mt-2">View More Related News</a>
            </div>
        </div>
    @endif

    @if(($trendingNews ?? collect())->isNotEmpty())
        <div class="news-detail-card">
            <div class="news-detail-card__head">Trending News</div>
            <div class="news-detail-card__body">
                @foreach($trendingNews as $index => $trending)
                    <a href="{{ $trending->publicUrl() }}" class="news-detail-trending-item">
                        <span class="news-detail-trending-item__num">{{ $index + 1 }}</span>
                        <div>
                            <p class="news-detail-trending-item__title">{{ $trending->title }}</p>
                            <div class="news-detail-trending-item__views">{{ number_format($trending->views_count) }} views</div>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('community.index', ['type' => 'news']) }}" class="btn btn-outline-success btn-sm w-100 mt-2">View All Trending News</a>
            </div>
        </div>
    @endif

    <div class="news-detail-card">
        <div class="news-detail-card__head">Follow Us</div>
        <div class="news-detail-card__body">
            <div class="news-detail-socials">
                <a href="https://www.facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://x.com" target="_blank" rel="noopener" class="news-detail-socials__x" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="https://www.instagram.com" target="_blank" rel="noopener" class="news-detail-socials__ig" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.youtube.com" target="_blank" rel="noopener" class="news-detail-socials__yt" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                <a href="https://www.linkedin.com" target="_blank" rel="noopener" class="news-detail-socials__in" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>
</aside>
