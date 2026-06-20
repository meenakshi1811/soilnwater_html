@if($post->isAwarenessPost())
    @php
        $audiences = array_values(array_filter((array) data_get($post->meta, 'awareness_target_audience', [])));
        $awarenessType = data_get($post->meta, 'awareness_type');
        $awarenessLevel = data_get($post->meta, 'awareness_level');
        $awarenessCategory = $post->awarenessCategoryLabel();
    @endphp

    @if(filled($awarenessCategory) || filled($awarenessType) || filled($awarenessLevel) || filled($post->awarenessCampaignPeriodForDisplay()))
        <div class="awareness-hero-strip mb-4">
            @if(filled($awarenessCategory))
                <div class="awareness-hero-strip__item">
                    <span class="awareness-hero-strip__label">Main category</span>
                    <span class="awareness-hero-strip__value">{{ $awarenessCategory }}</span>
                </div>
            @endif
            @if(filled($awarenessType))
                <div class="awareness-hero-strip__item">
                    <span class="awareness-hero-strip__label">Awareness type</span>
                    <span class="awareness-hero-strip__value">{{ $awarenessType }}</span>
                </div>
            @endif
            @if(filled($awarenessLevel))
                <div class="awareness-hero-strip__item">
                    <span class="awareness-hero-strip__label">Awareness level</span>
                    <span class="awareness-hero-strip__value">{{ $awarenessLevel }}</span>
                </div>
            @endif
            @if(filled($post->awarenessCampaignPeriodForDisplay()))
                <div class="awareness-hero-strip__item">
                    <span class="awareness-hero-strip__label">Campaign period</span>
                    <span class="awareness-hero-strip__value">{{ $post->awarenessCampaignPeriodForDisplay() }}</span>
                </div>
            @endif
        </div>
    @endif

    @if($audiences !== [])
        <div class="awareness-section-panel about-box mb-4">
            <div class="awareness-section-panel__header">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <h4 class="mb-0">Target audience</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($audiences as $audience)
                    <span class="awareness-audience-pill">{{ $audience }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @php
        $awarenessPostedBy = data_get($post->meta, 'awareness_posted_by');
        $awarenessOrganizationName = data_get($post->meta, 'awareness_organization_name');
    @endphp
    @if(filled($awarenessPostedBy) || filled($awarenessOrganizationName))
        <div class="awareness-section-panel about-box mb-4">
            <div class="awareness-section-panel__header">
                <i class="fa-solid fa-building" aria-hidden="true"></i>
                <h4 class="mb-0">Organization details</h4>
            </div>
            <div class="row g-3">
                @if(filled($awarenessPostedBy))
                    <div class="col-md-6">
                        <div class="awareness-meta-item">
                            <span class="awareness-meta-item__label">Posted by</span>
                            <span>{{ $awarenessPostedBy }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($awarenessOrganizationName))
                    <div class="col-md-6">
                        <div class="awareness-meta-item">
                            <span class="awareness-meta-item__label">Organization name</span>
                            <span>{{ $awarenessOrganizationName }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @php
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
    @endphp
    @if($structuredLocation->isNotEmpty())
        <div class="awareness-section-panel about-box mb-4">
            <div class="awareness-section-panel__header">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <h4 class="mb-0">Location</h4>
            </div>
            <div class="row g-3">
                @foreach($structuredLocation as $key => $value)
                    <div class="col-md-6 col-lg-4">
                        <div class="awareness-meta-item">
                            <span class="awareness-meta-item__label">{{ $locationLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @include('community.partials.awareness-media-sections', ['post' => $post])
    @include('community.partials.awareness-engagement-sections', [
        'post' => $post,
        'awarenessEngagement' => $awarenessEngagement ?? null,
        'awarenessPledgeCounts' => $awarenessPledgeCounts ?? [],
    ])
@endif
