@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }

    $railLayout = $railLayout ?? true;
    $overviewRailLayout = $overviewRailLayout ?? $railLayout;
@endphp

@if($post->isChildrensCornerPost())
    @include('community.partials.childrens-corner-show-sections', [
        'post' => $post,
        'placement' => 'intro',
        'sidebarLayout' => ! $overviewRailLayout,
        'overviewRailLayout' => $overviewRailLayout,
    ])
@elseif($post->isStudentCornerPost())
    @include('community.partials.student-corner-show-sections', [
        'post' => $post,
        'sidebarLayout' => ! $overviewRailLayout,
        'overviewRailLayout' => $overviewRailLayout,
    ])
@elseif($post->isYouthCornerPost())
    @include('community.partials.youth-corner-show-sections', [
        'post' => $post,
        'sidebarLayout' => ! $overviewRailLayout,
        'overviewRailLayout' => $overviewRailLayout,
    ])
@elseif($post->isSeniorCitizensForumPost())
    @include('community.partials.senior-citizens-forum-show-sections', [
        'post' => $post,
        'sidebarLayout' => ! $overviewRailLayout,
        'overviewRailLayout' => $overviewRailLayout,
    ])
@elseif($post->isWomensWorldPost())
    @include('community.partials.womens-world-show-sections', [
        'post' => $post,
        'sidebarLayout' => ! $overviewRailLayout,
        'overviewRailLayout' => $overviewRailLayout,
    ])
@endif
