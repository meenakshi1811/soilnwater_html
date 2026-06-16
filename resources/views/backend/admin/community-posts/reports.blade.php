@extends('backend.layouts.app')

@section('title', 'Reported Community Posts')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Admin Portal</p>
            <h2 class="admin-title mb-0">Reported Community Posts</h2>
            <p class="text-muted mb-0">Review posts flagged by community readers.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="chart-card p-3 table-responsive">
        <table id="communityPostReportsTable" class="table table-bordered align-middle w-100"
            data-source-url="{{ route('admin.community-posts.reports.data') }}">
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Reporter</th>
                    <th>Reason</th>
                    <th>Reported</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/admin-community-post-reports.js') }}?v={{ now()->timestamp }}"></script>
@endpush
