@extends('backend.layouts.app')
@section('title', $service->exists ? 'Edit Consultation Service' : 'Create Consultation Service')
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="ems-kicker mb-1">Consultant Portal</p><h2 class="admin-title mb-0">{{ $service->exists ? 'Edit Consultation Service' : 'Add Consultation Service' }}</h2></div><a href="{{ route('consultant.services.index') }}" class="btn btn-outline-secondary">Back to Listing</a></div>
  @php
    $visibleErrors = collect($errors->getMessages())->except(['latitude', 'longitude'])->flatten();
    $chargeDurationLabels = ['minute' => 'Minutes', 'hour' => 'Hours', 'day' => 'Days', 'month' => 'Months', 'contractual' => 'Contractual'];
    $storedChargeNotes = collect($service->consultation_charge_notes ?: []);
    $storedCharges = collect($service->consultation_charges ?: [])->map(function ($charge, $key) use ($storedChargeNotes) {
        return is_array($charge) ? ['duration' => $charge['duration'] ?? '', 'price' => $charge['price'] ?? '', 'note' => $storedChargeNotes->get($key, $charge['note'] ?? '')] : ['duration' => $key, 'price' => $charge, 'note' => $storedChargeNotes->get($key, '')];
    })->filter(fn($row) => ($row['duration'] ?? '') !== '' || ($row['price'] ?? '') !== '' || ($row['note'] ?? '') !== '')->values()->all();
    $oldCharges = old('charge_duration') ? collect(old('charge_duration'))->map(fn($duration, $idx) => ['duration' => $duration, 'price' => old('charge_price')[$idx] ?? '', 'note' => old('charge_note')[$idx] ?? ''])->values()->all() : $storedCharges;
    if (empty($oldCharges)) { $oldCharges = [['duration' => 'hour', 'price' => $service->price ?? '', 'note' => '']]; }
    $consultationType = old('consultation_type', $service->consultation_type ?: ($service->is_online ? 'online' : 'offline'));
    $businessTypes = ['Architect', 'Lawyer', 'Landscaper', 'Software Consultant', 'Business'];
  @endphp
  @if ($visibleErrors->isNotEmpty())<div class="alert alert-danger"><ul class="mb-0">@foreach ($visibleErrors as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form id="consultant-service-form" data-ajax-create="{{ $service->exists ? '0' : '1' }}" method="POST" enctype="multipart/form-data" action="{{ $service->exists ? route('consultant.services.update', $service) : route('consultant.services.store') }}" class="row g-3">@csrf @if($service->exists) @method('PUT') @endif
    <div class="col-lg-8"><div class="chart-card p-4"><h5 class="mb-3">Service Information</h5><div class="row g-3">
      <div class="col-12"><label class="form-label">Consultation Service Name *</label><input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $service->name) }}" required>@error('name')<div id="name-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Category *</label><select id="category_id" class="form-select @error('category_id') is-invalid @enderror" name="category_id" required><option value="">Select category</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('category_id', $service->category_id)==$cat->id)>{{ $cat->name }}</option>@endforeach</select>@error('category_id')<div id="category_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Subcategory</label><select id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" name="subcategory_id" data-current="{{ old('subcategory_id', $service->subcategory_id) }}"><option value="">Select subcategory</option></select>@error('subcategory_id')<div id="subcategory_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Consultation Type *</label><select class="form-select @error('consultation_type') is-invalid @enderror" name="consultation_type" required><option value="">Select type</option><option value="online" @selected($consultationType === 'online')>Online</option><option value="offline" @selected($consultationType === 'offline')>Offline</option><option value="both" @selected($consultationType === 'both')>Both</option></select>@error('consultation_type')<div id="consultation_type-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Business Type *</label><select class="form-select @error('business_type') is-invalid @enderror" name="business_type" required><option value="">Select business type</option>@foreach($businessTypes as $type)<option value="{{ $type }}" @selected(old('business_type', $service->business_type) === $type)>{{ $type }}</option>@endforeach</select>@error('business_type')<div id="business_type-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-12"><div class="chart-card p-4" style="background:#f1f5ff;border:1px solid #c9d8ff;"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0 text-primary"><i class="fa-solid fa-clock me-2"></i>Consultation Charges * / Time for Consultation</h5><button type="button" class="btn btn-primary btn-sm" id="add-charge-tier">+ Add Duration</button></div><div id="charges-wrap" class="row g-2">@foreach($oldCharges as $charge)<div class="col-12 charge-row"><div class="row g-2 align-items-end"><div class="col-md-5"><label class="form-label">Duration</label><select class="form-select charge-duration-select @error('charge_duration.*') is-invalid @enderror" name="charge_duration[]"><option value="">Select duration</option>@foreach($chargeDurationLabels as $durationKey => $durationLabel)<option value="{{ $durationKey }}" @selected(($charge['duration'] ?? '') === $durationKey)>{{ $durationLabel }}</option>@endforeach</select></div><div class="col-md-5"><label class="form-label">Price ₹</label><input type="number" step="0.01" min="0" class="form-control charge-price-input @error('charge_price.*') is-invalid @enderror" name="charge_price[]" value="{{ $charge['price'] ?? '' }}" placeholder="Price"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-charge-tier">Remove</button></div><div class="col-12"><label class="form-label small text-muted mt-2 mb-1">Note (optional)</label><textarea class="form-control @error('charge_note.*') is-invalid @enderror" name="charge_note[]" rows="2" placeholder="Optional note for this price">{{ $charge['note'] ?? '' }}</textarea></div></div></div>@endforeach</div><div id="charge-row-error" class="invalid-feedback d-block @if(!$errors->has('charge_duration.0') && !$errors->has('charge_price.*') && !$errors->has('charge_note.*')) d-none @endif">{{ $errors->first('charge_duration.0') ?: $errors->first('charge_price.*') ?: $errors->first('charge_note.*') }}</div><small class="text-primary d-block mt-2">Example: Select Hours @ ₹500, Months @ ₹15000, or Contractual @ ₹50000. Use + Add Duration for more pricing rows.</small></div></div>
      <div class="col-12"><label class="form-label">Short / Brief Description</label><input class="form-control @error('short_description') is-invalid @enderror" name="short_description" maxlength="500" value="{{ old('short_description', $service->short_description) }}" placeholder="Brief description">@error('short_description')<div id="short_description-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-12"><label class="form-label">Detailed Description</label><textarea class="form-control @error('description') is-invalid @enderror" rows="5" name="description" placeholder="Detailed description">{{ old('description', $service->description) }}</textarea>@error('description')<div id="description-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-12"><label class="form-label">Geographical Service Area</label><textarea class="form-control @error('service_area') is-invalid @enderror" rows="3" name="service_area" placeholder="Example: Dehradun, Mussoorie, Haridwar">{{ old('service_area', $service->service_area) }}</textarea>@error('service_area')<div id="service_area-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Enter offline service cities or areas separated by commas.</small></div>
    </div></div></div>
    <div class="col-lg-4"><div class="chart-card p-4"><h5 class="mb-3">Media & Location</h5>
      <label class="form-label">Consultant Image / Service Image</label><input class="form-control @error('image') is-invalid @enderror" type="file" name="image" accept="image/*">@error('image')<div id="image-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Upload one image only. Max 4 MB.</small>
      @if($service->image_path)<div class="d-flex flex-wrap gap-2 mt-2"><img src="{{ asset($service->image_path) }}" alt="{{ $service->name }}" style="width:70px;height:70px;object-fit:cover;border-radius:8px;"></div>@endif
      <label class="form-label mt-3">Consultant Location *</label><input id="location" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ old('location', $service->location) }}" placeholder="Search location in India" autocomplete="off">@error('location')<div id="location-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Select a Google Places suggestion to save latitude and longitude.</small>
      <input id="latitude" type="hidden" name="latitude" value="{{ old('latitude', $service->latitude) }}">
      <input id="longitude" type="hidden" name="longitude" value="{{ old('longitude', $service->longitude) }}">
      @unless($service->exists)<div class="form-check mt-3"><input class="form-check-input @error('accept_terms') is-invalid @enderror" type="checkbox" value="1" id="accept_terms" name="accept_terms" required><label class="form-check-label" for="accept_terms">I accept the <a href="{{ route('frontend.terms.show', ['moduleKey' => 'consultants']) }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-underline" style="color:#0d6efd;">Terms & Conditions</a>.</label>@error('accept_terms')<div id="accept_terms-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>@endunless
      <button type="submit" id="consultantServiceSubmitBtn" class="btn btn-primary ems-btn-primary w-100 mt-4">{{ $service->exists ? 'Update & Resubmit' : 'Submit for Approval' }}</button>
    </div></div>
  </form>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script>
