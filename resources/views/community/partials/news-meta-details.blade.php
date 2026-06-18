@php
    $orderedNewsMeta = \App\Support\CommunityPostFormFields::orderedNewsMetaForDisplay($post);
    $newsMetaLabels = \App\Support\CommunityPostFormFields::newsDetailMetaOrder();
    $narrativeKeys = \App\Support\CommunityPostFormFields::narrativeNewsMetaKeys();
    $newsDocuments = data_get($post->meta, 'news_documents', []);
    $structuredLocation = $post->structuredLocationForDisplay();
    $showSubmissionDate = $post->submitted_at || $post->created_at;
    $hasContent = $orderedNewsMeta->isNotEmpty()
        || $structuredLocation->isNotEmpty()
        || $showSubmissionDate
        || ! empty($newsDocuments);
@endphp

@if($hasContent)
    <div class="about-box mt-4">
        <h4>{{ $heading ?? 'News details' }}</h4>

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

        @if($structuredLocation->isNotEmpty())
            <div class="row g-3 {{ ($orderedNewsMeta->isNotEmpty() || $showSubmissionDate || ! empty($newsDocuments)) ? 'mb-3' : '' }}">
                @foreach($structuredLocation as $key => $value)
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">{{ \App\Models\CommunityPost::structuredLocationLabels()[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($orderedNewsMeta->isNotEmpty() || $showSubmissionDate)
            <div class="row g-3 {{ ! empty($newsDocuments) ? 'mb-3' : '' }}">
                @foreach($orderedNewsMeta as $key => $value)
                    @php
                        $displayValue = \App\Support\CommunityPostFormFields::formatNewsMetaValue($key, $value);
                        $isNarrativeField = in_array($key, $narrativeKeys, true);
                    @endphp
                    <div class="{{ $isNarrativeField ? 'col-12' : 'col-md-6' }}">
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
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">News submission date</strong>
                            <span>{{ optional($post->submitted_at ?? $post->created_at)->timezone(config('app.timezone'))->format('j F Y, g:i A') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if($post->hasMapCoordinates())
            <div class="{{ ! empty($newsDocuments) ? 'mb-3' : '' }}">
                <p class="mb-2"><strong>Map location:</strong> {{ $post->location_lat }}, {{ $post->location_lng }}</p>
                <div class="ratio ratio-16x9 border rounded overflow-hidden">
                    <iframe
                        title="News GPS location map"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $post->location_lng - 0.02 }},{{ $post->location_lat - 0.02 }},{{ $post->location_lng + 0.02 }},{{ $post->location_lat + 0.02 }}&layer=mapnik&marker={{ $post->location_lat }},{{ $post->location_lng }}"
                    ></iframe>
                </div>
            </div>
        @endif

        @if(! empty($newsDocuments))
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
