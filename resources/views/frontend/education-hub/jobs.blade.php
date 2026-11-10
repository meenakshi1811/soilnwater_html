@extends('frontend.layouts.app')

@section('meta_title', 'Job Openings | Education Hub | SoilnWater')
@section('meta_description', 'Teaching and staff jobs at schools, colleges and institutes on SoilnWater.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/education-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="eh-page eh-notices-page">
    <div class="eh-container">
        <nav class="eh-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('education.hub') }}"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Education Hub</a>
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span aria-current="page">Job Openings</span>
        </nav>

        <header class="eh-notices-head">
            <span class="eh-icon eh-icon--green eh-notices-head__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></span>
            <div>
                <h1 class="eh-notices-head__title">Job Openings</h1>
                <p class="eh-notices-head__text">Teaching and staff jobs at the schools, colleges and institutes on SoilnWater. Open one to see the details and apply.</p>
            </div>
        </header>

        {{-- Who posted them --}}
        <nav class="eh-notice-tabs" aria-label="Jobs by who posted them">
            <a href="{{ route('education.jobs') }}" class="eh-notice-tab {{ $kind === null ? 'is-active' : '' }}" @if($kind === null) aria-current="page" @endif>
                All <span>{{ $counts['all'] ?? 0 }}</span>
            </a>
            @foreach($kinds as $key => [$label, $icon])
                <a href="{{ route('education.jobs', ['kind' => $key]) }}" class="eh-notice-tab {{ $kind === $key ? 'is-active' : '' }}" @if($kind === $key) aria-current="page" @endif>
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

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
            <p class="eh-empty eh-notices-empty">
                {{ $kind ? 'No open jobs at '.strtolower($kinds[$kind][0]).' right now.' : 'No open jobs right now.' }}
                <a href="{{ route('education.hub') }}">Back to the Education Hub</a>
            </p>
        @endif
    </div>
</div>
@endsection
