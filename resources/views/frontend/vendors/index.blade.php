@extends('frontend.layouts.app')

@php
  $categoryIcons = ['fa-laptop', 'fa-car', 'fa-shirt', 'fa-seedling', 'fa-house', 'fa-wrench', 'fa-basket-shopping', 'fa-heart-pulse'];
  $formatStat = function (int $count): string {
      if ($count >= 1000) {
          return number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+';
      }

      return number_format($count).'+';
  };
  $listBusinessUrl = auth()->check() ? route('user.profile.edit') : route('login');
  $learnMoreUrl = route('frontend.premium.show', 'vendor');
  $locationDisplay = auth()->user()?->city ?: 'Your Location';
  $activeTab = request('tab', 'all');
  $activeView = $cardView ?? (request('view') === 'list' ? 'list' : 'grid');
  $ratingOptions = [4.5, 4.0, 3.5, 3.0, 2.0];
  $marketBannerImage = data_get($homepageSetting ?? null, 'hero_banner_image')
    ?: data_get($homepageSetting ?? null, 'offers_market_banner_image');
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<section class="vendors-market-banner">
  <img
    src="{{ $marketBannerImage ? asset($marketBannerImage) : 'https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=2200&q=80' }}"
    alt="Vendors marketplace banner"
    class="vendors-market-banner__image"
  >
</section>

<div
  class="vendors-page"
  id="vendorsPageRoot"
  data-index-url="{{ route('frontend.vendors.index') }}"
  data-has-location="{{ $hasLocation ? '1' : '0' }}"
  data-active-tab="{{ $activeTab }}"
  data-active-view="{{ $activeView }}"
