@extends('frontend.layouts.app')

@section('meta_title', $topic->title . ' – Discussions')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/discussion.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="discussion-page" data-discussion-topic-id="{{ $topic->id }}">
    <section class="discussion-banner discussion-banner--compact">
        <div class="container">
            <a href="{{ route('discussions.index') }}" class="discussion-back-link"><i class="fa-solid fa-arrow-left me-1"></i> All topics</a>
            <h1 class="discussion-topic-title">
                @if($topic->is_pinned)
                    <span class="badge bg-warning text-dark me-2 discussion-pin-badge"><i class="fa-solid fa-thumbtack me-1"></i>Pinned</span>
                @endif
                <span id="discussionTopicTitle">{{ $topic->title }}</span>
            </h1>
        </div>
    </section>

    <div class="container discussion-inner py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <article class="discussion-post border rounded-3 p-4 mb-4" id="discussionTopicPost" data-reactable-type="DiscussionTopic" data-reactable-id="{{ $topic->id }}">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                    <strong>{{ $topic->displayAuthorName() }}</strong>
                    <small class="text-muted d-block">{{ $topic->created_at->diffForHumans() }}</small>
                </div>
                @if($canPin)
                    <button type="button"
                            class="btn btn-sm {{ $topic->is_pinned ? 'btn-warning' : 'btn-outline-warning' }} discussion-pin-btn"
                            data-url="{{ route('discussions.pin', $topic) }}"
                            data-pinned="{{ $topic->is_pinned ? '1' : '0' }}">
                        <i class="fa-solid fa-thumbtack me-1"></i>
                        {{ $topic->is_pinned ? 'Unpin' : 'Pin' }}
                    </button>
                @endif
            </div>

            @if($topic->body)
                <p class="mb-3 discussion-body">{!! nl2br(e($topic->body)) !!}</p>
            @endif

            @include('discussions.partials.reactions', [
                'reactableType' => 'DiscussionTopic',
                'reactableId' => $topic->id,
                'counts' => $topic->reactionCounts(),
                'userReactions' => $userReactions['topic'] ?? [],
                'reactUrl' => route('discussions.react', $topic),
            ])
        </article>

        <section class="discussion-replies-section">
            <h2 class="h5 mb-3">Replies <span class="badge bg-secondary" id="discussionReplyCount">{{ $topic->replies_count }}</span></h2>

            <form class="discussion-reply-form mb-4" id="discussionReplyForm" data-url="{{ route('discussions.replies.store', $topic) }}">
                @csrf
                <label class="form-label" for="replyBody">Add a reply</label>
                <textarea name="body" id="replyBody" class="form-control" rows="3" maxlength="5000" required placeholder="Share your thoughts..."></textarea>
                <button type="submit" class="btn btn-success mt-2">
                    <i class="fa-solid fa-paper-plane me-1"></i> Post reply
                </button>
            </form>

            <div id="discussionReplyList">
                @forelse($topic->replies as $reply)
                    @include('discussions.partials.reply', [
                        'reply' => $reply,
                        'userReactions' => $userReactions['replies'][$reply->id] ?? [],
                    ])
                @empty
                    <p class="text-muted discussion-empty-replies" id="discussionEmptyReplies">No replies yet. Start the conversation.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>

@include('community.partials.toastr-assets')
@include('discussions.partials.realtime-config')
@endsection

@push('scripts')
<script src="{{ asset('assets/js/discussion.js') }}?v={{ now()->timestamp }}" defer></script>
@endpush
