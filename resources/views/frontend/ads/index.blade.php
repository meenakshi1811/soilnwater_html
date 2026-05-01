@extends('frontend.layouts.app')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h1 class="h3 mb-0">Ads Market</h1>
        <a href="{{ route('frontend.index') }}" class="view-all">Back to home ▶</a>
    </div>

    <div class="row g-2 mb-3 align-items-end" id="adsFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="col-12 col-md-4">
            <input id="adsMarketFilterSearch" class="form-control" placeholder="Search ads by title" value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-3">
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

<div class="modal fade offer-details-modal" id="adDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable offer-details-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Ad Details</h2>
                <button type="button" class="offer-modal-close-btn" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-0">
                <img id="adDetailsModalImage" src="" alt="Ad image" class="d-none offer-details-modal-image">
                <div class="offer-details-content">
                    <h3 class="h4 mb-2" id="adDetailsModalTitle"></h3>
                    <p class="text-muted mb-2" id="adDetailsModalMeta"></p>
                    <p class="text-muted mb-2" id="adDetailsModalSize"></p>
                    <p class="text-muted mb-3" id="adDetailsModalDescription"></p>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3 d-none" id="adDetailsEnlargeBtn">
                        <i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> Enlarge image
                    </button>

                    <div class="offer-share-panel mt-4">
                        <div class="offer-share-panel-head">
                            <h4 class="offer-share-title mb-1">Share this ad</h4>
                        </div>
                        <div class="offer-share-panel-body">
                            <div class="offer-share-qr-wrap">
                                <img id="adShareQr" src="" alt="Ad QR" class="offer-share-qr">
                            </div>
                            <div class="offer-share-links-wrap">
                                <input type="text" id="adShareLink" class="form-control form-control-sm offer-share-link-input" readonly>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <a id="adShareWhatsapp" href="#" target="_blank" class="btn btn-sm offer-share-btn share-whatsapp">WhatsApp</a>
                                    <a id="adShareFacebook" href="#" target="_blank" class="btn btn-sm offer-share-btn share-facebook">Facebook</a>
                                    <a id="adShareInstagram" href="#" target="_blank" class="btn btn-sm offer-share-btn share-instagram">Instagram</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adImageEnlargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h2 class="modal-title fs-6 text-white">Ad Image Preview</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <img id="adImageEnlargePreview" src="" alt="Enlarged ad image" class="img-fluid w-100 rounded">
            </div>
        </div>
    </div>
</div>
@endsection



@push('scripts')
<script>
// unchanged behavior; formatting only for reliability
// ...
document.addEventListener('DOMContentLoaded', function () {
const adModal = document.getElementById('adDetailsModal'); const adsGrid = document.getElementById('adsGrid'); if (!adsGrid) return;
const adEnlargeBtn = document.getElementById('adDetailsEnlargeBtn');
const adImageEnlargePreview = document.getElementById('adImageEnlargePreview');
const searchFilter = document.getElementById('adsMarketFilterSearch'); const categoryFilter = document.getElementById('adsMarketFilterCategory'); const subcategoryFilter = document.getElementById('adsMarketFilterSubcategory');
const clearFiltersBtn = document.getElementById('adsMarketClearFilters');
const loadingText = document.getElementById('adsLoadingText'); const summaryText = document.getElementById('adsSummaryText'); const scrollSentinel = document.getElementById('adsScrollSentinel');
let nextPageUrl = adsGrid.dataset.nextPageUrl || ''; let isLoading = false; let debounce;
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
async function refreshAds(){if(isLoading) return;isLoading=true;loadingText.classList.remove('d-none');const res=await fetch(buildUrl('{{ route('frontend.ads.index') }}'),{headers:{'X-Requested-With':'XMLHttpRequest'}});const payload=await res.json();adsGrid.innerHTML=payload.html||'';nextPageUrl=payload.next_page_url||'';adsGrid.dataset.nextPageUrl=nextPageUrl;summaryText.textContent=payload.total?`Showing 1 to ${payload.loaded_to} of ${payload.total} results`:'';loadingText.classList.add('d-none');isLoading=false;}
async function loadMore(){if(!nextPageUrl||isLoading) return;isLoading=true;loadingText.classList.remove('d-none');const r=await fetch(nextPageUrl,{headers:{'X-Requested-With':'XMLHttpRequest'}});const p=await r.json();adsGrid.insertAdjacentHTML('beforeend',p.html||'');nextPageUrl=p.next_page_url||'';adsGrid.dataset.nextPageUrl=nextPageUrl;summaryText.textContent=p.total?`Showing 1 to ${p.loaded_to} of ${p.total} results`:'';loadingText.classList.add('d-none');isLoading=false;}
if(scrollSentinel && 'IntersectionObserver' in window){new IntersectionObserver(e=>{if(e[0].isIntersecting) loadMore();},{rootMargin:'250px'}).observe(scrollSentinel);}
adsGrid.addEventListener('click',function(e){const trigger=e.target.closest('.js-ad-modal-trigger');if(!trigger) return;document.getElementById('adDetailsModalTitle').textContent=trigger.dataset.adTitle||'Ad Details';document.getElementById('adDetailsModalMeta').textContent=trigger.dataset.adMeta||'';document.getElementById('adDetailsModalDescription').textContent=trigger.dataset.adDescription||'';document.getElementById('adDetailsModalSize').textContent=trigger.dataset.adSize?`Selected size: ${trigger.dataset.adSize}`:'';const img=trigger.dataset.adImage||'';const imgEl=document.getElementById('adDetailsModalImage');if(img){imgEl.src=img;imgEl.classList.remove('d-none');adEnlargeBtn.classList.remove('d-none');}else{imgEl.src='';imgEl.classList.add('d-none');adEnlargeBtn.classList.add('d-none');}const url=trigger.dataset.adUrl||location.href;document.getElementById('adShareLink').value=url;document.getElementById('adShareQr').src=`https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(url)}`;document.getElementById('adShareWhatsapp').href=`https://wa.me/?text=${encodeURIComponent('Check this ad: '+url)}`;document.getElementById('adShareFacebook').href=`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;document.getElementById('adShareInstagram').href=url;new bootstrap.Modal(adModal).show();});
if (adEnlargeBtn) { adEnlargeBtn.addEventListener('click', function () { const imgEl = document.getElementById('adDetailsModalImage'); if (!imgEl || !imgEl.src) return; adImageEnlargePreview.src = imgEl.src; new bootstrap.Modal(document.getElementById('adImageEnlargeModal')).show(); }); }
});
</script>
@endpush
