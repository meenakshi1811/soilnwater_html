@extends('backend.layouts.app')

@section('title', 'Ad Templates')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Template Manager</h2>
            <p class="mb-0 text-secondary">Create and maintain templates per ad size type.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form method="GET" action="{{ route('admin.ads.templates.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                    <select name="size_type" class="form-select" style="min-width:220px;">
                        <option value="">All sizes</option>
                        @foreach($sizes as $key => $s)
                            <option value="{{ $key }}" {{ request('size_type') === $key ? 'selected' : '' }}>{{ $s['name'] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline-secondary">Filter</button>
                </form>
            </div>
            <a href="{{ route('admin.ads.templates.create') }}" class="btn btn-primary ems-btn-primary">
                <i class="fa-solid fa-plus me-2"></i>New Template
            </a>
        </div>

        <div id="adminAdTemplateAlert" class="alert d-none" role="alert"></div>

        <div class="table-responsive">
            <table
                id="adminAdTemplatesTable"
                class="table table-bordered align-middle w-100"
                data-url="{{ route('admin.ads.templates.data') }}"
            >
                <thead>
                <tr>
                    <th>Preview</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Updated</th>
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

