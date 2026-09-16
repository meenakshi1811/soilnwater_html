@extends('emails.layouts.base')

@section('content')
<p>Hello {{ $details['parent_name'] }},</p>

<p>You have submitted a child profile for <strong>{{ $details['child_name'] }}</strong> on SoilNWater.</p>

<p>The profile is currently <strong>{{ $details['status'] }}</strong>. Once the admin team approves it, you can access your child's dashboard from your parent profile.</p>

<p>Go to your parent dashboard: <a href="{{ $details['dashboard_url'] }}">{{ $details['dashboard_url'] }}</a></p>

<p>Regards,<br>SoilNWater Team</p>
@endsection
