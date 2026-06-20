@php
    $gallery = $post->businessGallery();
    $documents = $post->businessDocuments();
    $videoType = data_get($post->meta, 'business_video_type');
    $hasBusinessVideo = $post->hasVideo();
@endphp

@if($gallery !== [])
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-images" aria-hidden="true"></i>
            <h4 class="mb-0">Image gallery</h4>
        </div>
        <div class="business-gallery-grid">
            @foreach($gallery as $image)
                @if($post->isBusinessGalleryImage($image))
                    <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                        <img src="{{ data_get($image, 'url') }}" alt="{{ data_get($image, 'name', 'Gallery image') }}" loading="lazy">
                        <span class="business-gallery-card__label">{{ data_get($image, 'name', 'Image') }}</span>
                    </a>
                @else
                    <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-file me-1" aria-hidden="true"></i>{{ data_get($image, 'name', 'File') }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif

@if($hasBusinessVideo)
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-video" aria-hidden="true"></i>
            <h4 class="mb-0">
                Video
                @if(filled($videoType))
                    <span class="text-muted fw-normal fs-6">— {{ $videoType }}</span>
                @endif
            </h4>
        </div>
        @if($post->youtubeEmbedUrl())
            <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                <iframe
                    src="{{ $post->youtubeEmbedUrl() }}"
                    title="Business video for {{ $post->title }}"
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
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
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
