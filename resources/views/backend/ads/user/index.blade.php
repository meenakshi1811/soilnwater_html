@extends('backend.layouts.app')

@section('title', 'My Ads')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">My Ads</h2>
            <p class="mb-0 text-secondary">Create a new ad, customize a template, and submit it for admin approval.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">Ad listing</h5>
            <a href="{{ route('ads.create.size') }}" class="btn btn-primary ems-btn-primary">
                <i class="fa-solid fa-plus me-2"></i>Post New Ad
            </a>
        </div>

        <div id="userAdsAlert" class="alert d-none" role="alert"></div>

        <div class="table-responsive">
            <table
                id="userAdsTable"
                class="table table-bordered align-middle w-100"
                data-url="{{ route('ads.data') }}"
            >
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Size</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Submitted</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/ads.js') }}?v={{ now()->timestamp }}"></script>
@endpush

