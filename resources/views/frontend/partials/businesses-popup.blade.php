{{--
  Homepage hub popup, opened from a card under the banner (Businesses, Offers & Ads): a card per
  module with its busiest categories.
  $popupId, $popupTitle, $popupLead
  $modules: key, title, tagline, description, icon, image, categories (name, icon, url), url, view_all_url
--}}
@once
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/businesses-popup.css') }}?v={{ now()->timestamp }}">
@endpush
@endonce

@php
    $popupId = $popupId ?? 'businessesPopup';
@endphp

<div class="modal fade businesses-popup businesses-popup--cards-{{ count($modules) }}" id="{{ $popupId }}" tabindex="-1" aria-labelledby="{{ $popupId }}Title" aria-hidden="true" style="--bp-cols: {{ count($modules) }}">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down businesses-popup__dialog">
    <div class="modal-content businesses-popup__content">
      <div class="businesses-popup__head">
        <div>
          <h2 class="businesses-popup__title" id="{{ $popupId }}Title">{{ $popupTitle ?? 'Explore Businesses' }}</h2>
          <p class="businesses-popup__lead">{{ $popupLead ?? 'Vendors, consultants and service providers near you' }}</p>
        </div>
        <button type="button" class="btn-close businesses-popup__close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body businesses-popup__body">
        <div class="bp-grid">
          @foreach ($modules as $module)
            <article class="bp-card bp-card--{{ $module['key'] }}">
              <div class="bp-card__top">
                <img class="bp-card__art" src="{{ asset($module['image']) }}" alt="" loading="lazy">

                <div class="bp-card__head">
                  <span class="bp-card__icon" aria-hidden="true"><i class="fa-solid {{ $module['icon'] }}"></i></span>
                  <div class="bp-card__heading">
                    <h3 class="bp-card__title">{{ $module['title'] }}</h3>
                    <p class="bp-card__tagline">{{ $module['tagline'] }}</p>
                  </div>
                  <a href="{{ $module['view_all_url'] }}" class="bp-card__view-all">
                    Explore more <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                  </a>
                </div>

                <p class="bp-card__desc">{{ $module['description'] }}</p>

                <div class="bp-card__body">
                  @if ($module['categories'] !== [])
                    <ul class="bp-card__chips">
                      @foreach ($module['categories'] as $category)
                        <li>
                          <a href="{{ $category['url'] }}" class="bp-chip" title="{{ $category['name'] }}">
                            <i class="fa-solid {{ $category['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $category['name'] }}</span>
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </div>
              </div>

              <div class="bp-card__foot">
                <a href="{{ $module['url'] }}" class="bp-card__explore">
                  Explore {{ $module['title'] }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
              </div>
            </article>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
