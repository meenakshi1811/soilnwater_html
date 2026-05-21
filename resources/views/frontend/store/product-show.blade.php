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

<div class="container py-4 py-lg-5">
    <div class="row g-4 align-items-start">
        <main class="col-xl-8">
            <section class="product-shell shadow-sm mb-4">
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
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
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
            <section class="card border-0 shadow-sm">
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
                                    <strong class="text-primary">₹{{ number_format((float) $similar->final_price, 2) }}</strong>
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
.product-shell{background:linear-gradient(135deg,#fff 0%,#f5f8ff 50%,#eefcf8 100%);border:1px solid #e6ebf5;border-radius:18px;padding:1rem}
.product-gallery-layout{display:grid;grid-template-columns:84px 1fr;gap:1rem;align-items:stretch}.gallery-thumbs{display:flex;flex-direction:column;gap:.8rem}.product-main-image-wrap{position:relative;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;background:#e5e7eb;padding:1rem}.product-main-image{aspect-ratio:4/3;object-fit:cover;border-radius:8px;min-height:460px}.thumb-btn{border:1px solid #dbe4f2;background:#fff;padding:.2rem;border-radius:12px;overflow:hidden;width:100%;opacity:.9}.thumb-btn img{height:72px;width:100%;object-fit:cover;border-radius:9px}.thumb-btn.active{border-color:#111827;box-shadow:0 0 0 .15rem rgba(17,24,39,.08);opacity:1}.gallery-nav-btn{position:absolute;left:14px;top:50%;transform:translateY(-50%);height:52px;width:52px;border-radius:50%;border:none;background:#fff;color:#111827;box-shadow:0 8px 18px rgba(17,24,39,.18)}
.product-brand{font-size:.78rem;letter-spacing:.05em;text-transform:uppercase;color:#16a34a;font-weight:700}
.price-panels{display:grid;grid-template-columns:1fr 1fr;gap:.7rem}.price-panel{border:1px solid #dce6f7;border-radius:12px;padding:.7rem .8rem;background:#fff}.price-panel span{font-size:.78rem;color:#6b7280;display:block}.price-panel strong{font-size:1.2rem;color:#111827}.price-panel.featured{background:linear-gradient(135deg,#fff0f0 0,#ffffff 100%)}
.quick-meta{display:grid;grid-template-columns:1fr 1fr;gap:.7rem}.quick-meta div{border:1px solid #e8edf8;border-radius:10px;padding:.6rem .75rem;background:#fff}.quick-meta small{display:block;color:#6b7280}.quick-meta p{margin:0;font-weight:600;color:#1f2937}
.product-description{padding:.9rem;border-radius:12px;background:#fff;border:1px solid #e5e7eb;color:#374151;line-height:1.65}
.similar-card{padding:.75rem;border:1px solid #e5e7eb;border-radius:12px;background:#fff;transition:all .2s ease}.similar-card:hover{transform:translateY(-2px);border-color:#0d6efd}
.similar-card__image{width:80px;height:80px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb}
@media (max-width:991.98px){.product-gallery-layout{grid-template-columns:1fr}.gallery-thumbs{flex-direction:row;overflow:auto}.thumb-btn{min-width:78px}.gallery-nav-btn{height:42px;width:42px}.product-main-image{min-height:300px}}
@media (max-width:767.98px){.price-panels,.quick-meta{grid-template-columns:1fr}}
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
