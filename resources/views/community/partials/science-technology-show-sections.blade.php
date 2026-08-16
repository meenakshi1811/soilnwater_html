@if($post->isScienceTechnologyPost())
    @php
        $portalLayout = $portalLayout ?? false;
        $postType = data_get($post->meta, 'science_technology_post_type');
        $category = data_get($post->meta, 'science_technology_category', $post->category);
        $level = data_get($post->meta, 'science_technology_level');
        $technologies = (array) data_get($post->meta, 'science_technology_technologies_used', []);
        $waterSoilTopics = (array) data_get($post->meta, 'science_technology_water_soil_topics', []);
        $showcaseEnabled = (bool) data_get($post->meta, 'science_technology_enable_innovation_showcase', false);
        $expertReviewEnabled = (bool) data_get($post->meta, 'science_technology_enable_expert_review', false);
        $askCommunity = data_get($post->meta, 'science_technology_ask_community');
        $galleryCategories = \App\Support\CommunityContentTaxonomy::scienceTechnologyGalleryCategories();
    @endphp

    @unless($portalLayout)
    <div class="env-show-overview mb-4">
        <div class="env-show-overview__kicker">Science &amp; Technology · SoilnWater innovation network</div>
        <div class="env-show-overview__title">Research, engineering, and technology for community impact</div>
        <div class="env-show-overview__chips">
            @if(filled($postType))
                <span class="env-show-chip">{{ $postType }}</span>
            @endif
            @if(filled($category))
                <span class="env-show-chip">{{ $category }}</span>
            @endif
            @if(filled($level))
                <span class="env-show-chip">{{ $level }}</span>
            @endif
            @if($showcaseEnabled)
                <span class="env-show-chip env-show-chip--flagship"><i class="fa-solid fa-lightbulb me-1"></i>Innovation Showcase</span>
            @endif
            @if($expertReviewEnabled)
                <span class="env-show-chip env-show-chip--flagship"><i class="fa-solid fa-user-check me-1"></i>Expert Review</span>
            @endif
        </div>
    </div>
    @else
        @if($technologies !== [] || $waterSoilTopics !== [] || $showcaseEnabled || $expertReviewEnabled || filled($postType) || filled($level))
            <div class="news-detail-highlights mb-4">
                <h4>Project details</h4>
                <ul>
                    @if(filled($postType))
                        <li><strong>Post type:</strong> {{ $postType }}</li>
                    @endif
                    @if(filled($level))
                        <li><strong>Level:</strong> {{ $level }}</li>
                    @endif
                    @if($showcaseEnabled)
                        <li><strong>Innovation Showcase:</strong> Enabled</li>
                    @endif
                    @if($expertReviewEnabled)
                        <li><strong>Expert Review:</strong> Enabled</li>
                    @endif
                </ul>
            </div>
        @endif
    @endunless

    @if($technologies !== [])
        <div class="mb-4">
            <h5 class="h6 text-muted mb-2">Technologies used</h5>
            <div class="d-flex flex-wrap gap-2">
                @foreach($technologies as $tech)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $tech }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($waterSoilTopics !== [])
        <div class="env-flagship-banner env-flagship-banner--water d-flex align-items-start gap-3 mb-4" role="status">
            <i class="fa-solid fa-droplet text-info fs-4 mt-1" aria-hidden="true"></i>
            <div class="flex-grow-1">
                <div class="text-info fw-bold mb-1">Water &amp; soil technology · SoilnWater flagship</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($waterSoilTopics as $topic)
                        <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $topic }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(filled($askCommunity))
        <div class="alert alert-light border mb-4">
            <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-question me-1"></i>Ask the community</div>
            <p class="mb-0">{{ $askCommunity }}</p>
        </div>
    @endif

    @include('community.partials.science-technology-meta-details', ['post' => $post])

    @php
        $gallery = (array) data_get($post->meta, 'science_technology_gallery', []);
        $documents = (array) data_get($post->meta, 'science_technology_documents', []);
    @endphp

    @if($gallery !== [] || $documents !== [])
        <div class="about-box mt-4 business-meta-grid chart-card p-3 p-lg-4">
            <h4>Project files &amp; gallery</h4>
            @foreach($galleryCategories as $galleryKey => $galleryLabel)
                @php $photos = (array) data_get($gallery, $galleryKey, []); @endphp
                @if($photos !== [])
                    <div class="mb-3">
                        <h5 class="h6 mb-2">{{ $galleryLabel }}</h5>
                        <div class="business-gallery-grid">
                            @foreach($photos as $photo)
                                <a href="{{ data_get($photo, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                                    <img src="{{ data_get($photo, 'url') }}" alt="{{ $galleryLabel }}" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
            @if($documents !== [])
                <div class="d-flex flex-wrap gap-2">
                    @foreach($documents as $document)
                        <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">{{ data_get($document, 'name', 'Document') }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endif
