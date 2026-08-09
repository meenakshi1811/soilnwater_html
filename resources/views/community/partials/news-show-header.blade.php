@php
    $newsPriority = data_get($post->meta, 'news_priority');
    $newsSource = data_get($post->meta, 'news_source') ?: data_get($post->meta, 'reporter_name');
    $locationParts = $post->structuredLocationForDisplay()->only(['city', 'state', 'district', 'country'])->filter()->values();
    $locationLabel = $locationParts->isNotEmpty() ? $locationParts->implode(', ') : data_get($post->meta, 'news_dateline');
    $deck = $post->excerpt ?: data_get($post->meta, 'news_subtitle');
    $isTopNews = $post->is_featured || $post->is_highlighted;
    $isBreaking = $newsPriority === 'Breaking';
    $authorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($post->authorDisplayName(), 0, 1));
@endphp

<section class="news-detail-header">
    <div class="news-detail-header__container">
        <nav class="news-detail-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span>›</span>
            <a href="{{ route('community.index') }}">Community Hub</a><span>›</span>
            <a href="{{ route('community.index', ['type' => 'news']) }}">News</a><span>›</span>
            <span aria-current="page">{{ \Illuminate\Support\Str::limit($post->title, 60) }}</span>
        </nav>

        @if($isBreaking)
            <span class="news-detail-badge news-detail-badge--breaking">Breaking News</span>
        @elseif($isTopNews)
            <span class="news-detail-badge">Top News</span>
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
</section>
