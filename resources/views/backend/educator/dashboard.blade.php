@extends('backend.layouts.app')

@section('title', 'Educator Dashboard')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <p class="ems-kicker mb-1">{{ $educator->roleLabel() }} Panel</p>
            <h2 class="admin-title mb-1">Welcome, {{ $educator->display_name }}</h2>
            <p class="mb-0 text-secondary">Manage your profile, study materials, and student enquiries.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('educator.profile.edit') }}" class="btn btn-outline-primary">Edit profile</a>
            @if($educator->isApproved())
                <a href="{{ $educator->publicUrl() }}" target="_blank" class="btn btn-primary ems-btn-primary">View live profile</a>
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

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="chart-card">
                <h5 class="mb-3">Quick actions</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('educator.materials.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Upload material</a>
                    <a href="{{ route('educator.materials.index') }}" class="btn btn-outline-secondary">My materials</a>
                    <a href="{{ route('educator.enquiries.index') }}" class="btn btn-outline-secondary">Enquiries</a>
                    <a href="{{ route('study-materials.library') }}" target="_blank" class="btn btn-outline-success">Study library</a>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="chart-card">
                <h5 class="mb-3">Profile strength</h5>
                <div class="progress mb-2" style="height:10px">
                    <div class="progress-bar" role="progressbar" style="width: {{ $completeness }}%"></div>
                </div>
                <p class="mb-0 small text-secondary">{{ $completeness }}% complete — add subjects, qualifications, and availability to improve visibility.</p>
            </div>
        </div>
    </div>
</div>
@endsection
