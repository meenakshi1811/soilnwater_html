@if($post->isWomensWorldPost())
    @php
        $mainCategory = data_get($post->meta, 'womens_world_category') ?: $post->category;
        $contentType = data_get($post->meta, 'womens_world_content_type');
        $lifeStage = data_get($post->meta, 'womens_world_life_stage');
        $audiences = array_values(array_filter((array) data_get($post->meta, 'womens_world_target_audience', [])));
        $featuredTopics = array_values(array_filter((array) data_get($post->meta, 'womens_world_featured_topics', [])));
        $themes = array_values(array_filter((array) data_get($post->meta, 'womens_world_themes', [])));
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
        $visibilityLabel = $post->womensWorldVisibilityLabel();
        $publishAsLabel = \App\Support\CommunityContentTaxonomy::womensWorldPublishAsOptions()[$post->resolvedPublishAs()]
            ?? $post->publishAsLabel();
    @endphp

    @if(filled($mainCategory) || filled($contentType) || filled($lifeStage))
        <div class="business-hero-strip mb-4">
            @if(filled($mainCategory))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Main category</span>
                    <span class="business-hero-strip__value">{{ $mainCategory }}</span>
                </div>
            @endif
            @if(filled($contentType))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Content type</span>
                    <span class="business-hero-strip__value">{{ $contentType }}</span>
                </div>
            @endif
            @if(filled($lifeStage))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Life stage</span>
                    <span class="business-hero-strip__value">{{ $lifeStage }}</span>
                </div>
            @endif
            @if($post->womensWorldVisibilitySetting() !== 'public')
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Visibility</span>
                    <span class="business-hero-strip__value">{{ $visibilityLabel }}</span>
                </div>
            @endif
        </div>
    @endif

    @if($audiences !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <h4 class="mb-0">Target audience</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($audiences as $audience)
                    <span class="business-pill">{{ $audience }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($featuredTopics !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-star" aria-hidden="true"></i>
                <h4 class="mb-0">Featured topics</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($featuredTopics as $topic)
                    <span class="badge bg-warning-subtle text-dark border">{{ $topic }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($themes !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                <h4 class="mb-0">Themes</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($themes as $theme)
                    <span class="badge bg-light text-dark border">{{ $theme }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($structuredLocation->isNotEmpty())
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Location</h4>
                    <p class="text-muted small mb-0">For local relevance — exact addresses are not shown.</p>
                </div>
            </div>
            <div class="row g-3">
                @foreach($structuredLocation as $key => $value)
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">{{ $locationLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @include('community.partials.womens-world-media-sections', ['post' => $post])
    @include('community.partials.womens-world-engagement-sections', ['post' => $post])

    @if(!empty($post->tags))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-hashtag" aria-hidden="true"></i>
                <h4 class="mb-0">Tags</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                    <span class="badge bg-light text-dark border">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->allowsPoll() || $post->allow_comments || $post->allow_questions || $post->allow_suggestions || $post->allow_sharing)
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                <h4 class="mb-0">Reader engagement</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($post->allowsPoll())
                    <span class="badge bg-primary text-white">Poll open</span>
                @endif
                @if($post->allow_comments)
                    <span class="badge bg-success">Comments</span>
                @endif
                @if($post->allow_questions)
                    <span class="badge bg-success">Questions</span>
                @endif
                @if($post->allow_suggestions)
                    <span class="badge bg-success">Suggestions</span>
                @endif
                @if($post->allow_sharing)
                    <span class="badge bg-success">Sharing</span>
                @endif
            </div>
            @if($post->allowsPoll() && filled(data_get($post->meta, 'womens_world_poll_question')))
                <p class="small text-muted mb-0 mt-3">
                    <strong>Poll:</strong> {{ data_get($post->meta, 'womens_world_poll_question') }}
                </p>
            @endif
        </div>
    @endif

    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-heart" aria-hidden="true"></i>
            <h4 class="mb-0">Community reactions</h4>
        </div>
        <p class="text-muted small mb-2">Positive reactions only.</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::womensWorldReactionOptions() as $reaction => $icon)
                <span class="badge bg-light text-dark border">
                    <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
                </span>
            @endforeach
        </div>
    </div>
@endif
