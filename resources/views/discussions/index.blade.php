@extends('frontend.layouts.app')

@section('meta_title', 'Discussions – SoilnWater')

@section('content')
<div class="discussion-page">
    <section class="discussion-hero">
        <div class="container">
            <h1><i class="fa-solid fa-message me-2"></i>Community Chat</h1>
            <p class="mb-0">Join conversations with fellow SoilNWater members — or open the chat widget from any page.</p>
        </div>
    </section>

    <div class="container discussion-inner py-5">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="discussion-page-header">
            <div>
                <h2>Conversations</h2>
                <p>Pinned topics appear first · Unread shown with a badge</p>
            </div>
            <button type="button" class="discussion-btn" id="discussionPageOpenWidget">
                <i class="fa-solid fa-comments"></i> Open chat
            </button>
        </div>

        <div class="discussion-topic-list" id="discussionTopicList" data-can-pin="{{ $canPin ? '1' : '0' }}">
            @forelse($topics as $topic)
                @include('discussions.partials.topic-card', ['topic' => $topic, 'canPin' => $canPin, 'unreadCounts' => $unreadCounts])
            @empty
                <div class="discussion-empty" id="discussionEmptyState">
                    <i class="fa-regular fa-comments fa-2x"></i>
                    <p class="mb-0 mt-2">No conversations yet. Be the first to start one.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $topics->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('discussionPageOpenWidget')?.addEventListener('click', () => {
        window.soilnwaterDiscussionWidget?.open?.();
    });

    document.getElementById('discussionTopicList')?.addEventListener('click', (event) => {
        const link = event.target.closest('.discussion-topic-link, .discussion-btn');
        if (!link) {
            return;
        }

        const card = link.closest('[data-topic-id]');
        if (!card || !window.soilnwaterDiscussionWidget?.openTopic) {
            return;
        }

        event.preventDefault();
        window.soilnwaterDiscussionWidget.openTopic(card.dataset.topicId);
    });
</script>
@endpush
