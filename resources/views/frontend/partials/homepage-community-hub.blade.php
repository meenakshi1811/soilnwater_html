@php
    $cards = $cards ?? [];
    $viewAllUrl = $viewAllUrl ?? route('community.index');
@endphp

@if(count($cards) > 0)
    <section class="homepage-community-hub" aria-label="SoilnWater community">
        <div class="homepage-community-hub__shell">
            <header class="homepage-community-hub__head">
                <div class="homepage-community-hub__head-main">
                    <span class="homepage-community-hub__head-icon" aria-hidden="true">
                        <i class="fa-solid fa-book-open"></i>
                    </span>
                    <div class="homepage-community-hub__head-copy">
                        <h2 class="homepage-community-hub__title">
                            SoilnWater <span class="homepage-community-hub__title-accent">Community</span>
                        </h2>
                        <p class="homepage-community-hub__subtitle">
                            Stories · People · Memories · Ideas
                        </p>
                    </div>
                </div>
                <a class="homepage-community-hub__see-all" href="{{ $viewAllUrl }}">
                    See all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </header>

            <div class="homepage-community-hub__grid">
                @foreach($cards as $card)
                    <a href="{{ $card['url'] }}" class="homepage-community-hub-card">
                        <div class="homepage-community-hub-card__media">
                            <img
                                src="{{ $card['image'] }}"
                                alt=""
                                loading="lazy"
                                decoding="async"
                                width="640"
                                height="360"
                            >
                            <span class="homepage-community-hub-card__category homepage-community-hub-card__category--{{ $card['category_tone'] }}">
                                <i class="fa-solid {{ $card['category_icon'] }}" aria-hidden="true"></i>
                                {{ $card['category_label'] }}
                            </span>
                        </div>
                        <div class="homepage-community-hub-card__body">
                            <div class="homepage-community-hub-card__title-row">
                                <h3 class="homepage-community-hub-card__title">{{ $card['title'] }}</h3>
                                <p class="homepage-community-hub-card__rating">
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    {{ number_format($card['rating_score'], 1) }}
                                </p>
                            </div>
                            <div class="homepage-community-hub-card__desc-row">
                                <p class="homepage-community-hub-card__desc">{{ $card['description'] }}</p>
                                <span class="homepage-community-hub-card__reviews">({{ number_format($card['rating_count']) }})</span>
                            </div>
                            <p class="homepage-community-hub-card__location">
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
