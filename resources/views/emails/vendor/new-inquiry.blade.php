@extends('emails.layouts.base')
@section('content')
<h2 style="margin:0 0 10px;font-size:28px;color:#0f172a;">New Product Enquiry</h2>
<p style="margin:0 0 18px;color:#475569;font-size:14px;">You received a new product enquiry.</p>
<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate;border-spacing:0;border:1px solid #dbe5ef;border-radius:10px;overflow:hidden;">
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;width:42%;font-weight:700;color:#0f172a;">Product</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $product?->name ?? 'N/A' }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Email</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $inquiry->email }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Phone</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $inquiry->phone_number }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Connect via</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ ucfirst($inquiry->preferred_contact) }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;font-weight:700;color:#0f172a;vertical-align:top;">Reason</td><td style="padding:12px 14px;color:#1e293b;white-space:pre-wrap;">{{ $inquiry->reason }}</td></tr>
</table>
@endsection
