@extends('emails.layouts.base')

@php($subjectLine = 'Contact Support Request')

@section('content')
    <h2 style="margin:0 0 10px;font-size:28px;color:#0f172a;">New Contact Support Request</h2>
    <p style="margin:0 0 18px;color:#475569;font-size:14px;">A user submitted a support request from the dashboard.</p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate;border-spacing:0;border:1px solid #dbe5ef;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;width:42%;font-weight:700;color:#0f172a;">User Name</td>
            <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $user?->name ?? 'Guest' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Email</td>
            <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $user?->email ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Phone Number</td>
            <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $user?->phone_number ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Subject</td>
            <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $entry->subject }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;font-weight:700;color:#0f172a;vertical-align:top;">Message</td>
            <td style="padding:12px 14px;color:#1e293b;white-space:pre-wrap;">{{ $entry->message }}</td>
        </tr>
    </table>
@endsection
