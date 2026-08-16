@php
    $layout = $layout ?? 'list';
    $portalType = $portalType ?? 'news';
    $activeHub = $activeHub ?? null;
    $resolvedType = $resolvedType ?? '';
    $portalCopy = \App\Support\CommunityContentTaxonomy::portalCopy($portalType);
    $emptyMessage = $emptyMessage ?? ('No '.$portalCopy['label_short'].' posts found for this filter yet.');
    $hubKey = \App\Support\CommunityContentTaxonomy::isHubPortalKey($portalType)
        ? $portalType
        : (\App\Support\CommunityContentTaxonomy::isHubPortalKey($activeHub) ? $activeHub : null);
    $hubTypeTabs = $hubKey ? \App\Support\CommunityContentTaxonomy::hubPortalTypeTabs($hubKey) : [];
    $createType = $resolvedType ?: (
        \App\Support\CommunityContentTaxonomy::isHubPortalKey($portalType)
            ? \App\Support\CommunityContentTaxonomy::hubPortalDefaultCreateType($portalType)
            : $portalType
    );
@endphp
@forelse ($posts as $post)
    @include('community.partials.news-list-item', [
        'post' => $post,
        'engagement' => $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []],
        'portalType' => $portalType,
    ])
@empty
    @if(($layout ?? 'list') !== 'append')
        <div class="community-news-empty">
            <div class="community-news-empty__icon"><i class="fa-solid {{ $portalCopy['featured_icon'] }}"></i></div>
            <h3>No {{ strtolower($portalCopy['label_short']) }} yet</h3>
            <p>{{ $emptyMessage }}</p>
            <div class="community-news-empty__actions">
                @if ($hubTypeTabs !== [])
                    @foreach ($hubTypeTabs as $typeTab)
                        @auth
                            <a href="{{ route('community.posts.create', ['type' => $typeTab['key']]) }}" class="btn btn-success btn-sm">
                                Create {{ $typeTab['label'] }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-success btn-sm">
                                Create {{ $typeTab['label'] }}
                            </a>
                        @endauth
                    @endforeach
                @else
                    @auth
                        <a href="{{ route('community.posts.create', ['type' => $createType]) }}" class="btn btn-success btn-sm">
                            {{ $portalCopy['empty_create_label'] }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-success btn-sm">
                            {{ $portalCopy['empty_create_label'] }}
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    @endif
@endforelse
