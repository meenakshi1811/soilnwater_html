@extends('frontend.layouts.app')

@section('meta_title', $config['meta_title'])
@section('meta_description', $config['meta_description'])

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
  $colorClass = 'type-' . $config['color'];
@endphp

<div class="premium-page">
  <section class="premium-hero">
    <div class="premium-hero-inner">
      <div class="premium-brand-row">
        <img src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater">
        <div class="premium-type-switch">
          @foreach($allTypes as $typeKey => $typeConfig)
            <a
              href="{{ route('frontend.premium.show', $typeKey) }}"
              class="{{ $type === $typeKey ? 'is-active ' . 'type-' . $typeConfig['color'] : '' }}"
            >
              <i class="fa-solid {{ $typeConfig['icon'] }}"></i>
              {{ $typeConfig['label'] }}
            </a>
          @endforeach
        </div>
      </div>

      <div class="premium-hero-grid">
        <div class="premium-hero-copy">
          <h1>
            <span class="accent-navy">PREMIUM PROFILES.</span>
            <span class="accent-green"> MORE VISIBILITY.</span>
            <span class="accent-navy"> MORE BUSINESS.</span>
          </h1>
          <p class="premium-hero-subtitle">For Vendors, Consultants &amp; Service Providers</p>

          <div class="premium-audience-pills">
            @foreach($allTypes as $typeKey => $typeConfig)
              <span class="premium-audience-pill type-{{ $typeConfig['color'] }} {{ $type === $typeKey ? 'is-active' : '' }}">
                <i class="fa-solid {{ $typeConfig['icon'] }}"></i>
                {{ $typeConfig['tagline'] }}
              </span>
            @endforeach
          </div>

          <ul class="premium-hero-points list-unstyled mb-0">
            <li>
              <i class="fa-solid fa-circle-check"></i>
              <span><strong>Choose the right listing that grows your business.</strong> Start with FREE. Grow with PREMIUM.</span>
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              <span>Build a professional {{ $config['profile_label'] }} and reach thousands of customers across India.</span>
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              <span>Upgrade to premium for more visibility, more enquiries, and a trusted premium badge.</span>
            </li>
          </ul>
        </div>

        <div class="premium-device-card">
          <div class="premium-device-mock">
            <div class="premium-device-banner"></div>
            <div class="premium-device-body">
              <div class="premium-device-logo">
                <i class="fa-solid {{ $config['icon'] }}"></i>
              </div>
              <h3>Your {{ $config['singular'] }} Profile</h3>
              <p>Grow Your Presence. Grow Your Business.</p>
              <div class="premium-device-tags">
                <span>Home</span>
                <span>Products / Services</span>
                <span>About</span>
                <span>Contact</span>
              </div>
              <div class="premium-device-actions">
                <span class="primary">View Profile</span>
                <span class="secondary">Contact Us</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="premium-section">
    <div class="premium-compare-head">
      <h2>Free vs Premium Membership</h2>
      <p>Compare what you get today and what premium unlocks for your {{ strtolower($config['singular']) }} business.</p>
    </div>

    <div class="premium-compare-grid">
      <article class="premium-tier-card free">
        <div class="tier-head">
          <h3>FREE</h3>
          <p>Free listing features to get started</p>
        </div>
        <ul>
          @foreach($config['free_features'] as $feature)
            <li>
              <i class="fa-solid fa-check"></i>
              <span>{{ $feature }}</span>
            </li>
          @endforeach
        </ul>
      </article>

      <div class="premium-compare-vs">VS</div>

      <article class="premium-tier-card premium">
        <div class="tier-head">
          <h3>PREMIUM</h3>
          <p>Membership benefits that drive growth</p>
        </div>
        <ul>
          @foreach($config['premium_features'] as $feature)
            <li>
              <i class="fa-solid fa-crown"></i>
              <span>{{ $feature }}</span>
            </li>
          @endforeach
        </ul>
      </article>
    </div>
  </section>

  <section class="premium-section pt-0">
    <div class="premium-upgrade-band">
      <h2>Upgrade to Premium &amp; Take Your Business to the Next Level!</h2>

      <div class="premium-benefit-icons">
        <div class="premium-benefit-icon">
          <i class="fa-solid fa-chart-line"></i>
          <span>More Visibility</span>
        </div>
        <div class="premium-benefit-icon">
          <i class="fa-solid fa-envelope-open-text"></i>
          <span>More Enquiries</span>
        </div>
        <div class="premium-benefit-icon">
          <i class="fa-solid fa-shield-heart"></i>
          <span>More Trust</span>
        </div>
        <div class="premium-benefit-icon">
          <i class="fa-solid fa-briefcase"></i>
          <span>More Business</span>
        </div>
      </div>

      <div class="premium-cta-row">
        <a href="{{ route('login') }}" class="premium-btn premium-btn-free">FREE TO START</a>
        <button type="button" class="premium-btn premium-btn-premium" data-bs-toggle="modal" data-bs-target="#premiumQrModal">
          <i class="fa-solid fa-crown"></i>
          CHOOSE PREMIUM. CHOOSE GROWTH.
        </button>
      </div>
    </div>

    <div class="premium-footer-band">
      <div>
        <h3>Join thousands of businesses already growing on SoilnWater</h3>
        <p>Create your professional profile today and start reaching more customers.</p>
      </div>
      <ul class="premium-footer-contact">
        <li><i class="fa-solid fa-globe"></i> www.soilnwater.in</li>
        <li><i class="fa-solid fa-envelope"></i> support@soilnwater.in</li>
        <li><i class="fa-solid fa-phone"></i> +91 7055533011</li>
      </ul>
      <div class="premium-footer-qr">
        <img src="{{ asset('assets/images/dummy-premium-qr.svg') }}" alt="Scan to visit SoilnWater">
        <small>SCAN TO VISIT SOILNWATER</small>
      </div>
    </div>
  </section>
</div>

<div class="modal fade premium-qr-modal" id="premiumQrModal" tabindex="-1" aria-labelledby="premiumQrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="premiumQrModalLabel">
          <i class="fa-solid fa-crown me-2 text-warning"></i>
          Get Premium – {{ $config['singular'] }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <img src="{{ asset('assets/images/dummy-premium-qr.svg') }}" alt="Dummy premium payment QR code">
        <p>Scan this QR code to complete your premium upgrade. This is a placeholder for now — real payment integration will be added soon.</p>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection
