@php
    $layout = $layout ?? 'list';
    $portalType = $portalType ?? 'news';
    $portalCopy = \App\Support\CommunityContentTaxonomy::portalCopy($portalType);
    $emptyMessage = $emptyMessage ?? ('No '.$portalCopy['label_short'].' posts found for this filter yet.');
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
            @auth
                <a href="{{ route('community.posts.create', ['type' => $portalType]) }}" class="btn btn-success btn-sm">{{ $portalCopy['empty_create_label'] }}</a>
            @endauth
        </div>
    @endif
@endforelse
