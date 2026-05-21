@extends('frontend.store.layout')

@section('title', $vendor->publicDisplayName().' – Supplier Store')

@section('store_content')
@php
    $sectionAdRails = $sectionAdRails ?? [];
    $featuredProducts = $featuredProducts ?? collect();
@endphp

<section class="vendor-store-hero">
    @if($vendor->bannerSlides->count())
        <div id="storeHeroCarousel" class="carousel slide h-100" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                @foreach($vendor->bannerSlides as $i => $slide)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}" style="background-image:url('{{ asset($slide->image_path) }}')"></div>
                @endforeach
            </div>
        </div>
    @endif
    <div class="hero-overlay">
        <div class="container">
            <h1 style="@if(!empty($vendor->hero_main_style)){{ collect($vendor->hero_main_style)->map(fn($v,$k)=>$k.':'.$v)->implode(';') }}@endif">{{ $vendor->hero_main_heading ?: $vendor->publicDisplayName() }}</h1>
            @if($vendor->hero_sub_heading)
                <p class="lead mb-4 opacity-90" style="@if(!empty($vendor->hero_sub_style)){{ collect($vendor->hero_sub_style)->map(fn($v,$k)=>$k.':'.$v)->implode(';') }}@endif">{{ $vendor->hero_sub_heading }}</p>
            @endif
            <a href="{{ route('store.products.index', $vendor->slug) }}" class="btn btn-warning btn-lg fw-bold px-4">Browse Products</a>
            @if($vendor->whatsapp)
                <a href="https://wa.me/91{{ preg_replace('/\D/', '', $vendor->whatsapp) }}" target="_blank" rel="noopener" class="btn btn-success btn-lg ms-2 px-4">
                    <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                </a>
            @endif
        </div>
    </div>
</section>

@foreach($vendor->pageSections as $section)
    <section id="section-{{ $section->id }}" class="vendor-store-section {{ $loop->even ? 'alt' : '' }} vendor-custom-section">
        <div class="container">
            <div class="vendor-section-title-display">{!! $section->title !!}</div>
            <div class="row g-4 align-items-start">
                <div class="{{ !empty($sectionAdRails[$loop->index] ?? null) ? 'col-lg-8' : 'col-12' }}">
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
                @if(!empty($sectionAdRails[$loop->index] ?? null))
                    <div class="col-lg-4">
                        @include('frontend.store.partials.ads-rail', [
                            'ads' => $sectionAdRails[$loop->index],
                            'railId' => 'storeSectionAds'.$loop->index,
                        ])
                    </div>
                @endif
            </div>
        </div>
    </section>
@endforeach

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
                ])
            </div>
        </div>
    </section>
@endif
@endsection

@push('store_scripts')
@if($vendor->bannerSlides->count() > 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('storeHeroCarousel');
    if (el) new bootstrap.Carousel(el, { interval: 5000 });
});
</script>
@endif
@endpush
