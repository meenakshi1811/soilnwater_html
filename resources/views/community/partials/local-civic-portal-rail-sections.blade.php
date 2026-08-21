@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }
@endphp

@if($post->isAwarenessPost())
    @include('community.partials.awareness-portal-rail-sections', ['post' => $post])
@elseif($post->isLocalVoicesPost())
    @include('community.partials.local-voices-portal-rail-sections', ['post' => $post])
@elseif($post->isCommunityIssuesPost())
    @include('community.partials.community-issues-portal-rail-sections', [
        'post' => $post,
        'reportEngagement' => $reportEngagement ?? null,
    ])
@elseif($post->content_type === 'opinions-views')
    <div class="community-news-rail__opinions-views-extras" aria-label="Opinion post details">
        @include('community.partials.portal-rail-location-card', [
            'post' => $post,
            'title' => 'Location',
            'mapTitle' => 'Opinion location map',
        ])
    </div>
@endif
