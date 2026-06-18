@php
    $contentLabel = match ($post->content_type) {
        'poetry' => 'poem',
        'stories' => 'story',
        'autobiography' => 'autobiography',
        default => 'post',
    };
@endphp
<p>Hello {{ $post->user?->full_name ?: $post->user?->name }},</p>

@if($actor)
    <p>
        <strong>{{ $actor->full_name ?: $actor->name }}</strong>
        submitted new <strong>{{ strtolower($engagementType) }}</strong> on your {{ $contentLabel }}
        <strong>"{{ $post->title }}"</strong>.
    </p>
@else
    <p>
        Your {{ $contentLabel }} <strong>"{{ $post->title }}"</strong> received a new update.
    </p>
@endif

<p>{{ $summary }}</p>

@if(is_array($badgeLabels) && $badgeLabels !== [])
    <p><strong>Achievement{{ count($badgeLabels) === 1 ? '' : 's' }}:</strong> {{ implode(', ', $badgeLabels) }}</p>
@endif

@if($rating)
    <p><strong>Rating:</strong> {{ $rating }} / 5 stars</p>
@endif

@if($actionUrl)
    <p><a href="{{ $actionUrl }}">View your {{ $contentLabel }} in the portal</a></p>
@endif

<p>SoilnWater Community</p>
