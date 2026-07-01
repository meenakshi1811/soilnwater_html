@if($post->isReligionSpiritualityPost())
    @php
        $uniqueFeatures = $post->religionSpiritualityUniqueFeatureLabels();
    @endphp

    @if($uniqueFeatures !== [])
        <div class="rs-flagship-strip">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-2">
                <div>
                    <div class="rs-flagship-strip__title">SoilnWater unique features</div>
                    <p class="text-muted small mb-0">Flagship Religion &amp; Spirituality programs on SoilnWater.</p>
                </div>
                <span class="rs-unique-feature-badge">SoilnWater</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($uniqueFeatures as $feature)
                    <span class="badge bg-white text-dark border px-3 py-2">{{ $feature }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(data_get($post->meta, 'religion_spirituality_enable_digital_pilgrimage_guide'))
        <div class="rs-flagship-banner rs-flagship-banner--pilgrimage">
            <div class="rs-flagship-banner__heading">
                <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>Digital Pilgrimage Guide
            </div>
            @if(filled(data_get($post->meta, 'religion_spirituality_digital_pilgrimage_site_name')))
                <p class="mb-2 fw-semibold">{{ data_get($post->meta, 'religion_spirituality_digital_pilgrimage_site_name') }}</p>
            @endif
            @php $siteTypes = (array) data_get($post->meta, 'religion_spirituality_digital_pilgrimage_site_types', []); @endphp
            @if($siteTypes !== [])
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($siteTypes as $siteType)
                        <span class="badge bg-white text-dark border px-3 py-2">{{ $siteType }}</span>
                    @endforeach
                </div>
            @endif
            @foreach([
                'Verified information' => data_get($post->meta, 'religion_spirituality_digital_pilgrimage_verified_info'),
                'Nearby facilities' => data_get($post->meta, 'religion_spirituality_digital_pilgrimage_nearby_facilities'),
                'Accommodation' => data_get($post->meta, 'religion_spirituality_digital_pilgrimage_accommodation'),
                'Local businesses on SoilnWater' => data_get($post->meta, 'religion_spirituality_digital_pilgrimage_local_businesses'),
            ] as $label => $value)
                @if(filled($value))
                    <p class="small mb-2"><span class="text-muted">{{ $label }}:</span> {{ $value }}</p>
                @endif
            @endforeach
            @if(filled(data_get($post->meta, 'religion_spirituality_digital_pilgrimage_map_url')))
                <a href="{{ data_get($post->meta, 'religion_spirituality_digital_pilgrimage_map_url') }}" class="btn btn-sm btn-outline-primary mt-1" target="_blank" rel="noopener">
                    <i class="fa-solid fa-map me-1" aria-hidden="true"></i>View on map
                </a>
            @endif
        </div>
    @endif

    @if(data_get($post->meta, 'religion_spirituality_enable_festival_calendar'))
        <div class="rs-flagship-banner rs-flagship-banner--festival">
            <div class="rs-flagship-banner__heading">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>Festival Calendar
            </div>
            @php $eventTypes = (array) data_get($post->meta, 'religion_spirituality_festival_calendar_event_types', []); @endphp
            @if($eventTypes !== [])
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($eventTypes as $eventType)
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">{{ $eventType }}</span>
                    @endforeach
                </div>
            @endif
            @if(filled(data_get($post->meta, 'religion_spirituality_festival_calendar_event_name')))
                <p class="mb-1 fw-semibold">{{ data_get($post->meta, 'religion_spirituality_festival_calendar_event_name') }}</p>
            @endif
            @if(filled(data_get($post->meta, 'religion_spirituality_festival_calendar_event_date')))
                <p class="small text-muted mb-2">{{ \Illuminate\Support\Carbon::parse(data_get($post->meta, 'religion_spirituality_festival_calendar_event_date'))->format('F j, Y') }}</p>
            @endif
            @if(filled(data_get($post->meta, 'religion_spirituality_festival_calendar_description')))
                <p class="small mb-2">{{ data_get($post->meta, 'religion_spirituality_festival_calendar_description') }}</p>
            @endif
            @if(filled(data_get($post->meta, 'religion_spirituality_festival_calendar_linked_article_url')))
                <a href="{{ data_get($post->meta, 'religion_spirituality_festival_calendar_linked_article_url') }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                    <i class="fa-solid fa-book-open me-1" aria-hidden="true"></i>Read educational article
                </a>
            @endif
        </div>
    @endif

    @if(data_get($post->meta, 'religion_spirituality_enable_community_service_directory'))
        <div class="rs-flagship-banner rs-flagship-banner--service">
            <div class="rs-flagship-banner__heading">
                <i class="fa-solid fa-hands-holding-heart" aria-hidden="true"></i>Community Service Directory
            </div>
            @php $opportunities = (array) data_get($post->meta, 'religion_spirituality_service_directory_opportunities', []); @endphp
            @if($opportunities !== [])
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($opportunities as $opportunity)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">{{ $opportunity }}</span>
                    @endforeach
                </div>
            @endif
            @if(filled(data_get($post->meta, 'religion_spirituality_service_directory_organization')))
                <p class="mb-1"><strong>Organization:</strong> {{ data_get($post->meta, 'religion_spirituality_service_directory_organization') }}</p>
            @endif
            @if(filled(data_get($post->meta, 'religion_spirituality_service_directory_when_where')))
                <p class="mb-1"><strong>When &amp; where:</strong> {{ data_get($post->meta, 'religion_spirituality_service_directory_when_where') }}</p>
            @endif
            @if(filled(data_get($post->meta, 'religion_spirituality_service_directory_volunteer_notes')))
                <p class="small text-muted mb-0">{{ data_get($post->meta, 'religion_spirituality_service_directory_volunteer_notes') }}</p>
            @endif
        </div>
    @endif

    @if(data_get($post->meta, 'religion_spirituality_enable_wisdom_library'))
        <div class="rs-flagship-banner rs-flagship-banner--wisdom">
            <div class="rs-flagship-banner__heading">
                <i class="fa-solid fa-book" aria-hidden="true"></i>Wisdom Library
            </div>
            @php
                $wisdomThemes = (array) data_get($post->meta, 'religion_spirituality_wisdom_themes', []);
                $wisdomTraditions = (array) data_get($post->meta, 'religion_spirituality_wisdom_traditions', []);
            @endphp
            @if($wisdomThemes !== [])
                <p class="small text-muted mb-1">Universal themes</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($wisdomThemes as $theme)
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2">{{ $theme }}</span>
                    @endforeach
                </div>
            @endif
            @if($wisdomTraditions !== [])
                <p class="small text-muted mb-1">Traditions covered</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($wisdomTraditions as $tradition)
                        <span class="badge bg-white text-dark border px-3 py-2">{{ $tradition }}</span>
                    @endforeach
                </div>
            @endif
            @if(filled(data_get($post->meta, 'religion_spirituality_wisdom_collection_summary')))
                <p class="small mb-0">{{ data_get($post->meta, 'religion_spirituality_wisdom_collection_summary') }}</p>
            @endif
        </div>
    @endif
@endif
