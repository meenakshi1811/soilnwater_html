@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="admin-panel dashboard-modern">
    <div class="dashboard-hero mb-4">
        <div>
            <h2 class="admin-title mb-1">Admin Dashboard</h2>
            <p class="mb-0">Overview widgets, growth metrics and ad performance in one view.</p>
        </div>
        <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">Update Profile</a>
    </div>

    <div class="section-label">Overview Widgets</div>
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card users">
                <span>Total Users</span>
                <h3>{{ number_format($totalUsers) }}</h3>
                <small>{{ number_format($activeUsers) }} verified accounts</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card vendors">
                <span>Active Vendors</span>
                <h3>{{ number_format($activeVendors) }}</h3>
                <small>Verified vendor accounts</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card products">
                <span>Total Products</span>
                <h3>{{ number_format($totalProducts) }}</h3>
                <small>Catalog inventory</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card properties">
                <span>Total Properties</span>
                <h3>{{ number_format($totalProperties) }}</h3>
                <small>Listed properties</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card ads">
                <span>Active Ads</span>
                <h3>{{ number_format($activeAds) }}</h3>
                <small>Running campaigns</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card revenue">
                <span>Revenue Today / Month</span>
                <h3>${{ number_format($revenueToday) }}</h3>
                <small>${{ number_format($revenueMonth) }} since {{ $monthStart->format('M d') }}</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="chart-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Graphs: Revenue Trends</h5>
                    <small>Monthly split</small>
                </div>
                <canvas id="revenueTrendsChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Ad Performance</h5>
                    <small>Circle chart</small>
                </div>
                <canvas id="adPerformanceChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="chart-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">User Growth</h5>
                    <small>Last 7 days</small>
                </div>
                <canvas id="userGrowthChart" height="140"></canvas>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="chart-card h-100">
                <h5 class="mb-3">Notifications Panel</h5>
                <div class="notification-list">
                    <div class="notification-item">
                        <span>New vendor registrations</span>
                        <strong>{{ $newVendorRegistrations }}</strong>
                    </div>
                    <div class="notification-item">
                        <span>Pending approvals</span>
                        <strong>{{ $pendingApprovals }}</strong>
                    </div>
                    <div class="notification-item">
                        <span>New leads</span>
                        <strong>{{ $newLeads }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="chart-card h-100">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="quick-action-list">
                    <a href="#" class="quick-action-btn"><i class="fa-solid fa-layer-group"></i> Add Category</a>
                    <a href="#" class="quick-action-btn"><i class="fa-solid fa-check-circle"></i> Approve Listings</a>
                    <a href="#" class="quick-action-btn"><i class="fa-solid fa-bullhorn"></i> Create Ad Campaign</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revenueTrendsChart'), {
    type: 'bar',
    data: {
        labels: @json($revenueLabels),
        datasets: [
            {
                label: 'Revenue',
                data: @json($revenueTrends),
                borderColor: '#1d4ed8',
                backgroundColor: ['#1d4ed8', '#2563eb', '#3b82f6', '#60a5fa'],
                borderRadius: 10
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

new Chart(document.getElementById('userGrowthChart'), {
    type: 'line',
    data: {
        labels: @json($userGrowthLabels),
        datasets: [{
            label: 'Total Users',
            data: @json($userGrowthSeries),
            borderColor: '#16a34a',
            backgroundColor: 'rgba(22,163,74,.16)',
            fill: true,
            tension: .35,
            pointRadius: 3,
            pointHoverRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

new Chart(document.getElementById('adPerformanceChart'), {
    type: 'doughnut',
    data: {
        labels: @json($adPerformanceLabels),
        datasets: [{
            data: @json($adPerformanceSeries),
            backgroundColor: ['#4f46e5', '#f59e0b', '#22c55e'],
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
