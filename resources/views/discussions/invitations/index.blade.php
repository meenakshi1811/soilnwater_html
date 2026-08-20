@extends('frontend.layouts.app')

@section('meta_title', 'Group invitations – SoilnWater')

@section('content')
<div class="discussion-page">
    <section class="discussion-hero">
        <div class="container">
            <h1><i class="fa-solid fa-envelope-open-text me-2"></i>Group invitations</h1>
            <p class="mb-0">Approve or reject invitations to join community groups.</p>
        </div>
    </section>

    <div class="container discussion-inner py-5">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif

        @forelse($invitations as $invitation)
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
        @empty
            <div class="alert alert-light border">You have no pending group invitations.</div>
        @endforelse
    </div>
</div>
@endsection
