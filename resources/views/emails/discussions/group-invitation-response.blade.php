@extends('emails.layouts.base')

@section('content')
    @php
        $inviterName = $invitation->inviter?->authorDisplayName() ?: 'there';
        $inviteeName = $invitation->invitee?->authorDisplayName() ?: 'A community member';
        $groupTitle = $invitation->topic?->title ?: 'your group';
        $accepted = $invitation->status === \App\Models\DiscussionGroupInvitation::STATUS_ACCEPTED;
    @endphp

    <h2 style="margin:0 0 16px;color:#1f2937;">
        Invitation {{ $accepted ? 'accepted' : 'declined' }}
    </h2>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        Hello {{ $inviterName }},
    </p>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        <strong>{{ $inviteeName }}</strong>
        {{ $accepted ? 'accepted' : 'declined' }}
        your invitation to join
        <strong>"{{ $groupTitle }}"</strong>.
    </p>

    @if($accepted)
        <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
            They are now a member of the group.
        </p>
        <a href="{{ route('discussions.messenger', $invitation->topic) }}"
           style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">
            Open group
        </a>
    @else
        <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
            They were not added to the group.
        </p>
    @endif
@endsection
