@extends('frontend.layouts.app')

@section('content')
<div class="businesses-hub-page">
  <header class="businesses-hub-page__head">
    <a class="businesses-hub-page__back" href="{{ route('frontend.index') }}" aria-label="Back to home">
      <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    </a>
    <div>
      <h1 class="businesses-hub-page__title">Businesses</h1>
      <p class="businesses-hub-page__lead">Choose how you want to explore vendors, consultants, and service providers.</p>
    </div>
  </header>

  <div class="businesses-hub-page__grid">
    <a href="{{ route('frontend.vendors.index') }}" class="businesses-hub-card businesses-hub-card--vendors">
      <span class="businesses-hub-card__icon" aria-hidden="true"><i class="fa-solid fa-store"></i></span>
      <h2 class="businesses-hub-card__title">Vendors</h2>
      <p class="businesses-hub-card__desc">Browse local businesses, shops, and vendor listings.</p>
      <span class="businesses-hub-card__go" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
    </a>
    <a href="{{ route('frontend.consultants.index') }}" class="businesses-hub-card businesses-hub-card--consultants">
      <span class="businesses-hub-card__icon" aria-hidden="true"><i class="fa-solid fa-circle-user"></i></span>
      <h2 class="businesses-hub-card__title">Consultants</h2>
      <p class="businesses-hub-card__desc">Connect with experts and professional consultants.</p>
      <span class="businesses-hub-card__go" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
    </a>
    <a href="{{ route('frontend.service_providers.index') }}" class="businesses-hub-card businesses-hub-card--services">
      <span class="businesses-hub-card__icon businesses-hub-card__icon--wrench" aria-hidden="true"><i class="fa-solid fa-wrench"></i></span>
      <h2 class="businesses-hub-card__title">Services</h2>
      <p class="businesses-hub-card__desc">Find reliable service providers near you.</p>
      <span class="businesses-hub-card__go" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
    </a>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/businesses-hub.css') }}?v={{ now()->timestamp }}">
@endpush
