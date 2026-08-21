@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }
@endphp

@if($post->isCreativeCornerPost())
    @include('community.partials.creative-corner-portal-rail-sections', ['post' => $post])
@elseif($post->isCompetitionsPost())
    @include('community.partials.competitions-portal-rail-sections', ['post' => $post])
@elseif($post->content_type === 'discussions')
    <div class="community-news-rail__discussions-extras" aria-label="Discussion post details">
        @include('community.partials.portal-rail-location-card', [
            'post' => $post,
            'title' => 'Location',
            'mapTitle' => 'Discussion location map',
        ])

        @include('community.partials.discussions-additional-details', [
            'post' => $post,
            'railLayout' => true,
        ])
    </div>
@endif
