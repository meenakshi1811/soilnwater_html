@extends('frontend.layouts.app')

@section('meta_title', 'Courses & Online Learning | Education Hub | SoilnWater')
@section('meta_description', 'Courses and programs offered by colleges, universities and institutes on SoilnWater.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/education-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="eh-page eh-notices-page eh-listpage">
    <div class="eh-container">
        <nav class="eh-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('education.hub') }}"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Education Hub</a>
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span aria-current="page">Courses &amp; Online Learning</span>
        </nav>

        @include('frontend.education-hub.partials.page-hero', [
            'tone' => 'blue',
            'art' => 'courses',
            'title' => 'Courses & Online Learning',
            'text' => 'Academic, skill and professional courses from colleges, universities and institutes. Find one that fits and see the details on its page.',
            'action' => route('education.courses'),
            'placeholder' => 'Search courses, subjects or institutions…',
            'stats' => array_values(array_filter([
                $totals['courses'] ? ['fa-book-open', $totals['courses'], $totals['courses'] === 1 ? 'course' : 'courses'] : null,
                $totals['providers'] ? ['fa-building-columns', $totals['providers'], $totals['providers'] === 1 ? 'institution' : 'institutions'] : null,
            ])),
        ])

        {{-- Who offers them --}}
        <nav class="eh-notice-tabs eh-listtabs" aria-label="Courses by who offers them">
            <a href="{{ route('education.courses', array_filter(['q' => $search])) }}" class="eh-notice-tab {{ $kind === null ? 'is-active' : '' }}" @if($kind === null) aria-current="page" @endif>
                All <span>{{ $counts['all'] ?? 0 }}</span>
            </a>
            @foreach($kinds as $key => [$label, $icon])
                <a href="{{ route('education.courses', array_filter(['kind' => $key, 'q' => $search])) }}" class="eh-notice-tab {{ $kind === $key ? 'is-active' : '' }}" @if($kind === $key) aria-current="page" @endif>
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        @if($search !== '')
            <p class="eh-listnote">
                {{ number_format($courses->total()) }} {{ $courses->total() === 1 ? 'course' : 'courses' }} for “{{ $search }}”
                <a href="{{ route('education.courses', array_filter(['kind' => $kind])) }}">Clear search <i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
            </p>
        @endif

        @if($courses->isNotEmpty())
            <div class="eh-courses">
                @foreach($courses as $course)
                    @include('frontend.education-hub.partials.course-card', ['course' => $course])
                @endforeach
            </div>

            @if($courses->hasPages())
                <div class="eh-notice-pages">
                    {{ $courses->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <div class="eh-listempty">
                <img src="{{ asset('assets/images/education-hub/art-courses.webp') }}" alt="" width="70" height="84">
                <strong>
                    @if($search !== '')
                        No courses match “{{ $search }}”.
                    @elseif($kind)
                        No courses from {{ strtolower($kinds[$kind][0]) }} yet.
                    @else
                        No courses yet.
                    @endif
                </strong>
                <span>Colleges, universities and institutes add their courses on their profile; they will appear here.</span>
                <a href="{{ $search !== '' || $kind ? route('education.courses') : route('education.hub') }}" class="eh-listempty__btn">
                    {{ $search !== '' || $kind ? 'See all courses' : 'Back to the Education Hub' }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
