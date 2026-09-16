@extends('frontend.layouts.app')

@php
  $listingContext = $listingContext ?? 'schools';
  $isSchoolListing = $listingContext === 'schools';
  $pageTitle = $isSchoolListing ? 'Schools' : 'Institutes';
@endphp

@section('meta_title', 'All '.$pageTitle.' | SoilnWater')

@php
  $locationDisplay = auth()->user()?->city ?: 'Your Location';
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/institutes-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div
  class="vendors-page institutes-page"
  id="institutesPageRoot"
  data-index-url="{{ route($listingContext.'.index') }}"
  data-listings-url="{{ route($listingContext.'.listings') }}"
  data-has-location="{{ $hasLocation ? '1' : '0' }}"
  data-preview-listing="0"
>
  <div class="vendors-page__layout">
    @include('frontend.institutes.partials.filters-sidebar', compact('cities', 'boards', 'hasLocation', 'locationDisplay'))

    <main class="vendors-main">
      <div class="vendors-main__head">
        <div>
          <h1>All {{ $pageTitle }}</h1>
          <p class="vendors-main__lead"><a href="{{ route($listingContext.'.index') }}">&larr; Back to {{ strtolower($pageTitle) }} listing</a></p>
        </div>
        <select id="institutesMarketSort" class="form-select">
          <option value="recent" @selected(request('sort', 'recent') === 'recent')>Recently approved</option>
          <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
          @if($hasLocation)
            <option value="distance" @selected(request('sort') === 'distance')>Nearest first</option>
          @endif
        </select>
      </div>

      <p id="institutesSummaryText" class="vendors-summary-text {{ $institutes->total() ? '' : 'd-none' }}">
        Showing 1 to {{ $institutes->lastItem() ?? 0 }} of {{ $institutes->total() }} results
      </p>
      <p id="institutesLoadingText" class="vendors-loading-text d-none">Loading...</p>

      <div id="institutesGrid" class="vendors-grid institutes-grid" data-next-page-url="{{ $institutes->nextPageUrl() }}">
        @include('frontend.institutes.partials.cards', ['institutes' => $institutes, 'hasLocation' => $hasLocation])
      </div>

      <div id="institutesScrollSentinel" class="vendors-scroll-sentinel" aria-hidden="true"></div>
    </main>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/institutes-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
