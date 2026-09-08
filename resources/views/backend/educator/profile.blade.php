@extends('backend.layouts.app')
@section('title', 'Educator Profile')
@section('content')
@php
  $educator = $educator ?? null;
  $subjects = old('subjects', $educator->subjects ?? [['name' => '', 'level' => 'primary']]);
  $qualifications = old('qualifications', $educator->qualifications ?? [['degree' => '', 'institution' => '', 'year' => '']]);
  $experienceYears = range((int) date('Y'), 1970);
  $experiences = old('experiences');
  if ($experiences === null) {
    $experiences = collect($educator->experiences ?? [])
      ->map(fn ($row) => \App\Models\Educator::formatExperienceForForm(is_array($row) ? $row : []))
      ->all();
  }
  if (empty($experiences)) {
    $experiences = [['title' => '', 'organization' => '', 'start_year' => '', 'end_year' => '', 'is_current' => false, 'description' => '']];
  }
  $availability = old('availability', $educator->availability ?? [['day' => '', 'slots' => '']]);
  $tuitionBatches = old('tuition_batches');
  if ($tuitionBatches === null) {
    $tuitionBatches = $educator->normalizedTuitionBatches();
  }
  if (empty($tuitionBatches)) {
    $tuitionBatches = [['class' => '', 'subject' => '', 'batch_type' => '', 'student_count' => '', 'cost' => '']];
  }
  $toLines = fn ($arr) => is_array($arr) ? implode("\n", $arr) : '';
