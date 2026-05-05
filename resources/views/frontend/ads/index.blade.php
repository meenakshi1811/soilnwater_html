@extends('frontend.layouts.app')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-4 ads-market-page">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h1 class="h3 mb-0">Ads Market</h1>
        <a href="{{ route('frontend.index') }}" class="view-all">Back to home ▶</a>
    </div>

    <div class="row g-2 mb-3 align-items-end ads-market-filter-wrap" id="adsFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="col-12 col-md-4">
            <label for="adsMarketFilterSearch" class="form-label mb-1">Search</label>
            <input id="adsMarketFilterSearch" class="form-control" placeholder="Search ads by title" value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-3">
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
        <div class="col-12 col-md-3">
            <label for="adsMarketFilterSubcategory" class="form-label mb-1">Subcategory</label>
            <select id="adsMarketFilterSubcategory" class="form-select" disabled>
                <option value="">All subcategories</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-grid d-md-flex gap-2">
            <button type="button" id="adsMarketClearFilters" class="btn btn-outline-secondary w-100">
                <i class="fa-solid fa-filter-circle-xmark me-1"></i> Clear
            </button>
        </div>
    </div>

    <div id="adsGrid" class="ads-market-grid" data-next-page-url="{{ $ads->nextPageUrl() }}">
        @include('frontend.ads.partials.cards', ['ads' => $ads])
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

@include('frontend.ads.partials.modals')
@endsection

