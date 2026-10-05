@php
    $variant = $variant ?? 'offers';
    $cards = $cards ?? [];
    $isOffers = $variant === 'offers';
    $viewAllUrl = $viewAllUrl ?? ($isOffers ? route('frontend.offers.index') : route('frontend.ads.index'));
    $titleHighlight = $isOffers ? 'Offers' : 'Ads';
    $titleRest = $isOffers ? '& Discounts' : ' & Listings';
    $subtitle = $isOffers
        ? 'Save more at your favourite local businesses'
        : 'Discover sponsored listings and marketplace ads near you';
@endphp

@if(count($cards) > 0)
    <section
        class="homepage-marketplace-promo homepage-marketplace-promo--{{ $variant }}"
        aria-label="{{ $isOffers ? 'Latest offers and discounts' : 'Latest ads and listings' }}"
    >
        <div class="homepage-marketplace-promo__shell">
            <header class="homepage-marketplace-promo__head">
                <div class="homepage-marketplace-promo__head-main">
                    <span class="homepage-marketplace-promo__head-icon" aria-hidden="true">
                        @if($isOffers)
                            <i class="fa-solid fa-tags"></i>
                            <span class="homepage-marketplace-promo__spark homepage-marketplace-promo__spark--a"></span>
                            <span class="homepage-marketplace-promo__spark homepage-marketplace-promo__spark--b"></span>
                            <span class="homepage-marketplace-promo__spark homepage-marketplace-promo__spark--c"></span>
                        @else
                            <i class="fa-solid fa-rectangle-ad"></i>
                            <span class="homepage-marketplace-promo__spark homepage-marketplace-promo__spark--a"></span>
                            <span class="homepage-marketplace-promo__spark homepage-marketplace-promo__spark--b"></span>
                        @endif
                    </span>
                    <div class="homepage-marketplace-promo__head-copy">
                        <h2 class="homepage-marketplace-promo__title">
                            Latest <span class="homepage-marketplace-promo__title-accent">{{ $titleHighlight }}</span>{!! $titleRest !!}
                        </h2>
                        <p class="homepage-marketplace-promo__subtitle">{{ $subtitle }}</p>
                    </div>
                </div>
                <a class="homepage-marketplace-promo__see-all" href="{{ $viewAllUrl }}">
                    @include('frontend.partials.homepage-promo-see-all-label') <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </header>

            <div class="homepage-marketplace-promo__grid">
                @foreach($cards as $card)
                    <a href="{{ $card['url'] }}" class="homepage-marketplace-promo-card">
                        <div class="homepage-marketplace-promo-card__media">
                            <img
                                src="{{ $card['image'] }}"
                                alt=""
                                loading="lazy"
                                decoding="async"
                                width="640"
                                height="360"
                            >
                            <span class="homepage-marketplace-promo-card__discount">
                                <span class="homepage-marketplace-promo-card__discount-spark" aria-hidden="true"></span>
                                {{ $card['discount_badge'] }}
                            </span>
                            <span class="homepage-marketplace-promo-card__category homepage-marketplace-promo-card__category--{{ $card['category_tone'] }}">
                                <i class="fa-solid {{ $card['category_icon'] }}" aria-hidden="true"></i>
                                {{ $card['category_label'] }}
                            </span>
                        </div>
                        <div class="homepage-marketplace-promo-card__body">
                            <h3 class="homepage-marketplace-promo-card__title">{{ $card['title'] }}</h3>
                            <p class="homepage-marketplace-promo-card__desc">{{ $card['description'] }}</p>
                            <div class="homepage-marketplace-promo-card__meta">
                                <p class="homepage-marketplace-promo-card__location">
                                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                    {{ $card['location'] }}
                                </p>
                                <span class="homepage-marketplace-promo-card__valid">Valid till {{ $card['valid_till'] }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
