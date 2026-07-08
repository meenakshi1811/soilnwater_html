@php
  use App\Models\PremiumPrice;

  $types = [
    'vendor' => ['color' => 'green', 'icon' => 'fa-store', 'title' => 'Grow your vendor business with Premium'],
    'consultant' => ['color' => 'blue', 'icon' => 'fa-user-tie', 'title' => 'Get more clients with Premium'],
    'service' => ['color' => 'orange', 'icon' => 'fa-screwdriver-wrench', 'title' => 'Get more service leads with Premium'],
  ];
  $current = $types[$type] ?? $types['vendor'];
  $premiumAmountFormatted = PremiumPrice::formatAmount(PremiumPrice::amountFor($type));
@endphp

<div class="premium-listing-cta type-{{ $current['color'] }}">
  <div class="premium-listing-cta-copy">
    <h3><i class="fa-solid {{ $current['icon'] }} me-2"></i>{{ $current['title'] }}</h3>
    <p>Upgrade for higher visibility, premium badge, analytics, and ad-free listings from {{ $premiumAmountFormatted }}.</p>
  </div>
  <a href="{{ route('frontend.premium.show', $type) }}" class="premium-listing-cta-btn">
    <i class="fa-solid fa-crown"></i> Get Premium · {{ $premiumAmountFormatted }}
  </a>
</div>
