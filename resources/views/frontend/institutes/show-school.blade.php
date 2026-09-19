@extends('frontend.layouts.app')

@section('meta_title', $profile->displayName().' · School | SoilnWater')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($profile->aboutText() ?: $institute->tagline ?: 'School profile on SoilnWater'), 160))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/school-profile-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
  /** @var \App\Support\SchoolProfilePresenter $profile */
  $navItems = $profile->navItems();
  $authUser = auth()->user();
  $shareUrl = $institute->publicUrl();
  $aboutText = $profile->aboutText();
  $aboutNeedsToggle = strlen($aboutText) > 320;
@endphp

<div
  class="sch-page"
  id="schoolProfilePage"
  data-enquiry-url="{{ route('schools.enquiry', $institute->slug) }}"
  data-login-url="{{ route('login') }}"
  data-is-auth="{{ auth()->check() ? '1' : '0' }}"
  data-share-url="{{ $shareUrl }}"
  data-share-title="{{ $profile->displayName() }}"
>
  <div class="container-fluid sch-container">
    @if(session('status'))
      <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <nav class="sch-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route('schools.index') }}">Schools &amp; Colleges</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route('schools.index') }}">Schools</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="sch-breadcrumb__current" aria-current="page">{{ $profile->displayName() }}</span>
    </nav>

    <div class="sch-nav-mobile">
      <div class="sch-nav-mobile__inner">
        @foreach($navItems as $item)
          <a href="#{{ $item['id'] }}" class="sch-nav-mobile__link js-sch-nav-link">
            <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
            {{ $item['label'] }}
          </a>
        @endforeach
      </div>
    </div>

    <div class="sch-grid">
      @include('frontend.institutes.partials.school-profile.nav', ['navItems' => $navItems, 'institute' => $institute, 'shareUrl' => $shareUrl])

      <main class="sch-main">
        @include('frontend.institutes.partials.school-profile.hero', compact('profile', 'institute', 'aboutText', 'aboutNeedsToggle'))

        <div class="sch-card sch-quick-stats">
          @foreach($profile->quickStats() as $index => $stat)
            <article class="sch-quick-stat sch-quick-stat--tone-{{ ($index % 6) + 1 }}">
              <span class="sch-quick-stat__icon"><i class="fa-solid {{ $stat['icon'] }}" aria-hidden="true"></i></span>
              <div>
                <strong>{{ $stat['value'] }}</strong>
                <span>{{ $stat['label'] }}</span>
              </div>
            </article>
          @endforeach
        </div>

        <div class="sch-tabs" role="tablist" aria-label="School profile sections">
          @foreach(array_slice($navItems, 0, 8) as $index => $item)
            <a href="#{{ $item['id'] }}" class="sch-tab js-sch-nav-link {{ $index === 0 ? 'is-active' : '' }}">{{ $item['label'] }}</a>
          @endforeach
          @if(count($navItems) > 8)
            <div class="dropdown sch-tab-more">
              <button class="sch-tab sch-tab--more dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">More</button>
              <ul class="dropdown-menu">
                @foreach(array_slice($navItems, 8) as $item)
                  <li><a class="dropdown-item js-sch-nav-link" href="#{{ $item['id'] }}">{{ $item['label'] }}</a></li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>

        @include('frontend.institutes.partials.school-profile.content', compact('profile', 'institute', 'authUser', 'aboutText', 'aboutNeedsToggle'))
      </main>

      @include('frontend.institutes.partials.school-profile.sidebar', compact('profile', 'institute', 'shareUrl'))
    </div>

    @include('frontend.institutes.partials.school-profile.footer-bar')
  </div>
</div>

@include('frontend.institutes.partials.school-profile.modals', compact('institute', 'shareUrl', 'profile'))
@endsection

@push('scripts')
<script src="{{ asset('assets/js/school-profile-page.js') }}?v={{ now()->timestamp }}" defer></script>
@endpush
