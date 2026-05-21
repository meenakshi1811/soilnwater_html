@extends('frontend.store.layout')
@section('title', $product->name.' - '.$vendor->publicDisplayName())
@section('store_content')
@php
$primaryImage = is_array($product->images) ? ($product->images[0] ?? null) : null;
$galleryImages = collect(is_array($product->images) ? $product->images : [])->filter()->values();
$colorOptions = collect(explode(',', (string) ($product->colors ?? '')))->map(fn ($v) => trim($v))->filter()->values();
$sizeOptions = collect(explode(',', (string) ($product->sizes ?? '')))->map(fn ($v) => trim($v))->filter()->values();
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
    <div class="row g-4 align-items-start">
        <main class="col-xl-9">
            <section class="product-card-pro mb-4">
                <div class="row g-3 g-lg-4">
                    <div class="col-lg-7">
                        <div class="product-gallery-layout">
                            <div class="gallery-thumbs">
                                @foreach($galleryImages->take(5) as $image)
                                <button type="button" class="thumb-btn {{ $loop->first ? 'active' : '' }}" data-image="{{ asset($image) }}"><img src="{{ asset($image) }}" alt="{{ $product->name }} image {{ $loop->iteration }}"></button>
                                @endforeach
                            </div>
                            <div class="product-main-image-wrap">
                                <button type="button" class="gallery-nav-btn" id="galleryPrevBtn" aria-label="Previous image"><i class="fa-solid fa-chevron-left"></i></button>
                                <img id="mainProductImage" src="{{ $primaryImage ? asset($primaryImage) : asset('assets/images/ad-sample.png') }}" class="product-main-image" alt="{{ $product->name }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
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
                        <img src="{{ $similarImage ? asset($similarImage) : asset('assets/images/ad-sample.png') }}" alt="{{ $similar->name }}" class="similar-card__image"><div>
                            <h3 class="h6 text-dark mb-1">{{ $similar->name }}</h3><p class="text-muted mb-2 small">{{ $similar->brand ?: 'Trusted quality' }}</p>
                            <strong class="similar-price">₹{{ number_format((float) $similar->final_price, 2) }}</strong></div></a></div>
                    @endforeach
                </div></div>
            </section>
            @endif
        </main>

        <aside class="col-xl-3"><div class="sticky-xl-top ads-rail" style="top: 96px;">
            @php($railAds = $sideGroups->flatten(1)->filter()->values())
            @if($railAds->count() < 6)
                @php($railAds = $railAds->merge(($ads ?? collect())->filter())->unique('id')->values())
            @endif

            @php($sliderAds = $railAds->take(4)->values())
            @if($sliderAds->count() > 1)
                <div id="rightAdCarousel0" class="carousel slide mb-3" data-bs-ride="carousel">
                    <div class="carousel-inner rounded-3 overflow-hidden border shadow-sm bg-white">
                        @foreach($sliderAds as $i => $ad)
                        <div class="carousel-item {{ $i===0?'active':'' }}"><a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link"><img src="{{ asset($ad->final_image) }}" class="d-block w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a></div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#rightAdCarousel0" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#rightAdCarousel0" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                </div>
            @elseif($sliderAds->count() === 1)
                @php($ad = $sliderAds->first())<a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link d-block rounded-3 overflow-hidden border shadow-sm mb-3"><img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a>
            @endif

            @php($stackAds = $railAds->slice(1)->take(10))
            @foreach($stackAds as $ad)
                <a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link d-block rounded-3 overflow-hidden border shadow-sm mb-3 ad-stack-item"><img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a>
            @endforeach
        </div></aside>
    </div>
</div>
@includeWhen(true, 'frontend.store.partials.enquiry-modal')
@endsection

