@extends('frontend.layouts.app')

@php
  $formatStat = function (int $count): string {
      if ($count >= 1000) {
          return number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+';
      }

      return number_format($count).'+';
  };
  $listBusinessUrl = auth()->check() ? route('user.profile.edit') : route('login');
  $learnMoreUrl = route('frontend.premium.show', 'service');
  $locationDisplay = auth()->user()?->city ?: 'Your Location';
  $activeTab = request('tab', 'all');
  $activeView = $cardView ?? (request('view') === 'list' ? 'list' : 'grid');
  $ratingOptions = [4.5, 4.0, 3.5, 3.0, 2.0];
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div
  class="vendors-page vendors-page--listings"
  id="servicesPageRoot"
  data-index-url="{{ route('frontend.service_providers.listings') }}"
  data-premium-url="{{ route('frontend.service_providers.premium') }}"
  data-has-location="{{ $hasLocation ? '1' : '0' }}"
  data-active-tab="{{ $activeTab }}"
  data-active-view="{{ $activeView }}"
>
  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Find the Right Service</h2>
          <p class="vendors-sidebar__lead">Filter by category, location, and ratings.</p>
        </div>
        <button type="button" class="vendors-sidebar__reset" id="servicesMarketResetFilters">Reset All</button>
      </div>

      <div id="servicesFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="vendors-filter-group">
          <label for="servicesMarketFilterSearch">Search</label>
          <div class="vendors-filter-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
              id="servicesMarketFilterSearch"
              class="form-control"
              placeholder="Search service or provider..."
              value="{{ request('search') }}"
            >
          </div>
        </div>

        <div class="vendors-filter-group">
          <label for="servicesMarketFilterCategory">Category</label>
          <select id="servicesMarketFilterCategory" class="form-select">
            <option value="">All categories</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="servicesMarketFilterSubcategory">Sub Category</label>
          <select id="servicesMarketFilterSubcategory" class="form-select" disabled>
            <option value="">All subcategories</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="servicesMarketFilterLocation">Location</label>
          <div class="vendors-filter-location-wrap">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <input
              id="servicesMarketFilterLocation"
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
          <label for="servicesMarketFilterRadius">Within</label>
          <select id="servicesMarketFilterRadius" class="form-select" @disabled(! $hasLocation)>
            <option value="">Any distance</option>
            <option value="5" @selected(request('radius') == '5')>5 km</option>
            <option value="10" @selected(request('radius') == '10')>10 km</option>
            <option value="25" @selected(request('radius') == '25')>25 km</option>
            <option value="50" @selected(request('radius') == '50')>50 km</option>
            <option value="100" @selected(request('radius') == '100')>100 km</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <span class="d-block mb-2 fw-bold small">Provider Type</span>
          <div class="vendors-filter-checks">
            <label class="vendors-filter-check">
              <input type="checkbox" id="servicesMarketFilterPremium" @checked(request()->boolean('premium'))>
              <i class="fa-solid fa-crown" aria-hidden="true"></i>
              Premium Providers
            </label>
            <label class="vendors-filter-check">
              <input type="checkbox" id="servicesMarketFilterVerified" @checked(request()->boolean('verified'))>
              <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
              Verified Providers
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
                  class="services-market-filter-rating"
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
          <label for="servicesMarketFilterPayment">Payment Option</label>
          <select id="servicesMarketFilterPayment" class="form-select">
            <option value="">All payment options</option>
            <option value="online" @selected(request('payment') === 'online')>Online Payment</option>
            <option value="offline" @selected(request('payment') === 'offline')>Offline / Inquiry</option>
          </select>
        </div>

        <button type="button" class="vendors-filter-apply" id="servicesMarketApplyFilters">
          <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i> Apply Filters
        </button>
      </div>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero vendors-hero--compact">
        <div class="vendors-hero__intro">
          <span class="vendors-hero__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            Services Marketplace
          </span>
          <h1>All Service Listings</h1>
          <p>Browse every verified service provider on SoilnWater. Premium members appear first with highlighted profiles.</p>
        </div>
        <div class="vendors-hero__stats">
          <div class="vendors-stat">
            <span class="vendors-stat__icon" aria-hidden="true">
              <i class="fa-solid fa-screwdriver-wrench"></i>
            </span>
            <div class="vendors-stat__text">
              <strong>{{ $formatStat($serviceProviderStats['trusted']) }}</strong>
              <span>Trusted Providers</span>
            </div>
          </div>
          <div class="vendors-stat">
            <span class="vendors-stat__icon vendors-stat__icon--premium" aria-hidden="true">
              <i class="fa-solid fa-gem"></i>
            </span>
            <div class="vendors-stat__text">
              <strong>{{ $formatStat($serviceProviderStats['premium']) }}</strong>
              <span>Premium Providers</span>
            </div>
          </div>
        </div>
      </section>

      <section class="vendors-section" id="servicesAllSection">
        <div class="vendors-section__head">
          <h2><i class="fa-solid fa-border-all" aria-hidden="true"></i> All Services</h2>
          <a href="{{ route('frontend.service_providers.index') }}" class="vendors-section__link">
            Back to Marketplace <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
          </a>
        </div>

        <div class="vendors-all__toolbar">
          <div class="vendors-tabs" role="tablist" aria-label="Vendor listing tabs">
            <button type="button" class="vendors-tab @if($activeTab === 'all') is-active @endif" data-services-tab="all">All Services</button>
            <button type="button" class="vendors-tab @if($activeTab === 'recent') is-active @endif" data-services-tab="recent">Recently Joined</button>
            <button type="button" class="vendors-tab @if($activeTab === 'top_rated') is-active @endif" data-services-tab="top_rated">Top Rated</button>
            <button type="button" class="vendors-tab @if($activeTab === 'most_reviewed') is-active @endif" data-services-tab="most_reviewed">Most Reviewed</button>
          </div>
          <div class="vendors-all__controls">
            <select id="servicesMarketSort" class="form-select" aria-label="Sort services">
              <option value="recent" @selected(request('sort', 'recent') === 'recent')>Most Recent</option>
              <option value="name" @selected(request('sort') === 'name')>Name (A-Z)</option>
            </select>
            <div class="vendors-view-toggle" aria-label="Toggle vendor view">
              <button type="button" class="@if($activeView === 'grid') is-active @endif" data-services-view="grid" title="Grid view">
                <i class="fa-solid fa-grip" aria-hidden="true"></i>
              </button>
              <button type="button" class="@if($activeView === 'list') is-active @endif" data-services-view="list" title="List view">
                <i class="fa-solid fa-list" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </div>

        <div
          id="servicesGrid"
          class="vendors-results-grid @if($activeView === 'list') is-list-view @endif"
          data-next-page-url="{{ $service_providers->nextPageUrl() }}"
        >
          @if ($activeView === 'list')
            @include('frontend.service_providers.partials.list-cards', ['service_providers' => $service_providers, 'hasLocation' => $hasLocation])
          @else
            @include('frontend.service_providers.partials.cards', ['service_providers' => $service_providers, 'hasLocation' => $hasLocation])
          @endif
        </div>

        <div class="vendors-pagination-wrap offer-pagination-wrap" id="servicesPaginationState">
          @if ($service_providers->total() > 0)
            <p class="offer-pagination-summary mb-0" id="servicesSummaryText">
              Showing {{ $service_providers->firstItem() }} to {{ $service_providers->lastItem() }} of {{ $service_providers->total() }} results
            </p>
          @else
            <p class="offer-pagination-summary mb-0 d-none" id="servicesSummaryText"></p>
          @endif
          <p class="offer-pagination-loading mb-0 d-none" id="servicesLoadingText">Loading more services…</p>
        </div>

        <div id="servicesScrollSentinel" class="offer-scroll-sentinel" aria-hidden="true"></div>
      </section>

      <section class="vendors-cta">
        <div class="vendors-cta__icon" aria-hidden="true">
          <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>
        <div class="vendors-cta__copy">
          <h3>Are you a service provider?</h3>
          <p>List your services on SoilnWater and reach customers who need your expertise.</p>
        </div>
        <div class="vendors-cta__actions">
          <a href="{{ $listBusinessUrl }}" class="vendors-cta__primary">List Your Services</a>
          <a href="{{ $learnMoreUrl }}" class="vendors-cta__secondary">Learn About Premium</a>
        </div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/services-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
