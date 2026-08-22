@extends('frontend.layouts.app')

@section('meta_title', 'Group invitation – SoilnWater')

@section('content')
<div class="discussion-page">
    <section class="discussion-hero">
        <div class="container">
            <h1><i class="fa-solid fa-user-group me-2"></i>Group invitation</h1>
            <p class="mb-0">Register or sign in to respond to this group invitation.</p>
        </div>
    </section>

    <div class="container discussion-inner py-5">
        <div class="discussion-invite-card discussion-invite-card--detail">
            <div>
                <h2>{{ $invitation->topic?->title ?: 'Community group' }}</h2>
                <p>
                    <strong>{{ $invitation->inviter?->authorDisplayName() ?: 'A community member' }}</strong>
                    invited you to join this group on SoilnWater.
                </p>
                @if($invitation->topic?->body)
                    <p class="mb-0">{{ $invitation->topic->body }}</p>
                @endif
            </div>

            <div class="discussion-invite-card__actions">
                <a href="{{ route('register') }}" class="discussion-btn">Register on SoilnWater</a>
                <a href="{{ route('login') }}" class="discussion-btn discussion-btn--outline">Sign in</a>
            </div>

            <p class="mb-0 mt-3 text-muted small">
                After you register with the invited mobile number, open this link again to approve or decline the invitation.
            </p>
        </div>
    </div>
</div>
@endsection
