@extends('frontend.layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
  <style>
    .vendors-listing-sec .vendors-search-bar {
      background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
      border: 1px solid #dbe6f3;
      border-radius: 14px;
      padding: .95rem;
    }

    .vendors-store-grid {
      --bs-gutter-x: 1rem;
      --bs-gutter-y: 1.15rem;
    }

    .vendor-store-card {
      display: flex;
      flex-direction: column;
      height: 100%;
      border: 1px solid #dbe6f3;
      border-radius: 18px;
      overflow: hidden;
      background: #fff;
      color: inherit;
      box-shadow: 0 8px 24px rgba(15, 51, 88, 0.06);
      transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease;
    }

    .vendor-store-card:hover {
      transform: translateY(-6px);
      border-color: #b9d0ea;
      box-shadow: 0 18px 36px rgba(15, 51, 88, 0.14);
      color: inherit;
    }

    .vendor-store-card__hero {
      position: relative;
      aspect-ratio: 3 / 4;
      height: auto;
      overflow: hidden;
      background: #eef3f6;
    }

    .vendor-store-card__cover {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      display: block;
      image-rendering: auto;
      transition: transform .4s ease;
    }

    .vendor-store-card:hover .vendor-store-card__cover {
      transform: scale(1.06);
    }

    .vendor-store-card__shade {
      position: absolute;
      inset: auto 0 0 0;
      height: 42%;
      background: linear-gradient(180deg, rgba(8, 28, 52, 0) 0%, rgba(8, 28, 52, 0.22) 100%);
      pointer-events: none;
    }

    .vendor-store-card__premium-tag {
      position: absolute;
      top: .5rem;
      left: .5rem;
      z-index: 2;
      display: inline-flex;
      align-items: center;
      padding: .22rem .5rem;
      border-radius: 999px;
      background: linear-gradient(135deg, #f6e6a8, #c9a227);
      color: #4a3600;
      font-size: .62rem;
      font-weight: 800;
      letter-spacing: .03em;
      text-transform: uppercase;
      box-shadow: 0 6px 16px rgba(201, 162, 39, 0.28);
    }

    .vendor-store-card__product-tag {
      position: absolute;
      top: .5rem;
      right: .5rem;
      z-index: 2;
      display: inline-flex;
      align-items: center;
      padding: .22rem .5rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.92);
      color: #0f3358;
      font-size: .62rem;
      font-weight: 700;
      backdrop-filter: blur(6px);
    }

    .vendor-store-card__avatar {
      position: absolute;
      left: .75rem;
      bottom: -1.2rem;
      z-index: 3;
      width: 3.35rem;
      height: 3.35rem;
      border-radius: 14px;
      padding: .14rem;
      background: #fff;
      box-shadow: 0 10px 24px rgba(15, 51, 88, 0.16);
    }

    .vendor-store-card__avatar img {
      width: 100%;
      height: 100%;
      border-radius: 13px;
      object-fit: cover;
      display: block;
      background: #f3f7fb;
    }

    .vendor-store-card__panel {
      display: flex;
      flex: 1;
      flex-direction: column;
      gap: .55rem;
      padding: 1.7rem .75rem .8rem;
      min-height: 0;
    }

    .vendor-store-card__title-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: .55rem;
    }

    .vendor-store-card__name {
      margin: 0;
      font-family: 'Manrope', sans-serif;
      font-size: .92rem;
      font-weight: 800;
      line-height: 1.3;
      color: #0f3358;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
      overflow: hidden;
    }

    .vendor-store-card__meta {
      display: flex;
      flex-wrap: wrap;
      gap: .45rem;
    }

    .vendor-store-card__chip {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: .32rem .62rem;
      border-radius: 999px;
      background: #eef4fb;
      color: #4d6480;
      font-size: .72rem;
      font-weight: 600;
      line-height: 1.2;
    }

    .vendor-store-card__chip i {
      color: #2f68ad;
      font-size: .68rem;
    }

    .vendor-store-card__chip--distance {
      background: #e8f3ff;
      color: #1f66b4;
    }

    .vendor-store-card__featured {
      margin: 0;
      padding: .62rem .72rem;
      border-radius: 12px;
      background: #f8fbff;
      border: 1px dashed #c7dcfa;
      color: #52667a;
      font-size: .76rem;
      line-height: 1.45;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
      overflow: hidden;
    }

    .vendor-store-card__featured span {
      display: block;
      margin-bottom: .15rem;
      color: #1f66b4;
      font-size: .66rem;
      font-weight: 800;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .vendor-store-card__cta {
      margin-top: auto;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .45rem;
      width: 100%;
      padding: .62rem .85rem;
      border-radius: 999px;
      background: linear-gradient(90deg, #1f66b4 0%, #174684 100%);
      color: #fff;
      font-size: .78rem;
      font-weight: 800;
      letter-spacing: .02em;
      transition: box-shadow .2s ease, transform .2s ease;
    }

    .vendor-store-card:hover .vendor-store-card__cta {
      box-shadow: 0 10px 22px rgba(31, 102, 180, 0.28);
      transform: translateY(-1px);
    }

    .vendor-store-card.is-premium-card {
      border: 2px solid #c9a227;
      box-shadow:
        0 12px 30px rgba(201, 162, 39, 0.18),
        inset 0 0 0 1px rgba(255, 255, 255, 0.75);
    }

    .vendor-store-card.is-premium-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #f6e6a8 0%, #c9a227 50%, #f6e6a8 100%);
      z-index: 4;
    }

    .vendor-store-card.is-premium-card {
      position: relative;
    }

    .vendor-store-card.is-premium-card .vendor-store-card__avatar {
      box-shadow:
        0 10px 24px rgba(15, 51, 88, 0.16),
        0 0 0 2px rgba(201, 162, 39, 0.35);
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

    @media (max-width: 575.98px) {
      .vendor-store-card__hero {
        aspect-ratio: 3 / 4;
      }

      .vendor-store-card__avatar {
        width: 3.1rem;
        height: 3.1rem;
        bottom: -1.1rem;
      }
    }
  </style>
@endpush

@section('content')
<div class="main-wrap">
  <div class="main-col">
    @include('frontend.premium.partials.listing-cta', ['type' => 'vendor'])

    <section class="sec vendors-listing-sec">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-store"></i></span> All Vendors</div>
      </div>

      <p class="small text-muted mb-3">
        @if ($hasLocation)
          Showing nearby premium vendors first, then nearby normal vendors.
        @else
          Showing latest premium vendors first, followed by latest normal vendors.
        @endif
      </p>

      <div class="vendors-search-bar mb-4">
        <div class="row g-2 align-items-end" id="vendorsFilterBar">
          <div class="col-12 col-lg-9">
            <label for="vendorsMarketFilterSearch" class="form-label mb-1">Search vendors</label>
            <input
              id="vendorsMarketFilterSearch"
              class="form-control"
              placeholder="Search by store name or product"
              value="{{ request('search') }}"
            >
          </div>
          <div class="col-12 col-lg-3 d-grid">
            <button type="button" id="vendorsMarketClearSearch" class="btn btn-outline-secondary">
              <i class="fa-solid fa-filter-circle-xmark me-1"></i> Clear
            </button>
          </div>
        </div>
      </div>

      <div
        id="vendorsGrid"
        class="vendors-store-grid row row-cols-1 row-cols-sm-2 row-cols-lg-4 row-cols-xl-5 g-3"
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
    </section>
  </div>
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
