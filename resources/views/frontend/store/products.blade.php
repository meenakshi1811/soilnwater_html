@extends('frontend.store.layout')

@section('title', $pageTitle.' – '.$vendor->publicDisplayName())

@section('store_content')
@php
    $sidebarAds = $sidebarAds ?? collect();
@endphp

<section class="vendor-store-page-hero">
    <div class="container">
        <nav class="vendor-store-breadcrumb mb-2" aria-label="breadcrumb">
            <a href="{{ route('store.show', $vendor->slug) }}">Home</a>
            <span class="mx-1">›</span>
            <a href="{{ route('store.products.index', $vendor->slug) }}">Products</a>
            @if(!empty($activeCategory))
                <span class="mx-1">›</span>
                @if(!empty($activeSubcategory))
                    <a href="{{ route('store.products.category', [$vendor->slug, $activeCategory->id]) }}">{{ $activeCategory->name }}</a>
                    <span class="mx-1">›</span>
                    <span aria-current="page">{{ $activeSubcategory->name }}</span>
                @else
                    <span aria-current="page">{{ $activeCategory->name }}</span>
                @endif
            @else
                <span class="mx-1">›</span>
                <span aria-current="page">All products</span>
            @endif
        </nav>
        <h1 class="vendor-store-page-hero__title">{{ $pageTitle }}</h1>
        <p class="vendor-store-page-hero__subtitle mb-0">{{ $pageSubtitle }}</p>
    </div>
</section>

<section class="vendor-store-catalog-section">
    <div class="container py-4 py-lg-5">
        @include('frontend.store.partials.catalog-layout', [
            'vendor' => $vendor,
            'products' => $products,
            'vendorCategories' => $vendorCategories,
            'activeCategory' => $activeCategory ?? null,
            'activeSubcategory' => $activeSubcategory ?? null,
            'sidebarAds' => $sidebarAds,
            'adsRailId' => 'storeProductsAds',
        ])
    </div>
</section>
@endsection
