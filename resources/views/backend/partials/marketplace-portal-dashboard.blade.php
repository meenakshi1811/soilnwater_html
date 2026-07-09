@php
    $isPremium = (bool) ($profile->is_premium ?? false);
    $portalClass = 'marketplace-portal-dashboard portal-'.$portalType.($isPremium ? ' is-premium' : '');
@endphp

<div class="admin-panel ems-page {{ $portalClass }}">
    <div class="marketplace-portal-hero mb-4">
        <div>
            <p class="marketplace-portal-kicker mb-2">{{ $portalKicker }}</p>
            <h2 class="admin-title mb-2 text-white">Overview</h2>
            <p class="mb-0 text-white-50">{{ $welcomeText }}</p>
            @if ($isPremium)
                <span class="marketplace-portal-premium-pill">
                    <i class="fa-solid fa-crown"></i>
                    Premium {{ $portalSingular }} Member
                </span>
            @endif
        </div>
        <a href="{{ $liveUrl }}" target="_blank" class="btn btn-light marketplace-portal-hero-btn">
            <i class="fa-solid fa-external-link-alt me-1"></i> {{ $liveLabel }}
        </a>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($stats as $stat)
            <div class="col-md-3 col-6">
                <a href="{{ $stat['url'] }}" class="stat-card {{ $stat['class'] }} text-center h-100 d-block text-decoration-none">
                    <p class="small mb-1 text-white-50">{{ $stat['label'] }}</p>
                    <h3 class="mb-1 text-white">{{ number_format($stat['value']) }}</h3>
                    <span class="stat-detail text-white-50">{{ $stat['detail'] }}</span>
                </a>
            </div>
        @endforeach
    </div>

    <div class="analytics-panel mb-4">
        <div class="analytics-panel__head">
            <div>
                <h3 class="analytics-panel__title">
                    @if ($isPremium)
                        <i class="fa-solid fa-chart-line me-2" style="color: var(--portal-accent);"></i>Premium Analytics
                    @else
                        Performance Insights
                    @endif
                </h3>
                <p class="analytics-panel__subtitle">
                    Enquiry activity and listing performance based on your current portal data.
                </p>
            </div>
            <a href="{{ $analytics['inquiries_url'] }}" class="btn btn-sm {{ $isPremium ? 'btn-warning' : 'btn-outline-primary' }}">
                View all enquiries
            </a>
        </div>

        <div class="analytics-metric-grid">
            @foreach ($analytics['metrics'] as $metric)
                <div class="analytics-metric-card">
                    <span class="analytics-metric-card__icon">
                        <i class="fa-solid {{ $metric['icon'] }}"></i>
                    </span>
                    <strong>{{ number_format($metric['value']) }}</strong>
                    <span>{{ $metric['label'] }}</span>
                    <small>{{ $metric['hint'] }}</small>
                </div>
            @endforeach
        </div>

        <div class="analytics-trend">
            <div class="analytics-trend__label">Enquiries over the last 6 months</div>
            <div class="analytics-trend-bars">
                @foreach ($analytics['trend']['labels'] as $index => $label)
                    @php
                        $value = $analytics['trend']['values'][$index] ?? 0;
                        $height = max(8, (int) round(($value / $analytics['trend']['max']) * 110));
                    @endphp
                    <div class="analytics-trend-bar">
                        <div class="analytics-trend-bar__value">{{ number_format($value) }}</div>
                        <div class="analytics-trend-bar__fill" style="height: {{ $height }}px;"></div>
                        <div class="analytics-trend-bar__month">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <div class="analytics-trend__label mb-2">Recent enquiries</div>
            @if (! empty($analytics['recent']))
                <ul class="analytics-recent-list">
                    @foreach ($analytics['recent'] as $item)
                        <li class="analytics-recent-item">
                            <div>
                                <strong>{{ $item['title'] }}</strong>
                                <span>{{ $item['meta'] }}</span>
                            </div>
                            <time>{{ $item['date'] }}</time>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="analytics-empty">No enquiries recorded yet. Once customers reach out, activity will appear here.</p>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <a href="{{ $publicPageRoute }}" class="action-card d-block text-decoration-none h-100">
                <span class="icon-wrap bg-purple"><i class="fa-solid fa-globe"></i></span>
                <h5 class="mt-3">Public Page</h5>
                <p class="small mb-0">{{ $publicPageDescription }}</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ $branchesRoute }}" class="action-card d-block text-decoration-none h-100">
                <span class="icon-wrap bg-green"><i class="fa-solid fa-code-branch"></i></span>
                <h5 class="mt-3">My Branches</h5>
                <p class="small mb-0">Manage branch profiles with PAN, GST, contact and gallery.</p>
            </a>
        </div>
        <div class="col-md-4">
            <div class="action-card h-100">
                <span class="icon-wrap bg-amber"><i class="fa-solid fa-link"></i></span>
                <h5 class="mt-3">{{ $profileLinkTitle }}</h5>
                <p class="small">Share your public page:</p>
                <code class="d-block p-2 rounded portal-link-code">{{ $profileLinkUrl }}</code>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/marketplace-portal-dashboard.css') }}?v={{ now()->timestamp }}">
    <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
@endpush
