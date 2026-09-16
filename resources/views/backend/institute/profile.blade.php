@extends('backend.layouts.app')

@section('title', 'Institute Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
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
            <a href="{{ route($portalPrefix.'.public-page.edit') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-globe me-1"></i> Manage public page
            </a>
            @if($institute->isApproved())
                <a href="{{ $institute->publicUrl() }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View live page
                </a>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route($portalPrefix.'.profile.update') }}" enctype="multipart/form-data" class="js-ajax-form chart-card" data-success-redirect="{{ route($portalPrefix.'.profile.edit') }}">
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
                    <label class="form-label" for="date_of_birth">Date of birth (account holder) *</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" required>
                </div>
            </div>
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
});
</script>
@endpush
