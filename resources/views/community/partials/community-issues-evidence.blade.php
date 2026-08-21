@php
    $post = $post ?? null;
    if (! $post?->isCommunityIssuesPost()) {
        return;
    }

    $railLayout = $railLayout ?? false;
    $hasEvidence = $post->featured_image_path
        || $post->communityIssuePhotoEvidence() !== []
        || $post->hasVideo()
        || $post->communityIssueDocuments() !== [];
@endphp

@if($hasEvidence)
    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail community-issues-evidence-rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-camera"></i></span>
                <div>
                    <h4 class="community-detail-card__title">{{ $heading ?? 'Evidence upload' }}</h4>
                </div>
            </div>

            @if($post->featuredImageUrl())
                <div class="community-issues-evidence-rail__featured mb-3">
                    <div class="business-meta-item__label mb-2">Featured image</div>
                    <img src="{{ $post->featuredImageUrl() }}" alt="Featured issue evidence" class="img-fluid rounded border w-100">
                </div>
            @endif

            @if($post->communityIssuePhotoEvidence() !== [])
                <div class="community-issues-evidence-rail__gallery mb-3">
                    @foreach($post->communityIssuePhotoEvidence() as $photo)
                        <a href="{{ data_get($photo, 'url') }}" target="_blank" rel="noopener" class="community-issues-evidence-rail__thumb">
                            <img src="{{ data_get($photo, 'url') }}" alt="Issue evidence" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif

            @if($post->hasVideo())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Video evidence</div>
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Video evidence" allowfullscreen></iframe></div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
                    @endif
                </div>
            @endif

            @if($post->communityIssueDocuments() !== [])
                <div class="d-flex flex-column gap-2">
                    @foreach($post->communityIssueDocuments() as $document)
                        <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary text-start" target="_blank" rel="noopener">
                            <i class="fa-solid fa-file-lines me-1"></i>{{ data_get($document, 'name', 'Document') }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-camera text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">{{ $heading ?? 'Evidence upload' }}</h4>
            </div>

            @if($post->featuredImageUrl())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Featured image</div>
                    <img src="{{ $post->featuredImageUrl() }}" alt="Featured issue evidence" class="img-fluid rounded border">
                </div>
            @endif

            @if($post->communityIssuePhotoEvidence() !== [])
                <div class="business-gallery-grid mb-3">
                    @foreach($post->communityIssuePhotoEvidence() as $photo)
                        <a href="{{ data_get($photo, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                            <img src="{{ data_get($photo, 'url') }}" alt="Issue evidence" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif

            @if($post->hasVideo())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Video evidence</div>
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Video evidence" allowfullscreen></iframe></div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
                    @endif
                </div>
            @endif

            @if($post->communityIssueDocuments() !== [])
                <div class="d-flex flex-wrap gap-2">
                    @foreach($post->communityIssueDocuments() as $document)
                        <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                            <i class="fa-solid fa-file-lines me-1"></i>{{ data_get($document, 'name', 'Document') }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endif
