<p>Hello {{ $post->user?->full_name ?: $post->user?->name }},</p>

<p>
    @if($participant)
        <strong>{{ $participant->full_name ?: $participant->name }}</strong>
        submitted new <strong>{{ strtolower($participationType) }}</strong> on your community post
    @else
        Someone submitted new <strong>{{ strtolower($participationType) }}</strong> on your community post
    @endif
    <strong>"{{ $post->title }}"</strong>.
</p>

<p>{{ $summary }}</p>

@if($actionUrl)
    <p><a href="{{ $actionUrl }}">View the post and participation</a></p>
@endif

<p>SoilnWater Community</p>
