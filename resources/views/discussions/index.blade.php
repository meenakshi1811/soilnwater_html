@extends('frontend.layouts.app')

@section('meta_title', 'Discussions – SoilnWater')

@section('content')
<div class="discussion-page">
    <section class="discussion-banner">
        <div class="container">
            <h1><i class="fa-solid fa-comments me-2"></i>Discussions</h1>
            <p class="mb-0">Use the chat button to join conversations without leaving the page — or browse topics below.</p>
        </div>
    </section>

    <div class="container discussion-inner py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">Topics</h2>
                <p class="text-muted mb-0 small">Pinned topics appear first.</p>
            </div>
            <button type="button" class="btn btn-success" id="discussionPageOpenWidget">
                <i class="fa-solid fa-comments me-1"></i> Open chat
            </button>
        </div>

        <div class="discussion-topic-list" id="discussionTopicList" data-can-pin="{{ $canPin ? '1' : '0' }}">
            @forelse($topics as $topic)
                @include('discussions.partials.topic-card', ['topic' => $topic, 'canPin' => $canPin, 'unreadCounts' => $unreadCounts])
            @empty
                <div class="discussion-empty border rounded-3 p-4 text-center text-muted" id="discussionEmptyState">
                    <i class="fa-regular fa-comments fa-2x mb-2"></i>
                    <p class="mb-0">No topics yet. Be the first to start a discussion.</p>
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
        const link = event.target.closest('.discussion-topic-link, .btn-outline-success');
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
