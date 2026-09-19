@extends('emails.layouts.base')

@section('content')
    <h1 style="margin: 0 0 10px; color: #111827; font-size: 24px; line-height: 1.3;">New follower</h1>

    <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
        <strong>{{ $follower?->name ?: 'Someone' }}</strong> started following <strong>{{ $institute->displayName() }}</strong> on {{ config('app.name', 'SoilNWater') }}.
    </p>

    @if ($follower?->email)
        <p style="margin: 0 0 16px; color: #475569; font-size: 14px;">Contact: {{ $follower->email }}</p>
    @endif

    <p style="margin: 0 0 20px;">
        <a href="{{ $engagementPortalUrl }}" style="display: inline-block; padding: 12px 20px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">View in portal</a>
    </p>

    <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.7;">Regards,<br><strong>{{ config('app.name', 'SoilNWater') }} Team</strong></p>
@endsection
