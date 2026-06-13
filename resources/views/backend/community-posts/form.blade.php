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
            <div class="col-md-6" id="categoryFieldWrap">
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
                <small id="bodyHelp" class="text-muted d-block mt-1">Add text and images together. Select an image to align it, or drag its corner to resize.</small>
                <small class="text-muted d-block mt-1">Tip: use left/right alignment for text wrapping beside an image. Place the cursor in the open space next to the image before typing.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label d-flex align-items-center justify-content-between gap-2">
                    <span>Featured images</span>
                    <small class="text-muted fw-normal" id="featuredImagesCount">0 / 5</small>
                </label>
                <div class="featured-images-uploader border rounded-3 p-3">
                    <input type="file" id="featuredImagesInput" class="d-none" accept="image/*" multiple>
                    <button type="button" id="featuredImagesAddBtn" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-images me-1"></i>Add images
                    </button>
                    <small class="text-muted d-block mt-2">Upload up to 5 images. JPG, PNG, or WebP, max 4 MB each.</small>
                    <div id="featuredImagesPreview" class="featured-images-grid mt-3"></div>
                    <div id="featuredImagesRemovedWrap"></div>
                </div>
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
            <div class="col-12 type-extra news-flow" data-for="news">
                <div class="news-flow-card border rounded-3 p-3 bg-light">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Professional news structure</h5>
                            <p class="text-muted mb-0 small">Capture the who, what, when, where, why, source, and verification details for publish-ready news.</p>
                        </div>
                        <span class="badge bg-primary text-white">News only</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">News subtitle / deck</label>
                            <input type="text" name="news_subtitle" class="form-control" value="{{ old('news_subtitle', data_get($post->meta, 'news_subtitle')) }}" maxlength="255" placeholder="Optional second line below the headline">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Dateline / place <span class="text-danger">*</span></label>
                            <input type="text" name="news_dateline" class="form-control news-required" value="{{ old('news_dateline', data_get($post->meta, 'news_dateline')) }}" maxlength="160" placeholder="e.g. Jaipur">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">News date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="news_date" class="form-control news-required" value="{{ old('news_date', data_get($post->meta, 'news_date')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reporter / byline <span class="text-danger">*</span></label>
                            <input type="text" name="reporter_name" class="form-control news-required" value="{{ old('reporter_name', data_get($post->meta, 'reporter_name')) }}" maxlength="160" placeholder="Reporter or desk name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Primary source <span class="text-danger">*</span></label>
                            <input type="text" name="news_source" class="form-control news-required" value="{{ old('news_source', data_get($post->meta, 'news_source')) }}" maxlength="160" placeholder="Official, witness, release, or agency">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Source URL</label>
                            <input type="url" name="source_url" class="form-control" value="{{ old('source_url', data_get($post->meta, 'source_url')) }}" maxlength="255" placeholder="https://example.com/source">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Verified facts / 5W summary <span class="text-danger">*</span></label>
                            <textarea name="fact_summary" class="form-control news-required" rows="4" maxlength="2000" placeholder="Who, what, when, where, why, and how confirmed">{{ old('fact_summary', data_get($post->meta, 'fact_summary')) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Verification notes <span class="text-danger">*</span></label>
                            <textarea name="verification_notes" class="form-control news-required" rows="4" maxlength="2000" placeholder="Cross-checks, documents, official statements, and confirmation status">{{ old('verification_notes', data_get($post->meta, 'verification_notes')) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Impact / affected area</label>
                            <textarea name="impact_area" class="form-control" rows="3" maxlength="1000" placeholder="Who is affected and what readers should know">{{ old('impact_area', data_get($post->meta, 'impact_area')) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quote / attribution</label>
                            <textarea name="quote_attribution" class="form-control" rows="3" maxlength="1000" placeholder="Important quote with speaker attribution">{{ old('quote_attribution', data_get($post->meta, 'quote_attribution')) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 type-extra my-area-flow" data-for="reports">
                <div class="my-area-flow-card border rounded-3 p-3 bg-light">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">My Area problem report</h5>
                            <p class="text-muted mb-0 small">Turn local issues into trackable community action with evidence, GPS location, support, comments, and votes.</p>
                        </div>
                        <span class="badge bg-warning text-dark">Reports · My Area</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Report type <span class="text-danger">*</span></label>
                            <select name="report_type" class="form-select my-area-required">
                                <option value="">Select report type</option>
                                @foreach(\App\Support\CommunityContentTaxonomy::myAreaReportTypes() as $reportType)
                                    <option value="{{ $reportType }}" @selected(old('report_type', data_get($post->meta, 'report_type')) === $reportType)>{{ $reportType }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Choose what neighbours should support, comment on, and vote for.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="issue_priority" class="form-select my-area-required">
                                <option value="">Select priority</option>
                                @foreach(['Low', 'Medium', 'High', 'Urgent'] as $priority)
                                    <option value="{{ $priority }}" @selected(old('issue_priority', data_get($post->meta, 'issue_priority')) === $priority)>{{ $priority }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Issue status</label>
                            <select name="issue_status" class="form-select">
                                @foreach(['Open', 'Under Review', 'Resolved'] as $status)
                                    <option value="{{ $status }}" @selected(old('issue_status', data_get($post->meta, 'issue_status', 'Open')) === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reported to</label>
                            <input type="text" name="reported_to" class="form-control" value="{{ old('reported_to', data_get($post->meta, 'reported_to')) }}" maxlength="160" placeholder="Department/authority">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference / complaint no.</label>
                            <input type="text" name="issue_reference" class="form-control" value="{{ old('issue_reference', data_get($post->meta, 'issue_reference')) }}" maxlength="160" placeholder="Optional tracking ID">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Evidence files</label>
                            <input type="file" name="issue_attachments[]" class="form-control" accept="image/*,video/*,.pdf,.doc,.docx" multiple>
                            <small class="text-muted">Upload up to 6 photos, videos, or documents. Each file can be up to 20 MB.</small>
                            @if(!empty(data_get($post->meta, 'issue_attachments')))
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    @foreach(data_get($post->meta, 'issue_attachments', []) as $attachment)
                                        <a href="{{ data_get($attachment, 'url') }}" target="_blank" rel="noopener" class="badge bg-light text-dark border text-decoration-none">{{ data_get($attachment, 'name', 'Attachment') }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @include('backend.community-posts.partials.type-fields')
            @php
                $existingVideo = $post->videoData();
                $videoSourceType = old('video_source_type', $existingVideo['type'] ?? 'none');
            @endphp
            <div class="col-12 common-post-fields">
                <div class="border rounded-3 p-3 bg-white">
                    <h5 class="mb-3">Common settings</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" id="locationLabel">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" id="communityLocation" class="form-control" value="{{ old('location', $post->location ?? data_get($post->meta, 'location')) }}" maxlength="160" placeholder="Search and select a location" autocomplete="off" required>
                            <input type="hidden" name="location_lat" id="communityLocationLat" value="{{ old('location_lat', $post->location_lat ?? data_get($post->meta, 'location_lat')) }}">
                            <input type="hidden" name="location_lng" id="communityLocationLng" value="{{ old('location_lng', $post->location_lng ?? data_get($post->meta, 'location_lng')) }}">
                            <small class="text-muted" id="locationHelp">Select a Google Places suggestion so latitude and longitude are saved.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Video <span class="text-muted fw-normal">(optional)</span></label>
                            <div class="community-video-field border rounded-3 p-3">
                                <div class="btn-group btn-group-sm w-100 mb-3" role="group" aria-label="Video source type">
                                    <input type="radio" class="btn-check" name="video_source_type" id="videoSourceNone" value="none" @checked($videoSourceType === 'none')>
                                    <label class="btn btn-outline-secondary" for="videoSourceNone">No video</label>
                                    <input type="radio" class="btn-check" name="video_source_type" id="videoSourceYoutube" value="youtube" @checked($videoSourceType === 'youtube')>
                                    <label class="btn btn-outline-secondary" for="videoSourceYoutube">YouTube link</label>
                                    <input type="radio" class="btn-check" name="video_source_type" id="videoSourceUpload" value="upload" @checked($videoSourceType === 'upload')>
                                    <label class="btn btn-outline-secondary" for="videoSourceUpload">Upload file</label>
                                </div>

                                <div id="videoYoutubeWrap" class="video-source-panel">
                                    <label class="form-label" for="videoYoutubeUrl">YouTube URL</label>
                                    <input type="url" name="video_youtube_url" id="videoYoutubeUrl" class="form-control" value="{{ old('video_youtube_url', ($existingVideo['type'] ?? null) === 'youtube' ? ($existingVideo['url'] ?? '') : '') }}" placeholder="https://www.youtube.com/watch?v=..." maxlength="500">
                                    <small class="text-muted d-block mt-2">Paste a public YouTube, YouTube Shorts, or youtu.be link.</small>
                                </div>

                                <div id="videoUploadWrap" class="video-source-panel">
                                    <label class="form-label" for="videoFile">Video file</label>
                                    <input type="file" name="video_file" id="videoFile" class="form-control" accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska,.mp4,.mov,.avi,.webm,.mkv">
                                    <small class="text-muted d-block mt-2">MP4, MOV, AVI, WebM, or MKV. Maximum size: 50 MB.</small>
                                    @if(($existingVideo['type'] ?? null) === 'upload')
                                        <input type="hidden" name="keep_existing_video" id="keepExistingVideo" value="1">
                                        <div id="existingVideoPreview" class="alert alert-light border mt-3 mb-0 py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                                            <div class="small">
                                                <strong>Current video:</strong> {{ $existingVideo['name'] ?? 'Uploaded video' }}
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="removeExistingVideoBtn">Remove</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-12" id="allowCommentsWrap">
                            <div class="form-check">
                                <input type="checkbox" name="allow_comments" value="1" class="form-check-input" id="allowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
                                <label class="form-check-label" for="allowComments">Enable public discussion thread</label>
                                <small class="text-muted d-block">When enabled, logged-in readers can add comments and replies on the public post page.</small>
                            </div>
                        </div>
                    </div>
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
    .featured-images-uploader { background: #fafbfc; }
    .featured-images-grid { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fill, minmax(108px, 1fr)); }
    .featured-image-card { aspect-ratio: 1; background: #fff; border: 1px solid #dbe3ea; border-radius: .75rem; box-shadow: 0 1px 2px rgba(16, 24, 40, .06); overflow: hidden; position: relative; }
    .featured-image-card img { display: block; height: 100%; object-fit: cover; width: 100%; }
    .featured-image-remove { align-items: center; background: rgba(15, 23, 42, .78); border: 0; border-radius: 999px; color: #fff; display: inline-flex; height: 28px; justify-content: center; position: absolute; right: .45rem; top: .45rem; width: 28px; }
    .featured-image-remove:hover { background: rgba(185, 28, 28, .92); color: #fff; }
    .featured-image-badge { background: rgba(15, 23, 42, .72); border-radius: 999px; bottom: .45rem; color: #fff; font-size: .7rem; font-weight: 600; left: .45rem; padding: .15rem .45rem; position: absolute; }
    .community-video-field { background: #fafbfc; }
    .video-source-panel { display: none; }
    .video-source-panel.is-active { display: block; }
    .ck-editor__editable_inline { min-height: 360px; overflow: auto; }
    .ck-editor__editable.ck-content > p,
    .ck-editor__editable.ck-content > h2,
    .ck-editor__editable.ck-content > h3,
    .ck-editor__editable.ck-content > h4,
    .ck-editor__editable.ck-content > blockquote,
    .ck-editor__editable.ck-content > ul,
    .ck-editor__editable.ck-content > ol {
        clear: none !important;
    }
    .ck-editor__editable.ck-content .image {
        clear: none !important;
        display: block;
        margin: 0.5em auto;
        max-width: 100%;
        min-width: 40px;
    }
    .ck-editor__editable.ck-content .image img {
        display: block;
        height: auto;
        max-width: 100%;
        min-height: 60px;
        min-width: 60px;
        overflow: hidden;
        resize: both;
    }
    .ck-editor__editable.ck-content .image.image-style-align-left,
    .ck-editor__editable.ck-content .image.image-style-side {
        clear: none !important;
        display: block !important;
        float: left !important;
        margin: 0.35rem 1.25rem 0.75rem 0 !important;
        max-width: 50%;
    }
    .ck-editor__editable.ck-content .image.image-style-align-right {
        clear: none !important;
        display: block !important;
        float: right !important;
        margin: 0.35rem 0 0.75rem 1.25rem !important;
        max-width: 50%;
    }
    .ck-editor__editable.ck-content .image.image-style-align-center,
    .ck-editor__editable.ck-content .image.image-style-block {
        clear: both;
        display: table;
        float: none;
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
    }
    .ck-editor__editable.ck-content .image.image-style-inline {
        display: inline-block;
        float: none;
        margin: 0.15em 0.35em;
        max-width: 50%;
        vertical-align: top;
    }
    .ck-editor__editable.ck-content .image.image-inline {
        display: inline-block;
        max-width: 50%;
        vertical-align: top;
    }
    @media (max-width: 767.98px) {
        .ck-editor__editable.ck-content .image.image-style-align-left,
        .ck-editor__editable.ck-content .image.image-style-align-right,
        .ck-editor__editable.ck-content .image.image-style-side,
        .ck-editor__editable.ck-content .image.image-style-inline,
        .ck-editor__editable.ck-content .image.image-inline {
            display: block;
            float: none;
            margin: 1rem auto;
            max-width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    window.communityTypes = @json($types);
    window.communityBodyEditor = null;
    window.communityFeaturedImages = {
        max: 5,
        existing: @json(collect($post->featuredImages())->map(fn ($path) => [
            'path' => $path,
            'url' => \App\Models\CommunityPost::resolveImageUrl($path),
        ])->values()),
        pending: [],
        removed: [],
    };

    if (window.toastr) {
        window.toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 4000, extendedTimeOut: 2000 };
    }

    function refreshCommunityCategories() {
        const typeSelect = document.getElementById('contentType');
        const categorySelect = document.getElementById('categorySelect');
        const categoryWrap = document.getElementById('categoryFieldWrap');
        const help = document.getElementById('typeHelp');
        const selected = categorySelect.dataset.selected;
        const type = window.communityTypes[typeSelect.value];
        const selectedType = typeSelect.value;
        const isReport = selectedType === 'reports';
        const isNews = selectedType === 'news';
        const hasTypeSection = Boolean(window.communityTypes[selectedType]) && !['news', 'reports'].includes(selectedType);

        categorySelect.innerHTML = '<option value="">Select category</option>';
        help.textContent = type ? type.description : '';

        if (isReport) {
            const option = document.createElement('option');
            option.value = 'Community Problem Report';
            option.textContent = 'Community Problem Report';
            option.selected = true;
            categorySelect.appendChild(option);
            categorySelect.required = false;
            categoryWrap.style.display = 'none';
        } else {
            categorySelect.required = true;
            categoryWrap.style.display = '';

            if (type) {
                type.categories
                    .filter((category) => category !== 'Community Problem Report')
                    .forEach((category) => {
                        const option = document.createElement('option');
                        option.value = category;
                        option.textContent = category;
                        option.selected = category === selected;
                        categorySelect.appendChild(option);
                    });
            }
        }

        document.querySelectorAll('.type-extra').forEach((field) => {
            field.style.display = field.dataset.for === selectedType ? '' : 'none';
        });

        document.querySelectorAll('.general-extra').forEach((field) => {
            field.style.display = (isNews || isReport || hasTypeSection) ? 'none' : '';
        });

        document.querySelectorAll('.news-required').forEach((field) => {
            field.required = isNews;
        });

        document.querySelectorAll('.my-area-required').forEach((field) => {
            field.required = isReport;
        });

        document.querySelectorAll('.type-field-required').forEach((field) => {
            const section = field.closest('.type-fields-flow');
            field.required = Boolean(section && section.dataset.for === selectedType && section.style.display !== 'none');
        });

        const fieldCopy = isReport ? {
            excerptLabel: 'Issue summary',
            excerptPlaceholder: 'Briefly explain the problem, affected people, and urgency.',
            excerptHelp: 'Use a clear problem statement so neighbours can quickly support or vote on it.',
            bodyLabel: 'Detailed problem description <span class="text-danger">*</span>',
            bodyHelp: 'Include what happened, when it started, exact location landmarks, risk, and expected solution.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select the exact issue location from Google Places so the problem can be mapped.',
        } : (isNews ? {
            excerptLabel: 'News summary / standfirst',
            excerptPlaceholder: 'Summarize the news angle, confirmed facts, and why it matters.',
            excerptHelp: 'Use a concise newsroom-style summary with the main verified fact and reader impact.',
            bodyLabel: 'Full news story <span class="text-danger">*</span>',
            bodyHelp: 'Recommended flow: lead, nut graph, details, context, quotes, impact, and latest update.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select the news location from Google Places so the story is location-indexed.',
        } : {
            excerptLabel: 'Short excerpt',
            excerptPlaceholder: '',
            excerptHelp: 'A concise teaser shown in listing cards.',
            bodyLabel: 'Body <span class="text-danger">*</span>',
            bodyHelp: 'Add text and images together. Select an image to resize or align it.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select a Google Places suggestion so latitude and longitude are saved.',
        });

        document.getElementById('excerptLabel').textContent = fieldCopy.excerptLabel;
        document.getElementById('excerptField').placeholder = fieldCopy.excerptPlaceholder;
        document.getElementById('excerptHelp').textContent = fieldCopy.excerptHelp;
        document.getElementById('bodyLabel').innerHTML = fieldCopy.bodyLabel;
        document.getElementById('bodyHelp').textContent = fieldCopy.bodyHelp;
        document.getElementById('locationLabel').innerHTML = fieldCopy.locationLabel;
        document.getElementById('locationHelp').textContent = fieldCopy.locationHelp;
    }

    document.getElementById('contentType').addEventListener('change', function () {
        document.getElementById('categorySelect').dataset.selected = '';
        refreshCommunityCategories();
    });

    refreshCommunityCategories();

    class CommunityUploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return this.loader.file.then((file) => {
                const data = new FormData();
                data.append('upload', file);

                return fetch('{{ route('community.posts.uploads.image') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: data,
                })
                    .then(async (response) => {
                        const payload = await response.json();
                        if (!response.ok || !payload.url) {
                            throw new Error(payload.message || 'Unable to upload image.');
                        }

                        return { default: payload.url };
                    });
            });
        }

        abort() {}
    }

    function communityUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new CommunityUploadAdapter(loader);
    }

    function communityImageTextFlowPlugin(editor) {
        let locking = false;

        editor.model.document.on('change:data', () => {
            if (locking) {
                return;
            }

            const differ = editor.model.document.differ;
            let imageNode = null;

            for (const change of differ.getChanges()) {
                if (change.type !== 'insert' || !change.position) {
                    continue;
                }

                const insertedNode = change.position.nodeAfter;
                if (!insertedNode) {
                    continue;
                }

                if (insertedNode.is('element', 'imageBlock')) {
                    imageNode = insertedNode;
                    break;
                }

                if (insertedNode.is('element', 'paragraph')) {
                    const previousSibling = insertedNode.previousSibling;
                    if (!previousSibling || !previousSibling.is('element', 'imageBlock')) {
                        continue;
                    }

                    const text = [...insertedNode.getChildren()]
                        .filter((child) => child.is('$text'))
                        .map((child) => child.data)
                        .join('');

                    if (text.trim() === '') {
                        imageNode = previousSibling;
                        break;
                    }
                }
            }

            if (!imageNode) {
                return;
            }

            locking = true;
            editor.model.change({ isUndoable: false }, (writer) => {
                let targetParagraph = imageNode.nextSibling;

                if (!targetParagraph || !targetParagraph.is('element', 'paragraph')) {
                    targetParagraph = writer.createElement('paragraph');
                    writer.insert(targetParagraph, writer.createPositionAfter(imageNode));
                }

                writer.setSelection(targetParagraph, 'in');
            });
            locking = false;
        });
    }

    ClassicEditor.create(document.querySelector('#bodyEditor'), {
        extraPlugins: [communityUploadAdapterPlugin, communityImageTextFlowPlugin],
        toolbar: {
            items: [
                'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                'insertImage', 'blockQuote', 'insertTable', '|', 'undo', 'redo',
            ],
        },
        image: {
            toolbar: [
                'imageTextAlternative',
                'toggleImageCaption',
                '|',
                'imageStyle:inline',
                'imageStyle:alignLeft',
                'imageStyle:alignRight',
                'imageStyle:alignCenter',
                'imageStyle:block',
                'imageStyle:side',
            ],
            styles: {
                options: [
                    'inline',
                    'alignLeft',
                    'alignRight',
                    'alignCenter',
                    'block',
                    'side',
                ],
            },
        },
    })
        .then((editor) => { window.communityBodyEditor = editor; })
        .catch((error) => {
            console.error(error);
            notify('error', 'Unable to load the body editor.');
        });

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

    const featuredImagesInput = document.getElementById('featuredImagesInput');
    const featuredImagesAddBtn = document.getElementById('featuredImagesAddBtn');
    const featuredImagesPreview = document.getElementById('featuredImagesPreview');
    const featuredImagesRemovedWrap = document.getElementById('featuredImagesRemovedWrap');
    const featuredImagesCount = document.getElementById('featuredImagesCount');
    const featuredImagesState = window.communityFeaturedImages;

    function featuredImagesTotal() {
        return featuredImagesState.existing.length + featuredImagesState.pending.length;
    }

    function updateFeaturedImagesUi() {
        featuredImagesPreview.innerHTML = '';
        featuredImagesRemovedWrap.innerHTML = '';
        featuredImagesCount.textContent = featuredImagesTotal() + ' / ' + featuredImagesState.max;
        featuredImagesAddBtn.disabled = featuredImagesTotal() >= featuredImagesState.max;

        featuredImagesState.existing.forEach((image, index) => {
            featuredImagesPreview.appendChild(createFeaturedImageCard({
                src: image.url,
                label: index === 0 ? 'Cover' : 'Saved',
                onRemove: () => {
                    featuredImagesState.removed.push(image.path);
                    featuredImagesState.existing.splice(index, 1);
                    syncFeaturedImagesRemovedInputs();
                    updateFeaturedImagesUi();
                },
            }));
        });

        featuredImagesState.pending.forEach((item, index) => {
            featuredImagesPreview.appendChild(createFeaturedImageCard({
                src: item.previewUrl,
                label: featuredImagesState.existing.length === 0 && index === 0 ? 'Cover' : 'New',
                onRemove: () => {
                    URL.revokeObjectURL(item.previewUrl);
                    featuredImagesState.pending.splice(index, 1);
                    updateFeaturedImagesUi();
                },
            }));
        });
    }

    function createFeaturedImageCard({ src, label, onRemove }) {
        const card = document.createElement('div');
        card.className = 'featured-image-card';
        card.innerHTML = '<img alt="Featured image preview"><span class="featured-image-badge"></span><button type="button" class="featured-image-remove" aria-label="Remove image"><i class="fa-solid fa-xmark"></i></button>';
        card.querySelector('img').src = src;
        card.querySelector('.featured-image-badge').textContent = label;
        card.querySelector('.featured-image-remove').addEventListener('click', onRemove);
        return card;
    }

    function syncFeaturedImagesRemovedInputs() {
        featuredImagesRemovedWrap.innerHTML = '';
        featuredImagesState.removed.forEach((path) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'removed_featured_images[]';
            input.value = path;
            featuredImagesRemovedWrap.appendChild(input);
        });
    }

    featuredImagesAddBtn.addEventListener('click', () => featuredImagesInput.click());

    featuredImagesInput.addEventListener('change', function () {
        const files = Array.from(this.files || []);
        this.value = '';

        files.forEach((file) => {
            if (featuredImagesTotal() >= featuredImagesState.max) {
                notify('error', 'You can upload up to 5 featured images.');
                return;
            }

            if (!file.type.startsWith('image/')) {
                notify('error', 'Only image files are allowed.');
                return;
            }

            featuredImagesState.pending.push({
                id: crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random(),
                file,
                previewUrl: URL.createObjectURL(file),
            });
        });

        updateFeaturedImagesUi();
    });

    updateFeaturedImagesUi();

    const maxVideoFileBytes = 52428800;
    const videoYoutubeWrap = document.getElementById('videoYoutubeWrap');
    const videoUploadWrap = document.getElementById('videoUploadWrap');
    const videoFileInput = document.getElementById('videoFile');
    const keepExistingVideoInput = document.getElementById('keepExistingVideo');
    const existingVideoPreview = document.getElementById('existingVideoPreview');
    const removeExistingVideoBtn = document.getElementById('removeExistingVideoBtn');

    function refreshVideoSourcePanels() {
        const selected = document.querySelector('input[name="video_source_type"]:checked')?.value || 'none';
        videoYoutubeWrap?.classList.toggle('is-active', selected === 'youtube');
        videoUploadWrap?.classList.toggle('is-active', selected === 'upload');
    }

    document.querySelectorAll('input[name="video_source_type"]').forEach((input) => {
        input.addEventListener('change', refreshVideoSourcePanels);
    });

    removeExistingVideoBtn?.addEventListener('click', () => {
        document.getElementById('videoSourceNone')?.click();
        existingVideoPreview?.remove();
        keepExistingVideoInput?.remove();
        if (videoFileInput) {
            videoFileInput.value = '';
        }
    });

    videoFileInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) {
            return;
        }

        if (file.size > maxVideoFileBytes) {
            notify('error', 'Video file must be 50 MB or smaller.');
            this.value = '';
            return;
        }

        if (keepExistingVideoInput) {
            keepExistingVideoInput.value = '0';
        }
    });

    refreshVideoSourcePanels();

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

        const videoSource = document.querySelector('input[name="video_source_type"]:checked')?.value || 'none';
        if (videoSource === 'youtube') {
            const youtubeUrl = document.getElementById('videoYoutubeUrl')?.value.trim();
            if (!youtubeUrl) {
                notify('error', 'Please enter a YouTube video link or choose another video option.');
                return;
            }
        }

        if (videoSource === 'upload') {
            const hasNewFile = (videoFileInput?.files?.length || 0) > 0;
            const keepingExisting = keepExistingVideoInput?.value === '1';
            if (!hasNewFile && !keepingExisting) {
                notify('error', 'Please choose a video file to upload or switch to another video option.');
                return;
            }

            if (hasNewFile && videoFileInput.files[0].size > maxVideoFileBytes) {
                notify('error', 'Video file must be 50 MB or smaller.');
                return;
            }
        }

        submitButton.disabled = true;
        submitButton.innerHTML = 'Saving...';

        const formData = new FormData(form);
        formData.delete('featured_images[]');
        featuredImagesState.pending.forEach((item) => {
            formData.append('featured_images[]', item.file);
        });

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData,
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
