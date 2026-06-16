@extends('backend.layouts.app')

@section('title', 'My Community Posts')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community publishing</p>
            <h2 class="admin-title mb-1">My Community Posts</h2>
            <p class="mb-0 text-secondary">Post articles, news, stories, reports, discussions, and other community content.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $authorUniqueName = old('author_slug', auth()->user()->authorUniqueName());
    @endphp
    <div class="chart-card mb-3 p-3">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div>
                <h6 class="mb-1">Author URL</h6>
                <p class="text-muted small mb-1">Public page for your posts.</p>
                <a href="{{ route('community.authors.show', auth()->user()->authorUniqueName()) }}" target="_blank" rel="noopener" class="small">
                    /auther/{{ auth()->user()->authorUniqueName() }}
                </a>
            </div>
            <form method="POST" action="{{ route('community.posts.author-url.update') }}" class="d-flex gap-2 flex-wrap align-items-center">
                @csrf
                @method('PATCH')
                <div>
                    <label class="form-label visually-hidden" for="authorSlug">Unique author name</label>
                    <input type="text" id="authorSlug" name="author_slug" class="form-control form-control-sm @error('author_slug') is-invalid @enderror" value="{{ $authorUniqueName }}" maxlength="80" pattern="[a-z0-9]+(-[a-z0-9]+)*" required>
                    <small class="text-muted">e.g. john-doe</small>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-success">Save</button>
            </form>
        </div>
    </div>

    <div class="chart-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">Post listing</h5>
            <a href="{{ route('community.posts.create') }}" class="btn btn-primary ems-btn-primary">
                <i class="fa-solid fa-plus me-2"></i>Create Post
            </a>
        </div>

        <div class="table-responsive">
            <table id="myCommunityPostsTable" class="table table-bordered align-middle w-100"
                data-source-url="{{ route('community.posts.data') }}"
                data-delete-base-url="{{ url('/dashboard/community-posts') }}">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Published</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/community-posts.js') }}?v={{ now()->timestamp }}"></script>
@endpush
