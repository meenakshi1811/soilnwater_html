@extends('backend.layouts.child-portal')

@section('title', ($firstName ?? $childProfile->full_name) . "'s Education Dashboard")

@section('content')
@php
    $displayFirst = $firstName ?? explode(' ', trim($childProfile->full_name))[0] ?? $childProfile->full_name;
    $parentName = $viewingAsParent ? auth()->user()->full_name ?: auth()->user()->name : null;

    $statCards = [
        ['key' => 'materials', 'label' => 'Study Materials', 'sub' => 'Saved & Accessed', 'icon' => 'fa-file-lines', 'tone' => 'blue'],
        ['key' => 'questions', 'label' => 'Questions Asked', 'sub' => 'This Year', 'icon' => 'fa-circle-question', 'tone' => 'green'],
        ['key' => 'courses', 'label' => 'Courses Enrolled', 'sub' => 'Active Courses', 'icon' => 'fa-graduation-cap', 'tone' => 'purple'],
        ['key' => 'achievements', 'label' => 'Achievements', 'sub' => 'Certificates & Awards', 'icon' => 'fa-trophy', 'tone' => 'amber'],
    ];

    $subjectIcons = [
        'Mathematics' => ['icon' => 'fa-square-root-variable', 'tone' => 'blue'],
        'Science' => ['icon' => 'fa-flask', 'tone' => 'green'],
        'English' => ['icon' => 'fa-book-open', 'tone' => 'purple'],
        'Social Studies' => ['icon' => 'fa-globe', 'tone' => 'orange'],
        'Social Science' => ['icon' => 'fa-globe', 'tone' => 'orange'],
        'Hindi' => ['icon' => 'fa-language', 'tone' => 'teal'],
    ];

    $subjectResources = collect($subjects)->values()->map(function ($subject, $index) use ($subjectIcons) {
        $fallbackTones = ['blue', 'green', 'purple', 'orange', 'teal'];
        $fallbackIcons = ['fa-book', 'fa-flask', 'fa-pen', 'fa-globe', 'fa-language'];
        $meta = $subjectIcons[$subject] ?? ['icon' => $fallbackIcons[$index % 5], 'tone' => $fallbackTones[$index % 5]];

        return [
            'name' => $subject,
            'icon' => $meta['icon'],
            'tone' => $meta['tone'],
            'materials' => [8, 6, 5, 4, 3][$index % 5],
            'tests' => [3, 2, 2, 1, 1][$index % 5],
        ];
    });

    $recommendedTabs = ['Study Materials', 'Courses', 'Books', 'Videos', 'Practice Papers'];
@endphp

