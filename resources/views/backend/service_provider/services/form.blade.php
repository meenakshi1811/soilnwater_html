@extends('backend.layouts.app')
@section('title', $service->exists ? 'Edit Service' : 'Create Service')
@section('content')
@php
  $isAdmin = $isAdmin ?? false;
  $categories = $categories ?? collect();
  $serviceProviders = $serviceProviders ?? collect();
  $visibleErrors = collect(($errors ?? new \Illuminate\Support\ViewErrorBag)->getMessages())->except(['latitude', 'longitude'])->flatten();
  $chargeDurationLabels = ['minute' => 'Minutes', 'hour' => 'Hours', 'day' => 'Days', 'month' => 'Months', 'contractual' => 'Contractual'];
  $storedCharges = collect($service->consultation_charges ?: [])->map(function ($charge, $key) {
      return is_array($charge) ? ['duration' => $charge['duration'] ?? '', 'price' => $charge['price'] ?? ''] : ['duration' => $key, 'price' => $charge];
  })->filter(fn ($row) => ($row['duration'] ?? '') !== '' || ($row['price'] ?? '') !== '')->values()->all();
  $oldCharges = old('charge_duration')
      ? collect(old('charge_duration'))->map(fn ($duration, $idx) => ['duration' => $duration, 'price' => old('charge_price')[$idx] ?? ''])->values()->all()
      : $storedCharges;
  if (empty($oldCharges)) {
      $oldCharges = [['duration' => 'hour', 'price' => $service->price ?? '']];
  }
  $consultationType = old('consultation_type', $service->consultation_type ?: ($service->is_online ? 'online' : 'offline'));
  $businessTypes = ['Freelancer', 'Proprietorship Firm', 'Partnership Firm', 'Private Limited Company', 'Society', 'NGO'];
  $existingImagePath = old('remove_image') ? null : ($service->image_path ?: null);
