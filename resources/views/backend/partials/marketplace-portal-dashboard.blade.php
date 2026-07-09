@php
    $isPremium = (bool) ($profile->is_premium ?? false);
    $portalClass = 'marketplace-portal-dashboard portal-'.$portalType.($isPremium ? ' is-premium' : '');
    $chartId = 'portalTrendChart-'.$portalType;
    $breakdownChartId = 'portalBreakdownChart-'.$portalType;
@endphp

<div class="admin-panel ems-page {{ $portalClass }}">
    <div class="marketplace-portal-hero mb-4">
        <div class="marketplace-portal-hero__copy">
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

    <div class="portal-stat-grid mb-4">
        @foreach ($stats as $stat)
            <a href="{{ $stat['url'] }}" class="portal-stat-card {{ $stat['class'] }}">
                <div class="portal-stat-card__body">
                    <span class="portal-stat-card__label">{{ $stat['label'] }}</span>
                    <strong class="portal-stat-card__value">{{ number_format($stat['value']) }}</strong>
                    <small class="portal-stat-card__detail">{{ $stat['detail'] }}</small>
                </div>
                <span class="portal-stat-card__chevron"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
        @endforeach
    </div>

    <div class="analytics-panel mb-4">
        <div class="analytics-panel__head">
            <div>
                <h3 class="analytics-panel__title">
                    @if ($isPremium)
                        <i class="fa-solid fa-chart-line me-2"></i>Premium Analytics
                    @else
                        <i class="fa-solid fa-chart-simple me-2"></i>Performance Insights
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

        <div class="analytics-metric-grid mb-4">
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

        <div class="analytics-visual-grid mb-4">
            <div class="analytics-chart-card">
                <div class="analytics-card-head">
                    <h4>Enquiry trend</h4>
                    <span>Last 6 months</span>
                </div>
                <div class="analytics-chart-wrap">
                    <canvas id="{{ $chartId }}" height="220"></canvas>
                </div>
            </div>

            <div class="analytics-chart-card">
                <div class="analytics-card-head">
                    <h4>Enquiry split</h4>
                    <span>By enquiry type</span>
                </div>
                <div class="analytics-donut-wrap">
                    <canvas id="{{ $breakdownChartId }}" height="220"></canvas>
                </div>
            </div>
        </div>

        <div class="analytics-rings-grid mb-4">
            @foreach ($analytics['rings'] as $ring)
                @php
                    $radius = 54;
                    $circumference = round(2 * M_PI * $radius, 2);
                    $dashOffset = $circumference - ($circumference * ($ring['percent'] / 100));
                @endphp
                <div class="analytics-ring-card">
                    <div class="portal-progress-ring">
                        <svg viewBox="0 0 128 128" aria-hidden="true">
                            <circle class="portal-progress-ring__track" cx="64" cy="64" r="{{ $radius }}"></circle>
                            <circle
                                class="portal-progress-ring__bar"
                                cx="64"
                                cy="64"
                                r="{{ $radius }}"
                                style="stroke: {{ $ring['color'] }}; stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $dashOffset }};"
                            ></circle>
                        </svg>
                        <div class="portal-progress-ring__center">
                            <strong>{{ $ring['percent'] }}%</strong>
                        </div>
                    </div>
                    <div class="analytics-ring-card__meta">
                        <span class="analytics-ring-card__label">{{ $ring['label'] }}</span>
                        <small>{{ $ring['hint'] }}</small>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="analytics-recent-card">
            <div class="analytics-card-head mb-3">
                <h4>Recent enquiries</h4>
                <span>Latest customer activity</span>
            </div>
            @if (! empty($analytics['recent']))
                <ul class="analytics-recent-list">
                    @foreach ($analytics['recent'] as $item)
                        <li class="analytics-recent-item">
                            <div class="analytics-recent-item__icon">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div class="analytics-recent-item__copy">
                                <strong>{{ $item['title'] }}</strong>
                                <span>{{ $item['meta'] }}</span>
                            </div>
                            <time>{{ $item['date'] }}</time>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="analytics-empty-state">
                    <i class="fa-solid fa-chart-pie"></i>
                    <p>No enquiries recorded yet. Once customers reach out, charts and activity will appear here.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="portal-actions-grid">
        <a href="{{ $publicPageRoute }}" class="action-card d-block text-decoration-none h-100">
            <span class="icon-wrap bg-purple"><i class="fa-solid fa-globe"></i></span>
            <h5 class="mt-3">Public Page</h5>
            <p class="small mb-0">{{ $publicPageDescription }}</p>
        </a>
        <a href="{{ $branchesRoute }}" class="action-card d-block text-decoration-none h-100">
            <span class="icon-wrap bg-green"><i class="fa-solid fa-code-branch"></i></span>
            <h5 class="mt-3">My Branches</h5>
            <p class="small mb-0">Manage branch profiles with PAN, GST, contact and gallery.</p>
        </a>
        <div class="action-card h-100">
            <span class="icon-wrap bg-amber"><i class="fa-solid fa-link"></i></span>
            <h5 class="mt-3">{{ $profileLinkTitle }}</h5>
            <p class="small">Share your public page:</p>
            <code class="d-block p-2 rounded portal-link-code">{{ $profileLinkUrl }}</code>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/marketplace-portal-dashboard.css') }}?v={{ now()->timestamp }}">
    <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                return;
            }

            const isPremium = @json($isPremium);
            const accent = isPremium ? '#c9a227' : '#2563eb';
            const accentSoft = isPremium ? 'rgba(201, 162, 39, 0.18)' : 'rgba(37, 99, 235, 0.16)';
            const trend = @json($analytics['trend']);
            const breakdown = @json($analytics['breakdown']);

            const trendCanvas = document.getElementById(@json($chartId));
            if (trendCanvas) {
                new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: trend.labels,
                        datasets: [{
                            label: 'Enquiries',
                            data: trend.values,
                            borderColor: accent,
                            backgroundColor: accentSoft,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: accent,
                            pointBorderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b', font: { size: 12, weight: '600' } },
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(148, 163, 184, 0.18)' },
                                ticks: {
                                    precision: 0,
                                    color: '#64748b',
                                    font: { size: 12 },
                                },
                            },
                        },
                    },
                });
            }

            const breakdownCanvas = document.getElementById(@json($breakdownChartId));
            if (breakdownCanvas) {
                const breakdownValues = breakdown.map(function (item) { return item.value; });
                const hasData = breakdownValues.some(function (value) { return value > 0; });

                new Chart(breakdownCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: breakdown.map(function (item) { return item.label; }),
                        datasets: [{
                            data: hasData ? breakdownValues : [1],
                            backgroundColor: hasData
                                ? breakdown.map(function (item) { return item.color; })
                                : ['#e2e8f0'],
                            borderWidth: 0,
                            hoverOffset: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 16,
                                    color: '#334155',
                                    font: { size: 12, weight: '600' },
                                },
                            },
                        },
                    },
                });
            }
        });
    </script>
@endpush
