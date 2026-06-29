@php
    $metaLabels = \App\Support\CommunityPostFormFields::environmentDetailMetaOrder();
    $orderedMeta = collect($metaLabels)
        ->mapWithKeys(fn ($label, $key) => [$key => data_get($post->meta, $key)])
        ->filter(fn ($value) => filled($value) || is_bool($value));
    $pillKeys = [
        'environment_soil_conservation_methods',
        'environment_waste_types',
        'environment_biodiversity_types',
        'environment_climate_impacts',
        'environment_participation_requests',
        'environment_poll_options',
    ];
    $textareaKeys = [
        'environment_tree_maintenance_plan',
        'environment_scheme_eligibility',
        'environment_scheme_benefits',
        'environment_ask_community',
    ];
    $dateKeys = [
        'environment_tree_plantation_date',
        'environment_event_date',
    ];
@endphp

@if($post->isEnvironmentPost() && ($orderedMeta->isNotEmpty() || ($includeAdmin ?? false)))
    <div class="about-box mt-4 business-meta-grid chart-card p-3 p-lg-4">
        <h4>{{ $heading ?? (($includeAdmin ?? false) ? 'Saved Environment metadata' : 'Environment details') }}</h4>

        @if($includeAdmin ?? false)
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Publish as</span>
                        <span>{{ $post->publishAsLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Tags</span>
                        <span>{{ !empty($post->tags) ? implode(', ', $post->tags) : '—' }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Engagement</span>
                        <span>
                            Comments {{ $post->allow_comments ? 'on' : 'off' }} ·
                            Poll {{ $post->allow_poll ? 'on' : 'off' }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        @if($orderedMeta->isNotEmpty())
            <div class="row g-3">
                @foreach($orderedMeta as $key => $value)
                    <div class="col-md-6">
                        <div class="business-meta-item h-100">
                            <span class="business-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            @if(in_array($key, $pillKeys, true) && is_array($value))
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($value as $item)
                                        <span class="badge bg-light text-dark border">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @elseif(in_array($key, $textareaKeys, true))
                                <span>{!! nl2br(e((string) $value)) !!}</span>
                            @elseif(is_bool($value))
                                <span>{{ $value ? 'Enabled' : 'Disabled' }}</span>
                            @elseif(in_array($key, ['environment_scheme_official_link', 'environment_event_registration_link'], true))
                                <a href="{{ $value }}" target="_blank" rel="noopener">{{ $value }}</a>
                            @elseif(in_array($key, $dateKeys, true) && filled($value))
                                <span>{{ \Illuminate\Support\Carbon::parse($value)->format('d M Y') }}</span>
                            @else
                                <span>{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($post->environmentGalleryItemsForDisplay() !== [] || $post->environmentDocuments() !== [])
            <div class="mt-4">
                <h5 class="h6 mb-3">Uploaded environment files</h5>
                @if($post->environmentGalleryItemsForDisplay() !== [])
                    <div class="business-gallery-grid mb-3">
                        @foreach($post->environmentGalleryItemsForDisplay() as $item)
                            <a href="{{ data_get($item['photo'], 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                                <img src="{{ data_get($item['photo'], 'url') }}" alt="{{ $item['label'] }}" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                @endif
                @if($post->environmentDocuments() !== [])
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->environmentDocuments() as $document)
                            <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">{{ data_get($document, 'name', 'Document') }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
