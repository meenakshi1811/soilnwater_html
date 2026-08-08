@extends('frontend.store.layout')
@section('title', $product->name.' - '.$vendor->publicDisplayName())
@section('store_content')
@php
$primaryImage = is_array($product->images) ? ($product->images[0] ?? null) : null;
$galleryImages = collect(is_array($product->images) ? $product->images : [])->filter()->values();
$colorOptions = collect(explode(',', (string) ($product->colors ?? '')))->map(fn ($v) => trim($v))->filter()->values();
$sizeOptions = collect(explode(',', (string) ($product->sizes ?? '')))->map(fn ($v) => trim($v))->filter()->values();
$excludedDetailFields = ['id','vendor_id','user_id','slug','images','colors','sizes','created_at','updated_at','deleted_at','latitude','longitude','lat','lng','status','approved_at','approved_by','is_online_sale'];
$formatJsonList = function ($raw, $featureKey, $valueKey) {
    $decoded = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : []);
    if (!is_array($decoded)) {
        return collect();
    }
    return collect($decoded)->map(function ($row) use ($featureKey, $valueKey) {
        if (!is_array($row)) {
            return null;
        }
        $feature = trim((string) ($row[$featureKey] ?? ''));
        $value = trim((string) ($row[$valueKey] ?? ''));
        return ($feature !== '' && $value !== '') ? ['feature' => $feature, 'value' => $value] : null;
    })->filter()->values();
};

$specRows = $formatJsonList($product->specifications ?? ($product->specs ?? null), 'feature', 'value');
$bulkTierRows = $formatJsonList($product->bulk_tiers ?? null, 'buy_min', 'price');

$productDetails = collect($product->getAttributes() ?? [])->reject(function ($value, $key) use ($excludedDetailFields) {
    return in_array($key, $excludedDetailFields)
        || in_array($key, ['specifications','specs','bulk_tiers'])
        || is_null($value)
        || $value === '';
})->mapWithKeys(function ($value, $key) {
    $label = ucwords(str_replace('_', ' ', $key));
    if (is_bool($value)) {
        $value = $value ? 'Yes' : 'No';
    }
    return [$label => is_scalar($value) ? (string) $value : json_encode($value)];
});
$adFrameStyle = function ($ad) {
    $w = (int) ($ad->adSize->width ?? 0);
    $h = (int) ($ad->adSize->height ?? 0);
    return ($w > 0 && $h > 0) ? 'width:100%;max-width:'.$w.'px;aspect-ratio:'.$w.' / '.$h.';' : 'width:100%;';
};
@endphp
<section class="vendor-store-page-hero vendor-store-page-hero--compact">
    <div class="container">
        <nav class="vendor-store-breadcrumb mb-2">
            <a href="{{ route('store.show', $vendor->slug) }}">Home</a><span class="mx-1">›</span>
            <a href="{{ route('store.products.index', $vendor->slug) }}">Products</a><span class="mx-1">›</span>
            <span aria-current="page">{{ $product->name }}</span>
        </nav>
        <h1 class="vendor-store-page-hero__title h3 mb-0">{{ $product->name }}</h1>
    </div>
</section>

