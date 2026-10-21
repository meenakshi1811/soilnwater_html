@extends('frontend.layouts.app')
@section('meta_title', 'Education & Knowledge Hub | SoilnWater')
@section('meta_description', 'Study materials, teachers & tutors, schools, colleges and institutes, all in one place on SoilnWater.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/education-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
    $hubNav = [
        ['label' => 'Home', 'icon' => 'fa-house', 'url' => route('education.hub'), 'active' => true],
        ['label' => 'Study Materials', 'icon' => 'fa-book-open', 'items' => [
            ['label' => 'Study Material Library', 'url' => route('study-materials.library')],
            ['label' => 'Notes', 'url' => route('study-materials.notes', ['material_type' => 'notes'])],
            ['label' => 'Question Papers', 'url' => route('study-materials.notes', ['material_type' => 'question_papers'])],
            ['label' => 'Sample Papers', 'url' => route('study-materials.notes', ['material_type' => 'sample_papers'])],
            ['label' => 'Worksheets', 'url' => route('study-materials.notes', ['material_type' => 'worksheets'])],
        ]],
        ['label' => 'Courses', 'icon' => 'fa-graduation-cap', 'items' => [
            ['label' => 'Coaching Institutes', 'url' => route('institutes.index', ['type' => 'coaching'])],
            ['label' => 'Colleges', 'url' => route('institutes.index', ['type' => 'college'])],
            ['label' => 'Universities', 'url' => route('institutes.index', ['type' => 'university'])],
            ['label' => 'All Institutes', 'url' => route('institutes.index')],
        ]],
        ['label' => 'Teachers & Tutors', 'icon' => 'fa-chalkboard-user', 'url' => route('educator.index')],
        ['label' => 'Schools & Colleges', 'icon' => 'fa-school', 'url' => route('schools.index')],
        ['label' => 'Community', 'icon' => 'fa-people-group', 'items' => [
            ['label' => 'Community Stories', 'url' => route('community.index')],
            ['label' => 'Ask a Question', 'url' => route('discussions.index')],
        ]],
        ['label' => 'Careers', 'icon' => 'fa-briefcase', 'items' => [
            ['label' => 'Jobs & Employment', 'url' => route('community.index', ['type' => 'jobs-employment'])],
            ['label' => 'Career Guidance', 'url' => route('community.index', ['type' => 'career'])],
        ]],
        ['label' => 'More', 'icon' => 'fa-ellipsis', 'items' => [
            ['label' => 'All Institutes', 'url' => route('institutes.index')],
            ['label' => 'All Schools', 'url' => route('schools.index')],
            ['label' => 'SoilnWater Home', 'url' => route('frontend.index')],
        ]],
    ];

    $quickLinks = [
        ['label' => 'Find Study Materials', 'hint' => 'Notes, Papers, Worksheets', 'icon' => 'fa-book-open', 'tone' => 'blue', 'url' => route('study-materials.notes')],
        ['label' => 'Ask a Question', 'hint' => 'Get help from experts', 'icon' => 'fa-circle-question', 'tone' => 'green', 'url' => route('discussions.index')],
        ['label' => 'Find Courses', 'hint' => 'Upskill & learn new things', 'icon' => 'fa-graduation-cap', 'tone' => 'purple', 'url' => route('institutes.index')],
        ['label' => 'Find Teachers / Tutors', 'hint' => 'Connect with experts', 'icon' => 'fa-chalkboard-user', 'tone' => 'orange', 'url' => route('educator.index')],
        ['label' => 'Explore Schools & Colleges', 'hint' => 'Discover institutions', 'icon' => 'fa-school', 'tone' => 'pink', 'url' => route('schools.index')],
        ['label' => 'Find Jobs & Opportunities', 'hint' => 'Jobs, internships & more', 'icon' => 'fa-briefcase', 'tone' => 'teal', 'url' => route('community.index', ['type' => 'jobs-employment'])],
    ];

    $categories = [
        ['label' => 'School Education', 'hint' => 'Classes 1 – 12', 'icon' => 'fa-school', 'tone' => 'blue', 'url' => route('schools.index')],
        ['label' => 'College Education', 'hint' => 'UG, PG & Diploma', 'icon' => 'fa-building-columns', 'tone' => 'green', 'url' => route('institutes.index', ['type' => 'college'])],
        ['label' => 'Competitive Exams', 'hint' => 'Govt. & Entrance', 'icon' => 'fa-award', 'tone' => 'purple', 'url' => route('institutes.index', ['type' => 'coaching'])],
        ['label' => 'Skill Development', 'hint' => 'Learn New Skills', 'icon' => 'fa-user-gear', 'tone' => 'orange', 'url' => route('institutes.index', ['search' => 'skill'])],
        ['label' => 'Languages', 'hint' => 'Learn Languages', 'icon' => 'fa-language', 'tone' => 'teal', 'url' => route('study-materials.notes', ['q' => 'language'])],
        ['label' => 'Engineering', 'hint' => 'All Branches', 'icon' => 'fa-gears', 'tone' => 'blue', 'url' => route('study-materials.notes', ['q' => 'engineering'])],
        ['label' => 'Medical', 'hint' => 'NEET & More', 'icon' => 'fa-stethoscope', 'tone' => 'pink', 'url' => route('study-materials.notes', ['q' => 'medical'])],
        ['label' => 'Management', 'hint' => 'MBA & More', 'icon' => 'fa-chart-line', 'tone' => 'purple', 'url' => route('study-materials.notes', ['q' => 'management'])],
        ['label' => 'More', 'hint' => 'Categories', 'icon' => 'fa-ellipsis', 'tone' => 'slate', 'url' => route('study-materials.library')],
    ];

    $announcementIcons = [['fa-bullhorn', 'blue'], ['fa-calendar-days', 'purple'], ['fa-graduation-cap', 'green']];

    // 1234 → 1.2K+, small numbers as they are
    $statCount = fn (int $count): string => \App\Models\StudyMaterial::formatCompactCount($count).($count >= 1000 ? '+' : '');
@endphp

<div class="eh-page">
    {{-- Hub navigation --}}
    <nav class="eh-nav" aria-label="Education hub">
        <div class="eh-container">
            <ul class="eh-nav__list">
                @foreach($hubNav as $item)
                    <li class="eh-nav__item">
                        @if(! empty($item['items']))
                            <details class="eh-nav__dropdown">
                                <summary class="eh-nav__link">
                                    <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
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
                            <a href="{{ $item['url'] }}" class="eh-nav__link {{ ! empty($item['active']) ? 'is-active' : '' }}" @if(! empty($item['active'])) aria-current="page" @endif>
                                <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    <div class="eh-container">
        {{-- Hero --}}
        <section class="eh-hero" aria-labelledby="ehHeroTitle">
            <div class="eh-hero__main">
                <div class="eh-hero__copy">
                    <h1 class="eh-hero__title" id="ehHeroTitle">Your Learning Journey <span>Starts Here!</span></h1>
                    <p class="eh-hero__text">Discover quality study materials, expert teachers, top institutions and great opportunities – all in one place.</p>
                    <div class="eh-hero__actions">
                        <a href="{{ route('study-materials.notes') }}" class="eh-btn eh-btn--primary">
                            <i class="fa-solid fa-book-open" aria-hidden="true"></i> Explore Study Materials
                        </a>
                        <a href="{{ route('educator.index') }}" class="eh-btn eh-btn--outline">
                            <i class="fa-regular fa-user" aria-hidden="true"></i> Find Teachers
                        </a>
                    </div>

                    <div class="eh-stats">
                        @foreach($stats as $stat)
                            @php $tag = $stat['url'] ? 'a' : 'div'; @endphp
                            <{{ $tag }} class="eh-stat" @if($stat['url']) href="{{ $stat['url'] }}" @endif>
                                <span class="eh-icon eh-icon--{{ $stat['tone'] }}"><i class="fa-solid {{ $stat['icon'] }}" aria-hidden="true"></i></span>
                                <span>
                                    <strong class="eh-stat__count eh-tone--{{ $stat['tone'] }}">{{ $statCount($stat['count']) }}</strong>
                                    <small>{{ $stat['label'] }}</small>
                                </span>
                            </{{ $tag }}>
                        @endforeach
                    </div>
                </div>

                <img class="eh-hero__image" src="{{ asset('assets/images/education-hub/hero-student.webp') }}" alt="" width="328" height="348" fetchpriority="high">
            </div>

            <aside class="eh-card eh-quick" aria-labelledby="ehQuickTitle">
                <h2 class="eh-quick__title" id="ehQuickTitle">I want to…</h2>
                <ul class="eh-quick__list">
                    @foreach($quickLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="eh-quick__link">
                                <span class="eh-icon eh-icon--{{ $link['tone'] }}"><i class="fa-solid {{ $link['icon'] }}" aria-hidden="true"></i></span>
                                <span class="eh-quick__text">
                                    <strong>{{ $link['label'] }}</strong>
                                    <small>{{ $link['hint'] }}</small>
                                </span>
                                <i class="fa-solid fa-chevron-right eh-quick__arrow" aria-hidden="true"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>
        </section>

        {{-- Explore by category --}}
        <section class="eh-section" aria-labelledby="ehCategoriesTitle">
            <div class="eh-section__head">
                <h2 class="eh-section__title" id="ehCategoriesTitle">Explore by Category</h2>
                <a href="{{ route('study-materials.library') }}" class="eh-section__more">View all categories</a>
            </div>
            <div class="eh-categories">
                @foreach($categories as $category)
                    <a href="{{ $category['url'] }}" class="eh-category">
                        <span class="eh-icon eh-icon--lg eh-icon--{{ $category['tone'] }}"><i class="fa-solid {{ $category['icon'] }}" aria-hidden="true"></i></span>
                        <strong>{{ $category['label'] }}</strong>
                        <small>{{ $category['hint'] }}</small>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Trending materials, top teachers, announcements --}}
        <div class="eh-panels">
            <section class="eh-card eh-panel" aria-labelledby="ehTrendingTitle">
                <div class="eh-panel__head">
                    <h2 class="eh-panel__title" id="ehTrendingTitle"><i class="fa-solid fa-fire eh-tone--orange" aria-hidden="true"></i> Trending Study Materials</h2>
                    <a href="{{ route('study-materials.notes', ['sort' => 'downloads']) }}" class="eh-panel__more">View all</a>
                </div>
                @if($trendingMaterials->isNotEmpty())
                    <div class="eh-materials">
                        @foreach($trendingMaterials as $material)
                            @php $fileMeta = $material->fileTypeMeta(); @endphp
                            <a href="{{ $material->publicUrl() }}" class="eh-material">
                                <span class="eh-material__file eh-material__file--{{ $fileMeta['tone'] }}"><i class="fa-solid {{ $fileMeta['icon'] }}" aria-hidden="true"></i></span>
                                <span class="eh-material__body">
                                    <strong class="eh-material__title">{{ $material->title }}</strong>
                                    <span class="eh-material__type">{{ $material->subject ?: $material->materialTypeLabel() }}</span>
                                    <span class="eh-material__by">By: {{ $material->educator?->display_name ?: $material->user?->name ?: 'SoilnWater' }}</span>
                                </span>
                                <span class="eh-material__meta">
                                    <span title="Views"><i class="fa-regular fa-eye" aria-hidden="true"></i> {{ \App\Models\StudyMaterial::formatCompactCount($material->views_count) }}</span>
                                    <i class="fa-solid fa-download" aria-hidden="true" title="Download"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="eh-empty">Study materials will appear here soon.</p>
                @endif
            </section>

            <section class="eh-card eh-panel" aria-labelledby="ehTeachersTitle">
                <div class="eh-panel__head">
                    <h2 class="eh-panel__title" id="ehTeachersTitle"><i class="fa-solid fa-medal eh-tone--orange" aria-hidden="true"></i> Top Rated Teachers</h2>
                    <a href="{{ route('educator.index') }}" class="eh-panel__more">View all</a>
                </div>
                @if($topTeachers->isNotEmpty())
                    <div class="eh-teachers">
                        @foreach($topTeachers as $teacher)
                            @php $rating = (float) $teacher->average_rating; @endphp
                            <a href="{{ $teacher->publicUrl() }}" class="eh-teacher">
                                <img src="{{ $teacher->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="" class="eh-teacher__photo" loading="lazy" width="44" height="44">
                                <span class="eh-teacher__body">
                                    <strong>{{ $teacher->display_name ?: $teacher->roleLabel() }}</strong>
                                    <small>{{ $teacher->primarySubject() ?: $teacher->professional_headline ?: $teacher->roleLabel() }}</small>
                                    @if($rating > 0)
                                        <span class="eh-stars" aria-label="Rated {{ number_format($rating, 1) }} out of 5">
                                            @for($star = 1; $star <= 5; $star++)
                                                <i class="fa-solid fa-star{{ $star <= round($rating) ? '' : ' is-empty' }}" aria-hidden="true"></i>
                                            @endfor
                                            <span>{{ number_format($rating, 1) }}</span>
                                        </span>
                                    @else
                                        <span class="eh-stars eh-stars--new">New</span>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="eh-empty">Teachers will appear here soon.</p>
                @endif
            </section>

            <section class="eh-card eh-panel" aria-labelledby="ehAnnouncementsTitle">
                <div class="eh-panel__head">
                    <h2 class="eh-panel__title" id="ehAnnouncementsTitle"><i class="fa-solid fa-bullhorn eh-tone--blue" aria-hidden="true"></i> Announcements &amp; Events</h2>
                    <a href="{{ route('schools.index') }}" class="eh-panel__more">View all</a>
                </div>
                @if($announcements->isNotEmpty())
                    <ul class="eh-announcements">
                        @foreach($announcements as $announcement)
                            @php [$icon, $tone] = $announcementIcons[$loop->index % count($announcementIcons)]; @endphp
                            <li>
                                <a href="{{ $announcement['url'] }}" class="eh-announcement">
                                    <span class="eh-icon eh-icon--square eh-icon--{{ $tone }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                                    <span class="eh-announcement__body">
                                        <strong>{{ $announcement['title'] }}</strong>
                                        <small>{{ $announcement['source'] }}</small>
                                    </span>
                                    @if($announcement['date'])
                                        <time class="eh-announcement__date" datetime="{{ $announcement['date']->toDateString() }}">{{ $announcement['date']->format('j M Y') }}</time>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="eh-empty">No announcements right now.</p>
                @endif
            </section>
        </div>

        {{-- Schools, colleges and institutes --}}
        <section class="eh-section" aria-labelledby="ehInstitutionsTitle">
            <div class="eh-section__head">
                <h2 class="eh-section__title" id="ehInstitutionsTitle">Schools, Colleges &amp; Institutes</h2>
                <a href="{{ route('institutes.index') }}" class="eh-section__more">View all institutions</a>
            </div>

            <div class="eh-inst-groups">
                @foreach($institutionGroups as $group)
                    <div class="eh-card eh-inst-group">
                        <div class="eh-inst-group__head">
                            <span class="eh-icon eh-icon--square eh-icon--{{ $group['tone'] }}"><i class="fa-solid {{ $group['icon'] }}" aria-hidden="true"></i></span>
                            <div class="eh-inst-group__heading">
                                <h3 class="eh-inst-group__title">{{ $group['title'] }}</h3>
                                <small>{{ $group['hint'] }} · {{ number_format($group['count']) }} listed</small>
                            </div>
                            <a href="{{ $group['url'] }}" class="eh-panel__more">View all</a>
                        </div>

                        @if($group['items']->isNotEmpty())
                            <div class="eh-inst-grid">
                                @foreach($group['items'] as $institution)
                                    @php
                                        $gallery = is_array($institution->gallery) ? array_values(array_filter($institution->gallery)) : [];
                                        $logo = $institution->logoUrl();
                                        // A JPEG "logo" is nearly always a photo (e.g. of the building): use it as the cover
                                        $logoIsPhoto = $logo && preg_match('/\.jpe?g$/i', (string) $institution->logo);
                                        $cover = $gallery !== [] ? \App\Support\ImageThumbnail::url(asset($gallery[0])) : ($logoIsPhoto ? \App\Support\ImageThumbnail::url($logo) : null);
                                        if ($logoIsPhoto) {
                                            $logo = null;
                                        }
                                        $kind = collect([$group['noun'] === 'school' ? 'School' : $institution->institutionTypeLabel(), $institution->board_affiliation])->filter()->implode(' · ');
                                        $place = $institution->locationLabel();
                                    @endphp
                                    <a href="{{ $institution->publicUrl() }}" class="eh-inst {{ $cover && $logo ? 'eh-inst--with-logo' : '' }}">
                                        <span class="eh-inst__media eh-inst__media--{{ $group['tone'] }}">
                                            @if($cover)
                                                <img src="{{ $cover }}" alt="" class="eh-inst__cover" loading="lazy">
                                            @elseif($logo)
                                                <img src="{{ $logo }}" alt="" class="eh-inst__logo-only" loading="lazy">
                                            @else
                                                <i class="fa-solid {{ $group['icon'] }}" aria-hidden="true"></i>
                                            @endif
                                            @if($cover && $logo)
                                                <img src="{{ $logo }}" alt="" class="eh-inst__logo" loading="lazy" width="40" height="40">
                                            @endif
                                            @if($institution->is_verified)
                                                <span class="eh-inst__verified"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                                            @endif
                                        </span>
                                        <span class="eh-inst__body">
                                            <strong class="eh-inst__name">{{ $institution->publicDisplayName() }}</strong>
                                            @if($kind !== '')
                                                <span class="eh-inst__kind">{{ $kind }}</span>
                                            @endif
                                            @if($place !== '')
                                                <span class="eh-inst__place"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $place }}</span>
                                            @endif
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="eh-empty">No {{ $group['noun'] }}s listed yet.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Hub menus: one open at a time, closed by a click elsewhere or Escape, kept on screen
    (function () {
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
                if (overflow > 0) {
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

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                menus.forEach(function (menu) {
                    menu.open = false;
                });
            }
        });
    })();
</script>
@endpush
