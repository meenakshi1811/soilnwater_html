@php
    $cards = $cards ?? [];
    $viewAllUrl = $viewAllUrl ?? route('frontend.businesses.hub');
@endphp

@if(count($cards) > 0)
    <section class="homepage-popular-near" aria-label="Popular near you">
        <div class="homepage-popular-near__shell">
            <header class="homepage-popular-near__head">
                <div class="homepage-popular-near__head-main">
                    <span class="homepage-popular-near__pin" aria-hidden="true">
                        <i class="fa-solid fa-location-dot"></i>
                        <span class="homepage-popular-near__pin-wave homepage-popular-near__pin-wave--1"></span>
                        <span class="homepage-popular-near__pin-wave homepage-popular-near__pin-wave--2"></span>
                        <span class="homepage-popular-near__pin-wave homepage-popular-near__pin-wave--3"></span>
                    </span>
                    <div class="homepage-popular-near__head-copy">
                        <h2 class="homepage-popular-near__title">
                            Popular <span class="homepage-popular-near__title-accent">Near</span> You
                        </h2>
                        <p class="homepage-popular-near__subtitle">
                            Discover top businesses, services, offers and more in your area.
                        </p>
                    </div>
                </div>
                <a class="homepage-popular-near__see-all" href="{{ $viewAllUrl }}">
                    See all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </header>

            <div class="homepage-popular-near__grid">
                @foreach($cards as $card)
                    <a href="{{ $card['url'] }}" class="homepage-popular-near-card">
                        <div class="homepage-popular-near-card__media">
                            <img
                                src="{{ $card['image'] }}"
                                alt=""
                                loading="lazy"
                                decoding="async"
                                width="640"
                                height="360"
                            >
                            <span class="homepage-popular-near-card__category homepage-popular-near-card__category--{{ $card['category_tone'] }}">
                                <i class="fa-solid {{ $card['category_icon'] }}" aria-hidden="true"></i>
                                <span class="homepage-popular-near-card__category-divider" aria-hidden="true"></span>
                                {{ $card['category_label'] }}
                            </span>
                            @if(filled($card['offer_badge']))
                                <span class="homepage-popular-near-card__offer">
                                    {{ $card['offer_badge'] }}
                                    @if(filled($card['offer_badge_highlight']))
                                        <span class="homepage-popular-near-card__offer-accent">{{ $card['offer_badge_highlight'] }}</span>
                                    @endif
                                </span>
                            @endif
                        </div>
                        <div class="homepage-popular-near-card__body">
                            <div class="homepage-popular-near-card__title-row">
                                <h3 class="homepage-popular-near-card__title">{{ $card['title'] }}</h3>
                                <p class="homepage-popular-near-card__rating">
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    <span>{{ number_format($card['rating_score'], 1) }}</span>
                                    <small>({{ number_format($card['rating_count']) }})</small>
                                </p>
                            </div>
                            <p class="homepage-popular-near-card__desc">{{ $card['description'] }}</p>
                            <p class="homepage-popular-near-card__location">
                                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                {{ $card['location'] }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
