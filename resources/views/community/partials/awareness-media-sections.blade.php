@php
    $infographics = $post->awarenessInfographics();
    $documents = $post->awarenessDocuments();
    $videoType = data_get($post->meta, 'awareness_video_type');
    $hasAwarenessVideo = $post->hasVideo();
@endphp

@if($infographics !== [])
    <div class="awareness-section-panel about-box mb-4">
        <div class="awareness-section-panel__header">
            <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
            <h4 class="mb-0">Infographics</h4>
        </div>
        <div class="awareness-infographic-grid">
            @foreach($infographics as $infographic)
                @if($post->isAwarenessInfographicImage($infographic))
                    <a href="{{ data_get($infographic, 'url') }}" target="_blank" rel="noopener" class="awareness-infographic-card">
                        <img src="{{ data_get($infographic, 'url') }}" alt="{{ data_get($infographic, 'name', 'Infographic') }}" loading="lazy">
                        <span class="awareness-infographic-card__label">{{ data_get($infographic, 'name', 'Infographic') }}</span>
                    </a>
                @else
                    <a href="{{ data_get($infographic, 'url') }}" target="_blank" rel="noopener" class="awareness-document-link">
                        <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                        <span>{{ data_get($infographic, 'name', 'Infographic PDF') }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif

@if($hasAwarenessVideo)
    <div class="awareness-section-panel about-box mb-4">
        <div class="awareness-section-panel__header">
            <i class="fa-solid fa-video" aria-hidden="true"></i>
            <h4 class="mb-0">
                Video content
                @if(filled($videoType))
                    <span class="text-muted fw-normal fs-6">— {{ $videoType }}</span>
                @endif
            </h4>
        </div>
        @if($post->youtubeEmbedUrl())
            <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                <iframe
                    src="{{ $post->youtubeEmbedUrl() }}"
                    title="Awareness video for {{ $post->title }}"
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

@if($documents !== [])
    <div class="awareness-section-panel about-box mb-4">
        <div class="awareness-section-panel__header">
            <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
            <h4 class="mb-0">Documents</h4>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach($documents as $document)
                <a href="{{ data_get($document, 'url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-file-lines me-1" aria-hidden="true"></i>{{ data_get($document, 'name', 'Document') }}
                </a>
            @endforeach
        </div>
    </div>
@endif
