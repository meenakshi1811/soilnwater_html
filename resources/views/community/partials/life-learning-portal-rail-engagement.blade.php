@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }

    $askCommunityKey = match ($post->content_type) {
        'student-corner' => 'student_corner_ask_community',
        'youth-corner' => 'youth_corner_ask_community',
        'womens-world' => 'womens_world_ask_community',
        default => null,
    };
    $askCommunity = $askCommunityKey ? data_get($post->meta, $askCommunityKey) : null;
    $pollQuestionKey = match ($post->content_type) {
        'student-corner' => 'student_corner_poll_question',
        'youth-corner' => 'youth_corner_poll_question',
        'womens-world' => 'womens_world_poll_question',
        default => null,
    };
    $hasReaderEngagement = $post->allowsPoll()
        || $post->allow_comments
        || $post->allow_questions
        || $post->allow_suggestions
        || $post->allow_feedback
        || $post->allow_sharing;
@endphp

@if(filled($askCommunity))
    <div class="community-news-rail__card community-news-rail__card--detail">
        <h3 class="community-news-rail__title">Ask the community</h3>
        <blockquote class="community-news-rail__quote mb-0">"{{ $askCommunity }}"</blockquote>
    </div>
@endif

@if($hasReaderEngagement)
    <div class="community-news-rail__card community-news-rail__card--detail">
        <h3 class="community-news-rail__title">Reader engagement</h3>
        <div class="community-news-rail__badges">
            @if($post->allowsPoll())
                <span class="badge bg-primary text-white">Poll open</span>
            @endif
            @if($post->allow_comments)
                <span class="badge bg-success">Comments</span>
            @endif
            @if($post->allow_questions)
                <span class="badge bg-success">Questions</span>
            @endif
            @if($post->allow_suggestions)
                <span class="badge bg-success">Suggestions</span>
            @endif
            @if($post->allow_feedback)
                <span class="badge bg-success">Peer discussion</span>
            @endif
            @if($post->allow_sharing)
                <span class="badge bg-success">Sharing</span>
            @endif
        </div>
        @if($post->allowsPoll() && $pollQuestionKey && filled(data_get($post->meta, $pollQuestionKey)))
            <p class="small text-muted mb-0 mt-2"><strong>Poll:</strong> {{ data_get($post->meta, $pollQuestionKey) }}</p>
        @endif
    </div>
@endif
