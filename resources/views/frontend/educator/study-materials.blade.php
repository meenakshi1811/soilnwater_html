@extends('frontend.layouts.app')

@section('meta_title', $educator->display_name.' · Study Material | SoilnWater')
@section('meta_description', 'Browse study materials, notes, question papers, assignments and more from '.$educator->display_name.'.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/educator-module.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/educator-profile.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/educator-courses.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/educator-study-materials.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div
  class="edu-courses-page edu-study-materials-page"
  id="eduStudyMaterialsPage"
  data-materials-url="{{ route('educator.study-materials', $educator->slug) }}"
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
      <span class="edu-breadcrumb__current">Study Material</span>
    </nav>

    <header class="edu-courses-hero">
      <div class="edu-courses-hero__identity">
        <img src="{{ $educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="" class="edu-courses-hero__avatar">
        <div>
          <p class="edu-courses-hero__eyebrow">{{ $educator->roleLabel() }}</p>
          <h1>Study Material by {{ $educator->display_name }}</h1>
          <p class="edu-courses-hero__lead">Notes, question papers, assignments, worksheets, study guides and other resources from this educator.</p>
        </div>
      </div>
      <a href="{{ $educator->publicUrl() }}" class="edu-courses-hero__back">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to profile
      </a>
    </header>

    <div class="edu-courses-summary" id="eduStudyMaterialsSummary">
      @include('frontend.educator.partials.study-materials-summary')
    </div>

    <div class="edu-courses-layout">
      <aside class="edu-courses-filters">
        <form id="eduStudyMaterialsFilterForm" class="edu-courses-filters__form">
          <div class="edu-courses-filters__head">
            <h2>Filters</h2>
            <button type="button" class="edu-courses-filters__reset js-edu-sm-reset">Reset</button>
          </div>

          <div class="edu-courses-filter">
            <label for="eduSmSearch">Search</label>
            <div class="edu-courses-filter__search">
              <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
              <input type="search" id="eduSmSearch" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Title, subject, topic...">
            </div>
          </div>

          <div class="edu-courses-filter">
            <label for="eduSmType">Category / type</label>
            <select id="eduSmType" name="material_type" class="form-select">
              <option value="">All types</option>
              @foreach($materialTypes as $typeRow)
                <option value="{{ $typeRow->material_type }}" @selected($filters['material_type'] === $typeRow->material_type)>
                  {{ (new \App\Models\StudyMaterial(['material_type' => $typeRow->material_type]))->materialTypeLabel() }} ({{ $typeRow->total }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="edu-courses-filter">
            <label for="eduSmSubject">Subject</label>
            <select id="eduSmSubject" name="subject" class="form-select">
              <option value="">All subjects</option>
              @foreach($subjects as $subjectRow)
                <option value="{{ $subjectRow->subject }}" @selected($filters['subject'] === $subjectRow->subject)>
                  {{ $subjectRow->subject }} ({{ $subjectRow->total }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="edu-courses-filter">
            <label for="eduSmClass">Class</label>
            <select id="eduSmClass" name="class_course" class="form-select">
              <option value="">All classes</option>
              @foreach($classOptions as $classOption)
                <option value="{{ $classOption }}" @selected($filters['class_course'] === $classOption)>{{ $classOption }}</option>
              @endforeach
            </select>
          </div>

          <div class="edu-courses-filter">
            <label for="eduSmFileType">File type</label>
            <select id="eduSmFileType" name="file_type" class="form-select">
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
            <label for="eduSmPricing">Pricing</label>
            <select id="eduSmPricing" name="pricing" class="form-select">
              <option value="">All</option>
              <option value="free" @selected($filters['pricing'] === 'free')>Free only</option>
              <option value="paid" @selected($filters['pricing'] === 'paid')>Paid only</option>
            </select>
          </div>

          <input type="hidden" name="sort" id="eduSmSort" value="{{ $sort }}">
        </form>
      </aside>

      <section class="edu-courses-main">
        <div class="edu-courses-toolbar">
          <div class="edu-courses-toolbar__count">
            Showing <strong>{{ $materials->total() }}</strong> material{{ $materials->total() === 1 ? '' : 's' }}
          </div>
          <div class="edu-courses-toolbar__sort">
            <label for="eduSmSortSelect">Sort by</label>
            <select id="eduSmSortSelect" class="form-select js-edu-sm-sort">
              <option value="recent" @selected($sort === 'recent')>Newest first</option>
              <option value="rating" @selected($sort === 'rating')>Top rated</option>
              <option value="downloads" @selected($sort === 'downloads')>Most downloaded</option>
              <option value="title" @selected($sort === 'title')>Title A–Z</option>
            </select>
          </div>
        </div>

        <div id="eduStudyMaterialsResults" class="edu-courses-results">
          @include('frontend.educator.partials.study-materials-results')
        </div>
      </section>
    </div>
  </div>
</div>

@include('frontend.study-materials.partials.payment-modal-shell')
@endsection

@push('scripts')
@include('community.partials.toastr-assets')
<script src="{{ asset('assets/js/study-material-payment.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/educator-study-materials.js') }}?v={{ now()->timestamp }}" defer></script>
@endpush
