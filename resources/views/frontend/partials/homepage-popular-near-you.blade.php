@php
    $cards = $cards ?? [];
    $viewAllUrl = $viewAllUrl ?? route('frontend.businesses.hub');
@endphp

@if(count($cards) > 0)
    <section class="homepage-popular-near" aria-label="Popular businesses near you">
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
                            Popular <span class="homepage-popular-near__title-accent">Businesses</span> Near&nbsp;You
                        </h2>
                    </div>
                </div>
                <a class="homepage-popular-near__see-all" href="{{ $viewAllUrl }}">
                    Explore more <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
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
                            @if(filled($card['category_label']))
                                <span class="homepage-popular-near-card__category homepage-popular-near-card__category--{{ $card['category_tone'] }}">
                                    <i class="fa-solid {{ $card['category_icon'] }}" aria-hidden="true"></i>
                                    <span class="homepage-popular-near-card__category-divider" aria-hidden="true"></span>
                                    {{ $card['category_label'] }}
                                </span>
                            @endif
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
                                {{-- Category cards list their vendors' names, shown one after another --}}
                                <h3 class="homepage-popular-near-card__title"@if(count($card['title_names'] ?? []) > 1) data-rotate-names="{{ json_encode($card['title_names'], JSON_UNESCAPED_UNICODE) }}"@endif>@if(filled($card['title'])){{ $card['title'] }}@else&nbsp;@endif</h3>
                                <p class="homepage-popular-near-card__rating">
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    <span>{{ number_format($card['rating_score'], 1) }}</span>
                                    <small>({{ number_format($card['rating_count']) }})</small>
                                </p>
                            </div>
                            @if(filled($card['offer_badge']))
                                <span class="homepage-card-pill homepage-card-pill--deal">{{ $card['offer_badge'] }}@if(filled($card['offer_badge_highlight'])) {{ $card['offer_badge_highlight'] }}@endif</span>
                            @elseif(filled($card['category_label']))
                                <span class="homepage-card-pill homepage-card-pill--tag">{{ $card['category_label'] }}</span>
                            @endif
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

    @once
        @push('scripts')
            <script>
                // Category cards with several vendors: show their names one after another
                (function () {
                    var titles = document.querySelectorAll('.homepage-popular-near-card__title[data-rotate-names]');
                    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    Array.prototype.forEach.call(titles, function (title, index) {
                        var names;
                        try {
                            names = JSON.parse(title.getAttribute('data-rotate-names'));
                        } catch (error) {
                            return;
                        }
                        if (!Array.isArray(names) || names.length < 2) return;

                        var current = 0;
                        // Staggered so the cards don't all change at once
                        setTimeout(function () {
                            setInterval(function () {
                                if (document.hidden) return;
                                current = (current + 1) % names.length;
                                if (reduceMotion) {
                                    title.textContent = names[current];
                                    return;
                                }
                                title.classList.add('is-swapping');
                                setTimeout(function () {
                                    title.textContent = names[current];
                                    title.classList.remove('is-swapping');
                                }, 250);
                            }, 2800);
                        }, index * 450);
                    });
                })();
            </script>
        @endpush
    @endonce
@endif