>
  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <h2>Find the Right Vendor</h2>
        <button type="button" class="vendors-sidebar__reset" id="vendorsMarketResetFilters">Reset All</button>
      </div>

      <div id="vendorsFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="vendors-filter-group">
          <label for="vendorsMarketFilterSearch">Search</label>
          <div class="vendors-filter-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
              id="vendorsMarketFilterSearch"
              class="form-control"
              placeholder="Search store, product or service..."
              value="{{ request('search') }}"
            >
          </div>
        </div>

        <div class="vendors-filter-group">
          <label for="vendorsMarketFilterCategory">Category</label>
          <select id="vendorsMarketFilterCategory" class="form-select">
            <option value="">All categories</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="vendorsMarketFilterSubcategory">Sub Category</label>
          <select id="vendorsMarketFilterSubcategory" class="form-select" disabled>
            <option value="">All subcategories</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="vendorsMarketFilterLocation">Location</label>
          <div class="vendors-filter-location-wrap">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <input
              id="vendorsMarketFilterLocation"
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
          <label for="vendorsMarketFilterRadius">Within</label>
          <select id="vendorsMarketFilterRadius" class="form-select" @disabled(! $hasLocation)>
            <option value="">Any distance</option>
            <option value="5" @selected(request('radius') == '5')>5 km</option>
            <option value="10" @selected(request('radius') == '10')>10 km</option>
            <option value="25" @selected(request('radius') == '25')>25 km</option>
            <option value="50" @selected(request('radius') == '50')>50 km</option>
            <option value="100" @selected(request('radius') == '100')>100 km</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <span class="d-block mb-2 fw-bold small">Vendor Type</span>
          <div class="vendors-filter-checks">
            <label class="vendors-filter-check">
              <input type="checkbox" id="vendorsMarketFilterPremium" @checked(request()->boolean('premium'))>
              <i class="fa-solid fa-crown" aria-hidden="true"></i>
              Premium Vendors
            </label>
            <label class="vendors-filter-check">
              <input type="checkbox" id="vendorsMarketFilterVerified" @checked(request()->boolean('verified'))>
              <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
              Verified Vendors
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
                  class="vendors-market-filter-rating"
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
          <label for="vendorsMarketFilterPayment">Payment Option</label>
          <select id="vendorsMarketFilterPayment" class="form-select">
            <option value="">All payment options</option>
            <option value="online" @selected(request('payment') === 'online')>Online Payment</option>
            <option value="offline" @selected(request('payment') === 'offline')>Offline / Inquiry</option>
          </select>
        </div>

        <button type="button" class="vendors-filter-apply" id="vendorsMarketApplyFilters">
          <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i> Apply Filters
        </button>
      </div>
    </aside>

    <main class="vendors-main">
      @include('frontend.premium.partials.listing-cta', ['type' => 'vendor'])

      <section class="vendors-hero">
        <div class="vendors-hero__copy">
          <h1>Discover Trusted Vendors Near You</h1>
          <p>Find the best products and services from verified local businesses.</p>
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
            <div class="vendors-stat">
              <span class="vendors-stat__icon" aria-hidden="true">
                <i class="fa-solid fa-store"></i>
              </span>
              <div class="vendors-stat__text">
                <strong>{{ $formatStat($vendorStats['trusted']) }}</strong>
                <span>Trusted Businesses</span>
              </div>
            </div>
            <div class="vendors-stat">
              <span class="vendors-stat__icon" aria-hidden="true">
                <i class="fa-solid fa-chart-simple"></i>
              </span>
              <div class="vendors-stat__text">
                <strong>{{ $formatStat($vendorStats['categories']) }}</strong>
                <span>Categories</span>
              </div>
            </div>
            <div class="vendors-stat">
              <span class="vendors-stat__icon" aria-hidden="true">
                <i class="fa-solid fa-star"></i>
              </span>
              <div class="vendors-stat__text">
                <strong>{{ $formatStat($vendorStats['happy_customers']) }}</strong>
                <span>Happy Customers</span>
              </div>
            </div>
          </div>
        </div>
        <div class="vendors-hero__art">
          <img src="{{ asset('assets/images/vendors-hero-store.svg') }}" alt="" class="vendors-hero__illustration">
        </div>
      </section>

      @if ($topCategories->isNotEmpty())
        <section class="vendors-section">
          <div class="vendors-section__head">
            <h2>Top Categories</h2>
            <a href="#vendorsAllSection" class="vendors-section__link">View All Categories <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a>
          </div>
          <div class="vendors-categories-grid">
            @foreach ($topCategories as $index => $category)
              <a
                href="#"
                class="vendors-category-card"
                data-vendors-category-id="{{ $category->id }}"
              >
                <span class="vendors-category-card__icon">
                  <i class="fa-solid {{ $categoryIcons[$index % count($categoryIcons)] }}" aria-hidden="true"></i>
                </span>
                <h3>{{ $category->name }}</h3>
                <p>{{ number_format((int) $category->vendor_count) }}+ Vendors</p>
              </a>
            @endforeach
          </div>
        </section>
      @endif

      @if ($premiumVendors->isNotEmpty())
        <section class="vendors-section" id="vendorsPremiumSection">
          <div class="vendors-section__head">
            <h2><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium Vendors</h2>
            <a href="#" class="vendors-section__link" id="vendorsViewPremiumLink">View All Premium Vendors <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a>
          </div>
          <div class="vendors-premium-track" id="vendorsPremiumTrack">
            @include('frontend.vendors.partials.premium-cards', ['premiumVendors' => $premiumVendors, 'hasLocation' => $hasLocation])
          </div>
        </section>
      @else
        <section class="vendors-section d-none" id="vendorsPremiumSection">
          <div class="vendors-section__head">
            <h2><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium Vendors</h2>
            <a href="#" class="vendors-section__link" id="vendorsViewPremiumLink">View All Premium Vendors <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a>
          </div>
          <div class="vendors-premium-track" id="vendorsPremiumTrack"></div>
        </section>
      @endif

      <section class="vendors-section" id="vendorsAllSection">
        <div class="vendors-all__toolbar">
          <div class="vendors-tabs" role="tablist" aria-label="Vendor listing tabs">
            <button type="button" class="vendors-tab @if($activeTab === 'all') is-active @endif" data-vendors-tab="all">All Vendors</button>
            <button type="button" class="vendors-tab @if($activeTab === 'recent') is-active @endif" data-vendors-tab="recent">Recently Joined</button>
            <button type="button" class="vendors-tab @if($activeTab === 'top_rated') is-active @endif" data-vendors-tab="top_rated">Top Rated</button>
            <button type="button" class="vendors-tab @if($activeTab === 'most_reviewed') is-active @endif" data-vendors-tab="most_reviewed">Most Reviewed</button>
          </div>
          <div class="vendors-all__controls">
            <select id="vendorsMarketSort" class="form-select" aria-label="Sort vendors">
              <option value="recent" @selected(request('sort', 'recent') === 'recent')>Most Recent</option>
              <option value="name" @selected(request('sort') === 'name')>Name (A-Z)</option>
            </select>
            <div class="vendors-view-toggle" aria-label="Toggle vendor view">
              <button type="button" class="@if($activeView === 'grid') is-active @endif" data-vendors-view="grid" title="Grid view">
                <i class="fa-solid fa-grip" aria-hidden="true"></i>
              </button>
              <button type="button" class="@if($activeView === 'list') is-active @endif" data-vendors-view="list" title="List view">
                <i class="fa-solid fa-list" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </div>

        <div
          id="vendorsGrid"
          class="vendors-results-grid @if($activeView === 'list') is-list-view @endif"
          data-next-page-url="{{ $vendors->nextPageUrl() }}"
        >
          @if ($activeView === 'list')
            @include('frontend.vendors.partials.list-cards', ['vendors' => $vendors, 'hasLocation' => $hasLocation])
          @else
            @include('frontend.vendors.partials.cards', ['vendors' => $vendors, 'hasLocation' => $hasLocation])
          @endif
        </div>

        <div class="vendors-pagination-wrap offer-pagination-wrap" id="vendorsPaginationState">
          @if ($vendors->total() > 0)
            <p class="offer-pagination-summary mb-0" id="vendorsSummaryText">
              Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }} of {{ $vendors->total() }} results
            </p>
          @else
            <p class="offer-pagination-summary mb-0 d-none" id="vendorsSummaryText"></p>
          @endif
          <p class="offer-pagination-loading mb-0 d-none" id="vendorsLoadingText">Loading more vendors…</p>
        </div>

        <div id="vendorsScrollSentinel" class="offer-scroll-sentinel" aria-hidden="true"></div>
      </section>

      <section class="vendors-cta">
        <div class="vendors-cta__icon" aria-hidden="true">
          <i class="fa-solid fa-store"></i>
        </div>
        <div class="vendors-cta__copy">
          <h3>Are you a vendor?</h3>
          <p>List your business on SoilnWater and reach thousands of potential customers.</p>
        </div>
        <div class="vendors-cta__actions">
          <a href="{{ $listBusinessUrl }}" class="vendors-cta__primary">List Your Business</a>
          <a href="{{ $learnMoreUrl }}" class="vendors-cta__secondary">Learn More</a>
        </div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/vendors-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
