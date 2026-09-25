@extends('backend.layouts.app')

@section('title', (auth()->user()?->isSchool() ? 'School Profile' : 'Institute Profile'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@php
  use App\Support\InstituteGallery;
  $galleryItems = InstituteGallery::entries($institute->gallery);
  $gradesList = old('grades_offered', $institute->grades_offered ?? []);
  $gradesList = is_array($gradesList) ? array_values(array_filter($gradesList, fn ($g) => is_string($g) && trim($g) !== '')) : [];
  if ($gradesList === []) {
      $gradesList = [''];
  }
  $facilitiesList = old('facilities', $institute->facilities ?? []);
  $facilitiesList = is_array($facilitiesList) ? array_values(array_filter($facilitiesList, fn ($f) => is_string($f) && trim($f) !== '')) : [];
  if ($facilitiesList === []) {
      $facilitiesList = [''];
  }
  $storedGradesCount = count(array_filter($institute->grades_offered ?? [], fn ($g) => is_string($g) && trim($g) !== ''));
  $storedFacilitiesCount = count(array_filter($institute->facilities ?? [], fn ($f) => is_string($f) && trim($f) !== ''));
  $gradesSectionEnabled = old('grades_section_enabled') !== null
      ? (bool) old('grades_section_enabled')
      : $storedGradesCount > 0;
  $facilitiesSectionEnabled = old('facilities_section_enabled') !== null
      ? (bool) old('facilities_section_enabled')
      : $storedFacilitiesCount > 0;
  $gallerySectionEnabled = old('gallery_section_enabled') !== null
      ? (bool) old('gallery_section_enabled')
      : $galleryItems->isNotEmpty();
@endphp
<div class="admin-panel ems-page institute-portal">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div id="schoolInstituteProfileAlert" class="alert d-none" role="alert"></div>

    <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <p class="ems-kicker mb-1">{{ $institute->roleLabel() }} Profile</p>
            <h2 class="admin-title mb-1">Edit your public profile</h2>
            <p class="mb-0 text-secondary">
              @if(auth()->user()?->isSchool())
                Update your school details visible on the public listing and profile page.
              @else
                Update your institute details visible on the public listing and profile page.
              @endif
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ $portalRoute('public-page.edit') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-globe me-1"></i> Manage public page
            </a>
            @if($institute->isApproved())
                <a href="{{ $institute->publicUrl() }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View live page
                </a>
            @endif
        </div>
    </div>

    <form id="schoolInstituteProfileForm" method="POST" action="{{ $portalRoute('profile.update') }}" enctype="multipart/form-data" class="chart-card" data-has-logo="{{ $institute->logoUrl() ? '1' : '0' }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label" for="institution_name">Institution name *</label>
                    <input type="text" class="form-control" id="institution_name" name="institution_name" value="{{ old('institution_name', $institute->institution_name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="contact_person">Contact person *</label>
                    <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person', $institute->contact_person) }}" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="institution_type">Institution type *</label>
                        <select class="form-select" id="institution_type" name="institution_type" required>
                            <option value="">Select type</option>
                            @php
                                $institutionTypes = auth()->user()?->isSchool()
                                    ? ['school' => 'School', 'college' => 'College', 'university' => 'University']
                                    : ['coaching' => 'Coaching Institute', 'other' => 'Other'];
                            @endphp
                            @foreach ($institutionTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('institution_type', $institute->institution_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="board_affiliation">Board / affiliation *</label>
                        <input type="text" class="form-control" id="board_affiliation" name="board_affiliation" value="{{ old('board_affiliation', $institute->board_affiliation) }}" placeholder="CBSE, ICSE, State Board..." required>
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label" for="tagline">Tagline *</label>
                    <input type="text" class="form-control" id="tagline" name="tagline" value="{{ old('tagline', $institute->tagline) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="about">About *</label>
                    <textarea class="form-control" id="about" name="about" rows="5" required minlength="10">{{ old('about', $institute->about) }}</textarea>
                </div>
                <div class="mb-3 inst-profile-section">
                    <div class="inst-profile-section__head">
                        <label class="form-label mb-0" for="grades_section_enabled">Grades / classes offered</label>
                        <div class="form-check form-switch inst-profile-section__switch mb-0">
                            <input class="form-check-input js-profile-section-toggle" type="checkbox" role="switch" id="grades_section_enabled" name="grades_section_enabled" value="1" data-section="grades" @checked($gradesSectionEnabled)>
                            <label class="form-check-label" for="grades_section_enabled">Show section</label>
                        </div>
                    </div>
                    <div id="gradesSectionBody" class="inst-profile-section__body @unless($gradesSectionEnabled) d-none @endunless" data-section-body="grades">
                        <div id="gradesWrap">
                            @foreach ($gradesList as $grade)
                                <div class="input-group mb-2 js-repeat-row">
                                    <input type="text" class="form-control js-section-field" name="grades_offered[]" value="{{ $grade }}" placeholder="e.g. Class 1" data-section="grades">
                                    <button type="button" class="btn btn-outline-danger js-remove-row">&times;</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary js-section-control" id="addGradeRow" data-section="grades">+ Add grade</button>
                    </div>
                </div>
                <div class="mb-3 inst-profile-section">
                    <div class="inst-profile-section__head">
                        <label class="form-label mb-0" for="facilities_section_enabled">Facilities</label>
                        <div class="form-check form-switch inst-profile-section__switch mb-0">
                            <input class="form-check-input js-profile-section-toggle" type="checkbox" role="switch" id="facilities_section_enabled" name="facilities_section_enabled" value="1" data-section="facilities" @checked($facilitiesSectionEnabled)>
                            <label class="form-check-label" for="facilities_section_enabled">Show section</label>
                        </div>
                    </div>
                    <div id="facilitiesSectionBody" class="inst-profile-section__body @unless($facilitiesSectionEnabled) d-none @endunless" data-section-body="facilities">
                        <div id="facilitiesWrap">
                            @foreach ($facilitiesList as $facility)
                                <div class="input-group mb-2 js-repeat-row">
                                    <input type="text" class="form-control js-section-field" name="facilities[]" value="{{ $facility }}" placeholder="e.g. Library" data-section="facilities">
                                    <button type="button" class="btn btn-outline-danger js-remove-row">&times;</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary js-section-control" id="addFacilityRow" data-section="facilities">+ Add facility</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3 text-center">
                    <label class="form-label d-block">Logo @unless($institute->logoUrl()) * @endunless</label>
                    @if($institute->logoUrl())
                        <img src="{{ $institute->logoUrl() }}" alt="Logo" class="institute-portal__logo-preview mb-2">
                    @endif
                    <input type="file" class="form-control" name="logo" accept="image/*" @unless($institute->logoUrl()) required @endunless>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone_number">Phone *</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="whatsapp_number">WhatsApp *</label>
                    <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $user->whatsapp_number) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address">Address *</label>
                    <textarea class="form-control" id="address" name="address" rows="2" required autocomplete="off" placeholder="Start typing to search on Google Maps…">{{ old('address', $user->address ?: $institute->address) }}</textarea>
                    <div class="form-text">Select a place from suggestions to fill city, state, pincode, and map coordinates automatically.</div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $institute->latitude) }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $institute->longitude) }}">
                    <input type="hidden" name="place_id" id="place_id" value="{{ old('place_id', $institute->place_id) }}">
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" for="city">City *</label>
                        <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $user->city ?: $institute->city) }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="state">State *</label>
                        <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $institute->state) }}" required>
                    </div>
                </div>
                <div class="mb-3 mt-2">
                    <label class="form-label" for="pincode">Pincode *</label>
                    <input type="text" class="form-control" id="pincode" name="pincode" value="{{ old('pincode', $user->pincode) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="brochure">Profile brochure (PDF)</label>
                    @if($institute->brochureUrl())
                        <div class="small mb-2">
                            <a href="{{ $institute->brochureUrl() }}" target="_blank" rel="noopener">View current brochure</a>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="1" id="remove_brochure" name="remove_brochure">
                            <label class="form-check-label" for="remove_brochure">Remove brochure</label>
                        </div>
                    @endif
                    <input type="file" class="form-control" id="brochure" name="brochure" accept="application/pdf">
                    <div class="form-text">Used by the “Download Brochure” button on your public profile.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="date_of_establishment">Founded date *</label>
                    <input type="date" class="form-control" id="date_of_establishment" name="date_of_establishment" value="{{ old('date_of_establishment', optional($institute->date_of_establishment)->format('Y-m-d')) }}" max="{{ now()->toDateString() }}" required>
                    <div class="form-text">When the school or institute was established (shown on your public profile).</div>
                </div>
            </div>
        </div>

        <div class="border-top pt-4 mt-2 inst-profile-section">
            <div class="inst-profile-section__head mb-2">
                <h3 class="h5 mb-0">Gallery</h3>
                <div class="form-check form-switch inst-profile-section__switch mb-0">
                    <input class="form-check-input js-profile-section-toggle" type="checkbox" role="switch" id="gallery_section_enabled" name="gallery_section_enabled" value="1" data-section="gallery" @checked($gallerySectionEnabled)>
                    <label class="form-check-label" for="gallery_section_enabled">Show section</label>
                </div>
            </div>
            <div id="gallerySectionBody" class="inst-profile-section__body @unless($gallerySectionEnabled) d-none @endunless" data-section-body="gallery">
            <p class="text-secondary mb-3">
                Add campus photos and videos for your public profile gallery.
                Photos up to {{ InstituteGallery::imageLimitLabel() }} (JPG, PNG, WebP).
                Videos up to {{ InstituteGallery::videoLimitLabel() }} (MP4, WebM, MOV).
                Maximum {{ InstituteGallery::MAX_ITEMS }} items total.
            </p>

            @if($galleryItems->isNotEmpty())
                <div class="row g-3 mb-3" id="galleryExistingWrap">
                    @foreach($galleryItems as $item)
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="inst-gallery-manage-card">
                                <input type="checkbox" class="form-check-input js-section-field" name="removed_gallery[]" value="{{ $item['path'] }}" data-section="gallery">
                                <span class="inst-gallery-manage-card__frame">
                                    @if($item['type'] === 'video')
                                        <span class="inst-gallery-manage-card__video"><i class="fa-solid fa-circle-play" aria-hidden="true"></i> Video</span>
                                    @else
                                        <img src="{{ $item['url'] }}" alt="" loading="lazy" decoding="async">
                                    @endif
                                </span>
                                <span class="inst-gallery-manage-card__label">Remove</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mb-2">
                <label class="form-label" for="gallery_uploads">Upload gallery files</label>
                <input
                    type="file"
                    class="form-control js-inst-gallery-upload js-section-field"
                    id="gallery_uploads"
                    name="gallery_uploads[]"
                    data-section="gallery"
                    accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov"
                    multiple
                    data-image-max="{{ InstituteGallery::imageMaxBytes() }}"
                    data-video-max="{{ InstituteGallery::videoMaxBytes() }}"
                    data-image-label="{{ InstituteGallery::imageLimitLabel() }}"
                    data-video-label="{{ InstituteGallery::videoLimitLabel() }}"
                >
            </div>
            <div class="form-text mb-0" id="instGalleryUploadHelp">
                You can select multiple files. Large videos are checked before upload.
            </div>
            <div class="alert alert-warning d-none mt-2 mb-0 py-2" id="instGalleryUploadError" role="alert"></div>
            <input type="hidden" id="gallerySectionValidator" name="gallery_section_validator" value="">
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="submit" class="btn btn-primary" id="schoolInstituteProfileSubmitBtn">Save profile</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/institute-profile.js') }}?v={{ now()->timestamp }}"></script>
@if(config('services.google.maps_api_key'))
<script>
window.initSchoolInstituteProfilePlacesAutocomplete = function () {
    if (window.FormHelper && typeof window.FormHelper.initSchoolInstituteProfilePlaceAutocomplete === 'function') {
        window.FormHelper.initSchoolInstituteProfilePlaceAutocomplete();
    }
};
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initSchoolInstituteProfilePlacesAutocomplete"></script>
@endif
@endpush