@push('scripts')
<script>
// unchanged behavior; formatting only for reliability
// ...
document.addEventListener('DOMContentLoaded', function () {
const adModal = document.getElementById('adDetailsModal'); const adsGrid = document.getElementById('adsGrid'); if (!adsGrid) return;
const adEnlargeBtn = document.getElementById('adDetailsEnlargeBtn');
const adReportForm = document.getElementById('adReportForm');
const openAdReportPopupBtn = document.getElementById('openAdReportPopupBtn');
const closeAdReportPopupBtn = document.getElementById('closeAdReportPopupBtn');
const adReportPopupWrap = document.getElementById('adReportPopupWrap');
const adImageEnlargePreview = document.getElementById('adImageEnlargePreview');
const adDetailsModalInstance = adModal ? new bootstrap.Modal(adModal) : null;
const searchFilter = document.getElementById('adsMarketFilterSearch'); const categoryFilter = document.getElementById('adsMarketFilterCategory'); const subcategoryFilter = document.getElementById('adsMarketFilterSubcategory');
const clearFiltersBtn = document.getElementById('adsMarketClearFilters');
const loadingText = document.getElementById('adsLoadingText'); const summaryText = document.getElementById('adsSummaryText'); const scrollSentinel = document.getElementById('adsScrollSentinel');
let nextPageUrl = adsGrid.dataset.nextPageUrl || ''; let isLoading = false; let debounce;

function layoutAdsGrid(){
    if (window.matchMedia('(max-width: 768px)').matches) { adsGrid.style.height = 'auto'; return; }
    const items = Array.from(adsGrid.querySelectorAll('.ads-market-grid-item'));
    if (!items.length) { adsGrid.style.height = '0px'; return; }
    const gap = 16;
    const unit = 4;
    items.forEach((item)=>{ item.style.position='absolute'; item.style.left='0px'; item.style.top='0px'; });
    const containerWidth = adsGrid.clientWidth;
    const cols = Math.max(1, Math.floor(containerWidth / unit));
    const colHeights = Array(cols).fill(0);
    items.forEach((item)=>{
        const itemWidth = item.offsetWidth || 320;
        const itemHeight = item.offsetHeight || 220;
        const span = Math.min(cols, Math.max(1, Math.ceil(itemWidth / unit)));
        let bestCol = 0, bestTop = Number.POSITIVE_INFINITY;
        for (let start=0; start<=cols-span; start++) {
            const top = Math.max(...colHeights.slice(start, start + span));
            if (top < bestTop) { bestTop = top; bestCol = start; }
        }
        const left = bestCol * unit;
        item.style.left = `${left}px`;
        item.style.top = `${bestTop}px`;
        const nextHeight = bestTop + itemHeight + gap;
        for (let i=bestCol; i<bestCol+span; i++) colHeights[i] = nextHeight;
    });
    adsGrid.style.height = `${Math.max(...colHeights)}px`;
}
window.addEventListener('resize', ()=>{ clearTimeout(debounce); debounce=setTimeout(layoutAdsGrid,120); });
window.addEventListener('load', layoutAdsGrid);

function bindImageLayoutRefresh(scope){
    const root = scope || adsGrid;
    const images = Array.from(root.querySelectorAll('img'));
    images.forEach((img)=>{
        if (img.complete) return;
        img.addEventListener('load', layoutAdsGrid, { once: true });
        img.addEventListener('error', layoutAdsGrid, { once: true });
    });
}

const categories = JSON.parse(document.getElementById('adsFilterBar').dataset.categories || '[]');
function populateSubcategories(){const id=categoryFilter.value;subcategoryFilter.innerHTML='<option value="">All subcategories</option>';const cat=categories.find(c=>String(c.id)===String(id));if(!cat||!cat.children.length){subcategoryFilter.disabled=true;return;}cat.children.forEach(child=>{const o=document.createElement('option');o.value=child.id;o.textContent=child.name;if(String(new URLSearchParams(location.search).get('subcategory_id')||'')===String(child.id)) o.selected=true; subcategoryFilter.appendChild(o);});subcategoryFilter.disabled=false;}
populateSubcategories(); layoutAdsGrid(); bindImageLayoutRefresh(); categoryFilter.addEventListener('change',()=>{populateSubcategories();refreshAds();}); subcategoryFilter.addEventListener('change',refreshAds); searchFilter.addEventListener('input',()=>{clearTimeout(debounce);debounce=setTimeout(refreshAds,300);});
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
async function refreshAds(){if(isLoading) return;isLoading=true;loadingText.classList.remove('d-none');const res=await fetch(buildUrl('{{ route('frontend.ads.index') }}'),{headers:{'X-Requested-With':'XMLHttpRequest'}});const payload=await res.json();adsGrid.innerHTML=payload.html||'';nextPageUrl=payload.next_page_url||'';adsGrid.dataset.nextPageUrl=nextPageUrl;summaryText.textContent=payload.total?`Showing 1 to ${payload.loaded_to} of ${payload.total} results`:'';layoutAdsGrid();bindImageLayoutRefresh();loadingText.classList.add('d-none');isLoading=false;}
async function loadMore(){if(!nextPageUrl||isLoading) return;isLoading=true;loadingText.classList.remove('d-none');const r=await fetch(nextPageUrl,{headers:{'X-Requested-With':'XMLHttpRequest'}});const p=await r.json();adsGrid.insertAdjacentHTML('beforeend',p.html||'');nextPageUrl=p.next_page_url||'';adsGrid.dataset.nextPageUrl=nextPageUrl;summaryText.textContent=p.total?`Showing 1 to ${p.loaded_to} of ${p.total} results`:'';layoutAdsGrid();bindImageLayoutRefresh();loadingText.classList.add('d-none');isLoading=false;}
if(scrollSentinel && 'IntersectionObserver' in window){new IntersectionObserver(e=>{if(e[0].isIntersecting) loadMore();},{rootMargin:'250px'}).observe(scrollSentinel);}
adsGrid.addEventListener('click',function(e){const trigger=e.target.closest('.js-ad-modal-trigger');if(!trigger) return;document.getElementById('adDetailsModalTitle').textContent=trigger.dataset.adTitle||'Ad Details';document.getElementById('adDetailsModalMeta').textContent=trigger.dataset.adMeta||'';document.getElementById('adDetailsModalDescription').textContent=trigger.dataset.adDescription||'';const img=trigger.dataset.adImage||'';const imgEl=document.getElementById('adDetailsModalImage');if(img){imgEl.src=img;imgEl.classList.remove('d-none');adEnlargeBtn.classList.remove('d-none');}else{imgEl.src='';imgEl.classList.add('d-none');adEnlargeBtn.classList.add('d-none');}const url=trigger.dataset.adUrl||location.href;document.getElementById('adShareLink').value=url;document.getElementById('adShareQr').src=`https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(url)}`;document.getElementById('adShareWhatsapp').href=`https://wa.me/?text=${encodeURIComponent('Check this ad: '+url)}`;document.getElementById('adShareFacebook').href=`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;document.getElementById('adShareInstagram').href=url;
if (adReportForm && trigger.dataset.adId) { adReportForm.action = `{{ url('/ads-market') }}/${trigger.dataset.adId}/report`; }
if (adReportPopupWrap) { adReportPopupWrap.classList.add('d-none'); }
if (adDetailsModalInstance) { adDetailsModalInstance.show(); }});

if (openAdReportPopupBtn && adReportPopupWrap) {
    openAdReportPopupBtn.addEventListener('click', function () {
        adReportPopupWrap.classList.remove('d-none');
    });
}

if (closeAdReportPopupBtn && adReportPopupWrap) {
    closeAdReportPopupBtn.addEventListener('click', function () {
        adReportPopupWrap.classList.add('d-none');
    });
}

if (adEnlargeBtn) { adEnlargeBtn.addEventListener('click', function () { const imgEl = document.getElementById('adDetailsModalImage'); if (!imgEl || !imgEl.src) return; adImageEnlargePreview.src = imgEl.src; new bootstrap.Modal(document.getElementById('adImageEnlargeModal')).show(); }); }
});
</script>
@endpush
