@extends('frontend.layouts.app')

@php
  use App\Support\VendorCategoryIcon;

  $formatStat = function (int $count): string {
      if ($count >= 1000) {
          return number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+';
      }

      return number_format($count).'+';
  };
  $listBusinessUrl = auth()->check() ? route('user.profile.edit') : route('login');
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
  class="vendors-page"
  id="consultantsPageRoot"
  data-index-url="{{ route('frontend.consultants.index') }}"
  data-listings-url="{{ route('frontend.consultants.listings') }}"
  data-premium-url="{{ route('frontend.consultants.premium') }}"
  data-has-location="{{ $hasLocation ? '1' : '0' }}"
  data-preview-listing="1"
>
  @include('frontend.marketplace.partials.breadcrumb', ['module' => 'consultants'])
  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Find the Right Consultant</h2>
          <p class="vendors-sidebar__lead">Filter by category, location, and ratings.</p>
        </div>
        <button type="button" class="vendors-sidebar__reset" id="consultantsMarketResetFilters">Reset All</button>
      </div>

      <div id="consultantsFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="vendors-filter-group">
          <label for="consultantsMarketFilterSearch">Search</label>
          <div class="vendors-filter-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
              id="consultantsMarketFilterSearch"
              class="form-control"
              placeholder="Search consultant or service..."
              value="{{ request('search') }}"
            >
          </div>
        </div>

        <div class="vendors-filter-group">
          <label for="consultantsMarketFilterCategory">Category</label>
          <select id="consultantsMarketFilterCategory" class="form-select">
            <option value="">All categories</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="consultantsMarketFilterSubcategory">Sub Category</label>
          <select id="consultantsMarketFilterSubcategory" class="form-select" disabled>
            <option value="">All subcategories</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <label for="consultantsMarketFilterLocation">Location</label>
          <div class="vendors-filter-location-wrap">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <input
              id="consultantsMarketFilterLocation"
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
          <label for="consultantsMarketFilterRadius">Within</label>
          <select id="consultantsMarketFilterRadius" class="form-select" @disabled(! $hasLocation)>
            <option value="">Any distance</option>
            <option value="5" @selected(request('radius') == '5')>5 km</option>
            <option value="10" @selected(request('radius') == '10')>10 km</option>
            <option value="25" @selected(request('radius') == '25')>25 km</option>
            <option value="50" @selected(request('radius') == '50')>50 km</option>
            <option value="100" @selected(request('radius') == '100')>100 km</option>
          </select>
        </div>

        <div class="vendors-filter-group">
          <span class="d-block mb-2 fw-bold small">Consultant Type</span>
          <div class="vendors-filter-checks">
            <label class="vendors-filter-check">
              <input type="checkbox" id="consultantsMarketFilterPremium" @checked(request()->boolean('premium'))>
              <i class="fa-solid fa-crown" aria-hidden="true"></i>
              Premium Consultants
            </label>
            <label class="vendors-filter-check">
              <input type="checkbox" id="consultantsMarketFilterVerified" @checked(request()->boolean('verified'))>
              <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
              Verified Consultants
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
                  class="consultants-market-filter-rating"
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
          <label for="consultantsMarketFilterPayment">Payment Option</label>
          <select id="consultantsMarketFilterPayment" class="form-select">
            <option value="">All payment options</option>
            <option value="online" @selected(request('payment') === 'online')>Online Payment</option>
            <option value="offline" @selected(request('payment') === 'offline')>Offline / Inquiry</option>
          </select>
        </div>

        <button type="button" class="vendors-filter-apply" id="consultantsMarketApplyFilters">
          <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i> Apply Filters
        </button>
      </div>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero">
        <div class="vendors-hero__intro">
          <span class="vendors-hero__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-user-tie"></i></span>
            Consultant Marketplace
          </span>
          <h1>Discover Trusted Consultants Near You</h1>
          <p>Find expert consultants and advisory services from verified professionals in your area.</p>
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
            <div class="vendors-stat">
              <span class="vendors-stat__icon" aria-hidden="true">
                <i class="fa-solid fa-user-tie"></i>
              </span>
              <div class="vendors-stat__text">
                <strong>{{ $formatStat($consultantStats['trusted']) }}</strong>
                <span>Trusted Businesses</span>
              </div>
            </div>
            <div class="vendors-stat">
              <span class="vendors-stat__icon" aria-hidden="true">
                <i class="fa-solid fa-chart-simple"></i>
              </span>
              <div class="vendors-stat__text">
                <strong>{{ $formatStat($consultantStats['categories']) }}</strong>
                <span>Categories</span>
              </div>
            </div>
            <div class="vendors-stat">
              <span class="vendors-stat__icon" aria-hidden="true">
                <i class="fa-solid fa-star"></i>
              </span>
              <div class="vendors-stat__text">
                <strong>{{ $formatStat($consultantStats['happy_customers']) }}</strong>
                <span>Happy Customers</span>
              </div>
            </div>
          </div>
      </section>

      @include('frontend.premium.partials.listing-cta', ['type' => 'consultant'])

      @if ($topCategories->isNotEmpty())
        <section class="vendors-section vendors-section--categories">
          <div class="vendors-section__head vendors-section__head--compact">
            <h2>Top Categories</h2>
            <a href="{{ route('frontend.consultants.categories') }}" class="vendors-section__link">
              All Listing <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
            </a>
          </div>
          <div class="vendors-categories-strip">
            @foreach ($topCategories as $category)
              <a
                href="{{ route('frontend.consultants.listings', ['category_id' => $category->id]) }}"
                class="vendors-category-chip"
                title="{{ $category->name }}"
              >
                <span class="vendors-category-chip__icon vendors-category-chip__icon--{{ VendorCategoryIcon::toneIndex($category->name) }}">
                  <i class="fa-solid {{ VendorCategoryIcon::iconClass($category->name) }}" aria-hidden="true"></i>
                </span>
                <span class="vendors-category-chip__text">
                  <strong>{{ $category->name }}</strong>
                  <small>{{ number_format((int) $category->consultant_count) }}+ consultants</small>
                </span>
              </a>
            @endforeach
            <a
              href="{{ route('frontend.consultants.listings') }}"
              class="vendors-category-chip vendors-category-chip--all"
              title="View all vendor listings"
            >
              <span class="vendors-category-chip__icon vendors-category-chip__icon--all">
                <i class="fa-solid fa-border-all" aria-hidden="true"></i>
              </span>
              <span class="vendors-category-chip__text">
                <strong>All Listing</strong>
                <small>View all consultants</small>
              </span>
            </a>
          </div>
        </section>
      @endif

      <section class="vendors-section @if($premiumConsultants->isEmpty()) d-none @endif" id="consultantsPremiumSection">
          <div class="vendors-section__head">
            <h2><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium Consultants</h2>
            <a
              href="{{ route('frontend.consultants.premium') }}"
              class="vendors-section__link"
            >View All Premium Consultants <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a>
          </div>
          <div class="vendors-premium-track" id="consultantsPremiumTrack">
            @include('frontend.consultants.partials.premium-cards', ['premiumConsultants' => $premiumConsultants, 'hasLocation' => $hasLocation])
          </div>
        </section>

      <section class="vendors-section" id="consultantsAllSection">
        <div class="vendors-section__head">
          <h2><i class="fa-solid fa-border-all" aria-hidden="true"></i> All Consultants</h2>
          <a
            href="{{ route('frontend.consultants.listings', request()->only(['category_id', 'subcategory_id', 'search', 'verified', 'payment', 'min_rating', 'radius'])) }}"
            class="vendors-section__link"
            id="consultantsViewAllLink"
          >View All Consultants <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a>
        </div>

        <div
          id="consultantsGrid"
          class="vendors-results-grid"
          data-next-page-url=""
        >
          @include('frontend.consultants.partials.cards', ['consultants' => $consultants, 'hasLocation' => $hasLocation])
        </div>

        <div class="vendors-pagination-wrap offer-pagination-wrap" id="consultantsPaginationState">
          @if ($consultants->total() > 0)
            <p class="offer-pagination-summary mb-0" id="consultantsSummaryText">
              Showing {{ min($consultants->count(), 12) }} of {{ $consultants->total() }} consultants
            </p>
          @else
            <p class="offer-pagination-summary mb-0 d-none" id="consultantsSummaryText"></p>
          @endif
          <p class="offer-pagination-loading mb-0 d-none" id="consultantsLoadingText">Loading consultants…</p>
        </div>
      </section>

      <section class="vendors-cta">
        <div class="vendors-cta__icon" aria-hidden="true">
          <i class="fa-solid fa-user-tie"></i>
        </div>
        <div class="vendors-cta__copy">
          <h3>Are you a consultant?</h3>
          <p>List your consultancy on SoilnWater and reach clients who need your expertise.</p>
        </div>
        <div class="vendors-cta__actions">
          <a href="{{ $listBusinessUrl }}" class="vendors-cta__primary">List Your Profile</a>
          <a href="{{ $learnMoreUrl }}" class="vendors-cta__secondary">Learn More</a>
        </div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/consultants-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
