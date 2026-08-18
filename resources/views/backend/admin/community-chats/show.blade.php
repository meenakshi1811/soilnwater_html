@extends('backend.layouts.app')

@section('title', $topic->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/discussion.css') }}?v={{ now()->timestamp }}">
<style>
    .admin-chat-thread {
        display: flex;
        flex-direction: column;
        gap: 14px;
        max-height: 70vh;
        overflow-y: auto;
        padding-right: 6px;
    }
    .admin-chat-bubble {
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 16px;
        background: #fff;
        padding: 14px 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
    }
    .admin-chat-bubble__meta {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
        font-size: .82rem;
    }
    .admin-chat-participant {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(148, 163, 184, .16);
    }
    .admin-chat-participant:last-child {
        border-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community Chat</p>
            <h2 class="admin-title mb-1">{{ $topic->title }}</h2>
            <p class="mb-0 text-secondary">
                Started by {{ $topic->user?->authorDisplayName() ?? 'Unknown' }}
                on {{ $topic->created_at?->format('d M Y H:i') }}
                @if($topic->isGroupContainer())
                    · Group · {{ $topic->members_count }} members
                @elseif($topic->parent)
                    · Group topic in {{ $topic->parent->title }}
                @else
                    · Public chat
                @endif
            </p>
        </div>
        <a href="{{ route('admin.community-chats.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> All Chats
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <h5 class="mb-3">Messages</h5>

                @if($topic->isGroupContainer())
                    @forelse($topic->children as $child)
                        <a href="{{ route('admin.community-chats.show', $child) }}" class="d-block border rounded-3 p-3 mb-2 text-decoration-none text-dark">
                            <div class="fw-semibold">{{ $child->title }}</div>
                            <small class="text-muted">{{ $child->user?->authorDisplayName() }} · {{ $child->created_at?->diffForHumans() }}</small>
                        </a>
                    @empty
                        <p class="text-muted mb-0">This group has no topics yet.</p>
                    @endforelse
                @else
                    <div class="admin-chat-thread">
                        <div class="admin-chat-bubble">
                            <div class="admin-chat-bubble__meta">
                                <strong>{{ $topic->user?->authorDisplayName() ?? 'Unknown' }}</strong>
                                <span class="text-muted">{{ $topic->created_at?->format('d M Y h:i A') }}</span>
                            </div>
                            <div>{!! nl2br(e($topic->body ?: $topic->title)) !!}</div>
                            @include('discussions.partials.attachments', ['attachments' => $topic->attachments ?? []])
                        </div>

                        @forelse($topic->replies as $reply)
                            <div class="admin-chat-bubble">
                                <div class="admin-chat-bubble__meta">
                                    <strong>{{ $reply->user?->authorDisplayName() ?? 'Unknown' }}</strong>
                                    <span class="text-muted">{{ $reply->created_at?->format('d M Y h:i A') }}</span>
                                </div>
                                @if($reply->body)
                                    <div>{!! nl2br(e($reply->body)) !!}</div>
                                @endif
                                @include('discussions.partials.attachments', ['attachments' => $reply->attachments ?? []])
                            </div>
                        @empty
                            <p class="text-muted mb-0">No replies yet.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="chart-card">
                <h5 class="mb-3">Participants</h5>
                @forelse($participants as $participant)
                    <div class="admin-chat-participant">
                        <div>
                            <div class="fw-semibold">{{ $participant->authorDisplayName() }}</div>
                            <small class="text-muted">{{ $participant->email }}</small>
                            @if($participant->is_chat_blocked)
                                <div><span class="badge text-bg-danger mt-1">Chat blocked</span></div>
                            @endif
                        </div>
                        @if($participant->isAdmin())
                            <span class="text-muted small">Admin</span>
                        @else
                            <form method="POST" action="{{ route('admin.community-chats.users.toggle-block', $participant) }}" class="js-chat-block-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $participant->is_chat_blocked ? 'btn-outline-success' : 'btn-outline-danger' }}">
                                    {{ $participant->is_chat_blocked ? 'Unblock' : 'Block' }}
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">No participants found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    (function ($) {
        $(document).on('submit', '.js-chat-block-form', function (event) {
            event.preventDefault();
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            $button.prop('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function () {
                window.location.reload();
            }).fail(function (xhr) {
                $button.prop('disabled', false);
                alert((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to update chat block.');
            });
        });
    })(jQuery);
</script>
@endpush
