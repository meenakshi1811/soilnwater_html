@extends('backend.layouts.app')

@section('title', 'Public Page Content')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/educator-portal-profile.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page institute-portal institute-public-page">
  @includeWhen($managedInstitute, 'backend.institute.partials.admin-manage-bar')

  <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
      <p class="ems-kicker mb-1">{{ $portalLabel }} public website</p>
      <h2 class="admin-title mb-1">Manage public page content</h2>
      <p class="mb-0 text-secondary">Add notices, classes, results, achievements, and books shown on your public profile.</p>
    </div>
    @if($institute->isApproved())
      <a href="{{ $institute->publicUrl() }}" target="_blank" class="btn btn-outline-primary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View public page
      </a>
    @endif
  </div>

  <div id="instPublicPageAlert" class="alert d-none" role="alert"></div>

  <nav class="sch-portal-nav mb-4" aria-label="Public page sections">
    <a href="#inst-section-notices" class="sch-portal-nav__link is-active"><i class="fa-solid fa-bullhorn"></i> Notices</a>
    <a href="#inst-section-classes" class="sch-portal-nav__link"><i class="fa-solid fa-chalkboard"></i> Classes</a>
    <a href="#inst-section-performers" class="sch-portal-nav__link"><i class="fa-solid fa-medal"></i> Results</a>
    <a href="#inst-section-achievements" class="sch-portal-nav__link"><i class="fa-solid fa-trophy"></i> Achievements</a>
    <a href="#inst-section-books" class="sch-portal-nav__link"><i class="fa-solid fa-book"></i> Books</a>
  </nav>

  @include('backend.institute.partials.public-page.notices', ['institute' => $institute])
  @include('backend.institute.partials.public-page.classes', ['institute' => $institute])
  @include('backend.institute.partials.public-page.performers', ['institute' => $institute])
  @include('backend.institute.partials.public-page.achievements', ['institute' => $institute])
  @include('backend.institute.partials.public-page.books', ['institute' => $institute])
</div>
@endsection

@push('scripts')
@php
    $instPublicContentRoutes = [
        'notices' => $portalRoute('notices.store'),
        'achievements' => $portalRoute('achievements.store'),
        'performers' => $portalRoute('performers.store'),
        'classes' => $portalRoute('classes.store'),
        'books' => $portalRoute('books.store'),
    ];
@endphp
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
window.instPublicContentRoutes = @json($instPublicContentRoutes);
</script>
<script src="{{ asset('assets/js/institute-public-content.js') }}?v={{ now()->timestamp }}"></script>
@endpush
