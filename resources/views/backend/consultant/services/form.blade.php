@extends('backend.layouts.app')
@section('title', $service->exists ? 'Edit Consultation Service' : 'Create Consultation Service')
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="ems-kicker mb-1">Consultant Portal</p><h2 class="admin-title mb-0">{{ $service->exists ? 'Edit Consultation Service' : 'Add Consultation Service' }}</h2></div><a href="{{ route('consultant.services.index') }}" class="btn btn-outline-secondary">Back to Listing</a></div>
  @php
    $visibleErrors = collect($errors->getMessages())->except(['latitude', 'longitude'])->flatten();
    $storedCharges = $service->consultation_charges ?: [];
    if (empty($storedCharges) && $service->price !== null) { $storedCharges = ['hour' => $service->price]; }
    $chargeFields = ['minute' => 'Minute Charges', 'hour' => 'Hour Charges', 'day' => 'Day Charges', 'month' => 'Month Charges', 'contractual' => 'Contractual Charges'];
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
      <div class="col-12"><label class="form-label">Consultation Charges * / Time for Consultation</label><div class="row g-2">@foreach($chargeFields as $chargeKey => $chargeLabel)<div class="col-md-6 col-xl-4"><label class="form-label small text-muted mb-1">{{ $chargeLabel }}</label><input class="form-control consultation-charge-input @error('consultation_charges.'.$chargeKey) is-invalid @enderror" type="number" step="0.01" min="0" name="consultation_charges[{{ $chargeKey }}]" value="{{ old('consultation_charges.'.$chargeKey, data_get($storedCharges, $chargeKey)) }}" placeholder="0.00">@error('consultation_charges.'.$chargeKey)<div id="consultation_charges_{{ $chargeKey }}-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>@endforeach</div><small class="text-muted">Add different charges for minute, hour, day, month, or contractual consultation. Enter at least one charge.</small></div>
      <div class="col-12"><label class="form-label">Charges Detail</label><textarea class="form-control @error('charges_detail') is-invalid @enderror" rows="3" name="charges_detail" placeholder="Describe what each charge includes, contractual pricing terms, taxes, or any special conditions.">{{ old('charges_detail', $service->charges_detail) }}</textarea>@error('charges_detail')<div id="charges_detail-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
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

  $.validator.addMethod('requireCharge', function () {
    return $('.consultation-charge-input').filter(function () { return String($(this).val() || '').trim() !== ''; }).length > 0;
  }, 'Please enter at least one consultation charge.');

  function setSubmitLoading(isLoading) {
    if (isLoading) { originalBtnHtml = $submitBtn.html(); $submitBtn.prop('disabled', true).html('Saving...'); return; }
    $submitBtn.prop('disabled', false).html(originalBtnHtml);
  }

  function applyServerErrors(errors) {
    const validator = $form.data('validator');
    const mapped = {};
    Object.entries(errors || {}).forEach(function ([field, messages]) {
      const normalizedField = field.replace(/\.[0-9]+(?=\.|$)/g, '').replace(/\*$/, '');
      const message = Array.isArray(messages) ? messages[0] : String(messages || 'Invalid value');
      if (hiddenValidationFields.includes(normalizedField)) { mapped.location = 'Please select a location from the suggestions list.'; return; }
      if (normalizedField.startsWith('consultation_charges.')) { mapped[normalizedField.replace('consultation_charges.', 'consultation_charges[') + ']'] = message; return; }
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
      'consultation_charges[minute]': { requireCharge: true, number: true, min: 0 },
      'consultation_charges[hour]': { number: true, min: 0 },
      'consultation_charges[day]': { number: true, min: 0 },
      'consultation_charges[month]': { number: true, min: 0 },
      'consultation_charges[contractual]': { number: true, min: 0 },
      location: { required: true, locationPicked: true, maxlength: 255 },
      accept_terms: { required: true }
    },
    messages: {
      name: { required: 'Please enter the service name.' },
      category_id: { required: 'Please select a category.' },
      consultation_type: { required: 'Please select a consultation type.' },
      business_type: { required: 'Please select a business type.' },
      'consultation_charges[minute]': { requireCharge: 'Please enter at least one consultation charge.' },
      location: { required: 'Please enter a location.' },
      accept_terms: { required: 'Please accept the terms and conditions.' }
    },
    errorElement: 'div',
    errorClass: 'invalid-feedback d-block',
    highlight: function (element) { $(element).addClass('is-invalid').removeClass('is-valid'); },
    unhighlight: function (element) { $(element).removeClass('is-invalid').addClass('is-valid'); },
    errorPlacement: function (error, element) {
      const $container = $(element).closest('.col-12, .col-md-6, .form-check, .col-lg-4, .col-lg-8');
      const $existing = $container.find('.invalid-feedback').not(error).first();
      if ($existing.length) { $existing.text(error.text()).removeClass('d-none'); error.remove(); return; }
      error.insertAfter(element);
    },
    invalidHandler: function () { setSubmitLoading(false); },
    submitHandler: function (form) {
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
