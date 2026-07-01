@extends('emails.layouts.base')

@php($subjectLine = 'Premium payment proof submitted')

@section('content')
    <h2 style="margin:0 0 10px;font-size:28px;color:#0f172a;">Premium Payment Proof Submitted</h2>
    <p style="margin:0 0 18px;color:#475569;font-size:14px;">
        A user has submitted payment proof for premium membership. Please review the attached screenshot and verify the payment in the admin portal.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate;border-spacing:0;border:1px solid #dbe5ef;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;width:42%;font-weight:700;color:#0f172a;">User Name</td>
            <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $submission->user?->full_name ?: ($submission->user?->name ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Email</td>
            <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $submission->user?->email ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Profile Type</td>
            <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $submission->profileTypeLabel() }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Profile Name</td>
            <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $submission->profileDisplayName() }}</td>
        </tr>
        @if($submission->transaction_reference)
            <tr>
                <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Transaction Reference</td>
                <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $submission->transaction_reference }}</td>
            </tr>
        @endif
        @if($submission->user_note)
            <tr>
                <td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;vertical-align:top;">User Note</td>
                <td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;white-space:pre-wrap;">{{ $submission->user_note }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:12px 14px;background:#f8fbff;font-weight:700;color:#0f172a;">Submitted At</td>
            <td style="padding:12px 14px;color:#1e293b;">{{ $submission->submitted_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? 'N/A' }}</td>
        </tr>
    </table>

    <p style="margin:20px 0 10px;color:#475569;font-size:14px;line-height:1.7;">
        The payment screenshot is attached to this email for your review.
    </p>

    <a href="{{ route('admin.premium-payments.show', $submission) }}" style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">Review in Admin Portal</a>
@endsection
