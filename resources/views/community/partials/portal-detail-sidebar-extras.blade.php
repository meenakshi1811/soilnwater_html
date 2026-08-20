@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }

    $visibleMeta = collect($visibleMeta ?? []);
    $formFieldLabels = $formFieldLabels ?? \App\Support\CommunityPostFormFields::labels();
    $resolvedLocation = $resolvedLocation ?? ($post->location ?? data_get($post->meta, 'location'));
    $attachments = $attachments ?? match ($post->content_type) {
        'reports' => data_get($post->meta, 'issue_attachments', []),
        'articles', 'news' => data_get($post->meta, 'news_documents', []),
        'science-technology' => data_get($post->meta, 'science_technology_documents', []),
        default => [],
    };
    $poetryRegionalLocation = $post->content_type === 'poetry'
        ? collect(\App\Support\CommunityPostFormFields::poetryRegionalLocationOrder())
            ->mapWithKeys(fn (string $label, string $key): array => [$key => data_get($post->meta, $key)])
            ->filter(fn (mixed $value): bool => filled($value))
        : collect();
    $childrensCornerLocation = $post->isChildrensCornerPost()
        ? collect([
            'childrens_corner_city' => 'City',
            'childrens_corner_district' => 'District',
            'childrens_corner_state' => 'State',
        ])->mapWithKeys(fn (string $label, string $key): array => [$key => data_get($post->meta, $key)])
            ->filter(fn (mixed $value): bool => filled($value))
        : collect();
    if ($post->isChildrensCornerPost() && $post->showsLimitedChildInformationTo(auth()->user())) {
        $childrensCornerLocation = collect();
    }
    $optionalStructuredLocation = $post->structuredLocationForDisplay()->isNotEmpty()
        && in_array($post->content_type, ['womens-world', 'student-corner', 'youth-corner', 'senior-citizens-forum'], true);
    $careerBusinessHubRail = in_array($post->content_type, ['career', 'jobs-employment', 'business'], true);
    $cultureSpiritualityHubRail = in_array($post->content_type, \App\Support\CommunityContentTaxonomy::cultureSpiritualityTypes(), true);
    $religionSpiritualityLocationText = collect([
        data_get($post->meta, 'religion_spirituality_location_city'),
        data_get($post->meta, 'religion_spirituality_location_district'),
        data_get($post->meta, 'religion_spirituality_location_state'),
        data_get($post->meta, 'religion_spirituality_location_country'),
    ])->filter(fn (mixed $value): bool => filled($value))->implode(', ');
    $religionSpiritualityGps = trim((string) data_get($post->meta, 'religion_spirituality_location_gps', ''));
    $religionSpiritualityMapLat = null;
    $religionSpiritualityMapLng = null;
    if ($post->isReligionSpiritualityPost() && filled($religionSpiritualityGps) && ! str_starts_with($religionSpiritualityGps, 'http')) {
        if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $religionSpiritualityGps, $religionSpiritualityGpsMatches)) {
            $religionSpiritualityMapLat = $religionSpiritualityGpsMatches[1];
            $religionSpiritualityMapLng = $religionSpiritualityGpsMatches[2];
        }
    }
    $religionSpiritualityMapUrl = filled($religionSpiritualityGps)
        ? (str_starts_with($religionSpiritualityGps, 'http')
            ? $religionSpiritualityGps
            : 'https://www.google.com/maps/search/?api=1&query='.urlencode($religionSpiritualityGps))
        : null;
    $hasReligionSpiritualityLocation = $post->isReligionSpiritualityPost()
        && (filled($religionSpiritualityLocationText) || filled($religionSpiritualityGps));
    $showStructuredLocation = (
        (\App\Models\CommunityPost::usesStructuredLocation($post->content_type)
            && ! in_array($post->content_type, ['awareness', 'business', 'local-voices', 'my-area', 'community-issues', 'agriculture', 'environment'], true))
        || $optionalStructuredLocation
        || ($careerBusinessHubRail && $post->content_type === 'business')
    ) && $post->structuredLocationForDisplay()->isNotEmpty();
    $showLocationType = $post->content_type !== 'poetry' && filled($post->location_type);
    $hasLocation = $showStructuredLocation
        || $showLocationType
        || $post->hasMapCoordinates()
        || $poetryRegionalLocation->isNotEmpty()
        || $childrensCornerLocation->isNotEmpty()
        || $hasReligionSpiritualityLocation;
    $railHubExtras = $railHubExtras ?? ($railLiteratureExtras ?? false);
    $suppressRailMeta = $railHubExtras && in_array($post->content_type, [
        'stories', 'poetry', 'autobiography',
        'childrens-corner', 'student-corner', 'youth-corner', 'senior-citizens-forum', 'womens-world', 'health-wellness',
    ], true);
    $hasAttachments = ! ($railLocationOnly ?? false) && ! $suppressRailMeta && is_array($attachments) && $attachments !== [];
    $railMetaHasDisplayValue = function (mixed $value): bool {
        if (is_array($value)) {
            return array_filter($value, fn (mixed $item): bool => filled($item)) !== [];
        }

        if (is_object($value)) {
            return false;
        }

        return filled($value) || $value === false;
    };
    $hasAdditionalDetails = ! ($railLocationOnly ?? false) && ! $suppressRailMeta && $visibleMeta
        ->except(array_merge(
            \App\Models\CommunityPost::structuredLocationMetaKeys(),
            ['location', 'location_lat', 'location_lng', 'issue_attachments', 'news_documents']
        ))
        ->filter($railMetaHasDisplayValue)
        ->isNotEmpty();
    $showReportTrustScore = $post->isReportContent() && $post->reportTrustBreakdown() !== [];
    $showLifeLearningRailSections = ($railHubExtras ?? false)
        && in_array($post->content_type, ['childrens-corner', 'student-corner', 'youth-corner', 'senior-citizens-forum', 'womens-world'], true);
    $lifeLearningHubRail = ($railHubExtras ?? false)
        && in_array($post->content_type, \App\Support\CommunityContentTaxonomy::lifeLearningTypes(), true);
    $showLifeLearningOverviewOnRail = $showLifeLearningRailSections;
    $locationInlineText = collect();
    if ($showStructuredLocation) {
        $locationInlineText = $post->structuredLocationForDisplay()->values();
    } elseif ($poetryRegionalLocation->isNotEmpty()) {
        $locationInlineText = $poetryRegionalLocation->values();
    } elseif ($childrensCornerLocation->isNotEmpty()) {
        $locationInlineText = $childrensCornerLocation->values();
    } elseif (filled($resolvedLocation)) {
        $locationInlineText = collect([$resolvedLocation]);
    }
    $locationInlineText = $locationInlineText->filter(fn (mixed $value): bool => filled($value))->implode(', ');