<div class="child-dashboard">
    {{-- Page header --}}
    <header class="child-page-header">
        <div class="child-page-header__main">
            <nav class="child-breadcrumbs" aria-label="Breadcrumb">
                @if($viewingAsParent)
                    <a href="{{ route('parent.dashboard') }}">Dashboard</a>
                    <span>&rsaquo;</span>
                    <span>My Children</span>
                    <span>&rsaquo;</span>
                    <span class="is-current">{{ $childProfile->full_name }}</span>
                @else
                    <span>Dashboard</span>
                    <span>&rsaquo;</span>
                    <span class="is-current">Overview</span>
                @endif
            </nav>

            <div class="child-page-title-row">
                <div>
                    <h1 class="child-page-title">
                        {{ $displayFirst }}&rsquo;s Education Dashboard
                        <i class="fa-solid fa-star child-page-title__star"></i>
                    </h1>
                    <div class="child-page-meta">
                        @if($classBoard)
                            <span><i class="fa-solid fa-graduation-cap"></i> {{ $classBoard }}</span>
                        @endif
                        @if($childProfile->school_name)
                            <span><i class="fa-solid fa-school"></i> {{ $childProfile->school_name }}</span>
                        @endif
                    </div>
                </div>

                <div class="child-page-actions">
                    <div class="child-select-wrap">
                        <select class="child-select" aria-label="Academic year">
                            <option>Academic Year 2024-25</option>
                            <option>Academic Year 2023-24</option>
                        </select>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <button type="button" class="child-btn child-btn--primary">
                        <i class="fa-solid fa-download"></i> Download Progress Report
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Stats row --}}
    <section class="child-stats-grid">
        @foreach($statCards as $card)
            <article class="child-stat-card child-stat-card--{{ $card['tone'] }}">
                <div class="child-stat-card__icon"><i class="fa-solid {{ $card['icon'] }}"></i></div>
                <div class="child-stat-card__body">
                    <span class="child-stat-card__value">{{ $stats[$card['key']] ?? 0 }}</span>
                    <strong>{{ $card['label'] }}</strong>
                    <small>{{ $card['sub'] }}</small>
                </div>
                <a href="#" class="child-stat-card__link">View all <i class="fa-solid fa-arrow-right"></i></a>
            </article>
        @endforeach
    </section>

    {{-- Recent activity + Performance --}}
    <div class="child-grid child-grid--2">
        <section class="child-panel">
            <div class="child-panel__head">
                <h2>Recent Activity</h2>
                <a href="#" class="child-panel__link">View All</a>
            </div>
            <ul class="child-activity-list">
                @foreach($recentActivity as $item)
                    <li class="child-activity-item">
                        <span class="child-activity-item__icon child-activity-item__icon--{{ $item['tone'] }}">
                            <i class="fa-solid {{ $item['icon'] }}"></i>
                        </span>
                        <div class="child-activity-item__body">
                            <strong>{{ $item['title'] }}</strong>
                            <small>{{ $item['meta'] }}</small>
                        </div>
                        <i class="fa-solid fa-chevron-right child-activity-item__chevron"></i>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="child-panel">
            <div class="child-panel__head">
                <h2>Performance Snapshot</h2>
                <a href="#" class="child-panel__link">View Details</a>
            </div>
            <div class="child-performance">
                @foreach($performance as $row)
                    <div class="child-progress-row">
                        <span class="child-progress-row__label">{{ $row['label'] }}</span>
                        <div class="child-progress-row__bar">
                            <span class="child-progress-row__fill child-progress-row__fill--{{ $row['tone'] }}" style="width: {{ $row['value'] }}%"></span>
                        </div>
                        <span class="child-progress-row__value">{{ $row['value'] }}%</span>
                    </div>
                @endforeach

                <div class="child-overall-progress">
                    <div class="child-overall-progress__copy">
                        <small>Overall Progress</small>
                        <strong>{{ $overallProgress }}%</strong>
                        <p>Good Progress! Keep it up 👍</p>
                    </div>
                    <div class="child-overall-progress__chart" aria-hidden="true">
                        <svg viewBox="0 0 120 48" fill="none">
                            <polyline points="0,38 20,32 40,28 60,22 80,18 100,12 120,8" stroke="#22c55e" stroke-width="2.5" fill="none"/>
                            <polyline points="0,38 20,32 40,28 60,22 80,18 100,12 120,8" stroke="#22c55e" stroke-width="8" stroke-opacity="0.12" fill="none"/>
                        </svg>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Subjects + Deadlines --}}
    <div class="child-grid child-grid--2">
        <section class="child-panel">
            <div class="child-panel__head">
                <h2>Subjects &amp; Resources</h2>
                <a href="#" class="child-panel__link">View All</a>
            </div>
            <div class="child-subjects-row">
                @foreach($subjectResources as $subject)
                    <article class="child-subject-card child-subject-card--{{ $subject['tone'] }}">
                        <span class="child-subject-card__icon"><i class="fa-solid {{ $subject['icon'] }}"></i></span>
                        <strong>{{ $subject['name'] }}</strong>
                        <small>{{ $subject['materials'] }} Materials</small>
                        <small>{{ $subject['tests'] }} Tests</small>
                        <a href="{{ route('study-materials.library') }}" class="child-subject-card__link">Explore <i class="fa-solid fa-arrow-right"></i></a>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="child-panel">
            <div class="child-panel__head">
                <h2>Upcoming Tests / Deadlines</h2>
                <a href="#" class="child-panel__link">View All</a>
            </div>
            <ul class="child-deadline-list">
                @foreach($upcomingTests as $test)
                    <li class="child-deadline-item">
                        <div class="child-deadline-item__date">
                            <strong>{{ $test['day'] }}</strong>
                            <span>{{ $test['month'] }}</span>
                        </div>
                        <div class="child-deadline-item__body">
                            <strong>{{ $test['title'] }}</strong>
                            <small>{{ $test['time'] ?? 'Scheduled' }}</small>
                        </div>
                    </li>
                @endforeach
            </ul>
            <a href="#" class="child-panel__footer-link">View Full Calendar <i class="fa-solid fa-arrow-right"></i></a>
        </section>
    </div>

    {{-- Recommended + Teachers --}}
    <div class="child-grid child-grid--2">
        <section class="child-panel">
            <div class="child-panel__head">
                <h2>Recommended for {{ $displayFirst }}</h2>
                <a href="#" class="child-panel__link">View All</a>
            </div>
            <div class="child-tabs" role="tablist">
                @foreach($recommendedTabs as $index => $tab)
                    <button type="button" class="child-tab {{ $index === 0 ? 'is-active' : '' }}" data-tab="{{ \Illuminate\Support\Str::slug($tab) }}">{{ $tab }}</button>
                @endforeach
            </div>
            <div class="child-recommended-row">
                @foreach($recommended as $item)
                    <article class="child-recommended-card child-recommended-card--{{ $item['tone'] }}">
                        <div class="child-recommended-card__thumb">
                            <i class="fa-solid fa-file-lines"></i>
                            <span class="child-recommended-card__type">{{ $item['type'] }}</span>
                        </div>
                        <h3>{{ $item['title'] }}</h3>
                        <div class="child-recommended-card__meta">
                            <span><i class="fa-solid fa-star"></i> {{ $item['rating'] }}</span>
                            <span><i class="fa-solid fa-download"></i> {{ $item['downloads'] }}</span>
                        </div>
                        <button type="button" class="child-btn child-btn--outline child-btn--sm">View</button>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="child-panel">
            <div class="child-panel__head">
                <h2>Teachers Guiding {{ $displayFirst }}</h2>
                <a href="{{ route('educator.index') }}" class="child-panel__link">View All</a>
            </div>
            <ul class="child-teacher-list">
                @foreach($teachers as $teacher)
                    @php
                        $teacherInitials = collect(preg_split('/\s+/', trim($teacher['name'])) ?: [])->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                    @endphp
                    <li class="child-teacher-item">
                        <div class="child-teacher-item__avatar">{{ $teacherInitials }}</div>
                        <div class="child-teacher-item__body">
                            <strong>{{ $teacher['name'] }}</strong>
                            <small>{{ $teacher['subject'] }}</small>
                            <span class="child-teacher-item__rating"><i class="fa-solid fa-star"></i> {{ $teacher['rating'] }}</span>
                        </div>
                        <button type="button" class="child-btn child-btn--outline child-btn--sm">Send Message</button>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    {{-- Footer banner --}}
    <section class="child-footer-banner">
        <div class="child-footer-banner__icon"><i class="fa-solid fa-shield-heart"></i></div>
        <div class="child-footer-banner__copy">
            <strong>Stay Informed, Stay Involved</strong>
            <p>Regular engagement in your child's learning journey leads to better academic outcomes.</p>
        </div>
        @if($viewingAsParent)
            <a href="{{ route('parent.dashboard') }}" class="child-btn child-btn--outline">Explore Parent Resources</a>
        @else
            <a href="{{ route('frontend.index') }}" class="child-btn child-btn--outline">Explore Learning Resources</a>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const toggle = document.getElementById('childSidebarToggle');
    const shell = document.querySelector('.child-portal-shell');
    toggle?.addEventListener('click', () => shell?.classList.toggle('sidebar-open'));

    document.querySelectorAll('.child-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.child-tab').forEach((t) => t.classList.remove('is-active'));
            tab.classList.add('is-active');
        });
    });
})();
</script>
@endpush