(function initToastr() {
  if (!window.jQuery) return;
  const configureToastr = function () {
    if (!window.toastr) return;
    window.toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 4000, extendedTimeOut: 2000 };
  };
  if (window.toastr) { configureToastr(); return; }
  const toastrScript = document.createElement('script');
  toastrScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js';
  toastrScript.onload = configureToastr;
  document.head.appendChild(toastrScript);
})();

const categories = @json($categories->mapWithKeys(fn($cat) => [$cat->id => $cat->children->map(fn($child) => ['id' => $child->id, 'name' => $child->name])->values()])->toArray());
function fillSubcategories() {
  const category = document.getElementById('category_id');
  const subcategory = document.getElementById('subcategory_id');
  const current = subcategory.dataset.current;
  subcategory.innerHTML = '<option value="">Select subcategory</option>';
  (categories[category.value] || []).forEach(function (item) {
    const option = document.createElement('option'); option.value = item.id; option.textContent = item.name; option.selected = String(item.id) === String(current); subcategory.appendChild(option);
  });
}
document.getElementById('category_id')?.addEventListener('change', function () { document.getElementById('subcategory_id').dataset.current = ''; fillSubcategories(); if (window.jQuery) jQuery('#category_id').valid(); });
fillSubcategories();

const chargeDurationOptions = @json($chargeDurationLabels);
const chargesWrap = document.getElementById('charges-wrap');
document.getElementById('add-charge-tier')?.addEventListener('click', function () {
  const row = document.createElement('div');
  row.className = 'col-12 charge-row';
  const options = Object.entries(chargeDurationOptions).map(function ([value, label]) { return '<option value="' + value + '">' + label + '</option>'; }).join('');
  row.innerHTML = '<div class="row g-2 align-items-end"><div class="col-md-5"><label class="form-label">Duration</label><select class="form-select charge-duration-select" name="charge_duration[]"><option value="">Select duration</option>' + options + '</select></div><div class="col-md-5"><label class="form-label">Price ₹</label><input type="number" step="0.01" min="0" class="form-control charge-price-input" name="charge_price[]" placeholder="Price"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-charge-tier">Remove</button></div><div class="col-12"><label class="form-label small text-muted mt-2 mb-1">Note (optional)</label><textarea class="form-control" name="charge_note[]" rows="2" placeholder="Optional note for this price"></textarea></div></div>';
  chargesWrap?.appendChild(row);
});
chargesWrap?.addEventListener('click', function (event) { if (event.target.classList.contains('remove-charge-tier')) event.target.closest('.charge-row')?.remove(); });

