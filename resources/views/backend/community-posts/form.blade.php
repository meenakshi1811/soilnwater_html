@extends('backend.layouts.app')

@section('title', $mode === 'edit' ? 'Edit Community Post' : 'Create Community Post')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community publishing</p>
            <h2 class="admin-title mb-1">{{ $mode === 'edit' ? 'Edit Community Post' : 'Create Community Post' }}</h2>
            <p class="mb-0 text-secondary">Pick a post type first, then add the category and content details.</p>
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
            @php
                $selectedContentType = old('content_type', $post->content_type);
                $postTypeIcons = [
                    'articles' => 'fa-file-lines',
                    'reports' => 'fa-clipboard-list',
                    'my-area' => 'fa-map-location-dot',
                    'news' => 'fa-newspaper',
                    'stories' => 'fa-book-open',
                    'poetry' => 'fa-feather-pointed',
                    'biography' => 'fa-user-pen',
                    'autobiography' => 'fa-pen-fancy',
                    'childrens-corner' => 'fa-child-reaching',
                    'awareness' => 'fa-bullhorn',
                    'business' => 'fa-briefcase',
                    'student-corner' => 'fa-graduation-cap',
                    'career' => 'fa-chart-line',
                    'health-wellness' => 'fa-heart-pulse',
                    'womens-world' => 'fa-venus',
                    'senior-citizens-forum' => 'fa-person-cane',
                    'youth-corner' => 'fa-user-group',
                    'jobs-employment' => 'fa-briefcase',
                    'opinions-views' => 'fa-comments',
                    'travel-diaries' => 'fa-plane',
                    'culture-heritage' => 'fa-landmark',
                    'astro-consultancy' => 'fa-star',
                    'religion-spirituality' => 'fa-hands-praying',
                    'agriculture' => 'fa-seedling',
                    'environment' => 'fa-leaf',
                    'science-technology' => 'fa-flask',
                    'local-voices' => 'fa-microphone',
                    'community-issues' => 'fa-triangle-exclamation',
                    'creative-corner' => 'fa-palette',
                    'competitions' => 'fa-trophy',
                    'discussions' => 'fa-comments',
                ];
                $writingPurposeNounByType = [
                    'articles' => 'article',
                    'reports' => 'report',
                    'stories' => 'story',
                    'news' => 'news item',
                    'poetry' => 'poem',
                    'biography' => 'biography',
                    'autobiography' => 'autobiography',
                    'discussions' => 'discussion',
                    'competitions' => 'competition',
                ];
                if (isset($writingPurposeNounByType[$selectedContentType])) {
                    $writingPurposeNoun = $writingPurposeNounByType[$selectedContentType];
                } elseif (filled($selectedContentType)) {
                    $writingPurposeNoun = strtolower((string) data_get($types, $selectedContentType.'.label', 'post'));
                    if (
                        str_ends_with($writingPurposeNoun, 's')
                        && ! str_ends_with($writingPurposeNoun, 'ss')
                        && ! in_array($writingPurposeNoun, ['news', 'business'], true)
                    ) {
                        $writingPurposeNoun = substr($writingPurposeNoun, 0, -1);
                    }
                } else {
                    $writingPurposeNoun = 'post';
                }
                $postTypePillColors = \App\Support\CommunityContentTaxonomy::pillColors();
                $postTypePillFallback = \App\Support\CommunityContentTaxonomy::pillColorFallback();
            @endphp
            <div class="col-12">
                <div class="community-post-type-picker">
                    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
                        <div>
                            <label class="form-label mb-1">Post type <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-0">Choose the section this post belongs to. The rest of the form appears after you pick one.</p>
                        </div>
                        <div class="community-post-type-search">
                            <label class="visually-hidden" for="contentTypeSearch">Search post types</label>
                            <input type="search" id="contentTypeSearch" class="form-control form-control-sm" placeholder="Search types…" autocomplete="off">
                        </div>
                    </div>
                    <select name="content_type" id="contentType" class="visually-hidden" required tabindex="-1" aria-hidden="true">
                        <option value="">Select type</option>
                        @foreach($types as $key => $type)
                            <option value="{{ $key }}" @selected($selectedContentType === $key)>{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="community-post-type-grid" id="contentTypeGrid" role="listbox" aria-label="Post types">
                        @foreach($types as $key => $type)
                            @php
                                $pillColor = $postTypePillColors[$key] ?? $postTypePillFallback;
                            @endphp
                            <button
                                type="button"
                                class="community-post-type-card community-post-type-card--{{ $key }}{{ $selectedContentType === $key ? ' is-selected' : '' }}"
                                style="--pill-color: {{ $pillColor }}; background: {{ $pillColor }} !important; background-color: {{ $pillColor }} !important; border-color: {{ $pillColor }} !important; color: #fff !important;"
                                data-type="{{ $key }}"
                                data-label="{{ strtolower($type['label']) }}"
                                data-pill-color="{{ $pillColor }}"
                                title="{{ $type['description'] }}"
                                role="option"
                                aria-selected="{{ $selectedContentType === $key ? 'true' : 'false' }}"
                            >
                                <span class="community-post-type-card__label">{{ $type['label'] }}</span>
                                <span class="visually-hidden">{{ $type['description'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <small id="typeHelp" class="text-muted d-block mt-2"></small>
                    <p id="contentTypeEmptyHint" class="community-post-type-empty mb-0 mt-3{{ filled($selectedContentType) ? ' d-none' : '' }}">
                        Select a post type above to continue with category, title, and content fields.
                    </p>
                </div>
            </div>

            <div class="col-12" id="communityPostDetails" @if(! filled($selectedContentType)) hidden @endif>
            <div class="row g-3">
            <div class="col-12" id="writingPurposeFieldWrap">
                <label class="form-label" for="writingPurpose" id="writingPurposeLabel">Why are you writing this {{ $writingPurposeNoun }}? <span class="text-danger">*</span></label>
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
            <div class="col-12 type-extra womens-world-flow" data-for="womens-world">
                @include('backend.community-posts.partials.womens-world-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra senior-citizens-forum-flow" data-for="senior-citizens-forum">
                @include('backend.community-posts.partials.senior-citizens-forum-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra student-corner-flow" data-for="student-corner">
                @include('backend.community-posts.partials.student-corner-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra youth-corner-flow" data-for="youth-corner">
                @include('backend.community-posts.partials.youth-corner-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra local-voices-flow" data-for="local-voices">
                @include('backend.community-posts.partials.local-voices-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra my-area-flow" data-for="my-area">
                @include('backend.community-posts.partials.my-area-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra community-issues-flow" data-for="community-issues">
                @include('backend.community-posts.partials.community-issues-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra agriculture-flow" data-for="agriculture">
                @include('backend.community-posts.partials.agriculture-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra environment-flow" data-for="environment">
                @include('backend.community-posts.partials.environment-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra science-technology-flow" data-for="science-technology">
                @include('backend.community-posts.partials.science-technology-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra astro-consultancy-flow" data-for="astro-consultancy">
                @include('backend.community-posts.partials.astro-consultancy-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra religion-spirituality-flow" data-for="religion-spirituality">
                @include('backend.community-posts.partials.religion-spirituality-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra creative-corner-flow" data-for="creative-corner">
                @include('backend.community-posts.partials.creative-corner-flow-fields', ['post' => $post, 'placement' => 'setup'])
            </div>
            <div class="col-12 type-extra competitions-flow" data-for="competitions">
                @include('backend.community-posts.partials.competitions-flow-fields', ['post' => $post, 'placement' => 'setup'])
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
                <div id="womensWorldContentGuide" class="story-content-guide mb-3 womens-world-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Women's World content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full story, article, or advice post.</p>
                            </div>
                            <span class="badge bg-primary text-white">Women's World only</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Use the editor below for the full post.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Documents</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Tables</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Quotes</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertWomensWorldStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldContentStructure() as $heading => $hint)
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
                <div id="seniorCitizensForumContentGuide" class="story-content-guide mb-3 senior-citizens-forum-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full post. Large font mode is enabled for easier reading and writing.</p>
                            </div>
                            <span class="badge bg-primary text-white">Senior Citizens Forum</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Use the editor below for the full post.</p>
                                    <p class="small mb-1 fw-semibold">Features:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Audio</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Documents</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Quotes</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Basic formatting</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertSeniorCitizensForumStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumContentStructure() as $heading => $hint)
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
                <div id="studentCornerContentGuide" class="story-content-guide mb-3 student-corner-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full Student Corner post.</p>
                            </div>
                            <span class="badge bg-primary text-white">Student Corner</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Use the editor below for the full post.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Documents</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Tables</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Charts</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Polls</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertStudentCornerStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerContentStructure() as $heading => $hint)
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
                <div id="youthCornerContentGuide" class="story-content-guide mb-3 youth-corner-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full Youth Corner post.</p>
                            </div>
                            <span class="badge bg-primary text-white">Youth Corner</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Use the editor below for the full post.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Documents</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Tables</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertYouthCornerStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerContentStructure() as $heading => $hint)
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
                <div id="localVoicesContentGuide" class="story-content-guide mb-3 local-voices-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full Local Voices post.</p>
                            </div>
                            <span class="badge bg-primary text-white">Local Voices</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Use the editor below for your local story, concern, or feedback.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Documents</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Quotes</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Polls</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertLocalVoicesStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceContentStructure() as $heading => $hint)
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
                <div id="myAreaContentGuide" class="story-content-guide mb-3 my-area-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full My Area post.</p>
                            </div>
                            <span class="badge bg-success text-white">My Area</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">Report issues, recognize heroes, share achievements, or raise awareness for your neighbourhood.</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertMyAreaStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::myAreaContentStructure() as $heading => $hint)
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
                <div id="communityIssuesContentGuide" class="story-content-guide mb-3 community-issues-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Issue description</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below to describe the community issue in detail.</p>
                            </div>
                            <span class="badge bg-danger text-white">Community Issues</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Use the editor below for your full issue description.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Documents</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertCommunityIssuesStructureBtn">
                                        Insert recommended structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Recommended structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueContentStructure() as $heading => $hint)
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
                <div id="agricultureContentGuide" class="story-content-guide mb-3 agriculture-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full agriculture post.</p>
                            </div>
                            <span class="badge bg-success text-white">Agriculture</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Use the editor below for your farming experience, advisory, or article.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Tables</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Documents</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Charts</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertAgricultureStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::agricultureContentStructure() as $heading => $hint)
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
                <div id="environmentContentGuide" class="story-content-guide mb-3 environment-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full environmental post.</p>
                            </div>
                            <span class="badge bg-success text-white">Environment</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">This is the same body field on the form. Document environmental conditions, actions, and outcomes below.</p>
                                    <p class="small mb-1 fw-semibold">Support:</p>
                                    <ul class="story-content-support list-unstyled small text-muted mb-3">
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Text</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Images</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Videos</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Tables</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Documents</li>
                                        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Links</li>
                                    </ul>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertEnvironmentStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::environmentContentStructure() as $heading => $hint)
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
                <div id="astroConsultancyContentGuide" class="story-content-guide mb-3 astro-consultancy-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your full astro consultancy post.</p>
                            </div>
                            <span class="badge bg-warning text-dark">Astro Consultancy</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">Document traditional background, concepts, interpretation, and suggested practices below.</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertAstroConsultancyStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyContentStructure() as $heading => $hint)
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
                <div id="religionSpiritualityContentGuide" class="story-content-guide mb-3 religion-spirituality-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Content</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your Religion &amp; Spirituality post.</p>
                            </div>
                            <span class="badge bg-warning text-dark">Religion &amp; Spirituality</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">Support: images, tables, quotes, audio, videos, hyperlinks, and references.</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertReligionSpiritualityStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityContentStructure() as $heading => $hint)
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
                <div id="creativeCornerContentGuide" class="story-content-guide mb-3 creative-corner-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Creative work details</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your Creative Corner post.</p>
                            </div>
                            <span class="badge bg-warning text-dark">Creative Corner</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">Describe your creative work using the editor below.</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertCreativeCornerStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerContentStructure() as $heading => $hint)
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
                <div id="competitionsContentGuide" class="story-content-guide mb-3 competitions-flow-section" style="display:none;">
                    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="mb-1">Competition details</h5>
                                <p class="text-muted mb-0 small">Use the rich text editor below for your competition listing.</p>
                            </div>
                            <span class="badge bg-warning text-dark">Competitions</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Rich text editor <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-2">Describe the competition using the editor below.</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="insertCompetitionsStructureBtn">
                                        Insert suggested structure
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="story-content-panel h-100">
                                    <h6 class="story-content-panel__title">Suggested structure</h6>
                                    <ul class="story-content-structure list-unstyled small mb-0">
                                        @foreach(\App\Support\CommunityContentTaxonomy::competitionsContentStructure() as $heading => $hint)
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
                            <small class="text-muted d-block" id="editorLanguageHelp">Default is English. Choose Hinglish for phonetic typing, or Hindi for Word-style fonts and formatting.</small>
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
                    <div id="editorTransliterationHint" class="alert alert-info py-2 px-3 small mb-2 d-none" role="status">
                        <strong>Hinglish typing mode is ON.</strong> Click in the editor and type English letters — they convert to Hinglish instantly as you type.
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
            <div class="col-12 type-extra womens-world-flow" data-for="womens-world">
                @include('backend.community-posts.partials.womens-world-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra senior-citizens-forum-flow" data-for="senior-citizens-forum">
                @include('backend.community-posts.partials.senior-citizens-forum-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra student-corner-flow" data-for="student-corner">
                @include('backend.community-posts.partials.student-corner-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra youth-corner-flow" data-for="youth-corner">
                @include('backend.community-posts.partials.youth-corner-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra local-voices-flow" data-for="local-voices">
                @include('backend.community-posts.partials.local-voices-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra my-area-flow" data-for="my-area">
                @include('backend.community-posts.partials.my-area-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra community-issues-flow" data-for="community-issues">
                @include('backend.community-posts.partials.community-issues-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra agriculture-flow" data-for="agriculture">
                @include('backend.community-posts.partials.agriculture-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra environment-flow" data-for="environment">
                @include('backend.community-posts.partials.environment-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra science-technology-flow" data-for="science-technology">
                @include('backend.community-posts.partials.science-technology-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra astro-consultancy-flow" data-for="astro-consultancy">
                @include('backend.community-posts.partials.astro-consultancy-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra religion-spirituality-flow" data-for="religion-spirituality">
                @include('backend.community-posts.partials.religion-spirituality-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra creative-corner-flow" data-for="creative-corner">
                @include('backend.community-posts.partials.creative-corner-flow-fields', ['post' => $post, 'placement' => 'rest'])
            </div>
            <div class="col-12 type-extra competitions-flow" data-for="competitions">
                @include('backend.community-posts.partials.competitions-flow-fields', ['post' => $post, 'placement' => 'rest'])
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
                                <select name="report_status" class="form-select report-required">
                                    <option value="">Select report status</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::reportStatuses() as $reportStatus)
                                        <option value="{{ $reportStatus }}" @selected(old('report_status', data_get($post->meta, 'report_status')) === $reportStatus)>{{ $reportStatus }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">What this report is asking for or communicating.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Report type <span class="text-danger">*</span></label>
                                <select name="report_type" class="form-select report-required">
                                    <option value="">Select report type</option>
                                    @foreach(\App\Support\CommunityContentTaxonomy::reportTypes() as $reportType)
                                        <option value="{{ $reportType }}" @selected(old('report_type', data_get($post->meta, 'report_type')) === $reportType)>{{ $reportType }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Choose the format or nature of this report.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="issue_priority" class="form-select report-required">
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
                                <select name="report_author_type" id="reportAuthorType" class="form-select report-required">
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

        <div class="border rounded-3 p-3 bg-light mt-4 community-consent-section">
            <h5 class="mb-2 community-consent-section__title">Content responsibility &amp; posting policy</h5>
            <p class="community-consent-section__intro mb-3">
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
                <label class="form-check-label community-consent-section__label" for="acceptContentResponsibility">
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
                <label class="form-check-label community-consent-section__label" for="acceptOriginalWorkIndemnity">
                    I confirm that this content is my original work or that I have the necessary rights and permissions to publish it. I understand and agree that I am solely responsible for the content I submit, including its accuracy, legality, and compliance with applicable laws. I agree to indemnify and hold harmless SoilnWater, its owners, employees, and affiliates from any claims, damages, liabilities, costs, or legal proceedings arising from my submitted content.
                </label>
                @error('accept_original_work_indemnity')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
            </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('community.posts.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary ems-btn-primary">{{ $mode === 'edit' ? 'Update Post' : 'Create Post' }}</button>
        </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Tiro+Devanagari+Hindi&display=swap" rel="stylesheet">
<style>
    .type-extra { display: none; }
    .community-consent-section__title {
        font-size: 0.95rem;
        font-weight: 700;
    }
    .community-consent-section__intro,
    .community-consent-section__label {
        color: #64748b;
        font-size: 0.78rem;
        font-style: italic;
        line-height: 1.55;
    }
    .community-consent-section__intro a {
        font-style: italic;
    }
    .community-post-type-picker {
        background: transparent;
        border: 0;
        border-radius: 0;
        padding: 0;
    }
    .community-post-type-search {
        min-width: min(100%, 220px);
    }
    .community-post-type-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .community-post-type-card {
        --pill-color: #78909c;
        align-items: center;
        appearance: none !important;
        -webkit-appearance: none !important;
        background: var(--pill-color) !important;
        background-color: var(--pill-color) !important;
        border: 1px solid var(--pill-color) !important;
        border-radius: 999px !important;
        box-shadow: 0 2px 8px rgba(15, 47, 85, 0.18);
        color: #fff !important;
        cursor: pointer;
        display: inline-flex !important;
        gap: 0.35rem;
        justify-content: center;
        min-height: 40px;
        padding: 0.48rem 1.1rem;
        text-align: center;
        text-shadow: 0 1px 1px rgba(15, 47, 85, 0.18);
        transition: filter 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease, outline-color 0.2s ease;
        width: auto;
    }
    @foreach(\App\Support\CommunityContentTaxonomy::pillColors() as $pillKey => $pillHex)
    .ems-page button.community-post-type-card.community-post-type-card--{{ $pillKey }},
    .community-post-type-card.community-post-type-card--{{ $pillKey }} {
        --pill-color: {{ $pillHex }};
        background: {{ $pillHex }} !important;
        background-color: {{ $pillHex }} !important;
        border-color: {{ $pillHex }} !important;
        color: #fff !important;
    }
    @endforeach
    .community-post-type-card:hover,
    .community-post-type-card:focus-visible {
        background: var(--pill-color) !important;
        background-color: var(--pill-color) !important;
        box-shadow: 0 4px 14px rgba(15, 47, 85, 0.22);
        color: #fff !important;
        filter: brightness(1.06);
        outline: none;
        transform: translateY(-1px);
    }
    .community-post-type-card.is-selected {
        background: var(--pill-color) !important;
        background-color: var(--pill-color) !important;
        box-shadow: 0 6px 18px rgba(15, 47, 85, 0.28);
        color: #fff !important;
        filter: brightness(1.04);
        outline: 2px solid #0f2f55;
        outline-offset: 2px;
        transform: none;
    }
    .community-post-type-card.is-selected:hover,
    .community-post-type-card.is-selected:focus-visible {
        background: var(--pill-color) !important;
        background-color: var(--pill-color) !important;
        color: #fff !important;
        filter: brightness(1.08);
        transform: none;
    }
    .community-post-type-card__label {
        font-size: 0.83rem;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
    }
    .community-post-type-card.is-selected .community-post-type-card__label {
        font-weight: 700;
    }
    .community-post-type-empty {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: .75rem;
        color: #64748b;
        padding: .85rem 1rem;
    }
    #communityPostDetails[hidden] {
        display: none !important;
    }
    #communityPostDetails {
        scroll-margin-top: 1.25rem;
    }
    #communityPostDetails.is-revealed {
        animation: communityPostDetailsReveal 0.7s ease;
    }
    @keyframes communityPostDetailsReveal {
        0% {
            box-shadow: 0 0 0 0 rgba(25, 118, 210, 0.35);
        }
        40% {
            box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.18);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(25, 118, 210, 0);
        }
    }
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

    #bodyEditorMount.is-senior-citizens-forum-mode .ck-editor__editable_inline {
        font-size: 1.2rem;
        line-height: 1.75;
        min-height: 400px;
    }

    #bodyEditorMount.is-senior-citizens-forum-mode .ck.ck-toolbar .ck-button .ck-button__label,
    #bodyEditorMount.is-senior-citizens-forum-mode .ck.ck-toolbar .ck-dropdown .ck-button .ck-button__label {
        font-size: 0.95rem;
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
        font-family: "Noto Sans Devanagari", "Nirmala UI", "Mangal", "Aparajita", "Kokila", sans-serif;
        font-size: 1.05rem;
        line-height: 1.75;
    }

    #bodyEditorMount.is-hindi-word-mode .ck.ck-toolbar {
        flex-wrap: wrap;
        row-gap: 0.25rem;
    }

    #bodyEditorMount.is-hindi-word-mode .ck-editor__editable.ck-content {
        min-height: 280px;
        padding: 1.25rem 1.5rem;
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
    .womens-world-flow label.form-check,
    .student-corner-flow label.form-check,
    .youth-corner-flow label.form-check,
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
    .business-flow .community-flow-stack label.form-check,
    .womens-world-flow .community-flow-stack label.form-check,
    .student-corner-flow .community-flow-stack label.form-check,
    .youth-corner-flow .community-flow-stack label.form-check {
        align-items: flex-start;
    }
    .community-flow-checklist label.form-check:hover,
    .community-flow-stack label.form-check:hover,
    .news-flow-card label.form-check:hover,
    .story-flow-card label.form-check:hover,
    .awareness-flow label.form-check:hover,
    .business-flow label.form-check:hover,
    .womens-world-flow label.form-check:hover,
    .student-corner-flow label.form-check:hover,
    .youth-corner-flow label.form-check:hover,
    .childrens-corner-flow label.form-check:hover {
        border-color: rgba(13, 110, 253, 0.35) !important;
    }
    .community-flow-checklist label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .community-flow-stack label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .news-flow-card label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .story-flow-card label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .awareness-flow label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .business-flow label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .womens-world-flow label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .student-corner-flow label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .youth-corner-flow label.form-check:has(.form-check-input[type="checkbox"]:checked),
    .childrens-corner-flow label.form-check:has(.form-check-input[type="checkbox"]:checked) {
        background: #f0fdf4 !important;
        border-color: rgba(25, 135, 84, 0.45) !important;
        box-shadow: 0 0 0 1px rgba(25, 135, 84, 0.12);
    }
    .community-flow-checklist label.form-check:has(.form-check-input[type="radio"]:checked),
    .community-flow-stack label.form-check:has(.form-check-input[type="radio"]:checked),
    .awareness-flow label.form-check:has(.form-check-input[type="radio"]:checked),
    .business-flow label.form-check:has(.form-check-input[type="radio"]:checked),
    .womens-world-flow label.form-check:has(.form-check-input[type="radio"]:checked),
    .student-corner-flow label.form-check:has(.form-check-input[type="radio"]:checked),
    .youth-corner-flow label.form-check:has(.form-check-input[type="radio"]:checked),
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
    .womens-world-flow .form-check-input,
    .student-corner-flow .form-check-input,
    .youth-corner-flow .form-check-input,
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
    .business-flow .community-flow-stack .form-check-input,
    .womens-world-flow .community-flow-stack .form-check-input,
    .student-corner-flow .community-flow-stack .form-check-input,
    .youth-corner-flow .community-flow-stack .form-check-input {
        margin-top: 0.2rem !important;
    }
    .community-flow-checklist .form-check-input[type="radio"],
    .community-flow-stack .form-check-input[type="radio"],
    .news-flow-card .form-check-input[type="radio"],
    .story-flow-card .form-check-input[type="radio"],
    .awareness-flow .form-check-input[type="radio"],
    .business-flow .form-check-input[type="radio"],
    .womens-world-flow .form-check-input[type="radio"],
    .student-corner-flow .form-check-input[type="radio"],
    .youth-corner-flow .form-check-input[type="radio"],
    .childrens-corner-flow .form-check-input[type="radio"] {
        border-radius: 50%;
    }
    .community-flow-checklist .form-check-input[type="checkbox"]:checked,
    .community-flow-stack .form-check-input[type="checkbox"]:checked,
    .news-flow-card .form-check-input[type="checkbox"]:checked,
    .story-flow-card .form-check-input[type="checkbox"]:checked,
    .awareness-flow .form-check-input[type="checkbox"]:checked,
    .business-flow .form-check-input[type="checkbox"]:checked,
    .womens-world-flow .form-check-input[type="checkbox"]:checked,
    .student-corner-flow .form-check-input[type="checkbox"]:checked,
    .youth-corner-flow .form-check-input[type="checkbox"]:checked,
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
    .womens-world-flow .form-check-input[type="radio"]:checked,
    .student-corner-flow .form-check-input[type="radio"]:checked,
    .youth-corner-flow .form-check-input[type="radio"]:checked,
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
    .womens-world-flow .form-check-input:focus,
    .student-corner-flow .form-check-input:focus,
    .youth-corner-flow .form-check-input:focus,
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
    .womens-world-flow .form-check-label,
    .student-corner-flow .form-check-label,
    .youth-corner-flow .form-check-label,
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
    #bodyContentSection.is-waiting-for-type #editorTransliterationHint,
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
<script src="https://cdn.jsdelivr.net/npm/@indic-transliteration/sanscript@1.3.3/sanscript.js"></script>
<script>
    window.communityTypes = @json($types);
    window.communityBookTypes = @json(\App\Models\CommunityPost::BOOK_CONTENT_TYPES);
    window.communityLifeStoryTypes = @json(\App\Models\CommunityPost::LIFE_STORY_CONTENT_TYPES);
    window.communityBookPages = @json($communityBookPagesForJs);
    window.communityBodyEditor = null;
    window.communityActiveBookPage = 0;
    let communityBodyEditorInitPromise = null;
    const COMMUNITY_EDITOR_LANGUAGES = {
        en: { label: 'English', lang: 'en', dir: 'ltr' },
        hi: { label: 'Hinglish', lang: 'hi', dir: 'ltr' },
        hindi: { label: 'Hindi', lang: 'hi', dir: 'ltr' },
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
    const COMMUNITY_TRANSLITERATION_DEST_CODES = {
        hi: 'hi',
        ur: 'ur',
        pa: 'pa',
        bn: 'bn',
        mr: 'mr',
        gu: 'gu',
        ta: 'ta',
        te: 'te',
    };
    const COMMUNITY_SANSCRIPT_TARGETS = {
        hi: 'devanagari',
        mr: 'devanagari',
        bn: 'bengali',
        pa: 'gurmukhi',
        gu: 'gujarati',
        ta: 'tamil',
        te: 'telugu',
        ur: 'urdu',
    };
    let communityEditorTransliterationKeydownHandler = null;
    let communityEditorTransliterationSelectionHandler = null;
    let communityEditorTransliterationEditor = null;
    let communityPhoneticBuffer = '';
    let communityPhoneticInsertRange = null;
    let communityPhoneticUpdating = false;

    function isLifeStoryContentType(type) {
        return (window.communityLifeStoryTypes || []).includes(type);
    }

    function isCommunitySharedFormField(field) {
        if (!field) {
            return false;
        }

        return field.id === 'bodyEditor'
            || field.id === 'tagInput'
            || field.id === 'tagsHidden'
            || field.id === 'editorLanguageSelect'
            || Boolean(field.closest('#bodyEditorMount'))
            || Boolean(field.closest('#communityTagsWrap'))
            || Boolean(field.closest('#communityStructuredLocationWrapper'))
            || Boolean(field.closest('#communityFeaturedImagesWrap'))
            || Boolean(field.closest('#communityVideoWrap'));
    }

    function contentTypeMatchesDataset(selectedType, datasetFor) {
        return (datasetFor || '')
            .split(',')
            .map(function (value) { return value.trim(); })
            .filter(Boolean)
            .includes(selectedType);
    }

    function isCommunityFormSectionVisible(section) {
        return Boolean(section) && window.getComputedStyle(section).display !== 'none';
    }

    function writingPurposeNounForType(selectedType) {
        const known = {
            articles: 'article',
            reports: 'report',
            stories: 'story',
            news: 'news item',
            poetry: 'poem',
            biography: 'biography',
            autobiography: 'autobiography',
            discussions: 'discussion',
            competitions: 'competition',
        };

        if (known[selectedType]) {
            return known[selectedType];
        }

        const card = document.querySelector('.community-post-type-card[data-type="' + selectedType + '"]');
        const option = document.querySelector('#contentType option[value="' + selectedType + '"]');
        let noun = (
            card?.querySelector('.community-post-type-card__label')?.textContent
            || option?.textContent
            || 'post'
        ).trim().toLowerCase();

        if (
            noun.endsWith('s')
            && !noun.endsWith('ss')
            && noun !== 'news'
            && noun !== 'business'
        ) {
            noun = noun.slice(0, -1);
        }

        return noun || 'post';
    }

    function syncWritingPurposeLabel(selectedType) {
        const labelEl = document.getElementById('writingPurposeLabel');
        if (!labelEl) {
            return;
        }

        const noun = selectedType ? writingPurposeNounForType(selectedType) : 'post';
        labelEl.innerHTML = 'Why are you writing this ' + noun + '? <span class="text-danger">*</span>';
    }

    function syncContentTypePicker(selectedType) {
        document.querySelectorAll('.community-post-type-card').forEach((card) => {
            const isSelected = card.dataset.type === selectedType;
            card.classList.toggle('is-selected', isSelected);
            card.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        });

        const details = document.getElementById('communityPostDetails');
        const emptyHint = document.getElementById('contentTypeEmptyHint');
        const hasType = Boolean(selectedType);

        if (details) {
            details.hidden = !hasType;
        }
        if (emptyHint) {
            emptyHint.classList.toggle('d-none', hasType);
        }

        syncWritingPurposeLabel(selectedType);
    }

    function scrollToCommunityPostDetails() {
        const details = document.getElementById('communityPostDetails');
        if (!details || details.hidden) {
            return;
        }

        window.requestAnimationFrame(function () {
            details.scrollIntoView({ behavior: 'smooth', block: 'start' });
            details.classList.remove('is-revealed');
            // Retrigger the highlight animation on each selection.
            void details.offsetWidth;
            details.classList.add('is-revealed');

            const firstField = details.querySelector('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])');
            if (firstField && typeof firstField.focus === 'function') {
                window.setTimeout(function () {
                    try {
                        firstField.focus({ preventScroll: true });
                    } catch (error) {
                        firstField.focus();
                    }
                }, 350);
            }
        });
    }

    function setContentType(value, { silent = false } = {}) {
        const typeSelect = document.getElementById('contentType');
        if (!typeSelect) {
            return;
        }

        const nextValue = value || '';

        if (typeSelect.value === nextValue && silent) {
            syncContentTypePicker(nextValue);
            return;
        }

        typeSelect.value = nextValue;
        syncContentTypePicker(nextValue);

        if (!silent) {
            typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
            if (nextValue) {
                scrollToCommunityPostDetails();
            }
        }
    }

    function paintCommunityPostTypePillColors() {
        document.querySelectorAll('.community-post-type-card[data-pill-color]').forEach((card) => {
            const color = card.getAttribute('data-pill-color');
            if (!color) {
                return;
            }

            card.style.setProperty('--pill-color', color);
            card.style.setProperty('background', color, 'important');
            card.style.setProperty('background-color', color, 'important');
            card.style.setProperty('border-color', color, 'important');
            card.style.setProperty('color', '#fff', 'important');
        });
    }

    paintCommunityPostTypePillColors();

    document.getElementById('contentTypeGrid')?.addEventListener('click', function (event) {
        const card = event.target.closest('.community-post-type-card');
        if (!card) {
            return;
        }

        setContentType(card.dataset.type || '');
    });

    document.getElementById('contentTypeSearch')?.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();

        document.querySelectorAll('.community-post-type-card').forEach((card) => {
            const label = card.dataset.label || '';
            const type = (card.dataset.type || '').replace(/-/g, ' ');
            const matches = !query || label.includes(query) || type.includes(query);
            card.classList.toggle('d-none', !matches);
        });
    });

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

    function syncWomensWorldCategory() {
        const womensWorldCategory = document.getElementById('womensWorldCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'womens-world') {
            return;
        }

        categorySelect.value = womensWorldCategory;
        categorySelect.dataset.selected = womensWorldCategory;
    }

    document.getElementById('womensWorldCategory')?.addEventListener('change', syncWomensWorldCategory);

    function syncSeniorCitizensForumCategory() {
        const seniorCitizensForumCategory = document.getElementById('seniorCitizensForumCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'senior-citizens-forum') {
            return;
        }

        categorySelect.value = seniorCitizensForumCategory;
        categorySelect.dataset.selected = seniorCitizensForumCategory;
    }

    document.getElementById('seniorCitizensForumCategory')?.addEventListener('change', syncSeniorCitizensForumCategory);

    function syncStudentCornerCategory() {
        const studentCornerCategory = document.getElementById('studentCornerCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'student-corner') {
            return;
        }

        categorySelect.value = studentCornerCategory;
        categorySelect.dataset.selected = studentCornerCategory;
    }

    document.getElementById('studentCornerCategory')?.addEventListener('change', syncStudentCornerCategory);

    function syncYouthCornerCategory() {
        const youthCornerCategory = document.getElementById('youthCornerCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'youth-corner') {
            return;
        }

        categorySelect.value = youthCornerCategory;
        categorySelect.dataset.selected = youthCornerCategory;
    }

    document.getElementById('youthCornerCategory')?.addEventListener('change', syncYouthCornerCategory);

    function syncLocalVoicesCategory() {
        const localVoiceCategory = document.getElementById('localVoiceCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'local-voices') {
            return;
        }

        categorySelect.value = localVoiceCategory;
        categorySelect.dataset.selected = localVoiceCategory;
    }

    document.getElementById('localVoiceCategory')?.addEventListener('change', syncLocalVoicesCategory);

    function syncCommunityIssuesCategory() {
        const issueCategory = document.getElementById('communityIssueCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'community-issues') {
            return;
        }

        categorySelect.value = issueCategory;
        categorySelect.dataset.selected = issueCategory;
    }

    document.getElementById('communityIssueCategory')?.addEventListener('change', syncCommunityIssuesCategory);

    function syncAgricultureCategory() {
        const agricultureCategory = document.getElementById('agricultureCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'agriculture') {
            return;
        }

        categorySelect.value = agricultureCategory;
        categorySelect.dataset.selected = agricultureCategory;
    }

    document.getElementById('agricultureCategory')?.addEventListener('change', syncAgricultureCategory);

    function syncEnvironmentCategory() {
        const environmentCategory = document.getElementById('environmentCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'environment') {
            return;
        }

        categorySelect.value = environmentCategory;
        categorySelect.dataset.selected = environmentCategory;
    }

    document.getElementById('environmentCategory')?.addEventListener('change', syncEnvironmentCategory);

    function syncScienceTechnologyCategory() {
        const scienceTechnologyCategory = document.getElementById('scienceTechnologyCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'science-technology') {
            return;
        }

        categorySelect.value = scienceTechnologyCategory;
        categorySelect.dataset.selected = scienceTechnologyCategory;
    }

    document.getElementById('scienceTechnologyCategory')?.addEventListener('change', syncScienceTechnologyCategory);

    const ST_POST_TYPE_PROJECT = 'Project Showcase';
    const ST_POST_TYPE_EXPERIMENT = 'Experiment';
    const ST_POST_TYPE_SOFTWARE = 'Software Development';
    const ST_POST_TYPE_HARDWARE = 'Hardware Project';
    const ST_RESEARCH_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::scienceTechnologyResearchPostTypes());
    const ST_INNOVATION_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::scienceTechnologyInnovationPostTypes());

    function refreshScienceTechnologyPollFields() {
        const contentType = document.getElementById('contentType')?.value || '';
        const fields = document.getElementById('stPollFields');
        const enabled = document.getElementById('stAllowPoll')?.checked;
        if (fields) {
            fields.style.display = (contentType === 'science-technology' && enabled) ? '' : 'none';
        }
        const pollQuestion = document.getElementById('stPollQuestion');
        if (pollQuestion) {
            pollQuestion.required = contentType === 'science-technology' && Boolean(enabled);
            pollQuestion.disabled = !(contentType === 'science-technology' && enabled);
        }
        const pollOptions = document.getElementById('stPollOptions');
        if (pollOptions) {
            pollOptions.disabled = !(contentType === 'science-technology' && enabled);
        }
    }

    document.getElementById('stAllowPoll')?.addEventListener('change', refreshScienceTechnologyPollFields);

    function refreshScienceTechnologyConditionalSections() {
        const contentType = document.getElementById('contentType')?.value || '';
        const sectionIds = [
            'stProjectSection',
            'stResearchSection',
            'stExperimentSection',
            'stInnovationSection',
            'stSoftwareSection',
            'stHardwareSection',
        ];

        if (contentType !== 'science-technology') {
            sectionIds.forEach((id) => {
                const section = document.getElementById(id);
                if (section) {
                    section.style.display = 'none';
                }
            });
            refreshScienceTechnologyPollFields();
            return;
        }

        const postType = document.getElementById('scienceTechnologyPostType')?.value || '';

        const projectSection = document.getElementById('stProjectSection');
        if (projectSection) {
            projectSection.style.display = postType === ST_POST_TYPE_PROJECT ? '' : 'none';
        }

        const researchSection = document.getElementById('stResearchSection');
        if (researchSection) {
            researchSection.style.display = ST_RESEARCH_POST_TYPES.includes(postType) ? '' : 'none';
        }

        const experimentSection = document.getElementById('stExperimentSection');
        if (experimentSection) {
            experimentSection.style.display = postType === ST_POST_TYPE_EXPERIMENT ? '' : 'none';
        }

        const innovationSection = document.getElementById('stInnovationSection');
        if (innovationSection) {
            innovationSection.style.display = ST_INNOVATION_POST_TYPES.includes(postType) ? '' : 'none';
        }

        const softwareSection = document.getElementById('stSoftwareSection');
        if (softwareSection) {
            softwareSection.style.display = (postType === ST_POST_TYPE_SOFTWARE || postType === 'Tutorial') ? '' : 'none';
        }

        const hardwareSection = document.getElementById('stHardwareSection');
        if (hardwareSection) {
            hardwareSection.style.display = (postType === ST_POST_TYPE_HARDWARE || postType === 'Engineering Solution') ? '' : 'none';
        }

        refreshScienceTechnologyPollFields();
    }

    document.getElementById('scienceTechnologyPostType')?.addEventListener('change', refreshScienceTechnologyConditionalSections);
    document.getElementById('scienceTechnologyCategory')?.addEventListener('change', refreshScienceTechnologyConditionalSections);
    refreshScienceTechnologyConditionalSections();

    function syncAstroConsultancyCategory() {
        const astroConsultancyCategory = document.getElementById('astroConsultancyCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'astro-consultancy') {
            return;
        }

        categorySelect.value = astroConsultancyCategory;
        categorySelect.dataset.selected = astroConsultancyCategory;
    }

    document.getElementById('astroConsultancyCategory')?.addEventListener('change', syncAstroConsultancyCategory);

    const ASTRO_HOROSCOPE_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::astroConsultancyHoroscopePostTypes());
    const ASTRO_HOROSCOPE_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::astroConsultancyHoroscopeCategories());
    const ASTRO_VASTU_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::astroConsultancyVastuPostTypes());
    const ASTRO_VASTU_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::astroConsultancyVastuCategories());
    const ASTRO_NUMEROLOGY_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::astroConsultancyNumerologyPostTypes());
    const ASTRO_NUMEROLOGY_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::astroConsultancyNumerologyCategories());
    const ASTRO_FESTIVAL_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::astroConsultancyFestivalPostTypes());

    function refreshAstroConsultancyPollFields() {
        const contentType = document.getElementById('contentType')?.value || '';
        const fields = document.getElementById('astroPollFields');
        const enabled = document.getElementById('astroAllowPoll')?.checked;
        if (fields) {
            fields.style.display = (contentType === 'astro-consultancy' && enabled) ? '' : 'none';
        }
        const pollQuestion = document.getElementById('astroPollQuestion');
        if (pollQuestion) {
            pollQuestion.required = contentType === 'astro-consultancy' && Boolean(enabled);
            pollQuestion.disabled = !(contentType === 'astro-consultancy' && enabled);
        }
        const pollOptions = document.getElementById('astroPollOptions');
        if (pollOptions) {
            pollOptions.disabled = !(contentType === 'astro-consultancy' && enabled);
        }
    }

    document.getElementById('astroAllowPoll')?.addEventListener('change', refreshAstroConsultancyPollFields);

    function refreshAstroConsultancyConditionalSections() {
        const contentType = document.getElementById('contentType')?.value || '';
        const sectionIds = [
            'astroHoroscopeSection',
            'astroVastuSection',
            'astroNumerologySection',
            'astroFestivalSection',
        ];

        if (contentType !== 'astro-consultancy') {
            sectionIds.forEach((id) => {
                const section = document.getElementById(id);
                if (section) {
                    section.style.display = 'none';
                }
            });
            refreshAstroConsultancyPollFields();
            return;
        }

        const postType = document.getElementById('astroConsultancyPostType')?.value || '';
        const category = document.getElementById('astroConsultancyCategory')?.value || '';
        const showHoroscope = ASTRO_HOROSCOPE_POST_TYPES.includes(postType) || ASTRO_HOROSCOPE_CATEGORIES.includes(category);
        const showVastu = ASTRO_VASTU_POST_TYPES.includes(postType) || ASTRO_VASTU_CATEGORIES.includes(category);
        const showNumerology = ASTRO_NUMEROLOGY_POST_TYPES.includes(postType) || ASTRO_NUMEROLOGY_CATEGORIES.includes(category);
        const showFestival = ASTRO_FESTIVAL_POST_TYPES.includes(postType);

        const horoscopeSection = document.getElementById('astroHoroscopeSection');
        if (horoscopeSection) {
            horoscopeSection.style.display = showHoroscope ? '' : 'none';
        }

        const vastuSection = document.getElementById('astroVastuSection');
        if (vastuSection) {
            vastuSection.style.display = showVastu ? '' : 'none';
        }

        const numerologySection = document.getElementById('astroNumerologySection');
        if (numerologySection) {
            numerologySection.style.display = showNumerology ? '' : 'none';
        }

        const festivalSection = document.getElementById('astroFestivalSection');
        if (festivalSection) {
            festivalSection.style.display = showFestival ? '' : 'none';
        }

        refreshAstroConsultancyPollFields();
    }

    document.getElementById('astroConsultancyPostType')?.addEventListener('change', refreshAstroConsultancyConditionalSections);
    document.getElementById('astroConsultancyCategory')?.addEventListener('change', refreshAstroConsultancyConditionalSections);
    refreshAstroConsultancyConditionalSections();

    function syncReligionSpiritualityCategory() {
        const religionSpiritualityCategory = document.getElementById('religionSpiritualityCategory')?.value || '';
        const categorySelect = document.getElementById('categorySelect');

        if (!categorySelect || document.getElementById('contentType')?.value !== 'religion-spirituality') {
            return;
        }

        categorySelect.value = religionSpiritualityCategory;
        categorySelect.dataset.selected = religionSpiritualityCategory;
    }

    document.getElementById('religionSpiritualityCategory')?.addEventListener('change', syncReligionSpiritualityCategory);

    function syncCreativeCornerCategory() {
        const categorySelect = document.getElementById('categorySelect');
        const creativeCornerCategory = document.getElementById('creativeCornerCategory')?.value || '';

        if (!categorySelect || document.getElementById('contentType')?.value !== 'creative-corner') {
            return;
        }

        categorySelect.value = creativeCornerCategory;
        categorySelect.dataset.selected = creativeCornerCategory;
    }

    document.getElementById('creativeCornerCategory')?.addEventListener('change', syncCreativeCornerCategory);

    function syncCompetitionsCategory() {
        const categorySelect = document.getElementById('categorySelect');
        const competitionsCategory = document.getElementById('competitionsCategory')?.value || '';

        if (!categorySelect || document.getElementById('contentType')?.value !== 'competitions') {
            return;
        }

        categorySelect.value = competitionsCategory;
        categorySelect.dataset.selected = competitionsCategory;
    }

    document.getElementById('competitionsCategory')?.addEventListener('change', syncCompetitionsCategory);

    function refreshCompetitionsConditionalSections() {
        const contentType = document.getElementById('contentType')?.value || '';

        if (contentType !== 'competitions') {
            return;
        }

        const teamEnabled = document.getElementById('competitionsTeamAllowed')?.checked;
        const aiUsed = document.querySelector('input[name="competitions_ai_used"]:checked')?.value || 'No';
        const votingSystem = document.getElementById('competitionsVotingSystem')?.value || '';
        const publicVoting = ['Public Voting', 'Judges + Public'].includes(votingSystem);

        const teamFields = document.getElementById('competitionsTeamFields');
        if (teamFields) {
            teamFields.style.display = teamEnabled ? '' : 'none';
        }

        const aiFields = document.getElementById('competitionsAiFields');
        if (aiFields) {
            aiFields.style.display = aiUsed !== 'No' ? '' : 'none';
        }

        const publicVotingFields = document.getElementById('competitionsPublicVotingFields');
        if (publicVotingFields) {
            publicVotingFields.style.display = publicVoting ? '' : 'none';
        }
    }

    document.getElementById('competitionsTeamAllowed')?.addEventListener('change', refreshCompetitionsConditionalSections);
    document.querySelectorAll('input[name="competitions_ai_used"]').forEach((field) => {
        field.addEventListener('change', refreshCompetitionsConditionalSections);
    });
    document.getElementById('competitionsVotingSystem')?.addEventListener('change', refreshCompetitionsConditionalSections);

    function refreshCompetitionsUniqueFeatures() {
        const contentType = document.getElementById('contentType')?.value || '';
        const pairs = [
            ['competitionsEnableMultiSection', 'competitionsMultiSectionFields'],
            ['competitionsEnableAchievementBadges', 'competitionsAchievementBadgesFields'],
            ['competitionsEnableLeaderboards', 'competitionsLeaderboardsFields'],
            ['competitionsEnableInstitutionDashboard', 'competitionsInstitutionDashboardFields'],
            ['competitionsEnableSponsoredBranding', 'competitionsSponsoredBrandingFields'],
            ['competitionsEnableEcommerce', 'competitionsEcommerceFields'],
            ['competitionsEnableVotingFraudProtection', 'competitionsVotingFraudProtectionFields'],
            ['competitionsEnableDigitalCertificates', 'competitionsDigitalCertificatesFields'],
        ];

        pairs.forEach(([toggleId, fieldsId]) => {
            const fields = document.getElementById(fieldsId);
            const enabled = document.getElementById(toggleId)?.checked;
            if (fields) {
                fields.style.display = (contentType === 'competitions' && enabled) ? '' : 'none';
            }
        });
    }

    [
        'competitionsEnableMultiSection',
        'competitionsEnableAchievementBadges',
        'competitionsEnableLeaderboards',
        'competitionsEnableInstitutionDashboard',
        'competitionsEnableSponsoredBranding',
        'competitionsEnableEcommerce',
        'competitionsEnableVotingFraudProtection',
        'competitionsEnableDigitalCertificates',
    ].forEach((toggleId) => {
        document.getElementById(toggleId)?.addEventListener('change', refreshCompetitionsUniqueFeatures);
    });
    refreshCompetitionsUniqueFeatures();

    let competitionsJuryIndex = document.querySelectorAll('.competitions-jury-row').length;
    document.getElementById('competitionsAddJuryRow')?.addEventListener('click', function () {
        const list = document.getElementById('competitionsJuryList');
        if (!list) {
            return;
        }

        const index = competitionsJuryIndex++;
        const row = document.createElement('div');
        row.className = 'competitions-jury-row border rounded-3 p-3 bg-white';
        row.dataset.index = String(index);
        row.innerHTML = ''
            + '<div class="row g-3">'
            + '<div class="col-md-6"><label class="form-label">Judge name</label><input type="text" name="competitions_jury[' + index + '][name]" class="form-control competitions-flow-field" maxlength="160"></div>'
            + '<div class="col-md-6"><label class="form-label">Designation</label><input type="text" name="competitions_jury[' + index + '][designation]" class="form-control competitions-flow-field" maxlength="160"></div>'
            + '<div class="col-md-6"><label class="form-label">Organization</label><input type="text" name="competitions_jury[' + index + '][organization]" class="form-control competitions-flow-field" maxlength="160"></div>'
            + '<div class="col-md-6"><label class="form-label">Photo</label><input type="file" name="competitions_jury_photos[' + index + ']" class="form-control competitions-flow-field" accept="image/*"></div>'
            + '<div class="col-12"><label class="form-label">Profile</label><textarea name="competitions_jury[' + index + '][profile]" class="form-control competitions-flow-field" rows="2" maxlength="2000"></textarea></div>'
            + '</div>'
            + '<button type="button" class="btn btn-sm btn-outline-danger mt-2 competitions-remove-jury-row">Remove judge</button>';
        list.appendChild(row);
    });

    let competitionsSponsorIndex = document.querySelectorAll('.competitions-sponsor-row').length;
    document.getElementById('competitionsAddSponsorRow')?.addEventListener('click', function () {
        const list = document.getElementById('competitionsSponsorList');
        if (!list) {
            return;
        }

        const index = competitionsSponsorIndex++;
        const row = document.createElement('div');
        row.className = 'competitions-sponsor-row border rounded-3 p-3 bg-light';
        row.dataset.index = String(index);
        row.innerHTML = ''
            + '<div class="row g-3">'
            + '<div class="col-md-6"><label class="form-label">Sponsor name</label><input type="text" name="competitions_sponsors[' + index + '][name]" class="form-control competitions-flow-field" maxlength="160"></div>'
            + '<div class="col-md-6"><label class="form-label">Website</label><input type="url" name="competitions_sponsors[' + index + '][website]" class="form-control competitions-flow-field" maxlength="255" placeholder="https://"></div>'
            + '<div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="competitions_sponsor_logos[' + index + ']" class="form-control competitions-flow-field" accept="image/*"></div>'
            + '<div class="col-md-6"><label class="form-label">Contribution</label><input type="text" name="competitions_sponsors[' + index + '][contribution]" class="form-control competitions-flow-field" maxlength="255"></div>'
            + '</div>'
            + '<button type="button" class="btn btn-sm btn-outline-danger mt-2 competitions-remove-sponsor-row">Remove sponsor</button>';
        list.appendChild(row);
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('competitions-remove-jury-row')) {
            event.target.closest('.competitions-jury-row')?.remove();
        }

        if (event.target.classList.contains('competitions-remove-sponsor-row')) {
            event.target.closest('.competitions-sponsor-row')?.remove();
        }
    });

    function refreshCreativeCornerPollFields() {
        const contentType = document.getElementById('contentType')?.value || '';
        const enabled = document.getElementById('ccAllowPoll')?.checked;
        const fields = document.getElementById('ccPollFields');
        const pollQuestion = document.getElementById('ccPollQuestion');
        const pollOptions = document.getElementById('ccPollOptions');

        if (fields) {
            fields.style.display = (contentType === 'creative-corner' && enabled) ? '' : 'none';
        }

        if (pollQuestion) {
            pollQuestion.required = contentType === 'creative-corner' && Boolean(enabled);
            pollQuestion.disabled = !(contentType === 'creative-corner' && enabled);
        }

        if (pollOptions) {
            pollOptions.disabled = !(contentType === 'creative-corner' && enabled);
        }
    }

    document.getElementById('ccAllowPoll')?.addEventListener('change', refreshCreativeCornerPollFields);

    function refreshCreativeCornerConditionalSections() {
        const contentType = document.getElementById('contentType')?.value || '';

        if (contentType !== 'creative-corner') {
            return;
        }

        const competitionEnabled = document.getElementById('ccSubmitToCompetition')?.checked;
        const saleEnabled = document.getElementById('ccAvailableForSale')?.checked;
        const aiUsed = document.querySelector('input[name="creative_corner_ai_used"]:checked')?.value || 'No';

        const competitionFields = document.getElementById('ccCompetitionFields');
        if (competitionFields) {
            competitionFields.style.display = competitionEnabled ? '' : 'none';
        }

        const saleFields = document.getElementById('ccSaleFields');
        if (saleFields) {
            saleFields.style.display = saleEnabled ? '' : 'none';
        }

        const aiFields = document.getElementById('ccAiFields');
        if (aiFields) {
            aiFields.style.display = aiUsed !== 'No' ? '' : 'none';
        }

        refreshCreativeCornerPollFields();
    }

    document.getElementById('ccSubmitToCompetition')?.addEventListener('change', refreshCreativeCornerConditionalSections);
    document.getElementById('ccAvailableForSale')?.addEventListener('change', refreshCreativeCornerConditionalSections);
    document.querySelectorAll('input[name="creative_corner_ai_used"]').forEach((field) => {
        field.addEventListener('change', refreshCreativeCornerConditionalSections);
    });

    const RS_FESTIVAL_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityFestivalPostTypes());
    const RS_PILGRIMAGE_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityPilgrimagePostTypes());
    const RS_PLACE_OF_WORSHIP_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityPlaceOfWorshipPostTypes());
    const RS_MEDITATION_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityMeditationPostTypes());
    const RS_COMMUNITY_SERVICE_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityCommunityServicePostTypes());
    const RS_SCRIPTURE_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityScripturePostTypes());
    const RS_SCRIPTURE_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityScriptureCategories());
    const RS_FESTIVAL_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityFestivalCategories());
    const RS_PILGRIMAGE_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityPilgrimageCategories());
    const RS_PLACE_OF_WORSHIP_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityPlaceOfWorshipCategories());
    const RS_MEDITATION_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityMeditationCategories());
    const RS_COMMUNITY_SERVICE_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::religionSpiritualityCommunityServiceCategories());

    function refreshReligionSpiritualityPollFields() {
        const contentType = document.getElementById('contentType')?.value || '';
        const fields = document.getElementById('rsPollFields');
        const enabled = document.getElementById('rsAllowPoll')?.checked;
        if (fields) {
            fields.style.display = (contentType === 'religion-spirituality' && enabled) ? '' : 'none';
        }
        const pollQuestion = document.getElementById('rsPollQuestion');
        if (pollQuestion) {
            pollQuestion.required = contentType === 'religion-spirituality' && Boolean(enabled);
            pollQuestion.disabled = !(contentType === 'religion-spirituality' && enabled);
        }
        const pollOptions = document.getElementById('rsPollOptions');
        if (pollOptions) {
            pollOptions.disabled = !(contentType === 'religion-spirituality' && enabled);
        }
    }

    document.getElementById('rsAllowPoll')?.addEventListener('change', refreshReligionSpiritualityPollFields);

    function refreshReligionSpiritualityConditionalSections() {
        const contentType = document.getElementById('contentType')?.value || '';
        const sectionIds = [
            'rsScriptureSection',
            'rsFestivalSection',
            'rsPilgrimageSection',
            'rsPlaceOfWorshipSection',
            'rsLocationSection',
            'rsMeditationSection',
            'rsCommunityServiceSection',
        ];

        if (contentType !== 'religion-spirituality') {
            sectionIds.forEach((id) => {
                const section = document.getElementById(id);
                if (section) {
                    section.style.display = 'none';
                }
            });
            refreshReligionSpiritualityPollFields();
            return;
        }

        const postType = document.getElementById('religionSpiritualityPostType')?.value || '';
        const category = document.getElementById('religionSpiritualityCategory')?.value || '';
        const showScripture = RS_SCRIPTURE_POST_TYPES.includes(postType) || RS_SCRIPTURE_CATEGORIES.includes(category);
        const showFestival = RS_FESTIVAL_POST_TYPES.includes(postType) || RS_FESTIVAL_CATEGORIES.includes(category);
        const showPilgrimage = RS_PILGRIMAGE_POST_TYPES.includes(postType) || RS_PILGRIMAGE_CATEGORIES.includes(category);
        const showPlaceOfWorship = RS_PLACE_OF_WORSHIP_POST_TYPES.includes(postType) || RS_PLACE_OF_WORSHIP_CATEGORIES.includes(category);
        const showLocation = showPilgrimage || showPlaceOfWorship;
        const showMeditation = RS_MEDITATION_POST_TYPES.includes(postType) || RS_MEDITATION_CATEGORIES.includes(category);
        const showCommunityService = RS_COMMUNITY_SERVICE_POST_TYPES.includes(postType) || RS_COMMUNITY_SERVICE_CATEGORIES.includes(category);

        const scriptureSection = document.getElementById('rsScriptureSection');
        if (scriptureSection) {
            scriptureSection.style.display = showScripture ? '' : 'none';
        }

        const festivalSection = document.getElementById('rsFestivalSection');
        if (festivalSection) {
            festivalSection.style.display = showFestival ? '' : 'none';
        }

        const pilgrimageSection = document.getElementById('rsPilgrimageSection');
        if (pilgrimageSection) {
            pilgrimageSection.style.display = showPilgrimage ? '' : 'none';
        }

        const placeSection = document.getElementById('rsPlaceOfWorshipSection');
        if (placeSection) {
            placeSection.style.display = showPlaceOfWorship ? '' : 'none';
        }

        const locationSection = document.getElementById('rsLocationSection');
        if (locationSection) {
            locationSection.style.display = showLocation ? '' : 'none';
        }

        const meditationSection = document.getElementById('rsMeditationSection');
        if (meditationSection) {
            meditationSection.style.display = showMeditation ? '' : 'none';
        }

        const communityServiceSection = document.getElementById('rsCommunityServiceSection');
        if (communityServiceSection) {
            communityServiceSection.style.display = showCommunityService ? '' : 'none';
        }

        refreshReligionSpiritualityPollFields();
    }

    document.getElementById('religionSpiritualityPostType')?.addEventListener('change', refreshReligionSpiritualityConditionalSections);
    document.getElementById('religionSpiritualityCategory')?.addEventListener('change', refreshReligionSpiritualityConditionalSections);
    refreshReligionSpiritualityConditionalSections();

    function refreshReligionSpiritualityUniqueFeatures() {
        const contentType = document.getElementById('contentType')?.value || '';
        const pairs = [
            ['rsEnableDigitalPilgrimageGuide', 'rsDigitalPilgrimageGuideFields'],
            ['rsEnableFestivalCalendar', 'rsFestivalCalendarFields'],
            ['rsEnableCommunityServiceDirectory', 'rsCommunityServiceDirectoryFields'],
            ['rsEnableWisdomLibrary', 'rsWisdomLibraryFields'],
        ];

        pairs.forEach(([toggleId, fieldsId]) => {
            const fields = document.getElementById(fieldsId);
            const enabled = document.getElementById(toggleId)?.checked;
            if (fields) {
                fields.style.display = (contentType === 'religion-spirituality' && enabled) ? '' : 'none';
            }
        });
    }

    ['rsEnableDigitalPilgrimageGuide', 'rsEnableFestivalCalendar', 'rsEnableCommunityServiceDirectory', 'rsEnableWisdomLibrary'].forEach((toggleId) => {
        document.getElementById(toggleId)?.addEventListener('change', refreshReligionSpiritualityUniqueFeatures);
    });
    refreshReligionSpiritualityUniqueFeatures();

    const ENVIRONMENT_POST_TYPE_ISSUE = 'Environmental Issue';
    const ENVIRONMENT_POST_TYPE_INITIATIVE = 'Community Initiative';
    const ENVIRONMENT_POST_TYPE_WATER_ACTIVITY = 'Water Conservation Activity';
    const ENVIRONMENT_POST_TYPE_TREE_PLANTATION = 'Tree Plantation Drive';
    const ENVIRONMENT_POST_TYPE_WASTE = 'Waste Management Initiative';
    const ENVIRONMENT_POST_TYPE_GOVERNMENT_SCHEME = 'Government Scheme';
    const ENVIRONMENT_POST_TYPE_BIODIVERSITY = 'Biodiversity Documentation';
    const ENVIRONMENT_WATER_RELEVANT_CATEGORIES = ['Water Conservation', 'River Conservation', 'Wetlands'];
    const ENVIRONMENT_EVENT_POST_TYPES = @json(\App\Support\CommunityContentTaxonomy::environmentEventPostTypes());

    function refreshEnvironmentImpactCalculatorFields() {
        const contentType = document.getElementById('contentType')?.value || '';
        const fields = document.getElementById('environmentImpactCalculatorFields');
        const enabled = document.getElementById('environmentEnableImpactCalculator')?.checked;
        if (fields) {
            fields.style.display = (contentType === 'environment' && enabled) ? '' : 'none';
        }
    }

    document.getElementById('environmentEnableImpactCalculator')?.addEventListener('change', refreshEnvironmentImpactCalculatorFields);

    function refreshEnvironmentConditionalSections() {
        const contentType = document.getElementById('contentType')?.value || '';
        const sectionIds = [
            'environmentIssueSection',
            'environmentInitiativeSection',
            'environmentWaterConservationSection',
            'environmentSoilConservationSection',
            'environmentTreePlantationSection',
            'environmentWasteManagementSection',
            'environmentBiodiversitySection',
            'environmentEventSection',
            'environmentSchemeSection',
        ];

        if (contentType !== 'environment') {
            sectionIds.forEach((sectionId) => {
                const section = document.getElementById(sectionId);
                if (section) {
                    section.style.display = 'none';
                }
            });
            refreshEnvironmentImpactCalculatorFields();
            return;
        }

        const postType = document.getElementById('environmentPostType')?.value || '';
        const category = document.getElementById('environmentCategory')?.value || '';

        const issueSection = document.getElementById('environmentIssueSection');
        if (issueSection) {
            issueSection.style.display = postType === ENVIRONMENT_POST_TYPE_ISSUE ? '' : 'none';
        }

        const initiativeSection = document.getElementById('environmentInitiativeSection');
        if (initiativeSection) {
            initiativeSection.style.display = postType === ENVIRONMENT_POST_TYPE_INITIATIVE ? '' : 'none';
        }

        const waterSection = document.getElementById('environmentWaterConservationSection');
        if (waterSection) {
            const showWater = postType === ENVIRONMENT_POST_TYPE_WATER_ACTIVITY
                || ENVIRONMENT_WATER_RELEVANT_CATEGORIES.includes(category);
            waterSection.style.display = showWater ? '' : 'none';
        }

        const soilSection = document.getElementById('environmentSoilConservationSection');
        if (soilSection) {
            soilSection.style.display = category === 'Soil Conservation' ? '' : 'none';
        }

        const treeSection = document.getElementById('environmentTreePlantationSection');
        if (treeSection) {
            const showTree = postType === ENVIRONMENT_POST_TYPE_TREE_PLANTATION || category === 'Tree Plantation';
            treeSection.style.display = showTree ? '' : 'none';
        }

        const wasteSection = document.getElementById('environmentWasteManagementSection');
        if (wasteSection) {
            const showWaste = postType === ENVIRONMENT_POST_TYPE_WASTE
                || ['Waste Management', 'Plastic-Free Campaign'].includes(category);
            wasteSection.style.display = showWaste ? '' : 'none';
        }

        const biodiversitySection = document.getElementById('environmentBiodiversitySection');
        if (biodiversitySection) {
            const showBio = postType === ENVIRONMENT_POST_TYPE_BIODIVERSITY
                || ['Biodiversity', 'Wildlife Conservation', 'Wetlands', 'Forests'].includes(category);
            biodiversitySection.style.display = showBio ? '' : 'none';
        }

        const eventSection = document.getElementById('environmentEventSection');
        if (eventSection) {
            eventSection.style.display = ENVIRONMENT_EVENT_POST_TYPES.includes(postType) ? '' : 'none';
        }

        const schemeSection = document.getElementById('environmentSchemeSection');
        if (schemeSection) {
            schemeSection.style.display = postType === ENVIRONMENT_POST_TYPE_GOVERNMENT_SCHEME ? '' : 'none';
        }

        refreshEnvironmentImpactCalculatorFields();
    }

    document.getElementById('environmentPostType')?.addEventListener('change', refreshEnvironmentConditionalSections);
    document.getElementById('environmentCategory')?.addEventListener('change', refreshEnvironmentConditionalSections);
    refreshEnvironmentConditionalSections();
    refreshEnvironmentImpactCalculatorFields();

    const AGRICULTURE_CROP_RELEVANT_CATEGORIES = @json(\App\Support\CommunityContentTaxonomy::agricultureCropRelevantCategories());
    const AGRICULTURE_CROP_ADVISORY_SHARE_TYPE = 'Crop Advisory';
    const AGRICULTURE_LIVESTOCK_CATEGORIES = ['Livestock', 'Dairy Farming', 'Poultry Farming', 'Fish Farming', 'Beekeeping'];

    function refreshAgricultureCropSection() {
        const contentType = document.getElementById('contentType')?.value || '';
        const cropSection = document.getElementById('agricultureCropSection');
        if (!cropSection || contentType !== 'agriculture') {
            if (cropSection) {
                cropSection.style.display = 'none';
            }
            return;
        }

        const category = document.getElementById('agricultureCategory')?.value || '';
        const shareType = document.getElementById('agricultureShareType')?.value || '';
        const showCropDetails = AGRICULTURE_CROP_RELEVANT_CATEGORIES.includes(category)
            || shareType === AGRICULTURE_CROP_ADVISORY_SHARE_TYPE;
        cropSection.style.display = showCropDetails ? '' : 'none';
    }

    function refreshAgricultureSoilParameters() {
        const soilSection = document.getElementById('agricultureSoilParametersSection');
        const conducted = document.querySelector('input[name="agriculture_soil_test_conducted"]:checked')?.value || '';
        if (soilSection) {
            soilSection.style.display = conducted === 'yes' ? '' : 'none';
        }
    }

    document.querySelectorAll('input[name="agriculture_soil_test_conducted"]').forEach((field) => {
        field.addEventListener('change', refreshAgricultureSoilParameters);
    });
    refreshAgricultureSoilParameters();

    function refreshAgricultureConditionalSections() {
        const contentType = document.getElementById('contentType')?.value || '';
        if (contentType !== 'agriculture') {
            [
                'agricultureProblemSection',
                'agricultureMachinerySection',
                'agricultureSchemeSection',
                'agricultureMarketSection',
                'agricultureLivestockSection',
                'agricultureInnovationSection',
                'agricultureAgriBusinessSection',
            ].forEach((sectionId) => {
                const section = document.getElementById(sectionId);
                if (section) {
                    section.style.display = 'none';
                }
            });
            return;
        }

        const category = document.getElementById('agricultureCategory')?.value || '';
        const shareType = document.getElementById('agricultureShareType')?.value || '';

        const problemSection = document.getElementById('agricultureProblemSection');
        if (problemSection) {
            const showProblem = ['Problem & Solution', 'Question & Discussion', 'Crop Advisory'].includes(shareType);
            problemSection.style.display = showProblem ? '' : 'none';
        }

        const machinerySection = document.getElementById('agricultureMachinerySection');
        if (machinerySection) {
            machinerySection.style.display = (category === 'Farm Machinery' || shareType === 'Equipment Review') ? '' : 'none';
        }

        const schemeSection = document.getElementById('agricultureSchemeSection');
        if (schemeSection) {
            schemeSection.style.display = (category === 'Government Schemes' || shareType === 'Government Scheme') ? '' : 'none';
        }

        const marketSection = document.getElementById('agricultureMarketSection');
        if (marketSection) {
            marketSection.style.display = shareType === 'Market Information' ? '' : 'none';
        }

        const livestockSection = document.getElementById('agricultureLivestockSection');
        if (livestockSection) {
            livestockSection.style.display = (AGRICULTURE_LIVESTOCK_CATEGORIES.includes(category) || shareType === 'Livestock Management') ? '' : 'none';
        }

        const innovationSection = document.getElementById('agricultureInnovationSection');
        if (innovationSection) {
            innovationSection.style.display = shareType === 'Agricultural Innovation' ? '' : 'none';
        }

        const agriBusinessSection = document.getElementById('agricultureAgriBusinessSection');
        if (agriBusinessSection) {
            agriBusinessSection.style.display = (category === 'Agri-Business' || shareType === 'Agri-Business Opportunity') ? '' : 'none';
        }

        refreshAgricultureCropSection();
    }

    document.getElementById('agricultureCategory')?.addEventListener('change', refreshAgricultureConditionalSections);
    document.getElementById('agricultureShareType')?.addEventListener('change', refreshAgricultureConditionalSections);
    refreshAgricultureConditionalSections();

    function syncMyAreaCategory() {
        const categorySelect = document.getElementById('categorySelect');
        const topicCategory = document.getElementById('myAreaTopicCategory')?.value || '';
        if (!categorySelect || document.getElementById('contentType')?.value !== 'my-area') {
            return;
        }
        categorySelect.value = topicCategory;
        categorySelect.dataset.selected = topicCategory;
    }

    document.getElementById('myAreaTopicCategory')?.addEventListener('change', syncMyAreaCategory);

    function refreshMyAreaConditionalSections() {
        const activity = document.getElementById('myAreaActivityType')?.value || '';
        const heroSection = document.getElementById('myAreaHeroSection');
        const achievementSection = document.getElementById('myAreaAchievementSection');
        if (heroSection) heroSection.style.display = activity === 'Recognize Heroes' ? '' : 'none';
        if (achievementSection) achievementSection.style.display = activity === 'Share Local Achievements' ? '' : 'none';
    }

    document.getElementById('myAreaActivityType')?.addEventListener('change', refreshMyAreaConditionalSections);
    refreshMyAreaConditionalSections();

    function refreshMyAreaPrivacyFields() {
        const visibilitySelect = document.getElementById('myAreaVisibility');
        const privateLinkInfo = document.getElementById('myAreaPrivateLinkInfo');
        if (visibilitySelect && privateLinkInfo) {
            privateLinkInfo.style.display = visibilitySelect.value === 'private_link' ? '' : 'none';
        }
    }

    document.getElementById('myAreaVisibility')?.addEventListener('change', refreshMyAreaPrivacyFields);
    refreshMyAreaPrivacyFields();

    const STUDENT_CORNER_PROJECT_CONTENT_TYPE = 'Project Submission';

    function refreshStudentCornerProjectSection() {
        const contentType = document.getElementById('contentType')?.value || '';
        const studentCornerContentType = document.getElementById('studentCornerContentType')?.value || '';
        const isStudentCorner = contentType === 'student-corner';
        const isProjectSubmission = isStudentCorner && studentCornerContentType === STUDENT_CORNER_PROJECT_CONTENT_TYPE;
        const projectSection = document.getElementById('studentCornerProjectSection');

        if (projectSection) {
            projectSection.style.display = isProjectSubmission ? '' : 'none';
        }

        document.querySelectorAll('.student-corner-project-required').forEach((field) => {
            field.required = isProjectSubmission;
        });

        document.querySelectorAll('.student-corner-project-field').forEach((field) => {
            field.disabled = !isProjectSubmission;
        });
    }

    document.getElementById('studentCornerContentType')?.addEventListener('change', refreshStudentCornerProjectSection);

    const YOUTH_CORNER_PROJECT_CONTENT_TYPE = 'Project Showcase';

    function refreshYouthCornerProjectSection() {
        const contentType = document.getElementById('contentType')?.value || '';
        const youthCornerContentType = document.getElementById('youthCornerContentType')?.value || '';
        const isYouthCorner = contentType === 'youth-corner';
        const isProjectShowcase = isYouthCorner && youthCornerContentType === YOUTH_CORNER_PROJECT_CONTENT_TYPE;
        const projectSection = document.getElementById('youthCornerProjectSection');

        if (projectSection) {
            projectSection.style.display = isProjectShowcase ? '' : 'none';
        }

        document.querySelectorAll('.youth-corner-project-required').forEach((field) => {
            field.required = isProjectShowcase;
        });

        document.querySelectorAll('.youth-corner-project-field').forEach((field) => {
            field.disabled = !isProjectShowcase;
        });
    }

    document.getElementById('youthCornerContentType')?.addEventListener('change', refreshYouthCornerProjectSection);

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

    document.getElementById('insertWomensWorldStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Background</h2>',
            '<p>Set the context for your story or topic.</p>',
            '<h2>Challenge</h2>',
            '<p>Describe the difficulty, situation, or issue faced.</p>',
            '<h2>Experience</h2>',
            '<p>Share what happened and how you navigated it.</p>',
            '<h2>Lessons Learned</h2>',
            '<p>Key takeaways from your journey.</p>',
            '<h2>Advice to Others</h2>',
            '<p>Practical guidance for readers in similar situations.</p>',
            '<h2>Conclusion</h2>',
            '<p>Closing message, reflection, or call to action.</p>',
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

    document.getElementById('insertSeniorCitizensForumStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Background</h2>',
            '<p>Set the context — time, place, and situation.</p>',
            '<h2>Experience</h2>',
            '<p>Share what happened and how you lived through it.</p>',
            '<h2>Lessons Learned</h2>',
            '<p>Key takeaways from your journey.</p>',
            '<h2>Advice</h2>',
            '<p>Practical guidance for younger readers or the community.</p>',
            '<h2>Conclusion</h2>',
            '<p>Closing reflection or message.</p>',
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

    document.getElementById('insertStudentCornerStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Introduction</h2>',
            '<p>Introduce the topic and why it matters to students.</p>',
            '<h2>Objective</h2>',
            '<p>State what readers will learn or achieve.</p>',
            '<h2>Main Content</h2>',
            '<p>Share the core information, explanation, or experience.</p>',
            '<h2>Learnings</h2>',
            '<p>Highlight key takeaways from your study, project, or journey.</p>',
            '<h2>Tips / Recommendations</h2>',
            '<p>Practical advice for fellow students.</p>',
            '<h2>Conclusion</h2>',
            '<p>Closing summary, reflection, or call to action.</p>',
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

    document.getElementById('insertYouthCornerStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Problem/Challenge</h2>',
            '<p>Describe the challenge or situation you faced.</p>',
            '<h2>Experience/Story</h2>',
            '<p>Share your personal experience or journey.</p>',
            '<h2>Actions Taken</h2>',
            '<p>Explain the steps you took to address it.</p>',
            '<h2>Results</h2>',
            '<p>Highlight outcomes and impact.</p>',
            '<h2>Lessons Learned</h2>',
            '<p>Key takeaways from your experience.</p>',
            '<h2>Advice for Others</h2>',
            '<p>Practical guidance for fellow youth.</p>',
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

    document.getElementById('insertLocalVoicesStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Issue / Topic</h2>',
            '<p>State the main issue or topic clearly.</p>',
            '<h2>Background</h2>',
            '<p>Provide context and history.</p>',
            '<h2>Current Situation</h2>',
            '<p>Describe what is happening now.</p>',
            '<h2>Impact on Community</h2>',
            '<p>Explain who is affected and how.</p>',
            '<h2>Suggested Solution</h2>',
            '<p>Share practical ideas or requests.</p>',
            '<h2>Call for Action</h2>',
            '<p>Tell readers what they can do next.</p>',
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

    document.getElementById('insertCommunityIssuesStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>What is the Issue?</h2>',
            '<p>Describe the problem clearly.</p>',
            '<h2>When Did It Start?</h2>',
            '<p>When did you first notice it?</p>',
            '<h2>Who Is Affected?</h2>',
            '<p>Residents, students, businesses, or other groups.</p>',
            '<h2>What Is the Impact?</h2>',
            '<p>Explain how this affects daily life or safety.</p>',
            '<h2>What Action Has Been Taken So Far?</h2>',
            '<p>Complaints filed, authorities contacted, or community steps.</p>',
            '<h2>Suggested Solution</h2>',
            '<p>Practical ideas or requests for resolution.</p>',
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

    document.getElementById('insertAgricultureStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Background</h2>',
            '<p>Set the context for your farming topic or experience.</p>',
            '<h2>Problem</h2>',
            '<p>Describe the challenge, pest, weather, or market issue.</p>',
            '<h2>Method Used</h2>',
            '<p>Explain practices, inputs, or techniques applied.</p>',
            '<h2>Results</h2>',
            '<p>Share outcomes, yields, savings, or improvements.</p>',
            '<h2>Challenges</h2>',
            '<p>Note difficulties faced during the process.</p>',
            '<h2>Recommendations</h2>',
            '<p>Offer advice for other farmers or readers.</p>',
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

    document.getElementById('insertEnvironmentStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Background</h2>',
            '<p>Set the environmental context and why this topic matters.</p>',
            '<h2>Current Situation</h2>',
            '<p>Describe the present condition on the ground.</p>',
            '<h2>Environmental Impact</h2>',
            '<p>Explain effects on water, soil, air, wildlife, or communities.</p>',
            '<h2>Actions Taken</h2>',
            '<p>Document interventions, initiatives, or responses so far.</p>',
            '<h2>Results</h2>',
            '<p>Share measurable or observed outcomes.</p>',
            '<h2>Future Recommendations</h2>',
            '<p>Suggest next steps for the community or authorities.</p>',
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

    document.getElementById('insertAstroConsultancyStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Introduction</h2>',
            '<p>Introduce the topic and what readers will learn.</p>',
            '<h2>Traditional Background</h2>',
            '<p>Share cultural or scriptural context where relevant.</p>',
            '<h2>Astrological Concept</h2>',
            '<p>Explain the principle, planet, sign, or tradition involved.</p>',
            '<h2>Interpretation</h2>',
            '<p>Offer perspective as belief, tradition, or professional opinion.</p>',
            '<h2>Suggested Practices</h2>',
            '<p>Recommend rituals, remedies, or reflections readers may consider.</p>',
            '<h2>Conclusion</h2>',
            '<p>Summarize key takeaways and encourage thoughtful judgment.</p>',
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

    document.getElementById('insertReligionSpiritualityStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Introduction</h2>',
            '<p>Introduce the topic and its spiritual or cultural significance.</p>',
            '<h2>Historical Background</h2>',
            '<p>Share relevant history, origins, or context.</p>',
            '<h2>Teachings</h2>',
            '<p>Explain core teachings, practices, or beliefs respectfully.</p>',
            '<h2>Practical Relevance</h2>',
            '<p>Describe how readers can apply or appreciate this today.</p>',
            '<h2>Conclusion</h2>',
            '<p>Summarize key takeaways with a message of unity and respect.</p>',
            '<h2>References</h2>',
            '<p>Cite scriptures, scholars, or reliable sources where applicable.</p>',
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

    document.getElementById('insertCreativeCornerStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Concept</h2>',
            '<p>Describe the core idea behind your creative work.</p>',
            '<h2>Inspiration</h2>',
            '<p>What inspired you to create this?</p>',
            '<h2>Materials Used</h2>',
            '<p>List materials, tools, or resources used.</p>',
            '<h2>Creative Process</h2>',
            '<p>Walk through how you created this work.</p>',
            '<h2>Challenges</h2>',
            '<p>Share obstacles you faced and how you overcame them.</p>',
            '<h2>Final Outcome</h2>',
            '<p>Describe the finished result.</p>',
            '<h2>Future Improvements</h2>',
            '<p>What would you do differently next time?</p>',
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

    document.getElementById('insertCompetitionsStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Objective</h2>',
            '<p>What is the purpose of this competition?</p>',
            '<h2>Theme</h2>',
            '<p>Central theme or focus area participants should follow.</p>',
            '<h2>Who Can Participate</h2>',
            '<p>Eligibility, age groups, and any restrictions.</p>',
            '<h2>Submission Requirements</h2>',
            '<p>Format, file types, word limits, and delivery instructions.</p>',
            '<h2>Judging Criteria</h2>',
            '<p>How entries will be evaluated.</p>',
            '<h2>Prizes</h2>',
            '<p>Awards, recognition, and benefits for winners.</p>',
            '<h2>Important Dates</h2>',
            '<p>Registration, submission, and result timelines.</p>',
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

    document.getElementById('insertMyAreaStructureBtn')?.addEventListener('click', function () {
        const structureHtml = [
            '<h2>Issue / Topic</h2>',
            '<p>What is happening in your area?</p>',
            '<h2>Background</h2>',
            '<p>Provide local context.</p>',
            '<h2>Current Situation</h2>',
            '<p>Describe the present condition.</p>',
            '<h2>Impact on Community</h2>',
            '<p>Who is affected and how?</p>',
            '<h2>Suggested Solution</h2>',
            '<p>Practical ideas or requests.</p>',
            '<h2>Call for Action</h2>',
            '<p>What should neighbours or authorities do?</p>',
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

    function refreshLocalVoicesConditionalSections() {
        const voiceType = document.getElementById('localVoiceType')?.value || '';
        const heroSection = document.getElementById('localVoiceHeroSection');
        const initiativeSection = document.getElementById('localVoiceInitiativeSection');

        if (heroSection) {
            heroSection.style.display = voiceType === 'Local Hero' ? '' : 'none';
        }

        if (initiativeSection) {
            initiativeSection.style.display = voiceType === 'Community Initiative' ? '' : 'none';
        }
    }

    document.getElementById('localVoiceType')?.addEventListener('change', refreshLocalVoicesConditionalSections);
    refreshLocalVoicesConditionalSections();

    function refreshLocalVoicesPrivacyFields() {
        const visibilitySelect = document.getElementById('localVoiceVisibility');
        const privateLinkInfo = document.getElementById('localVoicePrivateLinkInfo');
        if (!visibilitySelect || !privateLinkInfo) {
            return;
        }

        privateLinkInfo.style.display = visibilitySelect.value === 'private_link' ? '' : 'none';
    }

    document.getElementById('localVoiceVisibility')?.addEventListener('change', refreshLocalVoicesPrivacyFields);
    document.getElementById('localVoiceCopyPrivateLinkBtn')?.addEventListener('click', function () {
        const input = document.getElementById('localVoicePrivateLinkUrl');
        if (!input?.value) {
            return;
        }
        navigator.clipboard?.writeText(input.value).then(function () {
            notify('success', 'Private link copied.');
        }).catch(function () {
            input.select();
            document.execCommand('copy');
            notify('success', 'Private link copied.');
        });
    });
    refreshLocalVoicesPrivacyFields();

    function refreshCommunityIssuesPrivacyFields() {
        const visibilitySelect = document.getElementById('communityIssueVisibility');
        const privateLinkInfo = document.getElementById('communityIssuePrivateLinkInfo');
        const penNameWrap = document.getElementById('communityIssuePenNameWrap');
        const selectedPublishAs = document.querySelector('#communityIssuePublishAsWrap input[name="publish_as"]:checked')?.value || '';

        if (visibilitySelect && privateLinkInfo) {
            privateLinkInfo.style.display = visibilitySelect.value === 'private_link' ? '' : 'none';
        }

        if (penNameWrap) {
            penNameWrap.style.display = selectedPublishAs === 'pen_name' ? '' : 'none';
        }
    }

    function refreshCommunityIssuesPriorReportFields() {
        const reportedYes = document.getElementById('communityIssueReportedYes')?.checked;
        const priorFields = document.getElementById('communityIssuePriorReportFields');
        if (priorFields) {
            priorFields.style.display = reportedYes ? '' : 'none';
        }
    }

    document.getElementById('communityIssueVisibility')?.addEventListener('change', refreshCommunityIssuesPrivacyFields);
    document.querySelectorAll('#communityIssuePublishAsWrap input[name="publish_as"]').forEach((input) => {
        input.addEventListener('change', refreshCommunityIssuesPrivacyFields);
    });
    document.getElementById('communityIssueCopyPrivateLinkBtn')?.addEventListener('click', function () {
        const input = document.getElementById('communityIssuePrivateLinkUrl');
        if (!input?.value) {
            return;
        }
        navigator.clipboard?.writeText(input.value).then(function () {
            notify('success', 'Private link copied.');
        }).catch(function () {
            input.select();
            document.execCommand('copy');
            notify('success', 'Private link copied.');
        });
    });
    document.getElementById('communityIssueReportedYes')?.addEventListener('change', refreshCommunityIssuesPriorReportFields);
    document.getElementById('communityIssueReportedNo')?.addEventListener('change', refreshCommunityIssuesPriorReportFields);
    document.getElementById('communityIssueAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);
    document.getElementById('agricultureAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);
    document.getElementById('environmentAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);
    refreshCommunityIssuesPrivacyFields();
    refreshCommunityIssuesPriorReportFields();

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
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isChildrensCorner;
        });

        document.querySelectorAll('.childrens-corner-flow-section input, .childrens-corner-flow-section textarea, .childrens-corner-flow-section select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

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
                ? 'Choose the script you are writing in. Type English letters and press space to convert automatically.'
                : 'Default is English. Choose Hinglish for phonetic typing, or Hindi for Word-style fonts and formatting.';
        }

        syncCommunityEditorTransliteration(nextValue);
    }

    function refreshPoetryEditorMode(contentType) {
        const editorMount = document.getElementById('bodyEditorMount');
        const isPoetry = contentType === 'poetry';
        const isSeniorCitizensForum = contentType === 'senior-citizens-forum';

        if (editorMount) {
            editorMount.classList.toggle('is-poetry-mode', isPoetry);
            editorMount.classList.toggle('is-senior-citizens-forum-mode', isSeniorCitizensForum);
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

    function getCommunitySanscript() {
        return window.Sanscript || null;
    }

    function transliterateCommunityWordWithSanscript(word, destLangCode) {
        const sanscript = getCommunitySanscript();
        const target = COMMUNITY_SANSCRIPT_TARGETS[destLangCode];

        if (!target || !sanscript || typeof sanscript.t !== 'function' || !word) {
            return word;
        }

        try {
            const converted = sanscript.t(word, 'itrans', target, { syncope: true });

            return converted && converted !== word ? converted : word;
        } catch (error) {
            return word;
        }
    }

    function resetCommunityPhoneticComposition() {
        communityPhoneticBuffer = '';
        communityPhoneticInsertRange = null;
    }

    function updateCommunityPhoneticCompositionInEditor(editor, destCode) {
        const converted = transliterateCommunityWordWithSanscript(communityPhoneticBuffer, destCode) || communityPhoneticBuffer;

        communityPhoneticUpdating = true;

        editor.model.change(function (writer) {
            let insertPosition = editor.model.document.selection.getFirstPosition();

            if (communityPhoneticInsertRange) {
                insertPosition = communityPhoneticInsertRange.start;
                writer.remove(communityPhoneticInsertRange);
            }

            writer.insertText(converted, insertPosition);

            const endPosition = insertPosition.getShiftedBy(converted.length);
            communityPhoneticInsertRange = writer.createRange(insertPosition, endPosition);
            writer.setSelection(endPosition);
        });

        communityPhoneticUpdating = false;
    }

    function commitCommunityPhoneticCompositionInEditor(editor, destCode, suffix) {
        if (!communityPhoneticBuffer) {
            return false;
        }

        const converted = transliterateCommunityWordWithSanscript(communityPhoneticBuffer, destCode) || communityPhoneticBuffer;

        communityPhoneticUpdating = true;

        editor.model.change(function (writer) {
            let insertPosition = editor.model.document.selection.getFirstPosition();

            if (communityPhoneticInsertRange) {
                insertPosition = communityPhoneticInsertRange.start;
                writer.remove(communityPhoneticInsertRange);
            }

            writer.insertText(converted + suffix, insertPosition);
            writer.setSelection(insertPosition.getShiftedBy(converted.length + suffix.length));
        });

        communityPhoneticUpdating = false;
        resetCommunityPhoneticComposition();

        return true;
    }

    function detachCommunityEditorTransliteration(editor) {
        const activeEditor = editor || communityEditorTransliterationEditor;

        if (activeEditor?.editing?.view?.document && communityEditorTransliterationKeydownHandler) {
            activeEditor.editing.view.document.off('keydown', communityEditorTransliterationKeydownHandler);
        }

        if (activeEditor?.model?.document?.selection && communityEditorTransliterationSelectionHandler) {
            activeEditor.model.document.selection.off('change:range', communityEditorTransliterationSelectionHandler);
        }

        communityEditorTransliterationKeydownHandler = null;
        communityEditorTransliterationSelectionHandler = null;
        communityEditorTransliterationEditor = null;
        resetCommunityPhoneticComposition();
    }

    function attachCommunityEditorTransliteration(editor) {
        detachCommunityEditorTransliteration(editor);

        communityEditorTransliterationEditor = editor;

        communityEditorTransliterationSelectionHandler = function () {
            if (communityPhoneticUpdating || !communityPhoneticBuffer || !communityPhoneticInsertRange) {
                return;
            }

            const selection = editor.model.document.selection;

            if (!selection.isCollapsed) {
                resetCommunityPhoneticComposition();
                return;
            }

            const focus = selection.focus;
            const compositionEnd = communityPhoneticInsertRange.end;

            if (!focus.isEqual(compositionEnd)) {
                resetCommunityPhoneticComposition();
            }
        };

        communityEditorTransliterationKeydownHandler = function (event, data) {
            const domEvent = data.domEvent;

            if (!domEvent || domEvent.isComposing || domEvent.ctrlKey || domEvent.metaKey || domEvent.altKey) {
                return;
            }

            const destCode = COMMUNITY_TRANSLITERATION_DEST_CODES[getActiveEditorLanguage()];

            if (!destCode) {
                return;
            }

            const key = domEvent.key;

            if (key === ' ' || key === 'Enter') {
                if (communityPhoneticBuffer) {
                    domEvent.preventDefault();
                    event.stop();
                    commitCommunityPhoneticCompositionInEditor(editor, destCode, key === 'Enter' ? '\n' : ' ');
                }

                return;
            }

            if (key === 'Backspace') {
                if (!communityPhoneticBuffer) {
                    return;
                }

                domEvent.preventDefault();
                event.stop();
                communityPhoneticBuffer = communityPhoneticBuffer.slice(0, -1);

                if (communityPhoneticBuffer === '') {
                    editor.model.change(function (writer) {
                        if (communityPhoneticInsertRange) {
                            writer.remove(communityPhoneticInsertRange);
                            writer.setSelection(communityPhoneticInsertRange.start);
                        }
                    });
                    resetCommunityPhoneticComposition();
                    return;
                }

                updateCommunityPhoneticCompositionInEditor(editor, destCode);
                return;
            }

            if (/^[a-zA-Z]$/.test(key)) {
                domEvent.preventDefault();
                event.stop();
                communityPhoneticBuffer += key;
                updateCommunityPhoneticCompositionInEditor(editor, destCode);
            }
        };

        editor.model.document.selection.on('change:range', communityEditorTransliterationSelectionHandler);
        editor.editing.view.document.on('keydown', communityEditorTransliterationKeydownHandler, { priority: 'highest' });
    }

    function syncCommunityEditorTransliteration(languageCode) {
        const language = normalizeEditorLanguage(languageCode);
        const editor = window.communityBodyEditor;
        const hint = document.getElementById('editorTransliterationHint');
        const editorMount = document.getElementById('bodyEditorMount');
        const needsTransliteration = Boolean(COMMUNITY_TRANSLITERATION_DEST_CODES[language]);
        const languageLabel = (COMMUNITY_EDITOR_LANGUAGES[language] || {}).label || language;
        const isHindiWordMode = language === 'hindi';

        if (editorMount) {
            editorMount.classList.toggle('is-hindi-word-mode', isHindiWordMode);
        }

        if (hint) {
            if (needsTransliteration) {
                hint.classList.remove('d-none');
                hint.innerHTML = '<strong>' + languageLabel + ' typing mode is ON.</strong> Click in the editor and type English letters — they convert to ' + languageLabel + ' instantly as you type.';
            } else if (isHindiWordMode) {
                hint.classList.remove('d-none');
                hint.innerHTML = '<strong>Hindi mode is ON.</strong> Use the toolbar to change font, size, color, highlight, and alignment — like Microsoft Word. Type with your Hindi keyboard or paste Hindi text.';
            } else {
                hint.classList.add('d-none');
            }
        }

        if (!editor) {
            resetCommunityPhoneticComposition();
            return;
        }

        if (needsTransliteration) {
            attachCommunityEditorTransliteration(editor);
        } else {
            detachCommunityEditorTransliteration(editor);
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

        syncCommunityEditorTransliteration(language);

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
    const COMMUNITY_STRUCTURED_LOCATION_TYPES = ['news', 'reports', 'awareness', 'business', 'local-voices', 'my-area', 'community-issues', 'agriculture', 'environment'];
    const COMMUNITY_OPTIONAL_STRUCTURED_LOCATION_TYPES = ['womens-world', 'student-corner', 'youth-corner', 'senior-citizens-forum'];
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
        return COMMUNITY_STRUCTURED_LOCATION_TYPES.includes(contentType)
            || COMMUNITY_OPTIONAL_STRUCTURED_LOCATION_TYPES.includes(contentType);
    }

    function requiresStructuredCommunityLocation(contentType) {
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
        const womensWorldSlot = document.getElementById('communityWomensWorldLocationSlot');
        const studentCornerSlot = document.getElementById('communityStudentCornerLocationSlot');
        const youthCornerSlot = document.getElementById('communityYouthCornerLocationSlot');
        const localVoicesSlot = document.getElementById('communityLocalVoicesLocationSlot');
        const myAreaSlot = document.getElementById('communityMyAreaLocationSlot');
        const communityIssuesSlot = document.getElementById('communityCommunityIssuesLocationSlot');
        const agricultureSlot = document.getElementById('communityAgricultureLocationSlot');
        const environmentSlot = document.getElementById('communityEnvironmentLocationSlot');
        const seniorCitizensForumSlot = document.getElementById('communitySeniorCitizensForumLocationSlot');
        const commonLocationSlot = document.getElementById('communityCommonLocationSlot');

        if (!wrapper) {
            return;
        }

        const isNews = contentType === 'news';
        const isReport = contentType === 'reports';
        const isAwareness = contentType === 'awareness';
        const isBusiness = contentType === 'business';
        const isWomensWorld = contentType === 'womens-world';
        const isStudentCorner = contentType === 'student-corner';
        const isYouthCorner = contentType === 'youth-corner';
        const isLocalVoices = contentType === 'local-voices';
        const isMyArea = contentType === 'my-area';
        const isCommunityIssues = contentType === 'community-issues';
        const isAgriculture = contentType === 'agriculture';
        const isEnvironment = contentType === 'environment';
        const isSeniorCitizensForum = contentType === 'senior-citizens-forum';
        const usesStructured = usesStructuredCommunityLocation(contentType);
        const requiresStructured = requiresStructuredCommunityLocation(contentType);
        const usesOptionalStructuredLocation = COMMUNITY_OPTIONAL_STRUCTURED_LOCATION_TYPES.includes(contentType);
        let targetSlot = hiddenSlot;

        if (isNews) {
            targetSlot = newsSlot;
        } else if (isReport) {
            targetSlot = reportSlot;
        } else if (isAwareness) {
            targetSlot = awarenessSlot;
        } else if (isBusiness) {
            targetSlot = businessSlot;
        } else if (isWomensWorld) {
            targetSlot = womensWorldSlot;
        } else if (isStudentCorner) {
            targetSlot = studentCornerSlot;
        } else if (isYouthCorner) {
            targetSlot = youthCornerSlot;
        } else if (isLocalVoices) {
            targetSlot = localVoicesSlot;
        } else if (isMyArea) {
            targetSlot = myAreaSlot;
        } else if (isCommunityIssues) {
            targetSlot = communityIssuesSlot;
        } else if (isAgriculture) {
            targetSlot = agricultureSlot;
        } else if (isEnvironment) {
            targetSlot = environmentSlot;
        } else if (isSeniorCitizensForum) {
            targetSlot = seniorCitizensForumSlot;
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
            field.required = requiresStructured;
            field.disabled = !usesStructured;
        });

        ['communityLocationCountry', 'communityLocationState', 'communityLocationDistrict', 'communityLocationCity'].forEach((fieldId) => {
            const label = document.querySelector(`label[for="${fieldId}"]`);
            if (!label) {
                return;
            }

            let baseText = label.textContent.replace(/\s*\*$/, '').trim();
            if (fieldId === 'communityLocationCity' && (isSeniorCitizensForum || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment)) {
                baseText = 'City/Town/Village';
            }
            if (fieldId === 'communityLocationCity' && isAgriculture) {
                baseText = 'Village / Town';
            }

            label.innerHTML = requiresStructured
                ? `${baseText} <span class="text-danger">*</span>`
                : baseText;
        });

        const localityWrap = document.getElementById('communityLocationLocalityWrap');
        const mapWrap = document.getElementById('communityStructuredLocationMapWrap');
        if (localityWrap) {
            localityWrap.style.display = (usesOptionalStructuredLocation || isAgriculture) ? 'none' : '';
        }
        if (mapWrap) {
            mapWrap.style.display = (usesOptionalStructuredLocation || isAgriculture) ? 'none' : '';
        }

        const localityField = document.getElementById('communityLocationLocality');
        const localityLabel = document.getElementById('communityLocationLocalityLabel');

        if (localityField) {
            localityField.required = requiresStructured && (isAwareness || isBusiness || isLocalVoices || isMyArea || isCommunityIssues || isEnvironment);
            localityField.disabled = !usesStructured || usesOptionalStructuredLocation || isAgriculture;

            if (isAwareness || isBusiness || isLocalVoices || isMyArea || isCommunityIssues || isEnvironment) {
                localityField.classList.add('structured-location-required');
            } else {
                localityField.classList.remove('structured-location-required');
                localityField.required = false;
            }
        }

        if (localityLabel) {
            if (isLocalVoices || isMyArea || isCommunityIssues || isEnvironment) {
                localityLabel.innerHTML = 'Locality / Area <span class="text-danger">*</span>';
            } else {
                localityLabel.innerHTML = (isAwareness || isBusiness)
                    ? 'Area <span class="text-danger">*</span>'
                    : 'Locality';
            }
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

    function mountNewsParticipationFields(isNews, isAwareness, isBusiness, isWomensWorld, isStudentCorner, isYouthCorner, isLocalVoices, isMyArea) {
        const isCommunityIssues = document.getElementById('contentType')?.value === 'community-issues';
        const isAgriculture = document.getElementById('contentType')?.value === 'agriculture';
        const isEnvironment = document.getElementById('contentType')?.value === 'environment';
        const isScienceTechnology = document.getElementById('contentType')?.value === 'science-technology';
        const isAstroConsultancy = document.getElementById('contentType')?.value === 'astro-consultancy';
        const isReligionSpirituality = document.getElementById('contentType')?.value === 'religion-spirituality';
        const isCreativeCorner = document.getElementById('contentType')?.value === 'creative-corner';
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
        const womensWorldParticipationWrap = document.getElementById('womensWorldParticipationWrap');
        const womensWorldPollWrap = document.getElementById('womensWorldPollWrap');
        const womensWorldPollFields = document.getElementById('womensWorldPollFields');
        const studentCornerParticipationWrap = document.getElementById('studentCornerParticipationWrap');
        const studentCornerPollWrap = document.getElementById('studentCornerPollWrap');
        const studentCornerPollFields = document.getElementById('studentCornerPollFields');
        const youthCornerParticipationWrap = document.getElementById('youthCornerParticipationWrap');
        const youthCornerPollWrap = document.getElementById('youthCornerPollWrap');
        const youthCornerPollFields = document.getElementById('youthCornerPollFields');
        const localVoiceParticipationWrap = document.getElementById('localVoiceParticipationWrap');
        const localVoicePollWrap = document.getElementById('localVoicePollWrap');
        const localVoicePollFields = document.getElementById('localVoicePollFields');
        const myAreaParticipationWrap = document.getElementById('myAreaParticipationWrap');
        const myAreaPollWrap = document.getElementById('myAreaPollWrap');
        const myAreaPollFields = document.getElementById('myAreaPollFields');
        const communityIssueParticipationWrap = document.getElementById('communityIssueParticipationWrap');
        const communityIssuePollWrap = document.getElementById('communityIssuePollWrap');
        const communityIssuePollFields = document.getElementById('communityIssuePollFields');
        const agricultureParticipationWrap = document.getElementById('agricultureParticipationWrap');
        const agriculturePollWrap = document.getElementById('agriculturePollWrap');
        const agriculturePollFields = document.getElementById('agriculturePollFields');
        const environmentParticipationWrap = document.getElementById('environmentParticipationWrap');
        const environmentPollWrap = document.getElementById('environmentPollWrap');
        const environmentPollFields = document.getElementById('environmentPollFields');

        if (publicParticipationWrap) {
            publicParticipationWrap.style.display = (isNews || isAwareness || isBusiness || isWomensWorld || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner) ? 'none' : '';
            publicParticipationWrap.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = isNews || isAwareness || isBusiness || isWomensWorld || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner;
            });
        }

        if (newsParticipationWrap) {
            newsParticipationWrap.style.display = isNews ? '' : 'none';
        }

        if (allowSharingWrap) {
            allowSharingWrap.style.display = (isAwareness || isBusiness || isWomensWorld || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner) ? 'none' : '';
            allowSharingWrap.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = isAwareness || isBusiness || isWomensWorld || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner;
            });
        }

        if (allowPollWrap) {
            allowPollWrap.style.display = (isAwareness || isBusiness || isNews || isWomensWorld || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner) ? 'none' : '';
            allowPollWrap.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = isAwareness || isBusiness || isNews || isWomensWorld || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner;
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

        if (womensWorldParticipationWrap) {
            womensWorldParticipationWrap.style.display = isWomensWorld ? '' : 'none';
        }

        if (womensWorldPollWrap) {
            womensWorldPollWrap.style.display = isWomensWorld ? '' : 'none';
        }

        if (studentCornerParticipationWrap) {
            studentCornerParticipationWrap.style.display = isStudentCorner ? '' : 'none';
        }

        if (studentCornerPollWrap) {
            studentCornerPollWrap.style.display = isStudentCorner ? '' : 'none';
        }

        if (youthCornerParticipationWrap) {
            youthCornerParticipationWrap.style.display = isYouthCorner ? '' : 'none';
        }

        if (youthCornerPollWrap) {
            youthCornerPollWrap.style.display = isYouthCorner ? '' : 'none';
        }

        if (localVoiceParticipationWrap) {
            localVoiceParticipationWrap.style.display = isLocalVoices ? '' : 'none';
        }

        if (localVoicePollWrap) {
            localVoicePollWrap.style.display = isLocalVoices ? '' : 'none';
        }

        if (myAreaParticipationWrap) {
            myAreaParticipationWrap.style.display = isMyArea ? '' : 'none';
        }

        if (myAreaPollWrap) {
            myAreaPollWrap.style.display = isMyArea ? '' : 'none';
        }

        if (communityIssueParticipationWrap) {
            communityIssueParticipationWrap.style.display = isCommunityIssues ? '' : 'none';
        }

        if (communityIssuePollWrap) {
            communityIssuePollWrap.style.display = isCommunityIssues ? '' : 'none';
        }

        if (agricultureParticipationWrap) {
            agricultureParticipationWrap.style.display = isAgriculture ? '' : 'none';
        }

        if (agriculturePollWrap) {
            agriculturePollWrap.style.display = isAgriculture ? '' : 'none';
        }

        if (environmentParticipationWrap) {
            environmentParticipationWrap.style.display = isEnvironment ? '' : 'none';
        }

        if (environmentPollWrap) {
            environmentPollWrap.style.display = isEnvironment ? '' : 'none';
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

        const womensWorldAllowPoll = document.getElementById('womensWorldAllowPoll');
        if (womensWorldPollFields) {
            const womensWorldPollEnabled = isWomensWorld && womensWorldAllowPoll?.checked;
            womensWorldPollFields.style.display = womensWorldPollEnabled ? '' : 'none';
            const womensWorldPollQuestion = document.getElementById('womensWorldPollQuestion');
            if (womensWorldPollQuestion) {
                womensWorldPollQuestion.required = Boolean(womensWorldPollEnabled);
                womensWorldPollQuestion.disabled = !womensWorldPollEnabled;
            }
            const womensWorldPollOptions = document.getElementById('womensWorldPollOptions');
            if (womensWorldPollOptions) {
                womensWorldPollOptions.disabled = !womensWorldPollEnabled;
            }
        }

        const studentCornerAllowPoll = document.getElementById('studentCornerAllowPoll');
        if (studentCornerPollFields) {
            const studentCornerPollEnabled = isStudentCorner && studentCornerAllowPoll?.checked;
            studentCornerPollFields.style.display = studentCornerPollEnabled ? '' : 'none';
            const studentCornerPollQuestion = document.getElementById('studentCornerPollQuestion');
            if (studentCornerPollQuestion) {
                studentCornerPollQuestion.required = Boolean(studentCornerPollEnabled);
                studentCornerPollQuestion.disabled = !studentCornerPollEnabled;
            }
            const studentCornerPollOptions = document.getElementById('studentCornerPollOptions');
            if (studentCornerPollOptions) {
                studentCornerPollOptions.disabled = !studentCornerPollEnabled;
            }
        }

        const youthCornerAllowPoll = document.getElementById('youthCornerAllowPoll');
        if (youthCornerPollFields) {
            const youthCornerPollEnabled = isYouthCorner && youthCornerAllowPoll?.checked;
            youthCornerPollFields.style.display = youthCornerPollEnabled ? '' : 'none';
            const youthCornerPollQuestion = document.getElementById('youthCornerPollQuestion');
            if (youthCornerPollQuestion) {
                youthCornerPollQuestion.required = Boolean(youthCornerPollEnabled);
                youthCornerPollQuestion.disabled = !youthCornerPollEnabled;
            }
            const youthCornerPollOptions = document.getElementById('youthCornerPollOptions');
            if (youthCornerPollOptions) {
                youthCornerPollOptions.disabled = !youthCornerPollEnabled;
            }
        }

        const localVoiceAllowPoll = document.getElementById('localVoiceAllowPoll');
        if (localVoicePollFields) {
            const localVoicePollEnabled = isLocalVoices && localVoiceAllowPoll?.checked;
            localVoicePollFields.style.display = localVoicePollEnabled ? '' : 'none';
            const localVoicePollQuestion = document.getElementById('localVoicePollQuestion');
            if (localVoicePollQuestion) {
                localVoicePollQuestion.required = Boolean(localVoicePollEnabled);
                localVoicePollQuestion.disabled = !localVoicePollEnabled;
            }
            const localVoicePollOptions = document.getElementById('localVoicePollOptions');
            if (localVoicePollOptions) {
                localVoicePollOptions.disabled = !localVoicePollEnabled;
            }
        }

        const myAreaAllowPoll = document.getElementById('myAreaAllowPoll');
        if (myAreaPollFields) {
            const myAreaPollEnabled = isMyArea && myAreaAllowPoll?.checked;
            myAreaPollFields.style.display = myAreaPollEnabled ? '' : 'none';
            const myAreaPollQuestion = myAreaPollFields.querySelector('input[name="my_area_poll_question"]');
            if (myAreaPollQuestion) {
                myAreaPollQuestion.required = Boolean(myAreaPollEnabled);
                myAreaPollQuestion.disabled = !myAreaPollEnabled;
            }
            const myAreaPollOptions = myAreaPollFields.querySelector('textarea[name="my_area_poll_options"]');
            if (myAreaPollOptions) {
                myAreaPollOptions.disabled = !myAreaPollEnabled;
            }
        }

        const communityIssueAllowPoll = document.getElementById('communityIssueAllowPoll');
        if (communityIssuePollFields) {
            const communityIssuePollEnabled = isCommunityIssues && communityIssueAllowPoll?.checked;
            communityIssuePollFields.style.display = communityIssuePollEnabled ? '' : 'none';
            const communityIssuePollQuestion = document.getElementById('communityIssuePollQuestion');
            if (communityIssuePollQuestion) {
                communityIssuePollQuestion.required = Boolean(communityIssuePollEnabled);
                communityIssuePollQuestion.disabled = !communityIssuePollEnabled;
            }
            const communityIssuePollOptions = document.getElementById('communityIssuePollOptions');
            if (communityIssuePollOptions) {
                communityIssuePollOptions.disabled = !communityIssuePollEnabled;
            }
        }

        const agricultureAllowPoll = document.getElementById('agricultureAllowPoll');
        if (agriculturePollFields) {
            const agriculturePollEnabled = isAgriculture && agricultureAllowPoll?.checked;
            agriculturePollFields.style.display = agriculturePollEnabled ? '' : 'none';
            const agriculturePollQuestion = document.getElementById('agriculturePollQuestion');
            if (agriculturePollQuestion) {
                agriculturePollQuestion.required = Boolean(agriculturePollEnabled);
                agriculturePollQuestion.disabled = !agriculturePollEnabled;
            }
            const agriculturePollOptions = document.getElementById('agriculturePollOptions');
            if (agriculturePollOptions) {
                agriculturePollOptions.disabled = !agriculturePollEnabled;
            }
        }

        const environmentAllowPoll = document.getElementById('environmentAllowPoll');
        if (environmentPollFields) {
            const environmentPollEnabled = isEnvironment && environmentAllowPoll?.checked;
            environmentPollFields.style.display = environmentPollEnabled ? '' : 'none';
            const environmentPollQuestion = document.getElementById('environmentPollQuestion');
            if (environmentPollQuestion) {
                environmentPollQuestion.required = Boolean(environmentPollEnabled);
                environmentPollQuestion.disabled = !environmentPollEnabled;
            }
            const environmentPollOptions = document.getElementById('environmentPollOptions');
            if (environmentPollOptions) {
                environmentPollOptions.disabled = !environmentPollEnabled;
            }
        }

        const awarenessHasEvent = document.getElementById('awarenessHasEvent');
        const awarenessEventFields = document.getElementById('awarenessEventFields');
        if (awarenessEventFields) {
            awarenessEventFields.style.display = isAwareness && awarenessHasEvent?.checked ? '' : 'none';
        }
    }

    function mountNewsMediaFields(isNews, isStories, isChildrensCorner, isAwareness, isBusiness, isWomensWorld, isSeniorCitizensForum, isStudentCorner, isYouthCorner, isLocalVoices, isMyArea) {
        const isCommunityIssues = document.getElementById('contentType')?.value === 'community-issues';
        const isAgriculture = document.getElementById('contentType')?.value === 'agriculture';
        const isEnvironment = document.getElementById('contentType')?.value === 'environment';
        const isScienceTechnology = document.getElementById('contentType')?.value === 'science-technology';
        const isAstroConsultancy = document.getElementById('contentType')?.value === 'astro-consultancy';
        const isReligionSpirituality = document.getElementById('contentType')?.value === 'religion-spirituality';
        const isCreativeCorner = document.getElementById('contentType')?.value === 'creative-corner';
        const featuredWrap = document.getElementById('communityFeaturedImagesWrap');
        const featuredSlot = document.getElementById('communityNewsFeaturedImagesSlot');
        const storyFeaturedSlot = document.getElementById('communityStoryFeaturedImagesSlot');
        const awarenessFeaturedSlot = document.getElementById('communityAwarenessFeaturedImagesSlot');
        const businessFeaturedSlot = document.getElementById('communityBusinessFeaturedImagesSlot');
        const womensWorldFeaturedSlot = document.getElementById('communityWomensWorldFeaturedImagesSlot');
        const studentCornerFeaturedSlot = document.getElementById('communityStudentCornerFeaturedImagesSlot');
        const youthCornerFeaturedSlot = document.getElementById('communityYouthCornerFeaturedImagesSlot');
        const localVoicesFeaturedSlot = document.getElementById('communityLocalVoicesFeaturedImagesSlot');
        const myAreaFeaturedSlot = document.getElementById('communityMyAreaFeaturedImagesSlot');
        const communityIssuesFeaturedSlot = document.getElementById('communityCommunityIssuesFeaturedImagesSlot');
        const agricultureFeaturedSlot = document.getElementById('communityAgricultureFeaturedImagesSlot');
        const environmentFeaturedSlot = document.getElementById('communityEnvironmentFeaturedImagesSlot');
        const scienceTechnologyFeaturedSlot = document.getElementById('communityScienceTechnologyFeaturedImagesSlot');
        const astroConsultancyFeaturedSlot = document.getElementById('communityAstroConsultancyFeaturedImagesSlot');
        const religionSpiritualityFeaturedSlot = document.getElementById('communityReligionSpiritualityFeaturedImagesSlot');
        const creativeCornerFeaturedSlot = document.getElementById('communityCreativeCornerFeaturedImagesSlot');
        const businessTagsSlot = document.getElementById('communityBusinessTagsSlot');
        const womensWorldTagsSlot = document.getElementById('communityWomensWorldTagsSlot');
        const studentCornerTagsSlot = document.getElementById('communityStudentCornerTagsSlot');
        const youthCornerTagsSlot = document.getElementById('communityYouthCornerTagsSlot');
        const localVoicesTagsSlot = document.getElementById('communityLocalVoicesTagsSlot');
        const myAreaTagsSlot = document.getElementById('communityMyAreaTagsSlot');
        const communityIssuesTagsSlot = document.getElementById('communityCommunityIssuesTagsSlot');
        const agricultureTagsSlot = document.getElementById('communityAgricultureTagsSlot');
        const environmentTagsSlot = document.getElementById('communityEnvironmentTagsSlot');
        const scienceTechnologyTagsSlot = document.getElementById('communityScienceTechnologyTagsSlot');
        const astroConsultancyTagsSlot = document.getElementById('communityAstroConsultancyTagsSlot');
        const religionSpiritualityTagsSlot = document.getElementById('communityReligionSpiritualityTagsSlot');
        const creativeCornerTagsSlot = document.getElementById('communityCreativeCornerTagsSlot');
        const tagsCol = document.getElementById('communityTagsWrap');
        const videoWrap = document.getElementById('communityVideoWrap');
        const videoSlot = document.getElementById('communityNewsVideoSlot');
        const storyVideoSlot = document.getElementById('communityStoryVideoSlot');
        const awarenessVideoSlot = document.getElementById('communityAwarenessVideoSlot');
        const businessVideoSlot = document.getElementById('communityBusinessVideoSlot');
        const womensWorldVideoSlot = document.getElementById('communityWomensWorldVideoSlot');
        const seniorCitizensForumVideoSlot = document.getElementById('communitySeniorCitizensForumVideoSlot');
        const studentCornerVideoSlot = document.getElementById('communityStudentCornerVideoSlot');
        const youthCornerVideoSlot = document.getElementById('communityYouthCornerVideoSlot');
        const localVoicesVideoSlot = document.getElementById('communityLocalVoicesVideoSlot');
        const myAreaVideoSlot = document.getElementById('communityMyAreaVideoSlot');
        const communityIssuesVideoSlot = document.getElementById('communityCommunityIssuesVideoSlot');
        const agricultureVideoSlot = document.getElementById('communityAgricultureVideoSlot');
        const environmentVideoSlot = document.getElementById('communityEnvironmentVideoSlot');
        const scienceTechnologyVideoSlot = document.getElementById('communityScienceTechnologyVideoSlot');
        const astroConsultancyVideoSlot = document.getElementById('communityAstroConsultancyVideoSlot');
        const religionSpiritualityVideoSlot = document.getElementById('communityReligionSpiritualityVideoSlot');
        const creativeCornerVideoSlot = document.getElementById('communityCreativeCornerVideoSlot');
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
            window.communityFeaturedImages.max = (isAwareness || isBusiness || isWomensWorld || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner) ? 1 : 5;
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
                } else if (isWomensWorld && womensWorldFeaturedSlot) {
                    womensWorldFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isStudentCorner && studentCornerFeaturedSlot) {
                    studentCornerFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isYouthCorner && youthCornerFeaturedSlot) {
                    youthCornerFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isLocalVoices && localVoicesFeaturedSlot) {
                    localVoicesFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isMyArea && myAreaFeaturedSlot) {
                    myAreaFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isCommunityIssues && communityIssuesFeaturedSlot) {
                    communityIssuesFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isAgriculture && agricultureFeaturedSlot) {
                    agricultureFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isEnvironment && environmentFeaturedSlot) {
                    environmentFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isScienceTechnology && scienceTechnologyFeaturedSlot) {
                    scienceTechnologyFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isAstroConsultancy && astroConsultancyFeaturedSlot) {
                    astroConsultancyFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isReligionSpirituality && religionSpiritualityFeaturedSlot) {
                    religionSpiritualityFeaturedSlot.appendChild(featuredWrap);
                    featuredWrap.classList.remove('col-md-6');
                } else if (isCreativeCorner && creativeCornerFeaturedSlot) {
                    creativeCornerFeaturedSlot.appendChild(featuredWrap);
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
                } else if (isWomensWorld && womensWorldTagsSlot) {
                    womensWorldTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isStudentCorner && studentCornerTagsSlot) {
                    studentCornerTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isYouthCorner && youthCornerTagsSlot) {
                    youthCornerTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isLocalVoices && localVoicesTagsSlot) {
                    localVoicesTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isMyArea && myAreaTagsSlot) {
                    myAreaTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isCommunityIssues && communityIssuesTagsSlot) {
                    communityIssuesTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isAgriculture && agricultureTagsSlot) {
                    agricultureTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isEnvironment && environmentTagsSlot) {
                    environmentTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isScienceTechnology && scienceTechnologyTagsSlot) {
                    scienceTechnologyTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isAstroConsultancy && astroConsultancyTagsSlot) {
                    astroConsultancyTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isReligionSpirituality && religionSpiritualityTagsSlot) {
                    religionSpiritualityTagsSlot.appendChild(tagsCol);
                    tagsCol.classList.remove('col-md-6');
                    tagsCol.style.display = '';
                } else if (isCreativeCorner && creativeCornerTagsSlot) {
                    creativeCornerTagsSlot.appendChild(tagsCol);
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
            } else if (isWomensWorld && womensWorldVideoSlot) {
                womensWorldVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isStudentCorner && studentCornerVideoSlot) {
                studentCornerVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isYouthCorner && youthCornerVideoSlot) {
                youthCornerVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isLocalVoices && localVoicesVideoSlot) {
                localVoicesVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isMyArea && myAreaVideoSlot) {
                myAreaVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isCommunityIssues && communityIssuesVideoSlot) {
                communityIssuesVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isAgriculture && agricultureVideoSlot) {
                agricultureVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isEnvironment && environmentVideoSlot) {
                environmentVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isScienceTechnology && scienceTechnologyVideoSlot) {
                scienceTechnologyVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isAstroConsultancy && astroConsultancyVideoSlot) {
                astroConsultancyVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isReligionSpirituality && religionSpiritualityVideoSlot) {
                religionSpiritualityVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isCreativeCorner && creativeCornerVideoSlot) {
                creativeCornerVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (isSeniorCitizensForum && seniorCitizensForumVideoSlot) {
                seniorCitizensForumVideoSlot.appendChild(videoWrap);
                videoWrap.classList.remove('col-md-6');
            } else if (! isAwareness && ! isBusiness && ! isWomensWorld && ! isStudentCorner && ! isYouthCorner && ! isLocalVoices && ! isMyArea && ! isCommunityIssues && ! isAgriculture && ! isEnvironment && ! isScienceTechnology && ! isAstroConsultancy && ! isReligionSpirituality && ! isCreativeCorner && ! isSeniorCitizensForum) {
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
            } else if (isWomensWorld) {
                videoFieldLabel.innerHTML = 'Video upload / link <span class="text-muted fw-normal">(optional)</span>';
            } else if (isStudentCorner) {
                videoFieldLabel.innerHTML = 'Video upload / link <span class="text-muted fw-normal">(optional)</span>';
            } else if (isYouthCorner) {
                videoFieldLabel.innerHTML = 'Video upload / link <span class="text-muted fw-normal">(optional)</span>';
            } else if (isLocalVoices) {
                videoFieldLabel.innerHTML = 'Video evidence <span class="text-muted fw-normal">(optional)</span>';
            } else if (isMyArea) {
                videoFieldLabel.innerHTML = 'Video evidence <span class="text-muted fw-normal">(optional)</span>';
            } else if (isCommunityIssues) {
                videoFieldLabel.innerHTML = 'Video evidence <span class="text-muted fw-normal">(optional)</span>';
            } else if (isAgriculture) {
                videoFieldLabel.innerHTML = 'Farm video <span class="text-muted fw-normal">(highly recommended)</span>';
            } else if (isEnvironment) {
                videoFieldLabel.innerHTML = 'Environment video <span class="text-muted fw-normal">(recommended)</span>';
            } else if (isSeniorCitizensForum) {
                videoFieldLabel.innerHTML = 'Video upload / link <span class="text-muted fw-normal">(optional)</span>';
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
            } else if (isWomensWorld) {
                featuredLabel.innerHTML = 'Cover image <span class="text-muted fw-normal">(recommended)</span>';
            } else if (isStudentCorner) {
                featuredLabel.innerHTML = 'Cover image <span class="text-muted fw-normal">(recommended)</span>';
            } else if (isYouthCorner) {
                featuredLabel.innerHTML = 'Cover image <span class="text-muted fw-normal">(recommended)</span>';
            } else if (isLocalVoices || isMyArea || isCommunityIssues) {
                featuredLabel.innerHTML = 'Featured image <span class="text-muted fw-normal">(recommended)</span>';
            } else if (isEnvironment) {
                featuredLabel.innerHTML = 'Featured image <span class="text-danger">*</span> <span class="text-muted fw-normal">(required for most posts)</span>';
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
            } else if (isWomensWorld) {
                featuredHelp.textContent = 'Used for homepage cards, category listings, and social sharing. JPG, PNG, or WebP, max 4 MB.';
            } else if (isStudentCorner) {
                featuredHelp.textContent = 'Used for homepage cards, category listings, and social sharing. JPG, PNG, or WebP, max 4 MB.';
            } else if (isYouthCorner) {
                featuredHelp.textContent = 'Used for homepage cards, category listings, and social sharing. JPG, PNG, or WebP, max 4 MB.';
            } else if (isEnvironment) {
                featuredHelp.textContent = 'Required for most environment posts. Used in listings, Green Map, and social sharing. JPG, PNG, or WebP, max 4 MB.';
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
            } else if (isWomensWorld) {
                featuredAddBtn.innerHTML = '<i class="fa-solid fa-image me-1"></i>Add cover image';
            } else if (isStudentCorner) {
                featuredAddBtn.innerHTML = '<i class="fa-solid fa-image me-1"></i>Add cover image';
            } else if (isYouthCorner) {
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
        const hasTypeSection = Boolean(window.communityTypes[selectedType]) && !['news', 'reports', 'stories', 'poetry', 'biography', 'autobiography', 'childrens-corner', 'awareness', 'business', 'womens-world', 'senior-citizens-forum', 'student-corner', 'youth-corner', 'local-voices', 'my-area', 'community-issues', 'agriculture', 'environment', 'science-technology', 'astro-consultancy', 'religion-spirituality', 'creative-corner', 'competitions'].includes(selectedType);

        syncContentTypePicker(selectedType);

        if (!selectedType) {
            document.querySelectorAll('#communityPostDetails input, #communityPostDetails select, #communityPostDetails textarea').forEach((field) => {
                field.required = false;
            });
        } else {
            const titleInput = document.querySelector('#communityPostDetails input[name="title"]');
            if (titleInput) {
                titleInput.required = true;
            }
            const statusSelect = document.getElementById('communityPostStatus');
            if (statusSelect) {
                statusSelect.required = true;
            }
            const acceptContent = document.getElementById('acceptContentResponsibility');
            if (acceptContent) {
                acceptContent.required = true;
            }
            const acceptIndemnity = document.getElementById('acceptOriginalWorkIndemnity');
            if (acceptIndemnity) {
                acceptIndemnity.required = true;
            }
        }

        const isReport = selectedType === 'reports';
        const isNews = selectedType === 'news';

        categorySelect.innerHTML = '<option value="">Select category</option>';
        help.textContent = type ? type.description : '';

        const categoryLabel = document.getElementById('categoryLabel');
        const categoryHelp = document.getElementById('categoryHelp');
        const subCategoryWrap = document.getElementById('subCategoryFieldWrap');
        const subCategoryHelp = document.getElementById('subCategoryHelp');

        categorySelect.required = Boolean(selectedType);
        categorySelect.disabled = !selectedType;
        categoryWrap.style.display = selectedType ? '' : 'none';

        const isStories = selectedType === 'stories';
        const isPoetry = selectedType === 'poetry';
        const isLifeStory = isLifeStoryContentType(selectedType);
        const isAutobiography = selectedType === 'autobiography';
        const isChildrensCorner = selectedType === 'childrens-corner';
        const isAwareness = selectedType === 'awareness';
        const isBusiness = selectedType === 'business';
        const isWomensWorld = selectedType === 'womens-world';
        const isSeniorCitizensForum = selectedType === 'senior-citizens-forum';
        const isStudentCorner = selectedType === 'student-corner';
        const isYouthCorner = selectedType === 'youth-corner';
        const isLocalVoices = selectedType === 'local-voices';
        const isMyArea = selectedType === 'my-area';
        const isCommunityIssues = selectedType === 'community-issues';
        const isAgriculture = selectedType === 'agriculture';
        const isEnvironment = selectedType === 'environment';
        const isScienceTechnology = selectedType === 'science-technology';
        const isAstroConsultancy = selectedType === 'astro-consultancy';
        const isReligionSpirituality = selectedType === 'religion-spirituality';
        const isCreativeCorner = selectedType === 'creative-corner';
        const isCompetitions = selectedType === 'competitions';

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
        } else if (isWomensWorld) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncWomensWorldCategory();
        } else if (isSeniorCitizensForum) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncSeniorCitizensForumCategory();
        } else if (isStudentCorner) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncStudentCornerCategory();
        } else if (isYouthCorner) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncYouthCornerCategory();
        } else if (isLocalVoices) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncLocalVoicesCategory();
        } else if (isMyArea) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncMyAreaCategory();
        } else if (isCommunityIssues) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncCommunityIssuesCategory();
        } else if (isAgriculture) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncAgricultureCategory();
        } else if (isEnvironment) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncEnvironmentCategory();
        } else if (isScienceTechnology) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncScienceTechnologyCategory();
        } else if (isAstroConsultancy) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncAstroConsultancyCategory();
        } else if (isReligionSpirituality) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncReligionSpiritualityCategory();
        } else if (isCreativeCorner) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncCreativeCornerCategory();
        } else if (isCompetitions) {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
            syncCompetitionsCategory();
        } else if (selectedType) {
            categoryWrap.style.display = '';
            categorySelect.disabled = false;
            categorySelect.required = true;
        } else {
            categoryWrap.style.display = 'none';
            categorySelect.required = false;
            categorySelect.disabled = true;
        }

        if (categoryLabel) {
            categoryLabel.innerHTML = (isStories || isReport || isPoetry)
                ? 'Main Category <span class="text-danger">*</span>'
                : 'Category <span class="text-danger">*</span>';
        }

        if (categoryHelp) {
            categoryHelp.innerHTML = '';
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
            field.style.display = contentTypeMatchesDataset(selectedType, field.dataset.for) ? 'block' : 'none';
        });

        document.querySelectorAll('.general-extra').forEach((field) => {
            field.style.display = (isNews || isReport || hasTypeSection || isPoetry || isLifeStory || isChildrensCorner || isAwareness || isBusiness || isWomensWorld || isSeniorCitizensForum || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner || isCompetitions) ? 'none' : '';
        });

        document.querySelectorAll('.general-extra input, .general-extra textarea, .general-extra select').forEach((field) => {
            field.disabled = isNews || isReport || hasTypeSection || isPoetry || isLifeStory || isChildrensCorner || isAwareness || isBusiness || isWomensWorld || isSeniorCitizensForum || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner || isCompetitions;
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

        document.querySelectorAll('.womens-world-required').forEach((field) => {
            field.required = isWomensWorld;
        });

        document.querySelectorAll('.senior-citizens-forum-required').forEach((field) => {
            field.required = isSeniorCitizensForum;
        });

        document.querySelectorAll('.student-corner-required').forEach((field) => {
            field.required = isStudentCorner;
        });

        document.querySelectorAll('.youth-corner-required').forEach((field) => {
            field.required = isYouthCorner;
        });

        document.querySelectorAll('.local-voices-required').forEach((field) => {
            field.required = isLocalVoices;
        });

        document.querySelectorAll('.my-area-required').forEach((field) => {
            field.required = isMyArea;
        });

        document.querySelectorAll('.community-issues-required').forEach((field) => {
            field.required = isCommunityIssues;
        });

        document.querySelectorAll('.agriculture-required').forEach((field) => {
            field.required = isAgriculture;
        });

        document.querySelectorAll('.environment-required').forEach((field) => {
            field.required = isEnvironment;
        });

        document.querySelectorAll('.astro-consultancy-required').forEach((field) => {
            field.required = isAstroConsultancy;
        });

        document.querySelectorAll('.religion-spirituality-required').forEach((field) => {
            field.required = isReligionSpirituality;
        });

        document.querySelectorAll('.creative-corner-required').forEach((field) => {
            field.required = isCreativeCorner;
        });

        document.querySelectorAll('.competitions-required').forEach((field) => {
            field.required = isCompetitions;
        });

        document.querySelectorAll('.report-required').forEach((field) => {
            field.required = isReport;
        });

        document.querySelectorAll('.awareness-flow input, .awareness-flow textarea, .awareness-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isAwareness;
        });

        document.querySelectorAll('.business-flow input, .business-flow textarea, .business-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isBusiness;
        });

        document.querySelectorAll('.womens-world-flow input, .womens-world-flow textarea, .womens-world-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isWomensWorld;
        });

        document.querySelectorAll('.senior-citizens-forum-flow input, .senior-citizens-forum-flow textarea, .senior-citizens-forum-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isSeniorCitizensForum;
        });

        document.querySelectorAll('.student-corner-flow input, .student-corner-flow textarea, .student-corner-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isStudentCorner;
        });

        document.querySelectorAll('.youth-corner-flow input, .youth-corner-flow textarea, .youth-corner-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isYouthCorner;
        });

        document.querySelectorAll('.local-voices-flow input, .local-voices-flow textarea, .local-voices-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isLocalVoices;
        });

        document.querySelectorAll('.my-area-flow input, .my-area-flow textarea, .my-area-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isMyArea;
        });

        document.querySelectorAll('.community-issues-flow input, .community-issues-flow textarea, .community-issues-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isCommunityIssues;
        });

        document.querySelectorAll('.agriculture-flow input, .agriculture-flow textarea, .agriculture-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isAgriculture;
        });

        document.querySelectorAll('.environment-flow input, .environment-flow textarea, .environment-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isEnvironment;
        });

        document.querySelectorAll('.science-technology-flow input, .science-technology-flow textarea, .science-technology-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isScienceTechnology;
        });

        document.querySelectorAll('.astro-consultancy-flow input, .astro-consultancy-flow textarea, .astro-consultancy-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isAstroConsultancy;
        });

        document.querySelectorAll('.religion-spirituality-flow input, .religion-spirituality-flow textarea, .religion-spirituality-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isReligionSpirituality;
        });

        document.querySelectorAll('.creative-corner-flow input, .creative-corner-flow textarea, .creative-corner-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isCreativeCorner;
        });

        document.querySelectorAll('.competitions-flow input, .competitions-flow textarea, .competitions-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isCompetitions;
        });

        const childSchoolName = document.getElementById('childSchoolName');
        const schoolPrivacySelected = document.querySelector('input[name="childrens_corner_privacy_setting"][value="school_community"]')?.checked;
        if (childSchoolName) {
            childSchoolName.required = isChildrensCorner && Boolean(schoolPrivacySelected);
        }

        refreshLifeTimelineRequiredState(isLifeStory);

        document.querySelectorAll('.autobiography-flow input, .autobiography-flow textarea, .autobiography-flow select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
                return;
            }

            field.disabled = !isLifeStory;
        });

        document.querySelectorAll('.life-story-flow-section input, .life-story-flow-section textarea, .life-story-flow-section select').forEach((field) => {
            if (isCommunitySharedFormField(field)) {
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

        // Tags are relocated into type-flow slots; keep the input enabled after those
        // flows toggle disabled on nested inputs.
        const tagInputEl = document.getElementById('tagInput');
        if (tagInputEl) {
            const tagCount = (document.getElementById('tagsHidden')?.value || '')
                .split(',')
                .map((tag) => tag.trim())
                .filter(Boolean)
                .length;
            tagInputEl.disabled = tagCount >= 10;
        }

        document.querySelectorAll('.type-field-required').forEach((field) => {
            const section = field.closest('.type-fields-flow');
            field.required = Boolean(section && section.dataset.for === selectedType && isCommunityFormSectionVisible(section));
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
        } : (isWomensWorld ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize your story, advice, or topic in one or two sentences.',
            excerptHelp: 'A concise standfirst shown in Women\'s World listings.',
            bodyLabel: 'Content <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for Background, Challenge, Experience, Lessons Learned, Advice to Others, and Conclusion.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select a Google Places suggestion so latitude and longitude are saved.',
        } : (isSeniorCitizensForum ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize your experience, advice, or story in one or two sentences.',
            excerptHelp: 'A concise standfirst shown in Senior Citizens Forum listings.',
            bodyLabel: 'Rich text editor <span class="text-danger">*</span>',
            bodyHelp: 'Large font mode is enabled. Use the editor for Background, Experience, Lessons Learned, Advice, and Conclusion.',
            locationLabel: 'Location',
            locationHelp: 'Optional — enter country, state, district, and city/village for local heritage and community stories.',
        } : (isStudentCorner ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize your topic, project, or learning in one or two sentences.',
            excerptHelp: 'A concise standfirst shown in Student Corner listings.',
            bodyLabel: 'Rich text editor <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for Introduction, Objective, Main Content, Learnings, Tips / Recommendations, and Conclusion.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select a Google Places suggestion so latitude and longitude are saved.',
        } : (isYouthCorner ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize your experience, opportunity, or insight in one or two sentences.',
            excerptHelp: 'A concise standfirst shown in Youth Corner listings.',
            bodyLabel: 'Rich text editor <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for Problem/Challenge, Experience/Story, Actions Taken, Results, Lessons Learned, and Advice for Others.',
            locationLabel: 'Location',
            locationHelp: 'Optional — enter country, state, district, and city for local opportunities and regional youth stories.',
        } : (isMyArea ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize your local issue, achievement, or call for neighbours.',
            excerptHelp: 'A concise standfirst shown in My Area listings.',
            bodyLabel: 'Rich text editor <span class="text-danger">*</span>',
            bodyHelp: 'Share full details for your neighbours — what happened, who is affected, and what should happen next.',
            locationLabel: 'Location',
            locationHelp: 'Enter country, state, district, city/town/village, and locality / area.',
        } : (isLocalVoices ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize your local issue, opinion, or story in one or two sentences.',
            excerptHelp: 'A concise standfirst shown in Local Voices listings.',
            bodyLabel: 'Rich text editor <span class="text-danger">*</span>',
            bodyHelp: 'Use the toolbar for text, images, videos, documents, links, quotes, and polls.',
            locationLabel: 'Location',
            locationHelp: 'Enter country, state, district, city/town/village, and locality / area.',
        } : (isCommunityIssues ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize the issue, who is affected, and what action is needed.',
            excerptHelp: 'A concise standfirst shown in Community Issues listings.',
            bodyLabel: 'Issue description <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for What is the Issue?, When Did It Start?, Who Is Affected?, What Is the Impact?, What Action Has Been Taken So Far?, and Suggested Solution.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select a Google Places suggestion so latitude and longitude are saved.',
        } : (isEnvironment ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize the environmental topic, location, and key message.',
            excerptHelp: 'A concise standfirst shown in Environment listings.',
            bodyLabel: 'Content <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for Background, Current Situation, Environmental Impact, Actions Taken, Results, and Future Recommendations.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Enter country, state, district, city/town/village, and locality. Use the map pin for exact GPS coordinates.',
        } : (isAstroConsultancy ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize the guidance topic, tradition, and key message.',
            excerptHelp: 'A concise standfirst shown in Astro Consultancy listings.',
            bodyLabel: 'Content <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for Introduction, Traditional Background, Astrological Concept, Interpretation, Suggested Practices, and Conclusion.',
            locationLabel: 'Location',
            locationHelp: 'Optional — add a location if the guidance is region-specific.',
        } : (isReligionSpirituality ? {
            excerptLabel: 'Summary',
            excerptPlaceholder: 'Summarize the spiritual topic, tradition, and key message.',
            excerptHelp: 'A concise standfirst shown in Religion & Spirituality listings.',
            bodyLabel: 'Content <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for Introduction, Historical Background, Teachings, Practical Relevance, Conclusion, and References.',
            locationLabel: 'Location',
            locationHelp: 'Optional — use the location section below for pilgrimage or place of worship posts.',
        } : (isCompetitions ? {
            excerptLabel: 'Competition summary',
            excerptPlaceholder: 'Summarize the competition, theme, and who should participate.',
            excerptHelp: 'A concise standfirst shown in competition listings.',
            bodyLabel: 'Competition details <span class="text-danger">*</span>',
            bodyHelp: 'Use the rich text editor for Objective, Theme, Who Can Participate, Submission Requirements, Judging Criteria, Prizes, and Important Dates.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select a Google Places suggestion so latitude and longitude are saved.',
        } : {
            excerptLabel: 'Short excerpt',
            excerptPlaceholder: '',
            excerptHelp: 'A concise teaser shown in listing cards.',
            bodyLabel: 'Body <span class="text-danger">*</span>',
            bodyHelp: 'Add text and images together. Select an image to resize or align it.',
            locationLabel: 'Location <span class="text-danger">*</span>',
            locationHelp: 'Select a Google Places suggestion so latitude and longitude are saved.',
        })))))))))))))));

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

        const womensWorldContentGuide = document.getElementById('womensWorldContentGuide');
        if (womensWorldContentGuide) {
            womensWorldContentGuide.style.display = isWomensWorld ? '' : 'none';
        }

        const seniorCitizensForumContentGuide = document.getElementById('seniorCitizensForumContentGuide');
        if (seniorCitizensForumContentGuide) {
            seniorCitizensForumContentGuide.style.display = isSeniorCitizensForum ? '' : 'none';
        }

        const studentCornerContentGuide = document.getElementById('studentCornerContentGuide');
        if (studentCornerContentGuide) {
            studentCornerContentGuide.style.display = isStudentCorner ? '' : 'none';
        }

        const youthCornerContentGuide = document.getElementById('youthCornerContentGuide');
        if (youthCornerContentGuide) {
            youthCornerContentGuide.style.display = isYouthCorner ? '' : 'none';
        }

        const localVoicesContentGuide = document.getElementById('localVoicesContentGuide');
        if (localVoicesContentGuide) {
            localVoicesContentGuide.style.display = isLocalVoices ? '' : 'none';
        }

        const myAreaContentGuide = document.getElementById('myAreaContentGuide');
        if (myAreaContentGuide) {
            myAreaContentGuide.style.display = isMyArea ? '' : 'none';
        }

        const communityIssuesContentGuide = document.getElementById('communityIssuesContentGuide');
        if (communityIssuesContentGuide) {
            communityIssuesContentGuide.style.display = isCommunityIssues ? '' : 'none';
        }

        const agricultureContentGuide = document.getElementById('agricultureContentGuide');
        if (agricultureContentGuide) {
            agricultureContentGuide.style.display = isAgriculture ? '' : 'none';
        }

        const environmentContentGuide = document.getElementById('environmentContentGuide');
        if (environmentContentGuide) {
            environmentContentGuide.style.display = isEnvironment ? '' : 'none';
        }

        const astroConsultancyContentGuide = document.getElementById('astroConsultancyContentGuide');
        if (astroConsultancyContentGuide) {
            astroConsultancyContentGuide.style.display = isAstroConsultancy ? '' : 'none';
        }

        const religionSpiritualityContentGuide = document.getElementById('religionSpiritualityContentGuide');
        if (religionSpiritualityContentGuide) {
            religionSpiritualityContentGuide.style.display = isReligionSpirituality ? '' : 'none';
        }

        const creativeCornerContentGuide = document.getElementById('creativeCornerContentGuide');
        if (creativeCornerContentGuide) {
            creativeCornerContentGuide.style.display = isCreativeCorner ? '' : 'none';
        }

        const competitionsContentGuide = document.getElementById('competitionsContentGuide');
        if (competitionsContentGuide) {
            competitionsContentGuide.style.display = isCompetitions ? '' : 'none';
        }

        refreshStudentCornerProjectSection();
        refreshYouthCornerProjectSection();
        refreshLocalVoicesConditionalSections();
        refreshMyAreaConditionalSections();
        refreshAgricultureConditionalSections();
        refreshAgricultureSoilParameters();
        refreshEnvironmentConditionalSections();
        refreshEnvironmentImpactCalculatorFields();
        refreshScienceTechnologyConditionalSections();
        refreshAstroConsultancyConditionalSections();
        refreshReligionSpiritualityConditionalSections();
        refreshReligionSpiritualityUniqueFeatures();
        refreshCreativeCornerConditionalSections();
        refreshCompetitionsConditionalSections();
        refreshCompetitionsUniqueFeatures();

        refreshPoetryEditorMode(selectedType);
        refreshPoetrySeriesFields();
        refreshChildrensCornerContentMode();

        mountStructuredLocationFields(selectedType);
        const commonLocationSlot = document.getElementById('communityCommonLocationSlot');
        if (commonLocationSlot) {
            commonLocationSlot.style.display = (isNews || isReport || isChildrensCorner || isAwareness || isBusiness || isWomensWorld || isSeniorCitizensForum || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment || isScienceTechnology || isAstroConsultancy || isReligionSpirituality || isCreativeCorner) ? 'none' : '';
            commonLocationSlot.querySelectorAll('input, select, textarea').forEach((field) => {
                if (isChildrensCorner) {
                    field.disabled = true;
                    field.required = false;
                } else if (!isNews && !isReport && !isAwareness && !isBusiness && !isWomensWorld && !isSeniorCitizensForum && !isStudentCorner && !isYouthCorner && !isLocalVoices && !isMyArea && !isCommunityIssues && !isAgriculture && !isEnvironment) {
                    field.disabled = false;
                }
            });
        }
        mountNewsMediaFields(isNews, isStories, isChildrensCorner, isAwareness, isBusiness, isWomensWorld, isSeniorCitizensForum, isStudentCorner, isYouthCorner, isLocalVoices, isMyArea);
        mountNewsParticipationFields(isNews, isAwareness, isBusiness, isWomensWorld, isStudentCorner, isYouthCorner, isLocalVoices, isMyArea);
        refreshCommunityLocationTypeOptions(isReport);
        refreshBookLayoutMode(selectedType);
        refreshCommunityLocationFields(fieldCopy.locationHelp);

        if (typeof refreshCommunityActionFields === 'function') {
            refreshCommunityActionFields();
        }

        const allowPollWrap = document.getElementById('allowPollWrap');
        const allowPoll = document.getElementById('allowPoll');
        if (allowPollWrap) {
            allowPollWrap.style.display = (isReport || isAwareness || isBusiness || isWomensWorld || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues || isAgriculture || isEnvironment) ? 'none' : '';
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

        if (typeof refreshPublishAsFields === 'function') {
            refreshPublishAsFields();
        }
        if (typeof refreshWomensWorldPrivacyFields === 'function') {
            refreshWomensWorldPrivacyFields();
        }
        if (typeof refreshCommunityIssuesPrivacyFields === 'function') {
            refreshCommunityIssuesPrivacyFields();
        }
        if (typeof refreshCommunityIssuesPriorReportFields === 'function') {
            refreshCommunityIssuesPriorReportFields();
        }
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
        const womensWorldPublishWrap = document.getElementById('womensWorldPublishAsWrap');
        const womensWorldPenNameWrap = document.getElementById('womensWorldPenNameWrap');
        const womensWorldPenNameInput = document.getElementById('womensWorldPenNameInput');
        const studentCornerPublishWrap = document.getElementById('studentCornerPublishAsWrap');
        const studentCornerPenNameWrap = document.getElementById('studentCornerPenNameWrap');
        const studentCornerPenNameInput = document.getElementById('studentCornerPenNameInput');
        const youthCornerPublishWrap = document.getElementById('youthCornerPublishAsWrap');
        const youthCornerPenNameWrap = document.getElementById('youthCornerPenNameWrap');
        const youthCornerPenNameInput = document.getElementById('youthCornerPenNameInput');
        const localVoicePublishWrap = document.getElementById('localVoicePublishAsWrap');
        const localVoicePenNameWrap = document.getElementById('localVoicePenNameWrap');
        const localVoicePenNameInput = document.getElementById('localVoicePenNameInput');
        const myAreaPublishWrap = document.getElementById('myAreaPublishAsWrap');
        const myAreaPenNameWrap = document.getElementById('myAreaPenNameWrap');
        const myAreaPenNameInput = document.getElementById('myAreaPenNameInput');
        const communityIssuePublishWrap = document.getElementById('communityIssuePublishAsWrap');
        const communityIssuePenNameWrap = document.getElementById('communityIssuePenNameWrap');
        const communityIssuePenNameInput = document.getElementById('communityIssuePenNameInput');
        const contentType = document.getElementById('contentType')?.value || '';
        const isWomensWorld = contentType === 'womens-world';
        const isStudentCorner = contentType === 'student-corner';
        const isYouthCorner = contentType === 'youth-corner';
        const isLocalVoices = contentType === 'local-voices';
        const isMyArea = contentType === 'my-area';
        const isCommunityIssues = contentType === 'community-issues';

        if (!statusSelect) {
            return;
        }

        const isPublishing = statusSelect.value === 'published';

        if (publishWrap) {
            publishWrap.style.display = (isPublishing && !isWomensWorld && !isStudentCorner && !isYouthCorner && !isLocalVoices && !isMyArea && !isCommunityIssues) ? '' : 'none';
        }

        if (womensWorldPublishWrap) {
            womensWorldPublishWrap.style.display = (isPublishing && isWomensWorld) ? '' : 'none';
        }

        if (studentCornerPublishWrap) {
            studentCornerPublishWrap.style.display = (isPublishing && isStudentCorner) ? '' : 'none';
        }

        if (youthCornerPublishWrap) {
            youthCornerPublishWrap.style.display = (isPublishing && isYouthCorner) ? '' : 'none';
        }

        if (localVoicePublishWrap) {
            localVoicePublishWrap.style.display = (isPublishing && isLocalVoices) ? '' : 'none';
        }

        if (myAreaPublishWrap) {
            myAreaPublishWrap.style.display = (isPublishing && isMyArea) ? '' : 'none';
        }

        if (communityIssuePublishWrap) {
            communityIssuePublishWrap.style.display = (isPublishing && isCommunityIssues) ? '' : 'none';
        }

        document.querySelectorAll('input[name="publish_as"]').forEach((input) => {
            const inWomensWorldFlow = Boolean(input.closest('#womensWorldPublishAsWrap'));
            const inStudentCornerFlow = Boolean(input.closest('#studentCornerPublishAsWrap'));
            const inYouthCornerFlow = Boolean(input.closest('#youthCornerPublishAsWrap'));
            const inLocalVoicesFlow = Boolean(input.closest('#localVoicePublishAsWrap'));
            const inMyAreaFlow = Boolean(input.closest('#myAreaPublishAsWrap'));
            const inCommunityIssuesFlow = Boolean(input.closest('#communityIssuePublishAsWrap'));
            const enabled = isPublishing && (
                (isWomensWorld && inWomensWorldFlow)
                || (isStudentCorner && inStudentCornerFlow)
                || (isYouthCorner && inYouthCornerFlow)
                || (isLocalVoices && inLocalVoicesFlow)
                || (isMyArea && inMyAreaFlow)
                || (isCommunityIssues && inCommunityIssuesFlow)
                || (!isWomensWorld && !isStudentCorner && !isYouthCorner && !isLocalVoices && !isMyArea && !isCommunityIssues && !inWomensWorldFlow && !inStudentCornerFlow && !inYouthCornerFlow && !inLocalVoicesFlow && !inMyAreaFlow && !inCommunityIssuesFlow)
            );
            input.required = enabled;
            input.disabled = !enabled;
        });

        const selectedPublishAs = document.querySelector('input[name="publish_as"]:checked:not(:disabled)')?.value || 'public_profile';
        const showPenName = isPublishing && selectedPublishAs === 'pen_name';

        if (penNameWrap) {
            penNameWrap.style.display = (showPenName && !isWomensWorld && !isStudentCorner && !isYouthCorner && !isLocalVoices && !isMyArea && !isCommunityIssues) ? '' : 'none';
        }

        if (womensWorldPenNameWrap) {
            womensWorldPenNameWrap.style.display = (showPenName && isWomensWorld) ? '' : 'none';
        }

        if (studentCornerPenNameWrap) {
            studentCornerPenNameWrap.style.display = (showPenName && isStudentCorner) ? '' : 'none';
        }

        if (youthCornerPenNameWrap) {
            youthCornerPenNameWrap.style.display = (showPenName && isYouthCorner) ? '' : 'none';
        }

        if (localVoicePenNameWrap) {
            localVoicePenNameWrap.style.display = (showPenName && isLocalVoices) ? '' : 'none';
        }

        if (myAreaPenNameWrap) {
            myAreaPenNameWrap.style.display = (showPenName && isMyArea) ? '' : 'none';
        }

        if (communityIssuePenNameWrap) {
            communityIssuePenNameWrap.style.display = (showPenName && isCommunityIssues) ? '' : 'none';
        }

        if (penNameInput) {
            penNameInput.required = showPenName && !isWomensWorld && !isStudentCorner && !isYouthCorner && !isLocalVoices && !isMyArea && !isCommunityIssues;
            penNameInput.disabled = !showPenName || isWomensWorld || isStudentCorner || isYouthCorner || isLocalVoices || isMyArea || isCommunityIssues;
        }

        if (womensWorldPenNameInput) {
            womensWorldPenNameInput.required = showPenName && isWomensWorld;
            womensWorldPenNameInput.disabled = !showPenName || !isWomensWorld;
        }

        if (studentCornerPenNameInput) {
            studentCornerPenNameInput.required = showPenName && isStudentCorner;
            studentCornerPenNameInput.disabled = !showPenName || !isStudentCorner;
        }

        if (youthCornerPenNameInput) {
            youthCornerPenNameInput.required = showPenName && isYouthCorner;
            youthCornerPenNameInput.disabled = !showPenName || !isYouthCorner;
        }

        if (localVoicePenNameInput) {
            localVoicePenNameInput.required = showPenName && isLocalVoices;
            localVoicePenNameInput.disabled = !showPenName || !isLocalVoices;
        }

        if (myAreaPenNameInput) {
            myAreaPenNameInput.required = showPenName && isMyArea;
            myAreaPenNameInput.disabled = !showPenName || !isMyArea;
        }

        if (communityIssuePenNameInput) {
            communityIssuePenNameInput.required = showPenName && isCommunityIssues;
            communityIssuePenNameInput.disabled = !showPenName || !isCommunityIssues;
        }
    }

    function refreshWomensWorldPrivacyFields() {
        const visibilitySelect = document.getElementById('womensWorldVisibility');
        const privateLinkInfo = document.getElementById('womensWorldPrivateLinkInfo');
        if (!visibilitySelect || !privateLinkInfo) {
            return;
        }

        privateLinkInfo.style.display = visibilitySelect.value === 'private_link' ? '' : 'none';
    }

    document.getElementById('womensWorldVisibility')?.addEventListener('change', refreshWomensWorldPrivacyFields);
    document.getElementById('womensWorldCopyPrivateLinkBtn')?.addEventListener('click', function () {
        const input = document.getElementById('womensWorldPrivateLinkUrl');
        if (!input?.value) {
            return;
        }
        navigator.clipboard?.writeText(input.value).then(function () {
            notify('success', 'Private link copied.');
        }).catch(function () {
            input.select();
            document.execCommand('copy');
            notify('success', 'Private link copied.');
        });
    });
    refreshWomensWorldPrivacyFields();

    function refreshSeniorCitizensForumPrivacyFields() {
        const visibilitySelect = document.getElementById('seniorCitizensForumVisibility');
        const privateLinkInfo = document.getElementById('seniorCitizensForumPrivateLinkInfo');
        if (!visibilitySelect || !privateLinkInfo) {
            return;
        }

        privateLinkInfo.style.display = visibilitySelect.value === 'private_link' ? '' : 'none';
    }

    document.getElementById('seniorCitizensForumVisibility')?.addEventListener('change', refreshSeniorCitizensForumPrivacyFields);
    document.getElementById('seniorCitizensForumCopyPrivateLinkBtn')?.addEventListener('click', function () {
        const input = document.getElementById('seniorCitizensForumPrivateLinkUrl');
        if (!input?.value) {
            return;
        }
        navigator.clipboard?.writeText(input.value).then(function () {
            notify('success', 'Private link copied.');
        }).catch(function () {
            input.select();
            document.execCommand('copy');
            notify('success', 'Private link copied.');
        });
    });
    refreshSeniorCitizensForumPrivacyFields();

    function refreshStudentCornerPrivacyFields() {
        const visibilitySelect = document.getElementById('studentCornerVisibility');
        const privateLinkInfo = document.getElementById('studentCornerPrivateLinkInfo');
        if (!visibilitySelect || !privateLinkInfo) {
            return;
        }

        privateLinkInfo.style.display = visibilitySelect.value === 'private_link' ? '' : 'none';
    }

    document.getElementById('studentCornerVisibility')?.addEventListener('change', refreshStudentCornerPrivacyFields);
    document.getElementById('studentCornerCopyPrivateLinkBtn')?.addEventListener('click', function () {
        const input = document.getElementById('studentCornerPrivateLinkUrl');
        if (!input?.value) {
            return;
        }
        navigator.clipboard?.writeText(input.value).then(function () {
            notify('success', 'Private link copied.');
        }).catch(function () {
            input.select();
            document.execCommand('copy');
            notify('success', 'Private link copied.');
        });
    });
    refreshStudentCornerPrivacyFields();

    function refreshYouthCornerPrivacyFields() {
        const visibilitySelect = document.getElementById('youthCornerVisibility');
        const privateLinkInfo = document.getElementById('youthCornerPrivateLinkInfo');
        if (!visibilitySelect || !privateLinkInfo) {
            return;
        }

        privateLinkInfo.style.display = visibilitySelect.value === 'private_link' ? '' : 'none';
    }

    document.getElementById('youthCornerVisibility')?.addEventListener('change', refreshYouthCornerPrivacyFields);
    document.getElementById('youthCornerCopyPrivateLinkBtn')?.addEventListener('click', function () {
        const input = document.getElementById('youthCornerPrivateLinkUrl');
        if (!input?.value) {
            return;
        }
        navigator.clipboard?.writeText(input.value).then(function () {
            notify('success', 'Private link copied.');
        }).catch(function () {
            input.select();
            document.execCommand('copy');
            notify('success', 'Private link copied.');
        });
    });
    refreshYouthCornerPrivacyFields();

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

    function refreshTypeParticipationFields() {
        const contentType = document.getElementById('contentType')?.value || '';
        mountNewsParticipationFields(
            contentType === 'news',
            contentType === 'awareness',
            contentType === 'business',
            contentType === 'womens-world',
            contentType === 'student-corner',
            contentType === 'youth-corner',
            contentType === 'local-voices',
            contentType === 'my-area'
        );
    }

    document.getElementById('awarenessAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);
    document.getElementById('awarenessHasEvent')?.addEventListener('change', refreshTypeParticipationFields);
    document.getElementById('businessAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);
    document.getElementById('womensWorldAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);
    document.getElementById('studentCornerAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);
    document.getElementById('youthCornerAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);
    document.getElementById('localVoiceAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);
    document.getElementById('myAreaAllowPoll')?.addEventListener('change', refreshTypeParticipationFields);

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
        const fileRepository = editor.plugins.get('FileRepository');
        if (!fileRepository) {
            return;
        }

        fileRepository.createUploadAdapter = (loader) => new CommunityUploadAdapter(loader);
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

            const applySelection = (writer) => {
                let targetParagraph = imageNode.nextSibling;

                if (!targetParagraph || !targetParagraph.is('element', 'paragraph')) {
                    targetParagraph = writer.createElement('paragraph');
                    writer.insert(targetParagraph, writer.createPositionAfter(imageNode));
                }

                writer.setSelection(targetParagraph, 'in');
            };

            if (typeof editor.model.enqueueChange === 'function') {
                editor.model.enqueueChange({ isUndoable: false }, applySelection);
            } else {
                editor.model.change(applySelection);
            }

            locking = false;
        });
    }

    function escapeCommunityEditorHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
        });
    }

    function insertCommunityEditorHtml(editor, html) {
        editor.model.change(() => {
            const viewFragment = editor.data.processor.toView(html);
            const modelFragment = editor.data.toModel(viewFragment);
            editor.model.insertContent(modelFragment);
        });
    }

    function uploadCommunityEditorAttachment(file) {
        const data = new FormData();
        data.append('upload', file);

        return fetch('{{ route('community.posts.uploads.attachment') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: data,
        }).then(async (response) => {
            const payload = await response.json();
            if (!response.ok || !payload.url) {
                throw new Error(payload.message || 'Unable to upload file.');
            }

            return payload;
        });
    }

    function createCommunityEditorFileButtonPlugin(pluginName, buttonName, label, accept, onFile) {
        const CK = window.CKEDITOR || {};
        const PluginBase = CK.Plugin;
        const ButtonView = CK.ButtonView;

        if (typeof PluginBase !== 'function' || typeof ButtonView !== 'function') {
            return null;
        }

        return class extends PluginBase {
            static get pluginName() {
                return pluginName;
            }

            init() {
                const editor = this.editor;

                editor.ui.componentFactory.add(buttonName, (locale) => {
                    const button = new ButtonView(locale);
                    button.set({
                        label,
                        withText: true,
                        tooltip: true,
                    });

                    button.on('execute', () => {
                        const input = document.createElement('input');
                        input.type = 'file';
                        input.accept = accept;
                        input.style.display = 'none';
                        document.body.appendChild(input);

                        input.addEventListener('change', () => {
                            const file = input.files?.[0];
                            input.remove();

                            if (!file) {
                                return;
                            }

                            onFile(editor, file).catch((error) => {
                                console.error(error);
                                notify('error', error.message || 'Unable to upload file.');
                            });
                        }, { once: true });

                        input.click();
                    });

                    return button;
                });
            }
        };
    }

    function createCommunityEditorPromptButtonPlugin(pluginName, buttonName, label, onExecute) {
        const CK = window.CKEDITOR || {};
        const PluginBase = CK.Plugin;
        const ButtonView = CK.ButtonView;

        if (typeof PluginBase !== 'function' || typeof ButtonView !== 'function') {
            return null;
        }

        return class extends PluginBase {
            static get pluginName() {
                return pluginName;
            }

            init() {
                const editor = this.editor;

                editor.ui.componentFactory.add(buttonName, (locale) => {
                    const button = new ButtonView(locale);
                    button.set({
                        label,
                        withText: true,
                        tooltip: true,
                    });

                    button.on('execute', () => {
                        try {
                            onExecute(editor);
                        } catch (error) {
                            console.error(error);
                            notify('error', error.message || 'Unable to insert content.');
                        }
                    });

                    return button;
                });
            }
        };
    }

    function getCommunityBodyEditorPlugins() {
        const CK = window.CKEDITOR || {};

        return [
            CK.Essentials,
            CK.Paragraph,
            CK.Heading,
            CK.Bold,
            CK.Italic,
            CK.Underline,
            CK.Strikethrough,
            CK.Subscript,
            CK.Superscript,
            CK.Font,
            CK.FontFamily,
            CK.FontSize,
            CK.FontColor,
            CK.FontBackgroundColor,
            CK.Alignment,
            CK.Indent,
            CK.IndentBlock,
            CK.Highlight,
            CK.RemoveFormat,
            CK.HorizontalLine,
            CK.SpecialCharacters,
            CK.Link,
            CK.List,
            CK.BlockQuote,
            CK.Image,
            CK.ImageCaption,
            CK.ImageStyle,
            CK.ImageToolbar,
            CK.ImageUpload,
            CK.Table,
            CK.TableToolbar,
            CK.MediaEmbed,
            CK.GeneralHtmlSupport,
            CK.Undo,
        ].filter(Boolean);
    }

    function getCommunityBodyEditorExtraPlugins() {
        const InsertDocumentPlugin = createCommunityEditorFileButtonPlugin(
            'CommunityInsertDocument',
            'insertDocument',
            'Documents',
            '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip',
            (editor, file) => uploadCommunityEditorAttachment(file).then((payload) => {
                insertCommunityEditorHtml(
                    editor,
                    '<p><a class="community-inline-document" href="' + escapeCommunityEditorHtml(payload.url) + '" target="_blank" rel="noopener noreferrer">' + escapeCommunityEditorHtml(payload.name || 'Download document') + '</a></p>'
                );
            })
        );

        const UploadVideoPlugin = createCommunityEditorFileButtonPlugin(
            'CommunityUploadVideo',
            'uploadVideo',
            'Video',
            'video/*,.mp4,.webm,.mov,.avi,.mkv',
            (editor, file) => uploadCommunityEditorAttachment(file).then((payload) => {
                insertCommunityEditorHtml(
                    editor,
                    '<figure class="media community-inline-video"><video controls preload="metadata" src="' + escapeCommunityEditorHtml(payload.url) + '"></video></figure>'
                );
            })
        );

        const InsertChartPlugin = createCommunityEditorPromptButtonPlugin(
            'CommunityInsertChart',
            'insertChart',
            'Chart',
            (editor) => {
                const title = window.prompt('Chart title');
                if (title === null) {
                    return;
                }

                const rawRows = window.prompt('Enter chart rows as Label:Value (one per line)', 'Category A:40\nCategory B:65\nCategory C:25');
                if (rawRows === null) {
                    return;
                }

                const rows = rawRows.split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
                if (rows.length === 0) {
                    throw new Error('Please add at least one chart row.');
                }

                let maxValue = 0;
                const parsedRows = rows.map((line) => {
                    const separatorIndex = line.indexOf(':');
                    const label = separatorIndex >= 0 ? line.slice(0, separatorIndex).trim() : line;
                    const value = separatorIndex >= 0 ? Number(line.slice(separatorIndex + 1).trim()) : 0;
                    const safeValue = Number.isFinite(value) ? Math.max(0, value) : 0;
                    maxValue = Math.max(maxValue, safeValue);

                    return { label, value: safeValue };
                });

                const chartRows = parsedRows.map((row) => {
                    const width = maxValue > 0 ? Math.round((row.value / maxValue) * 100) : 0;

                    return '<tr><th scope="row">' + escapeCommunityEditorHtml(row.label) + '</th><td><div class="community-inline-chart__bar" style="width:' + width + '%"></div><span class="community-inline-chart__value">' + escapeCommunityEditorHtml(String(row.value)) + '</span></td></tr>';
                }).join('');

                insertCommunityEditorHtml(
                    editor,
                    '<div class="community-inline-chart"><p class="community-inline-chart__title"><strong>' + escapeCommunityEditorHtml(title.trim() || 'Chart') + '</strong></p><table class="community-inline-chart__table"><tbody>' + chartRows + '</tbody></table></div>'
                );
            }
        );

        const InsertPollPlugin = createCommunityEditorPromptButtonPlugin(
            'CommunityInsertPoll',
            'insertPoll',
            'Polls',
            (editor) => {
                const question = window.prompt('Poll question');
                if (question === null || question.trim() === '') {
                    return;
                }

                const rawOptions = window.prompt('Poll options (comma separated)', 'Yes, No, Not sure');
                if (rawOptions === null) {
                    return;
                }

                const options = rawOptions.split(',').map((option) => option.trim()).filter(Boolean);
                if (options.length < 2) {
                    throw new Error('Please provide at least two poll options.');
                }

                const optionItems = options.map((option) => {
                    return '<li>' + escapeCommunityEditorHtml(option) + '</li>';
                }).join('');

                insertCommunityEditorHtml(
                    editor,
                    '<div class="community-inline-poll"><p class="community-inline-poll__question"><strong>' + escapeCommunityEditorHtml(question.trim()) + '</strong></p><ul class="community-inline-poll__options">' + optionItems + '</ul></div>'
                );
            }
        );

        return [
            communityUploadAdapterPlugin,
            communityImageTextFlowPlugin,
            InsertDocumentPlugin,
            UploadVideoPlugin,
            InsertChartPlugin,
            InsertPollPlugin,
        ].filter(Boolean);
    }

    function getCommunityBodyEditorConfig() {
        const extraPlugins = getCommunityBodyEditorExtraPlugins();

        const config = {
            toolbar: {
                items: [
                    'heading', '|',
                    'fontFamily', 'fontSize', '|',
                    'fontColor', 'fontBackgroundColor', 'highlight', '|',
                    'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', '|',
                    'alignment', '|',
                    'outdent', 'indent', '|',
                    'bulletedList', 'numberedList', '|',
                    'uploadImage', '|',
                    'mediaEmbed', 'uploadVideo', '|',
                    'insertDocument', '|',
                    'link', 'horizontalLine', 'specialCharacters', '|',
                    'blockQuote', '|',
                    'insertPoll', '|',
                    'insertTable', 'insertChart', '|',
                    'removeFormat', '|',
                    'undo', 'redo',
                ],
                shouldNotGroupWhenFull: true,
            },
            fontFamily: {
                options: [
                    'default',
                    { title: 'Noto Sans Devanagari', model: 'Noto Sans Devanagari, Nirmala UI, Mangal, sans-serif' },
                    { title: 'Tiro Devanagari Hindi', model: 'Tiro Devanagari Hindi, Noto Sans Devanagari, serif' },
                    { title: 'Mangal', model: 'Mangal, Nirmala UI, sans-serif' },
                    { title: 'Nirmala UI', model: 'Nirmala UI, Mangal, sans-serif' },
                    { title: 'Aparajita', model: 'Aparajita, Mangal, serif' },
                    { title: 'Kokila', model: 'Kokila, Mangal, serif' },
                    { title: 'Utsaah', model: 'Utsaah, Mangal, sans-serif' },
                    { title: 'Sanskrit Text', model: 'Sanskrit Text, Mangal, serif' },
                    { title: 'Arial', model: 'Arial, Helvetica, sans-serif' },
                    { title: 'Times New Roman', model: 'Times New Roman, Times, serif' },
                    { title: 'Georgia', model: 'Georgia, serif' },
                    { title: 'Verdana', model: 'Verdana, Geneva, sans-serif' },
                    { title: 'Tahoma', model: 'Tahoma, Geneva, sans-serif' },
                    { title: 'Courier New', model: 'Courier New, Courier, monospace' },
                ],
                supportAllValues: true,
            },
            fontSize: {
                options: [
                    9,
                    11,
                    13,
                    'default',
                    17,
                    19,
                    21,
                    24,
                    28,
                    32,
                    36,
                    48,
                    72,
                ],
                supportAllValues: true,
            },
            fontColor: {
                colors: [
                    { color: '#000000', label: 'Black' },
                    { color: '#424242', label: 'Dim grey' },
                    { color: '#757575', label: 'Grey' },
                    { color: '#BDBDBD', label: 'Light grey' },
                    { color: '#FFFFFF', label: 'White', hasBorder: true },
                    { color: '#C62828', label: 'Red' },
                    { color: '#EF6C00', label: 'Orange' },
                    { color: '#F9A825', label: 'Yellow' },
                    { color: '#2E7D32', label: 'Green' },
                    { color: '#1565C0', label: 'Blue' },
                    { color: '#6A1B9A', label: 'Purple' },
                    { color: '#00838F', label: 'Teal' },
                    { color: '#5D4037', label: 'Brown' },
                ],
                columns: 5,
            },
            fontBackgroundColor: {
                colors: [
                    { color: 'transparent', label: 'None', hasBorder: true },
                    { color: '#FFF59D', label: 'Yellow' },
                    { color: '#FFCC80', label: 'Orange' },
                    { color: '#EF9A9A', label: 'Red' },
                    { color: '#A5D6A7', label: 'Green' },
                    { color: '#90CAF9', label: 'Blue' },
                    { color: '#CE93D8', label: 'Purple' },
                    { color: '#B2EBF2', label: 'Cyan' },
                    { color: '#F5F5F5', label: 'Light grey' },
                ],
                columns: 5,
            },
            alignment: {
                options: ['left', 'center', 'right', 'justify'],
            },
            highlight: {
                options: [
                    { model: 'yellowMarker', class: 'marker-yellow', title: 'Yellow marker', color: 'var(--ck-highlight-marker-yellow)', type: 'marker' },
                    { model: 'greenMarker', class: 'marker-green', title: 'Green marker', color: 'var(--ck-highlight-marker-green)', type: 'marker' },
                    { model: 'pinkMarker', class: 'marker-pink', title: 'Pink marker', color: 'var(--ck-highlight-marker-pink)', type: 'marker' },
                    { model: 'blueMarker', class: 'marker-blue', title: 'Blue marker', color: 'var(--ck-highlight-marker-blue)', type: 'marker' },
                    { model: 'redPen', class: 'pen-red', title: 'Red pen', color: 'var(--ck-highlight-pen-red)', type: 'pen' },
                    { model: 'greenPen', class: 'pen-green', title: 'Green pen', color: 'var(--ck-highlight-pen-green)', type: 'pen' },
                ],
            },
            mediaEmbed: {
                previewsInData: true,
            },
            htmlSupport: {
                allow: [
                    { name: 'div', classes: ['community-inline-chart', 'community-inline-poll', 'community-inline-chart__bar'], styles: true, attributes: true },
                    { name: 'p', classes: ['community-inline-chart__title', 'community-inline-poll__question'], styles: true },
                    { name: 'ul', classes: ['community-inline-poll__options'] },
                    { name: 'li', classes: true },
                    { name: 'table', classes: ['community-inline-chart__table'] },
                    { name: 'tbody', classes: true },
                    { name: 'tr', classes: true },
                    { name: 'th', classes: true, attributes: { scope: true } },
                    { name: 'td', classes: true },
                    { name: 'figure', classes: ['media', 'community-inline-video'] },
                    { name: 'video', attributes: { controls: true, preload: true, src: true } },
                    { name: 'a', classes: ['community-inline-document'], attributes: { href: true, target: true, rel: true } },
                    { name: 'span', classes: true, styles: true, attributes: true },
                    { name: 'strong', classes: true, styles: true },
                    { name: 'em', classes: true, styles: true },
                    { name: 'u', classes: true, styles: true },
                    { name: 's', classes: true, styles: true },
                    { name: 'sub', classes: true },
                    { name: 'sup', classes: true },
                    { name: 'h2', styles: true },
                    { name: 'h3', styles: true },
                    { name: 'h4', styles: true },
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
            table: {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
            },
            // Always use the full super-build plugin set so Word-like font/background tools stay available.
            removePlugins: [
                'AIAssistant',
                'CKBox',
                'CKFinder',
                'EasyImage',
                'MultiLevelList',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'PresenceList',
                'Comments',
                'TrackChanges',
                'TrackChangesData',
                'RevisionHistory',
                'Pagination',
                'WProofreader',
                'MathType',
                'SlashCommand',
                'Template',
                'DocumentOutline',
                'FormatPainter',
                'TableOfContents',
                'PasteFromOfficeEnhanced',
                'CaseChange',
            ],
        };

        if (extraPlugins.length) {
            config.extraPlugins = extraPlugins;
        }

        return config;
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

    function initCommunityBodyEditor() {
        const bodyEditor = document.querySelector('#bodyEditor');
        const bodyEditorMount = document.getElementById('bodyEditorMount');
        const details = document.getElementById('communityPostDetails');

        if (!bodyEditor || !bodyEditorMount) {
            console.error('Community body editor mount was not found.');
            return Promise.resolve(null);
        }

        if (!contentTypeUsesBodyEditor(document.getElementById('contentType')?.value || '')) {
            return Promise.resolve(null);
        }

        // Avoid creating the editor while the details panel is still hidden.
        if (details && details.hidden) {
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

        const EditorClass = (window.CKEDITOR && window.CKEDITOR.ClassicEditor)
            || window.ClassicEditor
            || null;

        if (!EditorClass || typeof EditorClass.create !== 'function') {
            console.error('CKEditor failed to load.', { CKEDITOR: typeof window.CKEDITOR, ClassicEditor: typeof window.ClassicEditor });
            notify('error', 'Rich text editor failed to load. Please refresh the page or check your internet connection.');
            return Promise.resolve(null);
        }

        // CKEditor misbehaves (toolbar only, no typing) if created before the panel is painted.
        communityBodyEditorInitPromise = new Promise(function (resolve) {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    if (details) {
                        void details.offsetWidth;
                    }

                    if (details && details.hidden) {
                        communityBodyEditorInitPromise = null;
                        resolve(null);
                        return;
                    }

                    EditorClass.create(bodyEditor, getCommunityBodyEditorConfig())
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

                            try {
                                const root = editor.editing.view.getDomRoot();
                                if (root) {
                                    root.setAttribute('contenteditable', 'true');
                                    root.tabIndex = 0;
                                }
                                if (editor.disableReadOnlyMode) {
                                    editor.disableReadOnlyMode('community-init');
                                }
                            } catch (error) {
                                console.warn('Unable to unlock body editor editable root.', error);
                            }

                            resolve(editor);
                        })
                        .catch((error) => {
                            communityBodyEditorInitPromise = null;
                            console.error('Unable to load the body editor.', error);
                            notify('error', 'Unable to load the body editor. Please refresh and try again.');
                            resolve(null);
                        });
                });
            });
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
    let tags = (tagsHidden?.value || '').split(',').map((tag) => tag.trim()).filter(Boolean).slice(0, MAX_COMMUNITY_TAGS);

    function notify(type, message) {
        const toastType = type === 'error' ? 'error' : 'success';
        if (window.toastr && typeof window.toastr[toastType] === 'function') {
            window.toastr[toastType](message);
            return;
        }
        alert(message);
    }

    function syncTags() {
        if (tagsHidden) {
            tagsHidden.value = tags.join(', ');
        }

        if (tagsCount) {
            tagsCount.textContent = tags.length + ' / ' + MAX_COMMUNITY_TAGS;
        }

        if (tagInput) {
            tagInput.disabled = tags.length >= MAX_COMMUNITY_TAGS;
            tagInput.placeholder = tags.length >= MAX_COMMUNITY_TAGS
                ? 'Maximum of 10 tags reached'
                : 'Type a tag and press Enter or comma';
        }

        if (!tagList) {
            return;
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
        if (!tagInput) {
            return;
        }

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

    if (tagInput) {
        tagInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                addTagsFromInput();
            }
        });
        tagInput.addEventListener('blur', addTagsFromInput);
        document.querySelector('.tag-input-wrap')?.addEventListener('click', function () {
            if (!tagInput.disabled) {
                tagInput.focus();
            }
        });
    }
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

    const womensWorldAudioUploadWrap = document.getElementById('womensWorldAudioUploadWrap');
    const womensWorldAudioRecordingWrap = document.getElementById('womensWorldAudioRecordingWrap');
    const womensWorldAudioFileInput = document.getElementById('womensWorldAudioFile');
    const keepExistingWomensWorldAudioInput = document.getElementById('keepExistingWomensWorldAudio');
    const womensWorldAudioRecordBtn = document.getElementById('womensWorldAudioRecordBtn');
    const womensWorldAudioStopBtn = document.getElementById('womensWorldAudioStopBtn');
    const womensWorldAudioClearRecordingBtn = document.getElementById('womensWorldAudioClearRecordingBtn');
    const womensWorldAudioRecordingStatus = document.getElementById('womensWorldAudioRecordingStatus');
    const womensWorldAudioRecordingPreview = document.getElementById('womensWorldAudioRecordingPreview');
    let womensWorldAudioRecorder = null;
    let womensWorldAudioStream = null;
    let womensWorldAudioChunks = [];
    let womensWorldAudioBlob = null;

    function refreshWomensWorldAudioPanels() {
        const selected = document.querySelector('input[name="womens_world_audio_source_type"]:checked')?.value || 'none';
        womensWorldAudioUploadWrap?.classList.toggle('is-active', selected === 'upload');
        womensWorldAudioRecordingWrap?.classList.toggle('is-active', selected === 'recording');
    }

    document.querySelectorAll('input[name="womens_world_audio_source_type"]').forEach((input) => {
        input.addEventListener('change', refreshWomensWorldAudioPanels);
    });

    document.getElementById('removeWomensWorldAudio')?.addEventListener('change', function () {
        if (this.checked) {
            document.getElementById('womensWorldAudioSourceNone')?.click();
        }
    });

    womensWorldAudioFileInput?.addEventListener('change', function () {
        if (keepExistingWomensWorldAudioInput) {
            keepExistingWomensWorldAudioInput.value = this.files?.length ? '0' : '1';
        }
    });

    function resetWomensWorldAudioRecordingUi() {
        if (womensWorldAudioStream) {
            womensWorldAudioStream.getTracks().forEach((track) => track.stop());
            womensWorldAudioStream = null;
        }

        womensWorldAudioRecorder = null;
        womensWorldAudioChunks = [];
        womensWorldAudioBlob = null;

        if (womensWorldAudioRecordingPreview) {
            womensWorldAudioRecordingPreview.removeAttribute('src');
            womensWorldAudioRecordingPreview.style.display = 'none';
            womensWorldAudioRecordingPreview.load();
        }

        if (womensWorldAudioRecordBtn) {
            womensWorldAudioRecordBtn.disabled = false;
        }
        if (womensWorldAudioStopBtn) {
            womensWorldAudioStopBtn.disabled = true;
        }
        if (womensWorldAudioClearRecordingBtn) {
            womensWorldAudioClearRecordingBtn.disabled = true;
        }
        if (womensWorldAudioRecordingStatus) {
            womensWorldAudioRecordingStatus.textContent = 'Ready to record.';
        }
    }

    womensWorldAudioRecordBtn?.addEventListener('click', async function () {
        if (!navigator.mediaDevices?.getUserMedia) {
            notify('error', 'Voice recording is not supported in this browser.');
            return;
        }

        try {
            womensWorldAudioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            womensWorldAudioChunks = [];
            womensWorldAudioRecorder = new MediaRecorder(womensWorldAudioStream);
            womensWorldAudioRecorder.addEventListener('dataavailable', function (event) {
                if (event.data.size > 0) {
                    womensWorldAudioChunks.push(event.data);
                }
            });
            womensWorldAudioRecorder.addEventListener('stop', function () {
                womensWorldAudioBlob = new Blob(womensWorldAudioChunks, { type: 'audio/webm' });
                const previewUrl = URL.createObjectURL(womensWorldAudioBlob);

                if (womensWorldAudioRecordingPreview) {
                    womensWorldAudioRecordingPreview.src = previewUrl;
                    womensWorldAudioRecordingPreview.style.display = '';
                    womensWorldAudioRecordingPreview.load();
                }

                if (womensWorldAudioClearRecordingBtn) {
                    womensWorldAudioClearRecordingBtn.disabled = false;
                }
                if (womensWorldAudioRecordingStatus) {
                    womensWorldAudioRecordingStatus.textContent = 'Recording ready. Submit the form to save it.';
                }

                if (keepExistingWomensWorldAudioInput) {
                    keepExistingWomensWorldAudioInput.value = '0';
                }
            });

            womensWorldAudioRecorder.start();
            womensWorldAudioRecordBtn.disabled = true;
            womensWorldAudioStopBtn.disabled = false;
            womensWorldAudioRecordingStatus.textContent = 'Recording...';
        } catch (error) {
            notify('error', 'Microphone access is required for voice recording.');
        }
    });

    womensWorldAudioStopBtn?.addEventListener('click', function () {
        if (womensWorldAudioRecorder && womensWorldAudioRecorder.state !== 'inactive') {
            womensWorldAudioRecorder.stop();
        }

        if (womensWorldAudioStream) {
            womensWorldAudioStream.getTracks().forEach((track) => track.stop());
            womensWorldAudioStream = null;
        }

        womensWorldAudioRecordBtn.disabled = false;
        womensWorldAudioStopBtn.disabled = true;
    });

    womensWorldAudioClearRecordingBtn?.addEventListener('click', function () {
        resetWomensWorldAudioRecordingUi();
    });

    refreshWomensWorldAudioPanels();

    const seniorCitizensForumAudioUploadWrap = document.getElementById('seniorCitizensForumAudioUploadWrap');
    const seniorCitizensForumAudioRecordingWrap = document.getElementById('seniorCitizensForumAudioRecordingWrap');
    const seniorCitizensForumAudioFileInput = document.getElementById('seniorCitizensForumAudioFile');
    const keepExistingSeniorCitizensForumAudioInput = document.getElementById('keepExistingSeniorCitizensForumAudio');
    const seniorCitizensForumAudioRecordBtn = document.getElementById('seniorCitizensForumAudioRecordBtn');
    const seniorCitizensForumAudioStopBtn = document.getElementById('seniorCitizensForumAudioStopBtn');
    const seniorCitizensForumAudioClearRecordingBtn = document.getElementById('seniorCitizensForumAudioClearRecordingBtn');
    const seniorCitizensForumAudioRecordingStatus = document.getElementById('seniorCitizensForumAudioRecordingStatus');
    const seniorCitizensForumAudioRecordingPreview = document.getElementById('seniorCitizensForumAudioRecordingPreview');
    let seniorCitizensForumAudioRecorder = null;
    let seniorCitizensForumAudioStream = null;
    let seniorCitizensForumAudioChunks = [];
    let seniorCitizensForumAudioBlob = null;

    function refreshSeniorCitizensForumAudioPanels() {
        const selected = document.querySelector('input[name="senior_citizens_forum_audio_source_type"]:checked')?.value || 'none';
        seniorCitizensForumAudioUploadWrap?.classList.toggle('is-active', selected === 'upload');
        seniorCitizensForumAudioRecordingWrap?.classList.toggle('is-active', selected === 'recording');
    }

    document.querySelectorAll('input[name="senior_citizens_forum_audio_source_type"]').forEach((input) => {
        input.addEventListener('change', refreshSeniorCitizensForumAudioPanels);
    });

    document.getElementById('removeSeniorCitizensForumAudio')?.addEventListener('change', function () {
        if (this.checked) {
            document.getElementById('seniorCitizensForumAudioSourceNone')?.click();
        }
    });

    seniorCitizensForumAudioFileInput?.addEventListener('change', function () {
        if (keepExistingSeniorCitizensForumAudioInput) {
            keepExistingSeniorCitizensForumAudioInput.value = this.files?.length ? '0' : '1';
        }
    });

    function resetSeniorCitizensForumAudioRecordingUi() {
        if (seniorCitizensForumAudioStream) {
            seniorCitizensForumAudioStream.getTracks().forEach((track) => track.stop());
            seniorCitizensForumAudioStream = null;
        }

        seniorCitizensForumAudioRecorder = null;
        seniorCitizensForumAudioChunks = [];
        seniorCitizensForumAudioBlob = null;

        if (seniorCitizensForumAudioRecordingPreview) {
            seniorCitizensForumAudioRecordingPreview.removeAttribute('src');
            seniorCitizensForumAudioRecordingPreview.style.display = 'none';
            seniorCitizensForumAudioRecordingPreview.load();
        }

        if (seniorCitizensForumAudioRecordBtn) {
            seniorCitizensForumAudioRecordBtn.disabled = false;
        }
        if (seniorCitizensForumAudioStopBtn) {
            seniorCitizensForumAudioStopBtn.disabled = true;
        }
        if (seniorCitizensForumAudioClearRecordingBtn) {
            seniorCitizensForumAudioClearRecordingBtn.disabled = true;
        }
        if (seniorCitizensForumAudioRecordingStatus) {
            seniorCitizensForumAudioRecordingStatus.textContent = 'Ready to record.';
        }
    }

    seniorCitizensForumAudioRecordBtn?.addEventListener('click', async function () {
        if (!navigator.mediaDevices?.getUserMedia) {
            notify('error', 'Voice recording is not supported in this browser.');
            return;
        }

        try {
            seniorCitizensForumAudioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            seniorCitizensForumAudioChunks = [];
            seniorCitizensForumAudioRecorder = new MediaRecorder(seniorCitizensForumAudioStream);
            seniorCitizensForumAudioRecorder.addEventListener('dataavailable', function (event) {
                if (event.data.size > 0) {
                    seniorCitizensForumAudioChunks.push(event.data);
                }
            });
            seniorCitizensForumAudioRecorder.addEventListener('stop', function () {
                seniorCitizensForumAudioBlob = new Blob(seniorCitizensForumAudioChunks, { type: 'audio/webm' });
                const previewUrl = URL.createObjectURL(seniorCitizensForumAudioBlob);

                if (seniorCitizensForumAudioRecordingPreview) {
                    seniorCitizensForumAudioRecordingPreview.src = previewUrl;
                    seniorCitizensForumAudioRecordingPreview.style.display = '';
                    seniorCitizensForumAudioRecordingPreview.load();
                }

                if (seniorCitizensForumAudioClearRecordingBtn) {
                    seniorCitizensForumAudioClearRecordingBtn.disabled = false;
                }
                if (seniorCitizensForumAudioRecordingStatus) {
                    seniorCitizensForumAudioRecordingStatus.textContent = 'Recording ready. Submit the form to save it.';
                }

                if (keepExistingSeniorCitizensForumAudioInput) {
                    keepExistingSeniorCitizensForumAudioInput.value = '0';
                }
            });

            seniorCitizensForumAudioRecorder.start();
            seniorCitizensForumAudioRecordBtn.disabled = true;
            seniorCitizensForumAudioStopBtn.disabled = false;
            seniorCitizensForumAudioRecordingStatus.textContent = 'Recording...';
        } catch (error) {
            notify('error', 'Microphone access is required for voice recording.');
        }
    });

    seniorCitizensForumAudioStopBtn?.addEventListener('click', function () {
        if (seniorCitizensForumAudioRecorder && seniorCitizensForumAudioRecorder.state !== 'inactive') {
            seniorCitizensForumAudioRecorder.stop();
        }

        if (seniorCitizensForumAudioStream) {
            seniorCitizensForumAudioStream.getTracks().forEach((track) => track.stop());
            seniorCitizensForumAudioStream = null;
        }

        seniorCitizensForumAudioRecordBtn.disabled = false;
        seniorCitizensForumAudioStopBtn.disabled = true;
    });

    seniorCitizensForumAudioClearRecordingBtn?.addEventListener('click', function () {
        resetSeniorCitizensForumAudioRecordingUi();
    });

    refreshSeniorCitizensForumAudioPanels();

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
        containerId: 'seniorCitizensForumKeyLessonsEntries',
        templateId: 'seniorCitizensForumKeyLessonTemplate',
        addButtonId: 'addSeniorCitizensForumKeyLessonBtn',
        initialData: window.communitySeniorCitizensForumKeyLessons || [],
        emptyEntry: '',
        populate: function (entry, value) {
            const input = entry.querySelector('.js-senior-citizens-forum-key-lesson-input');
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

    const seniorCitizensForumAchievementEntries = document.getElementById('seniorCitizensForumAchievementEntries');
    const seniorCitizensForumAchievementTemplate = document.getElementById('seniorCitizensForumAchievementTemplate');
    const addSeniorCitizensForumAchievementBtn = document.getElementById('addSeniorCitizensForumAchievementBtn');
    let seniorCitizensForumAchievementNextIndex = 0;

    function syncSeniorCitizensForumAchievementFieldNames(entry, index) {
        entry.dataset.achievementIndex = String(index);
        entry.querySelector('.senior-citizens-forum-achievement-entry__title').textContent = 'Achievement ' + (index + 1);
        entry.querySelectorAll('[data-name]').forEach((field) => {
            const fieldName = field.dataset.name.replace(/__INDEX__/g, String(index));
            field.name = fieldName;
            field.id = fieldName.replace(/[\[\]]/g, '_');
        });
    }

    function bindSeniorCitizensForumAchievementEntry(entry) {
        const photoInput = entry.querySelector('.js-scf-achievement-photo');
        const photoPreviewWrap = entry.querySelector('.js-scf-achievement-photo-preview');
        const photoPreviewImage = photoPreviewWrap?.querySelector('img');
        const existingPhotoPathInput = entry.querySelector('.js-scf-achievement-existing-photo-path');
        const certificateInput = entry.querySelector('.js-scf-achievement-certificate');
        const certificatePreview = entry.querySelector('.js-scf-achievement-certificate-preview');
        const existingCertificatePathInput = entry.querySelector('.js-scf-achievement-existing-certificate-path');

        entry.querySelector('.js-remove-senior-citizens-forum-achievement-entry')?.addEventListener('click', function () {
            entry.remove();
            reindexSeniorCitizensForumAchievements();
        });

        photoInput?.addEventListener('change', function () {
            const file = photoInput.files?.[0];
            if (!file || !photoPreviewWrap || !photoPreviewImage) {
                return;
            }

            photoPreviewImage.src = URL.createObjectURL(file);
            photoPreviewWrap.style.display = '';
            if (existingPhotoPathInput) {
                existingPhotoPathInput.value = '';
            }
        });

        certificateInput?.addEventListener('change', function () {
            const file = certificateInput.files?.[0];
            if (!file || !certificatePreview) {
                return;
            }

            certificatePreview.textContent = 'Selected: ' + file.name;
            certificatePreview.style.display = '';
            if (existingCertificatePathInput) {
                existingCertificatePathInput.value = '';
            }
        });

        if (existingPhotoPathInput?.value && photoPreviewWrap && photoPreviewImage) {
            const existingPhotoUrl = entry.dataset.existingPhotoUrl || '';
            if (existingPhotoUrl) {
                photoPreviewImage.src = existingPhotoUrl;
                photoPreviewWrap.style.display = '';
            }
        }

        if (existingCertificatePathInput?.value && certificatePreview) {
            const existingCertificateName = entry.dataset.existingCertificateName || 'Certificate on file';
            certificatePreview.innerHTML = '<a href="' + (entry.dataset.existingCertificateUrl || '#') + '" target="_blank" rel="noopener">' + existingCertificateName + '</a>';
            certificatePreview.style.display = '';
        }
    }

    function reindexSeniorCitizensForumAchievements() {
        if (!seniorCitizensForumAchievementEntries) {
            return;
        }

        seniorCitizensForumAchievementEntries.querySelectorAll('.autobiography-achievement-entry').forEach((entry, index) => {
            syncSeniorCitizensForumAchievementFieldNames(entry, index);
        });
        seniorCitizensForumAchievementNextIndex = seniorCitizensForumAchievementEntries.querySelectorAll('.autobiography-achievement-entry').length;
    }

    function addSeniorCitizensForumAchievement(data) {
        if (!seniorCitizensForumAchievementEntries || !seniorCitizensForumAchievementTemplate) {
            return;
        }

        const fragment = seniorCitizensForumAchievementTemplate.content.cloneNode(true);
        const entry = fragment.querySelector('.autobiography-achievement-entry');
        const index = seniorCitizensForumAchievementNextIndex;
        syncSeniorCitizensForumAchievementFieldNames(entry, index);
        entry.querySelector('.js-scf-achievement-award-name').value = data?.award_name || '';
        entry.querySelector('.js-scf-achievement-year').value = data?.year || '';
        entry.querySelector('.js-scf-achievement-description').value = data?.description || '';

        const existingPhotoPathInput = entry.querySelector('.js-scf-achievement-existing-photo-path');
        if (existingPhotoPathInput && data?.existing_photo_path) {
            existingPhotoPathInput.value = data.existing_photo_path;
        }
        if (data?.existing_photo_url) {
            entry.dataset.existingPhotoUrl = data.existing_photo_url;
        }

        const existingCertificatePathInput = entry.querySelector('.js-scf-achievement-existing-certificate-path');
        if (existingCertificatePathInput && data?.existing_certificate_path) {
            existingCertificatePathInput.value = data.existing_certificate_path;
        }
        if (data?.existing_certificate_name) {
            entry.dataset.existingCertificateName = data.existing_certificate_name;
        }
        if (data?.existing_certificate_url) {
            entry.dataset.existingCertificateUrl = data.existing_certificate_url;
        }

        bindSeniorCitizensForumAchievementEntry(entry);
        seniorCitizensForumAchievementEntries.appendChild(entry);
        seniorCitizensForumAchievementNextIndex += 1;
    }

    function initSeniorCitizensForumAchievements() {
        if (!seniorCitizensForumAchievementEntries || !seniorCitizensForumAchievementTemplate) {
            return;
        }

        seniorCitizensForumAchievementEntries.innerHTML = '';
        seniorCitizensForumAchievementNextIndex = 0;
        const initialEntries = Array.isArray(window.communitySeniorCitizensForumAchievements) ? window.communitySeniorCitizensForumAchievements : [];
        if (initialEntries.length > 0) {
            initialEntries.forEach((entry) => addSeniorCitizensForumAchievement(entry));
        }
    }

    addSeniorCitizensForumAchievementBtn?.addEventListener('click', function () {
        addSeniorCitizensForumAchievement({});
    });

    initSeniorCitizensForumAchievements();

    const studentCornerAchievementEntries = document.getElementById('studentCornerAchievementEntries');
    const studentCornerAchievementTemplate = document.getElementById('studentCornerAchievementTemplate');
    const addStudentCornerAchievementBtn = document.getElementById('addStudentCornerAchievementBtn');
    let studentCornerAchievementNextIndex = 0;

    function syncStudentCornerAchievementFieldNames(entry, index) {
        entry.dataset.achievementIndex = String(index);
        entry.querySelector('.student-corner-achievement-entry__title').textContent = 'Achievement ' + (index + 1);
        entry.querySelectorAll('[data-name]').forEach((field) => {
            const fieldName = field.dataset.name.replace(/__INDEX__/g, String(index));
            field.name = fieldName;
            field.id = fieldName.replace(/[\[\]]/g, '_');
        });
    }

    function bindStudentCornerAchievementEntry(entry) {
        const certificateInput = entry.querySelector('.js-sc-achievement-certificate');
        const certificatePreview = entry.querySelector('.js-sc-achievement-certificate-preview');
        const existingCertificatePathInput = entry.querySelector('.js-sc-achievement-existing-certificate-path');

        entry.querySelector('.js-remove-student-corner-achievement-entry')?.addEventListener('click', function () {
            entry.remove();
            reindexStudentCornerAchievements();
        });

        certificateInput?.addEventListener('change', function () {
            const file = certificateInput.files?.[0];
            if (!file || !certificatePreview) {
                return;
            }

            certificatePreview.textContent = 'Selected: ' + file.name;
            certificatePreview.style.display = '';
            if (existingCertificatePathInput) {
                existingCertificatePathInput.value = '';
            }
        });

        if (existingCertificatePathInput?.value && certificatePreview) {
            const existingCertificateName = entry.dataset.existingCertificateName || 'Certificate on file';
            certificatePreview.innerHTML = '<a href="' + (entry.dataset.existingCertificateUrl || '#') + '" target="_blank" rel="noopener">' + existingCertificateName + '</a>';
            certificatePreview.style.display = '';
        }
    }

    function reindexStudentCornerAchievements() {
        if (!studentCornerAchievementEntries) {
            return;
        }

        studentCornerAchievementEntries.querySelectorAll('.student-corner-achievement-entry').forEach((entry, index) => {
            syncStudentCornerAchievementFieldNames(entry, index);
        });
        studentCornerAchievementNextIndex = studentCornerAchievementEntries.querySelectorAll('.student-corner-achievement-entry').length;
    }

    function addStudentCornerAchievement(data) {
        if (!studentCornerAchievementEntries || !studentCornerAchievementTemplate) {
            return;
        }

        const fragment = studentCornerAchievementTemplate.content.cloneNode(true);
        const entry = fragment.querySelector('.student-corner-achievement-entry');
        const index = studentCornerAchievementNextIndex;
        syncStudentCornerAchievementFieldNames(entry, index);
        entry.querySelector('.js-sc-achievement-title').value = data?.achievement_title || '';
        entry.querySelector('.js-sc-achievement-year').value = data?.year || '';

        const existingCertificatePathInput = entry.querySelector('.js-sc-achievement-existing-certificate-path');
        if (existingCertificatePathInput && data?.existing_certificate_path) {
            existingCertificatePathInput.value = data.existing_certificate_path;
        }
        if (data?.existing_certificate_name) {
            entry.dataset.existingCertificateName = data.existing_certificate_name;
        }
        if (data?.existing_certificate_url) {
            entry.dataset.existingCertificateUrl = data.existing_certificate_url;
        }

        bindStudentCornerAchievementEntry(entry);
        studentCornerAchievementEntries.appendChild(entry);
        studentCornerAchievementNextIndex += 1;
    }

    function initStudentCornerAchievements() {
        if (!studentCornerAchievementEntries || !studentCornerAchievementTemplate) {
            return;
        }

        studentCornerAchievementEntries.innerHTML = '';
        studentCornerAchievementNextIndex = 0;
        const initialEntries = Array.isArray(window.communityStudentCornerAchievements) ? window.communityStudentCornerAchievements : [];
        if (initialEntries.length > 0) {
            initialEntries.forEach((entry) => addStudentCornerAchievement(entry));
        }
    }

    addStudentCornerAchievementBtn?.addEventListener('click', function () {
        addStudentCornerAchievement({});
    });

    initStudentCornerAchievements();

    const youthCornerAchievementEntries = document.getElementById('youthCornerAchievementEntries');
    const youthCornerAchievementTemplate = document.getElementById('youthCornerAchievementTemplate');
    const addYouthCornerAchievementBtn = document.getElementById('addYouthCornerAchievementBtn');
    let youthCornerAchievementNextIndex = 0;

    function syncYouthCornerAchievementFieldNames(entry, index) {
        entry.dataset.achievementIndex = String(index);
        entry.querySelector('.youth-corner-achievement-entry__title').textContent = 'Achievement ' + (index + 1);
        entry.querySelectorAll('[data-name]').forEach((field) => {
            const fieldName = field.dataset.name.replace(/__INDEX__/g, String(index));
            field.name = fieldName;
            field.id = fieldName.replace(/[\[\]]/g, '_');
        });
    }

    function bindYouthCornerAchievementEntry(entry) {
        const certificateInput = entry.querySelector('.js-yc-achievement-certificate');
        const certificatePreview = entry.querySelector('.js-yc-achievement-certificate-preview');
        const existingCertificatePathInput = entry.querySelector('.js-yc-achievement-existing-certificate-path');

        entry.querySelector('.js-remove-youth-corner-achievement-entry')?.addEventListener('click', function () {
            entry.remove();
            reindexYouthCornerAchievements();
        });

        certificateInput?.addEventListener('change', function () {
            const file = certificateInput.files?.[0];
            if (!file || !certificatePreview) {
                return;
            }

            certificatePreview.textContent = 'Selected: ' + file.name;
            certificatePreview.style.display = '';
            if (existingCertificatePathInput) {
                existingCertificatePathInput.value = '';
            }
        });

        if (existingCertificatePathInput?.value && certificatePreview) {
            const existingCertificateName = entry.dataset.existingCertificateName || 'Certificate on file';
            certificatePreview.innerHTML = '<a href="' + (entry.dataset.existingCertificateUrl || '#') + '" target="_blank" rel="noopener">' + existingCertificateName + '</a>';
            certificatePreview.style.display = '';
        }
    }

    function reindexYouthCornerAchievements() {
        if (!youthCornerAchievementEntries) {
            return;
        }

        youthCornerAchievementEntries.querySelectorAll('.youth-corner-achievement-entry').forEach((entry, index) => {
            syncYouthCornerAchievementFieldNames(entry, index);
        });
        youthCornerAchievementNextIndex = youthCornerAchievementEntries.querySelectorAll('.youth-corner-achievement-entry').length;
    }

    function addYouthCornerAchievement(data) {
        if (!youthCornerAchievementEntries || !youthCornerAchievementTemplate) {
            return;
        }

        const fragment = youthCornerAchievementTemplate.content.cloneNode(true);
        const entry = fragment.querySelector('.youth-corner-achievement-entry');
        const index = youthCornerAchievementNextIndex;
        syncYouthCornerAchievementFieldNames(entry, index);
        entry.querySelector('.js-yc-achievement-title').value = data?.achievement_title || '';
        entry.querySelector('.js-yc-achievement-year').value = data?.year || '';

        const existingCertificatePathInput = entry.querySelector('.js-yc-achievement-existing-certificate-path');
        if (existingCertificatePathInput && data?.existing_certificate_path) {
            existingCertificatePathInput.value = data.existing_certificate_path;
        }
        if (data?.existing_certificate_name) {
            entry.dataset.existingCertificateName = data.existing_certificate_name;
        }
        if (data?.existing_certificate_url) {
            entry.dataset.existingCertificateUrl = data.existing_certificate_url;
        }

        bindYouthCornerAchievementEntry(entry);
        youthCornerAchievementEntries.appendChild(entry);
        youthCornerAchievementNextIndex += 1;
    }

    function initYouthCornerAchievements() {
        if (!youthCornerAchievementEntries || !youthCornerAchievementTemplate) {
            return;
        }

        youthCornerAchievementEntries.innerHTML = '';
        youthCornerAchievementNextIndex = 0;
        const initialEntries = Array.isArray(window.communityYouthCornerAchievements) ? window.communityYouthCornerAchievements : [];
        if (initialEntries.length > 0) {
            initialEntries.forEach((entry) => addYouthCornerAchievement(entry));
        }
    }

    addYouthCornerAchievementBtn?.addEventListener('click', function () {
        addYouthCornerAchievement({});
    });

    initYouthCornerAchievements();

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

        if (contentType === 'astro-consultancy') {
            syncAstroConsultancyCategory();

            const declarationFields = document.querySelectorAll('.astro-consultancy-declaration-required');
            if (Array.from(declarationFields).some((field) => !field.checked)) {
                notify('error', 'Please confirm all Astro Consultancy declaration statements.');
                return;
            }
        }

        if (contentType === 'religion-spirituality') {
            syncReligionSpiritualityCategory();

            const rsDeclarationFields = document.querySelectorAll('.religion-spirituality-declaration-required');
            if (Array.from(rsDeclarationFields).some((field) => !field.checked)) {
                notify('error', 'Please confirm all Religion & Spirituality declaration statements.');
                return;
            }
        }

        if (contentType === 'creative-corner') {
            syncCreativeCornerCategory();

            const ccDeclarationFields = document.querySelectorAll('.creative-corner-declaration-required');
            if (Array.from(ccDeclarationFields).some((field) => !field.checked)) {
                notify('error', 'Please confirm all Creative Corner declaration statements.');
                return;
            }
        }

        if (contentType === 'competitions') {
            syncCompetitionsCategory();

            const compDeclarationFields = document.querySelectorAll('.competitions-declaration-required');
            if (Array.from(compDeclarationFields).some((field) => !field.checked)) {
                notify('error', 'Please confirm all competition declaration statements.');
                return;
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

        if (contentType === 'womens-world') {
            syncWomensWorldCategory();

            const womensWorldCategory = document.getElementById('womensWorldCategory')?.value || '';
            if (!womensWorldCategory) {
                notify('error', 'Please select a main category for this Women\'s World post.');
                document.getElementById('womensWorldCategory')?.focus();
                return;
            }

            if (!document.getElementById('womensWorldContentType')?.value) {
                notify('error', 'Please select a content type.');
                document.getElementById('womensWorldContentType')?.focus();
                return;
            }

            const womensWorldAudienceCount = document.querySelectorAll('input[name="womens_world_target_audience[]"]:checked').length;
            if (!womensWorldAudienceCount) {
                notify('error', 'Please select at least one target audience.');
                return;
            }

            if (document.getElementById('womensWorldAllowPoll')?.checked) {
                const womensWorldPollQuestion = document.getElementById('womensWorldPollQuestion')?.value.trim() || '';
                if (!womensWorldPollQuestion) {
                    notify('error', 'Please enter a poll question for this Women\'s World post.');
                    document.getElementById('womensWorldPollQuestion')?.focus();
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

            if (contentType === 'local-voices' || contentType === 'my-area' || contentType === 'community-issues') {
                requiredStructuredFields.push(document.getElementById('communityLocationLocality'));
            }

            if (requiredStructuredFields.some((field) => !field?.value.trim())) {
                const locationMessage = contentType === 'awareness'
                    ? 'Please complete country, state, district, city, and area for this awareness post.'
                    : (contentType === 'community-issues'
                        ? 'Please complete country, state, district, city, and locality for this community issue.'
                        : (contentType === 'agriculture'
                            ? 'Please complete country, state, district, and village/town for this agriculture post.'
                            : (contentType === 'local-voices' || contentType === 'my-area'
                                ? 'Please complete country, state, district, city, and locality for this post.'
                                : 'Please complete country, state, district, and city for this post.')));
                notify('error', locationMessage);
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

            if (publishAs === 'pen_name') {
                const contentTypeForPenName = document.getElementById('contentType')?.value || '';
                const penNameValue = contentTypeForPenName === 'womens-world'
                    ? document.getElementById('womensWorldPenNameInput')?.value.trim()
                    : contentTypeForPenName === 'student-corner'
                        ? document.getElementById('studentCornerPenNameInput')?.value.trim()
                        : contentTypeForPenName === 'youth-corner'
                            ? document.getElementById('youthCornerPenNameInput')?.value.trim()
                            : contentTypeForPenName === 'local-voices'
                                ? document.getElementById('localVoicePenNameInput')?.value.trim()
                                : contentTypeForPenName === 'my-area'
                                    ? document.getElementById('myAreaPenNameInput')?.value.trim()
                                    : contentTypeForPenName === 'community-issues'
                                        ? document.getElementById('communityIssuePenNameInput')?.value.trim()
                                        : document.getElementById('penNameInput')?.value.trim();
                if (!penNameValue) {
                    notify('error', 'Please enter a pen name.');
                    return;
                }
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

        if (contentType === 'womens-world') {
            const womensWorldAudioSource = document.querySelector('input[name="womens_world_audio_source_type"]:checked')?.value || 'none';
            if (womensWorldAudioSource === 'upload') {
                const hasNewAudio = (womensWorldAudioFileInput?.files?.length || 0) > 0;
                const keepingExistingAudio = keepExistingWomensWorldAudioInput?.value === '1';
                if (!hasNewAudio && !keepingExistingAudio) {
                    notify('error', 'Please choose an audio file or switch to another audio option.');
                    return;
                }

                if (hasNewAudio && womensWorldAudioFileInput.files[0].size > maxStoryAudioBytes) {
                    notify('error', 'Audio file must be 20 MB or smaller.');
                    return;
                }
            }

            if (womensWorldAudioSource === 'recording') {
                const keepingExistingAudio = keepExistingWomensWorldAudioInput?.value === '1';
                if (!womensWorldAudioBlob && !keepingExistingAudio) {
                    notify('error', 'Please record your audio message or switch to another audio option.');
                    return;
                }

                if (womensWorldAudioBlob && womensWorldAudioBlob.size > maxStoryAudioBytes) {
                    notify('error', 'Recorded audio must be 20 MB or smaller.');
                    return;
                }
            }
        }

        if (contentType === 'senior-citizens-forum') {
            const seniorCitizensForumAudioSource = document.querySelector('input[name="senior_citizens_forum_audio_source_type"]:checked')?.value || 'none';
            if (seniorCitizensForumAudioSource === 'upload') {
                const hasNewAudio = (seniorCitizensForumAudioFileInput?.files?.length || 0) > 0;
                const keepingExistingAudio = keepExistingSeniorCitizensForumAudioInput?.value === '1';
                if (!hasNewAudio && !keepingExistingAudio) {
                    notify('error', 'Please choose an audio file or switch to another audio option.');
                    return;
                }

                if (hasNewAudio && seniorCitizensForumAudioFileInput.files[0].size > maxStoryAudioBytes) {
                    notify('error', 'Audio file must be 20 MB or smaller.');
                    return;
                }
            }

            if (seniorCitizensForumAudioSource === 'recording') {
                const keepingExistingAudio = keepExistingSeniorCitizensForumAudioInput?.value === '1';
                if (!seniorCitizensForumAudioBlob && !keepingExistingAudio) {
                    notify('error', 'Please record your audio memory or switch to another audio option.');
                    return;
                }

                if (seniorCitizensForumAudioBlob && seniorCitizensForumAudioBlob.size > maxStoryAudioBytes) {
                    notify('error', 'Recorded audio must be 20 MB or smaller.');
                    return;
                }
            }
        }

        if (contentType === 'student-corner') {
            syncStudentCornerCategory();

            if (!document.getElementById('studentCornerCategory')?.value) {
                notify('error', 'Please select a Student Corner main category.');
                document.getElementById('studentCornerCategory')?.focus();
                return;
            }

            if (!document.getElementById('studentCornerContentType')?.value) {
                notify('error', 'Please select a Student Corner content type.');
                document.getElementById('studentCornerContentType')?.focus();
                return;
            }

            if (document.getElementById('studentCornerContentType')?.value === STUDENT_CORNER_PROJECT_CONTENT_TYPE) {
                if (!document.getElementById('studentCornerProjectTitle')?.value.trim()) {
                    notify('error', 'Please enter a project title.');
                    document.getElementById('studentCornerProjectTitle')?.focus();
                    return;
                }

                if (!document.getElementById('studentCornerProjectCategory')?.value) {
                    notify('error', 'Please select a project category.');
                    document.getElementById('studentCornerProjectCategory')?.focus();
                    return;
                }

                if (!document.getElementById('studentCornerProjectDescription')?.value.trim()) {
                    notify('error', 'Please enter a project description.');
                    document.getElementById('studentCornerProjectDescription')?.focus();
                    return;
                }
            }
        }

        if (contentType === 'youth-corner') {
            syncYouthCornerCategory();

            if (!document.getElementById('youthCornerCategory')?.value) {
                notify('error', 'Please select a Youth Corner main category.');
                document.getElementById('youthCornerCategory')?.focus();
                return;
            }

            if (!document.getElementById('youthCornerContentType')?.value) {
                notify('error', 'Please select a Youth Corner content type.');
                document.getElementById('youthCornerContentType')?.focus();
                return;
            }
        }

        if (contentType === 'local-voices') {
            syncLocalVoicesCategory();

            if (!document.getElementById('localVoiceType')?.value) {
                notify('error', 'Please select what you would like to share.');
                document.getElementById('localVoiceType')?.focus();
                return;
            }

            if (!document.getElementById('localVoiceCategory')?.value) {
                notify('error', 'Please select a Local Voices main category.');
                document.getElementById('localVoiceCategory')?.focus();
                return;
            }
        }

        if (contentType === 'community-issues') {
            syncCommunityIssuesCategory();

            if (!document.getElementById('communityIssueCategory')?.value) {
                notify('error', 'Please select an issue category.');
                document.getElementById('communityIssueCategory')?.focus();
                return;
            }

            if (!document.getElementById('communityIssueType')?.value) {
                notify('error', 'Please select an issue type.');
                document.getElementById('communityIssueType')?.focus();
                return;
            }

            if (!document.getElementById('communityIssueSeverity')?.value) {
                notify('error', 'Please select issue severity.');
                document.getElementById('communityIssueSeverity')?.focus();
                return;
            }
        }

        if (contentType === 'agriculture') {
            syncAgricultureCategory();

            if (!document.getElementById('agricultureShareType')?.value) {
                notify('error', 'Please select what you would like to share.');
                document.getElementById('agricultureShareType')?.focus();
                return;
            }

            if (!document.getElementById('agricultureCategory')?.value) {
                notify('error', 'Please select an agriculture main category.');
                document.getElementById('agricultureCategory')?.focus();
                return;
            }
        }

        submitButton.disabled = true;
        submitButton.innerHTML = 'Saving...';

        if (contentType === 'student-corner') {
            reindexStudentCornerAchievements();
        }

        if (contentType === 'youth-corner') {
            reindexYouthCornerAchievements();
        }

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

        if (document.getElementById('contentType').value === 'womens-world') {
            formData.delete('womens_world_audio_recording');
            const womensWorldAudioSource = document.querySelector('input[name="womens_world_audio_source_type"]:checked')?.value || 'none';
            if (womensWorldAudioSource === 'recording' && womensWorldAudioBlob) {
                formData.append('womens_world_audio_recording', womensWorldAudioBlob, 'womens-world-recording.webm');
            }
        }

        if (document.getElementById('contentType').value === 'senior-citizens-forum') {
            formData.delete('senior_citizens_forum_audio_recording');
            const seniorCitizensForumAudioSource = document.querySelector('input[name="senior_citizens_forum_audio_source_type"]:checked')?.value || 'none';
            if (seniorCitizensForumAudioSource === 'recording' && seniorCitizensForumAudioBlob) {
                formData.append('senior_citizens_forum_audio_recording', seniorCitizensForumAudioBlob, 'senior-citizens-forum-recording.webm');
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
