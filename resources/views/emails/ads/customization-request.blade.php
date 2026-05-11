<!doctype html>
<html>
<head><meta charset="utf-8"><title>Customization Request</title></head>
<body style="font-family:Arial,sans-serif;color:#1f2937;line-height:1.5;">
    <h2 style="margin-bottom:8px;">New Ad Size Customization Request</h2>
    <p style="margin-top:0;color:#6b7280;">A user has requested ad-size customization for an inactive size.</p>
    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#e5e7eb;width:100%;max-width:680px;">
        <tr><td><strong>User Name</strong></td><td>{{ $user->name }}</td></tr>
        <tr><td><strong>Email</strong></td><td>{{ $user->email }}</td></tr>
        <tr><td><strong>Phone Number</strong></td><td>{{ $user->phone_number ?: 'N/A' }}</td></tr>
        <tr><td><strong>Size Type</strong></td><td>{{ $size['name'] ?? $sizeType }} ({{ $size['w'] ?? '-' }}×{{ $size['h'] ?? '-' }})</td></tr>
        <tr><td><strong>Details</strong></td><td>{{ $details }}</td></tr>
    </table>
</body>
</html>