@endphp
<div class="admin-panel ems-page">
  <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
      <p class="ems-kicker mb-1">Educator Portal</p>
      <h2 class="admin-title mb-0">Professional profile</h2>
    </div>
    @if($educator?->isApproved())
      <a href="{{ $educator->publicUrl() }}" target="_blank" class="btn btn-outline-primary">View public profile</a>
    @endif
  </div>

  @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
  @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif

  <div id="educatorProfileAlert" class="alert d-none" role="alert"></div>

  <form id="educatorProfileForm" method="POST" action="{{ route('educator.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="chart-card mb-4">
      <h5 class="mb-3">Account details</h5>
      <p class="text-secondary mb-3">These are the same required details collected when a Teacher / Tutor account is created.</p>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label" for="profile_photo">Profile Image{{ $educator->photoUrl() ? '' : ' *' }}</label>
          <input id="profile_photo" type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" {{ $educator->photoUrl() ? '' : 'required' }}>
          <small class="text-muted">Upload a JPG, PNG, or WebP image up to 2 MB. This image appears on the public teacher / tutor profile.</small>
          @if($educator->photoUrl())
            <img src="{{ $educator->photoUrl() }}" alt="" class="rounded mt-2" style="height:64px;width:64px;object-fit:cover">
          @endif
          @error('profile_photo')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        @include('backend.partials.registration-profile-fields', ['profile' => $educator, 'showMarketplaceFields' => false])
      </div>
    </div>

    <div class="chart-card mb-4">
      <h5 class="mb-3">About & teaching</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Professional headline</label>
          <input type="text" name="professional_headline" class="form-control" value="{{ old('professional_headline', $educator->professional_headline) }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Associated institute</label>
          <input type="text" name="associated_institute" class="form-control" value="{{ old('associated_institute', $educator->associated_institute) }}">
          <input type="hidden" name="institute_latitude" value="{{ old('institute_latitude', $educator->institute_latitude) }}">
          <input type="hidden" name="institute_longitude" value="{{ old('institute_longitude', $educator->institute_longitude) }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">State</label>
          <input type="text" name="state" class="form-control" value="{{ old('state', $educator->state) }}">
        </div>
        <div class="col-md-8 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_available_now" value="1" id="availNow" @checked(old('is_available_now', $educator->is_available_now))>
            <label class="form-check-label" for="availNow">Available now</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">About teacher</label>
          <textarea name="about" class="form-control" rows="5">{{ old('about', $educator->about) }}</textarea>
          <small class="text-muted">The first line is also used as the short intro on the public profile.</small>
        </div>

        <div class="col-12">
          <label class="form-label d-block mb-2">Qualifications</label>
          <div id="qualificationsWrap">
            @foreach($qualifications as $i => $row)
              <div class="row g-2 mb-2 js-repeat-row">
                <div class="col-md-4"><input type="text" name="qualifications[{{ $i }}][degree]" class="form-control" placeholder="Degree" value="{{ $row['degree'] ?? '' }}"></div>
                <div class="col-md-4"><input type="text" name="qualifications[{{ $i }}][institution]" class="form-control" placeholder="Institution" value="{{ $row['institution'] ?? '' }}"></div>
                <div class="col-md-3"><input type="text" name="qualifications[{{ $i }}][year]" class="form-control" placeholder="Year" value="{{ $row['year'] ?? '' }}"></div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div>
              </div>
            @endforeach
          </div>
          <button type="button" class="btn btn-sm btn-outline-primary" data-add="#qualificationsWrap" data-template="qualification">Add qualification</button>
        </div>

        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <label class="form-label d-block mb-0">Experience</label>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add="#experiencesWrap" data-template="experience">Add experience</button>
          </div>

          <div id="experiencesWrap" class="experience-wrap">
            @foreach($experiences as $i => $row)
              @php
                $isCurrentExperience = filter_var($row['is_current'] ?? false, FILTER_VALIDATE_BOOLEAN);
              @endphp
              <div class="experience-card js-repeat-row">
                <div class="row g-2 align-items-end">
                  <div class="col-md-6">
                    <label class="form-label">Job title</label>
                    <input type="text" name="experiences[{{ $i }}][title]" class="form-control" placeholder="Senior Physics Teacher" value="{{ $row['title'] ?? '' }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Place of work</label>
                    <input type="text" name="experiences[{{ $i }}][organization]" class="form-control js-experience-organization" autocomplete="off" placeholder="Search school or institute name" value="{{ $row['organization'] ?? '' }}">
                    <small class="text-muted">Start typing to search schools and institutes via Google.</small>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Start year</label>
                    <select name="experiences[{{ $i }}][start_year]" class="form-select">
                      <option value="">Select year</option>
                      @foreach($experienceYears as $year)
                        <option value="{{ $year }}" @selected((string) ($row['start_year'] ?? '') === (string) $year)>{{ $year }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">End year</label>
                    <select name="experiences[{{ $i }}][end_year]" class="form-select js-exp-end-year" @disabled($isCurrentExperience)>
                      <option value="">Select year</option>
                      @foreach($experienceYears as $year)
                        <option value="{{ $year }}" @selected((string) ($row['end_year'] ?? '') === (string) $year)>{{ $year }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-5">
                    <div class="form-check experience-current-check">
                      <input
                        class="form-check-input js-exp-current"
                        type="checkbox"
                        name="experiences[{{ $i }}][is_current]"
                        value="1"
                        id="experienceCurrent{{ $i }}"
                        @checked($isCurrentExperience)
                      >
                      <label class="form-check-label" for="experienceCurrent{{ $i }}">I still work here</label>
                    </div>
                  </div>
                  <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger w-100 js-remove-row" title="Remove experience">&times;</button>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="experiences[{{ $i }}][description]" class="form-control" rows="2" placeholder="Teaching responsibilities, achievements, and role details">{{ $row['description'] ?? '' }}</textarea>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="col-md-6"><label class="form-label">Teaching method</label><input type="text" name="teaching_method" class="form-control" value="{{ old('teaching_method', $educator->teaching_method) }}"></div>
        <div class="col-md-6"><label class="form-label">Teaching modes (one per line)</label>
          <textarea class="form-control js-lines" data-name="teaching_modes" rows="3">{{ $toLines(old('teaching_modes', $educator->teaching_modes ?? [])) }}</textarea>
        </div>
        <div class="col-md-4"><label class="form-label">Languages (one per line)</label><textarea class="form-control js-lines" data-name="languages" rows="3">{{ $toLines(old('languages', $educator->languages ?? [])) }}</textarea></div>
        <div class="col-md-4"><label class="form-label">Classes (one per line)</label><textarea class="form-control js-lines" data-name="classes" rows="3">{{ $toLines(old('classes', $educator->classes ?? [])) }}</textarea></div>
        <div class="col-md-4"><label class="form-label">Boards (one per line)</label><textarea class="form-control js-lines" data-name="boards" rows="3">{{ $toLines(old('boards', $educator->boards ?? [])) }}</textarea></div>
        <div class="col-md-4"><label class="form-label">Years experience</label><input type="number" name="years_experience" class="form-control" min="0" value="{{ old('years_experience', $educator->years_experience) }}"></div>
        <div class="col-md-4"><label class="form-label">Students taught</label><input type="number" name="students_taught" class="form-control" min="0" value="{{ old('students_taught', $educator->students_taught) }}"></div>
        <div class="col-md-4"><label class="form-label">Success rate %</label><input type="number" step="0.01" name="success_rate" class="form-control" value="{{ old('success_rate', $educator->success_rate) }}"></div>
        <div class="col-12"><label class="form-label">Service area (one per line)</label><textarea class="form-control js-lines" data-name="service_area" rows="2">{{ $toLines(old('service_area', $educator->service_area ?? [])) }}</textarea></div>
      </div>
    </div>

    <div class="chart-card mb-4">
      <h5 class="mb-3">Subjects</h5>
      <div id="subjectsWrap">
        @foreach($subjects as $i => $subject)
          <div class="row g-2 mb-2 js-repeat-row">
            <div class="col-md-7"><input type="text" name="subjects[{{ $i }}][name]" class="form-control" placeholder="Subject name" value="{{ is_array($subject) ? ($subject['name'] ?? '') : $subject }}"></div>
            <div class="col-md-4">
              <select name="subjects[{{ $i }}][level]" class="form-select">
                @foreach(['primary','secondary','specialized'] as $level)
                  <option value="{{ $level }}" @selected((is_array($subject) ? ($subject['level'] ?? 'primary') : 'primary') === $level)>{{ ucfirst($level) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div>
          </div>
        @endforeach
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary" data-add="#subjectsWrap" data-template="subject">Add subject</button>
    </div>

    <div class="chart-card mb-4">
      <h5 class="mb-3">Tuition details</h5>
      <div class="row g-3">
        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="take_tuitions" value="1" id="takeTuitions" @checked(old('take_tuitions', $educator->take_tuitions))>
            <label class="form-check-label" for="takeTuitions">I take tuitions</label>
            <small class="text-muted d-block mt-1">When enabled, your public page is shown as a tutor profile with fees and tuition details. When disabled, it is shown as an experienced teacher profile.</small>
          </div>
        </div>

        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div>
              <label class="form-label d-block mb-1">Tuition batches</label>
              <small class="text-muted">Add each class batch with subject, batch type, student count, and cost.</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add="#tuitionBatchesWrap" data-template="tuitionBatch">Add batch</button>
          </div>

          <div class="tuition-batches-table d-none d-md-grid text-muted small fw-semibold px-3 py-2 mb-2">
            <span>Class</span>
            <span>Subject</span>
            <span>Batch type</span>
            <span>Students</span>
            <span>Cost</span>
            <span></span>
          </div>

          <div id="tuitionBatchesWrap" class="tuition-batches-wrap">
            @foreach($tuitionBatches as $i => $batch)
              <div class="tuition-batch-card js-repeat-row">
                <div class="tuition-batch-card__grid">
                  <div>
                    <label class="form-label d-md-none">Class</label>
                    <input type="text" name="tuition_batches[{{ $i }}][class]" class="form-control" placeholder="Class 10" value="{{ $batch['class'] ?? '' }}">
                  </div>
                  <div>
                    <label class="form-label d-md-none">Subject</label>
                    <input type="text" name="tuition_batches[{{ $i }}][subject]" class="form-control" placeholder="Physics" value="{{ $batch['subject'] ?? '' }}">
                  </div>
                  <div>
                    <label class="form-label d-md-none">Batch type</label>
                    <input type="text" name="tuition_batches[{{ $i }}][batch_type]" class="form-control" placeholder="Small group" value="{{ $batch['batch_type'] ?? '' }}" list="tuitionBatchTypeOptions">
                  </div>
                  <div>
                    <label class="form-label d-md-none">Students</label>
                    <input type="number" name="tuition_batches[{{ $i }}][student_count]" class="form-control" min="1" placeholder="8" value="{{ $batch['student_count'] ?? '' }}">
                  </div>
                  <div>
                    <label class="form-label d-md-none">Cost</label>
                    <input type="text" name="tuition_batches[{{ $i }}][cost]" class="form-control" placeholder="₹500 / month" value="{{ $batch['cost'] ?? '' }}">
                  </div>
                  <div class="tuition-batch-card__actions">
                    <button type="button" class="btn btn-outline-danger w-100 js-remove-row" title="Remove batch">&times;</button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <datalist id="tuitionBatchTypeOptions">
            <option value="1-on-1"></option>
            <option value="Small group"></option>
            <option value="Crash course"></option>
            <option value="Weekend batch"></option>
            <option value="Online batch"></option>
          </datalist>
        </div>

        <div class="col-md-6">
          <label class="form-label">Tuition location</label>
          <input type="text" name="tuition_location" class="form-control" value="{{ old('tuition_location', $educator->tuition_location) }}" placeholder="Home, online, or centre address">
        </div>
        <div class="col-md-6">
          <label class="form-label">Tuition timings</label>
          <input type="text" name="tuition_timings" class="form-control" value="{{ old('tuition_timings', $educator->tuition_timings) }}" placeholder="Weekdays evenings, Saturday mornings">
        </div>
        <div class="col-12">
          <label class="form-label">Additional fee notes</label>
          <input type="text" name="tuition_charges" class="form-control" value="{{ old('tuition_charges', $educator->tuition_charges) }}" placeholder="Optional notes such as trial class, registration fee, or package details">
        </div>

        <div class="col-12">
          <label class="form-label d-block mb-2">Availability</label>
          <div id="availabilityWrap">
            @foreach($availability as $i => $row)
              <div class="row g-2 mb-2 js-repeat-row">
                <div class="col-md-4"><input type="text" name="availability[{{ $i }}][day]" class="form-control" placeholder="Day" value="{{ $row['day'] ?? '' }}"></div>
                <div class="col-md-7"><input type="text" name="availability[{{ $i }}][slots]" class="form-control" placeholder="Time slots" value="{{ $row['slots'] ?? '' }}"></div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div>
              </div>
            @endforeach
          </div>
          <button type="button" class="btn btn-sm btn-outline-primary" data-add="#availabilityWrap" data-template="availability">Add slot</button>
        </div>
      </div>
    </div>

    <div class="chart-card mb-4">
      <h5 class="mb-3">Achievements, certifications & socials</h5>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Achievements (one per line)</label><textarea class="form-control js-lines" data-name="achievements" rows="4">{{ $toLines(old('achievements', $educator->achievements ?? [])) }}</textarea></div>
        <div class="col-md-6"><label class="form-label">Certifications (one per line)</label><textarea class="form-control js-lines" data-name="certifications" rows="4">{{ $toLines(old('certifications', $educator->certifications ?? [])) }}</textarea></div>
        <div class="col-md-6"><label class="form-label">Facebook URL</label><input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $educator->facebook_url) }}"></div>
        <div class="col-md-6"><label class="form-label">Instagram URL</label><input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $educator->instagram_url) }}"></div>
        <div class="col-md-6"><label class="form-label">YouTube URL</label><input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $educator->youtube_url) }}"></div>
        <div class="col-md-6"><label class="form-label">LinkedIn URL</label><input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $educator->linkedin_url) }}"></div>
        <div class="col-md-6"><label class="form-label">WhatsApp URL</label><input type="url" name="whatsapp_url" class="form-control" value="{{ old('whatsapp_url', $educator->whatsapp_url) }}"></div>
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
      <a href="{{ route('educator.dashboard') }}" class="btn btn-outline-secondary">Back</a>
      <button id="educatorProfileSubmitBtn" type="submit" class="btn btn-primary ems-btn-primary px-4">Save Changes</button>
    </div>
  </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
  .tuition-batches-table {
    grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr) minmax(0, 1fr) minmax(0, 0.7fr) minmax(0, 1fr) 42px;
    gap: 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
  }

  .tuition-batches-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .tuition-batch-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    padding: 0.875rem;
  }

  .tuition-batch-card__grid {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr) minmax(0, 1fr) minmax(0, 0.7fr) minmax(0, 1fr) 42px;
    align-items: end;
  }

  .tuition-batch-card__actions {
    display: flex;
    align-items: end;
  }

  @media (max-width: 767.98px) {
    .tuition-batch-card__grid {
      grid-template-columns: 1fr 1fr;
    }

    .tuition-batch-card__actions {
      grid-column: 1 / -1;
    }
  }

  .experience-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .experience-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    padding: 0.875rem;
  }

  .experience-current-check {
    min-height: 38px;
    display: flex;
    align-items: center;
  }

  .pac-container {
    z-index: 2000;
  }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
