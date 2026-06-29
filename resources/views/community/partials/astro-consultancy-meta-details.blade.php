@php
    $metaLabels = \App\Support\CommunityPostFormFields::astroConsultancyDetailMetaOrder();
    $skipKeys = [
        'astro_consultancy_post_type',
        'astro_consultancy_category',
        'astro_consultancy_consultation_topics',
        'astro_consultancy_ask_community',
    ];
    $orderedMeta = collect($metaLabels)
        ->reject(fn ($label, $key) => in_array($key, $skipKeys, true))
        ->mapWithKeys(fn ($label, $key) => [$key => data_get($post->meta, $key)])
        ->filter(fn ($value) => filled($value) || is_bool($value));
    $pillKeys = [
        'astro_consultancy_target_audience',
        'astro_consultancy_consultation_topics',
        'astro_consultancy_vastu_property_types',
        'astro_consultancy_vastu_areas',
        'astro_consultancy_document_types',
        'astro_consultancy_related_service_actions',
        'astro_consultancy_knowledge_library_topics',
        'astro_consultancy_private_query_options',
        'astro_consultancy_comment_settings',
        'astro_consultancy_poll_options',
    ];
    $textareaKeys = [
        'astro_consultancy_gemstone_benefits',
        'astro_consultancy_gemstone_precautions',
        'astro_consultancy_festival_significance',
        'astro_consultancy_ask_community',
    ];
@endphp

@if($post->isAstroConsultancyPost() && ($orderedMeta->isNotEmpty() || ($includeAdmin ?? false)))
    <div class="about-box mt-4 business-meta-grid chart-card p-3 p-lg-4">
        <h4>{{ $heading ?? (($includeAdmin ?? false) ? 'Saved Astro Consultancy metadata' : 'Astro Consultancy details') }}</h4>

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
                            @elseif($key === 'astro_consultancy_consultant_profile_url' && filled($value))
                                <a href="{{ $value }}" target="_blank" rel="noopener">{{ $value }}</a>
                            @else
                                <span>{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
