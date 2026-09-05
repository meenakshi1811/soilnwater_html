@extends('frontend.layouts.app')
@section('meta_title', 'Teachers & Tutors | SoilnWater')
@section('meta_description', 'Find verified teachers and tutors. Browse professional profiles, subjects, classes and study materials.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/educator-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="edu-page">
  <section class="edu-hero">
    <div class="container">
      <h1 class="edu-name" style="font-size:2rem">Teachers &amp; Tutors</h1>
      <p class="edu-tagline mb-0">Discover verified educators, explore their profiles, and access study materials.</p>
    </div>
  </section>

  <div class="container py-4">
    <form method="GET" class="edu-card mb-4">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Name, headline, institute">
        </div>
        <div class="col-md-2">
          <label class="form-label">Takes tuitions</label>
          <select name="takes_tuitions" class="form-select">
            <option value="">All</option>
            <option value="1" @selected(($filters['takes_tuitions'] ?? '') === '1')>Yes</option>
            <option value="0" @selected(($filters['takes_tuitions'] ?? '') === '0')>No</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">City</label>
          <input type="text" name="city" class="form-control" value="{{ $filters['city'] }}">
        </div>
        <div class="col-md-2">
          <label class="form-label">Subject</label>
          <input type="text" name="subject" class="form-control" value="{{ $filters['subject'] }}">
        </div>
        <div class="col-md-2">
          <button class="edu-btn edu-btn-primary w-100" type="submit">Filter</button>
        </div>
      </div>
    </form>

    <div class="row g-3">
      @forelse($educators as $educator)
        <div class="col-md-6 col-lg-4">
          <a href="{{ $educator->publicUrl() }}" class="edu-card d-block text-decoration-none h-100">
            <div class="d-flex gap-3 align-items-start">
              <img src="{{ $educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="" style="width:72px;height:72px;border-radius:50%;object-fit:cover">
              <div>
                <div class="edu-badge mb-1">{{ $educator->verifiedBadgeLabel() }}</div>
                <h3 class="h5 mb-1 text-dark">{{ $educator->display_name }}</h3>
                <p class="small text-muted mb-2">{{ $educator->professional_headline ?: $educator->roleLabel() }}</p>
                <div class="small text-muted">
                  <span><i class="fa-solid fa-star text-warning"></i> {{ number_format((float)$educator->average_rating, 1) }}</span>
                  @if($educator->city)
                    · {{ $educator->city }}
                  @endif
                  · {{ number_format($educator->materials_count) }} materials
                </div>
              </div>
            </div>
          </a>
        </div>
      @empty
        <div class="col-12">
          <div class="edu-card"><p class="edu-empty mb-0">No approved teachers or tutors yet.</p></div>
        </div>
      @endforelse
    </div>

    <div class="mt-4">{{ $educators->links() }}</div>
  </div>
</div>
@endsection
