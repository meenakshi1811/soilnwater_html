@extends('emails.layouts.base')

@section('content')
<p>Hello {{ $details['recipient_name'] }},</p>

@if($details['audience'] === 'parent')
    <p>The child profile for <strong>{{ $details['child_name'] }}</strong> has been <strong>{{ strtolower($details['status']) }}</strong>.</p>
@else
    <p>Your child profile on SoilNWater has been <strong>{{ strtolower($details['status']) }}</strong>.</p>
    @if(!empty($details['parent_name']))
        <p>This profile was created by <strong>{{ $details['parent_name'] }}</strong>.</p>
    @endif
@endif

@if(!empty($details['reason']))
    <p><strong>Reason:</strong> {{ $details['reason'] }}</p>
@endif

@if($details['status'] === 'Approved' && !empty($details['login_url']))
    <p>You can now sign in using your registered email and password: <a href="{{ $details['login_url'] }}">{{ $details['login_url'] }}</a></p>
@endif

@if(!empty($details['dashboard_url']))
    <p><a href="{{ $details['dashboard_url'] }}">Open parent dashboard</a></p>
@endif

<p>Regards,<br>SoilNWater Team</p>
@endsection
