@extends('frontend.layouts.app')

@php
  use App\Support\VendorCategoryIcon;

  $formatStat = function (int $count): string {
      if ($count >= 1000) {
          return number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+';
      }

      return number_format($count).'+';
  };
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="vendors-page vendors-page--categories">
  @include('frontend.marketplace.partials.breadcrumb', ['module' => 'services', 'current' => 'Categories'])
  <div class="vendors-page__layout vendors-page__layout--single">
    <main class="vendors-main">
      <section class="vendors-hero vendors-hero--compact">
        <div class="vendors-hero__intro">
          <span class="vendors-hero__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-layer-group"></i></span>
            Service Categories
          </span>
          <h1>All Category Listing</h1>
          <p>Browse every service category on SoilnWater and jump straight to providers in the area you need.</p>
        </div>
        <div class="vendors-hero__stats">
          <div class="vendors-stat">
            <span class="vendors-stat__icon" aria-hidden="true">
              <i class="fa-solid fa-layer-group"></i>
            </span>
            <div class="vendors-stat__text">
              <strong>{{ number_format($categories->count()) }}</strong>
              <span>Categories</span>
            </div>
          </div>
          <div class="vendors-stat">
            <span class="vendors-stat__icon vendors-stat__icon--premium" aria-hidden="true">
              <i class="fa-solid fa-store"></i>
            </span>
            <div class="vendors-stat__text">
              <strong>{{ $formatStat($serviceProviderStats['trusted']) }}</strong>
              <span>Trusted Providers</span>
            </div>
          </div>
        </div>
      </section>

      <section class="vendors-section vendors-section--categories">
        <div class="vendors-section__head">
          <h2>All Categories</h2>
          <a href="{{ route('frontend.service_providers.listings') }}" class="vendors-section__link">
            View All Services <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
          </a>
        </div>

        @if ($categories->isNotEmpty())
          <div class="vendors-categories-strip vendors-categories-strip--all">
            @foreach ($categories as $category)
              <a
                href="{{ route('frontend.service_providers.listings', ['category_id' => $category->id]) }}"
                class="vendors-category-chip"
                title="{{ $category->name }}"
              >
                <span class="vendors-category-chip__icon vendors-category-chip__icon--{{ VendorCategoryIcon::toneIndex($category->name) }}">
                  <i class="fa-solid {{ VendorCategoryIcon::iconClass($category->name) }}" aria-hidden="true"></i>
                </span>
                <span class="vendors-category-chip__text">
                  <strong>{{ $category->name }}</strong>
                  <small>{{ number_format((int) $category->service_provider_count) }}+ providers</small>
                </span>
              </a>
            @endforeach
          </div>
        @else
          <div class="vendors-empty-state">
            <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-layer-group"></i></div>
            <h3>No vendor categories yet</h3>
            <p>Categories assigned to the Vendors module in admin will appear here automatically.</p>
          </div>
        @endif
      </section>
    </main>
  </div>
</div>
@endsection
