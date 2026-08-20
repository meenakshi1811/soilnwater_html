@php
    $placement = $placement ?? 'full';
    $portalSidebarLayout = $portalSidebarLayout ?? false;
@endphp

@if(in_array($placement, ['full', 'before'], true))
    @unless($portalSidebarLayout)
        @if($post->isChildrensCornerPost())
            @include('community.partials.childrens-corner-show-sections', [
                'post' => $post,
                'placement' => 'intro',
                'portalSidebarLayout' => $portalSidebarLayout,
            ])
        @endif

        @if($post->isWomensWorldPost())
            @include('community.partials.womens-world-show-sections', [
                'post' => $post,
                'portalSidebarLayout' => $portalSidebarLayout,
            ])
        @endif

        @if($post->isSeniorCitizensForumPost())
            @include('community.partials.senior-citizens-forum-show-sections', [
                'post' => $post,
                'portalSidebarLayout' => $portalSidebarLayout,
            ])
        @endif

        @if($post->isStudentCornerPost())
            @include('community.partials.student-corner-show-sections', [
                'post' => $post,
                'portalSidebarLayout' => $portalSidebarLayout,
            ])
        @endif

        @if($post->isYouthCornerPost())
            @include('community.partials.youth-corner-show-sections', [
                'post' => $post,
                'portalSidebarLayout' => $portalSidebarLayout,
            ])
        @endif
    @endunless
@endif

@if(in_array($placement, ['full', 'after'], true))
    @if($post->isSeniorCitizensForumPost() && ! $portalSidebarLayout)
        @include('community.partials.senior-citizens-forum-after-content', ['post' => $post])
    @endif

    @if($post->isSeniorCitizensForumPost() && $portalSidebarLayout)
        @include('community.partials.senior-citizens-forum-achievements-section', ['post' => $post])
    @endif

    @unless($portalSidebarLayout)
        @if($post->isStudentCornerPost())
            @include('community.partials.student-corner-meta-details', ['post' => $post])
        @endif

        @if($post->isYouthCornerPost())
            @include('community.partials.youth-corner-meta-details', ['post' => $post])
        @endif

        @if($post->isWomensWorldPost())
            @include('community.partials.womens-world-meta-details', ['post' => $post])
        @endif

        @if($post->isSeniorCitizensForumPost())
            @include('community.partials.senior-citizens-forum-meta-details', ['post' => $post])
        @endif

        @if($post->content_type === 'health-wellness')
            @include('community.partials.health-wellness-meta-details', ['post' => $post])
        @endif

        @if($post->isChildrensCornerPost())
            @include('community.partials.childrens-corner-show-sections', ['post' => $post, 'placement' => 'media'])
            @include('community.partials.childrens-corner-meta-details', ['post' => $post])
        @endif
    @endunless
@endif
