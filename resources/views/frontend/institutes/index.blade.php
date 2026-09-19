@extends('frontend.layouts.app')

@php
  $listingContext = $listingContext ?? 'schools';
  $isSchoolListing = $listingContext === 'schools';
  $pageTitle = $isSchoolListing ? 'Schools' : 'Institutes';
  $joinRole = $isSchoolListing ? 'school' : 'institute';
@endphp

@section('meta_title', $pageTitle.' | SoilnWater')
@section('meta_description', 'Find '.$pageTitle.' near you. Filter by city, board, institution type and more.')

@php
  $joinUrl = route('register', ['role' => $joinRole]);
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
  data-preview-listing="1"
>
  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Find {{ $pageTitle }}</h2>
          <p class="vendors-sidebar__lead">Filter by city, board, and institution type.</p>
        </div>
        <button type="button" class="vendors-sidebar__reset" id="institutesMarketResetFilters">Reset All</button>
      </div>

      <div id="institutesFilterBar">
        <div class="vendors-filter-group">
          <label for="institutesMarketFilterSearch">Search</label>
          <div class="vendors-filter-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input id="institutesMarketFilterSearch" class="form-control" placeholder="School name, board or city..." value="{{ request('search', request('q')) }}">
          </div>
        </div>

        <div class="vendors-filter-group">
          <label for="institutesMarketFilterType">Institution type</label>
          <select id="institutesMarketFilterType" class="form-select">
            <option value="">All types</option>
            @foreach (['school' => 'School', 'college' => 'College', 'university' => 'University', 'coaching' => 'Coaching Institute', 'other' => 'Other'] as $value => $label)
              <option value="{{ $value }}" @selected((string) request('type') === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="institutesMarketFilterCity">City</label>
          <select id="institutesMarketFilterCity" class="form-select">
            <option value="">All cities</option>
            @foreach ($cities as $city)
              <option value="{{ $city }}" @selected((string) request('city') === (string) $city)>{{ $city }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="institutesMarketFilterBoard">Board / affiliation</label>
          <select id="institutesMarketFilterBoard" class="form-select">
            <option value="">All boards</option>
            @foreach ($boards as $board)
              <option value="{{ $board }}" @selected((string) request('board') === (string) $board)>{{ $board }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="institutesMarketFilterLocation">Your location</label>
          <div class="vendors-filter-location-wrap">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <input id="institutesMarketFilterLocation" class="form-control" type="text" value="{{ $locationDisplay }}" readonly>
          </div>
          @unless ($hasLocation)
            <small class="vendors-location-note">Set your location from the header to enable distance filtering.</small>
          @endunless
        </div>

        <div class="vendors-filter-group">
          <label for="institutesMarketFilterRadius">Within</label>
          <select id="institutesMarketFilterRadius" class="form-select" @disabled(! $hasLocation)>
            <option value="">Any distance</option>
            @foreach ([5, 10, 25, 50, 100] as $km)
              <option value="{{ $km }}" @selected(request('radius') == $km)>{{ $km }} km</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label class="form-check">
            <input type="checkbox" class="form-check-input" id="institutesMarketFilterVerified" @checked(request()->boolean('verified'))>
            Verified only
          </label>
        </div>

        <button type="button" class="btn btn-primary w-100" id="institutesMarketApplyFilters">Apply filters</button>
      </div>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero">
        <div class="vendors-hero__intro">
          <span class="vendors-hero__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-school"></i></span>
            {{ $pageTitle }} Marketplace
          </span>
          <h1>{{ $isSchoolListing ? 'Discover Trusted Schools Near You' : 'Discover Trusted Institutes Near You' }}</h1>
          <p>Explore verified {{ strtolower($pageTitle) }} by city, board, and institution type on SoilnWater.</p>
        </div>
        <div class="vendors-hero__stats">
          <div class="vendors-stat">
            <span class="vendors-stat__icon" aria-hidden="true"><i class="fa-solid fa-school"></i></span>
            <div class="vendors-stat__text">
              <strong>{{ number_format($instituteStats['total']) }}</strong>
              <span>{{ $isSchoolListing ? 'Schools listed' : 'Institutes listed' }}</span>
            </div>
          </div>
          <div class="vendors-stat">
            <span class="vendors-stat__icon vendors-stat__icon--premium" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
            <div class="vendors-stat__text">
              <strong>{{ number_format($instituteStats['verified']) }}</strong>
              <span>Verified profiles</span>
            </div>
          </div>
          <div class="vendors-stat">
            <span class="vendors-stat__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
            <div class="vendors-stat__text">
              <strong>{{ number_format($instituteStats['cities']) }}</strong>
              <span>Cities covered</span>
            </div>
          </div>
          <div class="vendors-stat">
            <span class="vendors-stat__icon" aria-hidden="true"><i class="fa-solid fa-book-open"></i></span>
            <div class="vendors-stat__text">
              <strong>{{ number_format($boards->count()) }}</strong>
              <span>Boards &amp; affiliations</span>
            </div>
          </div>
        </div>
      </section>

      <section class="vendors-section" id="institutesAllSection">
        <div class="vendors-section__head">
          <h2><i class="fa-solid fa-border-all" aria-hidden="true"></i> {{ $isSchoolListing ? 'School Listing' : 'Institute Listing' }}</h2>
          <a href="{{ route($listingContext.'.listings', request()->query()) }}" class="vendors-section__link" id="institutesViewAllLink">
            View all listings <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
          </a>
        </div>

        <div class="vendors-all__toolbar institutes-toolbar">
          <p class="institutes-toolbar__summary mb-0">
            Browse approved {{ strtolower($pageTitle) }} and use filters on the left to narrow results.
          </p>
          <div class="vendors-all__controls">
            <select id="institutesMarketSort" class="form-select" aria-label="Sort listings">
              <option value="recent" @selected(request('sort', 'recent') === 'recent')>Recently approved</option>
              <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
              @if($hasLocation)
                <option value="distance" @selected(request('sort') === 'distance')>Nearest first</option>
              @endif
            </select>
            <a href="{{ $joinUrl }}" class="institutes-register-btn">
              Register your {{ $isSchoolListing ? 'school' : 'institute' }}
            </a>
          </div>
        </div>

        <p id="institutesSummaryText" class="vendors-summary-text {{ $institutes->total() ? '' : 'd-none' }}">
          Showing {{ $institutes->lastItem() ?? 0 }} of {{ $institutes->total() }} {{ strtolower($pageTitle) }}
        </p>
        <p id="institutesLoadingText" class="vendors-loading-text d-none">Loading...</p>

        <div id="institutesGrid" class="vendors-grid institutes-grid" data-next-page-url="{{ $institutes->nextPageUrl() }}">
          @include('frontend.institutes.partials.cards', ['institutes' => $institutes, 'hasLocation' => $hasLocation])
        </div>

        <div id="institutesScrollSentinel" class="vendors-scroll-sentinel" aria-hidden="true"></div>
      </section>

      <section class="vendors-cta">
        <div class="vendors-cta__icon" aria-hidden="true"><i class="fa-solid fa-school"></i></div>
        <div class="vendors-cta__copy">
          <h3>Run a {{ $isSchoolListing ? 'school' : 'institute' }}?</h3>
          <p>List your institution on SoilnWater, share updates, and connect with parents and students.</p>
        </div>
        <div class="vendors-cta__actions">
          <a href="{{ $joinUrl }}" class="vendors-cta__primary">Register your {{ $isSchoolListing ? 'school' : 'institute' }}</a>
          <a href="{{ route($listingContext.'.listings', request()->query()) }}" class="vendors-cta__secondary">Browse all listings</a>
        </div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/institutes-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
