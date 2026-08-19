@php
    $mapLat = $lat ?? ($post->location_lat ?? null);
    $mapLng = $lng ?? ($post->location_lng ?? null);
    $mapTitle = $title ?? 'Post location map';
    $mapZoom = $zoom ?? 15;
@endphp

@if(filled($mapLat) && filled($mapLng))
    <div @class(['community-detail-map ratio ratio-16x9', $wrapperClass ?? null])>
        <iframe
            title="{{ $mapTitle }}"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            src="https://maps.google.com/maps?q={{ $mapLat }},{{ $mapLng }}&z={{ $mapZoom }}&hl=en&output=embed"
            allowfullscreen
        ></iframe>
    </div>
@endif
