@extends('emails.layouts.base')

@section('content')
    @php
        $readerName = $subscriber->full_name ?: ($subscriber->name ?? 'there');
    @endphp

    <h2 style="margin:0 0 16px;color:#1f2937;">New community post for you</h2>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        Hello {{ $readerName }},
    </p>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        A new post matching a category or topic you follow has been published on SoilnWater Community.
    </p>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        <strong>{{ $post->title }}</strong><br>
        {{ $post->typeLabel() }} · {{ $post->category }}
    </p>

    <a href="{{ route('community.show', $post) }}" style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">Read Post</a>
@endsection
