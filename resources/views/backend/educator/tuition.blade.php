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
    $tuitionBatches = [['class' => '', 'subject' => '', 'batch_type' => '', 'student_count' => '', 'cost' => '']];
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
<script>
(function () {
  const templates = {
    availability: (i) => `<div class="edu-repeat-row edu-repeat-row--availability js-repeat-row"><input type="text" name="availability[${i}][day]" class="form-control" placeholder="Monday"><input type="text" name="availability[${i}][slots]" class="form-control" placeholder="4:00 PM – 7:00 PM"><button type="button" class="btn btn-outline-danger edu-btn-remove js-remove-row" title="Remove">&times;</button></div>`,
    tuitionBatch: (i) => `<div class="tuition-batch-card js-repeat-row"><div class="tuition-batch-card__grid"><div><label class="form-label d-md-none">Class</label><input type="text" name="tuition_batches[${i}][class]" class="form-control" placeholder="Class 10"></div><div><label class="form-label d-md-none">Subject</label><input type="text" name="tuition_batches[${i}][subject]" class="form-control" placeholder="Physics"></div><div><label class="form-label d-md-none">Batch type</label><input type="text" name="tuition_batches[${i}][batch_type]" class="form-control" placeholder="Small group" list="tuitionBatchTypeOptions"></div><div><label class="form-label d-md-none">Students</label><input type="number" name="tuition_batches[${i}][student_count]" class="form-control" min="1" placeholder="8"></div><div><label class="form-label d-md-none">Cost</label><input type="text" name="tuition_batches[${i}][cost]" class="form-control" placeholder="₹500 / month"></div><div class="tuition-batch-card__actions"><button type="button" class="btn btn-outline-danger edu-btn-remove w-100 js-remove-row" title="Remove batch">&times;</button></div></div></div>`
  };

  document.querySelectorAll('[data-add]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const wrap = document.querySelector(btn.dataset.add);
      const i = wrap.querySelectorAll('.js-repeat-row').length;
      wrap.insertAdjacentHTML('beforeend', templates[btn.dataset.template](i));
    });
  });

  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('js-delivery-option-toggle')) {
      const fields = e.target.closest('[data-delivery-option]')?.querySelector('.edu-delivery-option__fields');
      if (fields) {
        fields.classList.toggle('d-none', !e.target.checked);
      }
    }
  });

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('js-remove-row')) {
      const row = e.target.closest('.js-repeat-row');
      const wrap = row?.parentElement;
      if (row && wrap && wrap.querySelectorAll('.js-repeat-row').length > 1) {
        row.remove();
      }
    }
  });
})();
</script>
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
