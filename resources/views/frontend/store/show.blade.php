@extends('frontend.layouts.app')

@section('title', $vendor->publicDisplayName().' – Supplier Store')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="vendor-store-page">
    @if(!empty($preview))
        <div class="vendor-preview-banner">Preview mode — only you can see this until your store is published.</div>
    @endif

    <header class="vendor-store-header">
        <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                @if($vendor->logo)
                    <img src="{{ asset($vendor->logo) }}" alt="{{ $vendor->publicDisplayName() }}" height="48">
                @else
                    <strong class="fs-4">{{ $vendor->publicDisplayName() }}</strong>
                @endif
            </div>
            <nav class="vendor-store-nav d-none d-md-flex">
                <a href="#home">Home</a>
                <a href="#products">Products</a>
                @foreach($vendor->pageSections as $sec)
                    <a href="#section-{{ $sec->id }}">{{ strip_tags($sec->title) }}</a>
                @endforeach
                <a href="#contact">Contact</a>
            </nav>
        </div>
    </header>

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
                <a href="#products" class="btn btn-warning btn-lg fw-bold">Explore Products</a>
                @if($vendor->whatsapp)
                    <a href="https://wa.me/91{{ preg_replace('/\D/', '', $vendor->whatsapp) }}" target="_blank" class="btn btn-success btn-lg ms-2">
                        <i class="fa-brands fa-whatsapp me-1"></i> Chat on WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section id="products" class="vendor-store-section">
        <div class="container">
            <h2>Top Selling Products</h2>
            <div class="row g-4">
                @foreach(array_slice($dummyProducts, 0, 4) as $product)
                    <div class="col-6 col-md-3">
                        <div class="vendor-product-card">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
                            <div class="card-body">
                                <h6 class="mb-1">{{ $product['name'] }}</h6>
                                <p class="price mb-0">{{ $product['price'] }}</p>
                                <p class="moq mb-0">{{ $product['moq'] }}</p>
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2">Send Inquiry</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @foreach($vendor->pageSections as $section)
        <section id="section-{{ $section->id }}" class="vendor-store-section {{ $loop->even ? 'alt' : '' }} vendor-custom-section">
            <div class="container">
                <div class="vendor-section-title-display">{!! $section->title !!}</div>
                <div class="row g-4 align-items-center">
                    @if($section->image_path)
                        <div class="col-lg-6">
                            <img src="{{ asset($section->image_path) }}" alt="{{ $section->title }}" class="section-img">
                        </div>
                    @endif
                    <div class="{{ $section->image_path ? 'col-lg-6' : 'col-12' }}">
                        <div class="content-body">{!! $section->content !!}</div>
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    <section id="products" class="vendor-store-section alt">
        <div class="container">
            <h2>Products</h2>
            <p class="text-center text-secondary mb-4">Explore our complete product range.</p>
            <div class="row g-4">
                @foreach($dummyProducts as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="vendor-product-card">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
                            <div class="card-body">
                                <h6 class="mb-1 small">{{ $product['name'] }}</h6>
                                <p class="price mb-0">{{ $product['price'] }}</p>
                                <p class="moq mb-0">{{ $product['moq'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
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
