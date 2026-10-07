@extends('backend.layouts.app')
@section('title', 'Tuition Details')

@section('content')
@php
  $educator = $educator ?? null;
  $availability = old('availability', $educator->availability ?? [['day' => '', 'slots' => '']]);
  $tuitionBatches = old('tuition_batches');
  if ($tuitionBatches === null) {
    $tuitionBatches = $educator->normalizedTuitionBatches();
  }
  if (empty($tuitionBatches)) {
    $tuitionBatches = [['class' => '', 'subject' => '', 'batch_type' => '', 'batch_time' => '', 'days' => [], 'student_count' => '', 'cost' => '', 'seats_status' => 'available']];
  }
  $tuitionDeliveryOptions = $educator->normalizedTuitionDeliveryOptions();
@endphp
<div class="admin-panel ems-page edu-profile-page">
  <div class="mb-4">
    <p class="ems-kicker mb-1">Educator Portal</p>
    <h2 class="admin-title mb-1">Tuition details</h2>
    <p class="text-secondary mb-0">Configure batches, fees, and availability. Your public profile shows this section only when tuition is enabled and at least one detail is filled in.</p>
  </div>

  @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
  @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif

  <form method="POST" action="{{ route('educator.tuition.update') }}" class="chart-card p-3 p-lg-4">
    @csrf
    @method('PUT')

    @include('backend.educator.partials.tuition-form-fields', [
      'educator' => $educator,
      'tuitionBatches' => $tuitionBatches,
      'availability' => $availability,
      'tuitionDeliveryOptions' => $tuitionDeliveryOptions,
    ])

    <div class="d-flex gap-2 mt-4 pt-3 border-top">
      <a href="{{ route('educator.dashboard') }}" class="btn btn-outline-secondary">Back</a>
      <button type="submit" class="btn btn-primary ems-btn-primary px-4">
        <i class="fa-solid fa-floppy-disk me-1"></i> Save tuition details
      </button>
    </div>
  </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/educator-portal-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/educator-tuition-form.js') }}?v={{ now()->timestamp }}"></script>
@if(config('services.google.maps_api_key'))
<script>
window.initEducatorTuitionPlacesAutocomplete = function () {
  if (window.FormHelper && typeof window.FormHelper.initTuitionPointAddressAutocomplete === 'function') {
    window.FormHelper.initTuitionPointAddressAutocomplete();
  }
};
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initEducatorTuitionPlacesAutocomplete"></script>
@endif
@endpush
