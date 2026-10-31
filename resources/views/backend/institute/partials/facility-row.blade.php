{{-- A facility: its name and photo (institute-profile.js shows a picked photo and clears one) --}}
@php
  $facilityImageUrl = filled($facilityImage ?? null) ? asset($facilityImage) : null;
@endphp
<div class="inst-facility-row js-repeat-row js-facility-row mb-2">
  <div class="inst-facility-row__thumb js-facility-thumb {{ $facilityImageUrl ? '' : 'is-empty' }}">
    <img src="{{ $facilityImageUrl }}" alt="" class="{{ $facilityImageUrl ? '' : 'd-none' }}">
    <i class="fa-regular fa-image {{ $facilityImageUrl ? 'd-none' : '' }}" aria-hidden="true"></i>
  </div>
  <div class="inst-facility-row__fields">
    <input type="text" class="form-control js-section-field" name="facilities[]" value="{{ $facility ?? '' }}" placeholder="e.g. Library" data-section="facilities" aria-label="Facility name">
    <div class="inst-facility-row__photo">
      <input type="file" class="form-control form-control-sm js-section-field js-facility-image" name="facility_images[]" accept="image/jpeg,image/png,image/webp" data-section="facilities" aria-label="Facility photo">
      <button type="button" class="btn btn-sm btn-link text-danger js-facility-image-clear {{ $facilityImageUrl ? '' : 'd-none' }}">Remove photo</button>
    </div>
  </div>
  <input type="hidden" class="js-section-field js-facility-existing" name="facility_existing_images[]" value="{{ $facilityImage ?? '' }}" data-section="facilities">
  <button type="button" class="btn btn-outline-danger js-remove-row" aria-label="Remove facility">&times;</button>
</div>
