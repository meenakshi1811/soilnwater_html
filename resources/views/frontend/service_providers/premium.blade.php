@extends('frontend.layouts.app')

@php
  $formatStat = function (int $count): string {
      if ($count >= 1000) {
          return number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+';
      }

      return number_format($count).'+';
  };
  $learnMoreUrl = route('frontend.premium.show', 'service');
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
  id="premiumServicesPageRoot"
  data-index-url="{{ route('frontend.service_providers.premium') }}"
  data-has-location="{{ $hasLocation ? '1' : '0' }}"
>
  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Find Premium Services</h2>
          <p class="vendors-sidebar__lead">Filter trusted premium service providers by category, location, and ratings.</p>
        </div>
        <button type="button" class="vendors-sidebar__reset" id="premiumServicesResetFilters">Reset All</button>
      </div>

      <div id="premiumServicesFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="vendors-filter-group">
          <label for="premiumServicesFilterSearch">Search</label>
          <div class="vendors-filter-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
              id="premiumServicesFilterSearch"
              class="form-control"
              placeholder="Search premium services..."
              value="{{ request('search') }}"
            >
          </div>
        </div>

        <div class="vendors-filter-group">
          <label for="premiumServicesFilterCategory">Category</label>
          <select id="premiumServicesFilterCategory" class="form-select">
            <option value="">All categories</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="premiumServicesFilterSubcategory">Sub Category</label>
          <select id="premiumServicesFilterSubcategory" class="form-select" disabled>
            <option value="">All subcategories</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="premiumServicesFilterLocation">Location</label>
          <div class="vendors-filter-location-wrap">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <input
              id="premiumServicesFilterLocation"
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
          <label for="premiumServicesFilterRadius">Within</label>
          <select id="premiumServicesFilterRadius" class="form-select" @disabled(! $hasLocation)>
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
                  class="premium-services-filter-rating"
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
          <label for="premiumServicesFilterPayment">Payment Option</label>
          <select id="premiumServicesFilterPayment" class="form-select">
            <option value="">All payment options</option>
            <option value="online" @selected(request('payment') === 'online')>Online Payment</option>
            <option value="offline" @selected(request('payment') === 'offline')>Offline / Inquiry</option>
          </select>
        </div>

        <button type="button" class="vendors-filter-apply" id="premiumServicesApplyFilters">
          <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i> Apply Filters
        </button>
      </div>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero vendors-hero--premium">
        <div class="vendors-hero__intro">
          <span class="vendors-hero__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-crown"></i></span>
            Premium Services Marketplace
          </span>
          <h1>All Premium Services</h1>
          <p>Browse verified premium service providers with priority visibility, trusted badges, and richer profiles.</p>
        </div>
        <div class="vendors-hero__stats">
          <div class="vendors-stat">
            <span class="vendors-stat__icon vendors-stat__icon--premium" aria-hidden="true">
              <i class="fa-solid fa-gem"></i>
            </span>
            <div class="vendors-stat__text">
              <strong>{{ $formatStat($serviceProviderStats['premium']) }}</strong>
              <span>Premium Services</span>
            </div>
          </div>
        </div>
      </section>

      <section class="vendors-section" id="premiumServicesSection">
        <div class="vendors-section__head">
          <h2><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium Services</h2>
          <a href="{{ route('frontend.service_providers.listings') }}" class="vendors-section__link">
            View All Services <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
          </a>
        </div>

        <div
          class="vendors-premium-track"
          id="premiumServicesGrid"
          data-next-page-url="{{ $service_providers->nextPageUrl() }}"
        >
          @include('frontend.service_providers.partials.premium-cards', [
              'premiumServiceProviders' => $service_providers,
              'hasLocation' => $hasLocation,
          ])
        </div>

        <div class="vendors-pagination-wrap offer-pagination-wrap" id="premiumServicesPaginationState">
          @if ($service_providers->total() > 0)
            <p class="offer-pagination-summary mb-0" id="premiumServicesSummaryText">
              Showing {{ $service_providers->firstItem() }} to {{ $service_providers->lastItem() }} of {{ $service_providers->total() }} premium services
            </p>
          @else
            <p class="offer-pagination-summary mb-0 d-none" id="premiumServicesSummaryText"></p>
          @endif
          <p class="offer-pagination-loading mb-0 d-none" id="premiumServicesLoadingText">Loading more premium services…</p>
        </div>

        <div id="premiumServicesScrollSentinel" class="offer-scroll-sentinel" aria-hidden="true"></div>
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
          <a href="{{ route('frontend.service_providers.listings') }}" class="vendors-cta__secondary">Browse All Services</a>
        </div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/premium-services-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