(function () {
  const currentYear = new Date().getFullYear();
  const experienceYearOptions = function (selected) {
    let html = '<option value="">Select year</option>';
    for (let year = currentYear; year >= 1970; year--) {
      html += `<option value="${year}"${String(selected) === String(year) ? ' selected' : ''}>${year}</option>`;
    }
    return html;
  };

  const initExperienceOrganizationSearch = function () {
    if (window.FormHelper && typeof window.FormHelper.initEducatorExperienceOrganizationAutocomplete === 'function') {
      window.FormHelper.initEducatorExperienceOrganizationAutocomplete();
    }
  };

  const syncExperienceCurrentState = function (row) {
    if (!row) {
      return;
    }
    const checkbox = row.querySelector('.js-exp-current');
    const endSelect = row.querySelector('.js-exp-end-year');
    if (!checkbox || !endSelect) {
      return;
    }
    endSelect.disabled = checkbox.checked;
    if (checkbox.checked) {
      endSelect.value = '';
    }
  };

  const templates = {
    subject: (i) => `<div class="row g-2 mb-2 js-repeat-row"><div class="col-md-7"><input type="text" name="subjects[${i}][name]" class="form-control" placeholder="Subject name"></div><div class="col-md-4"><select name="subjects[${i}][level]" class="form-select"><option value="primary">Primary</option><option value="secondary">Secondary</option><option value="specialized">Specialized</option></select></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div></div>`,
    qualification: (i) => `<div class="row g-2 mb-2 js-repeat-row"><div class="col-md-4"><input type="text" name="qualifications[${i}][degree]" class="form-control" placeholder="Degree"></div><div class="col-md-4"><input type="text" name="qualifications[${i}][institution]" class="form-control" placeholder="Institution"></div><div class="col-md-3"><input type="text" name="qualifications[${i}][year]" class="form-control" placeholder="Year"></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div></div>`,
    experience: (i) => `<div class="experience-card js-repeat-row"><div class="row g-2 align-items-end"><div class="col-md-6"><label class="form-label">Job title</label><input type="text" name="experiences[${i}][title]" class="form-control" placeholder="Senior Physics Teacher"></div><div class="col-md-6"><label class="form-label">Place of work</label><input type="text" name="experiences[${i}][organization]" class="form-control js-experience-organization" autocomplete="off" placeholder="Search school or institute name"><small class="text-muted">Start typing to search schools and institutes via Google.</small></div><div class="col-md-3"><label class="form-label">Start year</label><select name="experiences[${i}][start_year]" class="form-select">${experienceYearOptions('')}</select></div><div class="col-md-3"><label class="form-label">End year</label><select name="experiences[${i}][end_year]" class="form-select js-exp-end-year">${experienceYearOptions('')}</select></div><div class="col-md-5"><div class="form-check experience-current-check"><input class="form-check-input js-exp-current" type="checkbox" name="experiences[${i}][is_current]" value="1" id="experienceCurrent${i}"><label class="form-check-label" for="experienceCurrent${i}">I still work here</label></div></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row" title="Remove experience">&times;</button></div><div class="col-12"><label class="form-label">Description</label><textarea name="experiences[${i}][description]" class="form-control" rows="2" placeholder="Teaching responsibilities, achievements, and role details"></textarea></div></div></div>`,
    availability: (i) => `<div class="row g-2 mb-2 js-repeat-row"><div class="col-md-4"><input type="text" name="availability[${i}][day]" class="form-control" placeholder="Day"></div><div class="col-md-7"><input type="text" name="availability[${i}][slots]" class="form-control" placeholder="Time slots"></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div></div>`,
    tuitionBatch: (i) => `<div class="tuition-batch-card js-repeat-row"><div class="tuition-batch-card__grid"><div><label class="form-label d-md-none">Class</label><input type="text" name="tuition_batches[${i}][class]" class="form-control" placeholder="Class 10"></div><div><label class="form-label d-md-none">Subject</label><input type="text" name="tuition_batches[${i}][subject]" class="form-control" placeholder="Physics"></div><div><label class="form-label d-md-none">Batch type</label><input type="text" name="tuition_batches[${i}][batch_type]" class="form-control" placeholder="Small group" list="tuitionBatchTypeOptions"></div><div><label class="form-label d-md-none">Students</label><input type="number" name="tuition_batches[${i}][student_count]" class="form-control" min="1" placeholder="8"></div><div><label class="form-label d-md-none">Cost</label><input type="text" name="tuition_batches[${i}][cost]" class="form-control" placeholder="₹500 / month"></div><div class="tuition-batch-card__actions"><button type="button" class="btn btn-outline-danger w-100 js-remove-row" title="Remove batch">&times;</button></div></div></div>`
  };

  document.querySelectorAll('[data-add]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const wrap = document.querySelector(btn.dataset.add);
      const i = wrap.querySelectorAll('.js-repeat-row').length;
      wrap.insertAdjacentHTML('beforeend', templates[btn.dataset.template](i));
      if (btn.dataset.template === 'experience') {
        syncExperienceCurrentState(wrap.lastElementChild);
        initExperienceOrganizationSearch();
      }
    });
  });

  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('js-exp-current')) {
      syncExperienceCurrentState(e.target.closest('.js-repeat-row'));
    }
  });

  document.querySelectorAll('#experiencesWrap .js-repeat-row').forEach(syncExperienceCurrentState);
  initExperienceOrganizationSearch();

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('js-remove-row')) {
      const row = e.target.closest('.js-repeat-row');
      if (row && row.parentElement.querySelectorAll('.js-repeat-row').length > 1) row.remove();
    }
  });

  document.getElementById('educatorProfileForm').addEventListener('submit', function () {
    document.querySelectorAll('#educatorProfileForm .js-lines').forEach(function (el) {
      const name = el.dataset.name;
      el.parentElement.querySelectorAll('input[type=hidden][data-generated="' + name + '"]').forEach(n => n.remove());
      el.value.split(/\r?\n/).map(v => v.trim()).filter(Boolean).forEach(function (line) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name + '[]';
        input.value = line;
        input.setAttribute('data-generated', name);
        el.parentElement.appendChild(input);
      });
    });
  });
})();
</script>
@if(config('services.google.maps_api_key'))
<script>
window.initEducatorExperiencePlacesAutocomplete = function () {
  if (window.FormHelper && typeof window.FormHelper.initEducatorExperienceOrganizationAutocomplete === 'function') {
    window.FormHelper.initEducatorExperienceOrganizationAutocomplete();
  }
};
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initEducatorExperiencePlacesAutocomplete"></script>
@endif
@endpush
