<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? config('app.name') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f6fb; font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f3f6fb; padding: 24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="width: 100%; max-width: 640px; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 6px 24px rgba(15, 23, 42, 0.08);">
                <tr>
                    <td style="background: linear-gradient(120deg, #f7fff9 0%, #e5f7eb 100%); text-align: center; border-bottom: 1px solid #d8eadf;">
                        <div style="display:inline-block;text-align:center;">
                            <img src="{{ asset('assets/images/soilandwater_logo.png') }}" alt="SoilNWater" width="200" style="display:block;width:200px;max-width:100%;height:auto;border:0;outline:none;text-decoration:none;">
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 30px 28px 10px;">
                        @yield('content')
                    </td>
                </tr>

                <tr>
                    <td style="padding: 20px 28px 28px;">
                        <div style="border-top: 1px solid #e5e7eb; padding-top: 16px; color: #6b7280; font-size: 12px; line-height: 1.6;">
                            <p style="margin: 0 0 8px;">If you did not request this message, please ignore this email or contact our support team immediately.</p>
                            <p style="margin: 0;">&copy; {{ now()->year }} {{ config('app.name', 'SoilNWater') }}. All rights reserved.</p>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
