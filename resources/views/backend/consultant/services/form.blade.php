@extends('backend.layouts.app')
@section('title', $service->exists ? 'Edit Consultation Service' : 'Create Consultation Service')
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="ems-kicker mb-1">Consultant Portal</p><h2 class="admin-title mb-0">{{ $service->exists ? 'Edit Consultation Service' : 'Add Consultation Service' }}</h2></div><a href="{{ route('consultant.services.index') }}" class="btn btn-outline-secondary">Back to Listing</a></div>
  @php($visibleErrors = collect($errors->getMessages())->except(['latitude', 'longitude'])->flatten())
  @if ($visibleErrors->isNotEmpty())<div class="alert alert-danger"><ul class="mb-0">@foreach ($visibleErrors as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form id="consultant-service-form" data-ajax-create="{{ $service->exists ? '0' : '1' }}" method="POST" enctype="multipart/form-data" action="{{ $service->exists ? route('consultant.services.update', $service) : route('consultant.services.store') }}" class="row g-3">@csrf @if($service->exists) @method('PUT') @endif
    <div class="col-lg-8"><div class="chart-card p-4"><h5 class="mb-3">Service Information</h5><div class="row g-3">
      <div class="col-12"><label class="form-label">Service Name *</label><input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $service->name) }}" required>@error('name')<div id="name-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Category *</label><select id="category_id" class="form-select @error('category_id') is-invalid @enderror" name="category_id" required><option value="">Select category</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('category_id', $service->category_id)==$cat->id)>{{ $cat->name }}</option>@endforeach</select>@error('category_id')<div id="category_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Subcategory</label><select id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" name="subcategory_id" data-current="{{ old('subcategory_id', $service->subcategory_id) }}"><option value="">Select subcategory</option></select>@error('subcategory_id')<div id="subcategory_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Price *</label><input class="form-control @error('price') is-invalid @enderror" type="number" step="0.01" min="0" name="price" value="{{ old('price', $service->price ?? 0) }}" required>@error('price')<div id="price-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Duration</label><input class="form-control @error('duration') is-invalid @enderror" name="duration" placeholder="30 minutes / 1 hour" value="{{ old('duration', $service->duration) }}">@error('duration')<div id="duration-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-12"><label class="form-label">Short Description</label><input class="form-control @error('short_description') is-invalid @enderror" name="short_description" maxlength="500" value="{{ old('short_description', $service->short_description) }}">@error('short_description')<div id="short_description-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-12"><label class="form-label">Full Description</label><textarea class="form-control @error('description') is-invalid @enderror" rows="5" name="description">{{ old('description', $service->description) }}</textarea>@error('description')<div id="description-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
    </div></div></div>
    <div class="col-lg-4"><div class="chart-card p-4"><h5 class="mb-3">Media & Availability</h5>
      <label class="form-label">Service Image</label><input class="form-control @error('image') is-invalid @enderror" type="file" name="image" accept="image/*">@error('image')<div id="image-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Upload one image only. Max 4 MB.</small>
      @if($service->image_path)<div class="d-flex flex-wrap gap-2 mt-2"><img src="{{ asset($service->image_path) }}" alt="{{ $service->name }}" style="width:70px;height:70px;object-fit:cover;border-radius:8px;"></div>@endif
      <label class="form-label mt-3">Location *</label><input id="location" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ old('location', $service->location) }}" placeholder="Search location in India" autocomplete="off">@error('location')<div id="location-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Select a location suggestion from Google Places.</small>
      <input id="latitude" type="hidden" name="latitude" value="{{ old('latitude', $service->latitude) }}">
      <input id="longitude" type="hidden" name="longitude" value="{{ old('longitude', $service->longitude) }}">
      <div class="form-check mt-3"><input class="form-check-input" type="checkbox" value="1" id="is_online" name="is_online" @checked(old('is_online', $service->is_online))><label class="form-check-label" for="is_online">Available for online consultation</label></div>
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
      mapped[normalizedField] = message;
    });
    if (validator && Object.keys(mapped).length) validator.showErrors(mapped);
  }

  $form.validate({
    ignore: [],
    rules: {
      name: { required: true, maxlength: 255 },
      category_id: { required: true },
      price: { required: true, number: true, min: 0 },
      location: { required: true, locationPicked: true, maxlength: 255 },
      accept_terms: { required: true }
    },
    messages: {
      name: { required: 'Please enter the service name.' },
      category_id: { required: 'Please select a category.' },
      price: { required: 'Please enter the price.' },
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
