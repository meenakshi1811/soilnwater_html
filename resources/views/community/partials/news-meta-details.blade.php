@php
    $orderedNewsMeta = \App\Support\CommunityPostFormFields::orderedNewsMetaForDisplay($post);
    $newsMetaLabels = \App\Support\CommunityPostFormFields::newsDetailMetaOrder();
    $narrativeKeys = \App\Support\CommunityPostFormFields::narrativeNewsMetaKeys();
    $newsDocuments = data_get($post->meta, 'news_documents', []);
    $structuredLocation = $post->structuredLocationForDisplay();
    $showSubmissionDate = $post->submitted_at || $post->created_at;
    $sidebarLayout = $sidebarLayout ?? false;
    $includeMap = $includeMap ?? ! $sidebarLayout;
    $includeLocation = $includeLocation ?? true;

    if ($sidebarLayout) {
        $orderedNewsMeta = $orderedNewsMeta->reject(
            fn (mixed $value, string $key): bool => in_array($key, $narrativeKeys, true)
        );
    }

    $hasContent = $orderedNewsMeta->isNotEmpty()
        || ($includeLocation && $structuredLocation->isNotEmpty())
        || $showSubmissionDate
        || (! $sidebarLayout && ! empty($newsDocuments));
@endphp

@if($hasContent)
    <div @class([
        'about-box mt-4' => ! $sidebarLayout,
        'community-news-sidebar__card community-news-sidebar__card--news-details' => $sidebarLayout,
    ])>
        @if($sidebarLayout)
            <p class="community-news-sidebar__label">{{ $heading ?? 'News details' }}</p>
        @else
            <h4>{{ $heading ?? 'News details' }}</h4>
        @endif

        @if(filled($post->location_type))
            <div class="mb-3">
                <span class="badge bg-primary-subtle text-primary border">{{ $post->geographicCoverageLabel() }}</span>
                @if(filled(data_get($post->meta, 'news_priority')))
                    <span class="badge bg-warning text-dark border">{{ data_get($post->meta, 'news_priority') }} priority</span>
                @endif
                @if(filled(data_get($post->meta, 'news_impact_level')))
                    <span class="badge bg-danger-subtle text-danger border">{{ data_get($post->meta, 'news_impact_level') }} impact</span>
                @endif
                @if(filled(data_get($post->meta, 'news_affected_group')))
                    <span class="badge bg-info-subtle text-info-emphasis border">Affects: {{ data_get($post->meta, 'news_affected_group') }}</span>
                @endif
            </div>
        @endif

        @if($includeLocation && $structuredLocation->isNotEmpty())
            <div @class([
                'row g-3' => ! $sidebarLayout,
                'news-sidebar-meta-grid' => $sidebarLayout,
                'mb-3' => $orderedNewsMeta->isNotEmpty() || $showSubmissionDate || ($includeMap && $post->hasMapCoordinates()) || (! $sidebarLayout && ! empty($newsDocuments)),
            ])>
                @foreach($structuredLocation as $key => $value)
                    <div @class([
                        'col-md-6' => ! $sidebarLayout,
                        'news-sidebar-meta-grid__item' => $sidebarLayout,
                    ])>
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">{{ \App\Models\CommunityPost::structuredLocationLabels()[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($orderedNewsMeta->isNotEmpty() || $showSubmissionDate)
            <div @class([
                'row g-3' => ! $sidebarLayout,
                'news-sidebar-meta-grid' => $sidebarLayout,
                'mb-3' => ($includeMap && $post->hasMapCoordinates()) || (! $sidebarLayout && ! empty($newsDocuments)),
            ])>
                @foreach($orderedNewsMeta as $key => $value)
                    @php
                        $displayValue = \App\Support\CommunityPostFormFields::formatNewsMetaValue($key, $value);
                        $isNarrativeField = in_array($key, $narrativeKeys, true);
                    @endphp
                    <div @class([
                        $isNarrativeField ? 'col-12' : 'col-md-6' => ! $sidebarLayout,
                        'news-sidebar-meta-grid__item' => $sidebarLayout,
                        'news-sidebar-meta-grid__item--wide' => $sidebarLayout && $isNarrativeField,
                    ])>
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">{{ $newsMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                            @if($key === 'source_url')
                                <a href="{{ $value }}" target="_blank" rel="noopener">{{ $value }}</a>
                            @else
                                <span>{!! nl2br(e($displayValue)) !!}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
                @if($showSubmissionDate)
                    <div @class([
                        'col-md-6' => ! $sidebarLayout,
                        'news-sidebar-meta-grid__item' => $sidebarLayout,
                    ])>
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">News submission date</strong>
                            <span>{{ optional($post->submitted_at ?? $post->created_at)->timezone(config('app.timezone'))->format('j F Y, g:i A') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if($includeMap && $post->hasMapCoordinates())
            <div class="{{ ! empty($newsDocuments) && ! $sidebarLayout ? 'mb-3' : '' }}">
                @unless($sidebarLayout)
                    <p class="mb-2"><strong>Map location:</strong> {{ $post->location_lat }}, {{ $post->location_lng }}</p>
                @endunless
                @include('community.partials.location-map-embed', [
                    'post' => $post,
                    'title' => 'News location map',
                ])
            </div>
        @endif

        @if(! $sidebarLayout && ! empty($newsDocuments))
            <h5 class="h6">Documents</h5>
            <div class="d-flex flex-wrap gap-2">
                @foreach($newsDocuments as $document)
                    <a href="{{ data_get($document, 'url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-file-lines me-1"></i>{{ data_get($document, 'name', 'Document') }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endif
