@php
    extract(\App\Support\ConsultantListingCard::data($consultant, $hasLocation ?? false));
    $primaryBranch = $consultant->branches->first();
    $professionalExperience = $consultant->branches->first(fn ($branch) => filled($branch->professional_experience))?->professional_experience;
    $servicesOffered = $consultant->branches->first(fn ($branch) => filled($branch->services_offered))?->services_offered;
    $metaLine = $locationLabel.' • '.number_format((int) ($consultant->services_count ?? 0)).' Services';
    if ($hasLocation && $consultant->nearest_distance_km !== null) {
        $metaLine .= ' • '.number_format($consultant->nearest_distance_km, 1).' km';
    }
    $description = filled($professionalExperience)
        ? \Illuminate\Support\Str::limit($professionalExperience, 100)
        : (filled($servicesOffered)
            ? \Illuminate\Support\Str::limit($servicesOffered, 100)
            : $metaLine);
@endphp

<article class="card h-100 shadow-sm border-0 offer-coupon-card vendor-offer-card{{ $consultant->is_premium ? ' is-premium-card' : '' }}">
    <a href="{{ $profileUrl }}" class="offer-coupon-image-wrap vendor-offer-card__media" aria-label="View {{ $consultant->publicDisplayName() }} profile">
        <img
            src="{{ $coverImage }}"
            alt="{{ $consultant->publicDisplayName() }}"
            class="offer-coupon-image"
            loading="lazy"
            decoding="async"
            onerror="this.onerror=null;this.src='{{ asset('assets/images/vendor-card-placeholder.svg') }}';"
        >
    </a>
    <div class="card-body d-flex flex-column gap-2">
        @if ($consultant->is_premium)
            @include('frontend.premium.partials.badge', ['size' => 'xs'])
        @else
            <span class="badge text-bg-primary w-fit">{{ number_format((int) ($consultant->services_count ?? 0)) }} Services</span>
        @endif
        <h4 class="h6 mb-1 offer-coupon-title">{{ $consultant->publicDisplayName() }}</h4>
        <p class="small text-muted mb-2 offer-coupon-description">{{ $description }}</p>
        <a href="{{ $profileUrl }}" class="vendor-offer-card__btn text-center text-decoration-none mt-auto">View Profile</a>
    </div>
</article>
