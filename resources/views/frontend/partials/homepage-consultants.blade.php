@php
    $cards = $cards ?? [];
    $viewAllUrl = $viewAllUrl ?? route('frontend.consultants.index');
    $showEnquiry = $showEnquiry ?? true;
@endphp

@if(count($cards) > 0)
    <section class="homepage-consultants" aria-label="Expert consultants">
        <div class="homepage-consultants__shell">
            <header class="homepage-consultants__head">
                <div class="homepage-consultants__head-main">
                    <span class="homepage-consultants__head-icon" aria-hidden="true">
                        <i class="fa-solid fa-user-tie"></i>
                    </span>
                    <div class="homepage-consultants__head-copy">
                        <h2 class="homepage-consultants__title">
                            Expert <span class="homepage-consultants__title-accent">Consultants</span>
                        </h2>
                        <p class="homepage-consultants__subtitle">
                            Connect with trusted professionals who can help you
                        </p>
                    </div>
                </div>
                <div class="homepage-consultants__head-actions">
                    @if($showEnquiry)
                        <button
                            type="button"
                            class="homepage-consultants__enquiry"
                            data-bs-toggle="modal"
                            data-bs-target="#consultantEnquiryModal"
                        >
                            Enquiry
                        </button>
                    @endif
                    <a class="homepage-consultants__see-all" href="{{ $viewAllUrl }}">
                        @include('frontend.partials.homepage-promo-see-all-label') <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </header>

            <div class="homepage-consultants__grid">
                @foreach($cards as $card)
                    <a href="{{ $card['url'] }}" class="homepage-consultants-card">
                        <div class="homepage-consultants-card__media">
                            <img
                                src="{{ $card['image'] }}"
                                alt=""
                                loading="lazy"
                                decoding="async"
                                width="640"
                                height="360"
                            >
                            <span class="homepage-consultants-card__category homepage-consultants-card__category--{{ $card['category_tone'] }}">
                                <i class="fa-solid {{ $card['category_icon'] }}" aria-hidden="true"></i>
                                {{ $card['category_label'] }}
                            </span>
                        </div>
                        <div class="homepage-consultants-card__body">
                            <div class="homepage-consultants-card__title-row">
                                <h3 class="homepage-consultants-card__title">{{ $card['title'] }}</h3>
                                <p class="homepage-consultants-card__rating">
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    {{ number_format($card['rating_score'], 1) }}
                                </p>
                            </div>
                            <div class="homepage-consultants-card__desc-row">
                                <p class="homepage-consultants-card__desc">{{ $card['description'] }}</p>
                                <span class="homepage-consultants-card__reviews">({{ number_format($card['rating_count']) }})</span>
                            </div>
                            <p class="homepage-consultants-card__location">
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
