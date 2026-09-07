@extends('frontend.layouts.app')
@section('meta_title', 'All Posted Notes | SoilnWater')
@section('meta_description', 'Explore thousands of quality notes shared by students, teachers and experts.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/study-materials.css') }}?v={{ now()->timestamp }}">
@endpush

@php
    $selectedMaterialTypes = (array) ($filters['material_types'] ?? []);
    $selectedFileTypes = (array) ($filters['file_types'] ?? []);

    $fileTypeGroups = [
        'pdf' => ['label' => 'PDF', 'icon' => 'fa-file-pdf', 'tone' => 'pdf', 'count' => 0],
        'doc' => ['label' => 'DOC/DOCX', 'icon' => 'fa-file-word', 'tone' => 'doc', 'count' => 0],
        'ppt' => ['label' => 'PPT', 'icon' => 'fa-file-powerpoint', 'tone' => 'ppt', 'count' => 0],
        'xls' => ['label' => 'XLS/XLSX', 'icon' => 'fa-file-excel', 'tone' => 'xls', 'count' => 0],
        'image' => ['label' => 'Image', 'icon' => 'fa-file-image', 'tone' => 'img', 'count' => 0],
    ];

    foreach ($fileTypes as $fileTypeRow) {
        $group = \App\Models\StudyMaterial::fileTypeGroup($fileTypeRow->file_type);
        if (isset($fileTypeGroups[$group])) {
            $fileTypeGroups[$group]['count'] += (int) $fileTypeRow->total;
        }
    }

    $subjectIcons = [
        'Mathematics' => 'fa-square-root-variable',
        'Physics' => 'fa-atom',
        'Chemistry' => 'fa-flask',
        'Biology' => 'fa-dna',
        'English' => 'fa-book-open',
        'History' => 'fa-landmark',
        'Geography' => 'fa-earth-americas',
        'Computer Science' => 'fa-laptop-code',
    ];

    $uploadUrl = auth()->check() && auth()->user()->isEducator()
        ? route('educator.materials.create')
        : route('login');
    $myNotesUrl = auth()->check() && auth()->user()->isEducator()
        ? route('educator.materials.index')
        : route('login');

    $queryWithoutPage = request()->except('page');
    $materialTypeCounts = $materialTypes->pluck('total', 'material_type');
    $allMaterialTypeKeys = ['notes', 'question_papers', 'sample_papers', 'worksheets', 'reference_books', 'study_guides', 'videos'];
@endphp

@section('content')
<div
    class="sm-page sm-notes-page"
    id="smNotesPage"
    data-notes-url="{{ route('study-materials.notes') }}"
    data-login-url="{{ route('login') }}"
    data-is-auth="{{ auth()->check() ? '1' : '0' }}"
