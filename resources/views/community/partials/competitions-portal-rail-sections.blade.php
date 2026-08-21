@php
    $post = $post ?? null;
    if (! $post?->isCompetitionsPost()) {
        return;
    }
@endphp

<div class="community-news-rail__competitions-extras" aria-label="Competition post details">
    @include('community.partials.competitions-intro-sections', [
        'post' => $post,
        'railLayout' => true,
    ])

    @include('community.partials.competitions-meta-details', [
        'post' => $post,
        'railLayout' => true,
    ])

    @include('community.partials.portal-rail-location-card', [
        'post' => $post,
        'title' => 'Location',
        'mapTitle' => 'Competition location map',
    ])

    @include('community.partials.competitions-additional-details', [
        'post' => $post,
        'railLayout' => true,
    ])
</div>
