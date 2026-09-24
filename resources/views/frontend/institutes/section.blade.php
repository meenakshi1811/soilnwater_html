@extends('frontend.layouts.app')

@section('meta_title', $profile->displayName().' · '.$sectionTitle.' | SoilnWater')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($sectionLead), 160))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/school-profile-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
  $listingIndexRoute = $listingContext === 'schools' ? 'schools.index' : 'institutes.index';
  $listingSectionLabel = $listingContext === 'schools' ? 'Schools & Colleges' : 'Institutes';
  $listingTypeLabel = $listingContext === 'schools' ? 'Schools' : 'Institutes';
@endphp

<div
  class="sch-page sch-section-list-page"
  id="schoolSectionPage"
  data-login-url="{{ route('login') }}"
  data-is-auth="{{ auth()->check() ? '1' : '0' }}"
>
  <div class="container-fluid sch-container">
    <nav class="sch-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route($listingIndexRoute) }}">{{ $listingSectionLabel }}</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ $institute->publicUrl() }}">{{ $profile->displayName() }}</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="sch-breadcrumb__current" aria-current="page">{{ $sectionTitle }}</span>
    </nav>

    <header class="sch-section-page-hero sch-card">
      <div class="sch-section-page-hero__identity">
        <img src="{{ $institute->logoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="" class="sch-section-page-hero__logo">
        <div>
          <p class="sch-section-page-hero__eyebrow">{{ $entityLabel }}</p>
          <h1>{{ $sectionTitle }}</h1>
          <p class="sch-section-page-hero__lead mb-0">{{ $sectionLead }}</p>
        </div>
      </div>
      <a href="{{ $institute->publicUrl() }}" class="sch-btn sch-btn-outline">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to profile
      </a>
    </header>

    <div class="sch-section-page-body">
      @if($section === 'notices')
        <div class="sch-notice-section__frame sch-card">
          @include('frontend.institutes.partials.notice-board', [
              'notices' => $institute->activeNotices,
              'featured' => false,
              'layout' => 'grid',
              'showViewAll' => false,
          ])
        </div>
      @else
        @include('frontend.institutes.partials.school-profile.sections', [
          'onlySection' => match ($section) {
              'articles' => 'news',
              default => $section,
          },
          'isProfilePreview' => false,
          'profile' => $profile,
          'institute' => $institute,
          'entityLabel' => $entityLabel,
          'listingContext' => $listingContext,
        ])
      @endif
    </div>
  </div>
</div>

@include('frontend.institutes.partials.notice-modal')
@if($section === 'jobs')
  @include('frontend.institutes.partials.school-profile.job-modal')
  @include('community.partials.toastr-assets')
@endif
@endsection

@push('scripts')
<script src="{{ asset('assets/js/school-profile-notify.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/profile-section-nav.js') }}?v={{ now()->timestamp }}" defer></script>
@if($section === 'jobs')
<script src="{{ asset('assets/js/school-profile-jobs.js') }}?v={{ now()->timestamp }}" defer></script>
@endif
<script src="{{ asset('assets/js/school-profile-page.js') }}?v={{ now()->timestamp }}" defer></script>
@endpush
