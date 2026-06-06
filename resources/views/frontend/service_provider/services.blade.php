@extends('frontend.service_provider.layout')

@section('title', $pageTitle.' – '.$service_provider->publicDisplayName())

@section('service_provider_content')
@php
    $service_providerRecentAds = $service_providerRecentAds ?? collect();
    $selectedCategoryNamesByServiceProviderAdId = $selectedCategoryNamesByServiceProviderAdId ?? [];
@endphp

<section class="vendor-store-page-hero">
    <div class="container">
        <nav class="vendor-store-breadcrumb mb-2" aria-label="breadcrumb">
            <a href="{{ route('service_provider.show', $service_provider->slug) }}">Home</a>
            <span class="mx-1">›</span>
            <a href="{{ route('service_provider.public-services.index', $service_provider->slug) }}">Services</a>
            @if(!empty($activeCategory))
                <span class="mx-1">›</span>
                @if(!empty($activeSubcategory))
                    <a href="{{ route('service_provider.public-services.category', [$service_provider->slug, $activeCategory->id]) }}">{{ $activeCategory->name }}</a>
                    <span class="mx-1">›</span>
                    <span aria-current="page">{{ $activeSubcategory->name }}</span>
                @else
                    <span aria-current="page">{{ $activeCategory->name }}</span>
                @endif
            @else
                <span class="mx-1">›</span>
                <span aria-current="page">All services</span>
            @endif
        </nav>
        <h1 class="vendor-store-page-hero__title">{{ $pageTitle }}</h1>
        <p class="vendor-store-page-hero__subtitle mb-0">{{ $pageSubtitle }}</p>
    </div>
</section>

<section class="vendor-store-catalog-section">
    <div class="container py-4 py-lg-5">
        @include('frontend.service_provider.partials.catalog-layout', [
            'service_provider' => $service_provider,
            'approvedServices' => $approvedServices,
            'service_providerCategories' => $service_providerCategories,
            'activeCategory' => $activeCategory ?? null,
            'activeSubcategory' => $activeSubcategory ?? null,
        ])
    </div>
</section>

@include('frontend.store.partials.recent-ads-slider', [
    'ads' => $service_providerRecentAds,
    'selectedCategoryNamesByAdId' => $selectedCategoryNamesByServiceProviderAdId,
    'sectionTitle' => 'Sponsored Ads',
    'sliderLabel' => 'Sponsored service ads slider',
    'defaultCategoryLabel' => 'Services',
])
@endsection
