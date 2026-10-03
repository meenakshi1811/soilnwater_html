@extends('frontend.layouts.app')

@section('content')
<div class="businesses-hub-page offers-ads-hub-page">
  <header class="businesses-hub-page__head">
    <a class="businesses-hub-page__back" href="{{ route('frontend.index') }}" aria-label="Back to home">
      <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    </a>
    <div>
      <h1 class="businesses-hub-page__title">Offers &amp; Ads</h1>
      <p class="businesses-hub-page__lead">Pick a module to browse discounts or classified listings.</p>
    </div>
  </header>

  <div class="businesses-hub-page__grid offers-ads-hub-page__grid">
    <a href="{{ route('frontend.offers.index') }}" class="businesses-hub-card businesses-hub-card--offers">
      <span class="businesses-hub-card__icon" aria-hidden="true"><i class="fa-solid fa-gift"></i></span>
      <h2 class="businesses-hub-card__title">Offers</h2>
      <p class="businesses-hub-card__desc">Explore deals, discounts, and promotions from local businesses.</p>
      <span class="businesses-hub-card__go" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
    </a>
    <a href="{{ route('frontend.ads.index') }}" class="businesses-hub-card businesses-hub-card--ads">
      <span class="businesses-hub-card__icon" aria-hidden="true"><i class="fa-solid fa-rectangle-ad"></i></span>
      <h2 class="businesses-hub-card__title">Ads</h2>
      <p class="businesses-hub-card__desc">Browse classified ads, property listings, and marketplace posts.</p>
      <span class="businesses-hub-card__go" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
    </a>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/businesses-hub.css') }}?v={{ now()->timestamp }}">
<style>
  .offers-ads-hub-page__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    max-width: 720px;
    margin: 0 auto;
  }

  .businesses-hub-card--offers {
    background: #fff1f2;
  }

  .businesses-hub-card--offers .businesses-hub-card__icon {
    color: #ef4444;
  }

  .businesses-hub-card--ads {
    background: #fefce8;
  }

  .businesses-hub-card--ads .businesses-hub-card__icon {
    color: #ca8a04;
  }

  @media (max-width: 575.98px) {
    .offers-ads-hub-page__grid {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush
