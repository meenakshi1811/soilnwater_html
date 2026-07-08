@php
  use App\Models\PremiumPrice;

  $premiumOptions = [
    'vendor' => [
      'color' => 'green',
      'icon' => 'fa-store',
      'title' => 'Premium Vendor',
      'tagline' => 'Grow your store visibility and reach more buyers across India.',
      'benefits' => ['Priority listing', 'Premium badge', 'Ad-free store'],
    ],
    'consultant' => [
      'color' => 'blue',
      'icon' => 'fa-user-tie',
      'title' => 'Premium Consultant',
      'tagline' => 'Stand out to clients with a trusted, verified consultant profile.',
      'benefits' => ['Top placement', 'Verified badge', 'More enquiries'],
    ],
    'service' => [
      'color' => 'orange',
      'icon' => 'fa-screwdriver-wrench',
      'title' => 'Premium Service',
      'tagline' => 'Get more service leads with premium visibility and credibility.',
      'benefits' => ['Featured listing', 'Premium badge', 'Ad-free profile'],
    ],
  ];
@endphp

<section class="sec homepage-premium-section" aria-label="Premium membership options">
  <div class="sec-head">
    <div class="sec-title">
      <span class="icon"><i class="fa-solid fa-crown"></i></span>
      Go Premium
    </div>
    <p class="homepage-premium-section__subtitle mb-0">Upgrade your profile for more visibility, trust, and business growth.</p>
  </div>

  <div class="homepage-premium-grid">
    @foreach($premiumOptions as $typeKey => $option)
      @php($amount = PremiumPrice::formatAmount(PremiumPrice::amountFor($typeKey)))
      <article class="homepage-premium-card type-{{ $option['color'] }}">
        <div class="homepage-premium-card__icon">
          <i class="fa-solid {{ $option['icon'] }}"></i>
        </div>
        <div class="homepage-premium-card__body">
          <span class="homepage-premium-card__eyebrow">Membership</span>
          <h3>{{ $option['title'] }}</h3>
          <p>{{ $option['tagline'] }}</p>
          <ul class="homepage-premium-card__benefits">
            @foreach($option['benefits'] as $benefit)
              <li><i class="fa-solid fa-circle-check"></i>{{ $benefit }}</li>
            @endforeach
          </ul>
        </div>
        <div class="homepage-premium-card__footer">
          <div class="homepage-premium-card__price">
            <span class="homepage-premium-card__price-label">From</span>
            <strong>{{ $amount }}</strong>
          </div>
          <a href="{{ route('frontend.premium.show', $typeKey) }}" class="homepage-premium-card__btn">
            <i class="fa-solid fa-crown"></i>
            Get Premium
          </a>
        </div>
      </article>
    @endforeach
  </div>
</section>
