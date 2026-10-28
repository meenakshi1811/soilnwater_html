@extends('frontend.layouts.app')
   
@section('content')
@php
  $marketBannerImage = data_get($homepageSetting ?? null, 'ads_market_banner_image');
@endphp

<section class="mb-2 mb-md-4">
  <img src="{{ $marketBannerImage ? asset($marketBannerImage) : "https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=2200&q=80" }}" alt="Ads market banner" class="w-100" style="display:block;max-height:220px;object-fit:cover;">
</section>

<div class="container-fluid pt-2 pb-4 pt-md-4 py-lg-5 px-3 px-lg-4 ads-market-page">

    <div class="row g-2 mb-3 align-items-end ads-market-filter-wrap" id="adsFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="col col-md-4 order-1">
            <label for="adsMarketFilterSearch" class="form-label mb-1">Search</label>
            <div class="ads-market-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input id="adsMarketFilterSearch" class="form-control" placeholder="Search ads by title" value="{{ request('search') }}">
            </div>
        </div>
        {{-- Phones: the dropdowns on a line of their own, under the search box and its clear button --}}
        <div class="w-100 d-md-none order-2"></div>
        <div class="col-6 col-md-3 order-3 order-md-2">
            <label for="adsMarketFilterCategory" class="form-label mb-1">Category</label>
            <select id="adsMarketFilterCategory" class="form-select">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3 order-4 order-md-3">
            <label for="adsMarketFilterSubcategory" class="form-label mb-1">Subcategory</label>
            <select id="adsMarketFilterSubcategory" class="form-select" disabled>
                <option value="">All subcategories</option>
            </select>
        </div>
        {{-- (on phones beside the search box, its icon only) --}}
        <div class="col-auto col-md-2 order-1 order-md-4 d-grid d-md-flex gap-2">
            <button type="button" id="adsMarketClearFilters" class="btn btn-outline-secondary w-100 ads-market-filter-clear" aria-label="Clear filters">
                <i class="fa-solid fa-filter-circle-xmark"></i><span class="d-none d-md-inline ms-1">Clear</span>
            </button>
        </div>
    </div>

    <div id="adsGrid" class="ads-market-grid" data-next-page-url="{{ $ads->nextPageUrl() }}">
        @include('frontend.ads.partials.cards', ['ads' => $ads, 'selectedCategoryNamesByAdId' => $selectedCategoryNamesByAdId ?? []])
    </div>

    <div class="mt-4 offer-pagination-wrap">
        <p class="offer-pagination-summary mb-0" id="adsSummaryText">
            @if ($ads->total() > 0)
                Showing {{ $ads->firstItem() }} to {{ $ads->lastItem() }} of {{ $ads->total() }} results
            @endif
        </p>
        <p class="offer-pagination-loading mb-0 d-none" id="adsLoadingText">Loading more ads…</p>
    </div>
    <div id="adsScrollSentinel" class="offer-scroll-sentinel" aria-hidden="true"></div>
</div>
@endsection

@push('styles')
<style>
.offer-login-message{display:flex;gap:.8rem;align-items:flex-start;background:#f8faff;border:1px solid #d6e4ff;border-radius:12px;padding:.85rem .9rem;margin-top:.75rem}
.offer-login-message-icon{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#e8f0ff;color:#2457c5;flex:0 0 34px}
.offer-login-message-title{font-size:1rem;font-weight:700;color:#1d3557}
.offer-login-message-text{font-size:.9rem;color:#5d6b82}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
const adsGrid = document.getElementById('adsGrid');
if (!adsGrid) return;
const searchFilter = document.getElementById('adsMarketFilterSearch'); const categoryFilter = document.getElementById('adsMarketFilterCategory'); const subcategoryFilter = document.getElementById('adsMarketFilterSubcategory');
const clearFiltersBtn = document.getElementById('adsMarketClearFilters');
const loadingText = document.getElementById('adsLoadingText'); const summaryText = document.getElementById('adsSummaryText'); const scrollSentinel = document.getElementById('adsScrollSentinel');
let nextPageUrl = adsGrid.dataset.nextPageUrl || ''; let isLoading = false; let debounce;
const adsMarketFillers = @json($sponsoredFillers);
const adsMarketBlankSizes = @json($sponsoredBlankSizes ?? []);
const adsMarketStaticImages = @json($staticSponsoredImages ?? []);
const categories = JSON.parse(document.getElementById('adsFilterBar').dataset.categories || '[]');
function populateSubcategories(){const id=categoryFilter.value;subcategoryFilter.innerHTML='<option value="">All subcategories</option>';const cat=categories.find(c=>String(c.id)===String(id));if(!cat||!cat.children.length){subcategoryFilter.disabled=true;return;}cat.children.forEach(child=>{const o=document.createElement('option');o.value=child.id;o.textContent=child.name;if(String(new URLSearchParams(location.search).get('subcategory_id')||'')===String(child.id)) o.selected=true; subcategoryFilter.appendChild(o);});subcategoryFilter.disabled=false;}
populateSubcategories(); categoryFilter.addEventListener('change',()=>{populateSubcategories();refreshAds();}); subcategoryFilter.addEventListener('change',refreshAds); searchFilter.addEventListener('input',()=>{clearTimeout(debounce);debounce=setTimeout(refreshAds,300);});
if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', function () {
        searchFilter.value = '';
        categoryFilter.value = '';
        subcategoryFilter.innerHTML = '<option value="">All subcategories</option>';
        subcategoryFilter.disabled = true;
        refreshAds();
    });
}
function buildUrl(base){const u=new URL(base,window.location.origin);const p=new URLSearchParams();if(searchFilter.value.trim()) p.set('search',searchFilter.value.trim());if(categoryFilter.value) p.set('category_id',categoryFilter.value);if(subcategoryFilter.value) p.set('subcategory_id',subcategoryFilter.value);u.search=p.toString();return u.toString();}

function parseCardsHtml(html){
    const temp = document.createElement('div');
    temp.innerHTML = html || '';
    return {
        source: temp.querySelector('#adsSource'),
        layout: temp.querySelector('#adsLayout')
    };
}
// A page that was loading when the filters changed is dropped instead of being added
let requestGeneration = 0;
let refreshController = null;
let retryDelay = 3000;
let sentinelTimer;

function renderCards(options) {
    if (typeof window.renderAdsMarketCards === 'function') {
        window.renderAdsMarketCards('ads', adsMarketFillers, Object.assign({ blankSizes: adsMarketBlankSizes, staticImages: adsMarketStaticImages }, options));
    }
}

async function fetchAds(url, signal) {
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: signal });
    if (!res.ok) {
        throw new Error('Ads request failed: ' + res.status);
    }

    return res.json();
}

// The ads are laid out a few frames after they arrive (and again when the page finishes loading),
// so the sentinel can look visible before the grid has its height: check once it has settled,
// and again after each page so a screen that is still not full keeps loading
function checkSentinel(delay) {
    clearTimeout(sentinelTimer);
    sentinelTimer = setTimeout(function () {
        requestAnimationFrame(function () {
            if (nextPageUrl && !isLoading && scrollSentinel && scrollSentinel.getBoundingClientRect().top <= window.innerHeight + 250) {
                loadMore();
            }
        });
    }, delay === undefined ? 300 : delay);
}

async function refreshAds() {
    const generation = ++requestGeneration;
    if (refreshController) {
        refreshController.abort();
    }
    const controller = new AbortController();
    refreshController = controller;

    isLoading = true;
    loadingText.classList.remove('d-none');

    try {
        const payload = await fetchAds(buildUrl('{{ route('frontend.ads.index') }}'), controller.signal);
        if (generation !== requestGeneration) {
            return;
        }

        if (!payload.total) {
            adsGrid.innerHTML = '<div class="text-center py-4"><h4>No result found</h4></div>';
            nextPageUrl = '';
            adsGrid.dataset.nextPageUrl = '';
            summaryText.textContent = '';
            return;
        }

        adsGrid.innerHTML = payload.html || '';
        nextPageUrl = payload.next_page_url || '';
        adsGrid.dataset.nextPageUrl = nextPageUrl;
        summaryText.textContent = `Showing 1 to ${payload.loaded_to} of ${payload.total} results`;
        renderCards({ resetFillers: true });
    } catch (error) {
        // Aborted by a newer filter change, or the request failed: the current ads stay
    } finally {
        if (generation === requestGeneration) {
            refreshController = null;
            isLoading = false;
            loadingText.classList.add('d-none');
            checkSentinel();
        }
    }
}

async function loadMore() {
    if (!nextPageUrl || isLoading) {
        return;
    }

    const generation = requestGeneration;
    isLoading = true;
    loadingText.classList.remove('d-none');

    let loaded = false;
    try {
        const p = await fetchAds(nextPageUrl);
        if (generation !== requestGeneration) {
            return;
        }

        const parsed = parseCardsHtml(p.html || '');
        const existingSource = adsGrid.querySelector('#adsSource');
        if (existingSource && parsed.source) {
            existingSource.insertAdjacentHTML('beforeend', parsed.source.innerHTML);
        } else if (parsed.source) {
            adsGrid.insertAdjacentHTML('beforeend', p.html || '');
        }

        nextPageUrl = p.next_page_url || '';
        adsGrid.dataset.nextPageUrl = nextPageUrl;
        summaryText.textContent = p.total ? `Showing 1 to ${p.loaded_to} of ${p.total} results` : '';
        renderCards({ append: true });
        loaded = true;
        retryDelay = 3000;
    } catch (error) {
        // A failed page (e.g. a dropped connection) is tried again shortly; nextPageUrl is kept
    } finally {
        if (generation === requestGeneration) {
            isLoading = false;
            loadingText.classList.add('d-none');

            if (loaded) {
                checkSentinel();
            } else {
                checkSentinel(retryDelay);
                retryDelay = Math.min(retryDelay * 2, 30000);
            }
        }
    }
}

if (scrollSentinel && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
            checkSentinel();
        }
    }, { rootMargin: '250px' }).observe(scrollSentinel);
}
});
</script>
@endpush
