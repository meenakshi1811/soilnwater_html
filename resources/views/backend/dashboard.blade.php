@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="admin-panel">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="admin-title mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</p>
        </div>
        <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">Update Profile</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="admin-stat-card">
                <span>Regions</span>
                <h2>10</h2>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="admin-stat-card">
                <span>Services</span>
                <h2>5</h2>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="admin-stat-card">
                <span>Customers</span>
                <h2>2.5K</h2>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="admin-stat-card">
                <span>Area Admins</span>
                <h2>10</h2>
            </div>
        </div>
    </div>

    <div class="card admin-table-card">
        <div class="card-body">
            <h5 class="mb-3">Upcoming Service</h5>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Date</th>
                        <th>Cost</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Clara Barton</td>
                        <td>clarabarton56@gmail.com</td>
                        <td>47 W 13th St, New York</td>
                        <td>(212) 340-1431</td>
                        <td>20 Nov, 2023</td>
                        <td>$150</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
