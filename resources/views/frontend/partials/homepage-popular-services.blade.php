@php
    $cards = $cards ?? [];
    $viewAllUrl = $viewAllUrl ?? route('frontend.service_providers.index');
@endphp

@if(count($cards) > 0)
    <section class="homepage-popular-services" aria-label="Popular services">
        <div class="homepage-popular-services__shell">
            <header class="homepage-popular-services__head">
                <div class="homepage-popular-services__head-main">
                    <span class="homepage-popular-services__head-icon" aria-hidden="true">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </span>
                    <div class="homepage-popular-services__head-copy">
                        <h2 class="homepage-popular-services__title">
                            Popular <span class="homepage-popular-services__title-accent">Services</span>
                        </h2>
                        <p class="homepage-popular-services__subtitle">
                            Find trusted service providers near you
                        </p>
                    </div>
                </div>
                <a class="homepage-popular-services__see-all" href="{{ $viewAllUrl }}">
                    See all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </header>

            <div class="homepage-popular-services__grid">
                @foreach($cards as $card)
                    <a href="{{ $card['url'] }}" class="homepage-popular-services-card">
                        <div class="homepage-popular-services-card__media">
                            <img
                                src="{{ $card['image'] }}"
                                alt=""
                                loading="lazy"
                                decoding="async"
                                width="640"
                                height="360"
                            >
                            <span class="homepage-popular-services-card__category homepage-popular-services-card__category--{{ $card['category_tone'] }}">
                                <i class="fa-solid {{ $card['category_icon'] }}" aria-hidden="true"></i>
                                {{ $card['category_label'] }}
                            </span>
                        </div>
                        <div class="homepage-popular-services-card__body">
                            <div class="homepage-popular-services-card__title-row">
                                <h3 class="homepage-popular-services-card__title">{{ $card['title'] }}</h3>
                                <p class="homepage-popular-services-card__rating">
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    {{ number_format($card['rating_score'], 1) }}
                                </p>
                            </div>
                            <div class="homepage-popular-services-card__desc-row">
                                <p class="homepage-popular-services-card__desc">{{ $card['description'] }}</p>
                                <span class="homepage-popular-services-card__reviews">({{ number_format($card['rating_count']) }})</span>
                            </div>
                            <p class="homepage-popular-services-card__location">
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
