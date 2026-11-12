@extends('frontend.layouts.app')

@section('meta_title', 'Jobs & Careers | Education Hub | SoilnWater')
@section('meta_description', 'Teaching and staff jobs at schools, colleges and institutes on SoilnWater.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/education-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="eh-page eh-notices-page eh-listpage">
    <div class="eh-container">
        <nav class="eh-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('education.hub') }}"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Education Hub</a>
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span aria-current="page">Jobs &amp; Careers</span>
        </nav>

        @include('frontend.education-hub.partials.page-hero', [
            'tone' => 'purple',
            'art' => 'jobs',
            'title' => 'Jobs & Careers',
            'text' => 'Teaching and staff jobs, internships and apprenticeships at the schools, colleges and institutes on SoilnWater. Open one to see the details and apply.',
            'action' => route('education.jobs'),
            'placeholder' => 'Search jobs, subjects, institutions or places…',
            'stats' => array_values(array_filter([
                $totals['jobs'] ? ['fa-briefcase', $totals['jobs'], $totals['jobs'] === 1 ? 'open job' : 'open jobs'] : null,
                $totals['employers'] ? ['fa-building-columns', $totals['employers'], $totals['employers'] === 1 ? 'employer' : 'employers'] : null,
            ])),
        ])

        {{-- Who posted them --}}
        <nav class="eh-notice-tabs eh-listtabs" aria-label="Jobs by who posted them">
            <a href="{{ route('education.jobs', array_filter(['q' => $search])) }}" class="eh-notice-tab {{ $kind === null ? 'is-active' : '' }}" @if($kind === null) aria-current="page" @endif>
                All <span>{{ $counts['all'] ?? 0 }}</span>
            </a>
            @foreach($kinds as $key => [$label, $icon])
                <a href="{{ route('education.jobs', array_filter(['kind' => $key, 'q' => $search])) }}" class="eh-notice-tab {{ $kind === $key ? 'is-active' : '' }}" @if($kind === $key) aria-current="page" @endif>
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        @if($search !== '')
            <p class="eh-listnote">
                {{ number_format($jobs->total()) }} {{ $jobs->total() === 1 ? 'job' : 'jobs' }} for “{{ $search }}”
                <a href="{{ route('education.jobs', array_filter(['kind' => $kind])) }}">Clear search <i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
            </p>
        @endif

        @if($jobs->isNotEmpty())
            <div class="eh-jobs">
                @foreach($jobs as $job)
                    @include('frontend.education-hub.partials.job-card', ['job' => $job])
                @endforeach
            </div>

            @if($jobs->hasPages())
                <div class="eh-notice-pages">
                    {{ $jobs->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <div class="eh-listempty">
                <img src="{{ asset('assets/images/education-hub/art-jobs.webp') }}" alt="" width="70" height="63">
                <strong>
                    @if($search !== '')
                        No jobs match “{{ $search }}”.
                    @elseif($kind)
                        No open jobs at {{ strtolower($kinds[$kind][0]) }} right now.
                    @else
                        No open jobs right now.
                    @endif
                </strong>
                <span>Schools, colleges and institutes post their openings here. Check back soon.</span>
                <a href="{{ $search !== '' || $kind ? route('education.jobs') : route('education.hub') }}" class="eh-listempty__btn">
                    {{ $search !== '' || $kind ? 'See all jobs' : 'Back to the Education Hub' }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
