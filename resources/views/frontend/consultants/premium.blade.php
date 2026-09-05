@extends('frontend.layouts.app')

@php
  $formatStat = function (int $count): string {
      if ($count >= 1000) {
          return number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+';
      }

      return number_format($count).'+';
  };
  $learnMoreUrl = route('frontend.premium.show', 'consultant');
  $locationDisplay = auth()->user()?->city ?: 'Your Location';
  $ratingOptions = [4.5, 4.0, 3.5, 3.0, 2.0];
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div
  class="vendors-page vendors-page--premium"
  id="premiumConsultantsPageRoot"
  data-index-url="{{ route('frontend.consultants.premium') }}"
  data-has-location="{{ $hasLocation ? '1' : '0' }}"
>
  @include('frontend.marketplace.partials.breadcrumb', ['module' => 'consultants', 'current' => 'Premium'])
  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Find Premium Consultants</h2>
          <p class="vendors-sidebar__lead">Filter trusted premium consultants by category, location, and ratings.</p>
        </div>
        <button type="button" class="vendors-sidebar__reset" id="premiumConsultantsResetFilters">Reset All</button>
      </div>

      <div id="premiumConsultantsFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="vendors-filter-group">
          <label for="premiumConsultantsFilterSearch">Search</label>
          <div class="vendors-filter-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
              id="premiumConsultantsFilterSearch"
              class="form-control"
              placeholder="Search premium consultants..."
              value="{{ request('search') }}"
            >
          </div>
        </div>

        <div class="vendors-filter-group">
          <label for="premiumConsultantsFilterCategory">Category</label>
          <select id="premiumConsultantsFilterCategory" class="form-select">
            <option value="">All categories</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="premiumConsultantsFilterSubcategory">Sub Category</label>
          <select id="premiumConsultantsFilterSubcategory" class="form-select" disabled>
            <option value="">All subcategories</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="premiumConsultantsFilterLocation">Location</label>
          <div class="vendors-filter-location-wrap">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <input
              id="premiumConsultantsFilterLocation"
              class="form-control"
              type="text"
              value="{{ $locationDisplay }}"
              readonly
            >
          </div>
          @unless ($hasLocation)
            <small class="vendors-location-note">Set your location from the header to enable distance filtering.</small>
          @endunless
        </div>

        <div class="vendors-filter-group">
          <label for="premiumConsultantsFilterRadius">Within</label>
          <select id="premiumConsultantsFilterRadius" class="form-select" @disabled(! $hasLocation)>
            <option value="">Any distance</option>
            <option value="5" @selected(request('radius') == '5')>5 km</option>
            <option value="10" @selected(request('radius') == '10')>10 km</option>
            <option value="25" @selected(request('radius') == '25')>25 km</option>
            <option value="50" @selected(request('radius') == '50')>50 km</option>
            <option value="100" @selected(request('radius') == '100')>100 km</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <span class="d-block mb-2 fw-bold small">Ratings</span>
          <div class="vendors-filter-ratings">
            @foreach ($ratingOptions as $rating)
              <label class="vendors-filter-rating">
                <input
                  type="checkbox"
                  class="premium-consultants-filter-rating"
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
          <label for="premiumConsultantsFilterPayment">Payment Option</label>
          <select id="premiumConsultantsFilterPayment" class="form-select">
            <option value="">All payment options</option>
            <option value="online" @selected(request('payment') === 'online')>Online Payment</option>
            <option value="offline" @selected(request('payment') === 'offline')>Offline / Inquiry</option>
          </select>
        </div>

        <button type="button" class="vendors-filter-apply" id="premiumConsultantsApplyFilters">
          <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i> Apply Filters
        </button>
      </div>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero vendors-hero--premium">
        <div class="vendors-hero__intro">
          <span class="vendors-hero__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-crown"></i></span>
            Premium Consultant Marketplace
          </span>
          <h1>All Premium Consultants</h1>
          <p>Browse verified premium consultants with priority visibility, trusted badges, and richer profiles.</p>
        </div>
        <div class="vendors-hero__stats">
          <div class="vendors-stat">
            <span class="vendors-stat__icon vendors-stat__icon--premium" aria-hidden="true">
              <i class="fa-solid fa-gem"></i>
            </span>
            <div class="vendors-stat__text">
              <strong>{{ $formatStat($consultantStats['premium']) }}</strong>
              <span>Premium Consultants</span>
            </div>
          </div>
        </div>
      </section>

      <section class="vendors-section" id="premiumConsultantsSection">
        <div class="vendors-section__head">
          <h2><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium Consultants</h2>
          <a href="{{ route('frontend.consultants.listings') }}" class="vendors-section__link">
            View All Consultants <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
          </a>
        </div>

        <div
          class="vendors-premium-track"
          id="premiumConsultantsGrid"
          data-next-page-url="{{ $consultants->nextPageUrl() }}"
        >
          @include('frontend.consultants.partials.premium-cards', [
              'premiumConsultants' => $consultants,
              'hasLocation' => $hasLocation,
          ])
        </div>

        <div class="vendors-pagination-wrap offer-pagination-wrap" id="premiumConsultantsPaginationState">
          @if ($consultants->total() > 0)
            <p class="offer-pagination-summary mb-0" id="premiumConsultantsSummaryText">
              Showing {{ $consultants->firstItem() }} to {{ $consultants->lastItem() }} of {{ $consultants->total() }} premium consultants
            </p>
          @else
            <p class="offer-pagination-summary mb-0 d-none" id="premiumConsultantsSummaryText"></p>
          @endif
          <p class="offer-pagination-loading mb-0 d-none" id="premiumConsultantsLoadingText">Loading more premium consultants…</p>
        </div>

        <div id="premiumConsultantsScrollSentinel" class="offer-scroll-sentinel" aria-hidden="true"></div>
      </section>

      <section class="vendors-cta">
        <div class="vendors-cta__icon" aria-hidden="true">
          <i class="fa-solid fa-crown"></i>
        </div>
        <div class="vendors-cta__copy">
          <h3>Upgrade to Premium</h3>
          <p>Get more visibility, a verified badge, and an ad-free store profile on SoilnWater.</p>
        </div>
        <div class="vendors-cta__actions">
          <a href="{{ $learnMoreUrl }}" class="vendors-cta__primary">Learn About Premium</a>
          <a href="{{ route('frontend.consultants.listings') }}" class="vendors-cta__secondary">Browse All Consultants</a>
        </div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/premium-consultants-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