>
    <div class="container-fluid sm-notes-container">
        <nav class="sm-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('frontend.index') }}">Home</a>
            <span aria-hidden="true">›</span>
            <a href="{{ route('study-materials.library') }}">Study Materials Library</a>
            <span aria-hidden="true">›</span>
            <span aria-current="page">All Posted Notes</span>
        </nav>

        <div class="sm-page-header">
            <div>
                <h1>All Posted Notes</h1>
                <p>Explore thousands of quality notes shared by students, teachers and experts.</p>
            </div>
            <div class="sm-page-header__actions">
                <a href="{{ $uploadUrl }}" class="sm-btn sm-btn-outline sm-btn-upload sm-btn-lg">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Notes
                </a>
                <a href="{{ $myNotesUrl }}" class="sm-btn sm-btn-primary sm-btn-lg">
                    <i class="fa-solid fa-folder-open"></i> My Notes
                </a>
            </div>
        </div>

        <div class="sm-stats-row">
            <div class="sm-stat-card sm-stat-card--blue">
                <span class="sm-stat-card__icon"><i class="fa-solid fa-book"></i></span>
                <div class="sm-stat-card__body">
                    <strong>{{ number_format($stats['total']) }}</strong>
                    <span>Total Notes</span>
                </div>
            </div>
            <div class="sm-stat-card sm-stat-card--green">
                <span class="sm-stat-card__icon"><i class="fa-solid fa-folder-tree"></i></span>
                <div class="sm-stat-card__body">
                    <strong>{{ number_format($stats['subjects']) }}</strong>
                    <span>Subjects</span>
                </div>
            </div>
            <div class="sm-stat-card sm-stat-card--purple">
                <span class="sm-stat-card__icon"><i class="fa-solid fa-user-group"></i></span>
                <div class="sm-stat-card__body">
                    <strong>{{ number_format($stats['contributors']) }}</strong>
                    <span>Contributors</span>
                </div>
            </div>
            <div class="sm-stat-card sm-stat-card--orange">
                <span class="sm-stat-card__icon"><i class="fa-solid fa-download"></i></span>
                <div class="sm-stat-card__body">
                    <strong>{{ number_format($stats['downloads']) }}</strong>
                    <span>Total Downloads</span>
                </div>
            </div>
        </div>

        <div class="sm-layout-3col">
            <aside class="sm-filter-sidebar">
                <div class="sm-filter-sidebar__head">
                    <h2>Filter &amp; Refine</h2>
                    <a href="{{ route('study-materials.notes') }}" class="sm-filter-reset js-sm-notes-reset">Reset All</a>
                </div>

                <form method="GET" action="{{ route('study-materials.notes') }}" id="smNotesFilterForm" class="js-sm-notes-filter-form">
                    <input type="hidden" name="view" value="{{ $viewMode }}" id="sm-filter-view">
                    <input type="hidden" name="sort" value="{{ $sort }}" id="sm-filter-sort">
                    <input type="hidden" name="category" value="{{ $filters['category'] ?? '' }}" id="sm-filter-category">

                    <div class="sm-filter-group">
                        <label for="sm-class-course">Class / Course</label>
                        <select id="sm-class-course" name="class_course" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classOptions as $option)
                                <option value="{{ $option }}" @selected($filters['class_course'] === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm-filter-group">
                        <label for="sm-board">Board / University</label>
                        <select id="sm-board" name="board_university" class="form-select">
                            <option value="">All Boards</option>
                            @foreach($boardOptions as $option)
                                <option value="{{ $option }}" @selected($filters['board_university'] === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm-filter-group">
                        <label for="sm-subject">Subject</label>
                        <select id="sm-subject" name="subject" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach($subjectOptions as $option)
                                <option value="{{ $option }}" @selected($filters['subject'] === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm-filter-group">
                        <label for="sm-topic">Topic / Chapter</label>
                        <select id="sm-topic" name="topic_chapter" class="form-select">
                            <option value="">All Topics</option>
                            @foreach($topicOptions as $option)
                                <option value="{{ $option }}" @selected($filters['topic_chapter'] === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm-filter-section">
                        <h3>Material Type</h3>
                        <div class="sm-filter-checks">
                            @foreach($allMaterialTypeKeys as $typeKey)
                                @php
                                    $meta = \App\Models\StudyMaterial::materialTypeMeta($typeKey);
                                    $typeCount = (int) ($materialTypeCounts[$typeKey] ?? 0);
                                @endphp
                                <label class="sm-filter-check">
                                    <input type="checkbox" name="material_types[]" value="{{ $typeKey }}" @checked(in_array($typeKey, $selectedMaterialTypes, true))>
                                    <span class="sm-filter-check__icon sm-filter-check__icon--{{ $meta['tone'] }}"><i class="fa-solid {{ $meta['icon'] }}"></i></span>
                                    <span class="sm-filter-check__label">{{ $meta['label'] }} ({{ number_format($typeCount) }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="sm-filter-section">
                        <h3>File Type</h3>
                        <div class="sm-filter-checks">
                            @foreach($fileTypeGroups as $groupKey => $group)
                                <label class="sm-filter-check">
                                    <input type="checkbox" name="file_types[]" value="{{ $groupKey }}" @checked(in_array($groupKey, $selectedFileTypes, true))>
                                    <span class="sm-filter-check__icon sm-filter-check__icon--{{ $group['tone'] }}"><i class="fa-solid {{ $group['icon'] }}"></i></span>
                                    <span class="sm-filter-check__label">{{ $group['label'] }} ({{ number_format($group['count']) }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="sm-btn sm-btn-primary sm-btn-block">Apply Filters</button>
                </form>
            </aside>

            <section class="sm-notes-main">
                <div class="sm-toolbar sm-toolbar--top">
                    <div></div>
                    <div class="sm-toolbar__controls">
                            <label for="sm-sort" class="sm-sort-label">Sort by:</label>
                            <select id="sm-sort" class="form-select form-select-sm js-sm-notes-sort">
                                <option value="recent" @selected($sort === 'recent')>Most Recent</option>
                                <option value="downloads" @selected($sort === 'downloads')>Most Downloaded</option>
                                <option value="rating" @selected($sort === 'rating')>Top Rated</option>
                                <option value="title" @selected($sort === 'title')>Title A–Z</option>
                            </select>
                        <div class="sm-view-toggle" aria-label="View mode">
                            <button type="button" class="sm-view-toggle__btn js-sm-notes-view {{ $viewMode === 'grid' ? 'is-active' : '' }}" data-view="grid" title="Grid view"><i class="fa-solid fa-grip"></i></button>
                            <button type="button" class="sm-view-toggle__btn js-sm-notes-view {{ $viewMode === 'list' ? 'is-active' : '' }}" data-view="list" title="List view"><i class="fa-solid fa-list"></i></button>
                        </div>
                    </div>
                </div>

                <div class="sm-category-tabs" id="smNotesCategoryTabs" role="tablist" aria-label="Categories">
                    @include('frontend.study-materials.partials.category-tabs')
                </div>

                <div id="smNotesResults" class="sm-notes-results">
                    @include('frontend.study-materials.partials.notes-results')
                </div>
            </section>

            <aside class="sm-right-sidebar">
                <div class="sm-side-card">
                    <div class="sm-side-card__head">
                        <h3>Popular Subjects</h3>
                        <a href="{{ route('study-materials.notes') }}" class="js-sm-notes-view-all">View All</a>
                    </div>
                    <div class="sm-subject-list">
                        @forelse($popularSubjects as $subject)
                            <a href="{{ route('study-materials.notes', array_merge($queryWithoutPage, ['subject' => $subject->subject])) }}" class="sm-subject-item js-sm-notes-filter-link" data-filter-subject="{{ $subject->subject }}">
                                <span class="sm-subject-item__icon"><i class="fa-solid {{ $subjectIcons[$subject->subject] ?? 'fa-book' }}"></i></span>
                                <span class="sm-subject-item__label">{{ $subject->subject }}</span>
                                <span class="sm-subject-item__count">{{ number_format($subject->total) }} Notes</span>
                            </a>
                        @empty
                            <p class="sm-empty mb-0">No subjects yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="sm-side-card">
                    <div class="sm-side-card__head">
                        <h3>Top Contributors</h3>
                        <a href="{{ route('educator.listings') }}">View All</a>
                    </div>
                    <div class="sm-contributor-list">
                        @forelse($topContributors as $row)
                            <div class="sm-contributor-item">
                                <img src="{{ $row->educator?->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="{{ $row->educator?->display_name }}" class="sm-contributor-item__avatar">
                                <div class="sm-contributor-item__body">
                                    <strong>{{ $row->educator?->display_name }}</strong>
                                    <span>{{ $row->educator?->professional_headline ?: $row->educator?->roleLabel() }}</span>
                                    <small>{{ number_format($row->materials_count) }} Notes · {{ \App\Models\StudyMaterial::formatCompactCount($row->views_sum) }} Views</small>
                                </div>
                                @auth
                                    @if($row->educator?->slug)
                                        <form method="POST" action="{{ route('educator.follow', $row->educator->slug) }}" class="js-sm-educator-follow-form">
                                            @csrf
                                            <button type="submit" class="sm-contributor-item__follow">Follow</button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="sm-contributor-item__follow">Follow</a>
                                @endauth
                            </div>
                        @empty
                            <p class="sm-empty mb-0">No contributors yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="sm-help-box">
                    <div class="sm-help-box__icon"><i class="fa-solid fa-headset"></i></div>
                    <div>
                        <h4>Need Help?</h4>
                        <p>Can't find the notes you are looking for?</p>
                        <a href="{{ auth()->check() ? route('discussions.index') : route('login') }}" class="sm-btn sm-btn-outline sm-btn-block">
                            <i class="fa-regular fa-comments"></i> Ask in Discussion Forum
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('community.partials.toastr-assets')
<script src="{{ asset('assets/js/study-materials-notes.js') }}?v={{ now()->timestamp }}" defer></script>
@endpush