window.initConsultantServiceLocationAutocomplete = function () {
  const locationInput = document.getElementById('location');
  const latitudeInput = document.getElementById('latitude');
  const longitudeInput = document.getElementById('longitude');
  if (!locationInput || !window.google || !google.maps || !google.maps.places) return;

  let selectedPlaceId = '';
  const autocomplete = new google.maps.places.Autocomplete(locationInput, {
    fields: ['formatted_address', 'geometry', 'address_components', 'place_id'],
    componentRestrictions: { country: 'in' }
  });

  autocomplete.addListener('place_changed', function () {
    const place = autocomplete.getPlace();
    if (!place?.geometry?.location) {
      latitudeInput.value = '';
      longitudeInput.value = '';
      selectedPlaceId = '';
      return;
    }
    selectedPlaceId = place.place_id || '';
    locationInput.value = place.formatted_address || locationInput.value;
    latitudeInput.value = place.geometry.location.lat().toFixed(7);
    longitudeInput.value = place.geometry.location.lng().toFixed(7);
    if (window.jQuery) jQuery(locationInput).valid();
  });

  locationInput.addEventListener('input', function () {
    if (selectedPlaceId) selectedPlaceId = '';
    latitudeInput.value = '';
    longitudeInput.value = '';
    if (window.jQuery) jQuery(locationInput).valid();
  });
};

function notify(type, msg) {
  const toastType = type === 'error' ? 'error' : 'success';
  if (window.toastr && typeof window.toastr[toastType] === 'function') { window.toastr[toastType](msg); return; }
  alert(msg);
}

