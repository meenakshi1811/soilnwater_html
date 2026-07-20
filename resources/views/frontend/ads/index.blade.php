@extends('frontend.layouts.app')
   
@section('content')
@php
  $marketBannerImage = data_get($homepageSetting ?? null, 'ads_market_banner_image');
@endphp

<section class="mb-4">
  <img src="{{ $marketBannerImage ? asset($marketBannerImage) : "https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=2200&q=80" }}" alt="Ads market banner" class="w-100" style="display:block;max-height:220px;object-fit:cover;">
</section>

<div class="container-fluid py-4 py-lg-5 px-3 px-lg-4 ads-market-page">

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

@include('frontend.ads.partials.modals')
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
// unchanged behavior; formatting only for reliability
// ...
document.addEventListener('DOMContentLoaded', function () {
const adModal = document.getElementById('adDetailsModal'); const adsGrid = document.getElementById('adsGrid'); if (!adsGrid) return;
const isLoggedIn = @json(auth()->check());
const adModalDialog = document.getElementById('adDetailsModalDialog');
const adEnlargeBtn = document.getElementById('adDetailsEnlargeBtn');
const adLoginMessageBox = document.getElementById('adLoginMessageBox');
const adSharePanel = document.getElementById('adSharePanel');
const adReportActions = document.getElementById('adReportActions');
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
const adsMarketFillers = @json($sponsoredFillers);
if(typeof window.renderAdsMarketCards==='function'){window.renderAdsMarketCards('ads', adsMarketFillers);}

function layoutAdsGrid(){
    const items = Array.from(adsGrid.querySelectorAll('.ads-market-grid-item'));
    items.forEach((item)=>{
        item.style.position = 'static';
        item.style.left = '';
        item.style.top = '';
    });
    adsGrid.style.height = 'auto';
}
window.addEventListener('resize', ()=>{ clearTimeout(debounce); debounce=setTimeout(layoutAdsGrid,120); });
const categories = JSON.parse(document.getElementById('adsFilterBar').dataset.categories || '[]');
function populateSubcategories(){const id=categoryFilter.value;subcategoryFilter.innerHTML='<option value="">All subcategories</option>';const cat=categories.find(c=>String(c.id)===String(id));if(!cat||!cat.children.length){subcategoryFilter.disabled=true;return;}cat.children.forEach(child=>{const o=document.createElement('option');o.value=child.id;o.textContent=child.name;if(String(new URLSearchParams(location.search).get('subcategory_id')||'')===String(child.id)) o.selected=true; subcategoryFilter.appendChild(o);});subcategoryFilter.disabled=false;}
populateSubcategories(); layoutAdsGrid(); categoryFilter.addEventListener('change',()=>{populateSubcategories();refreshAds();}); subcategoryFilter.addEventListener('change',refreshAds); searchFilter.addEventListener('input',()=>{clearTimeout(debounce);debounce=setTimeout(refreshAds,300);});
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
async function refreshAds(){if(isLoading) return;isLoading=true;loadingText.classList.remove('d-none');const res=await fetch(buildUrl('{{ route('frontend.ads.index') }}'),{headers:{'X-Requested-With':'XMLHttpRequest'}});const payload=await res.json();if(!payload.total){adsGrid.innerHTML='<div class="text-center py-4"><h4>No result found</h4></div>';nextPageUrl='';adsGrid.dataset.nextPageUrl='';summaryText.textContent='';loadingText.classList.add('d-none');isLoading=false;return;}adsGrid.innerHTML=payload.html||'';nextPageUrl=payload.next_page_url||'';adsGrid.dataset.nextPageUrl=nextPageUrl;summaryText.textContent=`Showing 1 to ${payload.loaded_to} of ${payload.total} results`;if(typeof window.renderAdsMarketCards==='function'){window.renderAdsMarketCards('ads', adsMarketFillers, { resetFillers: true });}layoutAdsGrid();loadingText.classList.add('d-none');isLoading=false;}
async function loadMore(){if(!nextPageUrl||isLoading) return;isLoading=true;loadingText.classList.remove('d-none');const r=await fetch(nextPageUrl,{headers:{'X-Requested-With':'XMLHttpRequest'}});const p=await r.json();const parsed=parseCardsHtml(p.html||'');const existingSource=adsGrid.querySelector('#adsSource');if(existingSource&&parsed.source){existingSource.insertAdjacentHTML('beforeend',parsed.source.innerHTML);}else if(parsed.source){adsGrid.insertAdjacentHTML('beforeend',p.html||'');}nextPageUrl=p.next_page_url||'';adsGrid.dataset.nextPageUrl=nextPageUrl;summaryText.textContent=p.total?`Showing 1 to ${p.loaded_to} of ${p.total} results`:'';if(typeof window.renderAdsMarketCards==='function'){window.renderAdsMarketCards('ads', adsMarketFillers, { append: true });}layoutAdsGrid();loadingText.classList.add('d-none');isLoading=false;}
if(scrollSentinel && 'IntersectionObserver' in window){new IntersectionObserver(e=>{if(e[0].isIntersecting) loadMore();},{rootMargin:'250px'}).observe(scrollSentinel);}

function syncAdModalSize(trigger){
    if(!adModalDialog || !trigger) return;
    const adWidth = Number(trigger.dataset.adW || 0);
    const adHeight = Number(trigger.dataset.adH || 0);
    const isLargeAd = adWidth >= 900 || adHeight >= 450;

    adModalDialog.classList.remove('modal-lg','modal-xl');
    adModalDialog.style.removeProperty('width');
    adModalDialog.style.removeProperty('max-width');

    if(isLargeAd){
        adModalDialog.classList.add('modal-xl');
        adModalDialog.style.width = 'min(100% - 1.5rem, 1140px)';
        adModalDialog.style.maxWidth = '1140px';
    }
}
adsGrid.addEventListener('click',function(e){const trigger=e.target.closest('.js-ad-modal-trigger');if(!trigger) return;syncAdModalSize(trigger);document.getElementById('adDetailsModalTitle').textContent=trigger.dataset.adTitle||'Ad Details';document.getElementById('adDetailsModalMeta').textContent=trigger.dataset.adMeta||'';document.getElementById('adDetailsModalDescription').textContent=trigger.dataset.adDescription||'';const img=trigger.dataset.adImage||'';const imgEl=document.getElementById('adDetailsModalImage');if(img){imgEl.src=img;imgEl.classList.remove('d-none');adEnlargeBtn.classList.remove('d-none');}else{imgEl.src='';imgEl.classList.add('d-none');adEnlargeBtn.classList.add('d-none');}const url=window.soilnwaterNormalizeShareUrl(trigger.dataset.adUrl||location.href);document.getElementById('adShareLink').value=url;document.getElementById('adShareQr').src=`https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(url)}`;document.getElementById('adShareWhatsapp').href=`https://wa.me/?text=${encodeURIComponent('Check this ad: '+url)}`;document.getElementById('adShareFacebook').href=window.soilnwaterFacebookShareUrl(url);document.getElementById('adShareInstagram').href=`https://www.instagram.com/?url=${encodeURIComponent(url)}`;
if(!isLoggedIn){if(adLoginMessageBox)adLoginMessageBox.classList.remove('d-none');if(adSharePanel)adSharePanel.classList.add('d-none');if(adReportActions)adReportActions.classList.add('d-none');document.getElementById('adDetailsModalTitle').textContent='You are not logged in';document.getElementById('adDetailsModalMeta').textContent='';document.getElementById('adDetailsModalDescription').textContent='';imgEl.src='';imgEl.classList.add('d-none');adEnlargeBtn.classList.add('d-none');document.getElementById('adShareLink').value='';document.getElementById('adShareQr').src='';document.getElementById('adShareWhatsapp').href='#';document.getElementById('adShareFacebook').href='#';document.getElementById('adShareInstagram').href='#';if (adReportPopupWrap) { adReportPopupWrap.classList.add('d-none'); }if (adDetailsModalInstance) { adDetailsModalInstance.show(); }return;}
if(adLoginMessageBox)adLoginMessageBox.classList.add('d-none');if(adSharePanel)adSharePanel.classList.remove('d-none');if(adReportActions)adReportActions.classList.remove('d-none');
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
