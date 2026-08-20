@php
    $gallery = $post->youthCornerGallery();
    $videoType = data_get($post->meta, 'youth_corner_video_type');
    $hasVideo = $post->hasVideo();
    $railLayout = $railLayout ?? false;
    $achievementsOnSidebar = $achievementsOnSidebar ?? false;
@endphp

@if($gallery !== [])
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-images" aria-hidden="true"></i>
            <h4 class="mb-0">Image gallery</h4>
        </div>
        <div class="business-gallery-grid">
            @foreach($gallery as $image)
                @if($post->isYouthCornerGalleryImage($image))
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

@if($hasVideo)
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

@php
    $documents = $post->youthCornerDocuments();
    $achievements = $post->youthCornerAchievements();
@endphp

@if($documents !== [])
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
            <h4 class="mb-0">Documents &amp; resources</h4>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach($documents as $document)
                <a href="{{ data_get($document, 'url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-download me-1" aria-hidden="true"></i>{{ data_get($document, 'name', 'Document') }}
                </a>
            @endforeach
        </div>
    </div>
@endif

@if($achievements !== [] && ! $railLayout && ! $achievementsOnSidebar)
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-award" aria-hidden="true"></i>
            <h4 class="mb-0">Achievements &amp; certificates</h4>
        </div>
        <div class="row g-3">
            @foreach($achievements as $achievement)
                <div class="col-md-6">
                    <div class="border rounded p-3 bg-light h-100">
                        <strong>{{ data_get($achievement, 'achievement_title', data_get($achievement, 'title', 'Achievement')) }}</strong>
                        @if(filled(data_get($achievement, 'year')))
                            <span class="text-muted"> — {{ data_get($achievement, 'year') }}</span>
                        @endif
                        @if(filled(data_get($achievement, 'certificate.url')))
                            <div class="mt-2">
                                <a href="{{ data_get($achievement, 'certificate.url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-certificate me-1" aria-hidden="true"></i>View certificate
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
