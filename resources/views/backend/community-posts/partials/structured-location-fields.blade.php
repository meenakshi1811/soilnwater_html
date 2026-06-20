@php
    $locationCountry = old('location_country', data_get($post->meta, 'location_country'));
    $locationState = old('location_state', data_get($post->meta, 'location_state'));
    $locationDistrict = old('location_district', data_get($post->meta, 'location_district'));
    $locationCity = old('location_city', data_get($post->meta, 'location_city'));
    $locationLocality = old('location_locality', data_get($post->meta, 'location_locality'));
@endphp

<div class="community-structured-location">
    <div class="community-structured-location__search mb-3">
        <label class="form-label" for="communityStructuredLocationSearch">Search place</label>
        <input
            type="text"
            id="communityStructuredLocationSearch"
            class="form-control"
            maxlength="255"
            placeholder="Search Google Places to auto-fill location fields"
            autocomplete="off"
        >
        <small class="text-muted d-block mt-1">Pick a suggestion to populate country, state, district, city, and locality.</small>
    </div>

    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="communityLocationCountry">Country <span class="text-danger">*</span></label>
            <input
                type="text"
                name="location_country"
                id="communityLocationCountry"
                class="form-control structured-location-required"
                value="{{ $locationCountry }}"
                maxlength="120"
                placeholder="e.g. India"
            >
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="communityLocationState">State <span class="text-danger">*</span></label>
            <input
                type="text"
                name="location_state"
                id="communityLocationState"
                class="form-control structured-location-required"
                value="{{ $locationState }}"
                maxlength="120"
                placeholder="e.g. Rajasthan"
            >
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="communityLocationDistrict">District <span class="text-danger">*</span></label>
            <input
                type="text"
                name="location_district"
                id="communityLocationDistrict"
                class="form-control structured-location-required"
                value="{{ $locationDistrict }}"
                maxlength="120"
                placeholder="e.g. Jaipur"
            >
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="communityLocationCity">City <span class="text-danger">*</span></label>
            <input
                type="text"
                name="location_city"
                id="communityLocationCity"
                class="form-control structured-location-required"
                value="{{ $locationCity }}"
                maxlength="120"
                placeholder="e.g. Jaipur"
            >
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="communityLocationLocality" id="communityLocationLocalityLabel">Locality</label>
            <input
                type="text"
                name="location_locality"
                id="communityLocationLocality"
                class="form-control structured-location-locality"
                value="{{ $locationLocality }}"
                maxlength="120"
                placeholder="e.g. Malviya Nagar"
            >
        </div>
    </div>

    <div class="community-structured-location__map mt-4 pt-3 border-top">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
            <div>
                <h6 class="mb-1">Map location</h6>
                <p class="text-muted small mb-0">Optional GPS coordinates</p>
            </div>
            <span class="badge bg-light text-dark border">Optional</span>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="communityLocationLat">Latitude</label>
                <input
                    type="number"
                    name="location_lat"
                    id="communityLocationLat"
                    class="form-control"
                    value="{{ old('location_lat', $post->location_lat ?? data_get($post->meta, 'location_lat')) }}"
                    step="any"
                    min="-90"
                    max="90"
                    placeholder="e.g. 26.9124000"
                >
            </div>
            <div class="col-md-4">
                <label class="form-label" for="communityLocationLng">Longitude</label>
                <input
                    type="number"
                    name="location_lng"
                    id="communityLocationLng"
                    class="form-control"
                    value="{{ old('location_lng', $post->location_lng ?? data_get($post->meta, 'location_lng')) }}"
                    step="any"
                    min="-180"
                    max="180"
                    placeholder="e.g. 75.7873000"
                >
            </div>
            <div class="col-12">
                <label class="form-label">Map marker</label>
                @if(!config('services.google.maps_api_key'))
                    <div class="alert alert-warning py-2 px-3 mb-2 small">Google Maps is not configured. Add <code>GOOGLE_MAPS_API_KEY</code> to your environment file to enable the map pin.</div>
                @endif
                <div id="communityGpsMap" class="community-gps-map border rounded bg-white" role="application" aria-label="Optional GPS map pin"></div>
                <small class="text-muted d-block mt-2">Click the map to place a pin, drag it to adjust, or enter coordinates manually.</small>
            </div>
        </div>
    </div>
</div>
