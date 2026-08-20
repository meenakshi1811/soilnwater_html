@extends('emails.layouts.base')

@section('content')
    @php
        $inviteeName = $invitation->invitee?->authorDisplayName() ?: 'there';
        $inviterName = $invitation->inviter?->authorDisplayName() ?: 'A community member';
        $groupTitle = $invitation->topic?->title ?: 'a community group';
    @endphp

    <h2 style="margin:0 0 16px;color:#1f2937;">Group invitation</h2>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        Hello {{ $inviteeName }},
    </p>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        <strong>{{ $inviterName }}</strong> invited you to join the community group
        <strong>"{{ $groupTitle }}"</strong>.
    </p>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        Approve the invitation to join the group, or reject it if you do not want to join.
    </p>

    <a href="{{ route('discussions.invitations.show', $invitation) }}"
       style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">
        Review invitation
    </a>
@endsection
