@extends('frontend.layouts.app')

@section('meta_title', 'Community Issues Hub | SoilnWater')
@section('meta_description', 'Explore the SoilnWater Community Issues heat map, civic dashboard, community champions, and geographically mapped water, road, and pollution reports.')
@section('meta_url', route('community.community-issues.index'))
@section('meta_canonical', route('community.community-issues.index'))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    .ci-hero {
        background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 40%, #dc2626 100%);
        color: #fff;
        padding: clamp(48px, 6vw, 72px) 24px;
    }
    .ci-hero__inner { max-width: min(1720px, calc(100vw - 48px)); margin: 0 auto; }
    .ci-hero__title { font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; margin-bottom: 0.75rem; }
    .ci-hero__text { max-width: 820px; opacity: 0.92; margin-bottom: 1.5rem; }
    .ci-shell { max-width: min(1720px, calc(100vw - 48px)); margin: 0 auto; padding: 2rem 24px 4rem; }
    .ci-breadcrumb a { color: #b91c1c; text-decoration: none; font-weight: 600; }
    .ci-breadcrumb a:hover { text-decoration: underline; }
    .ci-dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .ci-stat-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 8px 24px rgba(15, 47, 85, 0.06);
        border: 1px solid rgba(15, 47, 85, 0.06);
    }
    .ci-stat-card__value { font-size: 1.75rem; font-weight: 800; color: #0f2f55; line-height: 1.1; }
    .ci-stat-card__label { font-size: 0.85rem; color: #6c757d; margin-top: 0.35rem; }
    .ci-panel {
        background: #fff;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 8px 24px rgba(15, 47, 85, 0.06);
        margin-bottom: 2rem;
    }
    .ci-panel__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .ci-panel__title { font-size: 1.25rem; font-weight: 700; margin: 0; }
    .ci-map-tabs { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .ci-map-tab {
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #212529;
        border-radius: 999px;
        padding: 0.4rem 0.9rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .ci-map-tab.is-active,
    .ci-map-tab:hover {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }
    #communityIssuesHeatMap {
        height: 420px;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid #dee2e6;
    }
    .ci-champions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
    }
    .ci-champion-card {
        border: 1px solid #e9ecef;
        border-radius: 0.9rem;
        padding: 1rem;
        background: linear-gradient(180deg, #fff 0%, #f8f9fa 100%);
    }
    .ci-champion-card__avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        background: #dee2e6;
    }
    .ci-champion-badges { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.75rem; }
    .ci-filters { background: #fff; border-radius: 1rem; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 8px 24px rgba(15, 47, 85, 0.06); }
    .ci-unique-kicker {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.85;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<section class="ci-hero">
    <div class="ci-hero__inner">
        <p class="ci-unique-kicker">Unique SoilnWater Features</p>
        <h1 class="ci-hero__title">Community Issues Hub</h1>
        <p class="ci-hero__text">
            See civic problems on a geographic heat map, track community-wide resolution progress,
            and celebrate residents who report, verify, and help solve local issues.
        </p>
        @auth
            <a href="{{ route('community.posts.create', ['type' => 'community-issues']) }}" class="btn btn-light btn-lg">Report an issue</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-light btn-lg">Login to report an issue</a>
        @endauth
    </div>
</section>

<div class="ci-shell">
    <nav class="ci-breadcrumb mb-3" aria-label="Breadcrumb">
        <a href="{{ route('community.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Community Hub</a>
        <span class="text-muted mx-2">/</span>
        <span class="text-muted">Community Issues</span>
    </nav>

    <section aria-labelledby="communityDashboardHeading">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <p class="ci-unique-kicker text-danger mb-1">Community Dashboard</p>
                <h2 id="communityDashboardHeading" class="h4 mb-0">Civic impact at a glance</h2>
            </div>
        </div>
        <div class="ci-dashboard-grid">
            <div class="ci-stat-card">
                <div class="ci-stat-card__value">{{ number_format($dashboard['total_reported']) }}</div>
                <div class="ci-stat-card__label">Total issues reported</div>
            </div>
            <div class="ci-stat-card">
                <div class="ci-stat-card__value text-success">{{ number_format($dashboard['issues_resolved']) }}</div>
                <div class="ci-stat-card__label">Issues resolved</div>
            </div>
            <div class="ci-stat-card">
                <div class="ci-stat-card__value text-warning">{{ number_format($dashboard['issues_under_review']) }}</div>
                <div class="ci-stat-card__label">Issues under review</div>
            </div>
            <div class="ci-stat-card">
                <div class="ci-stat-card__value text-primary">{{ number_format($dashboard['community_support_count']) }}</div>
                <div class="ci-stat-card__label">Community support count</div>
            </div>
            <div class="ci-stat-card">
                <div class="ci-stat-card__value">{{ number_format($dashboard['authority_response_rate'], 1) }}%</div>
                <div class="ci-stat-card__label">Authority response rate</div>
                @if($dashboard['authority_eligible'] > 0)
                    <small class="text-muted d-block mt-1">{{ number_format($dashboard['authority_responded']) }} of {{ number_format($dashboard['authority_eligible']) }} forwarded issues acknowledged or beyond</small>
                @endif
            </div>
        </div>
    </section>

    <section class="ci-panel" aria-labelledby="issueHeatMapHeading">
        <div class="ci-panel__header">
            <div>
                <p class="ci-unique-kicker text-danger mb-1">Issue Heat Map</p>
                <h2 id="issueHeatMapHeading" class="ci-panel__title">Show issues geographically</h2>
                <p class="text-muted small mb-0">Filter by issue type to explore water, road, and pollution hotspots.</p>
            </div>
            <div class="ci-map-tabs" role="tablist" aria-label="Heat map presets">
                @foreach($heatMapPresets as $presetKey => $preset)
                    <button
                        type="button"
                        class="ci-map-tab {{ $activeMapPreset === $presetKey ? 'is-active' : '' }}"
                        data-map-preset="{{ $presetKey }}"
                        role="tab"
                        aria-selected="{{ $activeMapPreset === $presetKey ? 'true' : 'false' }}"
                    >{{ $preset['label'] }}</button>
                @endforeach
            </div>
        </div>
        <div id="communityIssuesHeatMap" aria-label="Community issues heat map"></div>
        <p class="text-muted small mt-2 mb-0" id="communityIssuesHeatMapCount">
            Showing <strong>{{ count($heatMapMarkers) }}</strong> mapped {{ \Illuminate\Support\Str::plural('issue', count($heatMapMarkers)) }} on <strong>{{ $heatMapPresets[$activeMapPreset]['label'] }}</strong>.
        </p>
    </section>

    <section class="ci-panel" aria-labelledby="communityChampionsHeading">
        <div class="ci-panel__header">
            <div>
                <p class="ci-unique-kicker text-danger mb-1">Community Champions</p>
                <h2 id="communityChampionsHeading" class="ci-panel__title">Residents helping solve issues</h2>
                <p class="text-muted small mb-0">Badges recognize reporters, volunteers, problem solvers, water warriors, and green champions.</p>
            </div>
        </div>

        @if($champions === [])
            <div class="alert alert-light border mb-0">
                Community champions will appear here as residents report issues, verify problems, and drive resolutions.
            </div>
        @else
            <div class="ci-champions-grid">
                @foreach($champions as $champion)
                    @php
                        $championUser = $champion['user'];
                        $displayName = $championUser->authorDisplayName();
                    @endphp
                    <article class="ci-champion-card">
                        <div class="d-flex align-items-center gap-3">
                            @if(filled($championUser->profile_image))
                                <img src="{{ asset($championUser->profile_image) }}" alt="{{ $displayName }}" class="ci-champion-card__avatar">
                            @else
                                <div class="ci-champion-card__avatar d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-user text-secondary" aria-hidden="true"></i>
                                </div>
                            @endif
                            <div>
                                <h3 class="h6 mb-1">
                                    <a href="{{ route('community.authors.show', $championUser->authorUniqueName()) }}" class="text-decoration-none">{{ $displayName }}</a>
                                </h3>
                                <p class="text-muted small mb-0">
                                    {{ number_format($champion['issues_reported']) }} reported ·
                                    {{ number_format($champion['supports_given'] + $champion['verifications_given']) }} civic actions
                                </p>
                            </div>
                        </div>
                        <div class="ci-champion-badges">
                            @foreach($champion['badges'] as $badge)
                                @php $badgeMeta = $championBadges[$badge] ?? null; @endphp
                                <span class="badge bg-{{ $badgeMeta['color'] ?? 'secondary' }} text-white" title="{{ $badgeMeta['description'] ?? $badge }}">
                                    @if($badgeMeta)
                                        <i class="{{ $badgeMeta['icon'] }} me-1" aria-hidden="true"></i>
                                    @endif
                                    {{ $badge }}
                                </span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <form method="get" action="{{ route('community.community-issues.index') }}" class="ci-filters">
        <input type="hidden" name="map" value="{{ $filters['map'] }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Severity</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">Any severity</option>
                    @foreach($severityLevels as $severity)
                        <option value="{{ $severity }}" @selected($filters['severity'] === $severity)>{{ $severity }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Any status</option>
                    @foreach($statusSteps as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">State</label>
                <input type="text" name="state" class="form-control form-control-sm" value="{{ $filters['state'] }}" placeholder="e.g. Uttarakhand">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger btn-sm w-100">Filter issues</button>
            </div>
        </div>
    </form>

    @if($posts->isEmpty())
        <div class="alert alert-light border text-center py-5">
            <h4 class="mb-2">No community issues yet</h4>
            <p class="text-muted mb-3">Be the first to report a civic problem and put it on the map.</p>
            @auth
                <a href="{{ route('community.posts.create', ['type' => 'community-issues']) }}" class="btn btn-danger">Report an issue</a>
            @endauth
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 g-lg-4">
            @include('community.partials.post-cards', ['posts' => $posts, 'engagement' => $engagement])
        </div>
        <div class="mt-4">{{ $posts->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
(function () {
    const mapElement = document.getElementById('communityIssuesHeatMap');
    const countElement = document.getElementById('communityIssuesHeatMapCount');
    const heatMapUrl = @json(route('community.community-issues.heat-map'));
    const presetColors = @json(collect($heatMapPresets)->mapWithKeys(fn ($preset, $key) => [$key => $preset['color']]));
    let initialMarkers = @json($heatMapMarkers);
    let activePreset = @json($activeMapPreset);
    let activeLabel = @json($heatMapPresets[$activeMapPreset]['label']);

    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    const map = L.map('communityIssuesHeatMap', { scrollWheelZoom: true }).setView([20.5937, 78.9629], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let heatLayer = null;
    let markerLayer = L.layerGroup().addTo(map);

    function popupHtml(marker) {
        const parts = [
            '<strong>' + marker.title + '</strong>',
            marker.category ? '<div class="small text-muted">' + marker.category + '</div>' : '',
            marker.severity ? '<div class="small">Severity: ' + marker.severity + '</div>' : '',
            marker.status ? '<div class="small">Status: ' + marker.status + '</div>' : '',
            '<a href="' + marker.url + '" class="small">View issue</a>',
        ];
        return parts.filter(Boolean).join('');
    }

    function renderMarkers(markers, preset) {
        if (heatLayer) {
            map.removeLayer(heatLayer);
            heatLayer = null;
        }
        markerLayer.clearLayers();

        if (!markers.length) {
            if (countElement) {
                countElement.innerHTML = 'No mapped issues on <strong>' + activeLabel + '</strong> yet.';
            }
            return;
        }

        const heatPoints = markers.map(function (marker) {
            return [marker.lat, marker.lng, marker.intensity || 0.4];
        });

        heatLayer = L.heatLayer(heatPoints, {
            radius: 22,
            blur: 18,
            maxZoom: 12,
            gradient: {
                0.2: '#fde68a',
                0.5: presetColors[preset] || '#dc2626',
                0.8: '#b91c1c',
                1.0: '#7f1d1d',
            },
        }).addTo(map);

        markers.forEach(function (marker) {
            L.circleMarker([marker.lat, marker.lng], {
                radius: 6,
                color: '#fff',
                weight: 2,
                fillColor: presetColors[preset] || '#dc2626',
                fillOpacity: 0.85,
            })
                .bindPopup(popupHtml(marker))
                .addTo(markerLayer);
        });

        const bounds = L.latLngBounds(markers.map(function (marker) {
            return [marker.lat, marker.lng];
        }));
        map.fitBounds(bounds.pad(0.2));

        if (countElement) {
            countElement.innerHTML = 'Showing <strong>' + markers.length + '</strong> mapped issue' + (markers.length === 1 ? '' : 's') + ' on <strong>' + activeLabel + '</strong>.';
        }
    }

    renderMarkers(initialMarkers, activePreset);

    document.querySelectorAll('[data-map-preset]').forEach(function (button) {
        button.addEventListener('click', function () {
            const preset = button.getAttribute('data-map-preset');
            if (!preset || preset === activePreset) {
                return;
            }

            document.querySelectorAll('[data-map-preset]').forEach(function (tab) {
                tab.classList.toggle('is-active', tab.getAttribute('data-map-preset') === preset);
                tab.setAttribute('aria-selected', tab.getAttribute('data-map-preset') === preset ? 'true' : 'false');
            });

            fetch(heatMapUrl + '?preset=' + encodeURIComponent(preset), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    activePreset = payload.preset;
                    activeLabel = payload.label;
                    renderMarkers(payload.markers || [], activePreset);
                })
                .catch(function () {
                    if (countElement) {
                        countElement.textContent = 'Unable to load heat map data right now.';
                    }
                });
        });
    });

    window.setTimeout(function () { map.invalidateSize(); }, 250);
})();
</script>
@endpush
