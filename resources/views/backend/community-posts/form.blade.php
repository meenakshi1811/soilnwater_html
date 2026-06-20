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
                <small id="categoryHelp" class="text-muted d-block mt-1"></small>
            </div>
            <div class="col-md-6" id="subCategoryFieldWrap" style="display:none;">
                <label class="form-label" id="subCategoryLabel">Sub Category <span class="text-danger">*</span></label>
                <select name="sub_category" id="subCategorySelect" class="form-select poetry-required" data-selected="{{ old('sub_category', data_get($post->meta, 'sub_category')) }}">
                    <option value="">Select sub category</option>
                    @foreach(\App\Support\CommunityContentTaxonomy::poetrySubCategories() as $subCategory)
                        <option value="{{ $subCategory }}" @selected(old('sub_category', data_get($post->meta, 'sub_category')) === $subCategory)>{{ $subCategory }}</option>
                    @endforeach
                </select>
                <small id="subCategoryHelp" class="text-muted d-block mt-1"></small>
            </div>
            <div class="col-12 type-extra" data-for="stories">
                <div class="news-classification-card border rounded-3 p-3 p-md-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">
                        <div>
                            <h5 class="mb-1">Story classification</h5>
                            <p class="text-muted mb-0 small">Choose the story format before writing your narrative.</p>
                        </div>
                        <span class="badge bg-primary text-white">Stories only</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="news-classification-panel h-100">
                                <div class="news-classification-panel__icon">
                                    <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                                </div>
                                <div class="news-classification-panel__copy">
                                    <h6 class="news-classification-panel__title">Story type <span class="text-danger">*</span></h6>
                                    <p class="news-classification-panel__hint">Very important.</p>
                                </div>
                                <select name="story_type" class="form-select stories-required">
                                    <option value="">Select story type</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::storyTypeGroups() as $groupLabel => $groupOptions)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach($groupOptions as $storyType)
                                                <option value="{{ $storyType }}" @selected(old('story_type', data_get($post->meta, 'story_type')) === $storyType)>{{ $storyType }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="news-classification-panel h-100">
                                <div class="news-classification-panel__icon">
                                    <i class="fa-solid fa-language" aria-hidden="true"></i>
                                </div>
                                <div class="news-classification-panel__copy">
                                    <h6 class="news-classification-panel__title">Language <span class="text-danger">*</span></h6>
                                    <p class="news-classification-panel__hint">Story language.</p>
                                </div>
                                <select name="story_language" class="form-select stories-required">
                                    <option value="">Select language</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::storyLanguages() as $storyLanguage)
                                        <option value="{{ $storyLanguage }}" @selected(old('story_language', data_get($post->meta, 'story_language')) === $storyLanguage)>{{ $storyLanguage }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 type-extra" data-for="poetry">
                <div class="news-classification-card border rounded-3 p-3 p-md-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">
                        <div>
                            <h5 class="mb-1">Poetry classification</h5>
                            <p class="text-muted mb-0 small">Choose the poetry format before writing your poem.</p>
                        </div>
                        <span class="badge bg-primary text-white">Poetry only</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="news-classification-panel h-100">
                                <div class="news-classification-panel__icon">
                                    <i class="fa-solid fa-feather-pointed" aria-hidden="true"></i>
                                </div>
                                <div class="news-classification-panel__copy">
                                    <h6 class="news-classification-panel__title">Poetry type <span class="text-danger">*</span></h6>
                                    <p class="news-classification-panel__hint">Very important.</p>
                                </div>
                                <select name="poetry_type" class="form-select poetry-required">
                                    <option value="">Select poetry type</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::poetryTypeGroups() as $groupLabel => $groupOptions)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach($groupOptions as $poetryType)
                                                <option value="{{ $poetryType }}" @selected(old('poetry_type', data_get($post->meta, 'poetry_type')) === $poetryType)>{{ $poetryType }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="news-classification-panel h-100">
                                <div class="news-classification-panel__icon">
                                    <i class="fa-solid fa-language" aria-hidden="true"></i>
                                </div>
                                <div class="news-classification-panel__copy">
                                    <h6 class="news-classification-panel__title">Poem language <span class="text-danger">*</span></h6>
                                    <p class="news-classification-panel__hint">Language of the poem.</p>
                                </div>
                                <select name="poem_language" class="form-select poetry-required">
                                    <option value="">Select poem language</option>
                                    @foreach(['Hindi', 'English', 'Urdu', 'Regional', 'Multilingual'] as $poemLanguage)
                                        <option value="{{ $poemLanguage }}" @selected(old('poem_language', data_get($post->meta, 'poem_language')) === $poemLanguage)>{{ $poemLanguage }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label" for="dedication">Dedication</label>
                            <input
                                type="text"
                                name="dedication"
                                id="dedication"
                                class="form-control"
                                value="{{ old('dedication', data_get($post->meta, 'dedication')) }}"
                                maxlength="160"
                                placeholder="Optional dedication line"
                            >
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="reading_time">Estimated reading time (minutes)</label>
                            <input
                                type="text"
                                name="reading_time"
                                id="reading_time"
                                class="form-control"
                                value="{{ old('reading_time', data_get($post->meta, 'reading_time')) }}"
                                maxlength="10"
                                placeholder="e.g. 3"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 type-extra life-story-flow-section" data-for="biography,autobiography">
                <div class="news-classification-card border rounded-3 p-3 p-md-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">
                        <div>
                            <h5 class="mb-1">Biography / Autobiography classification</h5>
                            <p class="text-muted mb-0 small">Choose the life-story format before writing the content.</p>
                        </div>
                        <span class="badge bg-primary text-white">Biography &amp; Autobiography</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="news-classification-panel h-100">
                                <div class="news-classification-panel__icon">
                                    <i class="fa-solid fa-book-open-reader" aria-hidden="true"></i>
                                </div>
                                <div class="news-classification-panel__copy">
                                    <h6 class="news-classification-panel__title">Life story format <span class="text-danger">*</span></h6>
                                    <p class="news-classification-panel__hint">Very important.</p>
                                </div>
                                <select name="autobiography_type" class="form-select autobiography-required">
                                    <option value="">Select life story format</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::autobiographyTypes() as $autobiographyType)
                                        <option value="{{ $autobiographyType }}" @selected(old('autobiography_type', data_get($post->meta, 'autobiography_type')) === $autobiographyType)>{{ $autobiographyType }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 type-extra childrens-corner-flow-section" data-for="childrens-corner">
                <div class="news-classification-card border rounded-3 p-3 p-md-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">
                        <div>
                            <h5 class="mb-1">Children's Corner classification</h5>
                            <p class="text-muted mb-0 small">Choose what the child would like to share.</p>
                        </div>
                        <span class="badge bg-primary text-white">Children's Corner only</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="news-classification-panel h-100">
                                <div class="news-classification-panel__icon">
                                    <i class="fa-solid fa-child-reaching" aria-hidden="true"></i>
                                </div>
                                <div class="news-classification-panel__copy">
                                    <h6 class="news-classification-panel__title">What would you like to share? <span class="text-danger">*</span></h6>
                                    <p class="news-classification-panel__hint">Story, poem, drawing, project, quiz, and more.</p>
                                </div>
                                <select name="child_share_type" id="childShareType" class="form-select childrens-corner-required">
                                    <option value="">Select share type</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerShareTypeGroups() as $groupLabel => $shareTypes)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach($shareTypes as $shareType)
                                                <option value="{{ $shareType }}" @selected(old('child_share_type', data_get($post->meta, 'child_share_type', $post->category)) === $shareType)>{{ $shareType }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 type-extra" data-for="news">
                <div class="news-classification-card border rounded-3 p-3 p-md-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">
                        <div>
                            <h5 class="mb-1">News classification</h5>
                            <p class="text-muted mb-0 small">Choose the news format and geographic scope before writing the story.</p>
                        </div>
                        <span class="badge bg-primary text-white">News only</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="news-classification-panel h-100">
                                <div class="news-classification-panel__icon">
                                    <i class="fa-solid fa-newspaper" aria-hidden="true"></i>
                                </div>
                                <div class="news-classification-panel__copy">
                                    <h6 class="news-classification-panel__title">News type <span class="text-danger">*</span></h6>
                                    <p class="news-classification-panel__hint">Very important.</p>
                                </div>
                                <select name="news_type" class="form-select news-required">
                                    <option value="">Select news type</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::newsTypeGroups() as $groupLabel => $groupOptions)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach($groupOptions as $newsType)
                                                <option value="{{ $newsType }}" @selected(old('news_type', data_get($post->meta, 'news_type')) === $newsType)>{{ $newsType }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="news-classification-panel news-classification-panel--location mt-4">
                        <div class="news-classification-panel__icon news-classification-panel__icon--location">
                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        </div>
                        <div class="news-classification-panel__copy">
                            <h6 class="news-classification-panel__title">Location fields</h6>
                            <p class="news-classification-panel__hint">Critical for SoilnWater.</p>
                        </div>
                        <div id="communityNewsLocationSlot" class="news-classification-panel__fields"></div>
                    </div>
                </div>
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
                $communityBookPagesForJs = collect($initialBookPages)->map(function ($page) {
                    return [
                        'content' => is_array($page) ? ($page['content'] ?? '') : (string) $page,
                        'language' => is_array($page) ? ($page['language'] ?? 'en') : 'en',
                        'title' => is_array($page) ? ($page['title'] ?? '') : '',
                        'summary' => is_array($page) ? ($page['summary'] ?? '') : '',
                    ];
                })->values()->all();
            @endphp
            <div class="col-12 type-extra poetry-flow" data-for="poetry">
                @include('backend.community-posts.partials.poetry-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra awareness-flow" data-for="awareness">
                @include('backend.community-posts.partials.awareness-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra business-flow" data-for="business">
                @include('backend.community-posts.partials.business-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12" id="bodyContentSection">
                <div id="storyContentGuide" class="story-content-guide mb-3" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Story content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below. For stories, write page by page like a book — each page uses the same editor.</p>
                            </div>
                            <span class="badge bg-primary text-white">Stories only</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field already on the form. Add your full narrative using the editor below.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-0">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Headings</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Bold</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Italic</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Lists</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Quotes</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        <li class="mb-2">
                                            <strong>Beginning</strong>
                                            <span class="text-muted d-block">Introduce characters or situation</span>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Main story</strong>
                                            <span class="text-muted d-block">Events and experiences</span>
                                        </li>
                                        <li>
                                            <strong>Lesson / message</strong>
                                            <span class="text-muted d-block">What readers can learn</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="poetryContentGuide" class="story-content-guide mb-3" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Poem content</h5>
                                <p class="text-muted mb-0 small">Use the large poetry editor below. This is the same body field — your poem displays on the public page exactly as you format it here.</p>
                            </div>
                            <span class="badge bg-primary text-white">Poetry only</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Poetry editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">Large text editor with full Unicode support for Indian languages.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Paragraphs</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Line breaks</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Unicode languages</li>
                                    </ul>
                                    <p class="small mb-1 fw-semibold">Languages:</p>
                                    <div class="poetry-language-pills d-flex flex-wrap gap-2">
                                        @foreach(\App\Support\CommunityContentTaxonomy::poetryEditorLanguages() as $languageLabel)
                                            <span class="badge bg-light text-dark border">{{ $languageLabel }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Example</h6>
                                    <pre class="poetry-content-example mb-0">The river flows,
Silent and deep,

Carrying stories,
The mountains keep.</pre>
                                    <small class="text-muted d-block mt-2">Use Enter for new lines and a blank line between stanzas.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="awarenessContentGuide" class="story-content-guide mb-3 awareness-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Awareness content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full awareness message. Add factual, responsible content with a clear call to action.</p>
                            </div>
                            <span class="badge bg-primary text-white">Awareness only</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Use the editor below for the full awareness post.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Tables</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>PDF attachments</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Infographics</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertAwarenessStructureBtn">
                                        Insert recommended structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Recommended structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::awarenessContentStructure() as $heading => $hint)
                                            <li class="mb-2">
                                                <strong>{{ $heading }}</strong>
                                                <span class="text-muted d-block">{{ $hint }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="businessContentGuide" class="story-content-guide mb-3 business-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Business content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full business article, story, or guide.</p>
                            </div>
                            <span class="badge bg-primary text-white">Business only</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Use the editor below for the full business post.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Tables</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Documents</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Charts</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertBusinessStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::businessContentStructure() as $heading => $hint)
                                            <li class="mb-2">
                                                <strong>{{ $heading }}</strong>
                                                <span class="text-muted d-block">{{ $hint }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="autobiographyContentGuide" class="story-content-guide mb-3 life-story-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Biography / Autobiography content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for each book page or chapter. Content is saved page by page.</p>
                            </div>
                            <span class="badge bg-primary text-white">Biography &amp; Autobiography</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">Write chapter content with headings, images, tables, quotes, and links.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-0">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Headings</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Tables</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Quotes</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Chapter management</h6>
                                    <p class="text-muted small mb-2">Add multiple chapters. Each chapter includes a title, summary, and rich content.</p>
                                    <p class="small mb-1 fw-semibold">Example:</p>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        <li class="mb-2"><strong>Chapter 1 – Childhood</strong></li>
                                        <li class="mb-2"><strong>Chapter 2 – Education</strong></li>
                                        <li class="mb-2"><strong>Chapter 3 – Starting My Career</strong></li>
                                        <li><strong>Chapter 4 – Building My Business</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="childrensCornerContentGuide" class="story-content-guide mb-3 childrens-corner-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1" id="childrensCornerContentGuideTitle">Children's Corner content</h5>
                                <p class="text-muted mb-0 small" id="childrensCornerContentGuideHelp">Use the editor or upload panel below based on your share type.</p>
                            </div>
                            <span class="badge bg-primary text-white">Children's Corner</span>
                        </div>
                    </div>
                </div>
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
                                <i class="fa-solid fa-plus me-1"></i><span id="addBookPageBtnLabel">Add another page</span>
                            </button>
                        </div>
                        <div id="bookChapterMetaFields" style="display:none;">
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label" for="activeChapterTitle">Chapter title <span class="text-danger">*</span></label>
                                    <input type="text" id="activeChapterTitle" class="form-control" maxlength="160" placeholder="Chapter 1 – Childhood">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label" for="activeChapterSummary">Chapter summary</label>
                                    <input type="text" id="activeChapterSummary" class="form-control" maxlength="500" placeholder="Brief summary of this chapter">
                                </div>
                            </div>
                            <label class="form-label" id="activeChapterContentLabel">Chapter content <span class="text-danger">*</span></label>
                            <small class="text-muted d-block mb-2">Use the rich text editor below. Add images inside the chapter content.</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong id="activeBookPageTitle">Page 1</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="removeBookPageBtn" style="display:none;">
                                <i class="fa-solid fa-trash me-1"></i><span id="removeBookPageBtnLabel">Remove page</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="bodyEditorMount" class="community-body-editor-mount border rounded-3 bg-white p-2">
                    <div id="bodyEditorPlaceholder" class="community-body-editor-placeholder">
                        <i class="fa-solid fa-pen-to-square fa-2x mb-3 text-primary" aria-hidden="true"></i>
                        <p class="mb-0 fw-semibold">Select a post type above to load the rich text editor.</p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 px-1" id="editorLanguageWrap">
                        <div>
                            <label for="editorLanguageSelect" class="form-label mb-0 small fw-semibold" id="editorLanguageLabel">Editor language</label>
                            <small class="text-muted d-block" id="editorLanguageHelp">Default is English. Switch to Hindi to write in Devanagari.</small>
                        </div>
                        <select id="editorLanguageSelect" class="form-select form-select-sm community-editor-language-select">
                            @foreach(\App\Support\CommunityContentTaxonomy::standardEditorLanguages() as $code => $label)
                                <option value="{{ $code }}" @selected(old('editor_language', data_get($post->meta, 'editor_language', 'en')) === $code)>{{ $label }}</option>
                            @endforeach
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
            <div class="col-12 type-extra poetry-flow" data-for="poetry">
                @include('backend.community-posts.partials.poetry-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra autobiography-flow life-story-flow-section" data-for="biography,autobiography">
                @include('backend.community-posts.partials.autobiography-flow-fields', ['post' => $post])
            </div>
            <div class="col-12 type-extra childrens-corner-flow" data-for="childrens-corner">
                @include('backend.community-posts.partials.childrens-corner-flow-fields', ['post' => $post])
            </div>
            <div class="col-12 type-extra awareness-flow" data-for="awareness">
                @include('backend.community-posts.partials.awareness-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra business-flow" data-for="business">
                @include('backend.community-posts.partials.business-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra story-flow" data-for="stories">
                <div class="news-flow-card story-flow-card story-flow-card--audience border rounded-3 p-3 p-md-4 bg-white mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Target audience</h5>
                            <p class="text-muted mb-0 small">Select all groups this story is meant for.</p>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
                    </div>
                    @php
                        $selectedAudiences = old('story_target_audience', data_get($post->meta, 'story_target_audience', []));
                    @endphp
                    <div class="row g-2">
                        @foreach(\App\Support\CommunityContentTaxonomy::storyTargetAudiences() as $audience)
                            <div class="col-md-6 col-lg-4">
                                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                                    <input
                                        type="checkbox"
                                        name="story_target_audience[]"
                                        value="{{ $audience }}"
                                        class="form-check-input"
                                        @checked(in_array($audience, (array) $selectedAudiences, true))
                                    >
                                    <span class="form-check-label">{{ $audience }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="news-flow-card story-flow-card story-flow-card--theme border rounded-3 p-3 p-md-4 bg-light mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Story theme</h5>
                            <p class="text-muted mb-0 small">Very useful for recommendations.</p>
                        </div>
                        <span class="badge bg-primary text-white">Recommendations</span>
                    </div>
                    @php
                        $selectedThemes = old('story_themes', data_get($post->meta, 'story_themes', []));
                    @endphp
                    <div class="row g-2">
                        @foreach(\App\Support\CommunityContentTaxonomy::storyThemes() as $theme)
                            <div class="col-md-6 col-lg-4">
                                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                                    <input
                                        type="checkbox"
                                        name="story_themes[]"
                                        value="{{ $theme }}"
                                        class="form-check-input"
                                        @checked(in_array($theme, (array) $selectedThemes, true))
                                    >
                                    <span class="form-check-label">{{ $theme }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="news-flow-card story-flow-card story-flow-card--achievements border rounded-3 p-3 p-md-4 bg-white mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Story achievements</h5>
                            <p class="text-muted mb-0 small">Automatic badges awarded based on reader engagement.</p>
                        </div>
                        <span class="badge bg-success text-white">Automatic</span>
                    </div>
                    <div class="row g-2">
                        @foreach(\App\Services\CommunityStoryAchievementService::BADGE_LABELS as $field => $badgeLabel)
                            @php $earned = (bool) ($post->{$field} ?? false); @endphp
                            <div class="col-md-6 col-lg-3">
                                <div class="story-achievement-item {{ $earned ? 'is-earned' : 'is-pending' }}">
                                    <span class="story-achievement-item__icon" aria-hidden="true">
                                        <i class="fa-solid {{ $earned ? 'fa-medal text-success' : 'fa-lock text-muted' }}"></i>
                                    </span>
                                    {{ $badgeLabel }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-muted small mb-0 mt-3">Readers can also rate stories from 1–5 stars on the public story page.</p>
                </div>

                <div class="news-flow-card story-flow-card story-flow-card--moral border rounded-3 p-3 p-md-4 bg-white mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Moral / takeaway</h5>
                            <p class="text-muted mb-0 small">Can be displayed separately on the public story page.</p>
                        </div>
                        <span class="badge bg-warning text-dark">Highly recommended</span>
                    </div>
                    <label class="form-label" for="story_moral_takeaway">Moral / takeaway</label>
                    <textarea
                        name="story_moral_takeaway"
                        id="story_moral_takeaway"
                        class="form-control"
                        rows="3"
                        maxlength="1000"
                        placeholder="Never underestimate the power of community cooperation."
                    >{{ old('story_moral_takeaway', data_get($post->meta, 'story_moral_takeaway')) }}</textarea>
                    <small class="text-muted d-block mt-1">Example: Never underestimate the power of community cooperation.</small>
                </div>

                <div class="news-flow-card story-flow-card story-flow-card--characters border rounded-3 p-3 p-md-4 bg-light mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Characters</h5>
                            <p class="text-muted mb-0 small">Optional context about the people in your story.</p>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="story_main_characters">Main characters</label>
                            <textarea
                                name="story_main_characters"
                                id="story_main_characters"
                                class="form-control"
                                rows="3"
                                maxlength="2000"
                                placeholder="Ramesh Kumar, Village Head, School Teacher"
                            >{{ old('story_main_characters', data_get($post->meta, 'story_main_characters')) }}</textarea>
                            <small class="text-muted d-block mt-1">Example: Ramesh Kumar, Village Head, School Teacher</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="story_character_type">Character type</label>
                            <select name="story_character_type" id="story_character_type" class="form-select">
                                <option value="">Select character type</option>
                                @foreach(\App\Support\CommunityContentTaxonomy::storyCharacterTypes() as $characterType)
                                    <option value="{{ $characterType }}" @selected(old('story_character_type', data_get($post->meta, 'story_character_type')) === $characterType)>{{ $characterType }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-white">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Location information</h5>
                            <p class="text-muted mb-0 small">Especially useful for local stories.</p>
                        </div>
                        <span class="badge bg-success text-white">Local stories</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="story_place_type">Story location</label>
                            <select name="story_place_type" id="story_place_type" class="form-select">
                                <option value="">Select location scope</option>
                                @foreach(\App\Support\CommunityContentTaxonomy::storyPlaceTypes() as $placeType)
                                    <option value="{{ $placeType }}" @selected(old('story_place_type', data_get($post->meta, 'story_place_type')) === $placeType)>{{ $placeType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="story_place_names">Place names</label>
                            <input
                                type="text"
                                name="story_place_names"
                                id="story_place_names"
                                class="form-control"
                                value="{{ old('story_place_names', data_get($post->meta, 'story_place_names')) }}"
                                maxlength="500"
                                placeholder="Dehradun, Uttarakhand, India"
                            >
                            <small class="text-muted d-block mt-1">Example: Dehradun, Uttarakhand, India</small>
                        </div>
                    </div>
                </div>

                <div class="news-flow-card story-flow-card story-flow-card--period border rounded-3 p-3 p-md-4 bg-light mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Time period</h5>
                            <p class="text-muted mb-0 small">When does your story take place?</p>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="story_time_period">Time period</label>
                            <select name="story_time_period" id="story_time_period" class="form-select">
                                <option value="">Select time period</option>
                                @foreach(\App\Support\CommunityContentTaxonomy::storyTimePeriods() as $timePeriod)
                                    <option value="{{ $timePeriod }}" @selected(old('story_time_period', data_get($post->meta, 'story_time_period')) === $timePeriod)>{{ $timePeriod }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Example: Childhood, Present Day, 1980s, Historical Period, Future</small>
                        </div>
                    </div>
                </div>

                <div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-white mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Featured image</h5>
                            <p class="text-muted mb-0 small">Cover image for story cards, social sharing, and homepage.</p>
                        </div>
                        <span class="badge bg-warning text-dark">Recommended</span>
                    </div>
                    <ul class="story-cover-uses list-unstyled small text-muted mb-3">
                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Story cards</li>
                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Social sharing</li>
                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Homepage</li>
                    </ul>
                    <div id="communityStoryFeaturedImagesSlot"></div>
                </div>

                <div class="news-flow-card story-flow-card story-flow-card--gallery border rounded-3 p-3 p-md-4 bg-light">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Story gallery</h5>
                            <p class="text-muted mb-0 small">Optional visual gallery alongside your story.</p>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
                    </div>
                    <p class="small text-muted mb-3">Upload: Photos, Illustrations, Drawings, Old Images</p>
                    <input type="file" name="story_gallery[]" class="form-control" accept="image/*" multiple>
                    <small class="text-muted d-block mt-1">JPG, PNG, WebP, or GIF. Up to 10 images, max 4 MB each.</small>
                    @if(!empty(data_get($post->meta, 'story_gallery')))
                        <div class="mt-3 d-flex flex-column gap-2">
                            @foreach(data_get($post->meta, 'story_gallery', []) as $galleryImage)
                                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                                    <input type="checkbox" name="removed_story_gallery[]" value="{{ data_get($galleryImage, 'path') }}" class="form-check-input">
                                    <span class="form-check-label">Remove {{ data_get($galleryImage, 'name', 'gallery image') }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                @php
                    $existingStoryAudio = data_get($post->meta, 'story_audio');
                    $storyAudioSourceType = old('story_audio_source_type', filled($existingStoryAudio) ? (($existingStoryAudio['type'] ?? '') === 'recording' ? 'recording' : 'upload') : 'none');
                @endphp

                <div class="news-flow-card story-flow-card story-flow-card--audio border rounded-3 p-3 p-md-4 bg-white mb-3 mt-3">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Audio story</h5>
                            <p class="text-muted mb-0 small">Unique feature — upload MP3 or record voice directly in the browser.</p>
                        </div>
                        <span class="badge bg-info text-white">Unique feature</span>
                    </div>
                    <ul class="story-audio-uses list-unstyled small text-muted mb-3">
                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Useful for senior citizens</li>
                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Regional language stories</li>
                    </ul>
                    <div class="community-audio-field border rounded-3 p-3 bg-light">
                        <div class="btn-group btn-group-sm w-100 mb-3" role="group" aria-label="Audio story source type">
                            <input type="radio" class="btn-check" name="story_audio_source_type" id="storyAudioSourceNone" value="none" @checked($storyAudioSourceType === 'none')>
                            <label class="btn btn-outline-secondary" for="storyAudioSourceNone">No audio</label>
                            <input type="radio" class="btn-check" name="story_audio_source_type" id="storyAudioSourceUpload" value="upload" @checked($storyAudioSourceType === 'upload')>
                            <label class="btn btn-outline-secondary" for="storyAudioSourceUpload">MP3 upload</label>
                            <input type="radio" class="btn-check" name="story_audio_source_type" id="storyAudioSourceRecording" value="recording" @checked($storyAudioSourceType === 'recording')>
                            <label class="btn btn-outline-secondary" for="storyAudioSourceRecording">Voice recording</label>
                        </div>

                        <div id="storyAudioUploadWrap" class="audio-source-panel">
                            <label class="form-label" for="storyAudioFile">MP3 file</label>
                            <input type="file" name="story_audio_file" id="storyAudioFile" class="form-control" accept="audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg,.mp3,.wav,.webm,.ogg">
                            <small class="text-muted d-block mt-2">MP3 or other audio formats. Maximum size: 20 MB.</small>
                        </div>

                        <div id="storyAudioRecordingWrap" class="audio-source-panel">
                            <div class="story-audio-recorder border rounded-3 p-3 bg-white">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                    <button type="button" class="btn btn-sm btn-danger" id="storyAudioRecordBtn">
                                        <i class="fa-solid fa-microphone me-1"></i>Start recording
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="storyAudioStopBtn" disabled>Stop</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="storyAudioClearRecordingBtn" disabled>Clear</button>
                                    <span class="small text-muted" id="storyAudioRecordingStatus">Ready to record.</span>
                                </div>
                                <audio id="storyAudioRecordingPreview" controls class="w-100" style="display:none;"></audio>
                                <small class="text-muted d-block">Use your microphone to record the story. The recording is saved when you submit the form.</small>
                            </div>
                        </div>

                        @if(filled($existingStoryAudio))
                            <input type="hidden" name="keep_existing_story_audio" id="keepExistingStoryAudio" value="1">
                            <div id="existingStoryAudioPreview" class="alert alert-light border mt-3 mb-0 py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                                <div class="small">
                                    <strong>Current audio:</strong> {{ $existingStoryAudio['name'] ?? 'Audio story' }}
                                    @if(filled($existingStoryAudio['url'] ?? null))
                                        <audio controls class="d-block mt-2 w-100" src="{{ $existingStoryAudio['url'] }}"></audio>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger align-self-start" id="removeExistingStoryAudioBtn">Remove</button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="news-flow-card story-flow-card story-flow-card--video border rounded-3 p-3 p-md-4 bg-light">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                        <div>
                            <h5 class="mb-1">Video story</h5>
                            <p class="text-muted mb-0 small">Optional video version — YouTube link or uploaded file.</p>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
                    </div>
                    <div id="communityStoryVideoSlot"></div>
                </div>
            </div>
            <div class="col-md-6" id="communityFeaturedImagesWrap">
                <label class="form-label d-flex align-items-center justify-content-between gap-2">
                    <span id="featuredImagesLabel">Featured images</span>
                    <small class="text-muted fw-normal" id="featuredImagesCount">0 / 5</small>
                </label>
                <div class="featured-images-uploader border rounded-3 p-3">
                    <input type="file" id="featuredImagesInput" class="d-none" accept="image/*" multiple>
                    <button type="button" id="featuredImagesAddBtn" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-images me-1"></i>Add images
                    </button>
                    <small class="text-muted d-block mt-2" id="featuredImagesHelp">Upload up to 5 images. JPG, PNG, or WebP, max 4 MB each.</small>
                    <div id="featuredImagesPreview" class="featured-images-grid mt-3"></div>
                    <div id="featuredImagesRemovedWrap"></div>
                </div>
            </div>
            <div class="col-md-6" id="communityTagsWrap">
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
                <div class="news-flow-stack d-flex flex-column gap-3">
                    <div class="news-flow-card border rounded-3 p-3 p-md-4 bg-light">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Event &amp; publication details</h5>
                                <p class="text-muted mb-0 small">Timing, dateline, and reporter information.</p>
                            </div>
                            <span class="badge bg-primary text-white">News only</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Event date <span class="text-danger">*</span></label>
                                <input type="date" name="event_date" class="form-control news-required" value="{{ old('event_date', data_get($post->meta, 'event_date')) }}">
                                <small class="text-muted d-block mt-1">Example: 15 June 2026</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Event time</label>
                                <input type="text" name="event_time" class="form-control" value="{{ old('event_time', data_get($post->meta, 'event_time')) }}" maxlength="40" placeholder="7:30 PM">
                                <small class="text-muted d-block mt-1">Optional. Example: 7:30 PM</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">News submission date</label>
                                @php
                                    $newsSubmissionAt = $post->submitted_at ?? ($post->exists ? $post->created_at : null);
                                @endphp
                                <input type="text" class="form-control bg-light" value="{{ $newsSubmissionAt ? $newsSubmissionAt->timezone(config('app.timezone'))->format('j F Y, g:i A') : 'Auto-generated on submit' }}" readonly tabindex="-1">
                                <small class="text-muted d-block mt-1">Auto generated.</small>
                            </div>
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
                            <div class="col-md-6">
                                <label class="form-label">Reporter / byline <span class="text-danger">*</span></label>
                                <input type="text" name="reporter_name" class="form-control news-required" value="{{ old('reporter_name', data_get($post->meta, 'reporter_name')) }}" maxlength="160" placeholder="Reporter or desk name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Impact / affected area</label>
                                <textarea name="impact_area" class="form-control" rows="3" maxlength="1000" placeholder="Who is affected and what readers should know">{{ old('impact_area', data_get($post->meta, 'impact_area')) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Quote / attribution</label>
                                <textarea name="quote_attribution" class="form-control" rows="2" maxlength="1000" placeholder="Important quote with speaker attribution">{{ old('quote_attribution', data_get($post->meta, 'quote_attribution')) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="news-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">News content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor above for the full story. Complete these structured fields for clarity and searchability.</p>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary border">Structured</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="news-narrative-field h-100">
                                    <label class="form-label">What happened? <span class="text-danger">*</span></label>
                                    <textarea name="news_what_happened" class="form-control news-required" rows="4" maxlength="2000" placeholder="Describe the core event or development">{{ old('news_what_happened', data_get($post->meta, 'news_what_happened', data_get($post->meta, 'fact_summary'))) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="news-narrative-field h-100">
                                    <label class="form-label">Where did it happen? <span class="text-danger">*</span></label>
                                    <textarea name="news_where_happened" class="form-control news-required" rows="4" maxlength="1000" placeholder="City, district, landmark, or venue">{{ old('news_where_happened', data_get($post->meta, 'news_where_happened')) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="news-narrative-field h-100">
                                    <label class="form-label">When did it happen? <span class="text-danger">*</span></label>
                                    <textarea name="news_when_happened" class="form-control news-required" rows="3" maxlength="500" placeholder="Date, time, or period readers should know">{{ old('news_when_happened', data_get($post->meta, 'news_when_happened')) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="news-narrative-field h-100">
                                    <label class="form-label">Who was involved? <span class="text-danger">*</span></label>
                                    <textarea name="news_who_involved" class="form-control news-required" rows="3" maxlength="1000" placeholder="People, departments, organizations, or groups">{{ old('news_who_involved', data_get($post->meta, 'news_who_involved')) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="news-narrative-field h-100">
                                    <label class="form-label">Why is it important? <span class="text-danger">*</span></label>
                                    <textarea name="news_why_important" class="form-control news-required" rows="3" maxlength="1000" placeholder="Explain the impact or significance for readers">{{ old('news_why_important', data_get($post->meta, 'news_why_important')) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="news-narrative-field h-100">
                                    <label class="form-label">Current status <span class="text-danger">*</span></label>
                                    <textarea name="news_current_status" class="form-control news-required" rows="3" maxlength="1000" placeholder="Latest update, ongoing action, or resolution status">{{ old('news_current_status', data_get($post->meta, 'news_current_status')) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="news-flow-card news-flow-card--impact border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">People, priority &amp; community impact</h5>
                                <p class="text-muted mb-0 small">Optional mentions and priority, plus SoilnWater community impact tracking.</p>
                            </div>
                            <span class="badge bg-success text-white">Community impact</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">People &amp; organizations mentioned</label>
                                <textarea name="news_people_organizations" class="form-control" rows="3" maxlength="2000" placeholder="Mayor, District Magistrate, School Principal, NGO, Company Name">{{ old('news_people_organizations', data_get($post->meta, 'news_people_organizations')) }}</textarea>
                                <small class="text-muted d-block mt-1">Optional. Examples: Mayor, District Magistrate, School Principal, NGO, Company Name</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">News priority</label>
                                <select name="news_priority" class="form-select">
                                    <option value="">Select priority (optional)</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::newsPriorities() as $newsPriority)
                                        <option value="{{ $newsPriority }}" @selected(old('news_priority', data_get($post->meta, 'news_priority')) === $newsPriority)>{{ $newsPriority }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">User suggestion.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Impact level <span class="text-danger">*</span></label>
                                <select name="news_impact_level" class="form-select news-required">
                                    <option value="">Select impact level</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::newsImpactLevels() as $impactLevel)
                                        <option value="{{ $impactLevel }}" @selected(old('news_impact_level', data_get($post->meta, 'news_impact_level')) === $impactLevel)>{{ $impactLevel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Affected group <span class="text-danger">*</span></label>
                                <select name="news_affected_group" class="form-select news-required">
                                    <option value="">Select affected group</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::newsAffectedGroups() as $affectedGroup)
                                        <option value="{{ $affectedGroup }}" @selected(old('news_affected_group', data_get($post->meta, 'news_affected_group')) === $affectedGroup)>{{ $affectedGroup }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-success py-2 px-3 mb-0 small">
                                    <strong>Unique SoilnWater feature.</strong> Impact level and affected group help readers understand who this news affects and how urgently the community should respond.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="news-flow-card news-flow-card--source border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">News source</h5>
                                <p class="text-muted mb-0 small">Very important for credibility.</p>
                            </div>
                            <span class="badge bg-warning text-dark">Credibility</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Source type <span class="text-danger">*</span></label>
                                <select name="news_source_type" class="form-select news-required">
                                    <option value="">Select source type</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::newsSourceTypes() as $sourceType)
                                        <option value="{{ $sourceType }}" @selected(old('news_source_type', data_get($post->meta, 'news_source_type')) === $sourceType)>{{ $sourceType }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Source <span class="text-danger">*</span></label>
                                <input type="text" name="news_source" class="form-control news-required" value="{{ old('news_source', data_get($post->meta, 'news_source')) }}" maxlength="160" placeholder="Name of witness, agency, publication, or organization">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Source URL</label>
                                <input type="url" name="source_url" class="form-control" value="{{ old('source_url', data_get($post->meta, 'source_url')) }}" maxlength="255" placeholder="https://example.com/source">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Verification notes <span class="text-danger">*</span></label>
                                <textarea name="verification_notes" class="form-control news-required" rows="3" maxlength="2000" placeholder="Cross-checks, documents, official statements, and confirmation status">{{ old('verification_notes', data_get($post->meta, 'verification_notes')) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Related authority</label>
                                <input type="text" name="news_related_authority" class="form-control" value="{{ old('news_related_authority', data_get($post->meta, 'news_related_authority')) }}" maxlength="160" placeholder="e.g. Municipal Corporation">
                                <small class="text-muted d-block mt-1">Optional. Examples: Municipal Corporation, District Administration, School Authority</small>
                            </div>
                        </div>
                    </div>

                    <div class="news-flow-card news-flow-card--media border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Images, video &amp; documents</h5>
                                <p class="text-muted mb-0 small">Add visual evidence, optional video, and supporting official documents.</p>
                            </div>
                            <span class="badge bg-info text-dark">Media</span>
                        </div>
                        <div class="row g-4">
                            <div class="col-12">
                                <div id="communityNewsFeaturedImagesSlot"></div>
                            </div>
                            <div class="col-12">
                                <div id="communityNewsVideoSlot"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Documents <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="file" name="news_documents[]" class="form-control" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" multiple>
                                <small class="text-muted d-block mt-2">Useful for: Government Orders, Circulars, Press Releases, Reports, Notices. Supported: PDF, DOC, DOCX. Up to 6 files, 20 MB each.</small>
                                @if(!empty(data_get($post->meta, 'news_documents')))
                                    <div class="mt-2 d-flex flex-column gap-2">
                                        @foreach(data_get($post->meta, 'news_documents', []) as $document)
                                            <label class="d-flex align-items-center gap-2 mb-0">
                                                <input type="checkbox" name="removed_news_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input">
                                                <a href="{{ data_get($document, 'url') }}" target="_blank" rel="noopener" class="badge bg-light text-dark border text-decoration-none">
                                                    <i class="fa-solid fa-file-lines me-1"></i>{{ data_get($document, 'name', 'Document') }}
                                                </a>
                                                <small class="text-muted">Remove on save</small>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="news-flow-card news-flow-card--discussion border rounded-3 p-3 p-md-4 bg-light" id="newsParticipationWrap">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Comments &amp; discussion</h5>
                                <p class="text-muted mb-0 small">Choose what logged-in readers can submit on the public news page. The author is notified in the portal and by email; readers are notified when questions are answered.</p>
                            </div>
                            <span class="badge bg-primary text-white">Engagement</span>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="allow_comments" value="1" class="form-check-input" id="newsAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
                            <label class="form-check-label" for="newsAllowComments">Comments</label>
                            <small class="text-muted d-block">Enable a public discussion thread with comments and replies.</small>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="allow_questions" value="1" class="form-check-input" id="newsAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
                            <label class="form-check-label" for="newsAllowQuestions">Questions</label>
                            <small class="text-muted d-block">Readers can ask the author a direct question. You answer in the author portal; the reader is notified by email.</small>
                        </div>
                        <div class="form-check mb-0">
                            <input type="checkbox" name="allow_suggestions" value="1" class="form-check-input" id="newsAllowSuggestions" @checked(old('allow_suggestions', $post->allow_suggestions ?? false))>
                            <label class="form-check-label" for="newsAllowSuggestions">Suggestions</label>
                            <small class="text-muted d-block">Readers can recommend actions, follow-ups, or improvements.</small>
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
                                <h5 class="mb-1">Location fields</h5>
                                <p class="text-muted mb-0 small">Country, state, district, city, and optional GPS coordinates for this report.</p>
                            </div>
                            <span class="badge bg-success-subtle text-success border">Critical for SoilnWater</span>
                        </div>
                        <div id="communityReportLocationSlot"></div>
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

                    <div class="report-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Location fields</h5>
                                <p class="text-muted mb-0 small">Country, state, district, city, and optional GPS coordinates for this report.</p>
                            </div>
                            <span class="badge bg-success-subtle text-success border">Critical for SoilnWater</span>
                        </div>
                        <div id="communityReportLocationSlot"></div>
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
            <div id="communityStructuredLocationHiddenSlot" class="d-none">
                <div id="communityStructuredLocationWrapper">
                    @include('backend.community-posts.partials.structured-location-fields', ['post' => $post])
                </div>
            </div>
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
                        <div class="col-12" id="communityCommonLocationSlot">
                            <div class="community-location-fields border rounded-3 p-3 bg-light">
                                <div class="community-location-fields__header">
                                    <h6 class="mb-1" id="communityLocationSectionTitle">Location information</h6>
                                    <p class="text-muted small mb-3" id="communityLocationSectionHelp">Very important for SoilnWater. Choose how broadly this post applies, then add a specific place when needed.</p>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="communityLocationType" id="communityLocationTypeLabel">Location type <span class="text-danger">*</span></label>
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
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="communityVideoHiddenSlot"></div>
                        <div class="col-md-6" id="communityVideoWrap">
                            <label class="form-label" id="videoFieldLabel">Video <span class="text-muted fw-normal">(optional)</span></label>
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

    #bodyEditorMount.is-poetry-mode .ck-editor__editable_inline {
        min-height: 480px;
    }

    .poetry-content-example {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: .75rem;
        color: #334155;
        font-family: Georgia, "Times New Roman", serif;
        font-size: .95rem;
        line-height: 1.7;
        padding: 1rem 1.15rem;
        white-space: pre-wrap;
    }

    .poetry-language-pills .badge {
        font-weight: 500;
    }

    .poetry-author-preview__avatar img,
    .poetry-author-preview__initials {
        align-items: center;
        background: #e2e8f0;
        color: #334155;
        display: inline-flex;
        font-weight: 700;
        height: 72px;
        justify-content: center;
        object-fit: cover;
        width: 72px;
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

    .ck-editor__editable.ck-content[lang="ur"] {
        direction: rtl;
        font-family: "Noto Nastaliq Urdu", "Jameel Noori Nastaleeq", "Urdu Typesetting", serif;
        text-align: right;
    }

    .ck-editor__editable.ck-content[lang="pa"] {
        font-family: "Noto Sans Gurmukhi", "Raavi", "AnmolUni", sans-serif;
    }

    .ck-editor__editable.ck-content[lang="bn"] {
        font-family: "Noto Sans Bengali", "Vrinda", "Shonar Bangla", sans-serif;
    }

    .ck-editor__editable.ck-content[lang="mr"] {
        font-family: "Noto Sans Devanagari", "Nirmala UI", "Mangal", sans-serif;
    }

    .ck-editor__editable.ck-content[lang="gu"] {
        font-family: "Noto Sans Gujarati", "Shruti", sans-serif;
    }

    .ck-editor__editable.ck-content[lang="ta"] {
        font-family: "Noto Sans Tamil", "Latha", "Vijaya", sans-serif;
    }

    .ck-editor__editable.ck-content[lang="te"] {
        font-family: "Noto Sans Telugu", "Gautami", "Vani", sans-serif;
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
    .news-flow-card h5 {
        font-size: 1.05rem;
    }
    .news-flow-card--source {
        border-left: 4px solid #ffc107 !important;
        background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
    }
    .news-narrative-field {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.75rem;
        padding: 1rem;
        background: #f8fafc;
    }
    .news-flow-card--impact {
        border-left: 4px solid #198754 !important;
        background: linear-gradient(180deg, #f8fff9 0%, #ffffff 100%);
    }
    .news-flow-card--media {
        border-left: 4px solid #0dcaf0 !important;
        background: linear-gradient(180deg, #f8fdff 0%, #ffffff 100%);
    }
    .news-flow-card--discussion {
        border-left: 4px solid #0d6efd !important;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }
    .story-flow-card {
        border-left: 4px solid #6f42c1 !important;
        background: linear-gradient(180deg, #faf8ff 0%, #ffffff 100%);
    }
    .story-flow-card--moral {
        border-left-color: #ffc107 !important;
        background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
    }
    .story-flow-card--characters {
        border-left-color: #0d6efd !important;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }
    .story-flow-card--location {
        border-left-color: #198754 !important;
        background: linear-gradient(180deg, #f8fff9 0%, #ffffff 100%);
    }
    .story-flow-card--period {
        border-left-color: #6c757d !important;
        background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
    }
    .story-flow-card--cover {
        border-left-color: #ffc107 !important;
        background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
    }
    .story-flow-card--gallery {
        border-left-color: #0dcaf0 !important;
        background: linear-gradient(180deg, #f8fdff 0%, #ffffff 100%);
    }
    .story-flow-card--audience {
        border-left-color: #6610f2 !important;
        background: linear-gradient(180deg, #f8f5ff 0%, #ffffff 100%);
    }
    .story-flow-card--theme {
        border-left-color: #0d6efd !important;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }
    /* Card-style checkbox/radio options (overrides Bootstrap .form-check float/negative margin) */
    .community-flow-checklist label.form-check,
    .community-flow-stack label.form-check,
    .news-flow-card label.form-check,
    .story-flow-card label.form-check,
    .awareness-flow label.form-check,
    .business-flow label.form-check,
    .childrens-corner-flow label.form-check {
        align-items: center;
        cursor: pointer;
        display: flex;
        flex-direction: row;
        gap: 0.75rem;
        margin-bottom: 0;
        min-height: auto;
        padding-left: unset;
        width: 100%;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }
    .community-flow-stack label.form-check,
    .childrens-corner-flow .community-flow-stack label.form-check,
    .awareness-flow .community-flow-stack label.form-check,
    .business-flow .community-flow-stack label.form-check {
        align-items: flex-start;
    }
    .community-flow-checklist label.form-check:hover,
    .community-flow-stack label.form-check:hover,
    .news-flow-card label.form-check:hover,
    .story-flow-card label.form-check:hover,
    .awareness-flow label.form-check:hover,
    .business-flow label.form-check:hover,
    .childrens-corner-flow label.form-check:hover {
        border-color: rgba(13, 110, 253, 0.35) !important;
    }
    .community-flow-checklist label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .community-flow-stack label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .news-flow-card label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .story-flow-card label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .awareness-flow label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .business-flow label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .childrens-corner-flow label.form-check:has(.form-check-input[type="checkbox"]:checked) {
        background: #f0fdf4 !important;
        border-color: rgba(25, 135, 84, 0.45) !important;
        box-shadow: 0 0 0 1px rgba(25, 135, 84, 0.12);
    }
    .community-flow-checklist label.form-check:has(.form-check-input[type="radio"]:checked),
    .community-flow-stack label.form-check:has(.form-check-input[type="radio"]:checked),
    .awareness-flow label.form-check:has(.form-check-input[type="radio"]:checked),
    .business-flow label.form-check:has(.form-check-input[type="radio"]:checked),
    .childrens-corner-flow label.form-check:has(.form-check-input[type="radio"]:checked) {
        background: #eff6ff !important;
        border-color: rgba(37, 99, 235, 0.45) !important;
        box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.12);
    }
    .community-flow-checklist .form-check-input,
    .community-flow-stack .form-check-input,
    .news-flow-card .form-check-input,
    .story-flow-card .form-check-input,
    .awareness-flow .form-check-input,
    .business-flow .form-check-input,
    .childrens-corner-flow .form-check-input {
        appearance: none;
        -webkit-appearance: none;
        background-color: #fff;
        background-position: center;
        background-repeat: no-repeat;
        background-size: contain;
        border: 2px solid #94a3b8;
        border-radius: 0.25rem;
        box-shadow: none;
        cursor: pointer;
        flex-shrink: 0;
        float: none !important;
        height: 1.125rem;
        margin: 0 !important;
        position: static !important;
        vertical-align: middle;
        width: 1.125rem;
    }
    .community-flow-stack .form-check-input,
    .childrens-corner-flow .community-flow-stack .form-check-input,
    .awareness-flow .community-flow-stack .form-check-input,
    .business-flow .community-flow-stack .form-check-input {
        margin-top: 0.2rem !important;
    }
    .community-flow-checklist .form-check-input[type="radio"],
    .community-flow-stack .form-check-input[type="radio"],
    .news-flow-card .form-check-input[type="radio"],
    .story-flow-card .form-check-input[type="radio"],
    .awareness-flow .form-check-input[type="radio"],
    .business-flow .form-check-input[type="radio"],
    .childrens-corner-flow .form-check-input[type="radio"] {
        border-radius: 50%;
    }
    .community-flow-checklist .form-check-input[type="checkbox"]:checked,
    .community-flow-stack .form-check-input[type="checkbox"]:checked,
    .news-flow-card .form-check-input[type="checkbox"]:checked,
    .story-flow-card .form-check-input[type="checkbox"]:checked,
    .awareness-flow .form-check-input[type="checkbox"]:checked,
    .business-flow .form-check-input[type="checkbox"]:checked,
    .childrens-corner-flow .form-check-input[type="checkbox"]:checked {
        background-color: #198754;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3 6-6'/%3e%3c/svg%3e");
        border-color: #198754;
    }
    .community-flow-checklist .form-check-input[type="radio"]:checked,
    .community-flow-stack .form-check-input[type="radio"]:checked,
    .news-flow-card .form-check-input[type="radio"]:checked,
    .story-flow-card .form-check-input[type="radio"]:checked,
    .awareness-flow .form-check-input[type="radio"]:checked,
    .business-flow .form-check-input[type="radio"]:checked,
    .childrens-corner-flow .form-check-input[type="radio"]:checked {
        background-color: #fff;
        background-image: radial-gradient(circle, #2563eb 0%, #2563eb 38%, transparent 42%, transparent 100%);
        border-color: #2563eb;
        border-width: 2px;
    }
    .community-flow-checklist .form-check-input:focus,
    .community-flow-stack .form-check-input:focus,
    .news-flow-card .form-check-input:focus,
    .story-flow-card .form-check-input:focus,
    .awareness-flow .form-check-input:focus,
    .business-flow .form-check-input:focus,
    .childrens-corner-flow .form-check-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.2);
        outline: 0;
    }
    .community-flow-checklist .form-check-label,
    .community-flow-stack .form-check-label,
    .news-flow-card .form-check-label,
    .story-flow-card .form-check-label,
    .awareness-flow .form-check-label,
    .business-flow .form-check-label,
    .childrens-corner-flow .form-check-label {
        cursor: pointer;
        flex: 1 1 auto;
        line-height: 1.4;
        margin-bottom: 0;
        min-width: 0;
        padding-left: 0;
    }
    #bodyContentSection.is-waiting-for-type #bodyEditorMount {
        display: block !important;
    }
    #bodyContentSection.is-waiting-for-type #editorLanguageWrap,
    #bodyContentSection.is-waiting-for-type #bodyEditor,
    #bodyContentSection.is-waiting-for-type .ck-editor {
        display: none !important;
    }
    #bodyContentSection.is-waiting-for-type #bodyEditorMount {
        min-height: auto;
    }
    #bodyContentSection .community-body-editor-mount {
        min-height: 360px;
    }
    #bodyContentSection .community-body-editor-placeholder {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 0.85rem;
        color: #64748b;
        padding: 2rem 1.25rem;
        text-align: center;
    }
    .story-flow-card--achievements {
        border-left-color: #198754 !important;
        background: linear-gradient(180deg, #f8fff9 0%, #ffffff 100%);
    }
    .story-flow-card--timeline {
        border-left-color: #ffc107 !important;
        background: linear-gradient(180deg, #fffdf5 0%, #ffffff 100%);
    }
    .story-flow-card--lessons {
        border-left-color: #0d6efd !important;
        background: linear-gradient(180deg, #f5f9ff 0%, #ffffff 100%);
    }
    .story-cover-uses {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.35rem 1rem;
    }
    #communityStoryFeaturedImagesSlot .featured-images-uploader {
        background: #fff;
    }
    #communityStoryVideoSlot .community-video-field {
        background: #fff;
    }
    .community-audio-field { background: #fafbfc; }
    .audio-source-panel { display: none; }
    .audio-source-panel.is-active { display: block; }
    .story-flow-card--audio {
        border-left-color: #0dcaf0 !important;
        background: linear-gradient(180deg, #f8fdff 0%, #ffffff 100%);
    }
    .story-flow-card--video {
        border-left-color: #dc3545 !important;
        background: linear-gradient(180deg, #fff8f8 0%, #ffffff 100%);
    }
    .story-audio-uses {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.35rem 1rem;
    }
    .story-content-panel {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.75rem;
        padding: 1rem;
        background: #f8fafc;
    }
    .story-content-panel__title {
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
    }
    .story-content-support {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.35rem 1rem;
    }
    #communityNewsFeaturedImagesSlot .featured-images-uploader,
    #communityAwarenessFeaturedImagesSlot .featured-images-uploader,
    #communityAwarenessVideoSlot .community-video-field,
    #communityBusinessVideoSlot .community-video-field,
    #communityNewsVideoSlot .community-video-field {
        background: #fff;
    }
    .news-classification-card {
        background: linear-gradient(135deg, #f8fbff 0%, #ffffff 55%, #f4f9f6 100%);
        border-color: rgba(13, 110, 253, 0.15) !important;
    }
    .news-classification-panel {
        display: grid;
        grid-template-columns: auto 1fr;
        grid-template-rows: auto auto;
        gap: 0.35rem 0.85rem;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(13, 110, 253, 0.12);
        border-radius: 0.85rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .news-classification-panel--location {
        border-color: rgba(25, 135, 84, 0.18);
    }
    .news-classification-panel__icon {
        grid-row: 1 / span 2;
        align-self: start;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        font-size: 1rem;
    }
    .news-classification-panel__icon--location {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }
    .news-classification-panel__title {
        margin-bottom: 0;
        font-size: 0.98rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        text-transform: uppercase;
    }
    .news-classification-panel__hint {
        margin-bottom: 0;
        color: #6c757d;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .news-classification-panel__copy {
        align-self: center;
    }
    .news-classification-panel > .form-select,
    .news-classification-panel__fields {
        grid-column: 1 / -1;
    }
    .news-classification-panel .community-location-fields {
        border: 0 !important;
        background: transparent !important;
        padding: 0 !important;
        box-shadow: none;
    }
    .news-classification-panel .community-location-fields__header {
        display: none;
    }
    .news-classification-panel .community-location-fields .row {
        --bs-gutter-y: 0.85rem;
    }
    .news-classification-panel .community-location-fields #communityLocationTypeLabel {
        text-transform: none;
        font-weight: 600;
        letter-spacing: normal;
    }
    .news-classification-panel .community-location-fields [class*="col-md-"] {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .news-classification-panel--location {
        display: grid;
        grid-template-columns: auto 1fr;
        grid-template-rows: auto auto;
        gap: 0.35rem 0.85rem;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(25, 135, 84, 0.18);
        border-radius: 0.85rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .community-structured-location {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.85rem;
        background: #fbfcfe;
        padding: 1rem;
    }
    .community-structured-location__search .form-control {
        background: #fff;
    }
    .community-structured-location__map {
        border-top-color: rgba(15, 23, 42, 0.08) !important;
    }
</style>
@include('community.partials.story-styles')
@endpush

@php
    $communityFeaturedImagesForJs = collect($post->featuredImages())->map(function ($path) {
        return [
            'path' => $path,
            'url' => \App\Models\CommunityPost::resolveImageUrl($path),
        ];
    })->values()->all();
@endphp

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>
<script>
    window.communityTypes = @json($types);
    window.communityBookTypes = @json(\App\Models\CommunityPost::BOOK_CONTENT_TYPES);
    window.communityLifeStoryTypes = @json(\App\Models\CommunityPost::LIFE_STORY_CONTENT_TYPES);
    window.communityBookPages = @json($communityBookPagesForJs);
    window.communityBodyEditor = null;
    window.communityActiveBookPage = 0;
    const COMMUNITY_EDITOR_LANGUAGES = {
        en: { label: 'English', lang: 'en', dir: 'ltr' },
        hi: { label: 'Hindi', lang: 'hi', dir: 'ltr' },
        ur: { label: 'Urdu', lang: 'ur', dir: 'rtl' },
        pa: { label: 'Punjabi', lang: 'pa', dir: 'ltr' },
        bn: { label: 'Bengali', lang: 'bn', dir: 'ltr' },
        mr: { label: 'Marathi', lang: 'mr', dir: 'ltr' },
        gu: { label: 'Gujarati', lang: 'gu', dir: 'ltr' },
        ta: { label: 'Tamil', lang: 'ta', dir: 'ltr' },
        te: { label: 'Telugu', lang: 'te', dir: 'ltr' },
    };
    const COMMUNITY_STANDARD_EDITOR_LANGUAGE_CODES = @json(array_keys(\App\Support\CommunityContentTaxonomy::standardEditorLanguages()));
    const COMMUNITY_POETRY_EDITOR_LANGUAGE_CODES = @json(array_keys(\App\Support\CommunityContentTaxonomy::poetryEditorLanguages()));

    function isLifeStoryContentType(type) {
        return (window.communityLifeStoryTypes || []).includes(type);
    }

    function contentTypeMatchesDataset(selectedType, datasetFor) {
        return (datasetFor || '')
            .split(',')
            .map(function (value) { return value.trim(); })
            .filter(Boolean)
            .includes(selectedType);
    }

    function getChildrensCornerContentMode(shareType) {
        const modes = window.communityChildrensCornerShareModes || {};

        if (!shareType) {
            return null;
        }

        for (const [mode, types] of Object.entries(modes)) {
            if ((types || []).includes(shareType)) {
                return mode;
            }
        }

        return 'rich_text';
    }

    function syncChildrensCornerCategory() {
        const shareType = document.getElementById('childShareType')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'childrens-corner') {
            return;
        }

        categorySelect.value = shareType;
        categorySelect.dataset.selected = shareType;
    }

    function syncAwarenessCategory() {
        const awarenessCategory = document.getElementById('awarenessCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'awareness') {
            return;
        }

        categorySelect.value = awarenessCategory;
        categorySelect.dataset.selected = awarenessCategory;
    }

    document.getElementById('awarenessCategory')?.addEventListener('change', syncAwarenessCategory);

    const communityTagsDefaultParent = document.getElementById('communityTagsWrap')?.parentElement || null;
    const communityTagsDefaultNextSibling = document.getElementById('communityTagsWrap')?.nextElementSibling || null;

    function syncBusinessCategory() {
        const businessCategory = document.getElementById('businessCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'business') {
            return;
        }

        categorySelect.value = businessCategory;
        categorySelect.dataset.selected = businessCategory;
    }

    document.getElementById('businessCategory')?.addEventListener('change', syncBusinessCategory);

    document.getElementById('insertAwarenessStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Problem</h2>',
            '<p>What issue exists?</p>',
            '<h2>Why It Matters</h2>',
            '<p>Why should people care?</p>',
            '<h2>Facts &amp; Statistics</h2>',
            '<p>Supporting information.</p>',
            '<h2>Solutions</h2>',
            '<p>Practical recommendations.</p>',
            '<h2>Call To Action</h2>',
            '<p>What should people do?</p>',
        ].join('');

        if (window.communityBodyEditor && typeof window.communityBodyEditor.setData === 'function') {
            const current = window.communityBodyEditor.getData?.() || '';
            window.communityBodyEditor.setData(current.trim() ? current + structureHtml : structureHtml);
            return;
        }

        const bodyEditor = document.getElementById('bodyEditor');
        if (bodyEditor) {
            bodyEditor.value = (bodyEditor.value || '').trim()
                ? bodyEditor.value + '\n\n' + structureHtml.replace(/<[^>]+>/g, '\n').replace(/\n+/g, '\n')
                : structureHtml.replace(/<[^>]+>/g, '\n').replace(/\n+/g, '\n');
        }
    });

    document.getElementById('insertBusinessStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Business Problem</h2>',
            '<p>What issue are you addressing?</p>',
            '<h2>Background</h2>',
            '<p>Context</p>',
            '<h2>Solution</h2>',
            '<p>Approach used</p>',
            '<h2>Results</h2>',
            '<p>Outcome</p>',
            '<h2>Lessons Learned</h2>',
            '<p>Key takeaways</p>',
            '<h2>Recommendations</h2>',
            '<p>Advice to others</p>',
        ].join('');

        if (window.communityBodyEditor && typeof window.communityBodyEditor.setData === 'function') {
            const current = window.communityBodyEditor.getData?.() || '';
            window.communityBodyEditor.setData(current.trim() ? current + structureHtml : structureHtml);
            return;
        }

        const bodyEditor = document.getElementById('bodyEditor');
        if (bodyEditor) {
            bodyEditor.value = (bodyEditor.value || '').trim()
                ? bodyEditor.value + '\n\n' + structureHtml.replace(/<[^>]+>/g, '\n').replace(/\n+/g, '\n')
                : structureHtml.replace(/<[^>]+>/g, '\n').replace(/\n+/g, '\n');
        }
    });

    const CHILDRENS_CORNER_CONTENT_GUIDE = {
        rich_text: {
            title: 'Story / essay / article',
            help: 'Use the rich text editor below. You can add text, images, and basic formatting.',
            bodyLabel: 'Content <span class="text-danger">*</span>',
            bodyHelp: 'Write your story, essay, or article. Add images and formatting as needed.',
        },
        poem: {
            title: 'Poem',
            help: 'Use the poetry editor below. Line breaks are preserved for verses and stanzas.',
            bodyLabel: 'Poem <span class="text-danger">*</span>',
            bodyHelp: 'Write your poem below. Use line breaks for verses and blank lines between stanzas.',
        },
        image: {
            title: 'Drawing / painting / photo',
            help: 'Upload your artwork in the project panel below. Formats: JPG, PNG, WEBP.',
        },
        project: {
            title: 'Project submission',
            help: 'Add a description and upload photos, PDF, or presentation files in the panel below.',
        },
        quiz: {
            title: 'Quiz / puzzle',
            help: 'Create questions with options and mark the correct answer in the quiz builder below.',
        },
    };

    function refreshChildrensCornerContentMode() {
        const contentType = document.getElementById('contentType')?.value || '';
        const isChildrensCorner = contentType === 'childrens-corner';
        const shareType = document.getElementById('childShareType')?.value || '';
        const mode = isChildrensCorner ? getChildrensCornerContentMode(shareType) : null;
        const showEditor = isChildrensCorner && (mode === 'rich_text' || mode === 'poem');
        const artPanel = document.getElementById('childrensCornerArtPanel');
        const projectPanel = document.getElementById('childrensCornerProjectPanel');
        const quizPanel = document.getElementById('childrensCornerQuizPanel');
        const standardBodyHeader = document.getElementById('standardBodyHeader');
        const bodyEditorMount = document.getElementById('bodyEditorMount');
        const guide = document.getElementById('childrensCornerContentGuide');
        const guideTitle = document.getElementById('childrensCornerContentGuideTitle');
        const guideHelp = document.getElementById('childrensCornerContentGuideHelp');
        const artFileInput = document.getElementById('childrensCornerArtFile');
        const projectDescription = document.getElementById('childrensCornerProjectDescription');
        const keepExistingArt = document.getElementById('keepExistingChildrensCornerArt');

        if (artPanel) {
            artPanel.style.display = isChildrensCorner && mode === 'image' ? '' : 'none';
        }
        if (projectPanel) {
            projectPanel.style.display = isChildrensCorner && mode === 'project' ? '' : 'none';
        }
        if (quizPanel) {
            quizPanel.style.display = isChildrensCorner && mode === 'quiz' ? '' : 'none';
        }

        if (standardBodyHeader && isChildrensCorner) {
            standardBodyHeader.style.display = showEditor ? '' : 'none';
        }

        if (bodyEditorMount && isChildrensCorner) {
            bodyEditorMount.style.display = showEditor ? '' : 'none';
        }

        if (guide) {
            guide.style.display = isChildrensCorner && mode ? '' : 'none';
        }

        if (guideTitle && guideHelp && mode && CHILDRENS_CORNER_CONTENT_GUIDE[mode]) {
            guideTitle.textContent = CHILDRENS_CORNER_CONTENT_GUIDE[mode].title;
            guideHelp.textContent = CHILDRENS_CORNER_CONTENT_GUIDE[mode].help;
        }

        if (isChildrensCorner && mode && CHILDRENS_CORNER_CONTENT_GUIDE[mode]) {
            const bodyLabel = document.getElementById('bodyLabel');
            const bodyHelp = document.getElementById('bodyHelp');

            if (bodyLabel && CHILDRENS_CORNER_CONTENT_GUIDE[mode].bodyLabel) {
                bodyLabel.innerHTML = CHILDRENS_CORNER_CONTENT_GUIDE[mode].bodyLabel;
            }

            if (bodyHelp && CHILDRENS_CORNER_CONTENT_GUIDE[mode].bodyHelp) {
                bodyHelp.textContent = CHILDRENS_CORNER_CONTENT_GUIDE[mode].bodyHelp;
            }
        }

        if (artFileInput) {
            artFileInput.required = Boolean(isChildrensCorner && mode === 'image' && !keepExistingArt);
        }

        if (projectDescription) {
            projectDescription.required = Boolean(isChildrensCorner && mode === 'project');
        }

        document.querySelectorAll('.childrens-corner-flow input, .childrens-corner-flow textarea, .childrens-corner-flow select').forEach((field) => {
            if (field.id === 'bodyEditor' || field.closest('#bodyEditorMount')) {
                return;
            }

            field.disabled = !isChildrensCorner;
        });

        document.querySelectorAll('.childrens-corner-flow-section input, .childrens-corner-flow-section textarea, .childrens-corner-flow-section select').forEach((field) => {
            field.disabled = !isChildrensCorner;
        });

        refreshPoetryEditorMode(isChildrensCorner && mode === 'poem' ? 'poetry' : contentType);
        syncChildrensCornerCategory();
        refreshChildrensCornerQuizRequiredState(isChildrensCorner && mode === 'quiz');
        mountChildrensCornerFeaturedImage(shareType);
        syncChildrensCornerCommentsSettings(isChildrensCorner);
        refreshBodyEditorVisibility(contentType);
    }

    function syncChildrensCornerCommentsSettings(isChildrensCorner) {
        const enableComments = document.getElementById('childrensCornerEnableComments');
        const allowComments = document.getElementById('allowComments');

        if (!isChildrensCorner || !enableComments || !allowComments) {
            return;
        }

        enableComments.checked = allowComments.checked;
    }

    function mountChildrensCornerFeaturedImage(shareType) {
        const featuredWrap = document.getElementById('communityFeaturedImagesWrap');
        const slot = document.getElementById('communityChildrensCornerFeaturedImagesSlot');
        const tagsCol = document.getElementById('communityTagsWrap');
        const panel = document.getElementById('childrensCornerFeaturedPanel');
        const featuredTypes = window.communityChildrensCornerFeaturedShareTypes || ['Story', 'Essay'];
        const isChildrensCorner = document.getElementById('contentType')?.value === 'childrens-corner';
        const showFeatured = isChildrensCorner && featuredTypes.includes(shareType);

        if (panel) {
            panel.style.display = showFeatured ? '' : 'none';
        }

        if (!featuredWrap) {
            return;
        }

        if (showFeatured && slot) {
            slot.appendChild(featuredWrap);
            featuredWrap.classList.remove('col-md-6');
            featuredWrap.style.display = '';
            window.communityFeaturedImages.max = 1;

            const featuredLabel = document.getElementById('featuredImagesLabel');
            const featuredHelp = document.getElementById('featuredImagesHelp');
            const featuredAddBtn = document.getElementById('featuredImagesAddBtn');

            if (featuredLabel) {
                featuredLabel.textContent = 'Featured image';
            }

            if (featuredHelp) {
                featuredHelp.textContent = 'Optional cover image for listing cards and sharing. JPG, PNG, or WebP, max 4 MB.';
            }

            if (featuredAddBtn) {
                featuredAddBtn.innerHTML = '<i class="fa-solid fa-image me-1"></i>Add featured image';
            }
        } else if (isChildrensCorner) {
            featuredWrap.style.display = 'none';
        }

        if (typeof updateFeaturedImagesUi === 'function') {
            updateFeaturedImagesUi();
        }
    }

    function editorLanguageOptionsForContentType(contentType) {
        const codes = contentType === 'poetry' ? COMMUNITY_POETRY_EDITOR_LANGUAGE_CODES : COMMUNITY_STANDARD_EDITOR_LANGUAGE_CODES;

        return codes.map(function (code) {
            return {
                code: code,
                label: (COMMUNITY_EDITOR_LANGUAGES[code] || { label: code }).label,
            };
        });
    }

    function refreshEditorLanguageOptions(contentType) {
        const select = document.getElementById('editorLanguageSelect');
        const hidden = document.getElementById('editorLanguageHidden');
        const label = document.getElementById('editorLanguageLabel');
        const help = document.getElementById('editorLanguageHelp');

        if (!select) {
            return;
        }

        const currentValue = hidden?.value || select.value || 'en';
        const options = editorLanguageOptionsForContentType(contentType);

        select.innerHTML = options.map(function (option) {
            return '<option value="' + option.code + '">' + option.label + '</option>';
        }).join('');

        const nextValue = options.some(function (option) { return option.code === currentValue; })
            ? currentValue
            : (options[0]?.code || 'en');

        applyEditorLanguage(nextValue, { skipSave: true });
        saveActiveEditorLanguage();

        if (label) {
            label.textContent = contentType === 'poetry' ? 'Poem script / language' : 'Editor language';
        }

        if (help) {
            help.textContent = contentType === 'poetry'
                ? 'Choose the script you are writing in. Unicode input is supported for all listed languages.'
                : 'Default is English. Switch to Hindi to write in Devanagari.';
        }
    }

    function refreshPoetryEditorMode(contentType) {
        const editorMount = document.getElementById('bodyEditorMount');
        const isPoetry = contentType === 'poetry';

        if (editorMount) {
            editorMount.classList.toggle('is-poetry-mode', isPoetry);
        }

        refreshEditorLanguageOptions(contentType);
    }

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
                const languageMeta = COMMUNITY_EDITOR_LANGUAGES[language] || COMMUNITY_EDITOR_LANGUAGES.en;
                root.setAttribute('lang', languageMeta.lang);
                root.setAttribute('dir', languageMeta.dir || 'ltr');
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
        existing: @json($communityFeaturedImagesForJs),
        pending: [],
        removed: [],
    };

    if (window.toastr) {
        window.toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 4000, extendedTimeOut: 2000 };
    }

    const COMMUNITY_LOCATION_TYPES_REQUIRING_PLACE = @json(\App\Models\CommunityPost::locationTypesRequiringPlace());
    const COMMUNITY_LOCATION_TYPE_GPS = @json(\App\Models\CommunityPost::LOCATION_TYPE_GPS);
    const COMMUNITY_BASE_LOCATION_TYPES = @json(\App\Models\CommunityPost::locationTypeOptions());
    const COMMUNITY_STRUCTURED_LOCATION_TYPES = ['news', 'reports', 'awareness', 'business'];
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

    function usesStructuredCommunityLocation(contentType) {
        return COMMUNITY_STRUCTURED_LOCATION_TYPES.includes(contentType);
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

    function mountStructuredLocationFields(contentType) {
        const wrapper = document.getElementById('communityStructuredLocationWrapper');
        const hiddenSlot = document.getElementById('communityStructuredLocationHiddenSlot');
        const newsSlot = document.getElementById('communityNewsLocationSlot');
        const reportSlot = document.getElementById('communityReportLocationSlot');
        const awarenessSlot = document.getElementById('communityAwarenessLocationSlot');
        const businessSlot = document.getElementById('communityBusinessLocationSlot');
        const commonLocationSlot = document.getElementById('communityCommonLocationSlot');

        if (!wrapper) {
            return;
        }

        const isNews = contentType === 'news';
        const isReport = contentType === 'reports';
        const isAwareness = contentType === 'awareness';
        const isBusiness = contentType === 'business';
        const usesStructured = usesStructuredCommunityLocation(contentType);
        let targetSlot = hiddenSlot;

        if (isNews) {
            targetSlot = newsSlot;
        } else if (isReport) {
            targetSlot = reportSlot;
        } else if (isAwareness) {
            targetSlot = awarenessSlot;
        } else if (isBusiness) {
            targetSlot = businessSlot;
        }

        if (targetSlot && wrapper.parentElement !== targetSlot) {
            targetSlot.appendChild(wrapper);
        }

        if (commonLocationSlot) {
            commonLocationSlot.style.display = usesStructured ? 'none' : '';
            commonLocationSlot.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = usesStructured;
            });
        }

        document.querySelectorAll('.structured-location-required').forEach((field) => {
            field.required = usesStructured;
            field.disabled = !usesStructured;
        });

        const localityField = document.getElementById('communityLocationLocality');
        const localityLabel = document.getElementById('communityLocationLocalityLabel');

        if (localityField) {
            localityField.required = usesStructured && (isAwareness || isBusiness);
            localityField.disabled = !usesStructured;

            if (isAwareness || isBusiness) {
                localityField.classList.add('structured-location-required');
            } else {
                localityField.classList.remove('structured-location-required');
                localityField.required = false;
            }
        }

        if (localityLabel) {
            localityLabel.innerHTML = (isAwareness || isBusiness)
                ? 'Area <span class="text-danger">*</span>'
                : 'Locality';
        }

        document.querySelectorAll('#communityStructuredLocationWrapper input[name="location_lat"], #communityStructuredLocationWrapper input[name="location_lng"]').forEach((field) => {
            field.disabled = !usesStructured;
        });

        if (usesStructured) {
            window.requestAnimationFrame(function () {
                initCommunityGpsMap();
            });
        }
    }

    function mountNewsParticipationFields(isNews, isAwareness, isBusiness) {
        const publicParticipationWrap = document.getElementById('publicParticipationWrap');
        const newsParticipationWrap = document.getElementById('newsParticipationWrap');
        const allowSharingWrap = document.getElementById('allowSharingWrap');
        const allowPollWrap = document.getElementById('allowPollWrap');
        const awarenessParticipationWrap = document.getElementById('awarenessParticipationWrap');
        const awarenessPollWrap = document.getElementById('awarenessPollWrap');
        const awarenessPollQuestionWrap = document.getElementById('awarenessPollQuestionWrap');
        const businessParticipationWrap = document.getElementById('businessParticipationWrap');
        const businessPollWrap = document.getElementById('businessPollWrap');
        const businessPollFields = document.getElementById('businessPollFields');

        if (publicParticipationWrap) {
            publicParticipationWrap.style.display = (isNews || isAwareness || isBusiness) ? 'none' : '';
            publicParticipationWrap.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = isNews || isAwareness || isBusiness;
            });
        }

        if (newsParticipationWrap) {
            newsParticipationWrap.style.display = isNews ? '' : 'none';
        }

        if (allowSharingWrap) {
            allowSharingWrap.style.display = (isAwareness || isBusiness) ? 'none' : '';
            allowSharingWrap.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = isAwareness || isBusiness;
            });
        }

        if (allowPollWrap) {
            allowPollWrap.style.display = (isAwareness || isBusiness || isNews) ? 'none' : '';
            allowPollWrap.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = isAwareness || isBusiness || isNews;
            });
        }

        if (awarenessParticipationWrap) {
            awarenessParticipationWrap.style.display = isAwareness ? '' : 'none';
        }

        if (awarenessPollWrap) {
            awarenessPollWrap.style.display = isAwareness ? '' : 'none';
        }

        if (businessParticipationWrap) {
            businessParticipationWrap.style.display = isBusiness ? '' : 'none';
        }

        if (businessPollWrap) {
            businessPollWrap.style.display = isBusiness ? '' : 'none';
        }

        const awarenessAllowPoll = document.getElementById('awarenessAllowPoll');
        if (awarenessPollQuestionWrap) {
            const pollEnabled = isAwareness && awarenessAllowPoll?.checked;
            awarenessPollQuestionWrap.style.display = pollEnabled ? '' : 'none';
            const awarenessPollQuestion = document.getElementById('awarenessPollQuestion');
            if (awarenessPollQuestion) {
                awarenessPollQuestion.required = Boolean(pollEnabled);
                awarenessPollQuestion.disabled = !pollEnabled;
            }
        }

        const businessAllowPoll = document.getElementById('businessAllowPoll');
        if (businessPollFields) {
            const businessPollEnabled = isBusiness && businessAllowPoll?.checked;
            businessPollFields.style.display = businessPollEnabled ? '' : 'none';
            const businessPollQuestion = document.getElementById('businessPollQuestion');
            if (businessPollQuestion) {
                businessPollQuestion.required = Boolean(businessPollEnabled);
                businessPollQuestion.disabled = !businessPollEnabled;
            }
            const businessPollOptions = document.getElementById('businessPollOptions');
            if (businessPollOptions) {
                businessPollOptions.disabled = !businessPollEnabled;
            }
        }

        const awarenessHasEvent = document.getElementById('awarenessHasEvent');
        const awarenessEventFields = document.getElementById('awarenessEventFields');
        if (awarenessEventFields) {
            awarenessEventFields.style.display = isAwareness && awarenessHasEvent?.checked ? '' : 'none';
        }
    }

    function mountNewsMediaFields(isNews, isStories, isChildrensCorner, isAwareness, isBusiness) {
        const featuredWrap = document.getElementById('communityFeaturedImagesWrap');
        const featuredSlot = document.getElementById('communityNewsFeaturedImagesSlot');
        const storyFeaturedSlot = document.getElementById('communityStoryFeaturedImagesSlot');
        const awarenessFeaturedSlot = document.getElementById('communityAwarenessFeaturedImagesSlot');
        const businessFeaturedSlot = document.getElementById('communityBusinessFeaturedImagesSlot');
        const businessTagsSlot = document.getElementById('communityBusinessTagsSlot');
        const tagsCol = document.getElementById('communityTagsWrap');
        const videoWrap = document.getElementById('communityVideoWrap');
        const videoSlot = document.getElementById('communityNewsVideoSlot');
        const storyVideoSlot = document.getElementById('communityStoryVideoSlot');
        const awarenessVideoSlot = document.getElementById('communityAwarenessVideoSlot');
        const businessVideoSlot = document.getElementById('communityBusinessVideoSlot');
        const videoAnchor = document.getElementById('communityVideoHiddenSlot');
        const videoFieldLabel = document.getElementById('videoFieldLabel');
        const featuredLabel = document.getElementById('featuredImagesLabel');
        const featuredHelp = document.getElementById('featuredImagesHelp');
        const featuredAddBtn = document.getElementById('featuredImagesAddBtn');

        if (isChildrensCorner) {
            window.communityFeaturedImages.max = featuredWrap?.parentElement?.id === 'communityChildrensCornerFeaturedImagesSlot'
                ? 1
                : window.communityFeaturedImages.max;
            mountChildrensCornerFeaturedImage(document.getElementById('childShareType')?.value || '');
            if (videoWrap) {
                videoWrap.style.display = 'none';
            }
        } else {
            window.communityFeaturedImages.max = (isAwareness || isBusiness) ? 1 : 5;
            if (videoWrap) {
                videoWrap.style.display = '';
            }

            if (featuredWrap && tagsCol) {
                featuredWrap.style.display = '';
                if (isNews && featuredSlot) {
                    featuredSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isStories && storyFeaturedSlot) {
                    storyFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isAwareness && awarenessFeaturedSlot) {
                    awarenessFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isBusiness && businessFeaturedSlot) {
                    businessFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else {
                    tagsCol.parentElement.insertBefore(featuredWrap, tagsCol);
                    featuredWrap.classList.add('col-md-6');
                }
            }

            if (tagsCol) {
                if (isBusiness && businessTagsSlot) {
                    businessTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (communityTagsDefaultParent) {
                    if (communityTagsDefaultNextSibling && communityTagsDefaultNextSibling.parentElement === communityTagsDefaultParent) {
                        communityTagsDefaultParent.insertBefore(tagsCol, communityTagsDefaultNextSibling);
                    } else {
                        communityTagsDefaultParent.appendChild(tagsCol);
                    }
                    tagsCol.classList.add('col-md-6');
                    tagsCol.style.display = '';
                }
            }
        }

        if (videoWrap && videoAnchor) {
            if (isNews && videoSlot) {
                videoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isStories && storyVideoSlot) {
                storyVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isAwareness && awarenessVideoSlot) {
                awarenessVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isBusiness && businessVideoSlot) {
                businessVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (! isAwareness && ! isBusiness) {
                videoAnchor.insertAdjacentElement('afterend', videoWrap);
                videoWrap.classList.add('col-md-6');
            } else {
                videoWrap.style.display = 'none';
            }
        }

        if (videoFieldLabel) {
            if (isAwareness) {
                videoFieldLabel.innerHTML = 'Video upload / link <span class="text-muted fw-normal">(optional)</span>';
            } else if (isBusiness) {
                videoFieldLabel.innerHTML = 'Business video <span class="text-muted fw-normal">(optional)</span>';
            } else {
                videoFieldLabel.innerHTML = isStories
                    ? 'Video story <span class="text-muted fw-normal">(optional)</span>'
                    : 'Video <span class="text-muted fw-normal">(optional)</span>';
            }
        }

        if (featuredLabel && ! isChildrensCorner) {
            if (isAwareness) {
                featuredLabel.innerHTML = 'Campaign banner <span class="text-danger">*</span> <span class="text-muted fw-normal">(recommended)</span>';
            } else if (isBusiness) {
                featuredLabel.innerHTML = 'Cover image <span class="text-muted fw-normal">(recommended)</span>';
            } else if (isStories) {
                featuredLabel.textContent = 'Cover image (recommended)';
            } else if (isNews) {
                featuredLabel.textContent = 'Featured image (recommended)';
            } else {
                featuredLabel.textContent = 'Featured images';
            }
        }

        if (featuredHelp && ! isChildrensCorner) {
            if (isAwareness) {
                featuredHelp.textContent = 'Used for homepage, social media, and awareness listings. JPG, PNG, or WebP, max 4 MB.';
            } else if (isBusiness) {
                featuredHelp.textContent = 'Recommended size: 1200 × 630 px. Used for listings, social sharing, and homepage. JPG, PNG, or WebP, max 4 MB.';
            } else if (isStories) {
                featuredHelp.textContent = 'Used for story cards, social sharing, and homepage. Upload your cover image first. JPG, PNG, or WebP, max 4 MB each.';
            } else if (isNews) {
                featuredHelp.textContent = 'Upload a lead image first, then add additional photos. Examples: event photos, road damage, flooding, community activities, meetings. JPG, PNG, or WebP, max 4 MB each.';
            } else {
                featuredHelp.textContent = 'Upload up to 5 images. JPG, PNG, or WebP, max 4 MB each.';
            }
        }

        if (featuredAddBtn && ! isChildrensCorner) {
            if (isAwareness) {
                featuredAddBtn.innerHTML = '<i class="fa-solid fa-image me-1"></i>Add campaign banner';
            } else if (isBusiness) {
                featuredAddBtn.innerHTML = '<i class="fa-solid fa-image me-1"></i>Add cover image';
            } else if (isStories) {
                featuredAddBtn.innerHTML = '<i class="fa-solid fa-image me-1"></i>Add cover image';
            } else if (isNews) {
                featuredAddBtn.innerHTML = '<i class="fa-solid fa-images me-1"></i>Add featured / additional images';
            } else {
                featuredAddBtn.innerHTML = '<i class="fa-solid fa-images me-1"></i>Add images';
            }
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
        const contentType = document.getElementById('contentType')?.value || '';
        const hiddenSlot = document.getElementById('communityStructuredLocationHiddenSlot');
        const wrapper = document.getElementById('communityStructuredLocationWrapper');

        if (!mapEl || !window.google?.maps || !usesStructuredCommunityLocation(contentType)) {
            return;
        }

        if (wrapper && hiddenSlot && wrapper.parentElement === hiddenSlot) {
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
        const scopeNote = document.getElementById('communityLocationScopeNote');
        const scopeText = document.getElementById('communityLocationScopeText');
        const locationInput = document.getElementById('communityLocation');
        const locationHelp = document.getElementById('locationHelp');
        const contentType = document.getElementById('contentType')?.value || '';

        if (usesStructuredCommunityLocation(contentType)) {
            return;
        }

        if (!typeSelect || !specificWrap) {
            return;
        }

        const locationType = typeSelect.value;
        const needsSpecific = requiresSpecificCommunityLocation(locationType);

        specificWrap.style.display = needsSpecific ? '' : 'none';

        if (locationInput) {
            locationInput.required = needsSpecific;
        }

        const helpText = {
            state: 'Search and select the state-level location from Google Places.',
            district: 'Search and select the district-level location from Google Places.',
            city: 'Search and select the city from Google Places so the story is location-indexed.',
            town: 'Search and select the town from Google Places so the story is location-indexed.',
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
        const hasTypeSection = Boolean(window.communityTypes[selectedType]) && !['news', 'reports', 'stories', 'poetry', 'biography', 'autobiography', 'childrens-corner', 'awareness', 'business'].includes(selectedType);

        categorySelect.innerHTML = '<option value="">Select category</option>';
        help.textContent = type ? type.description : '';

        const categoryLabel = document.getElementById('categoryLabel');
        const categoryHelp = document.getElementById('categoryHelp');
        const subCategoryWrap = document.getElementById('subCategoryFieldWrap');
        const subCategoryHelp = document.getElementById('subCategoryHelp');

        categorySelect.required = true;
        categorySelect.disabled = false;
        categoryWrap.style.display = '';

        const isStories = selectedType === 'stories';
        const isPoetry = selectedType === 'poetry';
        const isLifeStory = isLifeStoryContentType(selectedType);
        const isAutobiography = selectedType === 'autobiography';
        const isChildrensCorner = selectedType === 'childrens-corner';
        const isAwareness = selectedType === 'awareness';
        const isBusiness = selectedType === 'business';

        if (isChildrensCorner) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncChildrensCornerCategory();
        } else if (isAwareness) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncAwarenessCategory();
        } else if (isBusiness) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncBusinessCategory();
        } else {
            categoryWrap.style.display = '';
            categorySelect.disabled = false;
        }

        if (categoryLabel) {
            categoryLabel.innerHTML = (isStories || isReport || isPoetry)
                ? 'Main Category <span class="text-danger">*</span>'
                : 'Category <span class="text-danger">*</span>';
        }

        if (categoryHelp) {
            categoryHelp.innerHTML = isStories
                ? 'Examples:<br>Inspirational Stories · Life Experiences · Motivational Stories · Short Stories · Social Stories · Family Stories<br>'
                    + "Children's Stories · Educational Stories · Travel Stories · Historical Stories · Business Stories · Village Stories<br>"
                    + "Women's Stories · Senior Citizen Stories · Student Stories · Success Stories · Humor Stories · Fiction Stories"
                : (isPoetry
                    ? 'Examples:<br>Poetry · Shayari · Ghazal · Nazm · Geet (Song) · Haiku · Doha · Free Verse · Children\'s Poetry · Spiritual Poetry'
                    : (isLifeStory
                        ? (isAutobiography
                            ? 'Examples:<br>Personal Journey · Career Journey · Business Journey · Educational Journey · Women\'s Journey · Senior Citizen Journey<br>'
                                + "Farmer's Journey · Social Service Journey · Professional Journey · Spiritual Journey"
                            : 'Examples:<br>Freedom Fighters · Scientists · Entrepreneurs · Teachers · Social Workers · Local Heroes')
                        : ''));
        }

        if (subCategoryWrap) {
            subCategoryWrap.style.display = isPoetry ? '' : 'none';
        }

        if (subCategoryHelp) {
            subCategoryHelp.innerHTML = isPoetry
                ? 'Examples:<br>Love Poetry · Inspirational Poetry · Nature Poetry · Patriotic Poetry · Social Poetry · Humor Poetry · Spiritual Poetry<br>'
                    + "Women's Poetry · Student Poetry · Environmental Poetry · Village Poetry"
                : '';
        }

        if (type) {
            const categoryGroups = type.categoryGroups || null;

            if (categoryGroups && typeof categoryGroups === 'object') {
                Object.entries(categoryGroups).forEach(([groupLabel, groupCategories]) => {
                    const optgroup = document.createElement('optgroup');
                    optgroup.label = groupLabel;
                    groupCategories.forEach((category) => {
                        const option = document.createElement('option');
                        option.value = category;
                        option.textContent = category;
                        option.selected = category === selected;
                        optgroup.appendChild(option);
                    });
                    categorySelect.appendChild(optgroup);
                });
            } else {
                type.categories.forEach((category) => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    option.selected = category === selected;
                    categorySelect.appendChild(option);
                });
            }

            if (selected && !categorySelect.querySelector('option[value="' + CSS.escape(selected) + '"]')) {
                const legacyOption = document.createElement('option');
                legacyOption.value = selected;
                legacyOption.textContent = selected + ' (legacy)';
                legacyOption.selected = true;
                categorySelect.insertBefore(legacyOption, categorySelect.options[1] || null);
            }
        }

        document.querySelectorAll('.type-extra').forEach((field) => {
            field.style.display = contentTypeMatchesDataset(selectedType, field.dataset.for) ? '' : 'none';
        });

        document.querySelectorAll('.general-extra').forEach((field) => {
            field.style.display = (isNews || isReport || hasTypeSection || isPoetry || isLifeStory || isChildrensCorner || isAwareness || isBusiness) ? 'none' : '';
        });

        document.querySelectorAll('.general-extra input, .general-extra textarea, .general-extra select').forEach((field) => {
            field.disabled = isNews || isReport || hasTypeSection || isPoetry || isLifeStory || isChildrensCorner || isAwareness || isBusiness;
        });

        document.querySelectorAll('.news-required').forEach((field) => {
            field.required = isNews;
        });

        document.querySelectorAll('.stories-required').forEach((field) => {
            field.required = isStories;
        });

        document.querySelectorAll('.poetry-required').forEach((field) => {
            field.required = isPoetry;
        });

        document.querySelectorAll('.autobiography-required').forEach((field) => {
            field.required = isLifeStory;
        });

        document.querySelectorAll('.childrens-corner-required').forEach((field) => {
            field.required = isChildrensCorner;
        });

        document.querySelectorAll('.childrens-corner-consent-required').forEach((field) => {
            field.required = isChildrensCorner;
        });

        document.querySelectorAll('.childrens-corner-safety-required').forEach((field) => {
            field.required = isChildrensCorner;
        });

        document.querySelectorAll('.awareness-required').forEach((field) => {
            field.required = isAwareness;
        });

        document.querySelectorAll('.awareness-posted-by-required').forEach((field) => {
            field.required = isAwareness;
        });

        document.querySelectorAll('.business-required').forEach((field) => {
            field.required = isBusiness;
        });

        document.querySelectorAll('.awareness-flow input, .awareness-flow textarea, .awareness-flow select').forEach((field) => {
            if (field.id === 'bodyEditor' || field.closest('#bodyEditorMount') || field.closest('#communityStructuredLocationWrapper') || field.closest('#communityFeaturedImagesWrap') || field.closest('#communityVideoWrap')) {
                return;
            }

            field.disabled = !isAwareness;
        });

        document.querySelectorAll('.business-flow input, .business-flow textarea, .business-flow select').forEach((field) => {
            if (field.id === 'bodyEditor' || field.closest('#bodyEditorMount') || field.closest('#communityStructuredLocationWrapper') || field.closest('#communityFeaturedImagesWrap') || field.closest('#communityVideoWrap')) {
                return;
            }

            field.disabled = !isBusiness;
        });

        const childSchoolName = document.getElementById('childSchoolName');
        const schoolPrivacySelected = document.querySelector('input[name="childrens_corner_privacy_setting"][value="school_community"]')?.checked;
        if (childSchoolName) {
            childSchoolName.required = isChildrensCorner && Boolean(schoolPrivacySelected);
        }

        refreshLifeTimelineRequiredState(isLifeStory);

        document.querySelectorAll('.autobiography-flow input, .autobiography-flow textarea, .autobiography-flow select').forEach((field) => {
            if (field.id === 'bodyEditor' || field.closest('#bodyEditorMount')) {
                return;
            }

            field.disabled = !isLifeStory;
        });

        document.querySelectorAll('.life-story-flow-section input, .life-story-flow-section textarea, .life-story-flow-section select').forEach((field) => {
            if (field.id === 'bodyEditor' || field.closest('#bodyEditorMount')) {
                return;
            }

            field.disabled = !isLifeStory;
        });

        const bodyEditorMount = document.getElementById('bodyEditorMount');
        const bodyEditorField = document.getElementById('bodyEditor');
        if (bodyEditorMount && !isChildrensCorner) {
            bodyEditorMount.style.display = '';
        }
        if (bodyEditorField) {
            bodyEditorField.disabled = false;
        }

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
            bodyLabel: 'News content <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for the full news story. Add images, formatting, and complete narrative detail here.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select the news location from Google Places so the story is location-indexed.',
        } : (isPoetry ? {
            excerptLabel: 'Short excerpt',
            excerptPlaceholder: 'A brief teaser for your poem',
            excerptHelp: 'Optional summary shown in listing cards.',
            bodyLabel: 'Poetry editor <span class="text-danger">*</span>',
            bodyHelp: 'Write your poem below. Use line breaks for verses and blank lines between stanzas.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select a Google Places suggestion so latitude and longitude are saved.',
        } : (isAwareness ? {
            excerptLabel: 'Awareness summary',
            excerptPlaceholder: 'Summarize the issue, why it matters, and what readers should do.',
            excerptHelp: 'A concise standfirst shown in community listings.',
            bodyLabel: 'Awareness content <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for Problem, Why It Matters, Facts & Statistics, Solutions, and Call To Action.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Enter country, state, district, city, and area. Use Google Places search to auto-fill fields.',
        } : (isBusiness ? {
            excerptLabel: 'Business summary',
            excerptPlaceholder: 'Summarize the business insight, opportunity, or story in one or two sentences.',
            excerptHelp: 'A concise standfirst shown in business listings.',
            bodyLabel: 'Business content <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for Business Problem, Background, Solution, Results, Lessons Learned, and Recommendations.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Enter country, state, district, and city. Use Google Places search to auto-fill fields.',
        } : {
            excerptLabel: 'Short excerpt',
            excerptPlaceholder: '',
            excerptHelp: 'A concise teaser shown in listing cards.',
            bodyLabel: 'Body <span class="text-danger">*</span>',
            bodyHelp: 'Add text and images together. Select an image to resize or align it.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select a Google Places suggestion so latitude and longitude are saved.',
        }))));

        document.getElementById('excerptLabel').textContent = fieldCopy.excerptLabel;
        document.getElementById('excerptField').placeholder = fieldCopy.excerptPlaceholder;
        document.getElementById('excerptHelp').textContent = fieldCopy.excerptHelp;
        document.getElementById('bodyLabel').innerHTML = fieldCopy.bodyLabel;
        document.getElementById('bodyHelp').textContent = fieldCopy.bodyHelp;
        document.getElementById('locationLabel').innerHTML = fieldCopy.locationLabel;

        const storyContentGuide = document.getElementById('storyContentGuide');
        if (storyContentGuide) {
            storyContentGuide.style.display = isStories ? '' : 'none';
        }

        const poetryContentGuide = document.getElementById('poetryContentGuide');
        if (poetryContentGuide) {
            poetryContentGuide.style.display = isPoetry ? '' : 'none';
        }

        const autobiographyContentGuide = document.getElementById('autobiographyContentGuide');
        if (autobiographyContentGuide) {
            autobiographyContentGuide.style.display = isLifeStory ? '' : 'none';
        }

        const awarenessContentGuide = document.getElementById('awarenessContentGuide');
        if (awarenessContentGuide) {
            awarenessContentGuide.style.display = isAwareness ? '' : 'none';
        }

        const businessContentGuide = document.getElementById('businessContentGuide');
        if (businessContentGuide) {
            businessContentGuide.style.display = isBusiness ? '' : 'none';
        }

        refreshPoetrySeriesFields();
        refreshChildrensCornerContentMode();

        mountStructuredLocationFields(selectedType);
        const commonLocationSlot = document.getElementById('communityCommonLocationSlot');
        if (commonLocationSlot) {
            commonLocationSlot.style.display = (isNews || isReport || isChildrensCorner || isAwareness || isBusiness) ? 'none' : '';
            commonLocationSlot.querySelectorAll('input, select, textarea').forEach((field) => {
                if (isChildrensCorner) {
                    field.disabled = true;
                    field.required = false;
                } else if (!isNews && !isReport && !isAwareness && !isBusiness) {
                    field.disabled = false;
                }
            });
        }
        mountNewsMediaFields(isNews, isStories, isChildrensCorner, isAwareness, isBusiness);
        mountNewsParticipationFields(isNews, isAwareness, isBusiness);
        refreshCommunityLocationTypeOptions(isReport);
        refreshBookLayoutMode(selectedType);
        refreshCommunityLocationFields(fieldCopy.locationHelp);

        if (typeof refreshCommunityActionFields === 'function') {
            refreshCommunityActionFields();
        }

        const allowPollWrap = document.getElementById('allowPollWrap');
        const allowPoll = document.getElementById('allowPoll');
        if (allowPollWrap) {
            allowPollWrap.style.display = (isReport || isAwareness || isBusiness) ? 'none' : '';
        }
        if (isReport && allowPoll) {
            allowPoll.checked = false;
        }
        if (typeof refreshPollFields === 'function') {
            refreshPollFields();
        }

        if (isLifeStory && window.communityBodyEditor) {
            window.requestAnimationFrame(function () {
                bodyEditorMount?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }

        refreshBodyEditorVisibility(selectedType).then(function () {
            if (['poetry', 'awareness', 'business', 'news'].includes(selectedType)) {
                window.requestAnimationFrame(function () {
                    document.getElementById('bodyEditorMount')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
        });
    }

    function isBookContentType(type) {
        return (window.communityBookTypes || []).includes(type);
    }

    function isChapterContentType(type) {
        return type === 'autobiography';
    }

    function chapterTabLabel(page, index) {
        const title = (page?.title || '').trim();
        if (title !== '') {
            return title.length > 28 ? title.slice(0, 28) + '…' : title;
        }

        return 'Chapter ' + (index + 1);
    }

    function saveActiveChapterMeta() {
        if (!isChapterContentType(document.getElementById('contentType')?.value || '')) {
            return;
        }

        const titleInput = document.getElementById('activeChapterTitle');
        const summaryInput = document.getElementById('activeChapterSummary');
        if (!titleInput || !summaryInput) {
            return;
        }

        window.communityBookPages[window.communityActiveBookPage] = window.communityBookPages[window.communityActiveBookPage] || {
            content: '',
            language: 'en',
            title: '',
            summary: '',
        };
        window.communityBookPages[window.communityActiveBookPage].title = titleInput.value.trim();
        window.communityBookPages[window.communityActiveBookPage].summary = summaryInput.value.trim();
    }

    function loadActiveChapterMeta() {
        const titleInput = document.getElementById('activeChapterTitle');
        const summaryInput = document.getElementById('activeChapterSummary');
        if (!titleInput || !summaryInput) {
            return;
        }

        const page = window.communityBookPages[window.communityActiveBookPage] || {};
        titleInput.value = page.title || '';
        summaryInput.value = page.summary || '';
    }

    function refreshChapterEditorLabels(selectedType) {
        const chapterMode = isChapterContentType(selectedType);
        const bookBodyLabel = document.getElementById('bookBodyLabel');
        const bookBodyHelp = document.getElementById('bookBodyHelp');
        const chapterMetaFields = document.getElementById('bookChapterMetaFields');
        const addBookPageBtnLabel = document.getElementById('addBookPageBtnLabel');
        const removeBookPageBtnLabel = document.getElementById('removeBookPageBtnLabel');
        const bookPageTabs = document.getElementById('bookPageTabs');
        const titleInput = document.getElementById('activeChapterTitle');

        if (bookBodyLabel) {
            bookBodyLabel.innerHTML = chapterMode
                ? 'Autobiography content <span class="text-danger">*</span>'
                : 'Book pages <span class="text-danger">*</span>';
        }

        if (bookBodyHelp) {
            bookBodyHelp.textContent = chapterMode
                ? 'Add multiple chapters. Each chapter has a title, summary, and rich content with images.'
                : (selectedType === 'stories'
                    ? 'Write your story page by page, like a book. Use the rich text editor below for each page.'
                    : 'Write each page using the editor below. Switch tabs to edit Page 1, Page 2, and so on.');
        }

        if (chapterMetaFields) {
            chapterMetaFields.style.display = chapterMode ? '' : 'none';
        }

        if (addBookPageBtnLabel) {
            addBookPageBtnLabel.textContent = chapterMode ? 'Add another chapter' : 'Add another page';
        }

        if (removeBookPageBtnLabel) {
            removeBookPageBtnLabel.textContent = chapterMode ? 'Remove chapter' : 'Remove page';
        }

        if (bookPageTabs) {
            bookPageTabs.setAttribute('aria-label', chapterMode ? 'Autobiography chapters' : 'Book pages');
        }

        if (titleInput) {
            titleInput.required = chapterMode;
        }
    }

    function saveActiveBookPageContent() {
        if (!window.communityBodyEditor || !isBookContentType(document.getElementById('contentType').value)) {
            return;
        }

        saveActiveChapterMeta();
        window.communityBookPages[window.communityActiveBookPage] = window.communityBookPages[window.communityActiveBookPage] || {
            content: '',
            language: 'en',
            title: '',
            summary: '',
        };
        window.communityBookPages[window.communityActiveBookPage].content = window.communityBodyEditor.getData();
        saveActiveEditorLanguage();
        document.getElementById('bodyEditor').value = window.communityBookPages[window.communityActiveBookPage].content;
    }

    function renderBookPageTabs() {
        const tabs = document.getElementById('bookPageTabs');
        const removeBtn = document.getElementById('removeBookPageBtn');
        const selectedType = document.getElementById('contentType')?.value || '';
        const chapterMode = isChapterContentType(selectedType);
        if (!tabs) {
            return;
        }

        tabs.innerHTML = '';
        window.communityBookPages.forEach(function (page, index) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm book-page-tab' + (index === window.communityActiveBookPage ? ' active' : '');
            button.textContent = chapterMode ? chapterTabLabel(page, index) : ('Page ' + (index + 1));
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
            title.textContent = chapterMode
                ? chapterTabLabel(window.communityBookPages[window.communityActiveBookPage] || {}, window.communityActiveBookPage)
                : ('Page ' + (window.communityActiveBookPage + 1));
        }
    }

    function switchBookPage(index) {
        if (!window.communityBodyEditor) {
            return;
        }

        saveActiveBookPageContent();
        window.communityActiveBookPage = index;
        window.communityBookPages[index] = window.communityBookPages[index] || {
            content: '',
            language: 'en',
            title: '',
            summary: '',
        };
        window.communitySwitchingBookPage = true;
        window.communityBodyEditor.setData(window.communityBookPages[index].content || '');
        window.communitySwitchingBookPage = false;
        applyEditorLanguage(window.communityBookPages[index].language || 'en', { skipSave: true });
        loadActiveChapterMeta();
        renderBookPageTabs();
        window.communityBodyEditor.editing.view.focus();
    }

    function refreshBookLayoutMode(selectedType) {
        const bookMode = isBookContentType(selectedType);
        const standardHeader = document.getElementById('standardBodyHeader');
        const bookHeader = document.getElementById('bookBodyHeader');
        const editorField = document.getElementById('bodyEditor');

        refreshChapterEditorLabels(selectedType);

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

        if (!Array.isArray(window.communityBookPages) || window.communityBookPages.length === 0) {
            window.communityBookPages = [{
                content: editorField.value || '',
                language: getActiveEditorLanguage(),
                title: '',
                summary: '',
            }];
        }

        window.communityBookPages = window.communityBookPages.map(function (page) {
            return {
                content: page.content || '',
                language: page.language || 'en',
                title: page.title || '',
                summary: page.summary || '',
            };
        });

        window.communityActiveBookPage = Math.min(window.communityActiveBookPage, window.communityBookPages.length - 1);
        renderBookPageTabs();
        window.communityBodyEditor.setData(window.communityBookPages[window.communityActiveBookPage].content || '');
        applyEditorLanguage(window.communityBookPages[window.communityActiveBookPage].language || 'en', { skipSave: true });
        loadActiveChapterMeta();
    }

    function appendBookPagesToFormData(formData) {
        saveActiveBookPageContent();
        formData.delete('body');
        formData.delete('book_pages');

        window.communityBookPages.forEach(function (page, index) {
            formData.append('book_pages[' + index + '][content]', page.content || '');
            formData.append('book_pages[' + index + '][language]', normalizeEditorLanguage(page.language || 'en'));
            formData.append('book_pages[' + index + '][title]', page.title || '');
            formData.append('book_pages[' + index + '][summary]', page.summary || '');
        });

        formData.append('body', window.communityBookPages.map(function (page) {
            return page.content || '';
        }).join('\n'));
    }

    document.getElementById('addBookPageBtn')?.addEventListener('click', function () {
        saveActiveBookPageContent();
        window.communityBookPages.push({
            content: '',
            language: getActiveEditorLanguage(),
            title: '',
            summary: '',
        });
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
        loadActiveChapterMeta();
    });

    document.getElementById('activeChapterTitle')?.addEventListener('input', function () {
        saveActiveChapterMeta();
        renderBookPageTabs();
    });

    document.getElementById('activeChapterSummary')?.addEventListener('input', function () {
        saveActiveChapterMeta();
    });

    document.getElementById('contentType').addEventListener('change', function () {
        document.getElementById('categorySelect').dataset.selected = '';
        refreshCommunityCategories();
        refreshBodyEditorVisibility(this.value);
        if (window.communityBodyEditor) {
            refreshBookLayoutMode(this.value);
        }
    });

    document.getElementById('childShareType')?.addEventListener('change', function () {
        refreshChildrensCornerContentMode();
    });

    document.getElementById('removeExistingChildrensCornerArtBtn')?.addEventListener('click', function () {
        document.getElementById('keepExistingChildrensCornerArt')?.remove();
        document.getElementById('existingChildrensCornerArtPreview')?.remove();
        document.getElementById('childrensCornerArtFile')?.setAttribute('required', 'required');
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

    document.getElementById('awarenessAllowPoll')?.addEventListener('change', function () {
        mountNewsParticipationFields(false, document.getElementById('contentType')?.value === 'awareness', document.getElementById('contentType')?.value === 'business');
    });
    document.getElementById('awarenessHasEvent')?.addEventListener('change', function () {
        mountNewsParticipationFields(false, document.getElementById('contentType')?.value === 'awareness', document.getElementById('contentType')?.value === 'business');
    });
    document.getElementById('businessAllowPoll')?.addEventListener('change', function () {
        mountNewsParticipationFields(false, document.getElementById('contentType')?.value === 'awareness', document.getElementById('contentType')?.value === 'business');
    });

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

    try {
        refreshCommunityCategories();
    } catch (error) {
        console.error('Unable to initialize community form fields.', error);
    }

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

    function contentTypeUsesBodyEditor(contentType) {
        if (!contentType) {
            return false;
        }

        if (contentType === 'childrens-corner') {
            const shareType = document.getElementById('childShareType')?.value || '';
            const mode = getChildrensCornerContentMode(shareType);

            return mode === 'rich_text' || mode === 'poem';
        }

        return true;
    }

    function refreshBodyEditorVisibility(contentType) {
        const bodyContentSection = document.getElementById('bodyContentSection');
        const bodyEditorMount = document.getElementById('bodyEditorMount');
        const bodyEditorPlaceholder = document.getElementById('bodyEditorPlaceholder');
        const usesEditor = contentTypeUsesBodyEditor(contentType);

        if (bodyContentSection) {
            bodyContentSection.classList.toggle('is-waiting-for-type', !usesEditor);
        }

        if (bodyEditorPlaceholder) {
            bodyEditorPlaceholder.style.display = usesEditor ? 'none' : '';
        }

        if (bodyEditorMount && usesEditor) {
            bodyEditorMount.style.display = '';
        }

        if (!usesEditor) {
            return Promise.resolve(null);
        }

        return initCommunityBodyEditor().then(function (editor) {
            if (editor && bodyEditorMount) {
                refreshBookLayoutMode(contentType);
                refreshPoetryEditorMode(contentType);
            }

            return editor;
        });
    }

    let communityBodyEditorInitPromise = null;

    function initCommunityBodyEditor() {
        const bodyEditor = document.querySelector('#bodyEditor');
        const bodyEditorMount = document.getElementById('bodyEditorMount');

        if (!bodyEditor || !bodyEditorMount) {
            console.error('Community body editor mount was not found.');
            return Promise.resolve(null);
        }

        if (!contentTypeUsesBodyEditor(document.getElementById('contentType')?.value || '')) {
            return Promise.resolve(null);
        }

        bodyEditor.disabled = false;
        bodyEditorMount.style.display = '';

        if (window.communityBodyEditor) {
            return Promise.resolve(window.communityBodyEditor);
        }

        if (communityBodyEditorInitPromise) {
            return communityBodyEditorInitPromise;
        }

        if (typeof ClassicEditor === 'undefined' && typeof CKEDITOR === 'undefined') {
            console.error('CKEditor failed to load.');
            notify('error', 'Rich text editor failed to load. Please refresh the page or check your internet connection.');
            return Promise.resolve(null);
        }

        const EditorClass = (window.CKEDITOR && window.CKEDITOR.ClassicEditor) || window.ClassicEditor;

        communityBodyEditorInitPromise = EditorClass.create(bodyEditor, {
            extraPlugins: [communityUploadAdapterPlugin, communityImageTextFlowPlugin],
            removePlugins: [
                'RealTimeCollaborativeEditing',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'PresenceList',
                'Comments',
                'TrackChanges',
                'RevisionHistory',
            ],
            toolbar: {
                items: [
                    'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                    'uploadImage', 'insertTable', 'mediaEmbed', 'blockQuote', '|', 'undo', 'redo',
                ],
            },
            mediaEmbed: {
                previewsInData: true,
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
                refreshPoetryEditorMode(document.getElementById('contentType').value);

                return editor;
            })
            .catch((error) => {
                communityBodyEditorInitPromise = null;
                console.error(error);
                notify('error', 'Unable to load the body editor.');
                return null;
            });

        return communityBodyEditorInitPromise;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            refreshBodyEditorVisibility(document.getElementById('contentType')?.value || '');
        });
    } else {
        refreshBodyEditorVisibility(document.getElementById('contentType')?.value || '');
    }

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

    function featuredImageBadgeLabel(index, isPending) {
        const contentType = document.getElementById('contentType')?.value || '';
        const primaryLabel = contentType === 'awareness' ? 'Banner' : 'Cover';

        if (isPending) {
            return featuredImagesState.existing.length === 0 && index === 0 ? primaryLabel : 'New';
        }

        return index === 0 ? primaryLabel : 'Saved';
    }

    function updateFeaturedImagesUi() {
        featuredImagesPreview.innerHTML = '';
        featuredImagesRemovedWrap.innerHTML = '';
        featuredImagesCount.textContent = featuredImagesTotal() + ' / ' + featuredImagesState.max;
        featuredImagesAddBtn.disabled = featuredImagesTotal() >= featuredImagesState.max;

        featuredImagesState.existing.forEach((image, index) => {
            featuredImagesPreview.appendChild(createFeaturedImageCard({
                src: image.url,
                label: featuredImageBadgeLabel(index, false),
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
                label: featuredImageBadgeLabel(index, true),
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
                notify('error', 'You can upload up to ' + featuredImagesState.max + ' featured image' + (featuredImagesState.max === 1 ? '' : 's') + '.');
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

    const maxStoryAudioBytes = 20971520;
    const storyAudioUploadWrap = document.getElementById('storyAudioUploadWrap');
    const storyAudioRecordingWrap = document.getElementById('storyAudioRecordingWrap');
    const storyAudioFileInput = document.getElementById('storyAudioFile');
    const keepExistingStoryAudioInput = document.getElementById('keepExistingStoryAudio');
    const existingStoryAudioPreview = document.getElementById('existingStoryAudioPreview');
    const removeExistingStoryAudioBtn = document.getElementById('removeExistingStoryAudioBtn');
    const storyAudioRecordBtn = document.getElementById('storyAudioRecordBtn');
    const storyAudioStopBtn = document.getElementById('storyAudioStopBtn');
    const storyAudioClearRecordingBtn = document.getElementById('storyAudioClearRecordingBtn');
    const storyAudioRecordingStatus = document.getElementById('storyAudioRecordingStatus');
    const storyAudioRecordingPreview = document.getElementById('storyAudioRecordingPreview');
    let storyAudioRecorder = null;
    let storyAudioStream = null;
    let storyAudioChunks = [];
    let storyAudioBlob = null;

    function refreshStoryAudioPanels() {
        const selected = document.querySelector('input[name="story_audio_source_type"]:checked')?.value || 'none';
        storyAudioUploadWrap?.classList.toggle('is-active', selected === 'upload');
        storyAudioRecordingWrap?.classList.toggle('is-active', selected === 'recording');
    }

    document.querySelectorAll('input[name="story_audio_source_type"]').forEach((input) => {
        input.addEventListener('change', refreshStoryAudioPanels);
    });

    removeExistingStoryAudioBtn?.addEventListener('click', () => {
        document.getElementById('storyAudioSourceNone')?.click();
        existingStoryAudioPreview?.remove();
        keepExistingStoryAudioInput?.remove();

        let removeInput = document.getElementById('removeStoryAudioInput');
        if (!removeInput) {
            removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'remove_story_audio';
            removeInput.id = 'removeStoryAudioInput';
            removeInput.value = '1';
            document.getElementById('community-post-form')?.appendChild(removeInput);
        }

        if (storyAudioFileInput) {
            storyAudioFileInput.value = '';
        }
    });

    storyAudioFileInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) {
            return;
        }

        if (file.size > maxStoryAudioBytes) {
            notify('error', 'Audio file must be 20 MB or smaller.');
            this.value = '';
            return;
        }

        if (keepExistingStoryAudioInput) {
            keepExistingStoryAudioInput.value = '0';
        }
    });

    function resetStoryAudioRecordingUi() {
        if (storyAudioStream) {
            storyAudioStream.getTracks().forEach((track) => track.stop());
            storyAudioStream = null;
        }

        storyAudioRecorder = null;
        storyAudioChunks = [];
        storyAudioBlob = null;

        if (storyAudioRecordingPreview) {
            storyAudioRecordingPreview.removeAttribute('src');
            storyAudioRecordingPreview.style.display = 'none';
            storyAudioRecordingPreview.load();
        }

        if (storyAudioRecordBtn) {
            storyAudioRecordBtn.disabled = false;
        }
        if (storyAudioStopBtn) {
            storyAudioStopBtn.disabled = true;
        }
        if (storyAudioClearRecordingBtn) {
            storyAudioClearRecordingBtn.disabled = true;
        }
        if (storyAudioRecordingStatus) {
            storyAudioRecordingStatus.textContent = 'Ready to record.';
        }
    }

    storyAudioRecordBtn?.addEventListener('click', async function () {
        if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
            notify('error', 'Voice recording is not supported in this browser.');
            return;
        }

        try {
            resetStoryAudioRecordingUi();
            storyAudioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            storyAudioChunks = [];
            storyAudioRecorder = new MediaRecorder(storyAudioStream);
            storyAudioRecorder.addEventListener('dataavailable', function (event) {
                if (event.data && event.data.size > 0) {
                    storyAudioChunks.push(event.data);
                }
            });
            storyAudioRecorder.addEventListener('stop', function () {
                storyAudioBlob = new Blob(storyAudioChunks, { type: 'audio/webm' });
                const previewUrl = URL.createObjectURL(storyAudioBlob);

                if (storyAudioRecordingPreview) {
                    storyAudioRecordingPreview.src = previewUrl;
                    storyAudioRecordingPreview.style.display = '';
                    storyAudioRecordingPreview.load();
                }

                if (storyAudioClearRecordingBtn) {
                    storyAudioClearRecordingBtn.disabled = false;
                }
                if (storyAudioRecordingStatus) {
                    storyAudioRecordingStatus.textContent = 'Recording ready. Submit the form to save it.';
                }

                if (keepExistingStoryAudioInput) {
                    keepExistingStoryAudioInput.value = '0';
                }
            });

            storyAudioRecorder.start();
            storyAudioRecordBtn.disabled = true;
            storyAudioStopBtn.disabled = false;
            storyAudioRecordingStatus.textContent = 'Recording...';
        } catch (error) {
            notify('error', 'Microphone access is required for voice recording.');
        }
    });

    storyAudioStopBtn?.addEventListener('click', function () {
        if (storyAudioRecorder && storyAudioRecorder.state !== 'inactive') {
            storyAudioRecorder.stop();
        }

        if (storyAudioStream) {
            storyAudioStream.getTracks().forEach((track) => track.stop());
            storyAudioStream = null;
        }

        storyAudioRecordBtn.disabled = false;
        storyAudioStopBtn.disabled = true;
    });

    storyAudioClearRecordingBtn?.addEventListener('click', function () {
        resetStoryAudioRecordingUi();
    });

    refreshStoryAudioPanels();

    const lifeTimelineEntries = document.getElementById('lifeTimelineEntries');
    const lifeTimelineTemplate = document.getElementById('lifeTimelineEntryTemplate');
    const addLifeTimelineEntryBtn = document.getElementById('addLifeTimelineEntryBtn');
    let lifeTimelineNextIndex = 0;

    function refreshLifeTimelineRequiredState(isAutobiography) {
        const timelineEntries = document.getElementById('lifeTimelineEntries');
        if (!timelineEntries) {
            return;
        }

        timelineEntries.querySelectorAll('.autobiography-timeline-entry').forEach((entry) => {
            const yearInput = entry.querySelector('.js-timeline-year');
            const titleInput = entry.querySelector('.js-timeline-title');
            const descriptionInput = entry.querySelector('.js-timeline-description');

            if (yearInput) {
                yearInput.required = Boolean(isAutobiography);
            }
            if (titleInput) {
                titleInput.required = Boolean(isAutobiography);
            }
            if (descriptionInput) {
                descriptionInput.required = Boolean(isAutobiography);
            }
        });
    }

    function syncLifeTimelineFieldNames(entry, index) {
        entry.dataset.timelineIndex = String(index);
        entry.querySelector('.autobiography-timeline-entry__title').textContent = 'Milestone ' + (index + 1);

        entry.querySelectorAll('[data-name]').forEach((field) => {
            const fieldName = field.dataset.name.replace(/__INDEX__/g, String(index));
            field.name = fieldName;
            field.id = fieldName.replace(/[\[\]]/g, '_');
        });
    }

    function bindLifeTimelineEntry(entry) {
        const photoInput = entry.querySelector('.js-timeline-photo');
        const previewWrap = entry.querySelector('.js-timeline-photo-preview');
        const previewImage = previewWrap?.querySelector('img');
        const existingPathInput = entry.querySelector('.js-timeline-existing-photo-path');

        entry.querySelector('.js-remove-timeline-entry')?.addEventListener('click', function () {
            entry.remove();
            reindexLifeTimelineEntries();
            refreshLifeTimelineRequiredState(isLifeStoryContentType(document.getElementById('contentType')?.value || ''));
        });

        photoInput?.addEventListener('change', function () {
            const file = photoInput.files?.[0];
            if (!file || !previewWrap || !previewImage) {
                return;
            }

            previewImage.src = URL.createObjectURL(file);
            previewWrap.style.display = '';
            if (existingPathInput) {
                existingPathInput.value = '';
            }
        });

        if (existingPathInput?.value && previewWrap && previewImage) {
            const existingUrl = entry.dataset.existingPhotoUrl || '';
            if (existingUrl) {
                previewImage.src = existingUrl;
                previewWrap.style.display = '';
            }
        }
    }

    function reindexLifeTimelineEntries() {
        if (!lifeTimelineEntries) {
            return;
        }

        lifeTimelineEntries.querySelectorAll('.autobiography-timeline-entry').forEach((entry, index) => {
            syncLifeTimelineFieldNames(entry, index);
        });
        lifeTimelineNextIndex = lifeTimelineEntries.querySelectorAll('.autobiography-timeline-entry').length;
    }

    function addLifeTimelineEntry(data) {
        if (!lifeTimelineEntries || !lifeTimelineTemplate) {
            return;
        }

        const fragment = lifeTimelineTemplate.content.cloneNode(true);
        const entry = fragment.querySelector('.autobiography-timeline-entry');
        const index = lifeTimelineNextIndex;

        syncLifeTimelineFieldNames(entry, index);
        entry.querySelector('.js-timeline-year').value = data?.year || '';
        entry.querySelector('.js-timeline-title').value = data?.title || '';
        entry.querySelector('.js-timeline-description').value = data?.description || '';

        const existingPathInput = entry.querySelector('.js-timeline-existing-photo-path');
        if (existingPathInput && data?.existing_photo_path) {
            existingPathInput.value = data.existing_photo_path;
        }
        if (data?.existing_photo_url) {
            entry.dataset.existingPhotoUrl = data.existing_photo_url;
        }

        bindLifeTimelineEntry(entry);
        lifeTimelineEntries.appendChild(entry);
        lifeTimelineNextIndex += 1;
        refreshLifeTimelineRequiredState(isLifeStoryContentType(document.getElementById('contentType')?.value || ''));
    }

    function initLifeTimelineBuilder() {
        if (!lifeTimelineEntries || !lifeTimelineTemplate) {
            return;
        }

        lifeTimelineEntries.innerHTML = '';
        lifeTimelineNextIndex = 0;

        const initialEntries = Array.isArray(window.communityLifeTimeline) ? window.communityLifeTimeline : [];
        if (initialEntries.length > 0) {
            initialEntries.forEach((entry) => addLifeTimelineEntry(entry));
        }
    }

    addLifeTimelineEntryBtn?.addEventListener('click', function () {
        addLifeTimelineEntry({});
    });

    const childrensCornerQuizEntries = document.getElementById('childrensCornerQuizEntries');
    const childrensCornerQuizTemplate = document.getElementById('childrensCornerQuizTemplate');
    const addChildrensCornerQuizBtn = document.getElementById('addChildrensCornerQuizBtn');
    let childrensCornerQuizNextIndex = 0;

    function refreshChildrensCornerQuizRequiredState(isQuizMode) {
        if (!childrensCornerQuizEntries) {
            return;
        }

        childrensCornerQuizEntries.querySelectorAll('.childrens-corner-quiz-entry').forEach((entry) => {
            entry.querySelectorAll('.js-quiz-question, .js-quiz-option, .js-quiz-correct-answer').forEach((field) => {
                field.required = Boolean(isQuizMode);
            });
        });
    }

    function syncChildrensCornerQuizFieldNames(entry, index) {
        entry.dataset.quizIndex = String(index);
        const title = entry.querySelector('.childrens-corner-quiz-entry__title');
        if (title) {
            title.textContent = 'Question ' + (index + 1);
        }

        entry.querySelectorAll('[data-name]').forEach((field) => {
            const fieldName = field.dataset.name.replace(/__INDEX__/g, String(index));
            field.name = fieldName;
            field.id = fieldName.replace(/[\[\]]/g, '_');
        });
    }

    function bindChildrensCornerQuizEntry(entry) {
        entry.querySelector('.js-remove-childrens-quiz-entry')?.addEventListener('click', function () {
            entry.remove();
            reindexChildrensCornerQuizEntries();
            refreshChildrensCornerQuizRequiredState(
                getChildrensCornerContentMode(document.getElementById('childShareType')?.value || '') === 'quiz'
            );
        });
    }

    function reindexChildrensCornerQuizEntries() {
        if (!childrensCornerQuizEntries) {
            return;
        }

        childrensCornerQuizEntries.querySelectorAll('.childrens-corner-quiz-entry').forEach((entry, index) => {
            syncChildrensCornerQuizFieldNames(entry, index);
        });
        childrensCornerQuizNextIndex = childrensCornerQuizEntries.querySelectorAll('.childrens-corner-quiz-entry').length;
    }

    function addChildrensCornerQuizEntry(data) {
        if (!childrensCornerQuizEntries || !childrensCornerQuizTemplate) {
            return;
        }

        const fragment = childrensCornerQuizTemplate.content.cloneNode(true);
        const entry = fragment.querySelector('.childrens-corner-quiz-entry');
        const index = childrensCornerQuizNextIndex;

        syncChildrensCornerQuizFieldNames(entry, index);
        entry.querySelector('.js-quiz-question').value = data?.question || '';
        entry.querySelector('.js-quiz-correct-answer').value = data?.correct_answer || '';

        const options = Array.isArray(data?.options) ? data.options : [];
        entry.querySelectorAll('.js-quiz-option').forEach((field, optionIndex) => {
            field.value = options[optionIndex] || '';
        });

        bindChildrensCornerQuizEntry(entry);
        childrensCornerQuizEntries.appendChild(entry);
        childrensCornerQuizNextIndex += 1;
        refreshChildrensCornerQuizRequiredState(
            getChildrensCornerContentMode(document.getElementById('childShareType')?.value || '') === 'quiz'
        );
    }

    function initializeChildrensCornerQuizEntries() {
        if (!childrensCornerQuizEntries || !childrensCornerQuizTemplate) {
            return;
        }

        childrensCornerQuizEntries.innerHTML = '';
        childrensCornerQuizNextIndex = 0;

        const initialEntries = Array.isArray(window.communityChildrensQuiz) ? window.communityChildrensQuiz : [];
        if (initialEntries.length) {
            initialEntries.forEach((entry) => addChildrensCornerQuizEntry(entry));
        } else {
            addChildrensCornerQuizEntry({});
        }
    }

    addChildrensCornerQuizBtn?.addEventListener('click', function () {
        addChildrensCornerQuizEntry({});
    });

    initializeChildrensCornerQuizEntries();

    initLifeTimelineBuilder();

    function createListBuilder(config) {
        const container = document.getElementById(config.containerId);
        const template = document.getElementById(config.templateId);
        const addButton = document.getElementById(config.addButtonId);
        let nextIndex = 0;

        function syncFieldNames(entry, index) {
            entry.querySelectorAll('[data-name]').forEach((field) => {
                const fieldName = field.dataset.name.replace(/__INDEX__/g, String(index));
                field.name = fieldName;
                field.id = fieldName.replace(/[\[\]]/g, '_');
            });
        }

        function bindEntry(entry) {
            entry.querySelector('.js-remove-list-entry')?.addEventListener('click', function () {
                entry.remove();
                reindex();
            });
            entry.querySelector('.js-remove-related-person')?.addEventListener('click', function () {
                entry.remove();
                reindex();
            });
        }

        function reindex() {
            if (!container) {
                return;
            }

            const selector = config.entrySelector || '.autobiography-list-entry, .autobiography-related-person-entry';
            container.querySelectorAll(selector).forEach((entry, index) => {
                syncFieldNames(entry, index);
            });
            nextIndex = container.querySelectorAll(selector).length;
        }

        function addEntry(data) {
            if (!container || !template) {
                return;
            }

            const fragment = template.content.cloneNode(true);
            const entry = fragment.querySelector(config.entrySelector || '.autobiography-list-entry, .autobiography-related-person-entry');
            const index = nextIndex;
            syncFieldNames(entry, index);

            if (config.populate) {
                config.populate(entry, data || {});
            }

            bindEntry(entry);
            container.appendChild(entry);
            nextIndex += 1;
        }

        function init() {
            if (!container || !template) {
                return;
            }

            container.innerHTML = '';
            nextIndex = 0;
            const initialEntries = Array.isArray(config.initialData) ? config.initialData : [];
            if (initialEntries.length > 0) {
                initialEntries.forEach((entry) => addEntry(entry));
            }
        }

        addButton?.addEventListener('click', function () {
            addEntry(config.emptyEntry || '');
        });

        init();

        return { addEntry, reindex };
    }

    createListBuilder({
        containerId: 'placesMentionedEntries',
        templateId: 'placeMentionedTemplate',
        addButtonId: 'addPlaceMentionedBtn',
        initialData: window.communityPlacesMentioned || [],
        emptyEntry: '',
        populate: function (entry, value) {
            const input = entry.querySelector('.js-place-mentioned-input');
            if (input) {
                input.value = typeof value === 'string' ? value : '';
            }
        },
    });

    createListBuilder({
        containerId: 'keyLessonsEntries',
        templateId: 'keyLessonTemplate',
        addButtonId: 'addKeyLessonBtn',
        initialData: window.communityKeyLessons || [],
        emptyEntry: '',
        populate: function (entry, value) {
            const input = entry.querySelector('.js-key-lesson-input');
            if (input) {
                input.value = typeof value === 'string' ? value : '';
            }
        },
    });

    createListBuilder({
        containerId: 'relatedPeopleEntries',
        templateId: 'relatedPersonTemplate',
        addButtonId: 'addRelatedPersonBtn',
        entrySelector: '.autobiography-related-person-entry',
        initialData: window.communityRelatedPeople || [],
        emptyEntry: {},
        populate: function (entry, data) {
            const nameInput = entry.querySelector('.js-related-person-name');
            const relationshipInput = entry.querySelector('.js-related-person-relationship');
            if (nameInput) {
                nameInput.value = data?.name || '';
            }
            if (relationshipInput) {
                relationshipInput.value = data?.relationship || '';
            }
        },
    });

    const autobiographyAchievementEntries = document.getElementById('autobiographyAchievementEntries');
    const autobiographyAchievementTemplate = document.getElementById('autobiographyAchievementTemplate');
    const addAutobiographyAchievementBtn = document.getElementById('addAutobiographyAchievementBtn');
    let autobiographyAchievementNextIndex = 0;

    function syncAchievementFieldNames(entry, index) {
        entry.dataset.achievementIndex = String(index);
        entry.querySelector('.autobiography-achievement-entry__title').textContent = 'Achievement ' + (index + 1);
        entry.querySelectorAll('[data-name]').forEach((field) => {
            const fieldName = field.dataset.name.replace(/__INDEX__/g, String(index));
            field.name = fieldName;
            field.id = fieldName.replace(/[\[\]]/g, '_');
        });
    }

    function bindAchievementEntry(entry) {
        const imageInput = entry.querySelector('.js-achievement-image');
        const previewWrap = entry.querySelector('.js-achievement-image-preview');
        const previewImage = previewWrap?.querySelector('img');
        const existingPathInput = entry.querySelector('.js-achievement-existing-image-path');

        entry.querySelector('.js-remove-achievement-entry')?.addEventListener('click', function () {
            entry.remove();
            reindexAutobiographyAchievements();
        });

        imageInput?.addEventListener('change', function () {
            const file = imageInput.files?.[0];
            if (!file || !previewWrap || !previewImage) {
                return;
            }

            previewImage.src = URL.createObjectURL(file);
            previewWrap.style.display = '';
            if (existingPathInput) {
                existingPathInput.value = '';
            }
        });

        if (existingPathInput?.value && previewWrap && previewImage) {
            const existingUrl = entry.dataset.existingImageUrl || '';
            if (existingUrl) {
                previewImage.src = existingUrl;
                previewWrap.style.display = '';
            }
        }
    }

    function reindexAutobiographyAchievements() {
        if (!autobiographyAchievementEntries) {
            return;
        }

        autobiographyAchievementEntries.querySelectorAll('.autobiography-achievement-entry').forEach((entry, index) => {
            syncAchievementFieldNames(entry, index);
        });
        autobiographyAchievementNextIndex = autobiographyAchievementEntries.querySelectorAll('.autobiography-achievement-entry').length;
    }

    function addAutobiographyAchievement(data) {
        if (!autobiographyAchievementEntries || !autobiographyAchievementTemplate) {
            return;
        }

        const fragment = autobiographyAchievementTemplate.content.cloneNode(true);
        const entry = fragment.querySelector('.autobiography-achievement-entry');
        const index = autobiographyAchievementNextIndex;
        syncAchievementFieldNames(entry, index);
        entry.querySelector('.js-achievement-award-name').value = data?.award_name || '';
        entry.querySelector('.js-achievement-year').value = data?.year || '';
        entry.querySelector('.js-achievement-description').value = data?.description || '';

        const existingPathInput = entry.querySelector('.js-achievement-existing-image-path');
        if (existingPathInput && data?.existing_image_path) {
            existingPathInput.value = data.existing_image_path;
        }
        if (data?.existing_image_url) {
            entry.dataset.existingImageUrl = data.existing_image_url;
        }

        bindAchievementEntry(entry);
        autobiographyAchievementEntries.appendChild(entry);
        autobiographyAchievementNextIndex += 1;
    }

    function initAutobiographyAchievements() {
        if (!autobiographyAchievementEntries || !autobiographyAchievementTemplate) {
            return;
        }

        autobiographyAchievementEntries.innerHTML = '';
        autobiographyAchievementNextIndex = 0;
        const initialEntries = Array.isArray(window.communityAutobiographyAchievements) ? window.communityAutobiographyAchievements : [];
        if (initialEntries.length > 0) {
            initialEntries.forEach((entry) => addAutobiographyAchievement(entry));
        }
    }

    addAutobiographyAchievementBtn?.addEventListener('click', function () {
        addAutobiographyAchievement({});
    });

    initAutobiographyAchievements();

    const autobiographyAudioUploadWrap = document.getElementById('autobiographyAudioUploadWrap');
    const autobiographyAudioRecordingWrap = document.getElementById('autobiographyAudioRecordingWrap');
    const autobiographyAudioFileInput = document.getElementById('autobiographyAudioFile');
    const keepExistingAutobiographyAudioInput = document.getElementById('keepExistingAutobiographyAudio');
    const existingAutobiographyAudioPreview = document.getElementById('existingAutobiographyAudioPreview');
    const removeExistingAutobiographyAudioBtn = document.getElementById('removeExistingAutobiographyAudioBtn');
    const autobiographyAudioRecordBtn = document.getElementById('autobiographyAudioRecordBtn');
    const autobiographyAudioStopBtn = document.getElementById('autobiographyAudioStopBtn');
    const autobiographyAudioClearRecordingBtn = document.getElementById('autobiographyAudioClearRecordingBtn');
    const autobiographyAudioRecordingStatus = document.getElementById('autobiographyAudioRecordingStatus');
    const autobiographyAudioRecordingPreview = document.getElementById('autobiographyAudioRecordingPreview');
    let autobiographyAudioRecorder = null;
    let autobiographyAudioStream = null;
    let autobiographyAudioChunks = [];
    let autobiographyAudioBlob = null;

    function refreshAutobiographyAudioPanels() {
        const selected = document.querySelector('input[name="autobiography_audio_source_type"]:checked')?.value || 'none';
        autobiographyAudioUploadWrap?.classList.toggle('is-active', selected === 'upload');
        autobiographyAudioRecordingWrap?.classList.toggle('is-active', selected === 'recording');
    }

    document.querySelectorAll('input[name="autobiography_audio_source_type"]').forEach((input) => {
        input.addEventListener('change', refreshAutobiographyAudioPanels);
    });

    removeExistingAutobiographyAudioBtn?.addEventListener('click', () => {
        document.getElementById('autobiographyAudioSourceNone')?.click();
        existingAutobiographyAudioPreview?.remove();
        keepExistingAutobiographyAudioInput?.remove();

        let removeInput = document.getElementById('removeAutobiographyAudioInput');
        if (!removeInput) {
            removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'remove_autobiography_audio';
            removeInput.id = 'removeAutobiographyAudioInput';
            removeInput.value = '1';
            document.getElementById('community-post-form')?.appendChild(removeInput);
        }

        if (autobiographyAudioFileInput) {
            autobiographyAudioFileInput.value = '';
        }
    });

    function resetAutobiographyAudioRecordingUi() {
        if (autobiographyAudioStream) {
            autobiographyAudioStream.getTracks().forEach((track) => track.stop());
            autobiographyAudioStream = null;
        }

        autobiographyAudioRecorder = null;
        autobiographyAudioChunks = [];
        autobiographyAudioBlob = null;

        if (autobiographyAudioRecordingPreview) {
            autobiographyAudioRecordingPreview.removeAttribute('src');
            autobiographyAudioRecordingPreview.style.display = 'none';
            autobiographyAudioRecordingPreview.load();
        }

        if (autobiographyAudioRecordBtn) {
            autobiographyAudioRecordBtn.disabled = false;
        }
        if (autobiographyAudioStopBtn) {
            autobiographyAudioStopBtn.disabled = true;
        }
        if (autobiographyAudioClearRecordingBtn) {
            autobiographyAudioClearRecordingBtn.disabled = true;
        }
        if (autobiographyAudioRecordingStatus) {
            autobiographyAudioRecordingStatus.textContent = 'Ready to record.';
        }
    }

    autobiographyAudioRecordBtn?.addEventListener('click', async function () {
        try {
            autobiographyAudioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            autobiographyAudioChunks = [];
            autobiographyAudioRecorder = new MediaRecorder(autobiographyAudioStream);
            autobiographyAudioRecorder.addEventListener('dataavailable', function (event) {
                if (event.data.size > 0) {
                    autobiographyAudioChunks.push(event.data);
                }
            });
            autobiographyAudioRecorder.addEventListener('stop', function () {
                autobiographyAudioBlob = new Blob(autobiographyAudioChunks, { type: 'audio/webm' });
                const previewUrl = URL.createObjectURL(autobiographyAudioBlob);

                if (autobiographyAudioRecordingPreview) {
                    autobiographyAudioRecordingPreview.src = previewUrl;
                    autobiographyAudioRecordingPreview.style.display = '';
                    autobiographyAudioRecordingPreview.load();
                }

                if (autobiographyAudioClearRecordingBtn) {
                    autobiographyAudioClearRecordingBtn.disabled = false;
                }
                if (autobiographyAudioRecordingStatus) {
                    autobiographyAudioRecordingStatus.textContent = 'Recording ready. Submit the form to save it.';
                }

                if (keepExistingAutobiographyAudioInput) {
                    keepExistingAutobiographyAudioInput.value = '0';
                }
            });

            autobiographyAudioRecorder.start();
            autobiographyAudioRecordBtn.disabled = true;
            autobiographyAudioStopBtn.disabled = false;
            autobiographyAudioRecordingStatus.textContent = 'Recording...';
        } catch (error) {
            notify('error', 'Microphone access is required for voice recording.');
        }
    });

    autobiographyAudioStopBtn?.addEventListener('click', function () {
        if (autobiographyAudioRecorder && autobiographyAudioRecorder.state !== 'inactive') {
            autobiographyAudioRecorder.stop();
        }

        if (autobiographyAudioStream) {
            autobiographyAudioStream.getTracks().forEach((track) => track.stop());
            autobiographyAudioStream = null;
        }

        autobiographyAudioRecordBtn.disabled = false;
        autobiographyAudioStopBtn.disabled = true;
    });

    autobiographyAudioClearRecordingBtn?.addEventListener('click', function () {
        resetAutobiographyAudioRecordingUi();
    });

    refreshAutobiographyAudioPanels();

    const poetryAudioUploadWrap = document.getElementById('poetryAudioUploadWrap');
    const poetryAudioRecordingWrap = document.getElementById('poetryAudioRecordingWrap');
    const poetryAudioFileInput = document.getElementById('poetryAudioFile');
    const keepExistingPoetryAudioInput = document.getElementById('keepExistingPoetryAudio');
    const existingPoetryAudioPreview = document.getElementById('existingPoetryAudioPreview');
    const removeExistingPoetryAudioBtn = document.getElementById('removeExistingPoetryAudioBtn');
    const poetryAudioRecordBtn = document.getElementById('poetryAudioRecordBtn');
    const poetryAudioStopBtn = document.getElementById('poetryAudioStopBtn');
    const poetryAudioClearRecordingBtn = document.getElementById('poetryAudioClearRecordingBtn');
    const poetryAudioRecordingStatus = document.getElementById('poetryAudioRecordingStatus');
    const poetryAudioRecordingPreview = document.getElementById('poetryAudioRecordingPreview');
    let poetryAudioRecorder = null;
    let poetryAudioStream = null;
    let poetryAudioChunks = [];
    let poetryAudioBlob = null;

    function refreshPoetryAudioPanels() {
        const selected = document.querySelector('input[name="poetry_audio_source_type"]:checked')?.value || 'none';
        poetryAudioUploadWrap?.classList.toggle('is-active', selected === 'upload');
        poetryAudioRecordingWrap?.classList.toggle('is-active', selected === 'recording');
    }

    function refreshPoetrySeriesFields() {
        const isSeries = document.querySelector('input[name="poetry_part_of_series"]:checked')?.value === 'Yes';
        const nameWrap = document.getElementById('poetrySeriesNameWrap');
        const partWrap = document.getElementById('poetrySeriesPartWrap');
        const nameInput = document.getElementById('poetry_series_name');

        if (nameWrap) {
            nameWrap.style.display = isSeries ? '' : 'none';
        }
        if (partWrap) {
            partWrap.style.display = isSeries ? '' : 'none';
        }
        if (nameInput) {
            nameInput.required = isSeries && document.getElementById('contentType')?.value === 'poetry';
        }
    }

    document.querySelectorAll('input[name="poetry_audio_source_type"]').forEach((input) => {
        input.addEventListener('change', refreshPoetryAudioPanels);
    });

    document.querySelectorAll('.js-poetry-series-toggle').forEach((input) => {
        input.addEventListener('change', refreshPoetrySeriesFields);
    });

    removeExistingPoetryAudioBtn?.addEventListener('click', () => {
        document.getElementById('poetryAudioSourceNone')?.click();
        existingPoetryAudioPreview?.remove();
        keepExistingPoetryAudioInput?.remove();

        let removeInput = document.getElementById('removePoetryAudioInput');
        if (!removeInput) {
            removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'remove_poetry_audio';
            removeInput.id = 'removePoetryAudioInput';
            removeInput.value = '1';
            document.getElementById('community-post-form')?.appendChild(removeInput);
        }

        if (poetryAudioFileInput) {
            poetryAudioFileInput.value = '';
        }
    });

    poetryAudioFileInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) {
            return;
        }

        if (file.size > maxStoryAudioBytes) {
            notify('error', 'Audio file must be 20 MB or smaller.');
            this.value = '';
            return;
        }

        if (keepExistingPoetryAudioInput) {
            keepExistingPoetryAudioInput.value = '0';
        }
    });

    function resetPoetryAudioRecordingUi() {
        if (poetryAudioStream) {
            poetryAudioStream.getTracks().forEach((track) => track.stop());
            poetryAudioStream = null;
        }

        poetryAudioRecorder = null;
        poetryAudioChunks = [];
        poetryAudioBlob = null;

        if (poetryAudioRecordingPreview) {
            poetryAudioRecordingPreview.removeAttribute('src');
            poetryAudioRecordingPreview.style.display = 'none';
            poetryAudioRecordingPreview.load();
        }

        if (poetryAudioRecordBtn) {
            poetryAudioRecordBtn.disabled = false;
        }
        if (poetryAudioStopBtn) {
            poetryAudioStopBtn.disabled = true;
        }
        if (poetryAudioClearRecordingBtn) {
            poetryAudioClearRecordingBtn.disabled = true;
        }
        if (poetryAudioRecordingStatus) {
            poetryAudioRecordingStatus.textContent = 'Ready to record.';
        }
    }

    poetryAudioRecordBtn?.addEventListener('click', async function () {
        if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
            notify('error', 'Voice recording is not supported in this browser.');
            return;
        }

        try {
            resetPoetryAudioRecordingUi();
            poetryAudioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            poetryAudioChunks = [];
            poetryAudioRecorder = new MediaRecorder(poetryAudioStream);
            poetryAudioRecorder.addEventListener('dataavailable', function (event) {
                if (event.data && event.data.size > 0) {
                    poetryAudioChunks.push(event.data);
                }
            });
            poetryAudioRecorder.addEventListener('stop', function () {
                poetryAudioBlob = new Blob(poetryAudioChunks, { type: 'audio/webm' });
                const previewUrl = URL.createObjectURL(poetryAudioBlob);

                if (poetryAudioRecordingPreview) {
                    poetryAudioRecordingPreview.src = previewUrl;
                    poetryAudioRecordingPreview.style.display = '';
                    poetryAudioRecordingPreview.load();
                }

                if (poetryAudioClearRecordingBtn) {
                    poetryAudioClearRecordingBtn.disabled = false;
                }
                if (poetryAudioRecordingStatus) {
                    poetryAudioRecordingStatus.textContent = 'Recording ready. Submit the form to save it.';
                }

                if (keepExistingPoetryAudioInput) {
                    keepExistingPoetryAudioInput.value = '0';
                }
            });

            poetryAudioRecorder.start();
            poetryAudioRecordBtn.disabled = true;
            poetryAudioStopBtn.disabled = false;
            poetryAudioRecordingStatus.textContent = 'Recording...';
        } catch (error) {
            notify('error', 'Microphone access is required for voice recording.');
        }
    });

    poetryAudioStopBtn?.addEventListener('click', function () {
        if (poetryAudioRecorder && poetryAudioRecorder.state !== 'inactive') {
            poetryAudioRecorder.stop();
        }

        if (poetryAudioStream) {
            poetryAudioStream.getTracks().forEach((track) => track.stop());
            poetryAudioStream = null;
        }

        poetryAudioRecordBtn.disabled = false;
        poetryAudioStopBtn.disabled = true;
    });

    poetryAudioClearRecordingBtn?.addEventListener('click', function () {
        resetPoetryAudioRecordingUi();
    });

    refreshPoetryAudioPanels();
    refreshPoetrySeriesFields();

    function readCommunityAddressPart(components, type) {
        const match = (components || []).find((component) => (component.types || []).includes(type));
        return match?.long_name || '';
    }

    function fillStructuredLocationFromPlace(place) {
        const components = place?.address_components || [];
        const country = readCommunityAddressPart(components, 'country');
        const state = readCommunityAddressPart(components, 'administrative_area_level_1');
        const district = readCommunityAddressPart(components, 'administrative_area_level_2')
            || readCommunityAddressPart(components, 'administrative_area_level_3');
        const city = readCommunityAddressPart(components, 'locality')
            || readCommunityAddressPart(components, 'postal_town')
            || readCommunityAddressPart(components, 'administrative_area_level_2');
        const locality = readCommunityAddressPart(components, 'sublocality_level_1')
            || readCommunityAddressPart(components, 'sublocality')
            || readCommunityAddressPart(components, 'neighborhood')
            || readCommunityAddressPart(components, 'route');

        const countryInput = document.getElementById('communityLocationCountry');
        const stateInput = document.getElementById('communityLocationState');
        const districtInput = document.getElementById('communityLocationDistrict');
        const cityInput = document.getElementById('communityLocationCity');
        const localityInput = document.getElementById('communityLocationLocality');
        const latInput = document.getElementById('communityLocationLat');
        const lngInput = document.getElementById('communityLocationLng');

        if (countryInput && country) countryInput.value = country;
        if (stateInput && state) stateInput.value = state;
        if (districtInput && district) districtInput.value = district;
        if (cityInput && city) cityInput.value = city;
        if (localityInput && locality) localityInput.value = locality;

        if (place?.geometry?.location) {
            if (latInput) latInput.value = place.geometry.location.lat().toFixed(7);
            if (lngInput) lngInput.value = place.geometry.location.lng().toFixed(7);
            syncCommunityGpsMarkerFromInputs();
        }
    }

    window.initCommunityPostLocationAutocomplete = function () {
        if (!window.google || !google.maps || !google.maps.places) {
            return;
        }

        const structuredSearchInput = document.getElementById('communityStructuredLocationSearch');
        if (structuredSearchInput && !structuredSearchInput.dataset.autocompleteBound) {
            const structuredAutocomplete = new google.maps.places.Autocomplete(structuredSearchInput, {
                fields: ['address_components', 'formatted_address', 'geometry', 'place_id'],
            });

            structuredAutocomplete.addListener('place_changed', function () {
                fillStructuredLocationFromPlace(structuredAutocomplete.getPlace());
            });

            structuredSearchInput.dataset.autocompleteBound = '1';
        }

        const locationInput = document.getElementById('communityLocation');
        const latitudeInput = document.getElementById('communityLocationLat');
        const longitudeInput = document.getElementById('communityLocationLng');
        if (!locationInput || locationInput.dataset.autocompleteBound) {
            return;
        }

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

        locationInput.dataset.autocompleteBound = '1';
    };

    window.initCommunityPostMaps = function () {
        window.initCommunityPostLocationAutocomplete();

        const contentType = document.getElementById('contentType')?.value || '';
        if (usesStructuredCommunityLocation(contentType)) {
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
                const chapterMode = isChapterContentType(document.getElementById('contentType').value);
                const hasBookContent = window.communityBookPages.some(function (page) {
                    return (page.content || '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim() !== '';
                });

                if (!hasBookContent) {
                    notify('error', chapterMode
                        ? 'Please add content to at least one chapter.'
                        : 'Please add content to at least one book page.');
                    window.communityBodyEditor.editing.view.focus();
                    return;
                }

                if (chapterMode) {
                    const missingChapterTitle = window.communityBookPages.some(function (page) {
                        const hasContent = (page.content || '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim() !== '';
                        return hasContent && !(page.title || '').trim();
                    });

                    if (missingChapterTitle) {
                        notify('error', 'Please enter a title for each chapter that has content.');
                        document.getElementById('activeChapterTitle')?.focus();
                        return;
                    }
                }
            } else {
                const contentTypeValue = document.getElementById('contentType')?.value || '';
                const childrensMode = contentTypeValue === 'childrens-corner'
                    ? getChildrensCornerContentMode(document.getElementById('childShareType')?.value || '')
                    : null;
                const requiresBody = contentTypeValue !== 'childrens-corner'
                    || childrensMode === 'rich_text'
                    || childrensMode === 'poem';

                document.getElementById('bodyEditor').value = window.communityBodyEditor.getData();

                if (requiresBody) {
                    const bodyText = document.getElementById('bodyEditor').value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                    if (!bodyText) {
                        notify('error', 'Please enter content in the body field.');
                        window.communityBodyEditor.editing.view.focus();
                        return;
                    }
                } else {
                    document.getElementById('bodyEditor').value = '';
                }
            }
        }

        saveActiveEditorLanguage();
        addTagsFromInput();

        const contentType = document.getElementById('contentType')?.value || '';

        if (contentType === 'childrens-corner') {
            syncChildrensCornerCategory();
            reindexChildrensCornerQuizEntries();

            const shareType = document.getElementById('childShareType')?.value || '';
            if (!shareType) {
                notify('error', 'Please select what you would like to share.');
                document.getElementById('childShareType')?.focus();
                return;
            }

            const childrensMode = getChildrensCornerContentMode(shareType);

            if (childrensMode === 'image') {
                const artFileInput = document.getElementById('childrensCornerArtFile');
                const hasNewArt = (artFileInput?.files?.length || 0) > 0;
                const keepingExistingArt = Boolean(document.getElementById('keepExistingChildrensCornerArt'));
                if (!hasNewArt && !keepingExistingArt) {
                    notify('error', 'Please upload an image for this submission.');
                    artFileInput?.focus();
                    return;
                }
            }

            if (childrensMode === 'project') {
                const projectDescription = document.getElementById('childrensCornerProjectDescription')?.value.trim() || '';
                if (!projectDescription) {
                    notify('error', 'Please enter a project description.');
                    document.getElementById('childrensCornerProjectDescription')?.focus();
                    return;
                }
            }

            if (childrensMode === 'quiz') {
                const quizCount = document.querySelectorAll('#childrensCornerQuizEntries .childrens-corner-quiz-entry').length;
                if (!quizCount) {
                    notify('error', 'Please add at least one quiz question.');
                    return;
                }
            }

            const consentFields = [
                document.getElementById('childParentConsentIdentity'),
                document.getElementById('childParentConsentPublication'),
                document.getElementById('childParentConsentOriginal'),
            ];
            if (consentFields.some((field) => !field?.checked)) {
                notify('error', 'Please accept all parent/guardian consent statements.');
                return;
            }

            const safetyFields = document.querySelectorAll('.childrens-corner-safety-required');
            if (Array.from(safetyFields).some((field) => !field.checked)) {
                notify('error', 'Please confirm all safety declaration statements.');
                return;
            }

            const privacySetting = document.querySelector('input[name="childrens_corner_privacy_setting"]:checked')?.value || '';
            if (!privacySetting) {
                notify('error', 'Please choose a privacy setting for this submission.');
                return;
            }

            if (privacySetting === 'school_community' && !document.getElementById('childSchoolName')?.value.trim()) {
                notify('error', 'Please enter the school name for school community privacy.');
                document.getElementById('childSchoolName')?.focus();
                return;
            }

            const videoSource = document.querySelector('input[name="childrens_corner_video_source_type"]:checked')?.value || 'none';
            if (videoSource === 'youtube') {
                if (!document.getElementById('childrensCornerVideoYoutubeUrl')?.value.trim()) {
                    notify('error', 'Please enter a YouTube video link or choose another video option.');
                    return;
                }
            }
            if (videoSource === 'upload') {
                const childrensVideoFile = document.getElementById('childrensCornerVideoFile');
                const hasNewVideo = (childrensVideoFile?.files?.length || 0) > 0;
                const keepingExistingVideo = document.getElementById('keepExistingChildrensCornerVideo')?.value === '1';
                if (!hasNewVideo && !keepingExistingVideo) {
                    notify('error', 'Please choose a video file to upload or switch to another video option.');
                    return;
                }
            }

            const audioSource = document.querySelector('input[name="childrens_corner_audio_source_type"]:checked')?.value || 'none';
            if (audioSource === 'upload') {
                const childrensAudioFile = document.getElementById('childrensCornerAudioFile');
                const hasNewAudio = (childrensAudioFile?.files?.length || 0) > 0;
                const keepingExistingAudio = document.getElementById('keepExistingChildrensCornerAudio')?.value === '1';
                if (!hasNewAudio && !keepingExistingAudio) {
                    notify('error', 'Please choose an audio file or switch to another audio option.');
                    return;
                }
            }
            if (audioSource === 'recording') {
                const keepingExistingAudio = document.getElementById('keepExistingChildrensCornerAudio')?.value === '1';
                if (!window.childrensCornerAudioBlob && !keepingExistingAudio) {
                    notify('error', 'Please record audio or switch to another audio option.');
                    return;
                }
            }
        }

        if (contentType === 'business') {
            syncBusinessCategory();

            const businessCategory = document.getElementById('businessCategory')?.value || '';
            if (!businessCategory) {
                notify('error', 'Please select a business main category.');
                document.getElementById('businessCategory')?.focus();
                return;
            }

            if (!document.getElementById('businessContentType')?.value) {
                notify('error', 'Please select a business content type.');
                document.getElementById('businessContentType')?.focus();
                return;
            }

            if (!document.getElementById('businessStage')?.value) {
                notify('error', 'Please select a business stage.');
                document.getElementById('businessStage')?.focus();
                return;
            }

            const businessAudienceCount = document.querySelectorAll('input[name="business_target_audience[]"]:checked').length;
            if (!businessAudienceCount) {
                notify('error', 'Please select at least one target audience.');
                return;
            }

            if (document.getElementById('businessAllowPoll')?.checked) {
                const businessPollQuestion = document.getElementById('businessPollQuestion')?.value.trim() || '';
                if (!businessPollQuestion) {
                    notify('error', 'Please enter a poll question for this business post.');
                    document.getElementById('businessPollQuestion')?.focus();
                    return;
                }
            }
        }

        if (contentType === 'awareness') {
            syncAwarenessCategory();

            const awarenessCategory = document.getElementById('awarenessCategory')?.value || '';
            if (!awarenessCategory) {
                notify('error', 'Please select an awareness main category.');
                document.getElementById('awarenessCategory')?.focus();
                return;
            }

            if (!document.getElementById('awarenessType')?.value) {
                notify('error', 'Please select an awareness type.');
                document.getElementById('awarenessType')?.focus();
                return;
            }

            if (!document.getElementById('awarenessLevel')?.value) {
                notify('error', 'Please select an awareness level.');
                document.getElementById('awarenessLevel')?.focus();
                return;
            }

            const audienceCount = document.querySelectorAll('input[name="awareness_target_audience[]"]:checked').length;
            if (!audienceCount) {
                notify('error', 'Please select at least one target audience.');
                return;
            }

            if (!document.querySelector('input[name="awareness_posted_by"]:checked')) {
                notify('error', 'Please select who is posting this awareness content.');
                return;
            }

            const campaignStartDate = document.getElementById('awarenessCampaignStartDate')?.value || '';
            const campaignEndDate = document.getElementById('awarenessCampaignEndDate')?.value || '';
            if (campaignStartDate && campaignEndDate && campaignEndDate < campaignStartDate) {
                notify('error', 'Campaign end date must be on or after the start date.');
                document.getElementById('awarenessCampaignEndDate')?.focus();
                return;
            }

            const postStatus = document.getElementById('communityPostStatus')?.value || 'draft';
            if (postStatus === 'published' && featuredImagesTotal() === 0) {
                notify('error', 'Please upload a campaign banner for this awareness post.');
                document.getElementById('featuredImagesAddBtn')?.focus();
                return;
            }

            const callToAction = document.getElementById('awarenessCallToAction')?.value.trim() || '';
            if (!callToAction) {
                notify('error', 'Please enter a call to action for this awareness post.');
                document.getElementById('awarenessCallToAction')?.focus();
                return;
            }

            if (document.getElementById('awarenessAllowPoll')?.checked) {
                const pollQuestion = document.getElementById('awarenessPollQuestion')?.value.trim() || '';
                if (!pollQuestion) {
                    notify('error', 'Please enter a poll question for this awareness post.');
                    document.getElementById('awarenessPollQuestion')?.focus();
                    return;
                }
            }
        }

        if (usesStructuredCommunityLocation(contentType)) {
            const requiredStructuredFields = [
                document.getElementById('communityLocationCountry'),
                document.getElementById('communityLocationState'),
                document.getElementById('communityLocationDistrict'),
                document.getElementById('communityLocationCity'),
            ];

            if (contentType === 'awareness') {
                requiredStructuredFields.push(document.getElementById('communityLocationLocality'));
            }

            if (requiredStructuredFields.some((field) => !field?.value.trim())) {
                notify('error', contentType === 'awareness'
                    ? 'Please complete country, state, district, city, and area for this awareness post.'
                    : 'Please complete country, state, district, and city for this post.');
                return;
            }
        } else {
            const locationType = document.getElementById('communityLocationType')?.value || 'global';
            if (requiresSpecificCommunityLocation(locationType)) {
                if (!document.getElementById('communityLocation')?.value.trim()) {
                    notify('error', 'Please select a location from the Google Places suggestions.');
                    return;
                }
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
        if (contentType !== 'childrens-corner' && videoSource === 'youtube') {
            const youtubeUrl = document.getElementById('videoYoutubeUrl')?.value.trim();
            if (!youtubeUrl) {
                notify('error', 'Please enter a YouTube video link or choose another video option.');
                return;
            }
        }

        if (contentType !== 'childrens-corner' && videoSource === 'upload') {
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

        if (contentType === 'stories') {
            const storyLanguage = document.querySelector('select[name="story_language"]')?.value;
            if (!storyLanguage) {
                notify('error', 'Please select a story language.');
                return;
            }

            const audioSource = document.querySelector('input[name="story_audio_source_type"]:checked')?.value || 'none';
            if (audioSource === 'upload') {
                const hasNewAudio = (storyAudioFileInput?.files?.length || 0) > 0;
                const keepingExistingAudio = keepExistingStoryAudioInput?.value === '1';
                if (!hasNewAudio && !keepingExistingAudio) {
                    notify('error', 'Please choose an MP3 file or switch to another audio option.');
                    return;
                }

                if (hasNewAudio && storyAudioFileInput.files[0].size > maxStoryAudioBytes) {
                    notify('error', 'Audio file must be 20 MB or smaller.');
                    return;
                }
            }

            if (audioSource === 'recording') {
                const keepingExistingAudio = keepExistingStoryAudioInput?.value === '1';
                if (!storyAudioBlob && !keepingExistingAudio) {
                    notify('error', 'Please record your voice story or switch to another audio option.');
                    return;
                }

                if (storyAudioBlob && storyAudioBlob.size > maxStoryAudioBytes) {
                    notify('error', 'Recorded audio must be 20 MB or smaller.');
                    return;
                }
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

        if (document.getElementById('contentType').value === 'stories') {
            formData.delete('story_audio_recording');
            const audioSource = document.querySelector('input[name="story_audio_source_type"]:checked')?.value || 'none';
            if (audioSource === 'recording' && storyAudioBlob) {
                formData.append('story_audio_recording', storyAudioBlob, 'story-recording.webm');
            }
        }

        if (document.getElementById('contentType').value === 'poetry') {
            formData.delete('poetry_audio_recording');
            const poetryAudioSource = document.querySelector('input[name="poetry_audio_source_type"]:checked')?.value || 'none';
            if (poetryAudioSource === 'recording' && poetryAudioBlob) {
                formData.append('poetry_audio_recording', poetryAudioBlob, 'poetry-recording.webm');
            }
        }

        if (isLifeStoryContentType(document.getElementById('contentType').value)) {
            formData.delete('autobiography_audio_recording');
            const autobiographyAudioSource = document.querySelector('input[name="autobiography_audio_source_type"]:checked')?.value || 'none';
            if (autobiographyAudioSource === 'recording' && autobiographyAudioBlob) {
                formData.append('autobiography_audio_recording', autobiographyAudioBlob, 'autobiography-recording.webm');
            }
        }

        if (document.getElementById('contentType').value === 'childrens-corner') {
            formData.delete('childrens_corner_audio_recording');
            const childrensAudioSource = document.querySelector('input[name="childrens_corner_audio_source_type"]:checked')?.value || 'none';
            if (childrensAudioSource === 'recording' && window.childrensCornerAudioBlob) {
                formData.append('childrens_corner_audio_recording', window.childrensCornerAudioBlob, 'childrens-corner-recording.webm');
            }
        }

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