@endphp
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="ems-kicker mb-1">{{ $isAdmin ? 'Admin Portal' : 'Service Portal' }}</p><h2 class="admin-title mb-0">{{ $service->exists ? 'Edit Service' : ($isAdmin ? 'Create Service for Service Provider' : 'Add Service') }}</h2></div><a href="{{ $isAdmin ? route('admin.service-provider-services.all.index') : route('service_provider.services.index') }}" class="btn btn-outline-secondary">Back to Listing</a></div>
  @if ($visibleErrors->isNotEmpty())<div class="alert alert-danger"><ul class="mb-0">@foreach ($visibleErrors as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form id="service_provider-service-form"
    data-ajax-submit="1"
    data-is-edit="{{ $service->exists ? '1' : '0' }}"
    data-existing-image="{{ $existingImagePath ? asset($existingImagePath) : '' }}"
    data-existing-image-path="{{ $existingImagePath ?? '' }}"
    method="POST"
    enctype="multipart/form-data"
    action="{{ $service->exists ? route('service_provider.services.update', $service) : ($isAdmin ? route('admin.service-provider-services.store') : route('service_provider.services.store')) }}"
    class="row g-3">
    @csrf
    @if($service->exists) @method('PUT') @endif
    <div class="col-lg-8"><div class="chart-card p-4"><h5 class="mb-3">Service Information</h5><div class="row g-3">
      @if($isAdmin)
      <div class="col-12"><label class="form-label">Service Provider *</label><select class="form-select @error('service_provider_id') is-invalid @enderror" name="service_provider_id" required><option value="">Select service provider</option>@foreach($serviceProviders as $serviceProvider)<option value="{{ $serviceProvider->id }}" @selected(old('service_provider_id') == $serviceProvider->id)>{{ $serviceProvider->display_name ?: $serviceProvider->company_name }}</option>@endforeach</select>@error('service_provider_id')<div id="service_provider_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      @endif
      <div class="col-12"><label class="form-label">Service Name *</label><input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $service->name) }}" required>@error('name')<div id="name-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Service Category *</label><select id="category_id" class="form-select @error('category_id') is-invalid @enderror" name="category_id" required><option value="">Select category</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('category_id', $service->category_id)==$cat->id)>{{ $cat->name }}</option>@endforeach</select>@error('category_id')<div id="category_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Sub Services</label><select id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" name="subcategory_id" data-current="{{ old('subcategory_id', $service->subcategory_id) }}"><option value="">Select sub service</option></select>@error('subcategory_id')<div id="subcategory_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Service Type *</label><select class="form-select @error('consultation_type') is-invalid @enderror" name="consultation_type" required><option value="">Select type</option><option value="online" @selected($consultationType === 'online')>Online</option><option value="offline" @selected($consultationType === 'offline')>Offline</option><option value="both" @selected($consultationType === 'both')>Both</option></select>@error('consultation_type')<div id="consultation_type-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Business Type *</label><select class="form-select @error('business_type') is-invalid @enderror" name="business_type" required><option value="">Select business type</option>@foreach($businessTypes as $type)<option value="{{ $type }}" @selected(old('business_type', $service->business_type) === $type)>{{ $type }}</option>@endforeach</select>@error('business_type')<div id="business_type-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-12"><div class="chart-card p-4" style="background:#f1f5ff;border:1px solid #c9d8ff;"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0 text-primary"><i class="fa-solid fa-clock me-2"></i>Charges * / Service Time</h5><button type="button" class="btn btn-primary btn-sm" id="add-charge-tier">+ Add Duration</button></div><div id="charges-wrap" class="row g-2">@foreach($oldCharges as $charge)<div class="col-12 charge-row"><div class="row g-2 align-items-end"><div class="col-md-5"><label class="form-label">Duration</label><select class="form-select charge-duration-select @error('charge_duration.*') is-invalid @enderror" name="charge_duration[]"><option value="">Select duration</option>@foreach($chargeDurationLabels as $durationKey => $durationLabel)<option value="{{ $durationKey }}" @selected(($charge['duration'] ?? '') === $durationKey)>{{ $durationLabel }}</option>@endforeach</select></div><div class="col-md-5"><label class="form-label">Price ₹</label><input type="number" step="0.01" min="0" class="form-control charge-price-input @error('charge_price.*') is-invalid @enderror" name="charge_price[]" value="{{ $charge['price'] ?? '' }}" placeholder="Price"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-charge-tier">Remove</button></div></div></div>@endforeach</div><div id="charge-row-error" class="invalid-feedback d-block @if(!$errors->has('charge_duration.0') && !$errors->has('charge_price.*')) d-none @endif">{{ $errors->first('charge_duration.0') ?: $errors->first('charge_price.*') }}</div><small class="text-primary d-block mt-2">Example: Select Hours @ ₹500, Months @ ₹15000, or Contractual @ ₹50000. Use + Add Duration for more pricing rows.</small></div></div>
      <div class="col-12"><label class="form-label">Short / Brief Description</label><input class="form-control @error('short_description') is-invalid @enderror" name="short_description" maxlength="500" value="{{ old('short_description', $service->short_description) }}" placeholder="Brief description">@error('short_description')<div id="short_description-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-12"><label class="form-label">Description</label><textarea class="form-control @error('description') is-invalid @enderror" rows="5" name="description" placeholder="Description">{{ old('description', $service->description) }}</textarea>@error('description')<div id="description-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-12"><label class="form-label">Service Area</label><textarea class="form-control @error('service_area') is-invalid @enderror" rows="3" name="service_area" placeholder="Example: Dehradun, Mussoorie, Haridwar">{{ old('service_area', $service->service_area) }}</textarea>@error('service_area')<div id="service_area-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Enter offline service cities or areas separated by commas.</small></div>
    </div></div></div>
    <div class="col-lg-4"><div class="chart-card p-4"><h5 class="mb-3">Media & Location</h5>
      <div class="vendor-media-block">
        <label class="form-label">Service Image</label>
        <label class="vendor-media-dropzone" for="serviceImageInput" id="imageDropzone">
          <input class="vendor-media-input @error('image') is-invalid @enderror" type="file" id="serviceImageInput" name="image" accept="image/*">
          <div class="vendor-media-dropzone__inner">
            <span class="vendor-media-dropzone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
            <strong>Upload service image</strong>
            <span>Click to browse or drop a file here</span>
            <small>One image only · Max 4 MB</small>
          </div>
        </label>
        @error('image')<div id="image-error" class="invalid-feedback d-block">{{ $message }}</div>@else<div id="image-error" class="invalid-feedback d-none"></div>@enderror
        <input type="hidden" name="remove_image" id="removeImageFlag" value="{{ old('remove_image') ? '1' : '0' }}">
        <div id="imagePreviewGrid" class="vendor-media-preview-grid"></div>
      </div>

      <label class="form-label mt-3">Address *</label><input id="location" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ old('location', $service->location) }}" placeholder="Search address in India" autocomplete="off">@error('location')<div id="location-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Select a Google Places suggestion to save the service address.</small>
      <div class="row g-2 mt-1"><div class="col-md-6"><label class="form-label">Postal Code</label><input class="form-control @error('postal_code') is-invalid @enderror" name="postal_code" maxlength="20" value="{{ old('postal_code', $service->postal_code) }}" placeholder="Postal code">@error('postal_code')<div id="postal_code-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label">City</label><input class="form-control @error('city') is-invalid @enderror" name="city" maxlength="120" value="{{ old('city', $service->city) }}" placeholder="City">@error('city')<div id="city-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div></div>
      <label class="form-label mt-3">Service Radius</label><div class="input-group"><input type="number" min="0" step="1" class="form-control @error('service_radius') is-invalid @enderror" name="service_radius" value="{{ old('service_radius', $service->service_radius) }}" placeholder="Service radius"><span class="input-group-text">km</span></div>@error('service_radius')<div id="service_radius-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror
      <label class="form-label mt-3">Working Hours</label><textarea class="form-control @error('working_hours') is-invalid @enderror" rows="3" name="working_hours" placeholder="Example: Mon-Fri, 9:00 AM - 6:00 PM">{{ old('working_hours', $service->working_hours) }}</textarea>@error('working_hours')<div id="working_hours-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror
      <input id="latitude" type="hidden" name="latitude" value="{{ old('latitude', $service->latitude) }}">
      <input id="longitude" type="hidden" name="longitude" value="{{ old('longitude', $service->longitude) }}">
      @unless($isAdmin || $service->exists)<div class="form-check mt-3"><input class="form-check-input @error('accept_terms') is-invalid @enderror" type="checkbox" value="1" id="accept_terms" name="accept_terms" required><label class="form-check-label" for="accept_terms">I accept the <a href="{{ route('frontend.terms.show', ['moduleKey' => 'service_providers']) }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-underline" style="color:#0d6efd;">Terms and Condition</a>.</label>@error('accept_terms')<div id="accept_terms-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>@endunless
      <button type="submit" id="service_providerServiceSubmitBtn" class="btn btn-primary ems-btn-primary w-100 mt-4">{{ $service->exists ? 'Update & Resubmit' : ($isAdmin ? 'Create Service' : 'Submit for Approval') }}</button>
    </div></div>
  </form>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
