{{--
    A colourful panel like the homepage's education sections: gradient shell, a heading with one
    word in the accent colour, white cards (picture, name, tag, one line). With $join, the free
    places in a row of $columns show one "List your …" card.
--}}
@php
    $nav = $nav ?? [];
    $subtitle = $subtitle ?? null;
    $join = $join ?? null;
    $columns = $columns ?? 3;
    $freeSlots = $join ? max(0, $columns - count($cards)) : 0;
@endphp

<section class="eh-cp eh-cp--{{ $theme }}" aria-labelledby="{{ $id }}">
    <header class="eh-cp__head">
        <span class="eh-cp__icon" aria-hidden="true">
            <i class="fa-solid {{ $icon }}"></i>
            <span class="eh-cp__spark eh-cp__spark--a"></span>
            <span class="eh-cp__spark eh-cp__spark--b"></span>
            <span class="eh-cp__spark eh-cp__spark--c"></span>
        </span>
        <div class="eh-cp__heading">
            <h2 class="eh-cp__title" id="{{ $id }}">{{ $lead }} <span>{{ $accent }}</span></h2>
            @if(count($nav) > 0)
                <nav class="eh-cp__nav" aria-label="{{ $lead }} {{ $accent }}">
                    @foreach($nav as $link)
                        @if(! $loop->first)
                            <span class="eh-cp__nav-dot" aria-hidden="true">•</span>
                        @endif
                        <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            @elseif(filled($subtitle))
                <p class="eh-cp__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        <a class="eh-cp__more" href="{{ $url }}">
            <span class="eh-cp__more-lg">See all</span><span class="eh-cp__more-sm">Explore more</span>
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
    </header>

    <div class="eh-cp__grid">
        @foreach($cards as $card)
            <a href="{{ $card['url'] }}" class="eh-cp-card">
                <span class="eh-cp-card__media">
                    @if(filled($card['image']))
                        <img src="{{ $card['image'] }}" alt="" loading="lazy" decoding="async" width="640" height="400">
                    @elseif(filled($card['logo'] ?? null))
                        <img src="{{ $card['logo'] }}" alt="" class="eh-cp-card__logo" loading="lazy" decoding="async">
                    @else
                        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                    @endif
                    @if(! empty($card['verified']))
                        <span class="eh-cp-card__verified"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                    @endif
                </span>
                <span class="eh-cp-card__body">
                    {{-- An image card has no name; the blank line keeps the cards aligned --}}
                    <strong class="eh-cp-card__title">@if(filled($card['title'])){{ $card['title'] }}@else&nbsp;@endif</strong>
                    @if(filled($card['pill'] ?? null))
                        <span class="eh-cp-card__pill">{{ $card['pill'] }}</span>
                    @endif
                    @if(filled($card['place'] ?? null))
                        <span class="eh-cp-card__line"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span>{{ $card['place'] }}</span></span>
                    @elseif(filled($card['line'] ?? null))
                        <span class="eh-cp-card__line eh-cp-card__line--text">{{ $card['line'] }}</span>
                    @endif
                </span>
            </a>
        @endforeach

        @if($freeSlots > 0)
            <a href="{{ $join['url'] }}" class="eh-cp-join eh-cp-join--span-{{ $freeSlots }}">
                <span class="eh-cp-join__icon" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
                <strong>{{ $join['title'] }}</strong>
                <small>{{ $join['hint'] }}</small>
            </a>
        @endif
    </div>
</section>
