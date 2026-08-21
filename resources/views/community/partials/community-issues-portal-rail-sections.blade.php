@php
    $post = $post ?? null;
    if (! $post?->isCommunityIssuesPost()) {
        return;
    }
@endphp

<div class="community-news-rail__community-issues-extras" aria-label="Community issue post details">
    @include('community.partials.community-issues-meta-details', [
        'post' => $post,
        'railLayout' => true,
    ])

    @include('community.partials.portal-rail-location-card', [
        'post' => $post,
        'title' => 'Location',
        'mapTitle' => 'Community issue location map',
        'landmark' => data_get($post->meta, 'location_landmark'),
    ])

    @include('community.partials.community-issues-additional-details', [
        'post' => $post,
        'railLayout' => true,
    ])
</div>
