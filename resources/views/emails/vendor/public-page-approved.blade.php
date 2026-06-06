@extends('emails.layouts.base')
@section('title', 'Public page approved')
@section('content')
<h2 style="margin:0 0 16px;color:#1f2937;">Your public page is approved</h2>
<p style="margin:0 0 18px;color:#374151;line-height:1.7;">Admin approved the latest changes to your vendor page. The approved version is now visible on the main website.</p>
<a href="{{ route('store.show', data_get($vendor->published_page_data, 'profile.slug', $vendor->slug)) }}" style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">View Public Page</a>
@endsection
