@extends('frontend.layouts.app')

@php
  $formatStat = function (int $count): string {
      if ($count >= 1000) {
          return number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+';
      }

      return number_format($count).'+';
  };
  $learnMoreUrl = route('frontend.premium.show', 'vendor');
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
  id="premiumVendorsPageRoot"
  data-index-url="{{ route('frontend.vendors.premium') }}"
  data-has-location="{{ $hasLocation ? '1' : '0' }}"
>
  @include('frontend.marketplace.partials.breadcrumb', ['module' => 'vendors', 'current' => 'Premium'])
  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Find Premium Vendors</h2>
          <p class="vendors-sidebar__lead">Filter trusted premium businesses by category, location, and ratings.</p>
        </div>
        <button type="button" class="vendors-sidebar__reset" id="premiumVendorsResetFilters">Reset All</button>
      </div>

      <div id="premiumVendorsFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="vendors-filter-group">
          <label for="premiumVendorsFilterSearch">Search</label>
          <div class="vendors-filter-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
              id="premiumVendorsFilterSearch"
              class="form-control"
              placeholder="Search premium stores..."
              value="{{ request('search') }}"
            >
          </div>
        </div>

        <div class="vendors-filter-group">
          <label for="premiumVendorsFilterCategory">Category</label>
          <select id="premiumVendorsFilterCategory" class="form-select">
            <option value="">All categories</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="premiumVendorsFilterSubcategory">Sub Category</label>
          <select id="premiumVendorsFilterSubcategory" class="form-select" disabled>
            <option value="">All subcategories</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="premiumVendorsFilterLocation">Location</label>
          <div class="vendors-filter-location-wrap">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <input
              id="premiumVendorsFilterLocation"
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
          <label for="premiumVendorsFilterRadius">Within</label>
          <select id="premiumVendorsFilterRadius" class="form-select" @disabled(! $hasLocation)>
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
                  class="premium-vendors-filter-rating"
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
          <label for="premiumVendorsFilterPayment">Payment Option</label>
          <select id="premiumVendorsFilterPayment" class="form-select">
            <option value="">All payment options</option>
            <option value="online" @selected(request('payment') === 'online')>Online Payment</option>
            <option value="offline" @selected(request('payment') === 'offline')>Offline / Inquiry</option>
          </select>
        </div>

        <button type="button" class="vendors-filter-apply" id="premiumVendorsApplyFilters">
          <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i> Apply Filters
        </button>
      </div>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero vendors-hero--premium">
        <div class="vendors-hero__intro">
          <span class="vendors-hero__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-crown"></i></span>
            Premium Vendor Marketplace
          </span>
          <h1>All Premium Vendors</h1>
          <p>Browse verified premium businesses with priority visibility, trusted badges, and richer store profiles.</p>
        </div>
        <div class="vendors-hero__stats">
          <div class="vendors-stat">
            <span class="vendors-stat__icon vendors-stat__icon--premium" aria-hidden="true">
              <i class="fa-solid fa-gem"></i>
            </span>
            <div class="vendors-stat__text">
              <strong>{{ $formatStat($vendorStats['premium']) }}</strong>
              <span>Premium Vendors</span>
            </div>
          </div>
        </div>
      </section>

      <section class="vendors-section" id="premiumVendorsSection">
        <div class="vendors-section__head">
          <h2><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium Vendors</h2>
          <a href="{{ route('frontend.vendors.listings') }}" class="vendors-section__link">
            View All Vendors <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
          </a>
        </div>

        <div
          class="vendors-premium-track"
          id="premiumVendorsGrid"
          data-next-page-url="{{ $vendors->nextPageUrl() }}"
        >
          @include('frontend.vendors.partials.premium-cards', [
              'premiumVendors' => $vendors,
              'hasLocation' => $hasLocation,
          ])
        </div>

        <div class="vendors-pagination-wrap offer-pagination-wrap" id="premiumVendorsPaginationState">
          @if ($vendors->total() > 0)
            <p class="offer-pagination-summary mb-0" id="premiumVendorsSummaryText">
              Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }} of {{ $vendors->total() }} premium vendors
            </p>
          @else
            <p class="offer-pagination-summary mb-0 d-none" id="premiumVendorsSummaryText"></p>
          @endif
          <p class="offer-pagination-loading mb-0 d-none" id="premiumVendorsLoadingText">Loading more premium vendors…</p>
        </div>

        <div id="premiumVendorsScrollSentinel" class="offer-scroll-sentinel" aria-hidden="true"></div>
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
          <a href="{{ route('frontend.vendors.listings') }}" class="vendors-cta__secondary">Browse All Vendors</a>
        </div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/premium-vendors-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
