<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Premium Payment Proof</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
  <h2 style="margin-bottom: 8px;">Premium payment proof submitted</h2>
  <p style="margin-top: 0;">A user has submitted payment proof for premium membership. Please review the attached screenshot.</p>

  <table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
    <tr>
      <td><strong>User</strong></td>
      <td>{{ $submission->user?->full_name ?: $submission->user?->name }} ({{ $submission->user?->email }})</td>
    </tr>
    <tr>
      <td><strong>Profile type</strong></td>
      <td>{{ $submission->profileTypeLabel() }}</td>
    </tr>
    <tr>
      <td><strong>Profile name</strong></td>
      <td>{{ $submission->profileDisplayName() }}</td>
    </tr>
    @if($submission->transaction_reference)
      <tr>
        <td><strong>Transaction reference</strong></td>
        <td>{{ $submission->transaction_reference }}</td>
      </tr>
    @endif
    @if($submission->user_note)
      <tr>
        <td><strong>User note</strong></td>
        <td>{{ $submission->user_note }}</td>
      </tr>
    @endif
    <tr>
      <td><strong>Submitted at</strong></td>
      <td>{{ $submission->submitted_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
    </tr>
  </table>

  <p style="margin-top: 18px;">
  <a href="{{ route('admin.premium-payments.show', $submission) }}" style="display:inline-block;padding:10px 16px;background:#1a237e;color:#fff;text-decoration:none;border-radius:8px;">
    Review in admin portal
  </a>
  </p>
</body>
</html>
