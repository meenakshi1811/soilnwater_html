@php
    $placement = $placement ?? 'full';
@endphp

@if(in_array($placement, ['full', 'before'], true))
    @if($post->isChildrensCornerPost())
        @include('community.partials.childrens-corner-show-sections', ['post' => $post, 'placement' => 'intro'])
    @endif

    @if($post->isWomensWorldPost())
        @include('community.partials.womens-world-show-sections', ['post' => $post])
    @endif

    @if($post->isSeniorCitizensForumPost())
        @include('community.partials.senior-citizens-forum-show-sections', ['post' => $post])
    @endif

    @if($post->isStudentCornerPost())
        @include('community.partials.student-corner-show-sections', ['post' => $post])
    @endif

    @if($post->isYouthCornerPost())
        @include('community.partials.youth-corner-show-sections', ['post' => $post])
    @endif
@endif

@if(in_array($placement, ['full', 'after'], true))
    @if($post->isSeniorCitizensForumPost())
        @include('community.partials.senior-citizens-forum-after-content', ['post' => $post])
    @endif

    @if($post->isStudentCornerPost())
        @include('community.partials.student-corner-meta-details', ['post' => $post])
    @endif

    @if($post->isYouthCornerPost())
        @include('community.partials.youth-corner-meta-details', ['post' => $post])
    @endif

    @if($post->isChildrensCornerPost())
        @include('community.partials.childrens-corner-show-sections', ['post' => $post, 'placement' => 'media'])
        @include('community.partials.childrens-corner-meta-details', ['post' => $post])
    @endif
@endif
