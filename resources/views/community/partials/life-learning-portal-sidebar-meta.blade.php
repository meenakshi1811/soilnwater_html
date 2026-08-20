@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }
@endphp

@if($post->isChildrensCornerPost())
    @include('community.partials.childrens-corner-meta-details', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@elseif($post->isStudentCornerPost())
    @include('community.partials.student-corner-meta-details', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@elseif($post->isYouthCornerPost())
    @include('community.partials.youth-corner-meta-details', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@elseif($post->isSeniorCitizensForumPost())
    @include('community.partials.senior-citizens-forum-meta-details', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@elseif($post->isWomensWorldPost())
    @include('community.partials.womens-world-meta-details', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@elseif($post->content_type === 'health-wellness')
    @include('community.partials.health-wellness-meta-details', [
        'post' => $post,
        'sidebarLayout' => true,
    ])
@endif