$(function () {
  const $form = $('#consultant-service-form');
  if (!$form.length || String($form.data('ajax-create')) !== '1') return;

  const hiddenValidationFields = ['latitude', 'longitude'];
  const $submitBtn = $('#consultantServiceSubmitBtn');
  let originalBtnHtml = $submitBtn.html();

  $.validator.addMethod('locationPicked', function () {
    return String($('#latitude').val() || '').trim() !== '' && String($('#longitude').val() || '').trim() !== '';
  }, 'Please select a location from the suggestions list.');

  function setSubmitLoading(isLoading) {
    if (isLoading) { originalBtnHtml = $submitBtn.html(); $submitBtn.prop('disabled', true).html('Saving...'); return; }
    $submitBtn.prop('disabled', false).html(originalBtnHtml);
  }

  function chargeRows() {
    return $('.charge-row').map(function () {
      return {
        row: $(this),
        duration: String($(this).find('.charge-duration-select').val() || '').trim(),
        price: String($(this).find('.charge-price-input').val() || '').trim()
      };
    }).get();
  }

  function showChargeError(message) {
    $('#charge-row-error').text(message).removeClass('d-none');
    $('#charges-wrap').find('.charge-duration-select, .charge-price-input').removeClass('is-valid').addClass('is-invalid');
  }

  function clearChargeError() {
    $('#charge-row-error').addClass('d-none').text('');
    $('#charges-wrap').find('.charge-duration-select, .charge-price-input').removeClass('is-invalid');
  }

  function validateChargeRows(showErrors) {
    const rows = chargeRows();
    const hasCompleteRow = rows.some(function (item) { return item.duration !== '' && item.price !== ''; });
    const hasIncompleteRow = rows.some(function (item) { return (item.duration !== '' && item.price === '') || (item.duration === '' && item.price !== ''); });
    if (!hasCompleteRow) {
      if (showErrors) showChargeError('Please add at least one consultation duration and price.');
      return false;
    }
    if (hasIncompleteRow) {
      if (showErrors) showChargeError('Each consultation charge row must include both duration and price.');
      return false;
    }
    clearChargeError();
    return true;
  }

  $('#charges-wrap').on('input change', '.charge-duration-select, .charge-price-input', function () {
    if ($('#charge-row-error').is(':visible')) validateChargeRows(false);
  });

  function applyServerErrors(errors) {
    const validator = $form.data('validator');
    const mapped = {};
    Object.entries(errors || {}).forEach(function ([field, messages]) {
      const normalizedField = field.replace(/\.[0-9]+(?=\.|$)/g, '').replace(/\*$/, '');
      const message = Array.isArray(messages) ? messages[0] : String(messages || 'Invalid value');
      if (hiddenValidationFields.includes(normalizedField)) { mapped.location = 'Please select a location from the suggestions list.'; return; }
      if (normalizedField.startsWith('charge_duration') || normalizedField.startsWith('charge_price') || normalizedField.startsWith('charge_note')) { showChargeError(message); return; }
      mapped[normalizedField] = message;
    });
    if (validator && Object.keys(mapped).length) validator.showErrors(mapped);
  }

  $form.validate({
    ignore: [],
    rules: {
      name: { required: true, maxlength: 255 },
      category_id: { required: true },
      consultation_type: { required: true },
      business_type: { required: true },
      location: { required: true, locationPicked: true, maxlength: 255 },
      accept_terms: { required: true }
    },
    messages: {
      name: { required: 'Please enter the service name.' },
      category_id: { required: 'Please select a category.' },
      consultation_type: { required: 'Please select a consultation type.' },
      business_type: { required: 'Please select a business type.' },
      location: { required: 'Please enter a location.' },
      accept_terms: { required: 'Please accept the terms and conditions.' }
    },
    errorElement: 'div',
    errorClass: 'invalid-feedback d-block',
    highlight: function (element) { $(element).addClass('is-invalid').removeClass('is-valid'); },
    unhighlight: function (element) {
      const $element = $(element);
      $element.removeClass('is-invalid');
      if ($element.closest('#charges-wrap').length) return;
      $element.addClass('is-valid');
    },
    errorPlacement: function (error, element) {
      const $container = $(element).closest('.col-12, .col-md-6, .form-check, .col-lg-4, .col-lg-8');
      const $existing = $container.find('.invalid-feedback').not(error).first();
      if ($existing.length) { $existing.text(error.text()).removeClass('d-none'); error.remove(); return; }
      error.insertAfter(element);
    },
    invalidHandler: function () { setSubmitLoading(false); },
    submitHandler: function (form) {
      if (!validateChargeRows(true)) { setSubmitLoading(false); return false; }
      setSubmitLoading(true);
      fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: new FormData(form) })
        .then(async function (res) {
          const payload = await res.json();
          if (!res.ok) {
            applyServerErrors(payload.errors || {});
            notify('error', res.status === 422 ? 'Please fix the highlighted fields and try again.' : (payload.message || 'Unable to save consultation service.'));
            return;
          }
          notify('success', payload.message || 'Consultation service submitted successfully.');
          setTimeout(function () { window.location.href = payload.redirect || '{{ route('consultant.services.index') }}'; }, 800);
        })
        .catch(function () { notify('error', 'Network error while saving consultation service.'); })
        .finally(function () { setSubmitLoading(false); });
    }
  });
});
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initConsultantServiceLocationAutocomplete"></script>
@endpush
