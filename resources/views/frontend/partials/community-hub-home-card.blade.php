@php
    $hubUrl = route('community.index', ['hub' => $hubKey]);
@endphp

<a
    href="{{ $hubUrl }}"
    class="community-hub-mini-card"
    style="--hub-accent: {{ $hub['accent'] }};"
    aria-label="Browse {{ $hub['label'] }}"
>
    <span class="community-hub-mini-card__icon" aria-hidden="true">
        <i class="fa-solid {{ $hub['icon'] }}"></i>
    </span>
    <span class="community-hub-mini-card__body">
        <span class="community-hub-mini-card__title">{{ $hub['label'] }}</span>
        <span class="community-hub-mini-card__tagline">{{ $hub['tagline'] }}</span>
        <span class="community-hub-mini-card__cta">Explore <i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i></span>
    </span>
</a>
