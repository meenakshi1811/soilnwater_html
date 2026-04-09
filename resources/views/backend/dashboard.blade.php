@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="admin-panel dashboard-modern">
    <div class="dashboard-hero mb-4">
        <div>
            <h2 class="admin-title mb-1">Performance Dashboard</h2>
            <p class="mb-0">Modern insights for roles, ads and offers in one place.</p>
        </div>
        <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">Update Profile</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="modern-stat-card ads">
                <span>Total Ads</span>
                <h3>{{ number_format($totalAds) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="modern-stat-card offers">
                <span>Total Offers</span>
                <h3>{{ number_format($totalOffers) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="modern-stat-card roles">
                <span>Total Non-Admin Users</span>
                <h3>{{ number_format(collect($roleCounts)->sum()) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="modern-stat-card conversion">
                <span>Offer / Ad Ratio</span>
                <h3>{{ $totalAds > 0 ? round(($totalOffers / $totalAds) * 100) : 0 }}%</h3>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Ads & Offers Trend</h5>
                    <small>Last 7 snapshots</small>
                </div>
                <canvas id="adsOffersTrendChart" height="110"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <h5 class="mb-3">Role Distribution</h5>
                <canvas id="roleDistributionChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Role Category Circles</h5>
            <small>All role types (excluding admin)</small>
        </div>

        <div class="role-circle-grid">
            @foreach($roleStats as $item)
                <div class="role-circle-card">
                    <div class="role-circle" style="--progress: {{ $item['percentage'] }};">
                        <div class="role-circle-inner">
                            <strong>{{ $item['count'] }}</strong>
                            <span>{{ $item['percentage'] }}%</span>
                        </div>
                    </div>
                    <h6 class="mb-0 mt-2">{{ $item['label'] }}</h6>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const trendLabels = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'];

new Chart(document.getElementById('adsOffersTrendChart'), {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [
            {
                label: 'Ads',
                data: @json($adsSeries),
                borderColor: '#1976d2',
                backgroundColor: 'rgba(25,118,210,.18)',
                fill: true,
                tension: .35,
                pointRadius: 4
            },
            {
                label: 'Offers',
                data: @json($offersSeries),
                borderColor: '#2e7d32',
                backgroundColor: 'rgba(46,125,50,.14)',
                fill: true,
                tension: .35,
                pointRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        }
    }
});

new Chart(document.getElementById('roleDistributionChart'), {
    type: 'doughnut',
    data: {
        labels: @json($roleLabels),
        datasets: [{
            data: @json($roleCounts),
            backgroundColor: ['#1976d2','#2e7d32','#f9a825','#7c4dff','#0097a7','#ef6c00'],
            borderWidth: 0
        }]
    },
    options: {
        cutout: '70%',
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
@endpush
