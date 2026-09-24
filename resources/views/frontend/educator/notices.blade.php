@extends('frontend.layouts.app')

@section('meta_title', $educator->display_name.' · Notice Board | SoilnWater')
@section('meta_description', 'Active notices and announcements from '.$educator->display_name.'.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/educator-module.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/educator-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div
  class="edu-page edu-notices-page"
  id="eduNoticesPage"
  data-login-url="{{ route('login') }}"
  data-is-auth="{{ auth()->check() ? '1' : '0' }}"
>
  <div class="container-fluid edu-container">
    <nav class="edu-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <span class="edu-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route('educator.index') }}">Teachers &amp; Tutors</a>
      <span class="edu-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ $educator->publicUrl() }}">{{ $educator->display_name }}</a>
      <span class="edu-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="edu-breadcrumb__current">Notice Board</span>
    </nav>

    <header class="edu-notices-page__hero">
      <div class="edu-notices-page__identity">
        <img src="{{ $educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="" class="edu-notices-page__avatar">
        <div>
          <p class="edu-notices-page__eyebrow">{{ $educator->roleLabel() }}</p>
          <h1>Notice Board</h1>
          <p class="edu-notices-page__lead mb-0">All active notices and announcements from {{ $educator->display_name }}.</p>
        </div>
      </div>
      <a href="{{ $educator->publicUrl() }}" class="edu-notices-page__back">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to profile
      </a>
    </header>

    <section class="edu-notice-section" aria-label="Notice Board">
      <div class="edu-notice-section__frame">
        @include('frontend.educator.partials.notice-board', [
          'notices' => $notices,
          'featured' => false,
          'layout' => 'grid',
          'showViewAll' => false,
        ])
      </div>
    </section>
  </div>
</div>

@include('frontend.educator.partials.notice-modal')
@endsection

@push('scripts')
<script src="{{ asset('assets/js/profile-section-nav.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/educator-profile.js') }}?v={{ now()->timestamp }}"></script>
@endpush
