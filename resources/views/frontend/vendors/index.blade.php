@extends('frontend.layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
  <style>
    .vendors-market-page {
      background: linear-gradient(180deg, #f8fbff 0%, #ffffff 220px);
    }

    .vendors-market-head {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1rem;
    }

    .vendors-market-title {
      font-family: 'Manrope', sans-serif;
      font-size: clamp(1.5rem, 2.4vw, 2rem);
      font-weight: 800;
      color: #0f3358;
      margin-bottom: .35rem;
    }

    .vendors-market-title .icon-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2.2rem;
      height: 2.2rem;
      border-radius: 10px;
      background: linear-gradient(135deg, #2e7d32, #1b5e20);
      color: #fff;
      font-size: .95rem;
      margin-right: .55rem;
      vertical-align: middle;
    }

    .vendors-market-subtitle {
      color: #5b6f84;
      font-size: .95rem;
      margin-bottom: 0;
      max-width: 42rem;
    }

    .vendors-market-filter {
      background: #fff;
      border: 1px solid #dbe6f3;
      border-radius: 14px;
      padding: 1rem;
      box-shadow: 0 8px 24px rgba(15, 51, 88, 0.05);
    }

    .vendor-market-card {
      border-radius: 16px;
      overflow: hidden;
      transition: transform .22s ease, box-shadow .22s ease;
      background: #fff;
    }

    .vendor-market-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 34px rgba(15, 51, 88, 0.12) !important;
    }

    .vendor-market-card__media {
      position: relative;
      display: block;
      aspect-ratio: 4 / 3;
      overflow: hidden;
      text-decoration: none;
    }

    .vendor-market-card__image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform .35s ease;
    }

    .vendor-market-card:hover .vendor-market-card__image {
      transform: scale(1.04);
    }

    .vendor-market-card__count {
      position: absolute;
      left: .75rem;
      bottom: .75rem;
      background: rgba(15, 51, 88, 0.82);
      color: #fff;
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .02em;
      padding: .35rem .65rem;
      border-radius: 999px;
      backdrop-filter: blur(4px);
    }

    .vendor-market-card__body {
      padding: .95rem 1rem 1rem;
      gap: .55rem;
    }

    .vendor-market-card__head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: .5rem;
    }

    .vendor-market-card__name {
      font-family: 'Manrope', sans-serif;
      font-size: .98rem;
      font-weight: 800;
      color: #0f3358;
      margin: 0;
      line-height: 1.35;
    }

    .vendor-market-card__name a {
      color: inherit;
    }

    .vendor-market-card__meta {
      font-size: .82rem;
      color: #66788f;
    }

    .vendor-market-card__distance {
      color: #2f68ad;
      font-weight: 600;
    }

    .vendor-market-card__btn {
      border: 0;
      border-radius: 999px;
      background: linear-gradient(90deg, #1f66b4 0%, #1e4f9b 100%);
      color: #fff;
      font-size: .78rem;
      font-weight: 700;
      line-height: 1.25;
      padding: .55rem .85rem;
      width: 100%;
      transition: box-shadow .2s ease, transform .2s ease;
    }

    .vendor-market-card__btn:hover {
      color: #fff;
      box-shadow: 0 8px 18px rgba(31, 102, 180, 0.28);
      transform: translateY(-1px);
    }

    .vendor-market-card.is-premium-card {
      border: 1px solid #d9c27a !important;
      box-shadow:
        0 10px 28px rgba(201, 162, 39, 0.16),
        inset 0 0 0 1px rgba(255, 255, 255, 0.75) !important;
    }

    .vendor-market-card.is-premium-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #f6e6a8 0%, #c9a227 50%, #f6e6a8 100%);
      z-index: 2;
    }

    .vendor-market-card.is-premium-card {
      position: relative;
    }

    .vendor-empty-state-card {
      border: 1px dashed #c7dcfa;
      border-radius: 16px;
      padding: 2.5rem 1.25rem;
      text-align: center;
      background: #f8fbff;
    }

    .vendor-empty-state-icon {
      width: 3rem;
      height: 3rem;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #e8f0ff;
      color: #2457c5;
      font-size: 1.2rem;
      margin-bottom: .85rem;
    }

    .vendor-empty-state-title {
      font-family: 'Manrope', sans-serif;
      font-size: 1.1rem;
      font-weight: 800;
      color: #0f3358;
    }

    .vendor-empty-state-text {
      color: #66788f;
      font-size: .92rem;
    }

    @media (max-width: 767.98px) {
      .vendors-market-filter .btn {
        width: 100%;
      }
    }
  </style>
@endpush

@section('content')
@php
  $marketBannerImage = data_get($homepageSetting ?? null, 'hero_banner_image');
@endphp

<section class="mb-0">
  <img
    src="{{ $marketBannerImage ? asset($marketBannerImage) : 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=2200&q=80' }}"
    alt="Vendors marketplace banner"
    class="w-100"
    style="display:block;max-height:220px;object-fit:cover;"
  >
</section>

