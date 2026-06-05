@extends('emails.layouts.base')

@section('content')
@php
    $categoryName = isset($category) ? $category->name : ($service?->categoryModel?->name ?? '—');
    $subcategoryName = isset($subcategory) ? $subcategory->name : ($service?->subcategoryModel?->name ?? '—');
    $serviceName = $service?->name ?? 'a matching consultant service';
@endphp

<h2 style="margin:0 0 10px;font-size:28px;color:#0f172a;">New Consultation Enquiry</h2>
<p style="margin:0 0 18px;color:#475569;font-size:14px;">You received a new enquiry for <strong>{{ $serviceName }}</strong>.</p>

<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate;border-spacing:0;border:1px solid #dbe5ef;border-radius:10px;overflow:hidden;">
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;width:42%;font-weight:700;color:#0f172a;">Service</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $serviceName }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Category</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $categoryName }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Sub category</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $subcategoryName }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Client name</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $inquiry->client_name }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Phone</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $inquiry->phone_number }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Email</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $inquiry->email }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Occupation</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $inquiry->occupation ?: '—' }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;border-bottom:1px solid #dbe5ef;font-weight:700;color:#0f172a;">Date of birth</td><td style="padding:12px 14px;border-bottom:1px solid #dbe5ef;color:#1e293b;">{{ $inquiry->date_of_birth?->format('d M Y') ?? '—' }}</td></tr>
<tr><td style="padding:12px 14px;background:#f8fbff;font-weight:700;color:#0f172a;vertical-align:top;">Question</td><td style="padding:12px 14px;color:#1e293b;white-space:pre-wrap;">{{ $inquiry->question }}</td></tr>
</table>

<p style="margin:18px 0 0;color:#475569;font-size:14px;">Please log in to your consultant portal to view and respond to this enquiry.</p>
@endsection
