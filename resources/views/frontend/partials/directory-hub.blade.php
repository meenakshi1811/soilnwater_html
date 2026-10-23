{{--
  Hub page shared by /businesses and /offers-and-ads: hero with counts, a card per module
  (counts, popular categories, links), how it works, and a call to action.
  $hub: crumb, eyebrow, eyebrow_icon, title, highlight, text, stats, modules_lead, steps, cta
  $modules: title, tagline, description, icon, tone, stats, categories, categories_url, empty,
            url, primary, secondary, and optionally key, listings_title, listings_url, listings
            (shown as a "Featured …" panel in the homepage colours of bh-featured--{key});
            listings_kind 'deal' shows offer/ad cards instead of business cards, listings_lead
            replaces "Featured"
--}}
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@php
    $formatCount = fn (int $count): string => $count >= 1000
        ? number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+'
        : number_format($count);
@endphp

<div class="bh-page" style="--bh-cols: {{ $modules->count() }}">
  <div class="bh-container">
    <nav class="bh-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      <span aria-current="page">{{ $hub['crumb'] }}</span>
    </nav>

    <section class="bh-hero">
      <div class="bh-hero__copy">
        <span class="bh-eyebrow"><i class="fa-solid {{ $hub['eyebrow_icon'] }}" aria-hidden="true"></i> {{ $hub['eyebrow'] }}</span>
        <h1 class="bh-hero__title">{{ $hub['title'] }} <span>{{ $hub['highlight'] }}</span></h1>
        <p class="bh-hero__text">{{ $hub['text'] }}</p>
        <div class="bh-hero__jump">
          @foreach ($modules as $module)
            <a href="{{ $module['url'] }}" class="bh-jump bh-jump--{{ $module['tone'] }}">
              <i class="fa-solid {{ $module['icon'] }}" aria-hidden="true"></i> {{ $module['title'] }}
            </a>
          @endforeach
        </div>
      </div>

      <ul class="bh-hero__stats">
        @foreach ($hub['stats'] as $stat)
          <li class="bh-stat">
            <span class="bh-icon bh-icon--{{ $stat['tone'] }}" aria-hidden="true"><i class="fa-solid {{ $stat['icon'] }}"></i></span>
            <span class="bh-stat__text">
              <strong>{{ $formatCount((int) $stat['count']) }}</strong>
              <span>{{ $stat['label'] }}</span>
            </span>
          </li>
        @endforeach
      </ul>
    </section>

    {{-- A few listings from each module to start exploring (modules that pass 'listings') --}}
    @foreach ($modules as $module)
      @if (! empty($module['listings']))
        <section class="bh-section bh-featured bh-featured--{{ $module['key'] }}" aria-labelledby="bhFeatured{{ $loop->index }}">
          <div class="bh-featured__head">
            <h2 class="bh-featured__title" id="bhFeatured{{ $loop->index }}">
              <i class="fa-solid {{ $module['icon'] }} bh-featured__icon" aria-hidden="true"></i>
              <span>{{ $module['listings_lead'] ?? 'Featured' }} <span class="bh-featured__accent">{{ $module['listings_title'] }}</span></span>
            </h2>
            <a href="{{ $module['listings_url'] }}" class="bh-featured__more">Explore more <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
          </div>

          <ul class="bh-rail">
            @foreach ($module['listings'] as $listing)
              <li class="bh-rail__item">
                @if (($module['listings_kind'] ?? 'business') === 'deal')
                  <article class="bh-biz bh-deal">
                    <a href="{{ $listing['url'] }}" class="bh-biz__media" tabindex="-1" aria-hidden="true">
                      <img
                        src="{{ $listing['image'] }}"
                        alt=""
                        loading="lazy"
                        onerror="this.onerror=null;this.src='{{ asset('assets/images/vendor-card-placeholder.svg') }}';"
                      >
                      <span class="bh-deal__tag bh-deal__tag--{{ $listing['category_tone'] }}">
                        <i class="fa-solid {{ $listing['category_icon'] }}" aria-hidden="true"></i> {{ $listing['category_label'] }}
                      </span>
                    </a>
                    <div class="bh-deal__body">
                      <h3 class="bh-biz__name"><a href="{{ $listing['url'] }}">{{ $listing['title'] }}</a></h3>
                      <span class="bh-deal__badge">{{ $listing['discount_badge'] }}</span>
                      <p class="bh-deal__desc">{{ $listing['description'] }}</p>
                      <div class="bh-biz__foot">
                        <span class="bh-biz__location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <span class="bh-biz__location-text">{{ $listing['location'] }}</span></span>
                        <span class="bh-deal__valid">{{ $listing['valid_till'] === 'No expiry' || $listing['valid_till'] === 'Limited time' ? $listing['valid_till'] : 'Valid till '.$listing['valid_till'] }}</span>
                      </div>
                    </div>
                  </article>
                @else
                <article class="bh-biz">
                  <a href="{{ $listing['url'] }}" class="bh-biz__media" tabindex="-1" aria-hidden="true">
                    <img
                      src="{{ $listing['cover'] }}"
                      alt=""
                      loading="lazy"
                      onerror="this.onerror=null;this.src='{{ asset('assets/images/vendor-card-placeholder.svg') }}';"
                    >
                    @if ($listing['premium'])
                      <span class="bh-biz__badge"><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium</span>
                    @endif
                  </a>
                  <div class="bh-biz__body">
                    <img
                      class="bh-biz__avatar"
                      src="{{ $listing['avatar'] }}"
                      alt=""
                      loading="lazy"
                      onerror="this.onerror=null;this.src='{{ asset('assets/images/profile-placeholder.svg') }}';"
                    >
                    <h3 class="bh-biz__name"><a href="{{ $listing['url'] }}">{{ $listing['name'] }}</a></h3>
                    <p class="bh-biz__category">{{ $listing['category'] }}</p>
                    <p class="bh-biz__location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <span class="bh-biz__location-text">{{ $listing['location'] }}</span></p>
                    <div class="bh-biz__foot">
                      <span class="bh-biz__rating">
                        <i class="fa-solid fa-star" aria-hidden="true"></i> {{ number_format($listing['rating'], 1) }}
                        <span>({{ number_format($listing['reviews']) }})</span>
                      </span>
                      <span class="bh-biz__verified"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                    </div>
                  </div>
                </article>
                @endif
              </li>
            @endforeach
          </ul>
        </section>
      @endif
    @endforeach

    <section class="bh-section" aria-labelledby="bhModulesTitle">
      <div class="bh-section__head">
        <h2 class="bh-section__title" id="bhModulesTitle">Explore by type</h2>
        <p class="bh-section__lead">{{ $hub['modules_lead'] }}</p>
      </div>

      <div class="bh-modules">
        @foreach ($modules as $module)
          <article class="bh-module bh-module--{{ $module['tone'] }}">
            <div class="bh-module__main">
              <div class="bh-module__head">
                <span class="bh-module__icon" aria-hidden="true"><i class="fa-solid {{ $module['icon'] }}"></i></span>
                <div class="bh-module__heading">
                  <h3 class="bh-module__title"><a href="{{ $module['url'] }}">{{ $module['title'] }}</a></h3>
                  <p class="bh-module__tagline">{{ $module['tagline'] }}</p>
                </div>
              </div>

              <p class="bh-module__desc">{{ $module['description'] }}</p>

              <dl class="bh-module__stats">
                @foreach ($module['stats'] as $stat)
                  <div>
                    <dt>{{ $stat['label'] }}</dt>
                    <dd>{{ $formatCount((int) $stat['value']) }}</dd>
                  </div>
                @endforeach
              </dl>
            </div>

            <div class="bh-module__cats">
              <div class="bh-module__cats-head">
                <p class="bh-module__label">Popular categories</p>
                <a href="{{ $module['categories_url'] }}" class="bh-module__all">View all</a>
              </div>
              @if ($module['categories']->isNotEmpty())
                <ul class="bh-chips">
                  @foreach ($module['categories'] as $category)
                    <li>
                      <a href="{{ $category['url'] }}" class="bh-chip">
                        {{ $category['name'] }} <span class="bh-chip__count">{{ number_format($category['count']) }}</span>
                      </a>
                    </li>
                  @endforeach
                </ul>
              @else
                <p class="bh-module__empty">{{ $module['empty'] }}</p>
              @endif
            </div>

            <div class="bh-module__actions">
              <a href="{{ $module['url'] }}" class="bh-btn bh-btn--tone">
                {{ $module['primary'] }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </a>
              <a href="{{ $module['secondary']['url'] }}" class="bh-btn bh-btn--soft">
                <i class="fa-solid {{ $module['secondary']['icon'] }}" aria-hidden="true"></i> {{ $module['secondary']['label'] }}
              </a>
            </div>
          </article>
        @endforeach
      </div>
    </section>

    <section class="bh-section" aria-labelledby="bhStepsTitle">
      <div class="bh-section__head">
        <h2 class="bh-section__title" id="bhStepsTitle">How it works</h2>
      </div>

      <ol class="bh-steps">
        @foreach ($hub['steps'] as $step)
          <li class="bh-step">
            <span class="bh-step__icon" aria-hidden="true"><i class="fa-solid {{ $step['icon'] }}"></i></span>
            <span class="bh-step__number">Step {{ $loop->iteration }}</span>
            <h3 class="bh-step__title">{{ $step['title'] }}</h3>
            <p class="bh-step__text">{{ $step['text'] }}</p>
          </li>
        @endforeach
      </ol>
    </section>

    <section class="bh-cta">
      <div class="bh-cta__copy">
        <span class="bh-cta__icon" aria-hidden="true"><i class="fa-solid {{ $hub['cta']['icon'] }}"></i></span>
        <div>
          <h2>{{ $hub['cta']['title'] }}</h2>
          <p>{{ $hub['cta']['text'] }}</p>
        </div>
      </div>
      <div class="bh-cta__actions">
        @foreach ($hub['cta']['actions'] as $action)
          <a href="{{ $action['url'] }}" class="bh-btn {{ $loop->first ? 'bh-btn--light' : 'bh-btn--outline-light' }}">{{ $action['label'] }}</a>
        @endforeach
      </div>
    </section>
  </div>
</div>