<div class="container py-2 py-lg-3 product-page-wrap">
    <div class="row gx-4 gy-0 align-items-start">
        <main class="col-xl-10">
            <section class="product-card-pro mb-4">
                <div class="row g-3 g-lg-4">
                    <div class="col-lg-8">
                        <div class="product-gallery-layout">
                            <div class="gallery-thumbs">
                                @foreach($galleryImages->take(5) as $image)
                                <button type="button" class="thumb-btn {{ $loop->first ? 'active' : '' }}" data-image="{{ asset($image) }}"><img src="{{ asset($image) }}" alt="{{ $product->name }} image {{ $loop->iteration }}"></button>
                                @endforeach
                            </div>
                            <div class="product-main-image-wrap" id="productImageZoomWrap">
                                <img id="mainProductImage" src="{{ $primaryImage ? asset($primaryImage) : asset('assets/images/logo_soilnwater.webp') }}" class="product-main-image{{ $primaryImage ? '' : ' product-main-image--placeholder' }}" alt="{{ $product->name }}">
                                <button type="button" class="view-gallery-btn" data-bs-toggle="modal" data-bs-target="#productGalleryModal">
                                    <i class="fa-solid fa-expand me-1"></i> View gallery
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <p class="vendor-label mb-1">{{ strtoupper($vendor->publicDisplayName()) }}</p>
                        <h2 class="product-title mb-2">{{ $product->name }}</h2>
                        <p class="mb-1"><span class="label-muted">Vendor:</span> {{ $vendor->publicDisplayName() }}</p>
                        <p class="mb-1"><span class="label-muted">Availability:</span> {{ (int) $product->stock_quantity > 0 ? "In Stock" : "Out of Stock" }}</p>
                        <p class="mb-3"><span class="label-muted">Stock:</span> {{ number_format((int) $product->stock_quantity) }} units</p>

                        <div class="hero-price mb-3">₹{{ number_format((float) $product->final_price, 2) }}</div>

                        <div class="mb-3">
                            <p class="option-label mb-2">Color: <strong>{{ $colorOptions->first() ?? 'N/A' }}</strong></p>
                            @if($galleryImages->count())
                            <div class="d-flex gap-2">
                                @foreach($galleryImages->take(max(1, min(4, $colorOptions->count() ?: 2))) as $image)
                                <button type="button" class="color-chip {{ $loop->first ? 'active' : '' }}" data-image="{{ asset($image) }}" title="{{ $colorOptions[$loop->index] ?? ('Option '.($loop->iteration)) }}"><img src="{{ asset($image) }}" alt="color option {{ $loop->iteration }}"></button>
                                @endforeach
                            </div>
                            @endif
                            @if($colorOptions->count())<p class="small text-muted mt-2 mb-0">Available: {{ $colorOptions->implode(', ') }}</p>@endif
                        </div>

                        <div class="mb-3">
                            <p class="option-label mb-2">Size: <strong>{{ $sizeOptions->first() ?? 'N/A' }}</strong></p>
                            @if($sizeOptions->count())
                            <div class="size-grid">
                                @foreach($sizeOptions as $size)
                                <button type="button" class="size-chip {{ $loop->first ? 'active' : '' }}">{{ $size }}</button>
                                @endforeach
                            </div>
                            @else
                            <p class="small text-muted mb-0">No size options added.</p>
                            @endif
                        </div>

                        <button class="btn btn-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#enquiryModal">Send Inquiry</button>


                        <div class="product-details-card mt-3">
                            <h3 class="h6 mb-3">Product details</h3>
                            <div class="details-grid">
                                @foreach($productDetails as $label => $value)
                                <div class="detail-item">
                                    <span class="detail-label">{{ $label }}</span>
                                    <span class="detail-value">{{ $value }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>


                            @if($specRows->isNotEmpty())
                            <div class="spec-list-wrap mt-3">
                                <h4 class="detail-section-title mb-2">Specs</h4>
                                <div class="spec-list">
                                    @foreach($specRows as $item)
                                    <div class="spec-item"><span class="spec-label">{{ $item['feature'] }}</span><span class="spec-value">{{ $item['value'] }}</span></div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($bulkTierRows->isNotEmpty())
                            <div class="spec-list-wrap mt-3">
                                <h4 class="detail-section-title mb-2">Bulk Tiers</h4>
                                <div class="spec-list">
                                    @foreach($bulkTierRows as $tier)
                                    <div class="spec-item"><span class="spec-label">Buy {{ $tier['feature'] }}+</span><span class="spec-value">₹{{ number_format((float) $tier['value'], 2) }}</span></div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                    </div>
                </div>
            </section>

            @if(($similarProducts ?? collect())->count())
            <section class="card border-0 shadow-sm similar-wrap">
                <div class="card-header bg-white border-0 pb-0"><h2 class="h5 mb-0">Similar products</h2></div>
                <div class="card-body"><div class="row g-3">
                    @foreach($similarProducts as $similar)
                    @php($similarImage = is_array($similar->images) ? ($similar->images[0] ?? null) : null)
                    <div class="col-md-6"><a href="{{ route('store.products.show', [$vendor->slug, $similar->id]) }}" class="similar-card d-flex gap-3 text-decoration-none">
                        <img src="{{ $similarImage ? asset($similarImage) : asset('assets/images/logo_soilnwater.webp') }}" alt="{{ $similar->name }}" class="similar-card__image{{ $similarImage ? '' : ' similar-card__image--placeholder' }}"><div>
                            <h3 class="h6 text-dark mb-1">{{ $similar->name }}</h3><p class="text-muted mb-2 small">{{ $similar->brand ?: 'Trusted quality' }}</p>
                            <strong class="similar-price">₹{{ number_format((float) $similar->final_price, 2) }}</strong></div></a></div>
                    @endforeach
                </div></div>
            </section>
            @endif
        </main>

        @if(! $vendor->is_premium)
            @php($railAds = $sideGroups->flatten(1)->filter()->values())
            @if($railAds->count() < 6)
                @php($railAds = $railAds->merge(($ads ?? collect())->filter())->unique('id')->values())
            @endif

            @if($railAds->isNotEmpty())
                <aside class="col-xl-2"><div class="sticky-xl-top ads-rail" style="top: 12px;">
                    @php($sliderAds = $railAds->take(4)->values())
                    @if($sliderAds->count() > 1)
                        <div id="rightAdCarousel0" class="carousel slide mb-3" data-bs-ride="carousel">
                            <div class="carousel-inner rounded-3 overflow-hidden border shadow-sm bg-white">
                                @foreach($sliderAds as $i => $ad)
                                <div class="carousel-item {{ $i===0?'active':'' }}">
                                    <div class="ad-link js-ad-modal-trigger"
                                         role="button"
                                         tabindex="0"
                                         @include('frontend.ads.partials.ad-modal-attrs', ['ad' => $ad])>
                                        <img src="{{ asset($ad->final_image) }}" class="d-block w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#rightAdCarousel0" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                            <button class="carousel-control-next" type="button" data-bs-target="#rightAdCarousel0" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                        </div>
                    @elseif($sliderAds->count() === 1)
                        @php($ad = $sliderAds->first())
                        <div class="ad-link js-ad-modal-trigger d-block rounded-3 overflow-hidden border shadow-sm mb-3"
                             role="button"
                             tabindex="0"
                             @include('frontend.ads.partials.ad-modal-attrs', ['ad' => $ad])>
                            <img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}">
                        </div>
                    @endif

                    @php($stackAds = $railAds->slice(1)->take(10))
                    @foreach($stackAds as $ad)
                        <div class="ad-link js-ad-modal-trigger d-block rounded-3 overflow-hidden border shadow-sm mb-3 ad-stack-item"
                             role="button"
                             tabindex="0"
                             @include('frontend.ads.partials.ad-modal-attrs', ['ad' => $ad])>
                            <img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}">
                        </div>
                    @endforeach
                </div></aside>
            @endif
        @endif
    </div>
