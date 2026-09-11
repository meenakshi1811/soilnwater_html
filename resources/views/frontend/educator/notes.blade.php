@extends('frontend.layouts.app')

@section('meta_title', $educator->display_name.' · Notes & Materials | SoilnWater')
@section('meta_description', 'Browse all notes and study materials published by '.$educator->display_name.'.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/educator-module.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/educator-profile.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/educator-courses.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/educator-notes.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div
  class="edu-courses-page edu-notes-page"
  id="eduNotesPage"
  data-notes-url="{{ route('educator.notes', $educator->slug) }}"
  data-login-url="{{ route('login') }}"
  data-is-auth="{{ auth()->check() ? '1' : '0' }}"
>
  <div class="container-fluid edu-container">
    <nav class="edu-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <span class="edu-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route('educator.index') }}">Teachers &amp; Tutors</a>
      <span class="edu-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ $educator->publicUrl() }}">{{ $educator->display_name }}</a>
      <span class="edu-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="edu-breadcrumb__current">Notes &amp; Materials</span>
    </nav>

    <header class="edu-courses-hero">
      <div class="edu-courses-hero__identity">
        <img src="{{ $educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="" class="edu-courses-hero__avatar">
        <div>
          <p class="edu-courses-hero__eyebrow">{{ $educator->roleLabel() }}</p>
          <h1>Notes &amp; Materials by {{ $educator->display_name }}</h1>
          <p class="edu-courses-hero__lead">Browse PDF notes, worksheets, chapter summaries and study documents from this educator.</p>
        </div>
      </div>
      <a href="{{ $educator->publicUrl() }}" class="edu-courses-hero__back">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to profile
      </a>
    </header>

    <div class="edu-courses-summary" id="eduNotesSummary">
      @include('frontend.educator.partials.notes-summary')
    </div>

    <div class="edu-courses-layout">
      <aside class="edu-courses-filters">
        <form id="eduNotesFilterForm" class="edu-courses-filters__form">
          <div class="edu-courses-filters__head">
            <h2>Filters</h2>
            <button type="button" class="edu-courses-filters__reset js-edu-notes-reset">Reset</button>
          </div>

          <div class="edu-courses-filter">
            <label for="eduNotesSearch">Search</label>
            <div class="edu-courses-filter__search">
              <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
              <input type="search" id="eduNotesSearch" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Title, subject, topic...">
            </div>
          </div>

          <div class="edu-courses-filter">
            <label for="eduNotesSubject">Subject</label>
            <select id="eduNotesSubject" name="subject" class="form-select">
              <option value="">All subjects</option>
              @foreach($subjects as $subjectRow)
                <option value="{{ $subjectRow->subject }}" @selected($filters['subject'] === $subjectRow->subject)>
                  {{ $subjectRow->subject }} ({{ $subjectRow->total }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="edu-courses-filter">
            <label for="eduNotesClass">Class</label>
            <select id="eduNotesClass" name="class_course" class="form-select">
              <option value="">All classes</option>
              @foreach($classOptions as $classOption)
                <option value="{{ $classOption }}" @selected($filters['class_course'] === $classOption)>{{ $classOption }}</option>
              @endforeach
            </select>
          </div>

          <div class="edu-courses-filter">
            <label for="eduNotesFileType">File type</label>
            <select id="eduNotesFileType" name="file_type" class="form-select">
              <option value="">All file types</option>
              @foreach(['pdf' => 'PDF', 'doc' => 'DOC/DOCX', 'ppt' => 'PPT', 'xls' => 'XLS/XLSX', 'image' => 'Image'] as $fileKey => $fileLabel)
                @if(($fileTypeCounts[$fileKey] ?? 0) > 0)
                  <option value="{{ $fileKey }}" @selected($filters['file_type'] === $fileKey)>
                    {{ $fileLabel }} ({{ $fileTypeCounts[$fileKey] }})
                  </option>
                @endif
              @endforeach
            </select>
          </div>

          <div class="edu-courses-filter">
            <label for="eduNotesPricing">Pricing</label>
            <select id="eduNotesPricing" name="pricing" class="form-select">
              <option value="">All pricing</option>
              <option value="free" @selected($filters['pricing'] === 'free')>Free only</option>
              <option value="premium" @selected($filters['pricing'] === 'premium')>Premium only</option>
            </select>
          </div>

          <input type="hidden" name="sort" id="eduNotesSort" value="{{ $sort }}">
        </form>
      </aside>

      <section class="edu-courses-main">
        <div class="edu-courses-toolbar">
          <div class="edu-courses-toolbar__count">
            Showing <strong>{{ $notes->total() }}</strong> note{{ $notes->total() === 1 ? '' : 's' }}
          </div>
          <div class="edu-courses-toolbar__sort">
            <label for="eduNotesSortSelect">Sort by</label>
            <select id="eduNotesSortSelect" class="form-select js-edu-notes-sort">
              <option value="recent" @selected($sort === 'recent')>Newest first</option>
              <option value="rating" @selected($sort === 'rating')>Top rated</option>
              <option value="downloads" @selected($sort === 'downloads')>Most downloaded</option>
              <option value="title" @selected($sort === 'title')>Title A–Z</option>
            </select>
          </div>
        </div>

        <div id="eduNotesResults" class="edu-courses-results">
          @include('frontend.educator.partials.notes-results')
        </div>
      </section>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/educator-notes.js') }}?v={{ now()->timestamp }}" defer></script>
@endpush
