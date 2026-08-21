@php
    $post = $post ?? null;
    if (! $post?->isCreativeCornerPost()) {
        return;
    }
@endphp

<div class="community-news-rail__creative-corner-extras" aria-label="Creative Corner post details">
    @include('community.partials.creative-corner-meta-details', [
        'post' => $post,
        'railLayout' => true,
    ])

    @include('community.partials.creative-corner-portal-rail-location', ['post' => $post])

    @include('community.partials.creative-corner-additional-details', [
        'post' => $post,
        'railLayout' => true,
    ])

    @include('community.partials.creative-corner-media-sections', [
        'post' => $post,
        'railLayout' => true,
    ])
</div>
