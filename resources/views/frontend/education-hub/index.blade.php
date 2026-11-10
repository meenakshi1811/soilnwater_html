@extends('frontend.layouts.app')
@section('meta_title', 'Education & Knowledge Hub | SoilnWater')
@section('meta_description', 'Study materials, teachers & tutors, schools, colleges, institutes, jobs and notices, all in one place on SoilnWater.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/education-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
    $categories = [
        ['label' => 'School Education', 'icon' => 'fa-school', 'tone' => 'blue', 'url' => route('schools.index')],
        ['label' => 'College & University', 'icon' => 'fa-graduation-cap', 'tone' => 'green', 'url' => route('colleges.index')],
        ['label' => 'Competitive Exams', 'icon' => 'fa-bullseye', 'tone' => 'purple', 'url' => route('institutes.index', ['type' => 'coaching'])],
        ['label' => 'Skill Development', 'icon' => 'fa-screwdriver-wrench', 'tone' => 'orange', 'url' => route('institutes.index', ['search' => 'skill'])],
        ['label' => 'Subjects', 'icon' => 'fa-atom', 'tone' => 'sky', 'url' => route('study-materials.library')],
        ['label' => 'Courses & Online Learning', 'icon' => 'fa-laptop-code', 'tone' => 'rose', 'url' => route('institutes.index')],
        ['label' => 'Scholarships & Opportunities', 'icon' => 'fa-trophy', 'tone' => 'amber', 'url' => route('education.jobs')],
        ['label' => 'Community & Discussion', 'icon' => 'fa-people-group', 'tone' => 'teal', 'url' => route('community.index')],
    ];

    $hubNav = [
        ['label' => 'Study Materials', 'icon' => 'fa-book-open', 'items' => [
            ['label' => 'Study Material Library', 'url' => route('study-materials.library')],
            ['label' => 'Notes', 'url' => route('study-materials.notes', ['material_type' => 'notes'])],
            ['label' => 'Question Papers', 'url' => route('study-materials.notes', ['material_type' => 'question_papers'])],
            ['label' => 'Solved Papers', 'url' => route('study-materials.notes', ['material_type' => 'sample_papers'])],
            ['label' => 'Worksheets', 'url' => route('study-materials.notes', ['material_type' => 'worksheets'])],
            ['label' => 'Assignments', 'url' => route('study-materials.notes', ['material_type' => 'assignments'])],
            ['label' => 'Reference Books', 'url' => route('study-materials.notes', ['material_type' => 'reference_books'])],
        ]],
        ['label' => 'Courses & Learning', 'icon' => 'fa-graduation-cap', 'items' => [
            ['label' => 'Coaching Institutes', 'url' => route('institutes.index', ['type' => 'coaching'])],
            ['label' => 'Colleges & Universities', 'url' => route('colleges.index')],
            ['label' => 'Skill Development', 'url' => route('institutes.index', ['search' => 'skill'])],
            ['label' => 'All Institutes', 'url' => route('institutes.index')],
        ]],
        ['label' => 'Teachers & Tutors', 'icon' => 'fa-chalkboard-user', 'url' => route('educator.index')],
        ['label' => 'Institutions', 'icon' => 'fa-building-columns', 'items' => [
            ['label' => 'Schools', 'url' => route('schools.index')],
            ['label' => 'Colleges', 'url' => route('colleges.index')],
            ['label' => 'Institutes', 'url' => route('institutes.index')],
        ]],
        ['label' => 'Jobs & Careers', 'icon' => 'fa-briefcase', 'url' => route('education.jobs')],
        ['label' => 'Projects & Innovation', 'icon' => 'fa-lightbulb', 'url' => route('community.index', ['type' => 'science-technology'])],
        ['label' => 'Community', 'icon' => 'fa-people-group', 'items' => [
            ['label' => 'Community Stories', 'url' => route('community.index')],
            ['label' => 'Ask a Question', 'url' => route('discussions.index')],
            ['label' => 'Student Corner', 'url' => route('community.index', ['type' => 'student-corner'])],
            ['label' => 'Career Guidance', 'url' => route('community.index', ['type' => 'career'])],
        ]],
    ];

    $heroChips = [
        ['label' => 'Study Materials', 'icon' => 'fa-book-open', 'url' => route('study-materials.notes')],
        ['label' => 'Find Teachers', 'icon' => 'fa-chalkboard-user', 'url' => route('educator.index')],
        ['label' => 'Find Institutions', 'icon' => 'fa-building-columns', 'url' => route('schools.index')],
        ['label' => 'Ask a Question', 'icon' => 'fa-circle-question', 'url' => route('discussions.index')],
    ];

    $features = [
        ['title' => 'Courses & Online Learning', 'text' => 'Academic, skill and professional courses.', 'icon' => 'fa-laptop-code', 'tone' => 'blue', 'button' => 'Explore Courses', 'url' => route('institutes.index')],
        ['title' => 'Jobs & Careers', 'text' => 'Jobs, internships, apprenticeships and teaching opportunities.', 'icon' => 'fa-briefcase', 'tone' => 'purple', 'button' => 'Explore Jobs', 'url' => route('education.jobs')],
        ['title' => 'Projects & Innovation', 'text' => 'Showcase science, engineering and creative projects.', 'icon' => 'fa-lightbulb', 'tone' => 'orange', 'button' => 'Explore Projects', 'url' => route('community.index', ['type' => 'science-technology'])],
        ['title' => 'Scholarships', 'text' => 'Find scholarships, fellowships and financial support.', 'icon' => 'fa-award', 'tone' => 'green', 'button' => 'View Scholarships', 'url' => route('community.index', ['type' => 'student-corner'])],
    ];

    // The hero's picture: a photo of students when one is added (hero-students.webp), else the student cut-out
    $heroPhoto = file_exists(public_path('assets/images/education-hub/hero-students.webp'))
        ? asset('assets/images/education-hub/hero-students.webp')
        : asset('assets/images/education-hub/hero-student-cutout.webp');

    // 1234 → 1.2K+, smaller numbers with a plus
    $statCount = fn (int $count): string => \App\Models\StudyMaterial::formatCompactCount($count).($count > 0 ? '+' : '');
@endphp

<div class="eh-page">
    {{-- The hub's menu: a green bar (one button that opens it on phones) --}}
    <nav class="eh-nav js-eh-nav" aria-label="Education hub">
        <div class="eh-container">
            <button type="button" class="eh-nav__toggle js-eh-nav-toggle" aria-expanded="false" aria-controls="ehNavList">
                <span class="eh-nav__toggle-label"><i class="fa-solid fa-bars" aria-hidden="true"></i> Education Hub menu</span>
                <i class="fa-solid fa-chevron-down eh-nav__caret" aria-hidden="true"></i>
            </button>
            <ul class="eh-nav__list" id="ehNavList">
                <li class="eh-nav__item">
                    <details class="eh-nav__dropdown">
                        <summary class="eh-nav__link eh-nav__link--all">
                            <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i>
                            <span>All Categories</span>
                            <i class="fa-solid fa-chevron-down eh-nav__caret" aria-hidden="true"></i>
                        </summary>
                        <ul class="eh-nav__menu">
                            @foreach($categories as $category)
                                <li><a href="{{ $category['url'] }}"><i class="fa-solid {{ $category['icon'] }}" aria-hidden="true"></i> {{ $category['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </details>
                </li>
                @foreach($hubNav as $item)
                    <li class="eh-nav__item">
                        @if(! empty($item['items']))
                            <details class="eh-nav__dropdown">
                                <summary class="eh-nav__link">
                                    <span>{{ $item['label'] }}</span>
                                    <i class="fa-solid fa-chevron-down eh-nav__caret" aria-hidden="true"></i>
                                </summary>
                                <ul class="eh-nav__menu">
                                    @foreach($item['items'] as $sub)
                                        <li><a href="{{ $sub['url'] }}">{{ $sub['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            </details>
                        @else
                            <a href="{{ $item['url'] }}" class="eh-nav__link"><span>{{ $item['label'] }}</span></a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    {{-- Hero: heading, search, quick links, a photo of students and the numbers --}}
    <section class="eh-hero" aria-labelledby="ehHeroTitle">
        <div class="eh-container eh-hero__inner">
            <div class="eh-hero__copy">
                <h1 class="eh-hero__title" id="ehHeroTitle">Your Learning Journey <span>Starts Here!</span></h1>
                <p class="eh-hero__text">Discover quality study materials, connect with teachers, explore institutions and build your future with SoilnWater.</p>

                <form class="eh-search" action="{{ route('education.search') }}" method="GET" role="search">
                    <i class="fa-solid fa-magnifying-glass eh-search__icon" aria-hidden="true"></i>
                    <label for="ehSearch" class="visually-hidden">Search the Education Hub</label>
                    <input type="search" id="ehSearch" name="q" class="eh-search__input" placeholder="Search notes, teachers, courses, schools, colleges, jobs..." maxlength="150" autocomplete="off">
                    <button type="submit" class="eh-search__btn">Search</button>
                </form>

                <div class="eh-chips">
                    @foreach($heroChips as $chip)
                        <a href="{{ $chip['url'] }}" class="eh-chip"><i class="fa-solid {{ $chip['icon'] }}" aria-hidden="true"></i> {{ $chip['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <div class="eh-hero__photo">
                <img src="{{ $heroPhoto }}" alt="Students learning together" width="520" height="420" fetchpriority="high">
            </div>

            <ul class="eh-stats" aria-label="On SoilnWater">
                @foreach($stats as $stat)
                    <li>
                        <a href="{{ $stat['url'] }}" class="eh-stat">
                            <span class="eh-icon eh-icon--square eh-icon--{{ $stat['tone'] }}"><i class="fa-solid {{ $stat['icon'] }}" aria-hidden="true"></i></span>
                            <span class="eh-stat__text">
                                <strong class="eh-tone--{{ $stat['tone'] }}">{{ $statCount($stat['count']) }}</strong>
                                <small>{{ $stat['label'] }}</small>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <div class="eh-container">
        {{-- Explore by Category --}}
        <section class="eh-section" aria-labelledby="ehCategoriesTitle">
            <div class="eh-section__head">
                <h2 class="eh-section__title" id="ehCategoriesTitle">Explore by Category</h2>
                <a href="{{ route('study-materials.library') }}" class="eh-section__more">View All Categories <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="eh-categories">
                @foreach($categories as $category)
                    <a href="{{ $category['url'] }}" class="eh-category eh-category--{{ $category['tone'] }}">
                        <span class="eh-category__icon"><i class="fa-solid {{ $category['icon'] }}" aria-hidden="true"></i></span>
                        <strong>{{ $category['label'] }}</strong>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Trending Study Materials: a tab per kind, six cards each --}}
        <section class="eh-section" aria-labelledby="ehTrendingTitle">
            <div class="eh-section__head">
                <h2 class="eh-section__title" id="ehTrendingTitle">Trending Study Materials</h2>
                <a href="{{ route('study-materials.notes', ['sort' => 'downloads']) }}" class="eh-section__more js-eh-trending-more">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="eh-tabs" role="tablist" aria-label="Kinds of study material">
                @foreach($trendingTabs as $tab)
                    <button type="button" class="eh-tab {{ $loop->first ? 'is-active' : '' }}" role="tab" id="ehTab-{{ $tab['key'] }}" aria-controls="ehPanel-{{ $tab['key'] }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-more-url="{{ $tab['url'] }}" @unless($loop->first) tabindex="-1" @endunless>
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>
            @foreach($trendingTabs as $tab)
                <div class="eh-tabpanel" role="tabpanel" id="ehPanel-{{ $tab['key'] }}" aria-labelledby="ehTab-{{ $tab['key'] }}" @unless($loop->first) hidden @endunless>
                    @if($tab['cards'] !== [])
                        <div class="eh-materials">
                            @foreach($tab['cards'] as $card)
                                <a href="{{ $card['url'] }}" class="eh-material">
                                    <span class="eh-material__media eh-material__media--{{ $card['file']['tone'] }}">
                                        @if($card['image'])
                                            <img src="{{ $card['image'] }}" alt="" loading="lazy" decoding="async">
                                        @else
                                            <i class="fa-solid {{ $card['file']['icon'] }}" aria-hidden="true"></i>
                                        @endif
                                    </span>
                                    <span class="eh-material__body">
                                        <span class="eh-material__head">
                                            <span class="eh-material__file eh-material__file--{{ $card['file']['tone'] }}" title="{{ $card['file']['label'] }}"><i class="fa-solid {{ $card['file']['icon'] }}" aria-hidden="true"></i></span>
                                            <strong class="eh-material__title">{{ $card['title'] }}</strong>
                                        </span>
                                        <span class="eh-material__meta">{{ $card['meta'] }}</span>
                                        <span class="eh-material__foot">
                                            <span><i class="fa-regular fa-eye" aria-hidden="true"></i> {{ $card['views'] }} views</span>
                                            <span><i class="fa-solid fa-download" aria-hidden="true"></i> {{ $card['downloads'] }}</span>
                                        </span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="eh-empty">{{ $tab['label'] }} will appear here soon.</p>
                    @endif
                </div>
            @endforeach
        </section>

        {{-- Teachers, institutions, announcements --}}
        <div class="eh-columns">
            <section class="eh-card eh-panel" aria-labelledby="ehTeachersTitle">
                <div class="eh-panel__head">
                    <h2 class="eh-panel__title" id="ehTeachersTitle"><i class="fa-solid fa-user-graduate" aria-hidden="true"></i> Top Teachers &amp; Tutors</h2>
                    <a href="{{ route('educator.index') }}" class="eh-panel__more">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($topTeachers !== [])
                    <ul class="eh-people">
                        @foreach($topTeachers as $teacher)
                            <li class="eh-person">
                                <img src="{{ $teacher['photo'] }}" alt="" class="eh-person__photo" loading="lazy" width="48" height="48">
                                <span class="eh-person__body">
                                    <a href="{{ $teacher['url'] }}" class="eh-person__name">
                                        {{ $teacher['name'] }}
                                        @if($teacher['verified'])
                                            <i class="fa-solid fa-circle-check" aria-label="Verified"></i>
                                        @endif
                                    </a>
                                    <small>{{ $teacher['subject'] }}</small>
                                    @if($teacher['detail'] !== '')
                                        <small>{{ $teacher['detail'] }}</small>
                                    @endif
                                    @if($teacher['rating'] > 0)
                                        <span class="eh-person__rating"><i class="fa-solid fa-star" aria-hidden="true"></i> {{ number_format($teacher['rating'], 1) }}</span>
                                    @else
                                        <span class="eh-person__rating eh-person__rating--new">New</span>
                                    @endif
                                </span>
                                <a href="{{ $teacher['url'] }}" class="eh-btn-sm eh-btn-sm--outline">Contact</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="eh-empty">Teachers will appear here soon.</p>
                @endif
            </section>

            <section class="eh-card eh-panel" aria-labelledby="ehInstitutionsTitle">
                <div class="eh-panel__head">
                    <h2 class="eh-panel__title" id="ehInstitutionsTitle"><i class="fa-solid fa-building-columns" aria-hidden="true"></i> Educational Institutions</h2>
                    <a href="{{ route('schools.index') }}" class="eh-panel__more">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <ul class="eh-places">
                    @foreach($institutionRows as $row)
                        <li>
                            <a href="{{ $row['url'] }}" class="eh-place">
                                <span class="eh-place__photo eh-place__photo--{{ $row['tone'] }}">
                                    @if($row['image'])
                                        <img src="{{ $row['image'] }}" alt="" loading="lazy">
                                    @else
                                        <i class="fa-solid {{ $row['icon'] }}" aria-hidden="true"></i>
                                    @endif
                                </span>
                                <span class="eh-place__body">
                                    <strong>{{ $row['title'] }}</strong>
                                    <small>{{ $row['hint'] }}</small>
                                    <small class="eh-place__count">{{ number_format($row['count']) }} listed</small>
                                </span>
                                <i class="fa-solid fa-chevron-right eh-place__arrow" aria-hidden="true"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="eh-card eh-panel" aria-labelledby="ehAnnouncementsTitle">
                <div class="eh-panel__head">
                    <h2 class="eh-panel__title" id="ehAnnouncementsTitle"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Announcements &amp; Updates</h2>
                    <a href="{{ route('education.notices') }}" class="eh-panel__more">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($announcements->isNotEmpty())
                    {{-- All active notices moving upward (more than four; paused on hover); the list twice so it loops --}}
                    @php $tickerMoves = $announcements->count() > 4; @endphp
                    <div class="eh-ticker {{ $tickerMoves ? 'is-moving' : '' }}" style="--eh-ticker-time: {{ max(12, $announcements->count() * 4) }}s">
                        <div class="eh-ticker__track">
                            <ul class="eh-announcements">
                                @foreach($announcements as $announcement)
                                    @include('frontend.education-hub.partials.announcement-item', ['announcement' => $announcement])
                                @endforeach
                            </ul>
                            @if($tickerMoves)
                                <ul class="eh-announcements eh-ticker__copy" aria-hidden="true">
                                    @foreach($announcements as $announcement)
                                        @include('frontend.education-hub.partials.announcement-item', ['announcement' => $announcement, 'copy' => true])
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="eh-empty">No announcements right now.</p>
                @endif
            </section>
        </div>

        {{-- Courses, jobs, projects, scholarships --}}
        <div class="eh-features">
            @foreach($features as $feature)
                <a href="{{ $feature['url'] }}" class="eh-feature eh-feature--{{ $feature['tone'] }}">
                    <span class="eh-feature__art"><i class="fa-solid {{ $feature['icon'] }}" aria-hidden="true"></i></span>
                    <span class="eh-feature__body">
                        <strong>{{ $feature['title'] }}</strong>
                        <small>{{ $feature['text'] }}</small>
                        <span class="eh-feature__btn">{{ $feature['button'] }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Ask & discuss, join the community --}}
        <div class="eh-banners">
            <div class="eh-banner eh-banner--green">
                <span class="eh-banner__art"><i class="fa-solid fa-comments" aria-hidden="true"></i></span>
                <span class="eh-banner__body">
                    <strong>Ask &amp; Discuss <span>— Learn Together</span></strong>
                    <small>Ask academic questions, get expert answers, share ideas and discuss with the community.</small>
                </span>
                <a href="{{ route('discussions.index') }}" class="eh-banner__btn">Ask a Question <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="eh-banner eh-banner--blue">
                <span class="eh-banner__art"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
                <span class="eh-banner__body">
                    <strong>Join Our Learning Community</strong>
                    <small>Students • Parents • Teachers • Professionals • Hobbyists</small>
                </span>
                <a href="{{ auth()->check() ? route('community.index') : route('register') }}" class="eh-banner__btn">Join Community <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        // Hub menus: one open at a time, closed by a click elsewhere or Escape, kept on screen
        var menus = document.querySelectorAll('.eh-nav__dropdown');
        menus.forEach(function (menu) {
            menu.addEventListener('toggle', function () {
                if (!menu.open) {
                    return;
                }
                menus.forEach(function (other) {
                    if (other !== menu) {
                        other.open = false;
                    }
                });
                var list = menu.querySelector('.eh-nav__menu');
                list.style.left = '';
                var overflow = list.getBoundingClientRect().right - (window.innerWidth - 8);
                if (overflow > 0 && getComputedStyle(list).position === 'absolute') {
                    list.style.left = (-overflow) + 'px';
                }
            });
        });
        document.addEventListener('click', function (event) {
            menus.forEach(function (menu) {
                if (menu.open && !menu.contains(event.target)) {
                    menu.open = false;
                }
            });
        });

        // Phones: the menu button opens and closes the whole menu
        var nav = document.querySelector('.js-eh-nav');
        var toggle = nav ? nav.querySelector('.js-eh-nav-toggle') : null;
        var setOpen = function (open) {
            if (!toggle) {
                return;
            }
            nav.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        if (toggle) {
            toggle.addEventListener('click', function () {
                setOpen(!nav.classList.contains('is-open'));
            });
            document.addEventListener('click', function (event) {
                if (nav.classList.contains('is-open') && !nav.contains(event.target)) {
                    setOpen(false);
                }
            });
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                menus.forEach(function (menu) {
                    menu.open = false;
                });
                setOpen(false);
            }
        });

        // Trending tabs: show a kind's cards; View All follows the tab
        var tabs = document.querySelectorAll('.eh-tab');
        var more = document.querySelector('.js-eh-trending-more');
        var select = function (tab) {
            tabs.forEach(function (other) {
                var on = other === tab;
                other.classList.toggle('is-active', on);
                other.setAttribute('aria-selected', on ? 'true' : 'false');
                other.tabIndex = on ? 0 : -1;
                document.getElementById(other.getAttribute('aria-controls')).hidden = !on;
            });
            if (more && tab.dataset.moreUrl) {
                more.href = tab.dataset.moreUrl;
            }
        };
        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                select(tab);
            });
            tab.addEventListener('keydown', function (event) {
                var step = event.key === 'ArrowRight' ? 1 : (event.key === 'ArrowLeft' ? -1 : 0);
                if (step) {
                    var next = tabs[(index + step + tabs.length) % tabs.length];
                    select(next);
                    next.focus();
                }
            });
        });
    })();
</script>
@endpush
