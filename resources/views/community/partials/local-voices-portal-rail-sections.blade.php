@php
    $post = $post ?? null;
    if (! $post?->isLocalVoicesPost()) {
        return;
    }
@endphp

<div class="community-news-rail__local-voices-extras" aria-label="Local Voices post details">
    @include('community.partials.local-voices-meta-details', [
        'post' => $post,
        'railLayout' => true,
    ])

    @include('community.partials.portal-rail-location-card', [
        'post' => $post,
        'title' => 'Location',
        'mapTitle' => 'Local Voices location map',
    ])

    @include('community.partials.local-voices-additional-details', [
        'post' => $post,
        'railLayout' => true,
    ])
</div>
