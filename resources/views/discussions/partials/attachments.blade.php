@php
    use App\Support\DiscussionReactions;
    $attachments = $attachments ?? [];
@endphp

@if(! empty($attachments))
    <div class="discussion-attachments">
        @foreach($attachments as $attachment)
            @if(($attachment['kind'] ?? '') === 'video')
                <video class="discussion-attachments__video" controls preload="metadata" src="{{ $attachment['url'] ?? '' }}">
                    Your browser does not support video playback.
                </video>
            @else
                <a class="discussion-attachments__image-link" href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener">
                    <img class="discussion-attachments__image" src="{{ $attachment['url'] ?? '' }}" alt="{{ $attachment['name'] ?? 'Image attachment' }}" loading="lazy">
                </a>
            @endif
        @endforeach
    </div>
@endif
