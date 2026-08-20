@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }
@endphp

@if($post->isChildrensCornerPost())
    @include('community.partials.childrens-corner-show-sections', [
        'post' => $post,
        'placement' => 'intro',
        'sidebarLayout' => true,
    ])
@elseif($post->isStudentCornerPost())
    @include('community.partials.student-corner-show-sections', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@elseif($post->isYouthCornerPost())
    @include('community.partials.youth-corner-show-sections', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@elseif($post->isSeniorCitizensForumPost())
    @include('community.partials.senior-citizens-forum-show-sections', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@elseif($post->isWomensWorldPost())
    @include('community.partials.womens-world-show-sections', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@endif
