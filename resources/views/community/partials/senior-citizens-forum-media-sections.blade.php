@php
    $videoType = data_get($post->meta, 'senior_citizens_forum_video_type');
    $hasVideo = $post->hasVideo();
@endphp

@if($post->isSeniorCitizensForumPost() && $post->seniorCitizensForumAudioUrl())
    <div class="scf-section-panel about-box mb-4">
        <div class="scf-section-panel__header">
            <i class="fa-solid fa-microphone" aria-hidden="true"></i>
            <h4 class="mb-0">Audio memories</h4>
        </div>
        <audio controls class="w-100" preload="metadata">
            <source src="{{ $post->seniorCitizensForumAudioUrl() }}">
            Your browser does not support embedded audio playback.
        </audio>
        @if(filled(data_get($post->seniorCitizensForumAudioData(), 'name')))
            <small class="text-muted d-block mt-2">{{ data_get($post->seniorCitizensForumAudioData(), 'name') }}</small>
        @endif
    </div>
@endif

@if($hasVideo)
    <div class="scf-section-panel about-box mb-4">
        <div class="scf-section-panel__header">
            <i class="fa-solid fa-video" aria-hidden="true"></i>
            <h4 class="mb-0">
                Video memories
                @if(filled($videoType))
                    <span class="text-muted fw-normal fs-6">— {{ $videoType }}</span>
                @endif
            </h4>
        </div>
        @if($post->youtubeEmbedUrl())
            <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                <iframe
                    src="{{ $post->youtubeEmbedUrl() }}"
                    title="Video for {{ $post->title }}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                ></iframe>
            </div>
        @elseif($post->videoFileUrl())
            <video controls class="w-100 rounded shadow-sm" preload="metadata">
                <source src="{{ $post->videoFileUrl() }}">
                Your browser does not support embedded video playback.
            </video>
            @if(filled(data_get($post->videoData(), 'name')))
                <small class="text-muted d-block mt-2">{{ data_get($post->videoData(), 'name') }}</small>
            @endif
        @endif
    </div>
@endif
