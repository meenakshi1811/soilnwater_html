@if($post->isCreativeCornerPost())
    @php
        $railLayout = $railLayout ?? false;
        $gallery = (array) data_get($post->meta, 'creative_corner_gallery', []);
        $documents = (array) data_get($post->meta, 'creative_corner_documents', []);
        $audio = data_get($post->meta, 'creative_corner_audio');
        $videoType = data_get($post->meta, 'creative_corner_video_type');
        $hasMedia = $post->featuredImageUrl()
            || $post->hasVideo()
            || $gallery !== []
            || filled($audio)
            || $documents !== [];
    @endphp

    @if($hasMedia)
        @if($railLayout)
            <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--media community-detail-card--rail">
                <div class="community-detail-card__head">
                    <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-images"></i></span>
                    <div>
                        <h4 class="community-detail-card__title">Media &amp; documents</h4>
                    </div>
                </div>

                @if($post->featuredImageUrl())
                    <div class="mb-3">
                        <div class="business-meta-item__label mb-2">Featured image</div>
                        <img src="{{ $post->featuredImageUrl() }}" alt="Featured creative work" class="img-fluid rounded border creative-corner-rail-media__featured">
                    </div>
                @endif

                @if($gallery !== [])
                    <div class="mb-3">
                        <div class="business-meta-item__label mb-2">Gallery</div>
                        <div class="cc-gallery-grid cc-gallery-grid--rail">
                            @foreach($gallery as $image)
                                <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="cc-gallery-grid__item d-block">
                                    <img src="{{ data_get($image, 'url') }}" alt="{{ data_get($image, 'name', 'Gallery image') }}" class="img-fluid rounded border" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($post->hasVideo())
                    <div class="mb-3">
                        <div class="business-meta-item__label mb-2">
                            Video{{ filled($videoType) ? ' · '.$videoType : '' }}
                        </div>
                        @if($post->youtubeEmbedUrl())
                            <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Creative Corner video" allowfullscreen></iframe></div>
                        @elseif($post->videoFileUrl())
                            <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
                        @endif
                    </div>
                @endif

                @if(filled($audio))
                    <div class="mb-3">
                        <div class="business-meta-item__label mb-2">
                            Audio{{ filled(data_get($post->meta, 'creative_corner_audio_type')) ? ' · '.data_get($post->meta, 'creative_corner_audio_type') : '' }}
                        </div>
                        <audio controls class="w-100 rounded" preload="metadata" src="{{ data_get($audio, 'url') }}"></audio>
                    </div>
                @endif

                @if($documents !== [])
                    <div class="d-flex flex-column gap-2">
                        @foreach($documents as $document)
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
                    <i class="fa-solid fa-images text-warning" aria-hidden="true"></i>
                    <h4 class="mb-0">Media &amp; documents</h4>
                </div>

                @if($post->featuredImageUrl())
                    <div class="mb-3">
                        <div class="business-meta-item__label mb-2">Featured image</div>
                        <img src="{{ $post->featuredImageUrl() }}" alt="Featured creative work" class="img-fluid rounded border">
                    </div>
                @endif

                @if($gallery !== [])
                    <div class="mb-3 cc-gallery-grid">
                        <div class="business-meta-item__label mb-2">Gallery</div>
                        <div class="row g-2">
                            @foreach($gallery as $image)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="d-block">
                                        <img src="{{ data_get($image, 'url') }}" alt="{{ data_get($image, 'name', 'Gallery image') }}" class="img-fluid rounded border">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($post->hasVideo())
                    <div class="mb-3">
                        <div class="business-meta-item__label mb-2">
                            Video{{ filled($videoType) ? ' · '.$videoType : '' }}
                        </div>
                        @if($post->youtubeEmbedUrl())
                            <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Creative Corner video" allowfullscreen></iframe></div>
                        @elseif($post->videoFileUrl())
                            <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
                        @endif
                    </div>
                @endif

                @if(filled($audio))
                    <div class="mb-3">
                        <div class="business-meta-item__label mb-2">
                            Audio{{ filled(data_get($post->meta, 'creative_corner_audio_type')) ? ' · '.data_get($post->meta, 'creative_corner_audio_type') : '' }}
                        </div>
                        <audio controls class="w-100 rounded" preload="metadata" src="{{ data_get($audio, 'url') }}"></audio>
                    </div>
                @endif

                @if($documents !== [])
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($documents as $document)
                            <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                                <i class="fa-solid fa-file-lines me-1"></i>{{ data_get($document, 'name', 'Document') }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    @endif
@endif
