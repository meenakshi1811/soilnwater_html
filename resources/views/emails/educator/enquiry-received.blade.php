@extends('emails.layouts.base')

@section('content')
<p>Hello {{ $details['educator_name'] }},</p>

<p>You received a new enquiry on your Teacher / Tutor profile.</p>

<p>
    <strong>From:</strong> {{ $details['name'] }}<br>
    @if(!empty($details['email']))<strong>Email:</strong> {{ $details['email'] }}<br>@endif
    @if(!empty($details['phone']))<strong>Phone:</strong> {{ $details['phone'] }}<br>@endif
    @if(!empty($details['subject']))<strong>Subject:</strong> {{ $details['subject'] }}<br>@endif
</p>

<p><strong>Message:</strong></p>
<p style="white-space:pre-line">{{ $details['message'] }}</p>

<p><a href="{{ $details['enquiries_url'] }}">View enquiries in your portal</a></p>

<p>Regards,<br>SoilNWater Team</p>
@endsection
