<p>Hello {{ $post->user?->full_name ?: $post->user?->name }},</p>

<p>
    <strong>{{ $participant->full_name ?: $participant->name }}</strong>
    submitted new <strong>{{ strtolower($participationType) }}</strong> on your community post
    <strong>"{{ $post->title }}"</strong>.
</p>

<p>{{ $summary }}</p>

@if($actionUrl)
    <p><a href="{{ $actionUrl }}">View the post and participation</a></p>
@endif

<p>SoilnWater Community</p>
