@php
    $authorName = $authorName ?? null;
    $activeType = $activeType ?? '';
    $activeHub = $activeHub ?? null;
@endphp

<section class="community-hero">
    <div class="community-hero__inner">
        @if(($showNav ?? true) && !isset($activeAuthor))
            @include('community.partials.community-portal-nav', [
                'navContext' => 'listing',
                'portalKey' => $activeType ?: $activeHub,
                'activeType' => $activeType,
                'activeHub' => $activeHub,
                'resolvedType' => $activeType,
                'navTheme' => 'dark',
            ])
        @endif
        <div class="community-hero__top">
            <div class="community-hero__copy">
                @if ($authorName && isset($activeAuthor))
                    <div class="community-hero__profile">
                        @include('community.partials.author-avatar', [
                            'avatarUrl' => $activeAuthor->authorImageUrl(),
                            'initials' => $activeAuthor->authorInitials(),
                            'alt' => $authorName,
                            'sizeClass' => 'community-hero__avatar',
                        ])
                        <div>
                            <h1 class="community-hero__title mb-2">{{ $authorName }}&rsquo;s Posts</h1>
                            <p class="community-hero__subtitle mb-0">
                                Browse published stories, reports, and updates from {{ $authorName }}.
                            </p>
                        </div>
                    </div>
                @else
                    <h1 class="community-hero__title">
                        {{ $authorName ? $authorName . "'s Posts" : 'Community Hub' }}
                    </h1>
                    @if ($authorName)
                        <p class="community-hero__subtitle">
                            Browse published stories, reports, and updates from {{ $authorName }}.
                        </p>
                    @else
                        <p class="community-hero__subtitle">Community Hub and Knowledge Centre</p>
                    @endif
                @endif
            </div>
            @if (!isset($activeAuthor) && !empty($hubStats))
                <div class="community-hero__stats">
                    @foreach ($hubStats as $stat)
                        <div class="community-hero__stat-block">
                            <i class="fa-solid {{ $stat['icon'] }}" aria-hidden="true"></i>
                            <span class="community-hero__stat-value">{{ $stat['value'] }}</span>
                            <span class="community-hero__stat-label">{{ $stat['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="community-hero__actions">
            @auth
                <a href="{{ route('community.saved.index') }}" class="community-hero__ghost">
                    <i class="fa-solid fa-bookmark"></i> Saved Posts
                </a>
                <a href="{{ route('community.subscriptions.index') }}" class="community-hero__ghost">
                    <i class="fa-solid fa-bell"></i> My Subscriptions
                </a>
            @endauth
            @if (($posts->total() ?? 0) > 0)
                <span class="community-hero__stat">
                    <i class="fa-solid fa-file-lines"></i>{{ number_format($posts->total()) }} published {{ \Illuminate\Support\Str::plural('post', $posts->total()) }}
                </span>
            @endif
        </div>
    </div>
</section>
