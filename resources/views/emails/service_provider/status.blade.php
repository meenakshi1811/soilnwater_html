@extends('emails.layouts.base')

@section('content')
    @php
        $isPending = $action === 'pending';
        $isApproved = $action === 'approved';
        $isRejected = $action === 'rejected';
        $accentColor = $isPending ? '#1d4ed8' : ($isApproved ? '#166534' : ($isRejected ? '#92400e' : '#991b1b'));
        $accentBg = $isPending ? '#eff6ff' : ($isApproved ? '#f0fdf4' : ($isRejected ? '#fffbeb' : '#fef2f2'));
        $accentBorder = $isPending ? '#bfdbfe' : ($isApproved ? '#bbf7d0' : ($isRejected ? '#fde68a' : '#fecaca'));
    @endphp

    <h1 style="margin: 0 0 10px; color: #111827; font-size: 24px; line-height: 1.3;">{{ $subjectLine }}</h1>

    @if ($isPending)
        <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
            Welcome to {{ config('app.name', 'SoilNWater') }}. Your service profile is under observation. The admin team will check your details and approve it soon.
        </p>
    @elseif ($isApproved)
        <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
            Good news! Your service account has been approved by the {{ config('app.name', 'SoilNWater') }} team. You can now access the service portal and manage your service profile, branches, and enquiries.
        </p>
    @elseif ($isRejected)
        <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
            Thank you for registering as a service with {{ config('app.name', 'SoilNWater') }}. After reviewing your application, our team is unable to approve it at this time.
        </p>
    @else
        <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
            This email confirms that your service account has been removed from {{ config('app.name', 'SoilNWater') }} by the admin team.
        </p>
    @endif

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 16px;">
        <tr>
            <td style="padding: 16px; background-color: {{ $accentBg }}; border: 1px solid {{ $accentBorder }}; border-radius: 10px;">
                <p style="margin: 0 0 6px; font-size: 13px; color: {{ $accentColor }};"><strong>Company:</strong> {{ $service_providerDetails['company_name'] }}</p>
                @if (! empty($service_providerDetails['contact_person']))
                    <p style="margin: 0 0 6px; font-size: 13px; color: {{ $accentColor }};"><strong>Contact person:</strong> {{ $service_providerDetails['contact_person'] }}</p>
                @endif
                @if (! empty($service_providerDetails['email']))
                    <p style="margin: 0 0 6px; font-size: 13px; color: {{ $accentColor }};"><strong>Email:</strong> {{ $service_providerDetails['email'] }}</p>
                @endif
                <p style="margin: 0; font-size: 13px; color: {{ $accentColor }};"><strong>Status:</strong> {{ $service_providerDetails['status'] }}</p>
            </td>
        </tr>
    </table>

    @if ($isPending)
        <p style="margin: 0 0 12px; color: #374151; font-size: 14px; line-height: 1.7;">
            Please wait for admin approval before trying to access the service portal.
        </p>
    @elseif ($isApproved)
        <p style="margin: 0 0 18px; color: #374151; font-size: 14px; line-height: 1.7;">
            Please login with your registered credentials to continue setting up your service profile.
        </p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 0 18px;">
            <tr>
                <td style="border-radius: 8px; background-color: #12824e;">
                    <a href="{{ url('/login') }}" style="display: inline-block; padding: 11px 18px; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700;">Login to Service Portal</a>
                </td>
            </tr>
        </table>
        @if (! empty($service_providerDetails['service_provider_url']))
            <p style="margin: 0 0 12px; color: #4b5563; font-size: 13px; line-height: 1.7;">
                Your public service link: <a href="{{ $service_providerDetails['service_provider_url'] }}" style="color: #12824e;">{{ $service_providerDetails['service_provider_url'] }}</a>
            </p>
        @endif
    @elseif ($isRejected)
        <p style="margin: 0 0 12px; color: #374151; font-size: 14px; line-height: 1.7;">
            If you believe this decision needs another review, please contact our support team with updated business details.
        </p>
    @else
        <p style="margin: 0 0 12px; color: #374151; font-size: 14px; line-height: 1.7;">
            If this removal was unexpected, please contact our support team for assistance.
        </p>
    @endif

    <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.7;">Regards,<br><strong>{{ config('app.name', 'SoilNWater') }} Team</strong></p>
@endsection
