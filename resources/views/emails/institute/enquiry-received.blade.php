@extends('emails.layouts.base')

@section('content')
    <h1 style="margin: 0 0 10px; color: #111827; font-size: 24px; line-height: 1.3;">New enquiry for {{ $institutionName }}</h1>

    <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
        You have received a new enquiry on your public school / institute profile.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 16px;">
        <tr>
            <td style="padding: 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                <p style="margin: 0 0 6px; font-size: 13px; color: #334155;"><strong>From:</strong> {{ $enquiry->name }}</p>
                @if ($enquiry->email)
                    <p style="margin: 0 0 6px; font-size: 13px; color: #334155;"><strong>Email:</strong> {{ $enquiry->email }}</p>
                @endif
                @if ($enquiry->phone)
                    <p style="margin: 0 0 6px; font-size: 13px; color: #334155;"><strong>Phone:</strong> {{ $enquiry->phone }}</p>
                @endif
                @if ($enquiry->subject)
                    <p style="margin: 0 0 6px; font-size: 13px; color: #334155;"><strong>Subject:</strong> {{ $enquiry->subject }}</p>
                @endif
                <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.7; white-space: pre-wrap;">{{ $enquiry->message }}</p>
            </td>
        </tr>
    </table>

    <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.7;">Regards,<br><strong>{{ config('app.name', 'SoilNWater') }} Team</strong></p>
@endsection
