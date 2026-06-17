@extends('backend.layouts.app')

@section('title', 'All Community Posts')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Admin Portal</p>
            <h2 class="admin-title mb-0">All Community Posts</h2>
            <p class="text-muted mb-0">Browse posts created by admins, users, vendors, consultants, and service providers.</p>
        </div>
        <a href="{{ route('community.posts.create') }}" class="btn btn-primary ems-btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Create Post
        </a>
    </div>

    <div class="chart-card p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="statusFilter" class="form-label mb-1">Status filter</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All</option>
                    <option value="pending">Pending approval</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="declined">Rejected</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>
    </div>

    <div class="chart-card p-3 table-responsive">
        <table id="communityPostsAllTable" class="table table-bordered align-middle w-100"
            data-source-url="{{ route('admin.community-posts.all.data') }}"
            data-action-base-url="{{ url('/admin/community-posts') }}">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Trust</th>
                    <th>Promotion</th>
                    <th>Published</th>
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
