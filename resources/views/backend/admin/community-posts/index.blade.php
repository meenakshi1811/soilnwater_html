@extends('backend.layouts.app')

@section('title', 'Community Post Approvals')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Admin Portal</p>
            <h2 class="admin-title mb-0">Community Post Approvals</h2>
            <p class="text-muted mb-0">Review community posts submitted for publishing.</p>
        </div>
        <a href="{{ route('admin.approvals.index', ['module' => 'community-posts']) }}" class="btn btn-outline-secondary">Open Approval Center</a>
    </div>

    <div class="chart-card p-3 table-responsive">
        <table id="communityPostsTable" class="table table-bordered align-middle w-100"
            data-source-url="{{ route('admin.community-posts.data') }}"
            data-action-base-url="{{ url('/admin/community-posts') }}">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Submitted</th>
                    <th>Status</th>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/admin-community-posts.js') }}?v={{ now()->timestamp }}"></script>
@endpush
