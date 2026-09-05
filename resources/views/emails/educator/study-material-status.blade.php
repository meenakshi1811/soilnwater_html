@extends('emails.layouts.base')

@section('content')
<p>Hello {{ $details['educator_name'] }},</p>

@if($action === 'approved')
    <p>Your study material <strong>{{ $details['title'] }}</strong> has been <strong>approved</strong> and is now visible in the Study Material Library.</p>
    @if(!empty($details['materials_url']))
        <p><a href="{{ $details['materials_url'] }}">Manage your materials</a></p>
    @endif
@elseif($action === 'rejected')
    <p>Your study material <strong>{{ $details['title'] }}</strong> was <strong>rejected</strong>.</p>
    @if(!empty($details['reason']))
        <p><strong>Reason:</strong> {{ $details['reason'] }}</p>
    @endif
    <p>You can update and resubmit it from your educator portal.</p>
@else
    <p>Your study material <strong>{{ $details['title'] }}</strong> status is now: <strong>{{ $details['status'] }}</strong>.</p>
@endif

<p>Regards,<br>SoilNWater Team</p>
@endsection
