@extends('backend.layouts.app')

@section('title', 'Saved Community Posts')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community engagement</p>
            <h2 class="admin-title mb-1">Saved Posts</h2>
            <p class="mb-0 text-secondary">Articles and posts you saved from the community hub.</p>
        </div>
    </div>

    <div class="chart-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">Saved listing</h5>
            <a href="{{ route('community.index') }}" class="btn btn-outline-secondary btn-sm">Browse Community</a>
        </div>

        <div class="table-responsive">
            <table id="communitySavedPostsTable" class="table table-bordered align-middle w-100"
                data-source-url="{{ route('community.saved.data') }}"
                data-unsave-base-url="{{ url('/community') }}">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Published</th>
                        <th>Saved</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="{{ asset('assets/js/community-saved.js') }}?v={{ now()->timestamp }}"></script>
@endpush
