@extends('frontend.layouts.app')

@section('meta_title', $topic->title . ' – Discussions')

@section('content')
@php
    use Illuminate\Support\Str;
    $authorName = $topic->displayAuthorName();
    $authorInitials = collect(explode(' ', $authorName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->join('');
@endphp
<div class="discussion-page" data-discussion-topic-id="{{ $topic->id }}">
    <section class="discussion-hero discussion-hero--compact">
        <div class="container">
            <a href="{{ route('discussions.index') }}" class="discussion-back-link"><i class="fa-solid fa-arrow-left"></i> All conversations</a>
            <h1 class="discussion-topic-title">
                @if($topic->is_pinned)
                    <span class="discussion-pin-badge me-2"><i class="fa-solid fa-thumbtack"></i> Pinned</span>
                @endif
                <span id="discussionTopicTitle">{{ $topic->title }}</span>
            </h1>
            <p class="mb-0 mt-2 d-flex flex-wrap gap-2">
                <a href="{{ route('discussions.messenger', $topic) }}" class="discussion-btn discussion-btn--outline discussion-btn--sm">
                    <i class="fa-solid fa-up-right-from-square"></i> Open full page
                </a>
                <button type="button" class="discussion-btn discussion-btn--outline discussion-btn--sm" id="discussionPageOpenTopicWidget">
                    <i class="fa-solid fa-message"></i> Open popup
                </button>
                <a href="{{ route('discussions.show', $topic) }}" class="discussion-btn discussion-btn--outline discussion-btn--sm">
                    <i class="fa-solid fa-list"></i> Thread view
                </a>
            </p>
        </div>
    </section>

    <div class="container discussion-inner py-5">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <article class="discussion-thread-card" id="discussionTopicPost" data-reactable-type="DiscussionTopic" data-reactable-id="{{ $topic->id }}">
            <div class="discussion-thread-card__header">
                <div class="discussion-thread-card__author">
                    <span class="discussion-avatar" aria-hidden="true">{{ $authorInitials ?: 'M' }}</span>
                    <div>
                        <div class="discussion-thread-card__author-name">{{ $authorName }}</div>
                        <div class="discussion-thread-card__author-time">{{ $topic->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @if($canPin)
                    <button type="button"
                            class="discussion-btn discussion-btn--outline discussion-btn--sm discussion-pin-btn {{ $topic->is_pinned ? 'is-pinned' : '' }}"
                            data-url="{{ route('discussions.pin', $topic) }}"
                            data-pinned="{{ $topic->is_pinned ? '1' : '0' }}">
                        <i class="fa-solid fa-thumbtack"></i>
                        {{ $topic->is_pinned ? 'Unpin' : 'Pin' }}
                    </button>
                @endif
            </div>

            <div class="discussion-msg__bubble-wrap">
                @if($topic->body)
                    <div class="discussion-msg__bubble">
                        <p class="discussion-body discussion-msg__body">{!! nl2br(e($topic->body)) !!}</p>
                    </div>
                @endif

                @include('discussions.partials.attachments', ['attachments' => $topic->attachments ?? []])

                @include('discussions.partials.reactions', [
                    'reactableType' => 'DiscussionTopic',
                    'reactableId' => $topic->id,
                    'counts' => $topic->reactionCounts(),
                    'userReactions' => $userReactions['topic'] ?? [],
                    'reactUrl' => route('discussions.react', $topic),
                ])
            </div>
        </article>

        <section class="discussion-replies-section">
            <h2>Replies <span class="discussion-count-badge" id="discussionReplyCount">{{ $topic->replies_count }}</span></h2>

            <form class="discussion-composer-box" id="discussionReplyForm" data-url="{{ route('discussions.replies.store', $topic) }}" enctype="multipart/form-data">
                @csrf
                <label class="form-label" for="replyBody">Write a reply</label>
                <textarea name="body" id="replyBody" class="form-control" rows="3" maxlength="5000" placeholder="Share your thoughts…"></textarea>
                <div class="discussion-composer-media mt-3">
                    <label class="discussion-media-btn mb-0" for="replyAttachments">
                        <i class="fa-solid fa-image"></i>
                        Add photo or video
                    </label>
                    <input type="file" id="replyAttachments" name="attachments[]" class="visually-hidden" accept="image/*,video/mp4,video/webm" multiple>
                    <div class="discussion-media-preview" id="replyAttachmentsPreview" hidden></div>
                </div>
                <button type="submit" class="discussion-btn mt-3">
                    <i class="fa-solid fa-paper-plane"></i> Send reply
                </button>
            </form>

            <div id="discussionReplyList">
                @forelse($topic->replies as $reply)
                    @include('discussions.partials.reply', [
                        'reply' => $reply,
                        'userReactions' => $userReactions['replies'][$reply->id] ?? [],
                    ])
                @empty
                    <div class="discussion-empty-replies" id="discussionEmptyReplies">
                        <i class="fa-regular fa-comment-dots fa-2x"></i>
                        <p class="mb-0 mt-2">No replies yet. Start the conversation.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.soilnwaterDiscussion = window.soilnwaterDiscussion || {};
        window.soilnwaterDiscussion.topicId = @json($topic->id);

        document.getElementById('discussionPageOpenTopicWidget')?.addEventListener('click', () => {
            window.soilnwaterDiscussionWidget?.openTopic?.(@json($topic->id));
        });
    });
</script>
@endpush
