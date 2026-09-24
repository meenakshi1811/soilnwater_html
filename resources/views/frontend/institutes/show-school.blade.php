@extends('frontend.layouts.app')

@php
  $listingContext = $listingContext ?? 'schools';
  $entityLabel = ($ownerRole ?? ($listingContext === 'schools' ? 'school' : 'institute')) === 'school' ? 'School' : 'Institute';
  $listingIndexRoute = $listingContext === 'schools' ? 'schools.index' : 'institutes.index';
  $listingSectionLabel = $listingContext === 'schools' ? 'Schools & Colleges' : 'Institutes';
  $listingTypeLabel = $listingContext === 'schools' ? 'Schools' : 'Institutes';
@endphp

@section('meta_title', $profile->displayName().' · '.$entityLabel.' | SoilnWater')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($profile->aboutText() ?: $institute->tagline ?: $entityLabel.' profile on SoilnWater'), 160))

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
  $tabItems = [
    ['id' => 'sch-overview', 'label' => 'Overview'],
    ['id' => 'sch-courses', 'label' => 'Courses'],
    ['id' => 'sch-facilities', 'label' => 'Facilities'],
    ['id' => 'sch-faculty', 'label' => 'Faculty'],
    ['id' => 'sch-admission', 'label' => 'Admission'],
    ['id' => 'sch-results', 'label' => 'Results'],
    ['id' => 'sch-gallery', 'label' => 'Gallery'],
    ['id' => 'sch-reviews', 'label' => 'Reviews'],
  ];
  $tabItems = array_values(array_filter($tabItems, fn ($tab) => collect($navItems)->contains(fn ($item) => $item['id'] === $tab['id'])));
@endphp

<div
  class="sch-page"
  id="schoolProfilePage"
  data-enquiry-url="{{ route($listingContext.'.enquiry', $institute->slug) }}"
  data-follow-url="{{ route($listingContext.'.follow', $institute->slug) }}"
  data-bookmark-url="{{ route($listingContext.'.bookmark', $institute->slug) }}"
  data-compare-url="{{ route($listingContext.'.compare.toggle', $institute->slug) }}"
  data-brochure-url="{{ route($listingContext.'.brochure', $institute->slug) }}"
  data-helpful-url="{{ route($listingContext.'.helpful', $institute->slug) }}"
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
      <a href="{{ route($listingIndexRoute) }}">{{ $listingSectionLabel }}</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route($listingIndexRoute) }}">{{ $listingTypeLabel }}</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="sch-breadcrumb__current" aria-current="page">{{ $profile->displayName() }}</span>
    </nav>

    <div class="sch-nav-mobile">
      <div class="sch-nav-mobile__inner">
        @foreach($navItems as $item)
          <a href="{{ $item['href'] ?? '#'.$item['id'] }}" class="sch-nav-mobile__link {{ empty($item['href']) ? 'js-sch-nav-link' : '' }}">
            <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
            {{ $item['label'] }}
          </a>
        @endforeach
      </div>
    </div>

    <div class="sch-grid">
      @include('frontend.institutes.partials.school-profile.nav', ['navItems' => $navItems, 'institute' => $institute, 'shareUrl' => $shareUrl])

      <div class="sch-body">
        @include('frontend.institutes.partials.school-profile.hero', compact('profile', 'institute', 'aboutText', 'aboutNeedsToggle', 'engagement', 'listingContext'))

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

        @if($institute->activeNotices->isNotEmpty())
          @php
            $noticesTotal = $institute->activeNotices->count();
            $noticesPreviewLimit = \App\Support\SchoolProfilePresenter::PREVIEW_LIMITS['notices'];
          @endphp
          <section class="sch-notice-section" id="sch-notices" aria-label="Notice Board">
            <div class="sch-notice-section__frame sch-card">
              @include('frontend.institutes.partials.notice-board', [
                'notices' => $institute->activeNotices->take($noticesPreviewLimit),
                'featured' => true,
                'layout' => 'grid',
                'viewAllUrl' => $institute->publicSectionUrl('notices'),
                'viewAllLabel' => 'View all notices',
                'showViewAll' => true,
              ])
            </div>
          </section>
        @endif

        <div class="sch-tabs" role="tablist" aria-label="{{ $entityLabel }} profile sections">
          @foreach($tabItems as $index => $item)
            <a href="#{{ $item['id'] }}" class="sch-tab js-sch-nav-link {{ $index === 0 ? 'is-active' : '' }}">{{ $item['label'] }}</a>
          @endforeach
          @if(count($navItems) > count($tabItems))
            <div class="dropdown sch-tab-more">
              <button class="sch-tab sch-tab--more dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">More</button>
              <ul class="dropdown-menu">
                @foreach(array_slice($navItems, count($tabItems)) as $item)
                  <li><a class="dropdown-item js-sch-nav-link" href="#{{ $item['id'] }}">{{ $item['label'] }}</a></li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>

        <div class="sch-lower-grid">
          <main class="sch-main">
            @include('frontend.institutes.partials.school-profile.content', compact('profile', 'institute', 'authUser', 'aboutText', 'aboutNeedsToggle', 'listingContext', 'entityLabel', 'engagement'))
          </main>

          @include('frontend.institutes.partials.school-profile.sidebar', compact('profile', 'institute', 'shareUrl'))
        </div>
      </div>
    </div>

    @include('frontend.institutes.partials.school-profile.footer-bar', compact('engagement', 'listingContext'))
  </div>
</div>

@include('frontend.institutes.partials.school-profile.modals', compact('institute', 'shareUrl', 'profile', 'engagement', 'listingContext'))
@include('community.partials.toastr-assets')
@endsection

@push('scripts')
<script src="{{ asset('assets/js/school-profile-notify.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/institute-enquiry-form.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/school-profile-actions.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/school-profile-page.js') }}?v={{ now()->timestamp }}" defer></script>
<script defer>
document.addEventListener('DOMContentLoaded', function () {
  var pageRoot = document.getElementById('schoolProfilePage');
  if (!pageRoot || typeof window.initInstituteEnquiryForm !== 'function') return;
  window.initInstituteEnquiryForm({
    form: document.getElementById('schoolEnquiryModalForm'),
    enquiryUrl: pageRoot.dataset.enquiryUrl,
    loginUrl: pageRoot.dataset.loginUrl,
    feedbackEl: document.getElementById('schoolEnquiryModalFeedback'),
    submitBtn: document.querySelector('#schoolEnquiryModalForm .js-school-enquiry-submit'),
  });
});
</script>
@endpush
