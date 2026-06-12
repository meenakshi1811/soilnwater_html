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

    <form id="community-post-form" method="POST" action="{{ $mode === 'edit' ? route('community.posts.update', $post) : route('community.posts.store') }}" enctype="multipart/form-data" class="chart-card">
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
                <label class="form-label" id="excerptLabel">Short excerpt</label>
                <textarea name="excerpt" id="excerptField" class="form-control" rows="2" maxlength="1000">{{ old('excerpt', $post->excerpt) }}</textarea>
                <small id="excerptHelp" class="text-muted d-block mt-1">A concise teaser shown in listing cards.</small>
            </div>
            <div class="col-12">
                <label class="form-label" id="bodyLabel">Body <span class="text-danger">*</span></label>
                <textarea name="body" id="bodyEditor" class="form-control" rows="12">{{ old('body', $post->body) }}</textarea>
                <small id="bodyHelp" class="text-muted d-block mt-1">Add the main content for this community post.</small>
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
                <div class="tag-input-wrap border rounded p-2">
                    <div id="tagList" class="d-flex flex-wrap gap-2 mb-2"></div>
                    <input type="text" id="tagInput" class="form-control border-0 p-0 shadow-none" placeholder="Type a tag and press Enter or comma">
                </div>
                <input type="hidden" name="tags" id="tagsHidden" value="{{ old('tags', is_array($post->tags) ? implode(', ', $post->tags) : '') }}">
                <small class="text-muted">Add each tag separately. Duplicate tags are ignored.</small>
            </div>
            <div class="col-md-6 general-extra">
                <label class="form-label">Author bio</label>
                <input type="text" name="author_bio" class="form-control" value="{{ old('author_bio', data_get($post->meta, 'author_bio')) }}" maxlength="500">
            </div>
            <div class="col-md-6">
                <label class="form-label" id="locationLabel">Location / local area <span class="text-danger">*</span></label>
                <input type="text" name="location" id="communityLocation" class="form-control" value="{{ old('location', data_get($post->meta, 'location')) }}" maxlength="160" placeholder="Search and select a location" autocomplete="off" required>
                <input type="hidden" name="location_lat" id="communityLocationLat" value="{{ old('location_lat', data_get($post->meta, 'location_lat')) }}">
                <input type="hidden" name="location_lng" id="communityLocationLng" value="{{ old('location_lng', data_get($post->meta, 'location_lng')) }}">
                <small class="text-muted" id="locationHelp">Select a Google Places suggestion so latitude and longitude are saved.</small>
            </div>
            <div class="col-12 type-extra report-flow" data-for="reports">
                <div class="report-flow-card border rounded-3 p-3 bg-light">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Professional report structure</h5>
                            <p class="text-muted mb-0 small">Use these fields to make the report review-ready, traceable, and easy for readers to evaluate.</p>
                        </div>
                        <span class="badge bg-success text-white">Report only</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Report subtitle</label>
                            <input type="text" name="report_subtitle" class="form-control" value="{{ old('report_subtitle', data_get($post->meta, 'report_subtitle')) }}" maxlength="255" placeholder="Optional supporting title">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reporting period <span class="text-danger">*</span></label>
                            <input type="text" name="reporting_period" class="form-control report-required" value="{{ old('reporting_period', data_get($post->meta, 'reporting_period')) }}" maxlength="120" placeholder="e.g. Q1 2026">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Report date <span class="text-danger">*</span></label>
                            <input type="date" name="report_date" class="form-control report-required" value="{{ old('report_date', data_get($post->meta, 'report_date')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prepared by / organization <span class="text-danger">*</span></label>
                            <input type="text" name="prepared_by" class="form-control report-required" value="{{ old('prepared_by', data_get($post->meta, 'prepared_by')) }}" maxlength="160" placeholder="Author, department, or organization">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Scope / objective</label>
                            <textarea name="report_scope" class="form-control" rows="3" maxlength="1000" placeholder="What this report covers and why it was prepared">{{ old('report_scope', data_get($post->meta, 'report_scope')) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Methodology <span class="text-danger">*</span></label>
                            <textarea name="methodology" class="form-control report-required" rows="4" maxlength="2000" placeholder="Methods, sample size, tools, and assumptions">{{ old('methodology', data_get($post->meta, 'methodology')) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data sources <span class="text-danger">*</span></label>
                            <textarea name="data_sources" class="form-control report-required" rows="4" maxlength="2000" placeholder="Primary/secondary data sources, citations, survey sources">{{ old('data_sources', data_get($post->meta, 'data_sources')) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Key findings <span class="text-danger">*</span></label>
                            <textarea name="key_findings" class="form-control report-required" rows="5" maxlength="3000" placeholder="Bullet-style findings or concise conclusions">{{ old('key_findings', data_get($post->meta, 'key_findings')) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Recommendations <span class="text-danger">*</span></label>
                            <textarea name="recommendations" class="form-control report-required" rows="5" maxlength="3000" placeholder="Actionable recommendations and next steps">{{ old('recommendations', data_get($post->meta, 'recommendations')) }}</textarea>
                        </div>
                    </div>
                </div>
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
            <div class="col-12 general-extra" id="allowCommentsWrap">
                <div class="form-check">
                    <input type="checkbox" name="allow_comments" value="1" class="form-check-input" id="allowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
                    <label class="form-check-label" for="allowComments">Allow comments / discussions</label>
                </div>
            </div>
            <div class="col-12 type-extra" data-for="reports">
                <div class="alert alert-info mb-0 py-2">
                    Comments are disabled for reports so the published page remains a formal reference document.
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

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .tag-input-wrap:focus-within { border-color: #86b7fe !important; box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .15); }
    .community-tag-pill { align-items: center; background: #e8f5ee; border: 1px solid #badbcc; border-radius: 999px; color: #0f5132; display: inline-flex; font-size: .875rem; font-weight: 600; gap: .35rem; padding: .25rem .55rem; }
    .community-tag-remove { background: transparent; border: 0; color: inherit; line-height: 1; padding: 0; }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    window.communityTypes = @json($types);
    window.communityBodyEditor = null;

    if (window.toastr) {
        window.toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 4000, extendedTimeOut: 2000 };
    }

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

        const isReport = typeSelect.value === 'reports';

        document.querySelectorAll('.type-extra').forEach((field) => {
            field.style.display = field.dataset.for === typeSelect.value ? '' : 'none';
        });

        document.querySelectorAll('.general-extra').forEach((field) => {
            field.style.display = isReport ? 'none' : '';
        });

        document.querySelectorAll('.report-required').forEach((field) => {
            field.required = isReport;
        });

        document.getElementById('excerptLabel').textContent = isReport ? 'Executive summary' : 'Short excerpt';
        document.getElementById('excerptField').placeholder = isReport ? 'Summarize objective, scope, main findings, and recommendations.' : '';
        document.getElementById('excerptHelp').textContent = isReport ? 'Keep this professional: purpose, coverage, key insight, and action in 2–4 lines.' : 'A concise teaser shown in listing cards.';
        document.getElementById('bodyLabel').innerHTML = isReport ? 'Detailed analysis / full report <span class="text-danger">*</span>' : 'Body <span class="text-danger">*</span>';
        document.getElementById('bodyHelp').textContent = isReport ? 'Recommended flow: background, context, analysis, evidence, limitations, conclusion, and appendix notes.' : 'Add the main content for this community post.';
        document.getElementById('locationLabel').innerHTML = isReport ? 'Coverage / study area <span class="text-danger">*</span>' : 'Location / local area <span class="text-danger">*</span>';
        document.getElementById('locationHelp').textContent = isReport ? 'Select the report coverage area from Google Places so the report is location-indexed.' : 'Select a Google Places suggestion so latitude and longitude are saved.';
        document.getElementById('allowComments').checked = isReport ? false : document.getElementById('allowComments').checked;
    }

    document.getElementById('contentType').addEventListener('change', function () {
        document.getElementById('categorySelect').dataset.selected = '';
        refreshCommunityCategories();
    });

    refreshCommunityCategories();

    ClassicEditor.create(document.querySelector('#bodyEditor'))
        .then((editor) => { window.communityBodyEditor = editor; })
        .catch(() => notify('error', 'Unable to load the body editor.'));

    const tagInput = document.getElementById('tagInput');
    const tagList = document.getElementById('tagList');
    const tagsHidden = document.getElementById('tagsHidden');
    let tags = (tagsHidden.value || '').split(',').map((tag) => tag.trim()).filter(Boolean);

    function notify(type, message) {
        const toastType = type === 'error' ? 'error' : 'success';
        if (window.toastr && typeof window.toastr[toastType] === 'function') {
            window.toastr[toastType](message);
            return;
        }
        alert(message);
    }

    function syncTags() {
        tagsHidden.value = tags.join(', ');
        tagList.innerHTML = '';
        tags.forEach((tag, index) => {
            const pill = document.createElement('span');
            pill.className = 'community-tag-pill';
            pill.innerHTML = '<span>#' + tag.replace(/[&<>"']/g, function (char) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char]; }) + '</span><button type="button" class="community-tag-remove" aria-label="Remove tag">&times;</button>';
            pill.querySelector('button').addEventListener('click', () => {
                tags.splice(index, 1);
                syncTags();
            });
            tagList.appendChild(pill);
        });
    }

    function addTagsFromInput() {
        const nextTags = tagInput.value.split(',').map((tag) => tag.trim()).filter(Boolean);
        nextTags.forEach((tag) => {
            if (!tags.map((item) => item.toLowerCase()).includes(tag.toLowerCase())) {
                tags.push(tag);
            }
        });
        tagInput.value = '';
        syncTags();
    }

    tagInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ',') {
            event.preventDefault();
            addTagsFromInput();
        }
    });
    tagInput.addEventListener('blur', addTagsFromInput);
    syncTags();

    window.initCommunityPostLocationAutocomplete = function () {
        const locationInput = document.getElementById('communityLocation');
        const latitudeInput = document.getElementById('communityLocationLat');
        const longitudeInput = document.getElementById('communityLocationLng');
        if (!locationInput || !window.google || !google.maps || !google.maps.places) return;

        const autocomplete = new google.maps.places.Autocomplete(locationInput, {
            fields: ['formatted_address', 'geometry', 'place_id'],
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place?.geometry?.location) {
                latitudeInput.value = '';
                longitudeInput.value = '';
                return;
            }
            locationInput.value = place.formatted_address || locationInput.value;
            latitudeInput.value = place.geometry.location.lat().toFixed(7);
            longitudeInput.value = place.geometry.location.lng().toFixed(7);
        });

        locationInput.addEventListener('input', function () {
            latitudeInput.value = '';
            longitudeInput.value = '';
        });
    };

    document.getElementById('community-post-form').addEventListener('submit', function (event) {
        event.preventDefault();
        const form = event.currentTarget;
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonHtml = submitButton.innerHTML;

        if (window.communityBodyEditor) {
            document.getElementById('bodyEditor').value = window.communityBodyEditor.getData();
        }
        addTagsFromInput();

        const bodyText = document.getElementById('bodyEditor').value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
        if (bodyText.length < 20) {
            notify('error', 'Please enter at least 20 characters in the body field.');
            window.communityBodyEditor?.editing.view.focus();
            return;
        }

        if (!document.getElementById('communityLocationLat').value || !document.getElementById('communityLocationLng').value) {
            notify('error', 'Please select a location from the Google Places suggestions.');
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = 'Saving...';

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form),
        })
            .then(async (response) => {
                const payload = await response.json();
                if (!response.ok) {
                    const firstError = payload.errors ? Object.values(payload.errors).flat()[0] : null;
                    notify('error', firstError || payload.message || 'Please fix the highlighted fields and try again.');
                    return;
                }
                notify('success', payload.message || 'Community post saved successfully.');
                setTimeout(() => { window.location.href = payload.redirect || '{{ route('community.posts.index') }}'; }, 800);
            })
            .catch(() => notify('error', 'Network error while saving the community post.'))
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHtml;
            });
    });
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initCommunityPostLocationAutocomplete"></script>
@endpush
