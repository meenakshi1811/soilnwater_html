@extends('emails.layouts.base')

@section('content')
    <h2 style="margin:0 0 16px;color:#1f2937;">Your question was answered</h2>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        Hello {{ $details['asker_name'] }},
    </p>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        <strong>{{ $details['educator_name'] }}</strong> answered your question on their Teacher / Tutor profile.
    </p>

    @if(!empty($details['subject']))
        <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
            <strong>Topic:</strong> {{ $details['subject'] }}
        </p>
    @endif

    <p style="margin:0 0 8px;color:#64748b;font-size:14px;font-weight:700;">Your question</p>
    <div style="margin:0 0 18px;padding:14px 16px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#1f2937;line-height:1.7;white-space:pre-line;">
        {{ $details['question'] }}
    </div>

    <p style="margin:0 0 8px;color:#64748b;font-size:14px;font-weight:700;">Answer</p>
    <div style="margin:0 0 18px;padding:14px 16px;border-radius:10px;background:#ecfdf3;border:1px solid #bbf7d0;color:#14532d;line-height:1.7;white-space:pre-line;">
        {{ $details['answer'] }}
    </div>

    <a href="{{ $details['profile_url'] }}" style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">View teacher profile</a>
@endsection
