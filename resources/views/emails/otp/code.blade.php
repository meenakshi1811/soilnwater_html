@extends('emails.layouts.base')

@section('content')
    <h1 style="margin: 0 0 10px; color: #111827; font-size: 24px; line-height: 1.3;">{{ $headline }}</h1>

    <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
        {{ $contextLine }}
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 16px;">
        <tr>
            <td align="center" style="padding: 16px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;">
                <p style="margin: 0 0 6px; font-size: 12px; color: #166534; letter-spacing: 0.7px; text-transform: uppercase;">One-Time Password</p>
                <p style="margin: 0; font-size: 34px; letter-spacing: 10px; color: #064e3b; font-weight: 700;">{{ $otpCode }}</p>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 12px; color: #374151; font-size: 14px; line-height: 1.7;">
        This OTP will expire in <strong>{{ $expiresInMinutes }} minutes</strong>. For your security, do not share it with anyone.
    </p>

    <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.7;">Regards,<br><strong>{{ config('app.name', 'SoilNWater') }} Team</strong></p>
@endsection