</div>
@includeWhen(true, 'frontend.store.partials.enquiry-modal')

<div class="modal fade" id="productGalleryModal" tabindex="-1" aria-labelledby="productGalleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productGalleryModalLabel">{{ $product->name }} - Images</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modalMainProductImage" src="{{ $primaryImage ? asset($primaryImage) : asset('assets/images/logo_soilnwater.webp') }}" class="modal-main-image mb-3{{ $primaryImage ? '' : ' product-main-image--placeholder' }}" alt="{{ $product->name }}">
                <div class="modal-gallery-thumbs">
                    @foreach($galleryImages as $image)
                    <button type="button" class="modal-thumb-btn {{ $loop->first ? 'active' : '' }}" data-image="{{ asset($image) }}">
                        <img src="{{ asset($image) }}" alt="{{ $product->name }} image {{ $loop->iteration }}">
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.product-page-wrap{--brand:#1d4ed8;--ink:#0f172a;--muted:#64748b;--line:#e2e8f0}
.product-card-pro{background:#fff;border:1px solid var(--line);border-radius:20px;padding:1.2rem;box-shadow:0 14px 34px rgba(15,23,42,.08);font-family:'Inter',sans-serif}
@media (min-width:1200px){.product-card-pro .row{--bs-gutter-x:2rem}}
.product-gallery-layout{display:grid;grid-template-columns:92px 1fr;gap:1rem}.gallery-thumbs{display:flex;flex-direction:column;gap:.7rem}
.thumb-btn{border:1px solid #dbe4f2;background:#fff;padding:.2rem;border-radius:12px}.thumb-btn img{height:74px;width:100%;object-fit:cover;border-radius:9px}.thumb-btn.active{border-color:var(--brand);box-shadow:0 0 0 .18rem rgba(37,99,235,.12)}
.product-main-image-wrap{position:relative;border-radius:16px;overflow:hidden;border:1px solid #d7deea;background:#edf2f7;padding:.9rem}.product-main-image{width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:12px;min-height:520px;max-height:680px;display:block}
.product-main-image--placeholder{object-fit:contain;padding:2.5rem;background:#0b1220}
.view-gallery-btn{position:absolute;right:16px;bottom:16px;border:none;background:rgba(15,23,42,.78);color:#fff;border-radius:999px;padding:.45rem .8rem;font-size:.82rem;font-weight:700;font-family:'Manrope',sans-serif}
.modal-main-image{width:100%;max-height:70vh;object-fit:contain;border-radius:10px;background:#f8fafc}
.modal-gallery-thumbs{display:flex;gap:.6rem;overflow:auto;padding-bottom:.3rem}
.modal-thumb-btn{border:1px solid #dbe4f2;background:#fff;padding:.2rem;border-radius:10px;min-width:88px}
.modal-thumb-btn img{width:84px;height:64px;object-fit:cover;border-radius:8px}
.modal-thumb-btn.active{border-color:var(--brand);box-shadow:0 0 0 .15rem rgba(37,99,235,.14)}
.vendor-label{font-family:'Manrope',sans-serif;font-size:1.35rem;letter-spacing:.03em;font-weight:800;color:#16a34a;line-height:1.2}.product-title{font-family:'Manrope',sans-serif;font-size:1.05rem;line-height:1.35;color:var(--ink);font-weight:700}
.meta-line{font-size:.88rem;color:#475569;font-family:'Inter',sans-serif}.sold-chip{color:#b91c1c}.label-muted{color:#6b7280;font-weight:500}.hero-price{font-family:'Manrope',sans-serif;font-size:1.15rem;line-height:1.2;color:#020617;font-weight:800}
.option-label{font-family:'Manrope',sans-serif;font-size:.95rem;color:#374151;font-weight:700}.color-chip{height:42px;width:42px;border-radius:50%;border:2px solid #d1d5db;overflow:hidden;padding:0;background:#fff}.color-chip img{width:100%;height:100%;object-fit:cover}.color-chip.active{border-color:#111827}
.size-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.45rem}.size-chip{border:1px solid #cbd5e1;background:#fff;padding:.45rem .3rem;border-radius:8px;font-family:'Manrope',sans-serif;font-weight:700;font-size:.82rem}.size-chip.active{background:#020617;color:#fff;border-color:#020617}
.similar-wrap{border-radius:16px;overflow:hidden;border:1px solid #dbeafe}.similar-card{padding:.75rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.similar-card__image{width:84px;height:84px;object-fit:cover;border-radius:10px}.similar-card__image--placeholder{object-fit:contain;padding:.35rem;background:#0b1220}.similar-price{color:var(--brand);font-family:'Manrope',sans-serif;font-weight:800}
.ads-rail{max-width:280px;margin-left:auto;position:relative;z-index:2}.ads-rail .ad-link,.ads-rail .carousel-inner{border-radius:10px!important}.ads-rail img{max-height:240px;object-fit:cover}.ads-rail .carousel{position:relative;z-index:2}.ad-stack-item img{max-height:150px}
 .product-details-card{margin-top:1rem;padding:1rem;border:1px solid #dbeafe;background:linear-gradient(180deg,#f8fbff 0%,#fff 100%);border-radius:12px}
.details-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem .9rem}
.detail-item{padding:.55rem .65rem;background:#fff;border:1px solid #e2e8f0;border-radius:10px;display:flex;flex-direction:column;gap:.2rem;font-family:'Manrope',sans-serif}
.detail-item .label-muted,.detail-item strong{font-family:'Manrope',sans-serif}
.detail-label{font-family:'Manrope',sans-serif;font-size:.75rem;font-weight:700;letter-spacing:.02em;color:#64748b;text-transform:uppercase}
.detail-value{font-family:'Manrope',sans-serif;font-size:.88rem;color:#0f172a;word-break:break-word}
.detail-section-title{font-family:'Manrope',sans-serif;font-size:.95rem;font-weight:700;color:#0f172a}
.spec-list-wrap{padding:.85rem;border:1px solid #dbeafe;background:#fff;border-radius:12px}
.spec-list{display:flex;flex-direction:column;gap:.45rem}
.spec-item{display:flex;justify-content:space-between;gap:.75rem;padding:.45rem .55rem;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc}
.spec-label{font-size:.8rem;color:#475569;font-weight:700}.spec-value{font-size:.86rem;color:#020617;font-weight:600;text-align:right}
@media (max-width:991.98px){.product-gallery-layout{grid-template-columns:1fr}.gallery-thumbs{flex-direction:row;overflow:auto}.thumb-btn{min-width:80px}.product-main-image{min-height:280px;max-height:360px}.vendor-label{font-size:1.05rem}.product-title{font-size:.98rem}.hero-price{font-size:1.05rem}.ads-rail{max-width:100%;z-index:1}.ad-stack-item img{max-height:240px}.details-grid{grid-template-columns:1fr}}
</style>
@endpush
@push('store_scripts')
@auth
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
document.getElementById('enquiryForm')?.addEventListener('submit', async function(e){
    e.preventDefault();
    const submitBtn = this.querySelector('#enquirySubmitBtn');
    const loader = submitBtn?.querySelector('.js-enquiry-btn-loader');
    const sending = submitBtn?.querySelector('.js-enquiry-btn-sending');
    const btnText = submitBtn?.querySelector('.js-enquiry-btn-text');

    const showFeedback = (type, message) => {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            try {
                window.toastr[type](message);
                return;
            } catch (err) {
                console.warn('Toastr failed, falling back to alert.', err);
            }
        }
        alert(message);
    };

    if (submitBtn) submitBtn.disabled = true;
    btnText?.classList.add('d-none');
    loader?.classList.remove('d-none');
    sending?.classList.remove('d-none');

    const fd = new FormData(this);
    try {
        const res = await fetch("{{ route('store.products.enquiry', [$vendor->slug, $product->id]) }}", {
            method:'POST',
            headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
            body:fd
        });
        const data = await res.json();
        const toastType = res.ok ? 'success' : 'error';
        const toastMessage = data.message || (res.ok ? 'Enquiry sent successfully.' : 'Unable to send enquiry.');

        bootstrap.Modal.getInstance(document.getElementById('enquiryModal'))?.hide();

        showFeedback(toastType, toastMessage);

        if (res.ok) {
            this.reset();
        }
    } catch (error) {
        bootstrap.Modal.getInstance(document.getElementById('enquiryModal'))?.hide();
        showFeedback('error', 'Unable to send enquiry. Please try again.');
    } finally {
        if (submitBtn) submitBtn.disabled = false;
        btnText?.classList.remove('d-none');
        loader?.classList.add('d-none');
        sending?.classList.add('d-none');
    }
});
</script>
@endauth
<script>
const thumbs = Array.from(document.querySelectorAll('.thumb-btn, .color-chip'));
const onlyThumbs = Array.from(document.querySelectorAll('.thumb-btn'));
let activeIndex = onlyThumbs.findIndex((b) => b.classList.contains('active')); if (activeIndex < 0) activeIndex = 0;
const setImage = (src) => { const img=document.getElementById('mainProductImage'); if(img){ img.src=src; const wrap=document.getElementById('productImageZoomWrap'); if(wrap) wrap.style.backgroundImage=`url(${src})`; } };
onlyThumbs.forEach((button, index) => button.addEventListener('click', () => { setImage(button.dataset.image); onlyThumbs.forEach((b)=>b.classList.remove('active')); button.classList.add('active'); activeIndex=index; }));
document.querySelectorAll('.color-chip').forEach((button)=>button.addEventListener('click',()=>{ setImage(button.dataset.image); document.querySelectorAll('.color-chip').forEach((b)=>b.classList.remove('active')); button.classList.add('active'); }));
const modalMainImage = document.getElementById('modalMainProductImage');
const modalThumbs = Array.from(document.querySelectorAll('.modal-thumb-btn'));
modalThumbs.forEach((button) => {
    button.addEventListener('click', () => {
        if (modalMainImage) modalMainImage.src = button.dataset.image;
        modalThumbs.forEach((b) => b.classList.remove('active'));
        button.classList.add('active');
    });
});

</script>
@endpush
