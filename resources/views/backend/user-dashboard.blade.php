@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="admin-panel dashboard-modern user-dashboard-panel">
    <div class="dashboard-hero mb-4">
        <div>
            <h2 class="admin-title mb-1">User Dashboard</h2>
            <p class="mb-0">Your listings and activity at a glance.</p>
        </div>
        <a href="{{ route('user.profile.edit') }}" class="btn btn-primary">Update Profile</a>
    </div>

    <div class="section-label">Overview</div>
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card ads">
                <span>Total Ads</span>
                <h3>{{ number_format($totalAds) }}</h3>
                <small>Your posted advertisements</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card offers">
                <span>Total Offers</span>
                <h3>{{ number_format($totalOffers) }}</h3>
                <small>Active offers you have shared</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card products">
                <span>Total Products</span>
                <h3>{{ number_format($totalProducts) }}</h3>
                <small>Items in your catalog</small>
            </div>
        </div>
    </div>
</div>
@endsection