.vendor-media-block { margin-bottom: 0.25rem; }
.vendor-media-dropzone {
  display: block;
  border: 1.5px dashed #cbd5e1;
  border-radius: 16px;
  background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
  padding: 1.1rem;
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  margin-bottom: 0.75rem;
}
.vendor-media-dropzone:hover,
.vendor-media-dropzone.is-dragover {
  border-color: #2563eb;
  background: #f8fbff;
  box-shadow: 0 8px 22px rgba(37, 99, 235, 0.08);
}
.vendor-media-input {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}
.vendor-media-dropzone__inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.25rem;
  color: #475569;
}
.vendor-media-dropzone__inner strong {
  color: #0f172a;
  font-size: 0.92rem;
}
.vendor-media-dropzone__inner span { font-size: 0.82rem; }
.vendor-media-dropzone__inner small { color: #94a3b8; }
.vendor-media-dropzone__icon {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(37, 99, 235, 0.1);
  color: #2563eb;
  font-size: 1.1rem;
  margin-bottom: 0.35rem;
}
.vendor-media-dropzone.has-files { padding: 0.75rem; }
.vendor-media-dropzone.has-files .vendor-media-dropzone__inner strong { font-size: 0.84rem; }
.vendor-media-preview-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: 0.75rem;
}
.vendor-media-preview-card {
  position: relative;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
}
.vendor-media-preview-card img {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: cover;
  display: block;
}
.vendor-media-preview-card.is-existing { border-color: #bfdbfe; }
.vendor-media-preview-card__badge {
  position: absolute;
  top: 0.45rem;
  left: 0.45rem;
  padding: 0.15rem 0.45rem;
  border-radius: 999px;
  background: rgba(37, 99, 235, 0.92);
  color: #fff;
  font-size: 0.65rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}
.vendor-media-preview-card__name {
  display: block;
  padding: 0.45rem 0.55rem;
  font-size: 0.72rem;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.vendor-media-preview-remove {
  position: absolute;
  top: 0.45rem;
  right: 0.45rem;
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.72);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s ease;
}
.vendor-media-preview-remove:hover { background: #dc2626; }
#service_providerServiceSubmitBtn.is-loading {
  pointer-events: none;
  opacity: 0.85;
}
</style>
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
toastr.options = {
  closeButton: true,
  progressBar: true,
  positionClass: 'toast-top-right',
  timeOut: 4000,
  extendedTimeOut: 2000
};

function notify(type, msg) {
  const toastType = type === 'error' ? 'error' : 'success';
  if (window.toastr && typeof window.toastr[toastType] === 'function') {
    window.toastr[toastType](msg);
    return;
  }
  alert(msg);
}

const categories = @json($categories->mapWithKeys(fn($cat) => [$cat->id => $cat->children->map(fn($child) => ['id' => $child->id, 'name' => $child->name])->values()])->toArray());
function fillSubcategories() {
  const category = document.getElementById('category_id');
  const subcategory = document.getElementById('subcategory_id');
  const current = subcategory.dataset.current;
  subcategory.innerHTML = '<option value="">Select sub service</option>';
  (categories[category.value] || []).forEach(function (item) {
    const option = document.createElement('option');
    option.value = item.id;
    option.textContent = item.name;
    option.selected = String(item.id) === String(current);
    subcategory.appendChild(option);
  });
}
document.getElementById('category_id')?.addEventListener('change', function () {
  document.getElementById('subcategory_id').dataset.current = '';
  fillSubcategories();
  if (window.jQuery) jQuery('#category_id').valid();
});
fillSubcategories();

const chargeDurationOptions = @json($chargeDurationLabels);
const chargesWrap = document.getElementById('charges-wrap');
document.getElementById('add-charge-tier')?.addEventListener('click', function () {
  const row = document.createElement('div');
  row.className = 'col-12 charge-row';
  const options = Object.entries(chargeDurationOptions).map(function ([value, label]) {
    return '<option value="' + value + '">' + label + '</option>';
  }).join('');
  row.innerHTML = '<div class="row g-2 align-items-end"><div class="col-md-5"><label class="form-label">Duration</label><select class="form-select charge-duration-select" name="charge_duration[]"><option value="">Select duration</option>' + options + '</select></div><div class="col-md-5"><label class="form-label">Price ₹</label><input type="number" step="0.01" min="0" class="form-control charge-price-input" name="charge_price[]" placeholder="Price"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-charge-tier">Remove</button></div></div>';
  chargesWrap?.appendChild(row);
});
chargesWrap?.addEventListener('click', function (event) {
  if (event.target.classList.contains('remove-charge-tier')) {
    event.target.closest('.charge-row')?.remove();
  }
});

(function initServiceProviderServiceMedia() {
  const form = document.getElementById('service_provider-service-form');
  const imageInput = document.getElementById('serviceImageInput');
  const imageGrid = document.getElementById('imagePreviewGrid');
  const imageDropzone = document.getElementById('imageDropzone');
  const removeImageFlag = document.getElementById('removeImageFlag');
  if (!form || !imageInput || !imageGrid) return;

  let selectedFile = null;
  let objectUrl = '';
  let existingImage = { url: '', path: '' };
  let existingRemoved = removeImageFlag?.value === '1';

  if (form.dataset.existingImage) {
    existingImage = {
      url: form.dataset.existingImage,
      path: form.dataset.existingImagePath || ''
    };
  }

  function basename(path) {
    return String(path || '').split('/').pop() || 'image';
  }

  function syncInput() {
    const dt = new DataTransfer();
    if (selectedFile) dt.items.add(selectedFile);
    imageInput.files = dt.files;
  }

  function renderImage() {
    if (objectUrl) {
      URL.revokeObjectURL(objectUrl);
      objectUrl = '';
    }
    imageGrid.innerHTML = '';

    if (selectedFile) {
      objectUrl = URL.createObjectURL(selectedFile);
      const card = document.createElement('div');
      card.className = 'vendor-media-preview-card';
      const img = document.createElement('img');
      img.alt = selectedFile.name;
      img.src = objectUrl;
      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'vendor-media-preview-remove';
      removeBtn.setAttribute('data-remove', 'new');
      removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
      const name = document.createElement('span');
      name.className = 'vendor-media-preview-card__name';
      name.textContent = selectedFile.name;
      card.appendChild(img);
      card.appendChild(removeBtn);
      card.appendChild(name);
      imageGrid.appendChild(card);
      imageDropzone?.classList.add('has-files');
      const title = imageDropzone?.querySelector('.vendor-media-dropzone__inner strong');
      if (title) title.textContent = 'Replace service image';
      return;
    }

    if (existingImage.url && !existingRemoved) {
      const card = document.createElement('div');
      card.className = 'vendor-media-preview-card is-existing';
      const badge = document.createElement('span');
      badge.className = 'vendor-media-preview-card__badge';
      badge.textContent = 'Saved';
      const img = document.createElement('img');
      img.alt = basename(existingImage.path);
      img.src = existingImage.url;
      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'vendor-media-preview-remove';
      removeBtn.setAttribute('data-remove', 'existing');
      removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
      const name = document.createElement('span');
      name.className = 'vendor-media-preview-card__name';
      name.textContent = basename(existingImage.path);
      card.appendChild(badge);
      card.appendChild(img);
      card.appendChild(removeBtn);
      card.appendChild(name);
      imageGrid.appendChild(card);
      imageDropzone?.classList.add('has-files');
      const title = imageDropzone?.querySelector('.vendor-media-dropzone__inner strong');
      if (title) title.textContent = 'Replace service image';
      return;
    }

    imageDropzone?.classList.remove('has-files');
    const title = imageDropzone?.querySelector('.vendor-media-dropzone__inner strong');
    if (title) title.textContent = 'Upload service image';
  }

  function setImage(file) {
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      notify('error', 'Please choose a valid image file.');
      return;
    }
    if (file.size > 4 * 1024 * 1024) {
      notify('error', 'Image must be 4 MB or smaller.');
      return;
    }
    selectedFile = file;
    existingRemoved = false;
    if (removeImageFlag) removeImageFlag.value = '0';
    syncInput();
    renderImage();
    $('#image-error').text('').addClass('d-none');
    $(imageInput).removeClass('is-invalid');
  }

  function clearImage(removeExisting) {
    selectedFile = null;
    syncInput();
    if (removeExisting && existingImage.url) {
      existingRemoved = true;
      if (removeImageFlag) removeImageFlag.value = '1';
    }
    renderImage();
  }

  function bindDropzone(zone) {
    if (!zone) return;
    ['dragenter', 'dragover'].forEach(function (eventName) {
      zone.addEventListener(eventName, function (event) {
        event.preventDefault();
        zone.classList.add('is-dragover');
      });
    });
    ['dragleave', 'drop'].forEach(function (eventName) {
      zone.addEventListener(eventName, function (event) {
        event.preventDefault();
        zone.classList.remove('is-dragover');
        if (eventName === 'drop' && event.dataTransfer?.files?.length) {
          setImage(event.dataTransfer.files[0]);
        }
      });
    });
  }

  imageInput.addEventListener('change', function () {
    if (this.files?.[0]) setImage(this.files[0]);
  });

  imageGrid.addEventListener('click', function (event) {
    const button = event.target.closest('.vendor-media-preview-remove');
    if (!button) return;
    clearImage(button.getAttribute('data-remove') === 'existing');
  });

  bindDropzone(imageDropzone);
  renderImage();
})();

