@extends('emails.layouts.base')

@section('content')
    @php
        $isPending = $action === 'pending';
        $isApproved = $action === 'approved';
        $isRejected = $action === 'rejected';
        $accentColor = $isPending ? '#1d4ed8' : ($isApproved ? '#166534' : '#92400e');
        $accentBg = $isPending ? '#eff6ff' : ($isApproved ? '#f0fdf4' : '#fffbeb');
        $accentBorder = $isPending ? '#bfdbfe' : ($isApproved ? '#bbf7d0' : '#fde68a');
    @endphp

    <h1 style="margin: 0 0 10px; color: #111827; font-size: 24px; line-height: 1.3;">{{ $subjectLine }}</h1>

    @if ($isPending)
        <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
            Thank you for requesting a parent/guardian profile on {{ config('app.name', 'SoilNWater') }}. Our admin team is reviewing your application and will notify you once it is approved.
        </p>
    @elseif ($isApproved)
        <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
            Good news! Your parent profile has been approved. You can now access the parent dashboard and submit child profiles for admin approval.
        </p>
    @else
        <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
            Thank you for applying for a parent profile on {{ config('app.name', 'SoilNWater') }}. After reviewing your application, our team is unable to approve it at this time.
        </p>
    @endif

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 16px;">
        <tr>
            <td style="padding: 16px; background-color: {{ $accentBg }}; border: 1px solid {{ $accentBorder }}; border-radius: 10px;">
                <p style="margin: 0 0 6px; font-size: 13px; color: {{ $accentColor }};"><strong>Name:</strong> {{ $profileDetails['display_name'] }}</p>
                @if (! empty($profileDetails['email']))
                    <p style="margin: 0 0 6px; font-size: 13px; color: {{ $accentColor }};"><strong>Email:</strong> {{ $profileDetails['email'] }}</p>
                @endif
                <p style="margin: 0; font-size: 13px; color: {{ $accentColor }};"><strong>Status:</strong> {{ $profileDetails['status'] }}</p>
            </td>
        </tr>
    </table>

    @if ($isRejected && ! empty($profileDetails['reason']))
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 16px;">
            <tr>
                <td style="padding: 16px; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;">
                    <p style="margin: 0 0 6px; font-size: 13px; color: #92400e;"><strong>Rejection reason</strong></p>
                    <p style="margin: 0; font-size: 14px; color: #78350f; line-height: 1.7; white-space: pre-wrap;">{{ $profileDetails['reason'] }}</p>
                </td>
            </tr>
        </table>
    @endif

    @if ($isPending)
        <p style="margin: 0 0 12px; color: #374151; font-size: 14px; line-height: 1.7;">
            Please wait for admin approval before accessing the parent dashboard.
        </p>
    @elseif ($isApproved)
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 0 18px;">
            <tr>
                <td style="border-radius: 8px; background-color: #12824e;">
                    <a href="{{ url('/login') }}" style="display: inline-block; padding: 11px 18px; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700;">Login to Parent Dashboard</a>
                </td>
            </tr>
        </table>
    @else
        <p style="margin: 0 0 12px; color: #374151; font-size: 14px; line-height: 1.7;">
            If you believe this decision needs another review, please contact our support team.
        </p>
    @endif

    <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.7;">Regards,<br><strong>{{ config('app.name', 'SoilNWater') }} Team</strong></p>
@endsection
