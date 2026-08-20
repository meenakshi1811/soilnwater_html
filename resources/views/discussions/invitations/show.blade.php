@extends('frontend.layouts.app')

@section('meta_title', 'Group invitation – SoilnWater')

@section('content')
<div class="discussion-page">
    <section class="discussion-hero">
        <div class="container">
            <h1><i class="fa-solid fa-user-group me-2"></i>Group invitation</h1>
            <p class="mb-0">Review this invitation before joining the group.</p>
        </div>
    </section>

    <div class="container discussion-inner py-5">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif

        <div class="discussion-invite-card discussion-invite-card--detail">
            <div>
                <h2>{{ $invitation->topic?->title ?: 'Community group' }}</h2>
                <p>
                    <strong>{{ $invitation->inviter?->authorDisplayName() ?: 'A community member' }}</strong>
                    invited you to join this group.
                </p>
                @if($invitation->topic?->body)
                    <p class="mb-0">{{ $invitation->topic->body }}</p>
                @endif
            </div>

            @if($invitation->isPending())
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
            @else
                <p class="mb-0">
                    This invitation was {{ $invitation->status }}
                    @if($invitation->responded_at)
                        {{ $invitation->responded_at->diffForHumans() }}
                    @endif.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