window.initServiceProviderServiceLocationAutocomplete = function () {
  const locationInput = document.getElementById('location');
  const latitudeInput = document.getElementById('latitude');
  const longitudeInput = document.getElementById('longitude');
  const postalCodeInput = document.querySelector('[name="postal_code"]');
  const cityInput = document.querySelector('[name="city"]');
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
    const components = place.address_components || [];
    const findComponent = function (types) {
      const match = components.find(function (component) {
        return types.some(function (type) { return component.types.includes(type); });
      });
      return match ? match.long_name : '';
    };
    if (postalCodeInput && !postalCodeInput.value) postalCodeInput.value = findComponent(['postal_code']);
    if (cityInput && !cityInput.value) {
      cityInput.value = findComponent(['locality', 'postal_town', 'administrative_area_level_3', 'administrative_area_level_2']);
    }
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

$(function () {
  const $form = $('#service_provider-service-form');
  if (!$form.length || String($form.data('ajax-submit')) !== '1') return;

  const isEdit = String($form.data('is-edit')) === '1';
  const fieldContainerSelector = '.col-12, .col-md-6, .form-check, .col-lg-4, .col-lg-8, .vendor-media-block';
  const hiddenValidationFields = ['latitude', 'longitude'];
  const $submitBtn = $('#service_providerServiceSubmitBtn');
  let originalBtnHtml = $submitBtn.html();

  $.validator.addMethod('locationPicked', function () {
    return String($('#latitude').val() || '').trim() !== '' && String($('#longitude').val() || '').trim() !== '';
  }, 'Please select a location from the suggestions list.');

  function setSubmitLoading(isLoading) {
    if (isLoading) {
      originalBtnHtml = $submitBtn.html();
      $submitBtn.prop('disabled', true).addClass('is-loading').html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');
      return;
    }
    $submitBtn.prop('disabled', false).removeClass('is-loading').html(originalBtnHtml);
  }

  function showImageError(message) {
    $('#serviceImageInput').addClass('is-invalid').removeClass('is-valid');
    $('#image-error').text(message).removeClass('d-none');
  }

  function clearImageError() {
    $('#serviceImageInput').removeClass('is-invalid');
    $('#image-error').text('').addClass('d-none');
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
    const hasIncompleteRow = rows.some(function (item) {
      return (item.duration !== '' && item.price === '') || (item.duration === '' && item.price !== '');
    });
    if (!hasCompleteRow) {
      if (showErrors) showChargeError('Please add at least one service duration and price.');
      return false;
    }
    if (hasIncompleteRow) {
      if (showErrors) showChargeError('Each charge row must include both duration and price.');
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
    clearImageError();

    Object.entries(errors || {}).forEach(function ([field, messages]) {
      const normalizedField = field.replace(/\.[0-9]+(?=\.|$)/g, '').replace(/\*$/, '');
      const message = Array.isArray(messages) ? messages[0] : String(messages || 'Invalid value');
      if (hiddenValidationFields.includes(normalizedField)) {
        mapped.location = 'Please select a location from the suggestions list.';
        return;
      }
      if (normalizedField.startsWith('charge_duration') || normalizedField.startsWith('charge_price')) {
        showChargeError(message);
        return;
      }
      if (normalizedField === 'image') {
        showImageError(message);
        return;
      }
      mapped[normalizedField] = message;
    });

    if (validator && Object.keys(mapped).length) validator.showErrors(mapped);

    const $firstInvalid = $form.find('.is-invalid').filter(':visible').first();
    if ($firstInvalid.length) {
      $('html, body').animate({ scrollTop: Math.max($firstInvalid.offset().top - 120, 0) }, 250);
    }
  }

  $form.validate({
    ignore: [],
    rules: {
      name: { required: true, maxlength: 255 },
      category_id: { required: true },
      consultation_type: { required: true },
      business_type: { required: true },
      location: { required: true, locationPicked: true, maxlength: 255 },
      accept_terms: { required: {{ ($isAdmin || $service->exists) ? 'false' : 'true' }} },
      @if($isAdmin)
      service_provider_id: { required: true },
      @endif
    },
    messages: {
      name: { required: 'Please enter the service name.' },
      category_id: { required: 'Please select a category.' },
      consultation_type: { required: 'Please select a service type.' },
      business_type: { required: 'Please select a business type.' },
      location: { required: 'Please enter a location.' },
      accept_terms: { required: 'Please accept the terms and conditions.' },
      @if($isAdmin)
      service_provider_id: { required: 'Please select a service provider.' },
      @endif
    },
    errorElement: 'div',
    errorClass: 'invalid-feedback d-block',
    highlight: function (element) {
      $(element).addClass('is-invalid').removeClass('is-valid');
    },
    unhighlight: function (element) {
      const $element = $(element);
      $element.removeClass('is-invalid');
      if ($element.closest('#charges-wrap').length) return;
      $element.addClass('is-valid');
    },
    errorPlacement: function (error, element) {
      const $container = $(element).closest(fieldContainerSelector);
      const $existing = $container.find('.invalid-feedback').not(error).first();
      if ($existing.length) {
        $existing.text(error.text()).removeClass('d-none');
        error.remove();
        return;
      }
      error.insertAfter(element);
    },
    invalidHandler: function () {
      setSubmitLoading(false);
      const $firstInvalid = $form.find('.is-invalid').filter(':visible').first();
      if ($firstInvalid.length) {
        $('html, body').animate({ scrollTop: Math.max($firstInvalid.offset().top - 120, 0) }, 250);
      }
    },
    submitHandler: function (form) {
      if (!validateChargeRows(true)) {
        setSubmitLoading(false);
        return false;
      }

      setSubmitLoading(true);
      clearImageError();

      $.ajax({
        url: form.action,
        method: 'POST',
        data: new FormData(form),
        processData: false,
        contentType: false,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        success: function (payload) {
          notify('success', payload.message || (isEdit ? 'Service updated successfully.' : 'Service submitted successfully.'));
          setTimeout(function () {
            window.location.href = payload.redirect || '{{ $isAdmin ? route('admin.service-provider-services.all.index') : route('service_provider.services.index') }}';
          }, 800);
        },
        error: function (xhr) {
          const payload = xhr.responseJSON || {};
          applyServerErrors(payload.errors || {});
          if (xhr.status === 422) {
            notify('error', 'Please fix the highlighted fields and try again.');
            return;
          }
          notify('error', payload.message || (isEdit ? 'Unable to update service.' : 'Unable to save service.'));
        },
        complete: function () {
          setSubmitLoading(false);
        }
      });
    }
  });
});
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initServiceProviderServiceLocationAutocomplete"></script>
@endpush
