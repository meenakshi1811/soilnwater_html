<p>Hello {{ $recipient->full_name ?: $recipient->name }},</p>

<p>
    Good news — your comment on
    <strong>"{{ $post->title }}"</strong>
    in Children's Corner has been approved and is now visible publicly.
</p>

<p><em>"{{ \Illuminate\Support\Str::limit($comment->body, 200) }}"</em></p>

<p><a href="{{ route('community.show', $post) }}#public-participation">View the discussion</a></p>

<p>SoilnWater Community</p>