@endphp

@if($showReportTrustScore || $hasAttachments || $hasLocation || $hasAdditionalDetails || $showLifeLearningRailSections || $showLifeLearningOverviewOnRail)
    <div class="community-news-rail__detail-extras" aria-label="Post details sidebar">
        @if($showReportTrustScore)
            <div class="community-news-rail__card community-news-rail__card--detail">
                @include('community.partials.report-trust-score', ['post' => $post, 'compact' => true])
            </div>
        @endif

        @if($hasAttachments)
            <div class="community-news-rail__card community-news-rail__card--detail">
                <h3 class="community-news-rail__title">Attachments</h3>
                <div class="community-news-rail__attachments">
                    @foreach($attachments as $attachment)
                        <a href="{{ data_get($attachment, 'url') }}" target="_blank" rel="noopener" class="community-news-rail__attachment">
                            <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
                            <span>{{ data_get($attachment, 'name', 'Attachment') }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($showLifeLearningOverviewOnRail)
            @include('community.partials.life-learning-portal-sidebar-intro', [
                'post' => $post,
                'overviewRailLayout' => true,
            ])
        @endif

        @if($hasLocation)
            <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--location community-detail-card--rail">
                <div class="community-detail-card__head">
                    <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                    <div>
                        <h4 class="community-detail-card__title">Location</h4>
                    </div>
                </div>
                @if($lifeLearningHubRail || ($careerBusinessHubRail && $post->content_type === 'business'))
                    @if($post->hasMapCoordinates())
                        @include('community.partials.location-map-embed', ['post' => $post])
                    @endif
                    @if(filled($locationInlineText))
                        <p class="community-detail-location-inline mb-0">{{ $locationInlineText }}</p>
                    @elseif($showLocationType)
                        <span class="community-detail-location-type">
                            <i class="fa-solid fa-map-pin" aria-hidden="true"></i>
                            {{ $post->locationTypeLabel() }}
                        </span>
                        @if($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_GLOBAL)
                            <p class="community-detail-location-note mb-0">Global relevance.</p>
                        @elseif($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_INDIA)
                            <p class="community-detail-location-note mb-0">Applies across India.</p>
                        @endif
                    @endif
                @elseif($cultureSpiritualityHubRail && $post->isReligionSpiritualityPost() && $hasReligionSpiritualityLocation)
                    @if(filled($religionSpiritualityMapLat) && filled($religionSpiritualityMapLng))
                        @include('community.partials.location-map-embed', [
                            'post' => $post,
                            'lat' => $religionSpiritualityMapLat,
                            'lng' => $religionSpiritualityMapLng,
                        ])
                    @elseif($post->hasMapCoordinates())
                        @include('community.partials.location-map-embed', ['post' => $post])
                    @endif
                    @if(filled($religionSpiritualityLocationText))
                        <p class="community-detail-location-inline mb-0">{{ $religionSpiritualityLocationText }}</p>
                    @endif
                    @if(filled($religionSpiritualityMapUrl))
                        <a href="{{ $religionSpiritualityMapUrl }}" class="btn btn-sm btn-outline-primary mt-2" target="_blank" rel="noopener">
                            <i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i>Open map
                        </a>
                    @endif
                @else
                @if($showStructuredLocation)
                    <div class="community-detail-grid community-detail-grid--rail">
                        @foreach($post->structuredLocationForDisplay() as $key => $value)
                            <div class="community-detail-item">
                                <span class="community-detail-item__label">{{ \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type)[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                                <span class="community-detail-item__value">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                @elseif($showLocationType)
                    <span class="community-detail-location-type">
                        <i class="fa-solid fa-map-pin" aria-hidden="true"></i>
                        {{ $post->locationTypeLabel() }}
                    </span>
                    @if($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_GLOBAL)
                        <p class="community-detail-location-note mb-0">Global relevance.</p>
                    @elseif($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_INDIA)
                        <p class="community-detail-location-note mb-0">Applies across India.</p>
                    @elseif($post->usesGpsLocation() && ! $post->hasMapCoordinates())
                        <p class="community-detail-location-note mb-0">No map location provided.</p>
                    @elseif($post->requiresSpecificLocation() && filled($resolvedLocation))
                        <p class="community-detail-location-note mb-0">{{ $resolvedLocation }}</p>
                    @elseif(filled($resolvedLocation))
                        <p class="community-detail-location-note mb-0">{{ $resolvedLocation }}</p>
                    @endif
                @elseif($poetryRegionalLocation->isNotEmpty() || $childrensCornerLocation->isNotEmpty())
                    <div class="community-detail-grid community-detail-grid--rail">
                        @foreach(($poetryRegionalLocation->isNotEmpty() ? $poetryRegionalLocation : $childrensCornerLocation) as $key => $value)
                            <div class="community-detail-item">
                                <span class="community-detail-item__label">{{ $poetryRegionalLocation->isNotEmpty()
                                    ? (\App\Support\CommunityPostFormFields::poetryRegionalLocationOrder()[$key] ?? \Illuminate\Support\Str::headline($key))
                                    : (match ($key) {
                                        'childrens_corner_city' => 'City',
                                        'childrens_corner_district' => 'District',
                                        'childrens_corner_state' => 'State',
                                        default => \Illuminate\Support\Str::headline($key),
                                    }) }}</span>
                                <span class="community-detail-item__value">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if($post->hasMapCoordinates())
                    @include('community.partials.location-map-embed', ['post' => $post])
                @endif
                @endif
            </div>
        @endif

        @if($hasAdditionalDetails)
            <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--meta community-detail-card--rail">
                <div class="community-detail-card__head">
                    <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-circle-info"></i></span>
                    <div>
                        <h4 class="community-detail-card__title">Additional details</h4>
                    </div>
                </div>
                <dl class="community-detail-list community-detail-list--rail mb-0">
                    @foreach($visibleMeta->except(array_merge(
                        \App\Models\CommunityPost::structuredLocationMetaKeys(),
                        ['location', 'location_lat', 'location_lng', 'issue_attachments', 'news_documents']
                    )) as $key => $value)
                        @if(is_object($value))
                            @continue
                        @endif
                        @if(is_array($value))
                            @php
                                $value = implode(', ', array_values(array_filter($value, fn (mixed $item): bool => filled($item))));
                            @endphp
                        @endif
                        @if(blank($value) && $value !== false)
                            @continue
                        @endif
                        <div class="community-detail-list__row">
                            <dt>{{ $formFieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                            <dd>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif

        @if($showLifeLearningRailSections)
            @include('community.partials.life-learning-portal-rail-sections', ['post' => $post])
        @endif
    </div>
@endif
