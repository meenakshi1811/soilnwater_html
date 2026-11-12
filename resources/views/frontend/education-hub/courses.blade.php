@extends('frontend.layouts.app')

@section('meta_title', 'Courses & Online Learning | Education Hub | SoilnWater')
@section('meta_description', 'Courses and programs offered by colleges, universities and institutes on SoilnWater.')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/institutes-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/education-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
{{-- Courses & Online Learning: the listing layout of the other pages (filters on the left, the
     results on the right); filtering, sorting and more courses on scrolling load without a reload --}}
<div class="vendors-page institutes-page courses-page" id="coursesPageRoot" data-index-url="{{ route('education.courses') }}">
  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Filters</h2>
          <p class="vendors-sidebar__lead">Find the right course.</p>
        </div>
        <button type="button" class="vendors-sidebar__reset" id="coursesResetFilters">Reset All</button>
      </div>

      <div class="vendors-filter-group">
        <label for="coursesFilterSearch">Search</label>
        <div class="vendors-filter-search-wrap">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
          <input id="coursesFilterSearch" class="form-control" type="search" placeholder="Course, subject or institution..." value="{{ $search }}" autocomplete="off">
        </div>
      </div>

      <div class="vendors-filter-group">
        <label for="coursesFilterKind">Offered by</label>
        <select id="coursesFilterKind" class="form-select">
          <option value="">All institutions</option>
          @foreach($kinds as $key => [$label])
            <option value="{{ $key }}" @selected($kind === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="vendors-filter-group">
        <label for="coursesFilterCity">City</label>
        <select id="coursesFilterCity" class="form-select">
          <option value="">All cities</option>
          @foreach($cities as $name)
            <option value="{{ $name }}" @selected($city === $name)>{{ $name }}</option>
          @endforeach
        </select>
      </div>

      <button type="button" class="btn btn-primary w-100" id="coursesApplyFilters">Apply filters</button>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero vendors-hero--compact">
        <div class="vendors-hero__intro">
          <a href="{{ route('education.hub') }}" class="vendors-hero__eyebrow courses-page__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            Education Hub
          </a>
          <h1>Courses &amp; Online Learning</h1>
          <p>Academic, skill and professional courses from colleges, universities and institutes.</p>
          @if($totals['courses'])
            <ul class="courses-page__stats">
              <li><i class="fa-solid fa-book-open" aria-hidden="true"></i> <strong>{{ number_format($totals['courses']) }}</strong> {{ $totals['courses'] === 1 ? 'course' : 'courses' }}</li>
              <li><i class="fa-solid fa-building-columns" aria-hidden="true"></i> <strong>{{ number_format($totals['providers']) }}</strong> {{ $totals['providers'] === 1 ? 'institution' : 'institutions' }}</li>
            </ul>
          @endif
        </div>
        <img src="{{ asset('assets/images/education-hub/art-courses.webp') }}" alt="" class="courses-page__art" width="92" height="110">
      </section>

      <section class="vendors-section" id="coursesAllSection">
        <div class="vendors-section__head">
          <h2><i class="fa-solid fa-border-all" aria-hidden="true"></i> All Courses</h2>
        </div>

        <div class="vendors-all__toolbar institutes-toolbar">
          <p id="coursesSummaryText" class="institutes-toolbar__summary mb-0">
            @if($courses->total())
              Showing 1 to {{ $courses->lastItem() }} of {{ number_format($courses->total()) }} courses
            @else
              No courses to show
            @endif
          </p>
          <div class="vendors-all__controls">
            <select id="coursesSort" class="form-select" aria-label="Sort courses">
              <option value="recommended" @selected($sort === 'recommended')>Recommended</option>
              <option value="course" @selected($sort === 'course')>Course name A–Z</option>
              <option value="institution" @selected($sort === 'institution')>Institution A–Z</option>
            </select>
          </div>
        </div>

        <div id="coursesGrid" class="vendors-grid institutes-grid courses-grid" data-next-page-url="{{ $courses->nextPageUrl() }}" aria-live="polite">
          @include('frontend.education-hub.partials.course-cards', ['courses' => $courses])
        </div>

        <p id="coursesLoadingText" class="courses-page__loading d-none" role="status">
          <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Loading courses…
        </p>
        <div id="coursesScrollSentinel" class="vendors-scroll-sentinel" aria-hidden="true"></div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/education-courses.js') }}?v={{ now()->timestamp }}"></script>
@endpush
