@extends('frontend.layouts.app')

@section('title', $vendor->publicDisplayName().' – Products')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
    $sidebarAds = $sidebarAds ?? collect();
    $vendorCategories = $vendorCategories ?? collect();
@endphp
<div class="vendor-store-page">
    @if(!empty($preview))
        <div class="vendor-preview-banner">Preview mode — only you can see this until your store is published.</div>
    @endif

    @include('frontend.store.partials.store-header', ['vendor' => $vendor, 'activeNav' => 'products'])

    <section class="vendor-store-catalog-section">
        <div class="container-fluid px-3 px-lg-4 py-4">
            <div class="vendor-store-catalog-head mb-4">
                <h1 class="h3 mb-1">
                    @if(!empty($activeSubcategory))
                        {{ $activeSubcategory->name }}
                    @elseif(!empty($activeCategory))
                        {{ $activeCategory->name }}
                    @else
                        All Products
                    @endif
                </h1>
                <p class="text-muted mb-0">Browse products from {{ $vendor->publicDisplayName() }}</p>
            </div>

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
</div>
@endsection
