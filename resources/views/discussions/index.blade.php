@extends('frontend.layouts.app')

@section('meta_title', 'Discussions – SoilnWater')

@section('content')
<div class="discussion-page">
    <section class="discussion-hero">
        <div class="container">
            <h1><i class="fa-solid fa-comments me-2"></i>Chats</h1>
            <p class="mb-0">Open the chat popup on any page, or use the <a href="{{ route('discussions.messenger') }}" class="discussion-hero__link">full-page messenger</a>.</p>
        </div>
    </section>

    <div class="container discussion-inner py-5">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="discussion-page-header">
            <div>
                <h2>Chats</h2>
                <p>Search, open, or start a new group conversation</p>
            </div>
            <div class="discussion-page-header__actions">
                <button type="button"
                        class="discussion-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#newTopicModal">
                    <i class="fa-solid fa-comment-medical"></i> New chat
                </button>
                <a href="{{ route('discussions.messenger', ['compose' => 1]) }}" class="discussion-btn discussion-btn--outline">
                    <i class="fa-solid fa-up-right-from-square"></i> Full page
                </a>
                <button type="button" class="discussion-btn discussion-btn--outline" id="discussionPageOpenWidget">
                    <i class="fa-solid fa-comments"></i> Open popup
                </button>
            </div>
        </div>

        @if(($pendingInvitations ?? collect())->isNotEmpty())
            <div class="discussion-invite-list mb-4">
                <h3 class="h5 mb-3">Pending group invitations</h3>
                @foreach($pendingInvitations as $invitation)
                    <div class="discussion-invite-card">
                        <div>
                            <h2>{{ $invitation->topic?->title ?: 'Community group' }}</h2>
                            <p>Invited by {{ $invitation->inviter?->authorDisplayName() ?: 'a community member' }} · {{ $invitation->created_at?->diffForHumans() }}</p>
                        </div>
                        <div class="discussion-invite-card__actions">
                            <form method="POST" action="{{ route('discussions.invitations.reject', $invitation) }}">
                                @csrf
                                <button type="submit" class="discussion-btn discussion-btn--outline">Reject</button>
                            </form>
                            <form method="POST" action="{{ route('discussions.invitations.accept', $invitation) }}">
                                @csrf
                                <button type="submit" class="discussion-btn">Approve</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

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

@include('discussions.partials.new-topic-modal')
@endsection

@push('scripts')
<script>
    document.getElementById('discussionPageOpenWidget')?.addEventListener('click', () => {
        window.soilnwaterDiscussionWidget?.open?.();
    });

    document.getElementById('discussionTopicList')?.addEventListener('click', (event) => {
        const popupLink = event.target.closest('[data-open-popup="1"]');
        const card = event.target.closest('[data-topic-id]');
        if (!card) {
            return;
        }

        if (popupLink && window.soilnwaterDiscussionWidget?.openTopic) {
            event.preventDefault();
            window.soilnwaterDiscussionWidget.openTopic(card.dataset.topicId);
        }
    });
</script>
@endpush
