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
        @php
            $formStatus = old('status', $post->status === \App\Models\CommunityPost::STATUS_PENDING ? 'published' : $post->status);
        @endphp

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label" for="writingPurpose">Why are you writing this article? <span class="text-danger">*</span></label>
                <input type="text"
                    name="writing_purpose"
                    id="writingPurpose"
                    class="form-control"
                    list="writingPurposeOptions"
                    value="{{ old('writing_purpose', $post->writing_purpose) }}"
                    maxlength="120"
                    placeholder="Select a reason or type your own"
                    required>
                <datalist id="writingPurposeOptions">
                    @foreach(\App\Models\CommunityPost::WRITING_PURPOSE_OPTIONS as $purposeOption)
                        <option value="{{ $purposeOption }}"></option>
                    @endforeach
                </datalist>
                <small class="text-muted d-block mt-1">Pick one of the suggestions or enter your own reason.</small>
            </div>
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
                <label class="form-label" id="categoryLabel">Category <span class="text-danger">*</span></label>
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
                <select name="status" id="communityPostStatus" class="form-select" required>
                    <option value="published" @selected($formStatus === 'published')>Publish now</option>
                    <option value="draft" @selected($formStatus === 'draft')>Save as draft</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" id="excerptLabel">Short excerpt</label>
                <textarea name="excerpt" id="excerptField" class="form-control" rows="2" maxlength="1000">{{ old('excerpt', $post->excerpt) }}</textarea>
                <small id="excerptHelp" class="text-muted d-block mt-1">A concise teaser shown in listing cards.</small>
            </div>
            @php
                $initialBookPages = old('book_pages', $post->usesBookLayout() ? $post->bookPages() : []);
                if ($initialBookPages === []) {
                    $initialBookPages = [['content' => old('body', $post->body ?? '')]];
                }
            @endphp
            <div class="col-12" id="bodyContentSection">
                <div id="standardBodyHeader">
                    <label class="form-label" id="bodyLabel">Body <span class="text-danger">*</span></label>
                    <small id="bodyHelp" class="text-muted d-block mb-2">Add text and images together. There is no word limit. Select an image to align it, or drag its corner to resize.</small>
                </div>
                <div id="bookBodyHeader" style="display:none;">
                    <label class="form-label" id="bookBodyLabel">Book pages <span class="text-danger">*</span></label>
                    <small id="bookBodyHelp" class="text-muted d-block mb-3">Write your story page by page, like a book. Use the CKEditor below for each page.</small>
                    <div class="community-book-editor border rounded-3 p-3 bg-light mb-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div class="d-flex flex-wrap gap-2" id="bookPageTabs" role="tablist" aria-label="Book pages"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addBookPageBtn">
                                <i class="fa-solid fa-plus me-1"></i>Add another page
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong id="activeBookPageTitle">Page 1</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="removeBookPageBtn" style="display:none;">
                                <i class="fa-solid fa-trash me-1"></i>Remove page
                            </button>
                        </div>
                    </div>
                </div>
                <div id="bodyEditorMount" class="community-body-editor-mount border rounded-3 bg-white p-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 px-1" id="editorLanguageWrap">
                        <div>
                            <label for="editorLanguageSelect" class="form-label mb-0 small fw-semibold">Editor language</label>
                            <small class="text-muted d-block">Default is English. Switch to Hindi to write in Devanagari.</small>
                        </div>
                        <select id="editorLanguageSelect" class="form-select form-select-sm community-editor-language-select">
                            <option value="en">English</option>
                            <option value="hi">Hindi</option>
                        </select>
                        <input
                            type="hidden"
                            name="editor_language"
                            id="editorLanguageHidden"
                            value="{{ old('editor_language', data_get($post->meta, 'editor_language', 'en')) }}"
                        >
                    </div>
                    <textarea name="body" id="bodyEditor" class="form-control" rows="12">{{ old('body', $post->body) }}</textarea>
                </div>
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
                <label class="form-label d-flex align-items-center justify-content-between gap-2">
                    <span>Tags</span>
                    <small class="text-muted fw-normal" id="communityTagsCount">0 / 10</small>
                </label>
                <div class="tag-input-wrap border rounded p-2">
                    <div id="tagList" class="d-flex flex-wrap gap-2 mb-2"></div>
                    <input type="text" id="tagInput" class="form-control border-0 p-0 shadow-none" placeholder="Type a tag and press Enter or comma">
                </div>
                <input type="hidden" name="tags" id="tagsHidden" value="{{ old('tags', is_array($post->tags) ? implode(', ', $post->tags) : '') }}">
                <small class="text-muted">Add up to 10 tags. Duplicate tags are ignored.</small>
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
            @php
                $reportAuthorName = old(
                    'report_author_name',
                    data_get($post->meta, 'report_author_name', $post->user?->name ?: $post->user?->full_name ?: auth()->user()?->name ?: auth()->user()?->full_name)
                );
            @endphp
            <div class="col-12 type-extra report-flow" data-for="reports">
                <div class="report-flow-stack d-flex flex-column gap-3">
                    <div class="report-flow-card border rounded-3 p-3 p-md-4 bg-light">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Report classification</h5>
                                <p class="text-muted mb-0 small">Define the report format, priority, and tracking details.</p>
                            </div>
                            <span class="badge bg-warning text-dark">Reports</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Report status <span class="text-danger">*</span></label>
                                <select name="report_status" class="form-select my-area-required">
                                    <option value="">Select report status</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::reportStatuses() as $reportStatus)
                                        <option value="{{ $reportStatus }}" @selected(old('report_status', data_get($post->meta, 'report_status')) === $reportStatus)>{{ $reportStatus }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">What this report is asking for or communicating.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Report type <span class="text-danger">*</span></label>
                                <select name="report_type" class="form-select my-area-required">
                                    <option value="">Select report type</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::reportTypes() as $reportType)
                                        <option value="{{ $reportType }}" @selected(old('report_type', data_get($post->meta, 'report_type')) === $reportType)>{{ $reportType }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Choose the format or nature of this report.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="issue_priority" class="form-select my-area-required">
                                    <option value="">Select priority</option>
                                    @foreach(['Low', 'Medium', 'High', 'Urgent'] as $priority)
                                        <option value="{{ $priority }}" @selected(old('issue_priority', data_get($post->meta, 'issue_priority')) === $priority)>{{ $priority }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Issue status</label>
                                <select name="issue_status" class="form-select">
                                    @foreach(['Open', 'Under Review', 'Resolved'] as $status)
                                        <option value="{{ $status }}" @selected(old('issue_status', data_get($post->meta, 'issue_status', 'Open')) === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reported to</label>
                                <input type="text" name="reported_to" class="form-control" value="{{ old('reported_to', data_get($post->meta, 'reported_to')) }}" maxlength="160" placeholder="Department or authority">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reference / complaint no.</label>
                                <input type="text" name="issue_reference" class="form-control" value="{{ old('issue_reference', data_get($post->meta, 'issue_reference')) }}" maxlength="160" placeholder="Optional tracking ID">
                            </div>
                        </div>
                    </div>

                    <div class="report-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Report period</h5>
                                <p class="text-muted mb-0 small">Observation period covered by this report.</p>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary border">Optional dates</span>
                        </div>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label" for="observationPeriodFrom">From date</label>
                                <input
                                    type="date"
                                    name="observation_period_from"
                                    id="observationPeriodFrom"
                                    class="form-control"
                                    value="{{ old('observation_period_from', data_get($post->meta, 'observation_period_from')) }}"
                                >
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="observationPeriodTo">To date</label>
                                <input
                                    type="date"
                                    name="observation_period_to"
                                    id="observationPeriodTo"
                                    class="form-control"
                                    value="{{ old('observation_period_to', data_get($post->meta, 'observation_period_to')) }}"
                                >
                            </div>
                            <div class="col-md-4">
                                <div class="report-period-preview border rounded-3 px-3 py-2 bg-light h-100 d-flex flex-column justify-content-center">
                                    <small class="text-muted d-block mb-1">Example</small>
                                    <span class="small fw-semibold" id="observationPeriodPreview">01-Jan-2025 to 31-Dec-2025</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="report-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Report author details</h5>
                                <p class="text-muted mb-0 small">Your profile name is auto-filled. Choose the author type that best describes you.</p>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border">Auto-filled</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="reportAuthorName">Author name</label>
                                <input
                                    type="text"
                                    name="report_author_name"
                                    id="reportAuthorName"
                                    class="form-control bg-light"
                                    value="{{ $reportAuthorName }}"
                                    maxlength="160"
                                    readonly
                                >
                                <small class="text-muted">Taken from your account profile.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="reportAuthorType">Author type <span class="text-danger">*</span></label>
                                <select name="report_author_type" id="reportAuthorType" class="form-select my-area-required">
                                    <option value="">Select author type</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::reportAuthorTypes() as $authorType)
                                        <option value="{{ $authorType }}" @selected(old('report_author_type', data_get($post->meta, 'report_author_type')) === $authorType)>{{ $authorType }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="report-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Organization details</h5>
                                <p class="text-muted mb-0 small">Optional affiliation for institutional, academic, NGO, or government reports.</p>
                            </div>
                            <span class="badge bg-light text-dark border">Optional</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label" for="organizationType">Organization type</label>
                                <select name="organization_type" id="organizationType" class="form-select">
                                    <option value="">Select organization type</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::reportOrganizationTypes() as $organizationType)
                                        <option value="{{ $organizationType }}" @selected(old('organization_type', data_get($post->meta, 'organization_type')) === $organizationType)>{{ $organizationType }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label" for="organizationName">Organization name</label>
                                <input
                                    type="text"
                                    name="organization_name"
                                    id="organizationName"
                                    class="form-control"
                                    value="{{ old('organization_name', data_get($post->meta, 'organization_name')) }}"
                                    maxlength="160"
                                    placeholder="e.g. Soil & Water Research Institute"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="report-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Findings &amp; conclusions</h5>
                                <p class="text-muted mb-0 small">Structure the core narrative of your report from observations through to summary.</p>
                            </div>
                            <span class="badge bg-success-subtle text-success border">Report body</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="report-narrative-field report-narrative-field--findings h-100">
                                    <label class="form-label mb-1" for="reportFindings">Findings</label>
                                    <small class="text-muted d-block mb-2">Main observations</small>
                                    <textarea
                                        name="key_findings"
                                        id="reportFindings"
                                        class="form-control"
                                        rows="6"
                                        maxlength="3000"
                                        placeholder="What did you observe? List the key facts, patterns, and evidence gathered."
                                    >{{ old('key_findings', data_get($post->meta, 'key_findings')) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="report-narrative-field report-narrative-field--analysis h-100">
                                    <label class="form-label mb-1" for="reportAnalysis">Analysis</label>
                                    <small class="text-muted d-block mb-2">Interpretation</small>
                                    <textarea
                                        name="report_analysis"
                                        id="reportAnalysis"
                                        class="form-control"
                                        rows="6"
                                        maxlength="3000"
                                        placeholder="What do these observations mean? Explain causes, context, and implications."
                                    >{{ old('report_analysis', data_get($post->meta, 'report_analysis')) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="report-narrative-field report-narrative-field--recommendations h-100">
                                    <label class="form-label mb-1" for="reportRecommendations">Recommendations</label>
                                    <small class="text-muted d-block mb-2">Suggested solutions</small>
                                    <textarea
                                        name="recommendations"
                                        id="reportRecommendations"
                                        class="form-control"
                                        rows="6"
                                        maxlength="3000"
                                        placeholder="What actions should follow? Propose practical next steps for stakeholders."
                                    >{{ old('recommendations', data_get($post->meta, 'recommendations')) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="report-narrative-field report-narrative-field--conclusion h-100">
                                    <label class="form-label mb-1" for="reportConclusion">Conclusion</label>
                                    <small class="text-muted d-block mb-2">Summary</small>
                                    <textarea
                                        name="report_conclusion"
                                        id="reportConclusion"
                                        class="form-control"
                                        rows="6"
                                        maxlength="3000"
                                        placeholder="Wrap up with a concise summary of outcomes, impact, and final takeaway."
                                    >{{ old('report_conclusion', data_get($post->meta, 'report_conclusion')) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="report-flow-card report-flow-card--action border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Community action</h5>
                                <p class="text-muted mb-0 small">Unique SoilnWater feature — request follow-up action from the right audience.</p>
                            </div>
                            <span class="badge bg-info text-dark">SoilnWater feature</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="actionNeeded">Is action needed?</label>
                                <select name="action_needed" id="actionNeeded" class="form-select">
                                    <option value="">Select option</option>
                                    @foreach(['Yes', 'No'] as $actionNeeded)
                                        <option value="{{ $actionNeeded }}" @selected(old('action_needed', data_get($post->meta, 'action_needed')) === $actionNeeded)>{{ $actionNeeded }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8" id="communityActionDetailsWrap">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label" for="actionRequestedFrom">Action requested from <span class="text-danger action-required-marker" style="display:none;">*</span></label>
                                        <select name="action_requested_from" id="actionRequestedFrom" class="form-select">
                                            <option value="">Select audience</option>
                                            @foreach(\App\Support\CommunityContentTaxonomy::reportActionRequestedFrom() as $actionAudience)
                                                <option value="{{ $actionAudience }}" @selected(old('action_requested_from', data_get($post->meta, 'action_requested_from')) === $actionAudience)>{{ $actionAudience }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label" for="suggestedSolution">Suggested solution</label>
                                        <textarea
                                            name="suggested_solution"
                                            id="suggestedSolution"
                                            class="form-control"
                                            rows="3"
                                            maxlength="2000"
                                            placeholder="Describe the practical solution or action you want taken."
                                        >{{ old('suggested_solution', data_get($post->meta, 'suggested_solution')) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="report-flow-card border rounded-3 p-3 p-md-4 bg-light">
                        <div class="mb-3">
                            <h5 class="mb-1">Evidence files</h5>
                            <p class="text-muted mb-0 small">Upload supporting photos, videos, or documents for this report.</p>
                        </div>
                        <input type="file" name="issue_attachments[]" class="form-control" accept="image/*,video/*,.pdf,.doc,.docx" multiple>
                        <small class="text-muted d-block mt-2">Upload up to 6 photos, videos, or documents. Each file can be up to 20 MB.</small>
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
            @include('backend.community-posts.partials.type-fields')
            @php
                $existingVideo = $post->videoData();
                $videoSourceType = old('video_source_type', $existingVideo['type'] ?? 'none');
            @endphp
            <div class="col-12 common-post-fields">
                <div class="border rounded-3 p-3 bg-white">
                    <h5 class="mb-1">Common settings</h5>
                    <div class="row g-3">
                        <div class="col-12" id="publishAsWrap">
                            <div class="border rounded-3 p-3 bg-light">
                                <label class="form-label mb-2">Publish As <span class="text-danger">*</span></label>
                                <p class="text-muted small mb-3">Choose how your name appears on the public community page when this post is published.</p>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach(\App\Models\CommunityPost::PUBLISH_AS_OPTIONS as $value => $label)
                                        <div class="form-check">
                                            <input
                                                type="radio"
                                                class="form-check-input"
                                                name="publish_as"
                                                id="publishAs{{ \Illuminate\Support\Str::studly($value) }}"
                                                value="{{ $value }}"
                                                @checked(old('publish_as', $post->publish_as ?: \App\Models\CommunityPost::PUBLISH_AS_PUBLIC_PROFILE) === $value)
                                            >
                                            <label class="form-check-label" for="publishAs{{ \Illuminate\Support\Str::studly($value) }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3" id="penNameWrap" style="display:none;">
                                    <label class="form-label" for="penNameInput">Pen name <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="pen_name"
                                        id="penNameInput"
                                        class="form-control"
                                        value="{{ old('pen_name', $post->pen_name) }}"
                                        maxlength="120"
                                        placeholder="Enter the pen name readers will see"
                                    >
                                    <small class="text-muted">This name is shown instead of your public profile name.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded-3 p-3 bg-light">
                                <h6 class="mb-1">Location information</h6>
                                <p class="text-muted small mb-3">Very important for SoilnWater. Choose how broadly this post applies, then add a specific place when needed.</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="communityLocationType">Location type <span class="text-danger">*</span></label>
                                        <select name="location_type" id="communityLocationType" class="form-select" required>
                                            @foreach(\App\Models\CommunityPost::locationTypeOptions(old('content_type', $post->content_type)) as $value => $label)
                                                <option value="{{ $value }}" @selected(old('location_type', $post->location_type ?? \App\Models\CommunityPost::LOCATION_TYPE_GLOBAL) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12" id="communityLocationScopeNote" style="display:none;">
                                        <div class="alert alert-info py-2 px-3 mb-0 small" id="communityLocationScopeText"></div>
                                    </div>
                                    <div class="col-md-6" id="communitySpecificLocationWrap">
                                        <label class="form-label" id="locationLabel">Location <span class="text-danger">*</span></label>
                                        <input type="text" name="location" id="communityLocation" class="form-control" value="{{ old('location', $post->location ?? data_get($post->meta, 'location')) }}" maxlength="160" placeholder="Search and select a location" autocomplete="off">
                                        <small class="text-muted" id="locationHelp">Select a Google Places suggestion so latitude and longitude are saved.</small>
                                    </div>
                                    <div class="col-12" id="communityGpsLocationWrap" style="display:none;">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label" for="communityLocationLat">Latitude <span class="text-muted fw-normal">(optional)</span></label>
                                                <input
                                                    type="number"
                                                    name="location_lat"
                                                    id="communityLocationLat"
                                                    class="form-control"
                                                    value="{{ old('location_lat', $post->location_lat ?? data_get($post->meta, 'location_lat')) }}"
                                                    step="any"
                                                    min="-90"
                                                    max="90"
                                                    placeholder="e.g. 26.9124000"
                                                >
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="communityLocationLng">Longitude <span class="text-muted fw-normal">(optional)</span></label>
                                                <input
                                                    type="number"
                                                    name="location_lng"
                                                    id="communityLocationLng"
                                                    class="form-control"
                                                    value="{{ old('location_lng', $post->location_lng ?? data_get($post->meta, 'location_lng')) }}"
                                                    step="any"
                                                    min="-180"
                                                    max="180"
                                                    placeholder="e.g. 75.7873000"
                                                >
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Map marker <span class="text-muted fw-normal">(optional)</span></label>
                                                @if(!config('services.google.maps_api_key'))
                                                    <div class="alert alert-warning py-2 px-3 mb-2 small">Google Maps is not configured. Add <code>GOOGLE_MAPS_API_KEY</code> to your environment file to enable the map pin.</div>
                                                @endif
                                                <div id="communityGpsMap" class="community-gps-map border rounded bg-white" role="application" aria-label="Optional GPS map pin"></div>
                                                <small class="text-muted d-block mt-2">Click the map to place a pin, drag it to adjust, or enter coordinates manually.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                        <div class="col-12" id="publicParticipationWrap">
                            <div class="border rounded-3 p-3 bg-light">
                                <h5 class="mb-2">Public Participation</h5>
                                <p class="text-muted small mb-3">Choose what logged-in readers can submit on the public post page. The author is notified in the portal and by email for each submission.</p>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="allow_comments" value="1" class="form-check-input" id="allowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
                                    <label class="form-check-label" for="allowComments">Comments</label>
                                    <small class="text-muted d-block">Enable a public discussion thread with comments and replies.</small>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="allow_suggestions" value="1" class="form-check-input" id="allowSuggestions" @checked(old('allow_suggestions', $post->allow_suggestions ?? false))>
                                    <label class="form-check-label" for="allowSuggestions">Suggestions</label>
                                    <small class="text-muted d-block">Readers can recommend actions or improvements.</small>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="allow_feedback" value="1" class="form-check-input" id="allowFeedback" @checked(old('allow_feedback', $post->allow_feedback ?? false))>
                                    <label class="form-check-label" for="allowFeedback">Feedback</label>
                                    <small class="text-muted d-block">Readers can share constructive feedback with the author.</small>
                                </div>
                                <div class="form-check mb-0">
                                    <input type="checkbox" name="allow_additional_evidence" value="1" class="form-check-input" id="allowAdditionalEvidence" @checked(old('allow_additional_evidence', $post->allow_additional_evidence ?? false))>
                                    <label class="form-check-label" for="allowAdditionalEvidence">Additional Evidence</label>
                                    <small class="text-muted d-block">Readers can upload supporting photos or documents.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12" id="allowSharingWrap">
                            <div class="form-check">
                                <input type="checkbox" name="allow_sharing" value="1" class="form-check-input" id="allowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
                                <label class="form-check-label" for="allowSharing">Enable social sharing</label>
                                <small class="text-muted d-block">When enabled, readers can share this post using QR code, copy link, WhatsApp, Facebook, and Instagram.</small>
                            </div>
                        </div>
                        <div class="col-12" id="allowPollWrap">
                            <div class="form-check">
                                <input type="checkbox" name="allow_poll" value="1" class="form-check-input" id="allowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
                                <label class="form-check-label" for="allowPoll">Allow author to add poll</label>
                                <small class="text-muted d-block">When enabled, readers can answer a Yes / No / Not Sure poll on the public post page.</small>
                            </div>
                        </div>
                        <div class="col-12" id="pollSubjectWrap" style="display:none;">
                            <div class="border rounded-3 p-3 bg-light">
                                <label class="form-label" for="pollSubjectInput">Poll subject <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="poll_subject"
                                    id="pollSubjectInput"
                                    class="form-control"
                                    value="{{ old('poll_subject', $post->poll_subject) }}"
                                    maxlength="160"
                                    placeholder="e.g. rainwater harvesting"
                                >
                                <small class="text-muted d-block mt-2">Readers will see:</small>
                                <div class="mt-1 fw-semibold" id="pollQuestionPreview">Do you support …?</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border rounded-3 p-3 bg-light mt-4">
            <h5 class="mb-3">Content responsibility &amp; posting policy</h5>
            <p class="text-muted small mb-3">
                You must accept both statements before submitting. Your account ID, IP address, submission time, and acceptance timestamps are stored for every post.
                <a href="{{ route('frontend.community-posting-policy') }}" target="_blank" rel="noopener">Read the Community Posting Policy</a>.
            </p>

            <div class="form-check mb-3">
                <input
                    class="form-check-input @error('accept_content_responsibility') is-invalid @enderror"
                    type="checkbox"
                    name="accept_content_responsibility"
                    id="acceptContentResponsibility"
                    value="1"
                    @checked(old('accept_content_responsibility'))
                    required>
                <label class="form-check-label" for="acceptContentResponsibility">
                    You are solely responsible for the content you publish. Do not post false information, copyrighted material, personal attacks, or unlawful content. SoilnWater reserves the right to remove any content that violates platform policies.
                </label>
                @error('accept_content_responsibility')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check">
                <input
                    class="form-check-input @error('accept_original_work_indemnity') is-invalid @enderror"
                    type="checkbox"
                    name="accept_original_work_indemnity"
                    id="acceptOriginalWorkIndemnity"
                    value="1"
                    @checked(old('accept_original_work_indemnity'))
                    required>
                <label class="form-check-label" for="acceptOriginalWorkIndemnity">
                    I confirm that this content is my original work or that I have the necessary rights and permissions to publish it. I understand and agree that I am solely responsible for the content I submit, including its accuracy, legality, and compliance with applicable laws. I agree to indemnify and hold harmless SoilnWater, its owners, employees, and affiliates from any claims, damages, liabilities, costs, or legal proceedings arising from my submitted content.
                </label>
                @error('accept_original_work_indemnity')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
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
    #bodyEditorMount .ck-editor__editable_inline {
        min-height: 320px;
    }

    #bodyEditorMount .ck.ck-editor {
        width: 100%;
    }

    .community-editor-language-select {
        max-width: 220px;
        min-width: 160px;
    }

    .ck-editor__editable.ck-content[lang="hi"] {
        font-family: "Noto Sans Devanagari", "Nirmala UI", "Mangal", sans-serif;
    }

    .community-book-editor .book-page-tab {
        border: 1px solid #cfd8e3;
        border-radius: 999px;
        font-weight: 600;
        padding: .35rem .85rem;
    }

    .community-book-editor .book-page-tab.active {
        background: #0f766e;
        border-color: #0f766e;
        color: #fff;
    }

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
    .community-gps-map {
        height: 320px;
        min-height: 240px;
        width: 100%;
    }
    .report-flow-card h5 {
        font-size: 1.05rem;
    }
    .report-period-preview {
        min-height: calc(1.5em + 0.75rem + 2px);
    }
    .report-narrative-field {
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 0.75rem;
        padding: 1rem;
        background: #f8fafc;
    }
    .report-narrative-field textarea {
        resize: vertical;
        min-height: 150px;
        background: #fff;
    }
    .report-narrative-field--findings {
        border-top: 3px solid #0d6efd;
    }
    .report-narrative-field--analysis {
        border-top: 3px solid #6f42c1;
    }
    .report-narrative-field--recommendations {
        border-top: 3px solid #198754;
    }
    .report-narrative-field--conclusion {
        border-top: 3px solid #fd7e14;
    }
    .report-flow-card--action {
        border-left: 4px solid #0dcaf0 !important;
        background: linear-gradient(180deg, #f8fdff 0%, #ffffff 100%);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    window.communityTypes = @json($types);
    window.communityBookTypes = @json(\App\Models\CommunityPost::BOOK_CONTENT_TYPES);
    window.communityBookPages = @json(collect($initialBookPages)->map(fn ($page) => [
        'content' => is_array($page) ? ($page['content'] ?? '') : (string) $page,
        'language' => is_array($page) ? ($page['language'] ?? 'en') : 'en',
    ])->values());
    window.communityBodyEditor = null;
    window.communityActiveBookPage = 0;
    const COMMUNITY_EDITOR_LANGUAGES = {
        en: { label: 'English', lang: 'en' },
        hi: { label: 'Hindi', lang: 'hi' },
    };

    function normalizeEditorLanguage(code) {
        return COMMUNITY_EDITOR_LANGUAGES[code] ? code : 'en';
    }

    function getActiveEditorLanguage() {
        const select = document.getElementById('editorLanguageSelect');
        return select ? normalizeEditorLanguage(select.value) : 'en';
    }

    function saveActiveEditorLanguage() {
        const language = getActiveEditorLanguage();
        const hidden = document.getElementById('editorLanguageHidden');

        if (hidden) {
            hidden.value = language;
        }

        if (isBookContentType(document.getElementById('contentType').value)) {
            window.communityBookPages[window.communityActiveBookPage] = window.communityBookPages[window.communityActiveBookPage] || { content: '', language: 'en' };
            window.communityBookPages[window.communityActiveBookPage].language = language;
        }
    }

    function applyEditorLanguage(languageCode, options) {
        const settings = options || {};
        const language = normalizeEditorLanguage(languageCode);
        const select = document.getElementById('editorLanguageSelect');
        const hidden = document.getElementById('editorLanguageHidden');

        if (select) {
            select.value = language;
        }

        if (hidden) {
            hidden.value = language;
        }

        if (window.communityBodyEditor) {
            const root = window.communityBodyEditor.editing.view.getDomRoot();

            if (root) {
                root.setAttribute('lang', COMMUNITY_EDITOR_LANGUAGES[language].lang);
                root.setAttribute('dir', 'ltr');
            }
        }

        if (!settings.skipSave) {
            saveActiveEditorLanguage();
        }
    }

    document.getElementById('editorLanguageSelect')?.addEventListener('change', function () {
        applyEditorLanguage(this.value);
        window.communityBodyEditor?.editing.view.focus();
    });
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

    const COMMUNITY_LOCATION_TYPES_REQUIRING_PLACE = @json(\App\Models\CommunityPost::locationTypesRequiringPlace());
    const COMMUNITY_LOCATION_TYPE_GPS = @json(\App\Models\CommunityPost::LOCATION_TYPE_GPS);
    const COMMUNITY_BASE_LOCATION_TYPES = @json(\App\Models\CommunityPost::locationTypeOptions());
    let communityGpsMap = null;
    let communityGpsMarker = null;
    let communityGpsMapInitialized = false;
    let communityGpsInputListenersBound = false;

    function requiresSpecificCommunityLocation(type) {
        return COMMUNITY_LOCATION_TYPES_REQUIRING_PLACE.includes(type);
    }

    function isGpsCommunityLocation(type) {
        return type === COMMUNITY_LOCATION_TYPE_GPS;
    }

    function refreshCommunityLocationTypeOptions(isReport) {
        const typeSelect = document.getElementById('communityLocationType');
        if (!typeSelect) {
            return;
        }

        const selected = typeSelect.value;
        const options = { ...COMMUNITY_BASE_LOCATION_TYPES };

        if (isReport) {
            options[COMMUNITY_LOCATION_TYPE_GPS] = 'GPS Location';
        }

        typeSelect.innerHTML = '';
        Object.entries(options).forEach(([value, label]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            option.selected = value === selected;
            typeSelect.appendChild(option);
        });

        if (!Object.prototype.hasOwnProperty.call(options, selected)) {
            typeSelect.value = 'global';
        }
    }

    function updateCommunityGpsInputs(lat, lng) {
        const latInput = document.getElementById('communityLocationLat');
        const lngInput = document.getElementById('communityLocationLng');

        if (latInput) {
            latInput.value = Number(lat).toFixed(7);
        }

        if (lngInput) {
            lngInput.value = Number(lng).toFixed(7);
        }
    }

    function placeCommunityGpsMarker(lat, lng, centerMap = true) {
        updateCommunityGpsInputs(lat, lng);

        const position = { lat, lng };

        if (!communityGpsMarker) {
            communityGpsMarker = new google.maps.Marker({
                position,
                map: communityGpsMap,
                draggable: true,
            });

            communityGpsMarker.addListener('dragend', function (event) {
                updateCommunityGpsInputs(event.latLng.lat(), event.latLng.lng());
            });
        } else {
            communityGpsMarker.setPosition(position);
            communityGpsMarker.setMap(communityGpsMap);
        }

        if (centerMap && communityGpsMap) {
            communityGpsMap.panTo(position);
        }
    }

    function syncCommunityGpsMarkerFromInputs() {
        const lat = parseFloat(document.getElementById('communityLocationLat')?.value);
        const lng = parseFloat(document.getElementById('communityLocationLng')?.value);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            if (communityGpsMarker) {
                communityGpsMarker.setMap(null);
                communityGpsMarker = null;
            }

            return;
        }

        if (!communityGpsMap) {
            initCommunityGpsMap();
        }

        placeCommunityGpsMarker(lat, lng);
    }

    function queueCommunityGpsMapResize() {
        if (!communityGpsMap || !window.google?.maps) {
            return;
        }

        window.setTimeout(function () {
            google.maps.event.trigger(communityGpsMap, 'resize');

            const lat = parseFloat(document.getElementById('communityLocationLat')?.value);
            const lng = parseFloat(document.getElementById('communityLocationLng')?.value);

            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                communityGpsMap.setCenter({ lat, lng });
            }
        }, 200);
    }

    function initCommunityGpsMap() {
        const mapEl = document.getElementById('communityGpsMap');
        const gpsWrap = document.getElementById('communityGpsLocationWrap');
        const locationType = document.getElementById('communityLocationType')?.value;
        const contentType = document.getElementById('contentType')?.value;

        if (!mapEl || !gpsWrap || !isGpsCommunityLocation(locationType) || contentType !== 'reports' || !window.google?.maps) {
            return;
        }

        if (gpsWrap.style.display === 'none') {
            return;
        }

        const latInput = document.getElementById('communityLocationLat');
        const lngInput = document.getElementById('communityLocationLng');
        const defaultCenter = { lat: 20.5937, lng: 78.9629 };
        const initialLat = parseFloat(latInput?.value);
        const initialLng = parseFloat(lngInput?.value);
        const hasCoords = Number.isFinite(initialLat) && Number.isFinite(initialLng);

        const renderMap = function () {
            if (!communityGpsMapInitialized) {
                communityGpsMap = new google.maps.Map(mapEl, {
                    center: hasCoords ? { lat: initialLat, lng: initialLng } : defaultCenter,
                    zoom: hasCoords ? 14 : 5,
                    mapTypeControl: true,
                    streetViewControl: false,
                    fullscreenControl: true,
                });

                communityGpsMap.addListener('click', function (event) {
                    placeCommunityGpsMarker(event.latLng.lat(), event.latLng.lng());
                });

                if (hasCoords) {
                    placeCommunityGpsMarker(initialLat, initialLng, false);
                }

                communityGpsMapInitialized = true;
            } else if (communityGpsMap) {
                google.maps.event.trigger(communityGpsMap, 'resize');
                if (hasCoords) {
                    communityGpsMap.setCenter({ lat: initialLat, lng: initialLng });
                }
            }

            if (!communityGpsInputListenersBound) {
                latInput?.addEventListener('change', syncCommunityGpsMarkerFromInputs);
                lngInput?.addEventListener('change', syncCommunityGpsMarkerFromInputs);
                latInput?.addEventListener('input', syncCommunityGpsMarkerFromInputs);
                lngInput?.addEventListener('input', syncCommunityGpsMarkerFromInputs);
                communityGpsInputListenersBound = true;
            }

            queueCommunityGpsMapResize();
        };

        window.requestAnimationFrame(function () {
            window.setTimeout(renderMap, 150);
        });
    }

    function refreshCommunityLocationFields(fallbackHelp) {
        const typeSelect = document.getElementById('communityLocationType');
        const specificWrap = document.getElementById('communitySpecificLocationWrap');
        const gpsWrap = document.getElementById('communityGpsLocationWrap');
        const scopeNote = document.getElementById('communityLocationScopeNote');
        const scopeText = document.getElementById('communityLocationScopeText');
        const locationInput = document.getElementById('communityLocation');
        const locationHelp = document.getElementById('locationHelp');

        if (!typeSelect || !specificWrap) {
            return;
        }

        const locationType = typeSelect.value;
        const contentType = document.getElementById('contentType')?.value || '';
        const needsSpecific = requiresSpecificCommunityLocation(locationType);
        const showGpsPanel = isGpsCommunityLocation(locationType) && contentType === 'reports';

        specificWrap.style.display = needsSpecific ? '' : 'none';

        if (gpsWrap) {
            gpsWrap.style.display = showGpsPanel ? '' : 'none';
        }

        if (locationInput) {
            locationInput.required = needsSpecific;
        }

        if (showGpsPanel) {
            initCommunityGpsMap();
        }

        const helpText = {
            state: 'Search and select the state-level location from Google Places.',
            district: 'Search and select the district-level location from Google Places.',
            city: 'Search and select the city from Google Places so the story is location-indexed.',
            village: 'Search and select the village or locality from Google Places.',
        };

        if (locationHelp) {
            if (!needsSpecific) {
                locationHelp.textContent = '';
            } else if (contentType === 'reports') {
                locationHelp.textContent = 'Select the exact issue location from Google Places so the problem can be mapped.';
            } else if (contentType === 'news') {
                locationHelp.textContent = 'Select the news location from Google Places so the story is location-indexed.';
            } else {
                locationHelp.textContent = helpText[locationType] || fallbackHelp || 'Select a Google Places suggestion so latitude and longitude are saved.';
            }
        }

        if (scopeNote && scopeText) {
            if (locationType === 'global') {
                scopeNote.style.display = '';
                scopeText.textContent = 'This post will be treated as global. No specific GPS location is required.';
            } else if (locationType === 'india') {
                scopeNote.style.display = '';
                scopeText.textContent = 'This post applies across India. No specific GPS location is required.';
            } else if (showGpsPanel) {
                scopeNote.style.display = '';
                scopeText.textContent = 'GPS location is optional. Pin the report on the map or enter latitude and longitude manually.';
            } else {
                scopeNote.style.display = 'none';
                scopeText.textContent = '';
            }
        }
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

        const categoryLabel = document.getElementById('categoryLabel');

        categorySelect.required = true;
        categorySelect.disabled = false;
        categoryWrap.style.display = '';

        if (categoryLabel) {
            categoryLabel.innerHTML = 'Category <span class="text-danger">*</span>';
        }

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

        refreshCommunityLocationTypeOptions(isReport);
        refreshBookLayoutMode(selectedType);
        refreshCommunityLocationFields(fieldCopy.locationHelp);

        if (typeof refreshCommunityActionFields === 'function') {
            refreshCommunityActionFields();
        }

        const allowPollWrap = document.getElementById('allowPollWrap');
        const allowPoll = document.getElementById('allowPoll');
        if (allowPollWrap) {
            allowPollWrap.style.display = isReport ? 'none' : '';
        }
        if (isReport && allowPoll) {
            allowPoll.checked = false;
        }
        if (typeof refreshPollFields === 'function') {
            refreshPollFields();
        }
    }

    function isBookContentType(type) {
        return (window.communityBookTypes || []).includes(type);
    }

    function saveActiveBookPageContent() {
        if (!window.communityBodyEditor || !isBookContentType(document.getElementById('contentType').value)) {
            return;
        }

        window.communityBookPages[window.communityActiveBookPage] = window.communityBookPages[window.communityActiveBookPage] || { content: '', language: 'en' };
        window.communityBookPages[window.communityActiveBookPage].content = window.communityBodyEditor.getData();
        saveActiveEditorLanguage();
        document.getElementById('bodyEditor').value = window.communityBookPages[window.communityActiveBookPage].content;
    }

    function renderBookPageTabs() {
        const tabs = document.getElementById('bookPageTabs');
        const removeBtn = document.getElementById('removeBookPageBtn');
        if (!tabs) {
            return;
        }

        tabs.innerHTML = '';
        window.communityBookPages.forEach(function (page, index) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm book-page-tab' + (index === window.communityActiveBookPage ? ' active' : '');
            button.textContent = 'Page ' + (index + 1);
            button.addEventListener('click', function () {
                switchBookPage(index);
            });
            tabs.appendChild(button);
        });

        if (removeBtn) {
            removeBtn.style.display = window.communityBookPages.length > 1 ? '' : 'none';
        }

        const title = document.getElementById('activeBookPageTitle');
        if (title) {
            title.textContent = 'Page ' + (window.communityActiveBookPage + 1);
        }
    }

    function switchBookPage(index) {
        if (!window.communityBodyEditor) {
            return;
        }

        saveActiveBookPageContent();
        window.communityActiveBookPage = index;
        window.communityBookPages[index] = window.communityBookPages[index] || { content: '', language: 'en' };
        window.communitySwitchingBookPage = true;
        window.communityBodyEditor.setData(window.communityBookPages[index].content || '');
        window.communitySwitchingBookPage = false;
        applyEditorLanguage(window.communityBookPages[index].language || 'en', { skipSave: true });
        renderBookPageTabs();
        window.communityBodyEditor.editing.view.focus();
    }

    function refreshBookLayoutMode(selectedType) {
        const bookMode = isBookContentType(selectedType);
        const standardHeader = document.getElementById('standardBodyHeader');
        const bookHeader = document.getElementById('bookBodyHeader');
        const editorField = document.getElementById('bodyEditor');

        if (standardHeader) {
            standardHeader.style.display = bookMode ? 'none' : '';
        }

        if (bookHeader) {
            bookHeader.style.display = bookMode ? '' : 'none';
        }

        if (!bookMode) {
            if (window.communityBodyEditor) {
                saveActiveBookPageContent();
                const mergedBody = window.communityBookPages
                    .map(function (page) { return page.content || ''; })
                    .filter(function (content) { return content.trim() !== ''; })
                    .join('<hr>');

                if (mergedBody) {
                    window.communityBodyEditor.setData(mergedBody);
                    editorField.value = mergedBody;
                }
            }

            return;
        }

        if (!window.communityBodyEditor) {
            return;
        }

        const bookHelp = document.getElementById('bookBodyHelp');
        if (bookHelp) {
            bookHelp.textContent = 'Write each page using the editor below. Switch tabs to edit Page 1, Page 2, and so on.';
        }

        if (!Array.isArray(window.communityBookPages) || window.communityBookPages.length === 0) {
            window.communityBookPages = [{ content: editorField.value || '', language: getActiveEditorLanguage() }];
        }

        window.communityActiveBookPage = Math.min(window.communityActiveBookPage, window.communityBookPages.length - 1);
        renderBookPageTabs();
        window.communityBodyEditor.setData(window.communityBookPages[window.communityActiveBookPage].content || '');
        applyEditorLanguage(window.communityBookPages[window.communityActiveBookPage].language || 'en', { skipSave: true });
    }

    function appendBookPagesToFormData(formData) {
        saveActiveBookPageContent();
        formData.delete('body');
        formData.delete('book_pages');

        window.communityBookPages.forEach(function (page, index) {
            formData.append('book_pages[' + index + '][content]', page.content || '');
            formData.append('book_pages[' + index + '][language]', normalizeEditorLanguage(page.language || 'en'));
        });

        formData.append('body', window.communityBookPages.map(function (page) {
            return page.content || '';
        }).join('\n'));
    }

    document.getElementById('addBookPageBtn')?.addEventListener('click', function () {
        saveActiveBookPageContent();
        window.communityBookPages.push({ content: '', language: getActiveEditorLanguage() });
        switchBookPage(window.communityBookPages.length - 1);
        window.communityBodyEditor?.editing.view.focus();
    });

    document.getElementById('removeBookPageBtn')?.addEventListener('click', function () {
        if (window.communityBookPages.length <= 1) {
            return;
        }

        window.communityBookPages.splice(window.communityActiveBookPage, 1);
        window.communityActiveBookPage = Math.max(0, window.communityActiveBookPage - 1);
        renderBookPageTabs();
        window.communityBodyEditor?.setData(window.communityBookPages[window.communityActiveBookPage].content || '');
    });

    document.getElementById('contentType').addEventListener('change', function () {
        document.getElementById('categorySelect').dataset.selected = '';
        refreshCommunityCategories();
        if (window.communityBodyEditor) {
            refreshBookLayoutMode(this.value);
        }
    });

    document.getElementById('communityLocationType')?.addEventListener('change', function () {
        refreshCommunityLocationFields();
    });

    function refreshPublishAsFields() {
        const statusSelect = document.getElementById('communityPostStatus');
        const publishWrap = document.getElementById('publishAsWrap');
        const penNameWrap = document.getElementById('penNameWrap');
        const penNameInput = document.getElementById('penNameInput');

        if (!statusSelect || !publishWrap) {
            return;
        }

        const isPublishing = statusSelect.value === 'published';
        publishWrap.style.display = isPublishing ? '' : 'none';

        document.querySelectorAll('input[name="publish_as"]').forEach((input) => {
            input.required = isPublishing;
            input.disabled = !isPublishing;
        });

        const selectedPublishAs = document.querySelector('input[name="publish_as"]:checked')?.value || 'public_profile';
        const showPenName = isPublishing && selectedPublishAs === 'pen_name';

        if (penNameWrap) {
            penNameWrap.style.display = showPenName ? '' : 'none';
        }

        if (penNameInput) {
            penNameInput.required = showPenName;
            penNameInput.disabled = !showPenName;
        }
    }

    document.getElementById('communityPostStatus')?.addEventListener('change', refreshPublishAsFields);
    document.querySelectorAll('input[name="publish_as"]').forEach((input) => {
        input.addEventListener('change', refreshPublishAsFields);
    });
    refreshPublishAsFields();

    function refreshPollFields() {
        const allowPoll = document.getElementById('allowPoll');
        const subjectWrap = document.getElementById('pollSubjectWrap');
        const subjectInput = document.getElementById('pollSubjectInput');
        const preview = document.getElementById('pollQuestionPreview');

        if (!allowPoll || !subjectWrap) {
            return;
        }

        const enabled = allowPoll.checked;
        subjectWrap.style.display = enabled ? '' : 'none';

        if (subjectInput) {
            subjectInput.required = enabled;
            subjectInput.disabled = !enabled;
        }

        if (preview) {
            const subject = subjectInput?.value.trim();
            preview.textContent = subject
                ? 'Do you support ' + subject + '?'
                : 'Do you support …?';
        }
    }

    document.getElementById('allowPoll')?.addEventListener('change', refreshPollFields);
    document.getElementById('pollSubjectInput')?.addEventListener('input', refreshPollFields);
    refreshPollFields();

    function formatObservationDate(value) {
        if (!value) {
            return '';
        }

        const date = new Date(value + 'T00:00:00');
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).replace(/ /g, '-');
    }

    function refreshObservationPeriodPreview() {
        const preview = document.getElementById('observationPeriodPreview');
        const fromInput = document.getElementById('observationPeriodFrom');
        const toInput = document.getElementById('observationPeriodTo');

        if (!preview || !fromInput || !toInput) {
            return;
        }

        const fromLabel = formatObservationDate(fromInput.value);
        const toLabel = formatObservationDate(toInput.value);

        if (fromLabel && toLabel) {
            preview.textContent = fromLabel + ' to ' + toLabel;
            return;
        }

        if (fromLabel) {
            preview.textContent = fromLabel + ' to …';
            return;
        }

        if (toLabel) {
            preview.textContent = '… to ' + toLabel;
            return;
        }

        preview.textContent = '01-Jan-2025 to 31-Dec-2025';
    }

    document.getElementById('observationPeriodFrom')?.addEventListener('change', refreshObservationPeriodPreview);
    document.getElementById('observationPeriodTo')?.addEventListener('change', refreshObservationPeriodPreview);
    refreshObservationPeriodPreview();

    function refreshCommunityActionFields() {
        const actionNeeded = document.getElementById('actionNeeded')?.value || '';
        const detailsWrap = document.getElementById('communityActionDetailsWrap');
        const actionRequestedFrom = document.getElementById('actionRequestedFrom');
        const suggestedSolution = document.getElementById('suggestedSolution');
        const requiredMarker = document.querySelector('.action-required-marker');
        const needsAction = actionNeeded === 'Yes';

        if (detailsWrap) {
            detailsWrap.style.display = needsAction ? '' : 'none';
        }

        if (actionRequestedFrom) {
            actionRequestedFrom.required = needsAction;
            actionRequestedFrom.disabled = !needsAction;
        }

        if (suggestedSolution) {
            suggestedSolution.disabled = !needsAction;
        }

        if (requiredMarker) {
            requiredMarker.style.display = needsAction ? '' : 'none';
        }

        if (!needsAction) {
            if (actionRequestedFrom) {
                actionRequestedFrom.value = '';
            }
            if (suggestedSolution) {
                suggestedSolution.value = '';
            }
        }
    }

    document.getElementById('actionNeeded')?.addEventListener('change', refreshCommunityActionFields);

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
        .then((editor) => {
            window.communityBodyEditor = editor;
            const contentType = document.getElementById('contentType').value;
            let initialLanguage = document.getElementById('editorLanguageHidden')?.value || 'en';

            if (isBookContentType(contentType)) {
                initialLanguage = window.communityBookPages[window.communityActiveBookPage]?.language || 'en';
            }

            applyEditorLanguage(initialLanguage, { skipSave: true });
            editor.model.document.on('change:data', function () {
                if (window.communitySwitchingBookPage) {
                    return;
                }

                if (isBookContentType(document.getElementById('contentType').value)) {
                    saveActiveBookPageContent();
                }
            });
            refreshBookLayoutMode(document.getElementById('contentType').value);
        })
        .catch((error) => {
            console.error(error);
            notify('error', 'Unable to load the body editor.');
        });

    const tagInput = document.getElementById('tagInput');
    const tagList = document.getElementById('tagList');
    const tagsHidden = document.getElementById('tagsHidden');
    const tagsCount = document.getElementById('communityTagsCount');
    const MAX_COMMUNITY_TAGS = 10;
    let tags = (tagsHidden.value || '').split(',').map((tag) => tag.trim()).filter(Boolean).slice(0, MAX_COMMUNITY_TAGS);

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

        if (tagsCount) {
            tagsCount.textContent = tags.length + ' / ' + MAX_COMMUNITY_TAGS;
        }

        if (tagInput) {
            tagInput.disabled = tags.length >= MAX_COMMUNITY_TAGS;
            tagInput.placeholder = tags.length >= MAX_COMMUNITY_TAGS
                ? 'Maximum of 10 tags reached'
                : 'Type a tag and press Enter or comma';
        }

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
        if (tags.length >= MAX_COMMUNITY_TAGS) {
            notify('error', 'You can add up to ' + MAX_COMMUNITY_TAGS + ' tags only.');
            tagInput.value = '';
            return;
        }

        const nextTags = tagInput.value.split(',').map((tag) => tag.trim()).filter(Boolean);
        let limitReached = false;

        nextTags.forEach((tag) => {
            if (tags.length >= MAX_COMMUNITY_TAGS) {
                limitReached = true;
                return;
            }

            if (!tags.map((item) => item.toLowerCase()).includes(tag.toLowerCase())) {
                tags.push(tag);
            }
        });

        if (limitReached) {
            notify('error', 'You can add up to ' + MAX_COMMUNITY_TAGS + ' tags only.');
        }

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
                if (latitudeInput) latitudeInput.value = '';
                if (longitudeInput) longitudeInput.value = '';
                return;
            }
            locationInput.value = place.formatted_address || locationInput.value;
            if (latitudeInput) latitudeInput.value = place.geometry.location.lat().toFixed(7);
            if (longitudeInput) longitudeInput.value = place.geometry.location.lng().toFixed(7);
        });

        locationInput.addEventListener('input', function () {
            if (latitudeInput) latitudeInput.value = '';
            if (longitudeInput) longitudeInput.value = '';
        });
    };

    window.initCommunityPostMaps = function () {
        window.initCommunityPostLocationAutocomplete();

        if (
            document.getElementById('contentType')?.value === 'reports'
            && isGpsCommunityLocation(document.getElementById('communityLocationType')?.value)
        ) {
            initCommunityGpsMap();
        }
    };

    document.getElementById('community-post-form').addEventListener('submit', function (event) {
        event.preventDefault();
        const form = event.currentTarget;
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonHtml = submitButton.innerHTML;

        if (window.communityBodyEditor) {
            if (isBookContentType(document.getElementById('contentType').value)) {
                saveActiveBookPageContent();
                const hasBookContent = window.communityBookPages.some(function (page) {
                    return (page.content || '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim() !== '';
                });

                if (!hasBookContent) {
                    notify('error', 'Please add content to at least one book page.');
                    window.communityBodyEditor.editing.view.focus();
                    return;
                }
            } else {
                document.getElementById('bodyEditor').value = window.communityBodyEditor.getData();
                const bodyText = document.getElementById('bodyEditor').value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                if (!bodyText) {
                    notify('error', 'Please enter content in the body field.');
                    window.communityBodyEditor.editing.view.focus();
                    return;
                }
            }
        }

        saveActiveEditorLanguage();
        addTagsFromInput();

        const locationType = document.getElementById('communityLocationType')?.value || 'global';
        if (requiresSpecificCommunityLocation(locationType)) {
            if (!document.getElementById('communityLocationLat').value || !document.getElementById('communityLocationLng').value) {
                notify('error', 'Please select a location from the Google Places suggestions.');
                return;
            }
        }

        const postStatus = document.getElementById('communityPostStatus')?.value || 'draft';
        if (postStatus === 'published') {
            const publishAs = document.querySelector('input[name="publish_as"]:checked')?.value;
            if (!publishAs) {
                notify('error', 'Please choose how you want to publish this post.');
                return;
            }

            if (publishAs === 'pen_name' && !document.getElementById('penNameInput')?.value.trim()) {
                notify('error', 'Please enter a pen name.');
                return;
            }
        }

        if (document.getElementById('allowPoll')?.checked && !document.getElementById('pollSubjectInput')?.value.trim()) {
            notify('error', 'Please enter the poll subject.');
            return;
        }

        if (!document.getElementById('acceptContentResponsibility')?.checked || !document.getElementById('acceptOriginalWorkIndemnity')?.checked) {
            notify('error', 'Please accept both content responsibility statements before submitting.');
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
        if (isBookContentType(document.getElementById('contentType').value)) {
            appendBookPagesToFormData(formData);
        }
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
@if(config('services.google.maps_api_key'))
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initCommunityPostMaps"></script>
@else
<script>
    console.warn('Google Maps API key is missing. Set GOOGLE_MAPS_API_KEY in your environment file.');
</script>
@endif
@endpush
