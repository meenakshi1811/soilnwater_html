<div class="discussion-reply border rounded-3 p-3 mb-3"
     id="discussion-reply-{{ $reply->id }}"
     data-reply-id="{{ $reply->id }}"
     data-reactable-type="DiscussionReply"
     data-reactable-id="{{ $reply->id }}">
    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
        <div>
            <strong>{{ $reply->displayAuthorName() }}</strong>
            <small class="text-muted d-block discussion-reply-time">{{ $reply->created_at->diffForHumans() }}</small>
        </div>
    </div>
    @if($reply->body)
        <p class="mb-2 discussion-reply-body">{!! nl2br(e($reply->body)) !!}</p>
    @endif

    @include('discussions.partials.attachments', ['attachments' => $reply->attachments ?? []])

    @include('discussions.partials.reactions', [
        'reactableType' => 'DiscussionReply',
        'reactableId' => $reply->id,
        'counts' => $reply->reactionCounts(),
        'userReactions' => $userReactions,
        'reactUrl' => route('discussions.replies.react', $reply),
    ])
</div>
