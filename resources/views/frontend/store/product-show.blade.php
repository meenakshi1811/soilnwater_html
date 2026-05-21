@extends('frontend.store.layout')
@section('title', $product->name.' - '.$vendor->publicDisplayName())
@section('store_content')
@php
$primaryImage = is_array($product->images) ? ($product->images[0] ?? null) : null;
$galleryImages = collect(is_array($product->images) ? $product->images : [])->filter()->values();
$adFrameStyle = function ($ad) {
    $w = (int) ($ad->adSize->width ?? 0);
    $h = (int) ($ad->adSize->height ?? 0);

    if ($w > 0 && $h > 0) {
        return 'width:100%;max-width:'.$w.'px;aspect-ratio:'.$w.' / '.$h.';';
    }

    return 'width:100%;';
};
@endphp
<section class="vendor-store-page-hero vendor-store-page-hero--compact">
    <div class="container">
        <nav class="vendor-store-breadcrumb mb-2">
            <a href="{{ route('store.show', $vendor->slug) }}">Home</a>
            <span class="mx-1">›</span>
            <a href="{{ route('store.products.index', $vendor->slug) }}">Products</a>
            <span class="mx-1">›</span>
            <span aria-current="page">{{ $product->name }}</span>
        </nav>
        <h1 class="vendor-store-page-hero__title h3 mb-0">{{ $product->name }}</h1>
    </div>
</section>

<div class="container py-4 py-lg-5 product-page-wrap">
    <div class="row g-4 align-items-start">
        <main class="col-xl-8">
            <section class="product-shell shadow-lg mb-4 product-card-pro">
                <div class="row g-3 g-lg-4">
                    <div class="col-lg-7">
                        <div class="product-gallery-layout">
                            <div class="gallery-thumbs">
                                @foreach($galleryImages->take(5) as $image)
                                <button type="button" class="thumb-btn {{ $loop->first ? 'active' : '' }}" data-image="{{ asset($image) }}">
                                    <img src="{{ asset($image) }}" class="img-fluid" alt="{{ $product->name }} image {{ $loop->iteration }}">
                                </button>
                                @endforeach
                            </div>
                            <div class="product-main-image-wrap">
                                <button type="button" class="gallery-nav-btn" id="galleryPrevBtn" aria-label="Previous image"><i class="fa-solid fa-chevron-left"></i></button>
                                <img id="mainProductImage" src="{{ $primaryImage ? asset($primaryImage) : asset('assets/images/ad-sample.png') }}" class="img-fluid w-100 product-main-image" alt="{{ $product->name }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3 product-header">
                            <div>
                                <p class="product-brand mb-1">{{ $product->brand ?: $vendor->publicDisplayName() }}</p>
                                <h2 class="h3 mb-0">{{ $product->name }}</h2>
                            </div>
                            <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#enquiryModal">Send Inquiry</button>
                        </div>
                        <div class="price-panels mb-3">
                            <div class="price-panel featured">
                                <span>Offer price</span>
                                <strong>₹{{ number_format((float) $product->final_price, 2) }}</strong>
                            </div>
                            <div class="price-panel">
                                <span>Stock available</span>
                                <strong>{{ number_format((int) $product->stock_quantity) }}</strong>
                            </div>
                        </div>
                        <div class="quick-meta mb-3">
                            <div><small>Category</small><p>{{ $product->category?->name ?? 'General' }}</p></div>
                            <div><small>SKU</small><p>{{ $product->sku ?: 'NA' }}</p></div>
                        </div>
                        <div class="product-description">
                            <h3 class="h6">Description</h3>
                            <p class="mb-0">{!! nl2br(e($product->description ?: 'No description available.')) !!}</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="mt-4">@if(($similarProducts ?? collect())->count())
            <section class="card border-0 shadow-sm similar-wrap">
                <div class="card-header bg-white border-0 pb-0">
                    <h2 class="h5 mb-0">Similar products</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($similarProducts as $similar)
                        @php($similarImage = is_array($similar->images) ? ($similar->images[0] ?? null) : null)
                        <div class="col-md-6">
                            <a href="{{ route('store.products.show', [$vendor->slug, $similar->id]) }}" class="similar-card d-flex gap-3 text-decoration-none">
                                <img src="{{ $similarImage ? asset($similarImage) : asset('assets/images/ad-sample.png') }}" alt="{{ $similar->name }}" class="similar-card__image">
                                <div>
                                    <h3 class="h6 text-dark mb-1">{{ $similar->name }}</h3>
                                    <p class="text-muted mb-2 small">{{ $similar->brand ?: 'Trusted quality' }}</p>
                                    <strong class="similar-price">₹{{ number_format((float) $similar->final_price, 2) }}</strong>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif</div>
        </main>

        <aside class="col-xl-4">
            <div class="sticky-xl-top" style="top: 88px;">
                @foreach($sideGroups as $gi => $group)
                    @php($id = 'rightAdCarousel'.$gi)
                    @if($group->count() > 1)
                    <div id="{{ $id }}" class="carousel slide mb-3" data-bs-ride="carousel"><div class="carousel-inner rounded-3 overflow-hidden border shadow-sm bg-white">@foreach($group as $i => $ad)<div class="carousel-item {{ $i===0?'active':'' }}"><a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link"><img src="{{ asset($ad->final_image) }}" class="d-block w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a></div>@endforeach</div></div>
                    @else
                    @php($ad = $group->first())<a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link d-block rounded-3 overflow-hidden border shadow-sm mb-3"><img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a>
                    @endif
                @endforeach
            </div>
        </aside>
    </div>
