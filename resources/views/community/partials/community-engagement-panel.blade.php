@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }

    $sidebarLayout = $sidebarLayout ?? false;
    $isFollowingAuthor = $isFollowingAuthor ?? false;
    $reactionCounts = $post->reactions->groupBy('reaction')->map->count();
    $userReactions = auth()->check() ? $post->reactions->where('user_id', auth()->id())->pluck('reaction')->all() : [];
    $reactionOptions = $post->reactionOptionsForDisplay();
@endphp

<div @class([
    'about-box mt-3 community-engagement-panel community-engagement-panel--article' => ! $sidebarLayout,
    'community-news-sidebar__card community-engagement-panel community-engagement-panel--sidebar' => $sidebarLayout,
])>
    @if($sidebarLayout)
        <p class="community-news-sidebar__label">Engagement</p>
    @endif

    <div @class([
        'community-engagement-stats' => ! $sidebarLayout,
        'community-engagement-bar__stats' => $sidebarLayout,
    ]) role="list">
        <div @class([
            'community-engagement-stat' => ! $sidebarLayout,
            'community-engagement-bar__stat' => $sidebarLayout,
        ]) role="listitem" title="Views">
            <i class="fa-solid fa-eye" aria-hidden="true"></i>
            @if($sidebarLayout)
                <span class="community-engagement-bar__stat-copy">
                    <strong>{{ number_format($post->views_count) }}</strong>
                    <small>Views</small>
                </span>
            @else
                <span class="community-engagement-stat__value">{{ number_format($post->views_count) }}</span>
                <span class="visually-hidden">Views</span>
            @endif
        </div>
        <div @class([
            'community-engagement-stat' => ! $sidebarLayout,
            'community-engagement-bar__stat' => $sidebarLayout,
        ]) role="listitem" title="Shares">
            <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
            @if($sidebarLayout)
                <span class="community-engagement-bar__stat-copy">
                    <strong>{{ number_format($post->shares_count) }}</strong>
                    <small>Shares</small>
                </span>
            @else
                <span class="community-engagement-stat__value">{{ number_format($post->shares_count) }}</span>
                <span class="visually-hidden">Shares</span>
            @endif
        </div>
        @if($post->article_score > 0)
            <div @class([
                'community-engagement-stat' => ! $sidebarLayout,
                'community-engagement-bar__stat' => $sidebarLayout,
            ]) role="listitem" title="Article score">
                <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
                @if($sidebarLayout)
                    <span class="community-engagement-bar__stat-copy">
                        <strong>{{ number_format((float) $post->article_score, 1) }}</strong>
                        <small>Score</small>
                    </span>
                @else
                    <span class="community-engagement-stat__value">{{ number_format((float) $post->article_score, 1) }}</span>
                    <span class="visually-hidden">Article score out of 100</span>
                @endif
            </div>
        @endif
    </div>

    @auth
        <div @class([
            'community-engagement-actions d-flex flex-wrap gap-2 mb-3' => ! $sidebarLayout,
            'community-engagement-bar__actions' => $sidebarLayout,
        ]) id="communityReactionButtons">
            @foreach($reactionOptions as $reaction => $icon)
                <form method="POST" action="{{ route('community.react', $post) }}" class="js-community-reaction-form">
                    @csrf
                    <input type="hidden" name="reaction" value="{{ $reaction }}">
                    <button type="submit" class="btn {{ in_array($reaction, $userReactions, true) ? 'btn-success' : 'btn-outline-success' }} btn-sm community-engagement-icon-btn" data-reaction-button="{{ $reaction }}" title="{{ $reaction }}" aria-label="{{ $reaction }} ({{ $reactionCounts[$reaction] ?? 0 }})">
                        <i class="{{ $icon }}" aria-hidden="true"></i>
                        <span class="reaction-count">{{ $reactionCounts[$reaction] ?? 0 }}</span>
                    </button>
                </form>
            @endforeach
            @if($post->showsAuthorProfileLink() && auth()->id() !== $post->user_id)
                <button type="button"
                    class="btn btn-sm community-engagement-icon-btn js-community-follow-author {{ $isFollowingAuthor ? 'btn-success is-following' : 'btn-outline-success' }}"
                    data-url="{{ route('community.authors.follow', $post->user) }}"
                    data-label-following="Unfollow"
                    data-label-unfollowed="Follow Author"
                    title="{{ $isFollowingAuthor ? 'Unfollow author' : 'Follow author' }}"
                    aria-label="{{ $isFollowingAuthor ? 'Unfollow author' : 'Follow author' }}">
                    <i class="fa-solid {{ $isFollowingAuthor ? 'fa-user-check' : 'fa-user-plus' }}" aria-hidden="true"></i>
                </button>
            @endif
        </div>
    @else
        <p @class(['small text-muted mb-0' => $sidebarLayout, 'small text-muted mb-3' => ! $sidebarLayout])>
            <a href="{{ route('login') }}">Login</a> to react or follow this author.
        </p>
    @endauth
</div>
