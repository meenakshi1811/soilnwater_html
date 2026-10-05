@php
    $cards = $cards ?? [];
    $navLinks = $navLinks ?? [];
    $viewAllUrl = $viewAllUrl ?? route('study-materials.library');
@endphp

@if(count($cards) > 0)
    <section class="homepage-study-library" aria-label="Study material library">
        <div class="homepage-study-library__shell">
            <header class="homepage-study-library__head">
                <div class="homepage-study-library__head-main">
                    <span class="homepage-study-library__head-icon" aria-hidden="true">
                        <i class="fa-solid fa-book"></i>
                        <span class="homepage-study-library__spark homepage-study-library__spark--a"></span>
                        <span class="homepage-study-library__spark homepage-study-library__spark--b"></span>
                    </span>
                    <div class="homepage-study-library__head-copy">
                        <h2 class="homepage-study-library__title">
                            Study Material <span class="homepage-study-library__title-accent">Library</span>
                        </h2>
                        @if(count($navLinks) > 0)
                            <nav class="homepage-study-library__nav" aria-label="Study material categories">
                                @foreach($navLinks as $index => $link)
                                    @if($index > 0)
                                        <span class="homepage-study-library__nav-dot" aria-hidden="true">•</span>
                                    @endif
                                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                                @endforeach
                            </nav>
                        @endif
                    </div>
                </div>
                <a class="homepage-study-library__see-all" href="{{ $viewAllUrl }}">
                    @include('frontend.partials.homepage-promo-see-all-label') <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </header>

            <div class="homepage-study-library__grid">
                @foreach($cards as $card)
                    <a href="{{ $card['url'] }}" class="homepage-study-library-card">
                        <div class="homepage-study-library-card__media">
                            <img
                                src="{{ $card['image'] }}"
                                alt=""
                                loading="lazy"
                                decoding="async"
                                width="640"
                                height="360"
                            >
                            <span class="homepage-study-library-card__badge homepage-study-library-card__badge--{{ $card['badge_tone'] }}">
                                <i class="fa-solid {{ $card['badge_icon'] }}" aria-hidden="true"></i>
                                <span>{{ $card['badge_label'] }}</span>
                            </span>
                        </div>
                        <div class="homepage-study-library-card__body">
                            <h3 class="homepage-study-library-card__title">{{ $card['title'] }}</h3>
                            <p class="homepage-study-library-card__subtitle">{{ $card['subtitle'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
