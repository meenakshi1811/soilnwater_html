@extends('frontend.layouts.app')

@section('meta_title', $institute->displayName().' | Schools & Institutes | SoilnWater')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($institute->about ?: $institute->tagline ?: $institute->description ?: 'School profile on SoilnWater'), 160))

@php
  $photo = $institute->logoUrl() ?: asset('assets/images/logo_soilnwater.webp');
  $grades = collect($institute->grades_offered ?? []);
  $facilities = collect($institute->facilities ?? []);
  $authUser = auth()->user();
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/institute-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div
  class="institute-profile-page"
  id="instituteProfilePage"
  data-enquiry-url="{{ route('institute.enquiry', $institute->slug) }}"
  data-login-url="{{ route('login') }}"
  data-is-auth="{{ auth()->check() ? '1' : '0' }}"
>
  <div class="container py-4">
    <nav class="institute-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}">Home</a>
      <span aria-hidden="true">›</span>
      <a href="{{ route('institute.index') }}">Schools &amp; Institutes</a>
      <span aria-hidden="true">›</span>
      <span aria-current="page">{{ $institute->displayName() }}</span>
    </nav>

    <div class="institute-hero chart-card">
      <div class="institute-hero__media">
        <img src="{{ $photo }}" alt="{{ $institute->displayName() }}" class="institute-hero__logo" onerror="this.onerror=null;this.src='{{ asset('assets/images/logo_soilnwater.webp') }}';">
      </div>
      <div class="institute-hero__body">
        <div class="institute-hero__badges">
          <span class="badge text-bg-primary">{{ $institute->institutionTypeLabel() }}</span>
          @if($institute->is_verified)
            <span class="badge text-bg-success"><i class="fa-solid fa-circle-check"></i> Verified</span>
          @endif
          @if($institute->board_affiliation)
            <span class="badge text-bg-light text-dark">{{ $institute->board_affiliation }}</span>
          @endif
        </div>
        <h1 class="institute-hero__title">{{ $institute->displayName() }}</h1>
        @if($institute->tagline)
          <p class="institute-hero__tagline">{{ $institute->tagline }}</p>
        @endif
        <p class="institute-hero__location">
          <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
          {{ $institute->address ? $institute->address.', ' : '' }}{{ $institute->locationLabel() }} {{ $institute->pincode }}
        </p>
        @if($institute->website_url)
          <p class="mb-0"><a href="{{ $institute->website_url }}" target="_blank" rel="noopener"><i class="fa-solid fa-globe"></i> Visit website</a></p>
        @endif
      </div>
    </div>

    <div class="row g-4 mt-1">
      <div class="col-lg-8">
        @if($institute->about || $institute->description)
          <section class="chart-card institute-section">
            <h2>About</h2>
            <div class="institute-section__content">{!! nl2br(e($institute->about ?: $institute->description)) !!}</div>
          </section>
        @endif

        @if($grades->isNotEmpty())
          <section class="chart-card institute-section">
            <h2>Grades / Classes offered</h2>
            <div class="institute-chip-list">
              @foreach($grades as $grade)
                <span class="institute-chip">{{ $grade }}</span>
              @endforeach
            </div>
          </section>
        @endif

        @if($facilities->isNotEmpty())
          <section class="chart-card institute-section">
            <h2>Facilities</h2>
            <div class="institute-chip-list">
              @foreach($facilities as $facility)
                <span class="institute-chip institute-chip--facility"><i class="fa-solid fa-check"></i> {{ $facility }}</span>
              @endforeach
            </div>
          </section>
        @endif
      </div>

      <div class="col-lg-4">
        <section class="chart-card institute-section">
          <h2>Contact details</h2>
          <ul class="institute-contact-list list-unstyled mb-0">
            @if($institute->phone)
              <li><i class="fa-solid fa-phone"></i> {{ $institute->phone }}</li>
            @endif
            @if($institute->whatsapp)
              <li><i class="fa-brands fa-whatsapp"></i> {{ $institute->whatsapp }}</li>
            @endif
            @if($institute->email)
              <li><i class="fa-solid fa-envelope"></i> {{ $institute->email }}</li>
            @endif
            @if($institute->contact_person)
              <li><i class="fa-solid fa-user"></i> {{ $institute->contact_person }}</li>
            @endif
          </ul>
        </section>

        <section class="chart-card institute-section" id="instituteEnquirySection">
          <h2>Send an enquiry</h2>
          @guest
            <p class="text-secondary">Please <a href="{{ route('login') }}">login</a> to send an enquiry to this institute.</p>
          @else
            <form id="instituteEnquiryForm" class="js-institute-enquiry-form">
              @csrf
              <div class="mb-3">
                <label class="form-label" for="enquiry_name">Your name</label>
                <input type="text" class="form-control" id="enquiry_name" name="name" value="{{ $authUser->name }}" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="enquiry_email">Email</label>
                <input type="email" class="form-control" id="enquiry_email" name="email" value="{{ $authUser->email }}">
              </div>
              <div class="mb-3">
                <label class="form-label" for="enquiry_phone">Phone</label>
                <input type="text" class="form-control" id="enquiry_phone" name="phone" value="{{ $authUser->phone_number }}">
              </div>
              <div class="mb-3">
                <label class="form-label" for="enquiry_subject">Subject</label>
                <input type="text" class="form-control" id="enquiry_subject" name="subject" placeholder="Admission enquiry">
              </div>
              <div class="mb-3">
                <label class="form-label" for="enquiry_message">Message</label>
                <textarea class="form-control" id="enquiry_message" name="message" rows="4" required placeholder="Tell us about your enquiry..."></textarea>
              </div>
              <div id="instituteEnquiryFeedback" class="alert d-none" role="alert"></div>
              <button type="submit" class="btn btn-primary w-100 js-institute-enquiry-submit">
                <span class="js-enquiry-btn-text">Send enquiry</span>
                <span class="js-enquiry-btn-sending d-none">Sending...</span>
              </button>
            </form>
          @endguest
        </section>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/institute-profile.js') }}?v={{ now()->timestamp }}"></script>
@endpush
