@extends('emails.layouts.base')

@section('content')
    @php
        $authorName = $question->authorDisplayName();
        $askerName = $question->askerDisplayName();
    @endphp

    <h2 style="margin:0 0 16px;color:#1f2937;">New question from a reader</h2>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        Hello {{ $authorName }},
    </p>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        <strong>{{ $askerName }}</strong> asked you a question on SoilnWater Community.
    </p>

    @if($question->post)
        <p style="margin:0 0 12px;color:#64748b;font-size:14px;">
            Related post: <strong>{{ $question->post->title }}</strong>
        </p>
    @endif

    <div style="margin:0 0 18px;padding:14px 16px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#1f2937;line-height:1.7;">
        {{ $question->question }}
    </div>

    <a href="{{ route('community.author-questions.index') }}" style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">Answer in Portal</a>
@endsection
