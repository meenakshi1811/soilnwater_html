@extends('backend.layouts.app')

@section('title', 'Ad Submissions')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Ad Submissions</h2>
            <p class="mb-0 text-secondary">Review submitted ads and approve/reject them.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="chart-card">
        <div id="adminAdSubmissionAlert" class="alert d-none" role="alert"></div>

        <div class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Size</label>
                <select id="adminAdsFilterSize" class="form-select">
                    <option value="">All sizes</option>
                    @foreach($sizes as $key => $s)
                        <option value="{{ $key }}">{{ $s['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Status</label>
                <select id="adminAdsFilterStatus" class="form-select">
                    <option value="">All</option>
                    @foreach(['pending','approved','rejected'] as $st)
                        <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                <button type="button" id="adminAdsApplyFilters" class="btn btn-outline-secondary w-100">Apply Filters</button>
            </div>
        </div>

        <div class="table-responsive">
            <table
                id="adminAdSubmissionsTable"
                class="table table-bordered align-middle w-100"
                data-url="{{ route('admin.ads.submissions.data') }}"
            >
                <thead>
                <tr>
                    <th>Title</th>
                    <th>User</th>
                    <th>Size</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="text-end">Action</th>
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

