@extends('emails.layouts.base')

@section('content')
    <h1 style="margin: 0 0 10px; color: #111827; font-size: 24px; line-height: 1.3;">Welcome to SoilNWater Employee Portal</h1>

    <p style="margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7;">
        Your employee account has been created successfully. Use the credentials below to login and start managing your assigned modules.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 16px;">
        <tr>
            <td style="padding: 16px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;">
                <p style="margin: 0 0 6px; font-size: 13px; color: #166534;"><strong>Name:</strong> {{ $employee->name }}</p>
                <p style="margin: 0 0 6px; font-size: 13px; color: #166534;"><strong>Email:</strong> {{ $employee->email }}</p>
                <p style="margin: 0 0 6px; font-size: 13px; color: #166534;"><strong>Assigned role:</strong> {{ $roleName }}</p>
                <p style="margin: 0; font-size: 13px; color: #166534;"><strong>Temporary Password:</strong> {{ $temporaryPassword }}</p>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 12px; color: #374151; font-size: 14px; line-height: 1.7;">
        Please login and change your password immediately for security. Access will be limited based on your role and permission matrix.
    </p>

    <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.7;">Regards,<br><strong>{{ config('app.name', 'SoilNWater') }} Team</strong></p>
@endsection
