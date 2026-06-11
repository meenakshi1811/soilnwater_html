@extends('backend.layouts.app')

@section('title', $mode === 'edit' ? 'Edit Community Post' : 'Create Community Post')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community publishing</p>
            <h2 class="admin-title mb-1">{{ $mode === 'edit' ? 'Edit Community Post' : 'Create Community Post' }}</h2>
            <p class="mb-0 text-secondary">Select a post type and category, then add the content details.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $mode === 'edit' ? route('community.posts.update', $post) : route('community.posts.store') }}" enctype="multipart/form-data" class="chart-card">
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Post type <span class="text-danger">*</span></label>
                <select name="content_type" id="contentType" class="form-select" required>
                    <option value="">Select type</option>
                    @foreach($types as $key => $type)
                        <option value="{{ $key }}" @selected(old('content_type', $post->content_type) === $key)>{{ $type['label'] }}</option>
                    @endforeach
                </select>
                <small id="typeHelp" class="text-muted d-block mt-1"></small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category" id="categorySelect" class="form-select" data-selected="{{ old('category', $post->category) }}" required>
                    <option value="">Select category</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $post->title) }}" maxlength="255" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="published" @selected(old('status', $post->status) === 'published')>Publish now</option>
                    <option value="draft" @selected(old('status', $post->status) === 'draft')>Save as draft</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Short excerpt</label>
                <textarea name="excerpt" class="form-control" rows="2" maxlength="1000">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Body <span class="text-danger">*</span></label>
                <textarea name="body" class="form-control" rows="12" required>{{ old('body', $post->body) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Featured image</label>
                <input type="file" name="featured_image" class="form-control" accept="image/*">
                @if($post->featured_image_path)
                    <small class="text-muted">Current: {{ $post->featured_image_path }}</small>
                @endif
            </div>
            <div class="col-md-6">
                <label class="form-label">Tags</label>
                <input type="text" name="tags" class="form-control" value="{{ old('tags', is_array($post->tags) ? implode(', ', $post->tags) : '') }}" placeholder="water, community, education">
            </div>
            <div class="col-md-6">
                <label class="form-label">Author bio</label>
                <input type="text" name="author_bio" class="form-control" value="{{ old('author_bio', data_get($post->meta, 'author_bio')) }}" maxlength="500">
            </div>
            <div class="col-md-6">
                <label class="form-label">Location / local area</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', data_get($post->meta, 'location')) }}" maxlength="160">
            </div>
            <div class="col-md-4 type-extra" data-for="childrens-corner">
                <div class="form-check mt-4">
                    <input type="checkbox" name="parent_approved" value="1" class="form-check-input" id="parentApproved" @checked(old('parent_approved', data_get($post->meta, 'parent_approved')))>
                    <label class="form-check-label" for="parentApproved">Parent approved</label>
                </div>
            </div>
            <div class="col-md-4 type-extra" data-for="childrens-corner">
                <label class="form-label">School name</label>
                <input type="text" name="school_name" class="form-control" value="{{ old('school_name', data_get($post->meta, 'school_name')) }}" maxlength="160">
            </div>
            <div class="col-md-4 type-extra" data-for="astro-consultancy">
                <label class="form-label">Consultation fee/details</label>
                <input type="text" name="consultation_fee" class="form-control" value="{{ old('consultation_fee', data_get($post->meta, 'consultation_fee')) }}" maxlength="120">
            </div>
            <div class="col-md-4 type-extra" data-for="competitions">
                <label class="form-label">Competition deadline</label>
                <input type="date" name="competition_deadline" class="form-control" value="{{ old('competition_deadline', data_get($post->meta, 'competition_deadline')) }}">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" name="allow_comments" value="1" class="form-check-input" id="allowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
                    <label class="form-check-label" for="allowComments">Allow comments / discussions</label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('community.posts.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary ems-btn-primary">{{ $mode === 'edit' ? 'Update Post' : 'Create Post' }}</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    window.communityTypes = @json($types);

    function refreshCommunityCategories() {
        const typeSelect = document.getElementById('contentType');
        const categorySelect = document.getElementById('categorySelect');
        const help = document.getElementById('typeHelp');
        const selected = categorySelect.dataset.selected;
        const type = window.communityTypes[typeSelect.value];

        categorySelect.innerHTML = '<option value="">Select category</option>';
        help.textContent = type ? type.description : '';

        if (type) {
            type.categories.forEach((category) => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                option.selected = category === selected;
                categorySelect.appendChild(option);
            });
        }

        document.querySelectorAll('.type-extra').forEach((field) => {
            field.style.display = field.dataset.for === typeSelect.value ? '' : 'none';
        });
    }

    document.getElementById('contentType').addEventListener('change', function () {
        document.getElementById('categorySelect').dataset.selected = '';
        refreshCommunityCategories();
    });

    refreshCommunityCategories();
</script>
@endpush
