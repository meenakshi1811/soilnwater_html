@extends('frontend.layouts.app')
@section('meta_title', 'Businesses near you | SoilnWater')
@section('meta_description', 'Find trusted vendors, consultants and service providers near you on SoilnWater.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/businesses-directory.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
    $formatCount = fn (int $count): string => $count >= 1000
        ? number_format($count / 1000, $count >= 10000 ? 0 : 1).'K+'
        : number_format($count);
    $listBusinessUrl = auth()->check() ? route('user.profile.edit') : route('login');
    $heroStats = [
        ['label' => 'Businesses listed', 'count' => $totals['businesses'], 'icon' => 'fa-briefcase', 'tone' => 'green'],
        ['label' => 'Premium partners', 'count' => $totals['premium'], 'icon' => 'fa-crown', 'tone' => 'orange'],
        ['label' => 'Categories', 'count' => $totals['categories'], 'icon' => 'fa-layer-group', 'tone' => 'purple'],
        ['label' => 'Members', 'count' => $totals['members'], 'icon' => 'fa-users', 'tone' => 'blue'],
    ];
    $steps = [
        ['title' => 'Search & filter', 'text' => 'Narrow down by category, location, distance and ratings.', 'icon' => 'fa-magnifying-glass'],
        ['title' => 'Check the profile', 'text' => 'See photos, services, reviews and verified badges.', 'icon' => 'fa-id-card'],
        ['title' => 'Contact directly', 'text' => 'Call or send an enquiry straight to the business.', 'icon' => 'fa-phone'],
        ['title' => 'Rate & review', 'text' => 'Share your experience to help others choose well.', 'icon' => 'fa-star'],
    ];
@endphp

<div class="bh-page">
  <div class="bh-container">
    <nav class="bh-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      <span aria-current="page">Businesses</span>
    </nav>

    <section class="bh-hero">
      <div class="bh-hero__copy">
        <span class="bh-eyebrow"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Business Directory</span>
        <h1 class="bh-hero__title">Find trusted businesses, <span>experts and services near you</span></h1>
        <p class="bh-hero__text">Vendors, consultants and service providers on SoilnWater, all in one place. Choose where you want to start.</p>
        <div class="bh-hero__jump">
          @foreach ($modules as $module)
            <a href="{{ $module['url'] }}" class="bh-jump bh-jump--{{ $module['tone'] }}">
              <i class="fa-solid {{ $module['icon'] }}" aria-hidden="true"></i> {{ $module['title'] }}
            </a>
          @endforeach
        </div>
      </div>

      <ul class="bh-hero__stats">
        @foreach ($heroStats as $stat)
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

    <section class="bh-section" aria-labelledby="bhModulesTitle">
      <div class="bh-section__head">
        <h2 class="bh-section__title" id="bhModulesTitle">Explore by type</h2>
        <p class="bh-section__lead">Only approved listings are shown.</p>
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
                <div>
                  <dt>Listed</dt>
                  <dd>{{ $formatCount((int) $module['stats']['trusted']) }}</dd>
                </div>
                <div>
                  <dt>Premium</dt>
                  <dd>{{ $formatCount((int) $module['stats']['premium']) }}</dd>
                </div>
                <div>
                  <dt>Categories</dt>
                  <dd>{{ $formatCount((int) $module['stats']['categories']) }}</dd>
                </div>
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
                <p class="bh-module__empty">Categories show up here as {{ $module['noun'] }} join.</p>
              @endif
            </div>

            <div class="bh-module__actions">
              <a href="{{ $module['url'] }}" class="bh-btn bh-btn--tone">
                Explore {{ strtolower($module['title']) }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </a>
              <a href="{{ $module['premium_url'] }}" class="bh-btn bh-btn--soft">
                <i class="fa-solid fa-crown" aria-hidden="true"></i> Premium
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
        @foreach ($steps as $step)
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
        <span class="bh-cta__icon" aria-hidden="true"><i class="fa-solid fa-rocket"></i></span>
        <div>
          <h2>Own a business? Get listed on SoilnWater</h2>
          <p>Create your vendor, consultant or service profile and reach customers near you.</p>
        </div>
      </div>
      <div class="bh-cta__actions">
        <a href="{{ $listBusinessUrl }}" class="bh-btn bh-btn--light">List your business</a>
        <a href="{{ route('frontend.premium.show', 'vendor') }}" class="bh-btn bh-btn--outline-light">See premium plans</a>
      </div>
    </section>
  </div>
</div>
@endsection
