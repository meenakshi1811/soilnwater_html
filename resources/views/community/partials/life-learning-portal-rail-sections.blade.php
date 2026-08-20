@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }

    $hasRailSections = $post->isChildrensCornerPost()
        || $post->isStudentCornerPost()
        || $post->isYouthCornerPost()
        || $post->isSeniorCitizensForumPost()
        || $post->isWomensWorldPost();
@endphp

@if($hasRailSections)
<div class="community-news-rail__life-learning-extras" aria-label="Life and learning post extras">
    @if($post->isChildrensCornerPost())
        @include('community.partials.childrens-corner-media-sections', [
            'post' => $post,
            'railLayout' => true,
        ])
    @elseif($post->isStudentCornerPost())
        @include('community.partials.student-corner-show-sections', [
            'post' => $post,
            'railLayout' => true,
        ])
    @elseif($post->isYouthCornerPost())
        @include('community.partials.youth-corner-show-sections', [
            'post' => $post,
            'railLayout' => true,
        ])
    @elseif($post->isSeniorCitizensForumPost())
        @include('community.partials.senior-citizens-forum-show-sections', [
            'post' => $post,
            'railLayout' => true,
        ])
        @include('community.partials.senior-citizens-forum-after-content', [
            'post' => $post,
            'railLayout' => true,
        ])
    @elseif($post->isWomensWorldPost())
        @include('community.partials.womens-world-show-sections', [
            'post' => $post,
            'railLayout' => true,
        ])
    @endif
</div>
@endif
