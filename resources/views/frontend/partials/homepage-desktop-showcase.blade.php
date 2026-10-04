@php
    $section = $section ?? [];
    $cards = $cards ?? [];
    $sectionKey = $section['key'] ?? 'default';
    $footerMode = $section['footer_mode'] ?? 'rating';
@endphp

@if(count($cards) > 0)
    <section
        class="hp-desktop hp-desktop--{{ $sectionKey }}"
        aria-label="{{ trim(($section['title'] ?? '').' '.($section['title_accent'] ?? '')) }}"
    >
        <div class="hp-desktop__hero" style="--hp-desktop-hero: url('{{ $section['hero_bg'] ?? '' }}')">
            <div class="hp-desktop__hero-overlay"></div>
            <div class="hp-desktop__hero-inner">
                <div class="hp-desktop__hero-main">
                    <span class="hp-desktop__hero-icon hp-desktop__hero-icon--{{ $section['icon_style'] ?? 'default' }}" aria-hidden="true">
                        <i class="fa-solid {{ $section['icon'] ?? 'fa-compass' }}"></i>
                    </span>
                    <div class="hp-desktop__hero-copy">
                        <h2 class="hp-desktop__title">
                            {{ $section['title'] ?? '' }}
                            <span class="hp-desktop__title-accent">{{ $section['title_accent'] ?? '' }}</span>
                        </h2>
                        @if(!empty($section['subtitle']))
                            <p class="hp-desktop__subtitle">{{ $section['subtitle'] }}</p>
                        @endif
                        @if(!empty($section['chips']))
                            <ul class="hp-desktop__chips">
                                @foreach($section['chips'] as $chip)
                                    <li class="hp-desktop__chip hp-desktop__chip--{{ $chip['tone'] ?? 'slate' }}">
                                        <i class="fa-solid {{ $chip['icon'] ?? 'fa-circle' }}" aria-hidden="true"></i>
                                        {{ $chip['label'] }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                @if(!empty($section['cta_url']))
                    <a class="hp-desktop__cta" href="{{ $section['cta_url'] }}">
                        {{ $section['cta_label'] ?? 'See All' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </div>

        <div class="hp-desktop__grid">
            @foreach($cards as $card)
                <a href="{{ $card['url'] }}" class="hp-desktop-card">
                    <div class="hp-desktop-card__media">
                        <img src="{{ $card['image'] }}" alt="" loading="lazy" decoding="async" width="640" height="400">
                        @if(!empty($card['category_label']))
                            <span class="hp-desktop-card__badge hp-desktop-card__badge--{{ $card['category_tone'] ?? 'slate' }}">
                                <i class="fa-solid {{ $card['category_icon'] ?? 'fa-tag' }}" aria-hidden="true"></i>
                                {{ $card['category_label'] }}
                            </span>
                        @endif
                        @if(!empty($card['discount_badge']))
                            <span class="hp-desktop-card__discount">{{ $card['discount_badge'] }}</span>
                        @endif
                    </div>
                    <div class="hp-desktop-card__body">
                        <h3 class="hp-desktop-card__title">{{ $card['title'] }}</h3>
                        <p class="hp-desktop-card__desc">{{ $card['description'] ?? $card['subtitle'] ?? '' }}</p>
                        <div class="hp-desktop-card__foot">
                            @if($footerMode === 'community')
                                <div class="hp-desktop-card__stats">
                                    @if(!empty($card['stat_primary']))
                                        <span>{{ $card['stat_primary'] }}</span>
                                    @endif
                                    @if(!empty($card['stat_secondary']))
                                        <span>{{ $card['stat_secondary'] }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="hp-desktop-card__meta">
                                    @if(!empty($card['location']))
                                        <span class="hp-desktop-card__loc">
                                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                            {{ $card['location'] }}
                                        </span>
                                    @endif
                                    @if($footerMode === 'rating' && ($card['rating_score'] ?? 0) > 0)
                                        <span class="hp-desktop-card__rating">
                                            <i class="fa-solid fa-star" aria-hidden="true"></i>
                                            {{ number_format($card['rating_score'], 1) }}
                                            @if(($card['rating_count'] ?? 0) > 0)
                                                ({{ number_format($card['rating_count']) }})
                                            @endif
                                        </span>
                                    @endif
                                    @if($footerMode === 'offer' && !empty($card['footer_note']))
                                        <span class="hp-desktop-card__offer-meta">
                                            <i class="fa-solid fa-tag" aria-hidden="true"></i>
                                            {{ $card['footer_note'] }}
                                        </span>
                                    @endif
                                    @if($footerMode === 'study' && !empty($card['footer_note']))
                                        <span class="hp-desktop-card__study-meta">{{ $card['footer_note'] }}</span>
                                    @endif
                                </div>
                            @endif
                            <span class="hp-desktop-card__action hp-desktop-card__action--{{ $card['category_tone'] ?? 'slate' }}">
                                {{ $card['cta_label'] ?? 'View Details' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
