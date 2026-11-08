{{-- The top of the page: photo, headline, the two numbers, teaching modes and languages --}}
<form class="edu-inline-form" novalidate enctype="multipart/form-data" data-save-url="{{ route('educator.inline.update', [$educator->slug, 'overview']) }}">
  @csrf
  <input type="hidden" name="_method" value="PUT">
  <div class="alert d-none mb-3" role="alert"></div>

  <div class="row g-3">
    <div class="col-lg-4">
      <label class="form-label" for="eduInlinePhoto">Profile photo</label>
      <div class="edu-inline-photo">
        <img src="{{ $educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="" class="edu-inline-photo__img js-edu-inline-photo-preview">
        <input type="file" id="eduInlinePhoto" name="profile_photo" class="form-control form-control-sm js-edu-inline-photo" accept="image/jpeg,image/png,image/webp">
        <p class="form-text mb-0">JPG, PNG or WebP, up to 2 MB. Leave empty to keep this one.</p>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="mb-3">
        <label class="form-label" for="eduInlineHeadline">Headline</label>
        <input type="text" id="eduInlineHeadline" name="professional_headline" class="form-control" maxlength="255" value="{{ $educator->professional_headline }}" placeholder="e.g. Physics teacher for Classes 9–12 · CBSE & ICSE">
        <p class="form-text mb-0">Shown under your name.</p>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label" for="eduInlineYears">Years of experience</label>
          <input type="number" id="eduInlineYears" name="years_experience" class="form-control" min="0" max="80" value="{{ $educator->years_experience }}">
        </div>
        <div class="col-sm-6">
          <label class="form-label" for="eduInlineStudents">Students taught</label>
          <input type="number" id="eduInlineStudents" name="students_taught" class="form-control" min="0" value="{{ $educator->students_taught }}">
        </div>
      </div>
      <div class="row g-3">
        <div class="col-sm-6">
          <label class="form-label" for="eduInlineModes">Teaching modes <span class="text-secondary fw-normal">(one per line)</span></label>
          <textarea id="eduInlineModes" name="teaching_modes" class="form-control" rows="4" placeholder="Online&#10;Home tuition&#10;Batch classes">{{ implode("\n", $educator->teaching_modes ?? []) }}</textarea>
        </div>
        <div class="col-sm-6">
          <label class="form-label" for="eduInlineLanguages">Languages <span class="text-secondary fw-normal">(one per line)</span></label>
          <textarea id="eduInlineLanguages" name="languages" class="form-control" rows="4" placeholder="English&#10;Hindi">{{ implode("\n", $educator->languages ?? []) }}</textarea>
        </div>
      </div>
    </div>
  </div>

  @include('frontend.educator.partials.inline.save-button')
</form>
