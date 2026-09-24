@extends('emails.layouts.base')

@section('content')
    <h1 style="margin: 0 0 10px; color: #111827; font-size: 24px; line-height: 1.3;">New application for {{ $application->job?->title }}</h1>

    <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
        Someone applied to a job posting on your {{ $institutionName }} public profile.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 16px;">
        <tr>
            <td style="padding: 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                <p style="margin: 0 0 6px; font-size: 13px; color: #334155;"><strong>Applicant:</strong> {{ $application->user?->name }}</p>
                @if ($application->user?->email)
                    <p style="margin: 0 0 6px; font-size: 13px; color: #334155;"><strong>Email:</strong> {{ $application->user->email }}</p>
                @endif
                @if ($application->cover_message)
                    <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.7; white-space: pre-wrap;">{{ $application->cover_message }}</p>
                @endif
            </td>
        </tr>
    </table>

    @if ($jobsPortalUrl ?? null)
        <p style="margin: 0 0 20px;">
            <a href="{{ $jobsPortalUrl }}" style="display: inline-block; padding: 12px 20px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">Review applications</a>
        </p>
    @endif

    <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.7;">Regards,<br><strong>{{ config('app.name', 'SoilNWater') }} Team</strong></p>
@endsection
