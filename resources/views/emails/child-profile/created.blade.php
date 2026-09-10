@extends('emails.layouts.base')

@section('content')
<p>Hello {{ $details['child_name'] }},</p>

<p>Your parent/guardian <strong>{{ $details['parent_name'] }}</strong> has created a child profile for you on SoilNWater.</p>

<p>Your profile is currently <strong>{{ $details['status'] }}</strong>. Once the admin team approves it, you will be able to sign in using your registered email and the password set by your parent.</p>

<p>Registered email: <strong>{{ $details['email'] }}</strong></p>

<p>After approval, you can sign in here: <a href="{{ $details['login_url'] }}">{{ $details['login_url'] }}</a></p>

<p>Regards,<br>SoilNWater Team</p>
@endsection
