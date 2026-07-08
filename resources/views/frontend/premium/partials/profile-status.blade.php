@php
  use App\Models\PremiumPrice;

  $premiumType = $type ?? 'vendor';
  $profileLabel = match ($premiumType) {
    'consultant' => 'Consultant',
    'service' => 'Service Provider',
    default => 'Vendor',
  };
  $isOwner = $isOwner ?? (auth()->check() && (int) auth()->id() === (int) ($profile->user_id ?? 0));
  $premiumAmount = PremiumPrice::formatAmount(PremiumPrice::amountFor($premiumType));
@endphp

@if($profile->is_premium)
  <section class="premium-profile-status premium-profile-status--verified" aria-label="Premium member status">
    <div class="container">
      <div class="premium-profile-status__inner">
        <div class="premium-profile-status__badge-wrap">
          @include('frontend.premium.partials.badge', ['size' => 'lg'])
        </div>
        <div class="premium-profile-status__copy">
          <h2>Verified Premium {{ $profileLabel }}</h2>
          <p>This profile is a trusted premium member with priority visibility, verified badge, and an ad-free experience.</p>
        </div>
        <ul class="premium-profile-status__features">
          <li><i class="fa-solid fa-ranking-star"></i> Priority listing</li>
          <li><i class="fa-solid fa-shield-halved"></i> Verified badge</li>
          <li><i class="fa-solid fa-ban"></i> Ad-free profile</li>
        </ul>
      </div>
    </div>
  </section>
@elseif($isOwner)
  <section class="premium-profile-status premium-profile-status--upgrade" aria-label="Upgrade to premium">
    <div class="container">
      <div class="premium-profile-upgrade-band type-{{ $premiumType === 'service' ? 'orange' : ($premiumType === 'consultant' ? 'blue' : 'green') }}">
        <div class="premium-profile-upgrade-band__copy">
          <span class="premium-profile-upgrade-band__eyebrow"><i class="fa-solid fa-crown"></i> Upgrade available</span>
          <h2>Take your {{ strtolower($profileLabel) }} profile to Premium</h2>
          <p>Get higher visibility, a premium badge, analytics, and an ad-free profile from {{ $premiumAmount }}.</p>
        </div>
        <a href="{{ route('frontend.premium.show', $premiumType) }}" class="premium-profile-upgrade-band__btn">
          <i class="fa-solid fa-crown"></i>
          Get Premium · {{ $premiumAmount }}
        </a>
      </div>
    </div>
  </section>
@endif
