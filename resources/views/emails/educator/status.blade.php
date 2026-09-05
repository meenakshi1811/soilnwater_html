@extends('emails.layouts.base')

@section('content')
<p>Hello {{ $educatorDetails['display_name'] }},</p>

@if($action === 'pending')
    <p>Thank you for registering as a <strong>{{ $educatorDetails['role_label'] }}</strong> on SoilNWater.</p>
    <p>Your profile is currently <strong>{{ $educatorDetails['status'] }}</strong>. Our admin team will review and approve it soon.</p>
@elseif($action === 'approved')
    <p>Great news! Your <strong>{{ $educatorDetails['role_label'] }}</strong> account has been <strong>approved</strong>.</p>
    <p>You can now sign in and complete your professional profile, upload study materials, and start connecting with students.</p>
    @if(!empty($educatorDetails['profile_url']))
        <p><a href="{{ $educatorDetails['profile_url'] }}">View your public profile</a></p>
    @endif
@elseif($action === 'rejected')
    <p>Your <strong>{{ $educatorDetails['role_label'] }}</strong> application was <strong>rejected</strong>.</p>
    @if(!empty($educatorDetails['reason']))
        <p><strong>Reason:</strong> {{ $educatorDetails['reason'] }}</p>
    @endif
    <p>Please contact support if you need further assistance.</p>
@else
    <p>Your {{ $educatorDetails['role_label'] }} account status is now: <strong>{{ $educatorDetails['status'] }}</strong>.</p>
@endif

<p>Regards,<br>SoilNWater Team</p>
@endsection