@push('styles')
<style>
.product-page-wrap{--brand:#1d4ed8;--ink:#0f172a;--muted:#64748b;--line:#e2e8f0}
.product-card-pro{background:#fff;border:1px solid var(--line);border-radius:20px;padding:1.2rem;box-shadow:0 14px 34px rgba(15,23,42,.08)}
.product-gallery-layout{display:grid;grid-template-columns:92px 1fr;gap:1rem}.gallery-thumbs{display:flex;flex-direction:column;gap:.7rem}
.thumb-btn{border:1px solid #dbe4f2;background:#fff;padding:.2rem;border-radius:12px}.thumb-btn img{height:74px;width:100%;object-fit:cover;border-radius:9px}.thumb-btn.active{border-color:var(--brand);box-shadow:0 0 0 .18rem rgba(37,99,235,.12)}
.product-main-image-wrap{position:relative;border-radius:16px;overflow:hidden;border:1px solid #d7deea;background:#edf2f7;padding:.9rem}.product-main-image{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:12px;min-height:340px;max-height:360px}
.gallery-nav-btn{position:absolute;left:14px;top:50%;transform:translateY(-50%);height:48px;width:48px;border-radius:50%;border:none;background:#fff;box-shadow:0 8px 20px rgba(15,23,42,.2)}
.vendor-label{font-size:1.35rem;letter-spacing:.03em;font-weight:800;color:#16a34a;line-height:1.2}.product-title{font-size:1.05rem;line-height:1.35;color:var(--ink);font-weight:700}
.meta-line{font-size:.88rem;color:#475569}.sold-chip{color:#b91c1c}.label-muted{color:#6b7280;font-weight:500}.hero-price{font-size:1.15rem;line-height:1.2;color:#020617;font-weight:800}
.option-label{font-size:.95rem;color:#374151}.color-chip{height:42px;width:42px;border-radius:50%;border:2px solid #d1d5db;overflow:hidden;padding:0;background:#fff}.color-chip img{width:100%;height:100%;object-fit:cover}.color-chip.active{border-color:#111827}
.size-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.45rem}.size-chip{border:1px solid #cbd5e1;background:#fff;padding:.45rem .3rem;border-radius:8px;font-weight:700;font-size:.82rem}.size-chip.active{background:#020617;color:#fff;border-color:#020617}
.similar-wrap{border-radius:16px;overflow:hidden;border:1px solid #dbeafe}.similar-card{padding:.75rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.similar-card__image{width:84px;height:84px;object-fit:cover;border-radius:10px}.similar-price{color:var(--brand)}
.ads-rail{max-width:320px;margin-left:auto;position:relative;z-index:2}.ads-rail .ad-link,.ads-rail .carousel-inner{border-radius:10px!important}.ads-rail img{max-height:240px;object-fit:cover}.ads-rail .carousel{position:relative;z-index:2}.ad-stack-item img{max-height:165px}
@media (max-width:991.98px){.product-gallery-layout{grid-template-columns:1fr}.gallery-thumbs{flex-direction:row;overflow:auto}.thumb-btn{min-width:80px}.product-main-image{min-height:250px;max-height:300px}.vendor-label{font-size:1.05rem}.product-title{font-size:.98rem}.hero-price{font-size:1.05rem}.ads-rail{max-width:100%;z-index:1}.ad-stack-item img{max-height:240px}}
</style>
@endpush
@push('store_scripts')
@auth
<script>document.getElementById('enquiryForm')?.addEventListener('submit', async function(e){e.preventDefault();const fd = new FormData(this);const res = await fetch("{{ route('store.products.enquiry', [$vendor->slug, $product->id]) }}", {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:fd});const data = await res.json();alert(data.message || 'Done');if(res.ok){ bootstrap.Modal.getInstance(document.getElementById('enquiryModal')).hide(); }});</script>
@endauth
<script>
const thumbs = Array.from(document.querySelectorAll('.thumb-btn, .color-chip'));
const onlyThumbs = Array.from(document.querySelectorAll('.thumb-btn'));
let activeIndex = onlyThumbs.findIndex((b) => b.classList.contains('active')); if (activeIndex < 0) activeIndex = 0;
const setImage = (src) => { const img=document.getElementById('mainProductImage'); if(img) img.src=src; };
onlyThumbs.forEach((button, index) => button.addEventListener('click', () => { setImage(button.dataset.image); onlyThumbs.forEach((b)=>b.classList.remove('active')); button.classList.add('active'); activeIndex=index; }));
document.querySelectorAll('.color-chip').forEach((button)=>button.addEventListener('click',()=>{ setImage(button.dataset.image); document.querySelectorAll('.color-chip').forEach((b)=>b.classList.remove('active')); button.classList.add('active'); }));
document.getElementById('galleryPrevBtn')?.addEventListener('click', () => { const next = activeIndex <= 0 ? onlyThumbs.length - 1 : activeIndex - 1; onlyThumbs[next]?.click(); });
</script>
@endpush
