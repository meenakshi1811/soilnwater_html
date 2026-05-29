@extends('frontend.store.layout')

@section('title', $vendor->publicDisplayName().' – Supplier Store')

@section('store_content')
@php
    $featuredProducts = $featuredProducts ?? collect();
    $vendorRecentAds = $vendorRecentAds ?? collect();
    $selectedCategoryNamesByVendorAdId = $selectedCategoryNamesByVendorAdId ?? [];
@endphp

<section class="vendor-store-hero">
    @if($vendor->bannerSlides->count())
        <div id="storeHeroCarousel" class="carousel slide h-100" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                @foreach($vendor->bannerSlides as $i => $slide)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <img src="{{ asset($slide->image_path) }}" alt="{{ $vendor->publicDisplayName() }} banner {{ $i + 1 }}" class="vendor-store-hero__image">
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>


<section class="vendor-hero-text-section">
    <div class="container">
        <h1 style="@if(!empty($vendor->hero_main_style)){{ collect($vendor->hero_main_style)->map(fn($v,$k)=>$k.':'.$v)->implode(';') }}@endif">{{ $vendor->hero_main_heading ?: $vendor->publicDisplayName() }}</h1>
        @if($vendor->hero_sub_heading)
            <p class="lead mb-0 opacity-90" style="white-space: pre-line;@if(!empty($vendor->hero_sub_style)){{ collect($vendor->hero_sub_style)->map(fn($v,$k)=>$k.':'.$v)->implode(';') }}@endif">{!! html_entity_decode($vendor->hero_sub_heading) !!}</p>
        @endif
    </div>
</section>

@foreach($vendor->pageSections as $section)
    <section id="section-{{ $section->id }}" class="vendor-store-section {{ $loop->even ? 'alt' : '' }} vendor-custom-section">
        <div class="container">
            <div class="vendor-section-title-display">{!! $section->title !!}</div>
            <div class="row g-4 align-items-center">
                @if($section->image_path)
                    <div class="col-md-6">
                        <img src="{{ asset($section->image_path) }}" alt="{{ strip_tags($section->title) }}" class="section-img">
                    </div>
                @endif
                <div class="{{ $section->image_path ? 'col-md-6' : 'col-12' }}">
                    <div class="content-body">{!! $section->content !!}</div>
                </div>
            </div>
        </div>
    </section>
@endforeach

@include('frontend.store.partials.recent-ads-slider', ['ads' => $vendorRecentAds, 'selectedCategoryNamesByAdId' => $selectedCategoryNamesByVendorAdId])

@if($featuredProducts->isNotEmpty())
    <section class="vendor-store-section vendor-store-featured">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="vendor-store-eyebrow mb-1">Featured</p>
                    <h2 class="vendor-store-section-title mb-0">Popular products</h2>
                </div>
                <a href="{{ route('store.products.index', $vendor->slug) }}" class="btn btn-store-primary">View all products</a>
            </div>
            <div class="row g-3 g-md-4">
                @include('frontend.store.partials.product-cards', [
                    'products' => $featuredProducts,
                    'storeSlug' => $vendor->slug,
                    'cardStyle' => 'featured',
                ])
            </div>
        </div>
    </section>
@endif

<div class="modal fade" id="storeSectionImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">Image preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0 text-center">
                <img id="storeSectionImageModalImg" src="" alt="Store section image" class="img-fluid rounded" style="max-height:80vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('store_scripts')
<style>
    .content-body .js-remove-brochure-image,
    .content-body .js-remove-brochure-pdf {
        display: none !important;
    }
    .content-body [data-brochure-image-slot] {
        cursor: zoom-in;
    }
</style>

@if($vendor->bannerSlides->count() > 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('storeHeroCarousel');
    if (el) new bootstrap.Carousel(el, { interval: 5000 });
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('storeSectionImageModal');
    const modalImg = document.getElementById('storeSectionImageModalImg');
    if (!modalEl || !modalImg || typeof bootstrap === 'undefined') return;
    const previewModal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.content-body [data-brochure-image-slot], .content-body [data-brochure-image-list] img').forEach(function (img) {
        img.addEventListener('click', function () {
            if (!img.src) return;
            modalImg.src = img.src;
            modalImg.alt = img.alt || 'Store section image';
            previewModal.show();
        });
    });
});
</script>
@endpush
