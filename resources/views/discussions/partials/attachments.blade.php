@php
    use App\Support\DiscussionAttachments;
    $attachments = $attachments ?? [];
@endphp

@if(! empty($attachments))
    <div class="discussion-attachments">
        @foreach($attachments as $attachment)
            @php
                $kind = $attachment['kind'] ?? 'image';
                $icon = $attachment['icon'] ?? DiscussionAttachments::iconForKind($kind, $attachment['extension'] ?? null);
                $extension = strtoupper((string) ($attachment['extension'] ?? ''));
            @endphp
            @if($kind === 'document')
                <a class="discussion-attachments__document" href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener">
                    <span class="discussion-attachments__document-icon"><i class="fa-solid {{ $icon }}"></i></span>
                    <span class="discussion-attachments__document-meta">
                        <strong>{{ $attachment['name'] ?? 'Document' }}</strong>
                        <span>{{ $extension !== '' ? $extension.' file' : 'Document' }}</span>
                    </span>
                    <span class="discussion-attachments__type-badge"><i class="fa-solid fa-file-lines"></i></span>
                </a>
            @elseif($kind === 'video')
                <div class="discussion-attachments__video-wrap">
                    <span class="discussion-attachments__type-badge"><i class="fa-solid fa-video"></i></span>
                    <video class="discussion-attachments__video" controls preload="metadata" src="{{ $attachment['url'] ?? '' }}">
                        Your browser does not support video playback.
                    </video>
                </div>
            @else
                <div class="discussion-attachments__image-wrap">
                    <span class="discussion-attachments__type-badge"><i class="fa-solid fa-image"></i></span>
                    <a class="discussion-attachments__image-link" href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener">
                        <img class="discussion-attachments__image" src="{{ $attachment['url'] ?? '' }}" alt="{{ $attachment['name'] ?? 'Image attachment' }}" loading="lazy">
                    </a>
                </div>
            @endif
        @endforeach
    </div>
@endif