<div class="container-fluid py-4 py-lg-5 px-3 px-lg-4 vendors-market-page">
  @include('frontend.premium.partials.listing-cta', ['type' => 'vendor'])

  <div class="vendors-market-head mt-4 mb-3">
    <div>
      <h1 class="vendors-market-title">
        <span class="icon-badge"><i class="fa-solid fa-store"></i></span>
        Vendor Marketplace
      </h1>
      <p class="vendors-market-subtitle">
        @if ($hasLocation)
          Discover nearby premium vendors first, followed by other stores around your location.
        @else
          Browse premium vendors first, then explore the latest stores on SoilnWater.
        @endif
      </p>
    </div>
  </div>

  <div class="vendors-market-filter mb-4">
    <div class="row g-2 align-items-end" id="vendorsFilterBar">
      <div class="col-12 col-lg-8">
        <label for="vendorsMarketFilterSearch" class="form-label mb-1">Search vendors</label>
        <input
          id="vendorsMarketFilterSearch"
          class="form-control"
          placeholder="Search by store name or product"
          value="{{ request('search') }}"
        >
      </div>
      <div class="col-12 col-lg-4 d-grid d-lg-flex gap-2">
        <button type="button" id="vendorsMarketClearSearch" class="btn btn-outline-secondary">
          <i class="fa-solid fa-filter-circle-xmark me-1"></i> Clear
        </button>
      </div>
    </div>
  </div>

  <div
    id="vendorsGrid"
    class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3"
    data-next-page-url="{{ $vendors->nextPageUrl() }}"
  >
    @include('frontend.vendors.partials.cards', ['vendors' => $vendors, 'hasLocation' => $hasLocation])
  </div>

  <div class="mt-4 offer-pagination-wrap" id="vendorsPaginationState">
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
</div>
@endsection

@push('scripts')
<script>
  (function () {
    const vendorsGrid = document.getElementById('vendorsGrid');
    if (!vendorsGrid) return;

    const searchFilter = document.getElementById('vendorsMarketFilterSearch');
    const clearSearchBtn = document.getElementById('vendorsMarketClearSearch');
    const loadingText = document.getElementById('vendorsLoadingText');
    const summaryText = document.getElementById('vendorsSummaryText');
    const scrollSentinel = document.getElementById('vendorsScrollSentinel');

    let nextPageUrl = vendorsGrid.dataset.nextPageUrl || '';
    let isLoading = false;
    let debounceTimer;

    function setLoadingState(show) {
      if (!loadingText) return;
      loadingText.classList.toggle('d-none', !show);
    }

    function buildVendorsUrl(pageUrl) {
      const url = new URL(pageUrl || '{{ route('frontend.vendors.index') }}', window.location.origin);
      const searchValue = searchFilter ? searchFilter.value.trim() : '';

      if (searchValue) {
        url.searchParams.set('search', searchValue);
      } else {
        url.searchParams.delete('search');
      }

      return url.toString();
    }

    function updateSummary(payload) {
      if (!summaryText) return;

      if (payload.total > 0) {
        summaryText.textContent = `Showing 1 to ${payload.loaded_to} of ${payload.total} results`;
        summaryText.classList.remove('d-none');
      } else {
        summaryText.textContent = '';
        summaryText.classList.add('d-none');
      }
    }

    async function reloadVendorsFromStart() {
      if (isLoading) return;

      isLoading = true;
      setLoadingState(true);

      try {
        const response = await fetch(buildVendorsUrl('{{ route('frontend.vendors.index') }}'), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error('Failed to search vendors');
        }

        const payload = await response.json();
        vendorsGrid.innerHTML = payload.html || '';
        nextPageUrl = payload.next_page_url || '';
        vendorsGrid.dataset.nextPageUrl = nextPageUrl;
        updateSummary(payload);
      } catch (error) {
        console.error(error);
      } finally {
        isLoading = false;
        setLoadingState(false);
      }
    }

    async function loadNextVendorsPage() {
      if (!nextPageUrl || isLoading) return;

      isLoading = true;
      setLoadingState(true);

      try {
        const response = await fetch(buildVendorsUrl(nextPageUrl), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error('Failed to load more vendors');
        }

        const payload = await response.json();
        const emptyState = vendorsGrid.querySelector('.vendor-empty-state');

        if (emptyState) {
          emptyState.remove();
        }

        if (payload.html) {
          vendorsGrid.insertAdjacentHTML('beforeend', payload.html);
        }

        nextPageUrl = payload.next_page_url || '';
        vendorsGrid.dataset.nextPageUrl = nextPageUrl;
        updateSummary(payload);
      } catch (error) {
        console.error(error);
      } finally {
        isLoading = false;
        setLoadingState(false);
      }
    }

    if (searchFilter) {
      searchFilter.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(reloadVendorsFromStart, 300);
      });
    }

    if (clearSearchBtn) {
      clearSearchBtn.addEventListener('click', function () {
        if (searchFilter) {
          searchFilter.value = '';
        }
        reloadVendorsFromStart();
      });
    }

    if (scrollSentinel && 'IntersectionObserver' in window) {
      const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            loadNextVendorsPage();
          }
        });
      }, {
        rootMargin: '300px 0px',
      });

      observer.observe(scrollSentinel);
    } else {
      window.addEventListener('scroll', function () {
        if (!nextPageUrl || isLoading || !scrollSentinel) return;

        const sentinelTop = scrollSentinel.getBoundingClientRect().top;
        if (sentinelTop <= window.innerHeight + 300) {
          loadNextVendorsPage();
        }
      }, { passive: true });
    }
  })();
</script>
@endpush
