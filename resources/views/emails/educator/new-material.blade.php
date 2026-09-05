@extends('emails.layouts.base')

@section('content')
<p>Hello {{ $details['follower_name'] }},</p>

<p><strong>{{ $details['educator_name'] }}</strong> (who you follow) just published new study material:</p>

<p>
    <strong>{{ $details['title'] }}</strong><br>
    Type: {{ $details['material_type'] }}
</p>

<p>
    <a href="{{ $details['material_url'] }}">View the material</a>
    &nbsp;·&nbsp;
    <a href="{{ $details['profile_url'] }}">View profile</a>
</p>

<p>Regards,<br>SoilNWater Team</p>
@endsection
