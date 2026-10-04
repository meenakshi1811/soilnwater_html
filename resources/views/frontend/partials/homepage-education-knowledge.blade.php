@php
    $cards = $cards ?? [];
    $viewAllUrl = $viewAllUrl ?? route('schools.index');
@endphp

@if(count($cards) > 0)
    <section class="homepage-education-knowledge" aria-label="Education and knowledge">
        <div class="homepage-education-knowledge__shell">
            <header class="homepage-education-knowledge__head">
                <div class="homepage-education-knowledge__head-main">
                    <span class="homepage-education-knowledge__head-icon" aria-hidden="true">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span class="homepage-education-knowledge__spark homepage-education-knowledge__spark--a"></span>
                        <span class="homepage-education-knowledge__spark homepage-education-knowledge__spark--b"></span>
                        <span class="homepage-education-knowledge__spark homepage-education-knowledge__spark--c"></span>
                    </span>
                    <div class="homepage-education-knowledge__head-copy">
                        <h2 class="homepage-education-knowledge__title">
                            Education &amp; <span class="homepage-education-knowledge__title-accent">Knowledge</span>
                        </h2>
                        <p class="homepage-education-knowledge__subtitle">
                            Learn, build skills and stay informed
                        </p>
                    </div>
                </div>
                <a class="homepage-education-knowledge__see-all" href="{{ $viewAllUrl }}">
                    See all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </header>

            <div class="homepage-education-knowledge__grid">
                @foreach($cards as $card)
                    <a href="{{ $card['url'] }}" class="homepage-education-knowledge-card">
                        <div class="homepage-education-knowledge-card__media">
                            <img
                                src="{{ $card['image'] }}"
                                alt=""
                                loading="lazy"
                                decoding="async"
                                width="640"
                                height="360"
                            >
                            <span class="homepage-education-knowledge-card__category homepage-education-knowledge-card__category--{{ $card['category_tone'] }}">
                                <i class="fa-solid {{ $card['category_icon'] }}" aria-hidden="true"></i>
                                {{ $card['category_label'] }}
                            </span>
                        </div>
                        <div class="homepage-education-knowledge-card__body">
                            <div class="homepage-education-knowledge-card__title-row">
                                <h3 class="homepage-education-knowledge-card__title">{{ $card['title'] }}</h3>
                                <p class="homepage-education-knowledge-card__rating">
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    {{ number_format($card['rating_score'], 1) }}
                                </p>
                            </div>
                            <div class="homepage-education-knowledge-card__desc-row">
                                <p class="homepage-education-knowledge-card__desc">{{ $card['description'] }}</p>
                                <span class="homepage-education-knowledge-card__reviews">({{ number_format($card['rating_count']) }})</span>
                            </div>
                            <p class="homepage-education-knowledge-card__location">
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
