@php
    $metaLabels = \App\Support\CommunityPostFormFields::religionSpiritualityDetailMetaOrder();
    $skipKeys = [
        'religion_spirituality_post_type',
        'religion_spirituality_category',
        'religion_spirituality_tradition',
        'religion_spirituality_ask_community',
    ];
    $orderedMeta = collect($metaLabels)
        ->reject(fn ($label, $key) => in_array($key, $skipKeys, true))
        ->mapWithKeys(fn ($label, $key) => [$key => data_get($post->meta, $key)])
        ->filter(fn ($value) => filled($value) || is_bool($value));
    $pillKeys = [
        'religion_spirituality_target_audience',
        'religion_spirituality_moral_messages',
        'religion_spirituality_meditation_topics',
        'religion_spirituality_community_service_activities',
        'religion_spirituality_document_types',
        'religion_spirituality_digital_pilgrimage_site_types',
        'religion_spirituality_festival_calendar_event_types',
        'religion_spirituality_service_directory_opportunities',
        'religion_spirituality_wisdom_themes',
        'religion_spirituality_wisdom_traditions',
        'religion_spirituality_related_service_actions',
        'religion_spirituality_comment_settings',
        'religion_spirituality_poll_options',
    ];
    $textareaKeys = [
        'religion_spirituality_festival_historical_significance',
        'religion_spirituality_festival_traditional_practices',
        'religion_spirituality_festival_celebration_methods',
        'religion_spirituality_festival_regional_variations',
        'religion_spirituality_pilgrimage_history',
        'religion_spirituality_pilgrimage_facilities',
        'religion_spirituality_pilgrimage_travel_tips',
        'religion_spirituality_pilgrimage_accommodation',
        'religion_spirituality_digital_pilgrimage_verified_info',
        'religion_spirituality_digital_pilgrimage_nearby_facilities',
        'religion_spirituality_digital_pilgrimage_accommodation',
        'religion_spirituality_digital_pilgrimage_local_businesses',
        'religion_spirituality_festival_calendar_description',
        'religion_spirituality_service_directory_volunteer_notes',
        'religion_spirituality_wisdom_collection_summary',
    ];
    $urlKeys = [
        'religion_spirituality_location_gps',
        'religion_spirituality_digital_pilgrimage_map_url',
        'religion_spirituality_festival_calendar_linked_article_url',
    ];
    $flagKeys = [
        'religion_spirituality_enable_digital_pilgrimage_guide',
        'religion_spirituality_enable_festival_calendar',
        'religion_spirituality_enable_community_service_directory',
        'religion_spirituality_enable_wisdom_library',
    ];
@endphp

@if($post->isReligionSpiritualityPost() && ($orderedMeta->isNotEmpty() || ($includeAdmin ?? false)))
    <div class="about-box mt-4 business-meta-grid chart-card p-3 p-lg-4">
        <h4>{{ $heading ?? (($includeAdmin ?? false) ? 'Saved Religion & Spirituality metadata' : 'Religion & Spirituality details') }}</h4>

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
                            @elseif(in_array($key, $urlKeys, true) && filled($value))
                                <a href="{{ $value }}" target="_blank" rel="noopener">{{ $value }}</a>
                            @elseif($key === 'religion_spirituality_festival_calendar_event_date' && filled($value))
                                <span>{{ \Illuminate\Support\Carbon::parse($value)->format('F j, Y') }}</span>
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
