@extends('frontend.layouts.app')

@section('meta_title', 'All Teachers & Tutors | SoilnWater')
@section('meta_description', 'Browse all approved teachers and tutors with filters for subject, city, ratings and tuition.')

@php
  $formatStat = function (int $count): string {
      if ($count >= 1000) {
          return number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+';
      }

      return number_format($count).'+';
  };
  $joinUrl = route('register', ['role' => 'teacher']);
  $locationDisplay = auth()->user()?->city ?: 'Your Location';
  $ratingOptions = [4.5, 4.0, 3.5, 3.0, 2.0];
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/educators-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div
  class="vendors-page vendors-page--listings educators-page"
  id="educatorsPageRoot"
  data-index-url="{{ route('educator.listings') }}"
  data-listings-url="{{ route('educator.listings') }}"
  data-has-location="{{ $hasLocation ? '1' : '0' }}"
  data-preview-listing="0"
>
  @include('frontend.marketplace.partials.breadcrumb', ['module' => 'educators', 'current' => 'All Listings'])

  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Find Teachers &amp; Tutors</h2>
          <p class="vendors-sidebar__lead">Filter by subject, city, rating and tuition.</p>
        </div>
        <button type="button" class="vendors-sidebar__reset" id="educatorsMarketResetFilters">Reset All</button>
      </div>

      <div id="educatorsFilterBar">
        <div class="vendors-filter-group">
          <label for="educatorsMarketFilterSearch">Search</label>
          <div class="vendors-filter-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
              id="educatorsMarketFilterSearch"
              class="form-control"
              placeholder="Name, subject or institute..."
              value="{{ request('search', request('q')) }}"
            >
          </div>
        </div>

        <div class="vendors-filter-group">
          <label for="educatorsMarketFilterSubject">Subject</label>
          <select id="educatorsMarketFilterSubject" class="form-select">
            <option value="">All subjects</option>
            @foreach ($subjects as $subject)
              <option value="{{ $subject }}" @selected((string) request('subject') === (string) $subject)>{{ $subject }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="educatorsMarketFilterCity">City</label>
          <select id="educatorsMarketFilterCity" class="form-select">
            <option value="">All cities</option>
            @foreach ($cities as $city)
              <option value="{{ $city }}" @selected((string) request('city') === (string) $city)>{{ $city }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="educatorsMarketFilterLocation">Your location</label>
          <div class="vendors-filter-location-wrap">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <input id="educatorsMarketFilterLocation" class="form-control" type="text" value="{{ $locationDisplay }}" readonly>
          </div>
          @unless ($hasLocation)
            <small class="vendors-location-note">Set your location from the header to enable distance filtering.</small>
          @endunless
        </div>

        <div class="vendors-filter-group">
          <label for="educatorsMarketFilterRadius">Within</label>
          <select id="educatorsMarketFilterRadius" class="form-select" @disabled(! $hasLocation)>
            <option value="">Any distance</option>
            <option value="5" @selected(request('radius') == '5')>5 km</option>
            <option value="10" @selected(request('radius') == '10')>10 km</option>
            <option value="25" @selected(request('radius') == '25')>25 km</option>
            <option value="50" @selected(request('radius') == '50')>50 km</option>
            <option value="100" @selected(request('radius') == '100')>100 km</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <span class="d-block mb-2 fw-bold small">Educator type</span>
          <div class="vendors-filter-checks">
            <label class="vendors-filter-check">
              <input type="checkbox" id="educatorsMarketFilterVerified" @checked(request()->boolean('verified'))>
              <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
              Verified only
            </label>
            <label class="vendors-filter-check">
              <input type="checkbox" id="educatorsMarketFilterTuitions" @checked(request('takes_tuitions') === '1' || request()->boolean('takes_tuitions'))>
              <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
              Takes tuitions
            </label>
            <label class="vendors-filter-check">
              <input type="checkbox" id="educatorsMarketFilterAvailable" @checked(request()->boolean('available_now'))>
              <i class="fa-solid fa-bolt" aria-hidden="true"></i>
              Available now
            </label>
          </div>
        </div>

        <div class="vendors-filter-group">
          <span class="d-block mb-2 fw-bold small">Ratings</span>
          <div class="vendors-filter-ratings">
            @foreach ($ratingOptions as $rating)
              <label class="vendors-filter-rating">
                <input
                  type="checkbox"
                  class="educators-market-filter-rating"
                  value="{{ $rating }}"
                  @checked((string) request('min_rating') === (string) $rating)
                >
                <span class="stars" aria-hidden="true">
                  @for ($star = 1; $star <= 5; $star++)
                    <i class="fa-solid fa-star"></i>
                  @endfor
                </span>
                <span>{{ number_format($rating, 1) }} &amp; above</span>
              </label>
            @endforeach
          </div>
        </div>

        <div class="vendors-filter-group">
          <label for="educatorsMarketSort">Sort by</label>
          <select id="educatorsMarketSort" class="form-select">
            <option value="recent" @selected(request('sort', 'recent') === 'recent')>Newest</option>
            <option value="rating" @selected(request('sort') === 'rating')>Top rated</option>
            <option value="experience" @selected(request('sort') === 'experience')>Most experience</option>
            <option value="students" @selected(request('sort') === 'students')>Most students</option>
            @if ($hasLocation)
              <option value="distance" @selected(request('sort') === 'distance')>Nearest</option>
            @endif
          </select>
        </div>

        <button type="button" class="vendors-filter-apply" id="educatorsMarketApplyFilters">
          <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i> Apply Filters
        </button>
      </div>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero vendors-hero--compact">
        <div class="vendors-hero__intro">
          <span class="vendors-hero__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-chalkboard-user"></i></span>
            All Educators
          </span>
          <h1>Browse Teachers &amp; Tutors</h1>
          <p>Scroll to load more. Use filters to narrow by subject, city and ratings.</p>
        </div>
        <div class="vendors-hero__stats">
          <div class="vendors-stat">
            <span class="vendors-stat__icon" aria-hidden="true"><i class="fa-solid fa-chalkboard-user"></i></span>
            <div class="vendors-stat__text">
              <strong>{{ $formatStat($educatorStats['trusted']) }}</strong>
              <span>Educators</span>
            </div>
          </div>
          <div class="vendors-stat">
            <span class="vendors-stat__icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
            <div class="vendors-stat__text">
              <strong>{{ $formatStat($educatorStats['verified']) }}</strong>
              <span>Verified</span>
            </div>
          </div>
        </div>
      </section>

      <section class="vendors-section" id="educatorsAllSection">
        <div class="vendors-section__head">
          <h2><i class="fa-solid fa-border-all" aria-hidden="true"></i> All Teachers &amp; Tutors</h2>
        </div>

        <div
          id="educatorsGrid"
          class="vendors-results-grid"
          data-next-page-url="{{ $educators->nextPageUrl() ?: '' }}"
        >
          @include('frontend.educator.partials.cards', ['educators' => $educators, 'hasLocation' => $hasLocation])
        </div>

        <div class="vendors-pagination-wrap offer-pagination-wrap" id="educatorsPaginationState">
          @if ($educators->total() > 0)
            <p class="offer-pagination-summary mb-0" id="educatorsSummaryText">
              Showing 1 to {{ $educators->lastItem() }} of {{ $educators->total() }} results
            </p>
          @else
            <p class="offer-pagination-summary mb-0 d-none" id="educatorsSummaryText"></p>
          @endif
          <p class="offer-pagination-loading mb-0 d-none" id="educatorsLoadingText">Loading educators…</p>
        </div>
        <div id="educatorsScrollSentinel" class="offer-scroll-sentinel" aria-hidden="true"></div>
      </section>

      <section class="vendors-cta">
        <div class="vendors-cta__icon" aria-hidden="true"><i class="fa-solid fa-chalkboard-user"></i></div>
        <div class="vendors-cta__copy">
          <h3>Are you a teacher or tutor?</h3>
          <p>Create your professional profile and reach students on SoilnWater.</p>
        </div>
        <div class="vendors-cta__actions">
          <a href="{{ $joinUrl }}" class="vendors-cta__primary">Join as Teacher / Tutor</a>
          <a href="{{ route('educator.index') }}" class="vendors-cta__secondary">Back to overview</a>
        </div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/educators-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