</div>
@includeWhen(true, 'frontend.store.partials.enquiry-modal')
@endsection

@push('styles')
<style>
.product-page-wrap{--brand:#1d4ed8;--brand-2:#2563eb;--ink:#0f172a;--muted:#64748b;--line:#e2e8f0;--bg:#f8fafc}
.product-card-pro{background:#fff;border:1px solid var(--line);border-radius:20px;padding:1.25rem;box-shadow:0 14px 34px rgba(15,23,42,.08)}
.product-gallery-layout{display:grid;grid-template-columns:96px 1fr;gap:1rem;align-items:stretch}
.gallery-thumbs{display:flex;flex-direction:column;gap:.75rem}
.thumb-btn{border:1px solid #dbe4f2;background:#fff;padding:.2rem;border-radius:12px;overflow:hidden;width:100%;opacity:.95;transition:.18s ease}
.thumb-btn img{height:74px;width:100%;object-fit:cover;border-radius:9px}
.thumb-btn.active,.thumb-btn:hover{border-color:var(--brand);box-shadow:0 0 0 .18rem rgba(37,99,235,.12);opacity:1}
.product-main-image-wrap{position:relative;border-radius:16px;overflow:hidden;border:1px solid #d7deea;background:#edf2f7;padding:.9rem}
.product-main-image{aspect-ratio:4/3;object-fit:cover;border-radius:12px;min-height:460px;background:#fff}
.gallery-nav-btn{position:absolute;left:14px;top:50%;transform:translateY(-50%);height:48px;width:48px;border-radius:50%;border:none;background:#fff;color:#0f172a;box-shadow:0 8px 20px rgba(15,23,42,.2)}
.product-brand{font-size:.78rem;letter-spacing:.08em;text-transform:uppercase;color:#16a34a;font-weight:700}
.product-header .btn-primary{border-radius:10px;padding:.6rem 1rem;background:linear-gradient(135deg,var(--brand),var(--brand-2));border:none;font-weight:600}
.price-panels,.quick-meta{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.price-panel,.quick-meta div,.product-description{border:1px solid var(--line);border-radius:12px;background:#fff;padding:.8rem .9rem}
.price-panel.featured{background:#fef2f2;border-color:#fecaca}
.price-panel span,.quick-meta small{font-size:.76rem;color:var(--muted);display:block}
.price-panel strong{font-size:1.35rem;color:var(--ink)}
.quick-meta p{margin:0;color:#1e293b;font-weight:700}
.product-description h3{color:#0f172a}.product-description{line-height:1.65;color:#334155;background:#fcfdff}
.similar-wrap{border-radius:16px;overflow:hidden;border:1px solid #dbeafe;background:#fff}
.similar-wrap .card-header{background:#f8fbff}
.similar-card{padding:.75rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff;transition:.2s ease}
.similar-card:hover{transform:translateY(-2px);border-color:#93c5fd;box-shadow:0 10px 20px rgba(30,64,175,.1)}
.similar-card__image{width:84px;height:84px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb}
.similar-price{color:var(--brand);font-weight:700}
@media (max-width:991.98px){.product-gallery-layout{grid-template-columns:1fr}.gallery-thumbs{flex-direction:row;overflow:auto}.thumb-btn{min-width:80px}.gallery-nav-btn{height:42px;width:42px}.product-main-image{min-height:300px}}
@media (max-width:767.98px){.price-panels,.quick-meta{grid-template-columns:1fr}.product-card-pro{padding:1rem}}
</style>
@endpush
@push('store_scripts')
@auth
<script>document.getElementById('enquiryForm')?.addEventListener('submit', async function(e){e.preventDefault();const fd = new FormData(this);const res = await fetch("{{ route('store.products.enquiry', [$vendor->slug, $product->id]) }}", {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:fd});const data = await res.json();alert(data.message || 'Done');if(res.ok){ bootstrap.Modal.getInstance(document.getElementById('enquiryModal')).hide(); }});</script>
@endauth
<script>
const thumbs = Array.from(document.querySelectorAll('.thumb-btn'));
let activeIndex = thumbs.findIndex((b) => b.classList.contains('active'));
if (activeIndex < 0) activeIndex = 0;
const setActiveImage = (index) => {
  if (!thumbs[index]) return;
  document.getElementById('mainProductImage').src = thumbs[index].dataset.image;
  thumbs.forEach((btn) => btn.classList.remove('active'));
  thumbs[index].classList.add('active');
  activeIndex = index;
};
thumbs.forEach((button, index) => button.addEventListener('click', () => setActiveImage(index)));
document.getElementById('galleryPrevBtn')?.addEventListener('click', () => {
  const next = activeIndex <= 0 ? thumbs.length - 1 : activeIndex - 1;
  setActiveImage(next);
});
</script>
@endpush
