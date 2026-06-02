@extends('frontend.consultant.layout')

@section('title', $pageTitle.' – '.$consultant->publicDisplayName())

@section('consultant_content')
@php
    $consultantRecentAds = $consultantRecentAds ?? collect();
    $selectedCategoryNamesByConsultantAdId = $selectedCategoryNamesByConsultantAdId ?? [];
@endphp

<section class="vendor-store-page-hero">
    <div class="container">
        <nav class="vendor-store-breadcrumb mb-2" aria-label="breadcrumb">
            <a href="{{ route('consultant.show', $consultant->slug) }}">Home</a>
            <span class="mx-1">›</span>
            <a href="{{ route('consultant.public-services.index', $consultant->slug) }}">Services</a>
            @if(!empty($activeCategory))
                <span class="mx-1">›</span>
                @if(!empty($activeSubcategory))
                    <a href="{{ route('consultant.public-services.category', [$consultant->slug, $activeCategory->id]) }}">{{ $activeCategory->name }}</a>
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
        @include('frontend.consultant.partials.catalog-layout', [
            'consultant' => $consultant,
            'approvedServices' => $approvedServices,
            'consultantCategories' => $consultantCategories,
            'activeCategory' => $activeCategory ?? null,
            'activeSubcategory' => $activeSubcategory ?? null,
        ])
    </div>
</section>

@include('frontend.store.partials.recent-ads-slider', [
    'ads' => $consultantRecentAds,
    'selectedCategoryNamesByAdId' => $selectedCategoryNamesByConsultantAdId,
    'sectionTitle' => 'Sponsored Ads',
    'sliderLabel' => 'Sponsored consultant ads slider',
    'defaultCategoryLabel' => 'Consultants',
])
@endsection
