@extends('backend.layouts.app')

@section('title', 'Institute Dashboard')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <p class="ems-kicker mb-1">{{ $institute->roleLabel() }} Panel</p>
            <h2 class="admin-title mb-1">Welcome, {{ $institute->displayName() }}</h2>
            <p class="mb-0 text-secondary">Manage your public profile and student enquiries.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ $portalRoute('profile.edit') }}" class="btn btn-outline-primary">Edit profile</a>
            <a href="{{ $portalRoute('public-page.edit') }}" class="btn btn-outline-primary">Manage public page</a>
            @if($institute->isApproved())
                <a href="{{ $institute->publicUrl() }}" target="_blank" class="btn btn-primary ems-btn-primary">View live profile</a>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($stats as $stat)
            <div class="col-md-6 col-xl-3">
                <a href="{{ $stat['url'] }}" class="chart-card text-decoration-none d-block h-100 p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-secondary small">{{ $stat['label'] }}</div>
                            <div class="fs-3 fw-bold text-dark">{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }}</div>
                            <div class="small text-muted">{{ $stat['detail'] }}</div>
                        </div>
                        <span class="text-primary"><i class="fa-solid {{ $stat['icon'] }} fa-lg"></i></span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="chart-card">
        <h5 class="mb-3">Quick actions</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ $portalRoute('profile.edit') }}" class="btn btn-primary"><i class="fa-solid fa-pen me-1"></i>Edit profile</a>
            <a href="{{ $portalRoute('public-page.edit') }}" class="btn btn-outline-primary"><i class="fa-solid fa-globe me-1"></i>Manage public page</a>
            <a href="{{ $portalRoute('enquiries.index') }}" class="btn btn-outline-secondary">View enquiries</a>
            <a href="{{ route($portalPrefix === 'school' ? 'schools.index' : 'institutes.index') }}" target="_blank" class="btn btn-outline-success">Browse public listing</a>
        </div>
    </div>
</div>
@endsection
