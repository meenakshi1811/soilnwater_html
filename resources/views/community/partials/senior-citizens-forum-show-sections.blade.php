@if($post->isSeniorCitizensForumPost())
    @php
        $mainCategory = data_get($post->meta, 'senior_citizens_forum_category') ?: $post->category;
        $contentType = data_get($post->meta, 'senior_citizens_forum_content_type');
        $ageGroup = data_get($post->meta, 'senior_citizens_forum_age_group');
        $lifeJourney = array_values(array_filter((array) data_get($post->meta, 'senior_citizens_forum_life_journey_categories', [])));
        $themes = array_values(array_filter((array) data_get($post->meta, 'senior_citizens_forum_themes', [])));
        $contributions = array_values(array_filter((array) data_get($post->meta, 'senior_citizens_forum_community_contributions', [])));
        $intergenerational = array_values(array_filter((array) data_get($post->meta, 'senior_citizens_forum_intergenerational_connections', [])));
        $keyLessons = array_values(array_filter((array) data_get($post->meta, 'senior_citizens_forum_key_lessons', [])));
        $preserveLegacy = (bool) data_get($post->meta, 'senior_citizens_forum_preserve_digital_legacy', false);
        $visibilityLabel = $post->seniorCitizensForumVisibilityLabel();
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
    @endphp

    @if($preserveLegacy)
        <div class="scf-digital-legacy-banner mb-4">
            <div>
                <div class="scf-digital-legacy-banner__title">
                    <i class="fa-solid fa-landmark me-2" aria-hidden="true"></i>Preserved as Digital Legacy
                </div>
                <p class="mb-0 small opacity-75">This story is part of a lasting archive for family and future generations.</p>
            </div>
            <ul class="scf-digital-legacy-banner__benefits">
                @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumDigitalLegacyBenefits() as $benefit)
                    <li>{{ $benefit }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(filled($mainCategory) || filled($contentType) || filled($ageGroup))
        <div class="scf-hero-strip mb-4">
            @if(filled($mainCategory))
                <div class="scf-hero-strip__item">
                    <span class="scf-hero-strip__label">Main category</span>
                    <span class="scf-hero-strip__value">{{ $mainCategory }}</span>
                </div>
            @endif
            @if(filled($contentType))
                <div class="scf-hero-strip__item">
                    <span class="scf-hero-strip__label">Content type</span>
                    <span class="scf-hero-strip__value">{{ $contentType }}</span>
                </div>
            @endif
            @if(filled($ageGroup))
                <div class="scf-hero-strip__item">
                    <span class="scf-hero-strip__label">Age group</span>
                    <span class="scf-hero-strip__value">{{ $ageGroup }}</span>
                </div>
            @endif
            @if($post->seniorCitizensForumVisibilitySetting() !== 'public')
                <div class="scf-hero-strip__item">
                    <span class="scf-hero-strip__label">Visibility</span>
                    <span class="scf-hero-strip__value">{{ $visibilityLabel }}</span>
                </div>
            @endif
        </div>
    @endif

    @if($intergenerational !== [])
        <div class="scf-section-panel about-box mb-4">
            <div class="scf-section-panel__header">
                <i class="fa-solid fa-seedling" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Intergenerational connections</h4>
                    <p class="text-muted small mb-0">Wisdom tagged for younger readers.</p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($intergenerational as $connection)
                    <span class="scf-pill scf-pill--wisdom">{{ $connection }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($keyLessons !== [])
        <div class="scf-section-panel about-box mb-4">
            <div class="scf-section-panel__header">
                <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Key life lessons</h4>
                    <p class="text-muted small mb-0">Highlights from a lifetime of experience.</p>
                </div>
            </div>
            <div class="scf-key-lessons">
                @foreach($keyLessons as $lesson)
                    <div class="scf-key-lesson">{{ $lesson }}</div>
                @endforeach
            </div>
        </div>
    @endif

    @if($lifeJourney !== [])
        <div class="scf-section-panel about-box mb-4">
            <div class="scf-section-panel__header">
                <i class="fa-solid fa-road" aria-hidden="true"></i>
                <h4 class="mb-0">Life journey</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($lifeJourney as $journey)
                    <span class="scf-pill">{{ $journey }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($themes !== [])
        <div class="scf-section-panel about-box mb-4">
            <div class="scf-section-panel__header">
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

    @if($contributions !== [])
        <div class="scf-section-panel about-box mb-4">
            <div class="scf-section-panel__header">
                <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Community contribution</h4>
                    <p class="text-muted small mb-0">How this author has served their community.</p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($contributions as $contribution)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $contribution }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($structuredLocation->isNotEmpty())
        @unless($portalSidebarLayout ?? false)
        <div class="scf-section-panel about-box mb-4">
            <div class="scf-section-panel__header">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Location</h4>
                    <p class="text-muted small mb-0">Local heritage context — exact addresses are not shown.</p>
                </div>
            </div>
            <div class="row g-3">
                @foreach($structuredLocation as $key => $value)
                    <div class="col-md-6 col-lg-4">
                        <div class="scf-meta-item">
                            <span class="scf-meta-item__label">{{ $locationLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endunless
    @endif
@endif
