@extends('emails.layouts.base')

@section('content')
    @php
        $askerName = $question->askerDisplayName();
        $authorName = $question->authorDisplayName();
    @endphp

    <h2 style="margin:0 0 16px;color:#1f2937;">Your question was answered</h2>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        Hello {{ $askerName }},
    </p>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        <strong>{{ $authorName }}</strong> answered your question on SoilnWater Community.
    </p>

    <p style="margin:0 0 8px;color:#64748b;font-size:14px;font-weight:700;">Your question</p>
    <div style="margin:0 0 18px;padding:14px 16px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#1f2937;line-height:1.7;">
        {{ $question->question }}
    </div>

    <p style="margin:0 0 8px;color:#64748b;font-size:14px;font-weight:700;">Author answer</p>
    <div style="margin:0 0 18px;padding:14px 16px;border-radius:10px;background:#ecfdf3;border:1px solid #bbf7d0;color:#14532d;line-height:1.7;">
        {{ $question->answer }}
    </div>

    <a href="{{ $question->publicUrl() }}" style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">View Answer</a>
@endsection
