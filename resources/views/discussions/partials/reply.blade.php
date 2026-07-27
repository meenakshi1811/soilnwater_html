@php
    use Illuminate\Support\Str;
    $authorName = $reply->displayAuthorName();
    $initials = collect(explode(' ', $authorName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->join('');
@endphp
<div class="discussion-reply"
     id="discussion-reply-{{ $reply->id }}"
     data-reply-id="{{ $reply->id }}"
     data-reactable-type="DiscussionReply"
     data-reactable-id="{{ $reply->id }}">
    <span class="discussion-avatar discussion-avatar--sm" aria-hidden="true">{{ $initials ?: 'M' }}</span>
    <div class="discussion-reply__content">
        <div class="discussion-reply__header">
            <span class="discussion-reply__author">{{ $authorName }}</span>
            <small class="discussion-reply-time">{{ $reply->created_at->format('d M Y, h:i A') }}</small>
        </div>
        <div class="discussion-msg__bubble-wrap">
            @if($reply->body || ! empty($reply->attachments))
                <div class="discussion-msg__bubble">
                    @if($reply->body)
                        <p class="discussion-reply-body discussion-msg__body">{!! nl2br(e($reply->body)) !!}</p>
                    @endif
                    @include('discussions.partials.attachments', ['attachments' => $reply->attachments ?? []])
                </div>
            @endif

            @include('discussions.partials.reactions', [
                'reactableType' => 'DiscussionReply',
                'reactableId' => $reply->id,
                'counts' => $reply->reactionCounts(),
                'userReactions' => $userReactions,
                'reactUrl' => route('discussions.replies.react', $reply),
            ])
        </div>
    </div>
</div>
