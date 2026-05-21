@extends('frontend.layouts.app')

@section('title', $vendor->publicDisplayName().' – Supplier Store')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
    $sidebarAds = $sidebarAds ?? collect();
    $sectionAdRails = $sectionAdRails ?? [];
    $vendorCategories = $vendorCategories ?? collect();
@endphp
<div class="vendor-store-page">
    @if(!empty($preview))
        <div class="vendor-preview-banner">Preview mode — only you can see this until your store is published.</div>
    @endif

    @include('frontend.store.partials.store-header', ['vendor' => $vendor])

    <section id="home" class="vendor-store-hero">
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
                <a href="{{ route('store.products.index', $vendor->slug) }}" class="btn btn-warning btn-lg fw-bold">Explore Products</a>
                @if($vendor->whatsapp)
                    <a href="https://wa.me/91{{ preg_replace('/\D/', '', $vendor->whatsapp) }}" target="_blank" class="btn btn-success btn-lg ms-2">
                        <i class="fa-brands fa-whatsapp me-1"></i> Chat on WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </section>

    @foreach($vendor->pageSections as $section)
        <section id="section-{{ $section->id }}" class="vendor-store-section {{ $loop->even ? 'alt' : '' }} vendor-custom-section">
            <div class="container-fluid px-3 px-lg-4">
                <div class="vendor-section-title-display">{!! $section->title !!}</div>
                <div class="row g-4 align-items-start">
                    <div class="{{ !empty($sectionAdRails[$loop->index] ?? null) ? 'col-lg-8' : 'col-12' }}">
                        <div class="row g-4 align-items-center">
                            @if($section->image_path)
                                <div class="col-md-6">
                                    <img src="{{ asset($section->image_path) }}" alt="{{ $section->title }}" class="section-img">
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

    <section id="products" class="vendor-store-section vendor-store-catalog-section">
        <div class="container-fluid px-3 px-lg-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h2 class="vendor-store-section-title mb-1">Products</h2>
                    <p class="text-muted mb-0">Browse by category or view our latest items.</p>
                </div>
                <a href="{{ route('store.products.index', $vendor->slug) }}" class="btn btn-store-primary">View all products</a>
            </div>

            @include('frontend.store.partials.catalog-layout', [
                'vendor' => $vendor,
                'products' => $products,
                'vendorCategories' => $vendorCategories,
                'activeCategory' => null,
                'activeSubcategory' => null,
                'sidebarAds' => $sidebarAds,
                'adsRailId' => 'storeHomeProductsAds',
            ])
        </div>
    </section>

    <footer id="contact" class="vendor-store-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="text-white">{{ $vendor->publicDisplayName() }}</h5>
                    <p class="small">{{ $vendor->description }}</p>
                    <p class="small mb-0">
                        @if($vendor->address){{ $vendor->address }}, @endif
                        {{ $vendor->city }}@if($vendor->state), {{ $vendor->state }}@endif @if($vendor->pincode){{ $vendor->pincode }}@endif
                    </p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white">Contact</h6>
                    @if($vendor->phone)<p class="small mb-1"><i class="fa-solid fa-phone me-1"></i> {{ $vendor->phone }}</p>@endif
                    @if($vendor->email)<p class="small mb-1"><i class="fa-solid fa-envelope me-1"></i> {{ $vendor->email }}</p>@endif
                </div>
                <div class="col-md-3">
                    <h6 class="text-white">Follow</h6>
                    @if($vendor->facebook_url)<a href="{{ $vendor->facebook_url }}" target="_blank" class="small d-block">Facebook</a>@endif
                    @if($vendor->instagram_url)<a href="{{ $vendor->instagram_url }}" target="_blank" class="small d-block">Instagram</a>@endif
                </div>
            </div>
            <hr class="border-secondary my-4">
            <p class="small text-center mb-0">&copy; {{ date('Y') }} {{ $vendor->publicDisplayName() }} · Powered by SoilNWater</p>
        </div>
    </footer>

    @if($vendor->whatsapp)
        <a href="https://wa.me/91{{ preg_replace('/\D/', '', $vendor->whatsapp) }}" class="vendor-whatsapp-float" target="_blank" aria-label="WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
    @endif
</div>
@endsection

@push('scripts')
@if($vendor->bannerSlides->count() > 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Carousel(document.getElementById('storeHeroCarousel'), { interval: 5000 });
});
</script>
@endif
@endpush
