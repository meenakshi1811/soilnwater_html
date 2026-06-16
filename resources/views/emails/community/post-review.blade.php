@extends('emails.layouts.base')

@section('content')
    @php
        $isApproved = $status === 'approved';
        $isDeclined = $status === 'declined';
        $authorName = $post->user?->full_name ?: ($post->user?->name ?? 'there');
    @endphp

    <h2 style="margin:0 0 16px;color:#1f2937;">{{ $subjectLine }}</h2>

    <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
        Hello {{ $authorName }},
    </p>

    @if ($isApproved)
        <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
            Good news! Your community post <strong>{{ $post->title }}</strong> has been approved and is now live on the website.
        </p>
        <a href="{{ route('community.show', $post) }}" style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">View Published Post</a>
    @elseif ($isDeclined)
        <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
            After reviewing your community post <strong>{{ $post->title }}</strong>, our team is unable to approve it at this time.
        </p>
        @if (filled($post->review_note))
            <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
                <strong>Admin note:</strong> {{ $post->review_note }}
            </p>
        @endif
        <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
            You can review the feedback, update your post, and submit it again for approval.
        </p>
        <a href="{{ route('community.posts.edit', $post) }}" style="display:inline-block;padding:11px 18px;border-radius:8px;background:#12824e;color:#fff;text-decoration:none;font-weight:700;">Edit Post</a>
    @else
        <p style="margin:0 0 18px;color:#374151;line-height:1.7;">
            Your community post <strong>{{ $post->title }}</strong> has been reviewed.
        </p>
    @endif
@endsection
