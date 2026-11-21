{{--
  All of a store's products ("View all products" on the store page), laid out like the other
  listing pages: filters on the left, the title card, then the products with sort and pages.
  $vendor, $products (paginated), $totalProducts, $categories (id, name, count), $categoryNames,
  $sorts, $filters (search, category_id, deals, sort), $preview
--}}
@extends('frontend.layouts.app')

@php
  use App\Support\BusinessProfilePage;

  $biz = BusinessProfilePage::for('vendor', $vendor);
  $resetUrl = route('store.products.index', $vendor->slug);
  $hasFilters = $filters['search'] !== '' || $filters['category_id'] || $filters['deals'];
  $chosenCategory = $filters['category_id'] ? $categories->firstWhere('id', $filters['category_id']) : null;
@endphp

@section('meta_title', 'All products · '.$biz['name'].' | SoilnWater')
@section('meta_description', 'Browse all products from '.$biz['name'].' on SoilnWater.')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="vendors-page vendors-page--listings store-products-page">
  <nav class="marketplace-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('frontend.index') }}" class="marketplace-breadcrumb__link">
      <i class="fa-solid fa-house" aria-hidden="true"></i>
      <span>Home</span>
    </a>
    <span class="marketplace-breadcrumb__sep" aria-hidden="true">›</span>
    <a href="{{ $biz['listingUrl'] }}" class="marketplace-breadcrumb__link">{{ $biz['listingLabel'] }}</a>
    <span class="marketplace-breadcrumb__sep" aria-hidden="true">›</span>
    <a href="{{ $biz['url'] }}" class="marketplace-breadcrumb__link">{{ $biz['name'] }}</a>
    <span class="marketplace-breadcrumb__sep" aria-hidden="true">›</span>
    <span class="marketplace-breadcrumb__current" aria-current="page">All Products</span>
  </nav>

  @if($preview)
    <div class="alert alert-warning py-2 small"><i class="fa-solid fa-eye me-1" aria-hidden="true"></i> Preview — only staff can see this store until it is published.</div>
  @endif

  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Find a Product</h2>
          <p class="vendors-sidebar__lead">Search and filter {{ $biz['name'] }}'s products.</p>
        </div>
        @if($hasFilters)
          <a href="{{ $resetUrl }}" class="vendors-sidebar__reset">Reset All</a>
        @endif
      </div>

      <form method="GET" action="{{ $resetUrl }}" id="storeProductsFilters">
        <div class="vendors-filter-group">
          <label for="storeProductsSearch">Search</label>
          <div class="vendors-filter-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input id="storeProductsSearch" name="search" class="form-control" placeholder="Product name, brand or code..." value="{{ $filters['search'] }}">
          </div>
        </div>

        @if($categories->isNotEmpty())
          <div class="vendors-filter-group">
            <label for="storeProductsCategory">Category</label>
            <select id="storeProductsCategory" name="category_id" class="form-select">
              <option value="">All categories ({{ number_format($totalProducts) }})</option>
              @foreach($categories as $category)
                <option value="{{ $category['id'] }}" @selected($filters['category_id'] === $category['id'])>{{ $category['name'] }} ({{ number_format($category['count']) }})</option>
              @endforeach
            </select>
          </div>
        @endif

        <div class="vendors-filter-group">
          <span class="d-block mb-2 fw-bold small">Offers</span>
          <div class="vendors-filter-checks">
            <label class="vendors-filter-check">
              <input type="checkbox" name="deals" value="1" @checked($filters['deals'])>
              <i class="fa-solid fa-tags" aria-hidden="true"></i>
              On offer (discounted)
            </label>
          </div>
        </div>

        <input type="hidden" name="sort" value="{{ $filters['sort'] }}" id="storeProductsSortValue">

        <button type="submit" class="vendors-filter-apply">
          <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i> Apply Filters
        </button>
      </form>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero vendors-hero--compact store-products-hero">
        <div class="vendors-hero__intro">
          <span class="vendors-hero__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-store"></i></span>
            {{ $biz['name'] }}
          </span>
          <h1>All Products</h1>
          <p>Everything {{ $biz['name'] }} sells on SoilnWater. Open a product for its details, or send the store an enquiry.</p>
        </div>
        @if($biz['logo'])
          <a href="{{ $biz['url'] }}" class="store-products-hero__logo" aria-label="Visit {{ $biz['name'] }}">
            <img src="{{ $biz['logo'] }}" alt="{{ $biz['name'] }} logo">
          </a>
        @endif
      </section>

      <section class="vendors-section">
        <div class="vendors-section__head">
          <h2>
            <i class="fa-solid fa-box-open" aria-hidden="true"></i>
            {{ $chosenCategory['name'] ?? 'All Products' }}
            <span class="store-products-count">{{ number_format($products->total()) }}</span>
          </h2>
          <a href="{{ $biz['url'] }}" class="vendors-section__link">
            Back to store <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
          </a>
        </div>

        <div class="vendors-all__toolbar">
          <p class="store-products-summary mb-0">
            @if($products->total() > 0)
              Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }}
              @if($filters['search'] !== '') for “{{ $filters['search'] }}” @endif
            @endif
          </p>
          <div class="vendors-all__controls">
            <select class="form-select" aria-label="Sort products" id="storeProductsSort">
              @foreach($sorts as $value => $label)
                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="vendors-results-grid store-products-grid">
          @forelse($products as $product)
            @include('frontend.store.partials.product-listing-card', ['product' => $product, 'slug' => $vendor->slug, 'categoryNames' => $categoryNames])
          @empty
            <div class="vendors-empty-state">
              <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-box-open"></i></div>
              <h3>No products found</h3>
              <p>
                @if($hasFilters)
                  Try another search or category. <a href="{{ $resetUrl }}">Show all products</a>
                @else
                  This store hasn't listed any products yet.
                @endif
              </p>
            </div>
          @endforelse
        </div>

        @if($products->hasPages())
          <div class="store-products-pagination">
            {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
          </div>
        @endif
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Sorting applies straight away (with the current filters)
  document.getElementById('storeProductsSort')?.addEventListener('change', function () {
    var form = document.getElementById('storeProductsFilters');
    document.getElementById('storeProductsSortValue').value = this.value;
    if (form) form.submit();
  });
</script>
@endpush
