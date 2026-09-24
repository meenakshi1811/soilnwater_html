@extends('backend.layouts.app')

@section('title', 'Institute Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
  use App\Support\InstituteGallery;
  $galleryItems = InstituteGallery::entries($institute->gallery);
@endphp
<div class="admin-panel ems-page institute-portal">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <p class="ems-kicker mb-1">{{ $institute->roleLabel() }} Profile</p>
            <h2 class="admin-title mb-1">Edit your public profile</h2>
            <p class="mb-0 text-secondary">Update your school details visible on the public listing and profile page.</p>
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

    <form method="POST" action="{{ $portalRoute('profile.update') }}" enctype="multipart/form-data" class="js-ajax-form chart-card" data-success-redirect="{{ $portalRoute('profile.edit') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label" for="institution_name">Institution name *</label>
                    <input type="text" class="form-control" id="institution_name" name="institution_name" value="{{ old('institution_name', $institute->institution_name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="contact_person">Contact person</label>
                    <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person', $institute->contact_person) }}">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="institution_type">Institution type</label>
                        <select class="form-select" id="institution_type" name="institution_type">
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
                        <label class="form-label" for="board_affiliation">Board / affiliation</label>
                        <input type="text" class="form-control" id="board_affiliation" name="board_affiliation" value="{{ old('board_affiliation', $institute->board_affiliation) }}" placeholder="CBSE, ICSE, State Board...">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label" for="tagline">Tagline</label>
                    <input type="text" class="form-control" id="tagline" name="tagline" value="{{ old('tagline', $institute->tagline) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="about">About</label>
                    <textarea class="form-control" id="about" name="about" rows="5">{{ old('about', $institute->about) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Grades / classes offered</label>
                    <div id="gradesWrap">
                        @php $grades = old('grades_offered', $institute->grades_offered ?? ['']); @endphp
                        @foreach ($grades as $i => $grade)
                            <div class="input-group mb-2 js-repeat-row">
                                <input type="text" class="form-control" name="grades_offered[]" value="{{ $grade }}" placeholder="e.g. Class 1">
                                <button type="button" class="btn btn-outline-danger js-remove-row">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addGradeRow">+ Add grade</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Facilities</label>
                    <div id="facilitiesWrap">
                        @php $facilities = old('facilities', $institute->facilities ?? ['']); @endphp
                        @foreach ($facilities as $facility)
                            <div class="input-group mb-2 js-repeat-row">
                                <input type="text" class="form-control" name="facilities[]" value="{{ $facility }}" placeholder="e.g. Library">
                                <button type="button" class="btn btn-outline-danger js-remove-row">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addFacilityRow">+ Add facility</button>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3 text-center">
                    <label class="form-label d-block">Logo</label>
                    @if($institute->logoUrl())
                        <img src="{{ $institute->logoUrl() }}" alt="Logo" class="institute-portal__logo-preview mb-2">
                    @endif
                    <input type="file" class="form-control" name="logo" accept="image/*">
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
                    <textarea class="form-control" id="address" name="address" rows="2" required>{{ old('address', $user->address) }}</textarea>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" for="city">City *</label>
                        <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $user->city) }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="state">State</label>
                        <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $institute->state) }}">
                    </div>
                </div>
                <div class="mb-3 mt-2">
                    <label class="form-label" for="pincode">Pincode *</label>
                    <input type="text" class="form-control" id="pincode" name="pincode" value="{{ old('pincode', $user->pincode) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="website_url">Website</label>
                    <input type="url" class="form-control" id="website_url" name="website_url" value="{{ old('website_url', $institute->website_url) }}">
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
                    <label class="form-label" for="date_of_birth">Date of birth (account holder) *</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        <div class="border-top pt-4 mt-2">
            <h3 class="h5 mb-1">Gallery</h3>
            <p class="text-secondary mb-3">
                Add campus photos and videos for your public profile gallery.
                Photos up to {{ InstituteGallery::imageLimitLabel() }} (JPG, PNG, WebP).
                Videos up to {{ InstituteGallery::videoLimitLabel() }} (MP4, WebM, MOV).
                Maximum {{ InstituteGallery::MAX_ITEMS }} items total.
            </p>

            @if($galleryItems->isNotEmpty())
                <div class="row g-3 mb-3">
                    @foreach($galleryItems as $item)
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="inst-gallery-manage-card">
                                <input type="checkbox" class="form-check-input" name="removed_gallery[]" value="{{ $item['path'] }}">
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
                    class="form-control js-inst-gallery-upload"
                    id="gallery_uploads"
                    name="gallery_uploads[]"
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
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Save profile</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  function bindRepeat(addBtnId, wrapId, inputName, placeholder) {
    var addBtn = document.getElementById(addBtnId);
    var wrap = document.getElementById(wrapId);
    if (!addBtn || !wrap) return;
    addBtn.addEventListener('click', function () {
      var row = document.createElement('div');
      row.className = 'input-group mb-2 js-repeat-row';
      row.innerHTML = '<input type="text" class="form-control" name="' + inputName + '" placeholder="' + placeholder + '"><button type="button" class="btn btn-outline-danger js-remove-row">&times;</button>';
      wrap.appendChild(row);
    });
    wrap.addEventListener('click', function (e) {
      if (e.target.closest('.js-remove-row')) {
        var rows = wrap.querySelectorAll('.js-repeat-row');
        var row = e.target.closest('.js-repeat-row');
        if (rows.length > 1) row.remove();
      }
    });
  }
  bindRepeat('addGradeRow', 'gradesWrap', 'grades_offered[]', 'e.g. Class 1');
  bindRepeat('addFacilityRow', 'facilitiesWrap', 'facilities[]', 'e.g. Library');

  var galleryInput = document.getElementById('gallery_uploads');
  var galleryError = document.getElementById('instGalleryUploadError');
  if (galleryInput) {
    galleryInput.addEventListener('change', function () {
      if (!galleryError) return;
      var imageMax = Number(galleryInput.dataset.imageMax || 0);
      var videoMax = Number(galleryInput.dataset.videoMax || 0);
      var imageLabel = galleryInput.dataset.imageLabel || '2 MB';
      var videoLabel = galleryInput.dataset.videoLabel || '20 MB';
      var invalid = [];

      Array.from(galleryInput.files || []).forEach(function (file) {
        var isVideo = (file.type || '').indexOf('video/') === 0;
        var maxBytes = isVideo ? videoMax : imageMax;
        var label = isVideo ? videoLabel : imageLabel;
        if (maxBytes > 0 && file.size > maxBytes) {
          invalid.push(file.name + ' exceeds ' + label);
        }
      });

      if (invalid.length) {
        galleryError.textContent = invalid.join(' ');
        galleryError.classList.remove('d-none');
        galleryInput.value = '';
        return;
      }

      galleryError.classList.add('d-none');
      galleryError.textContent = '';
    });
  }
});
</script>
@endpush
