@extends('backend.layouts.app')
@section('title', 'Educator Profile')
@section('content')
@php
  $educator = $educator ?? null;
  $subjects = old('subjects', $educator->subjects ?? [['name' => '', 'level' => 'primary']]);
  $qualifications = old('qualifications', $educator->qualifications ?? [['degree' => '', 'institution' => '', 'year' => '']]);
  $experiences = old('experiences', $educator->experiences ?? [['title' => '', 'organization' => '', 'duration' => '', 'description' => '']]);
  $availability = old('availability', $educator->availability ?? [['day' => '', 'slots' => '']]);
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

  <form method="POST" action="{{ route('educator.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="chart-card mb-4">
      <h5 class="mb-3">Basic info</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Display name *</label>
          <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $educator->display_name) }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Profile photo</label>
          <input type="file" name="profile_photo" class="form-control" accept="image/*">
          @if($educator->photoUrl())
            <img src="{{ $educator->photoUrl() }}" alt="" class="rounded mt-2" style="height:64px;width:64px;object-fit:cover">
          @endif
        </div>
        <div class="col-md-6">
          <label class="form-label">Professional headline</label>
          <input type="text" name="professional_headline" class="form-control" value="{{ old('professional_headline', $educator->professional_headline) }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Tagline</label>
          <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $educator->tagline) }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Associated institute</label>
          <input type="text" name="associated_institute" class="form-control" value="{{ old('associated_institute', $educator->associated_institute) }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Institute latitude</label>
          <input type="text" name="institute_latitude" class="form-control" value="{{ old('institute_latitude', $educator->institute_latitude) }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Institute longitude</label>
          <input type="text" name="institute_longitude" class="form-control" value="{{ old('institute_longitude', $educator->institute_longitude) }}">
        </div>
        <div class="col-md-4"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="{{ old('city', $educator->city) }}"></div>
        <div class="col-md-4"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="{{ old('state', $educator->state) }}"></div>
        <div class="col-md-4"><label class="form-label">Pincode</label><input type="text" name="pincode" class="form-control" value="{{ old('pincode', $educator->pincode) }}"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea name="residential_address" class="form-control" rows="2">{{ old('residential_address', $educator->residential_address) }}</textarea></div>
        <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $educator->phone) }}"></div>
        <div class="col-md-4"><label class="form-label">WhatsApp</label><input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $educator->whatsapp) }}"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $educator->email) }}"></div>
        <div class="col-md-8"><label class="form-label">Video profile URL</label><input type="url" name="video_profile_url" class="form-control" value="{{ old('video_profile_url', $educator->video_profile_url) }}"></div>
        <div class="col-md-4 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_available_now" value="1" id="availNow" @checked(old('is_available_now', $educator->is_available_now))>
            <label class="form-check-label" for="availNow">Available now</label>
          </div>
        </div>
      </div>
    </div>

    <div class="chart-card mb-4">
      <h5 class="mb-3">About & teaching</h5>
      <div class="row g-3">
        <div class="col-12"><label class="form-label">About</label><textarea name="about" class="form-control" rows="5">{{ old('about', $educator->about) }}</textarea></div>
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
      <h5 class="mb-3">Qualifications</h5>
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

    <div class="chart-card mb-4">
      <h5 class="mb-3">Experience</h5>
      <div id="experiencesWrap">
        @foreach($experiences as $i => $row)
          <div class="border rounded p-3 mb-2 js-repeat-row">
            <div class="row g-2">
              <div class="col-md-4"><input type="text" name="experiences[{{ $i }}][title]" class="form-control" placeholder="Title" value="{{ $row['title'] ?? '' }}"></div>
              <div class="col-md-4"><input type="text" name="experiences[{{ $i }}][organization]" class="form-control" placeholder="Organization" value="{{ $row['organization'] ?? '' }}"></div>
              <div class="col-md-3"><input type="text" name="experiences[{{ $i }}][duration]" class="form-control" placeholder="Duration" value="{{ $row['duration'] ?? '' }}"></div>
              <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div>
              <div class="col-12"><textarea name="experiences[{{ $i }}][description]" class="form-control" rows="2" placeholder="Description">{{ $row['description'] ?? '' }}</textarea></div>
            </div>
          </div>
        @endforeach
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary" data-add="#experiencesWrap" data-template="experience">Add experience</button>
    </div>

    <div class="chart-card mb-4">
      <h5 class="mb-3">Availability</h5>
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

    <div class="chart-card mb-4">
      <h5 class="mb-3">Tuition details</h5>
      <div class="row g-3">
        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="take_tuitions" value="1" id="takeTuitions" @checked(old('take_tuitions', $educator->take_tuitions))>
            <label class="form-check-label" for="takeTuitions">I take tuitions</label>
          </div>
        </div>
        <div class="col-md-4"><label class="form-label">Tuition classes</label><textarea class="form-control js-lines" data-name="tuition_classes" rows="3">{{ $toLines(old('tuition_classes', $educator->tuition_classes ?? [])) }}</textarea></div>
        <div class="col-md-4"><label class="form-label">Tuition subjects</label><textarea class="form-control js-lines" data-name="tuition_subjects" rows="3">{{ $toLines(old('tuition_subjects', $educator->tuition_subjects ?? [])) }}</textarea></div>
        <div class="col-md-4"><label class="form-label">Tuition types</label><textarea class="form-control js-lines" data-name="tuition_types" rows="3">{{ $toLines(old('tuition_types', $educator->tuition_types ?? [])) }}</textarea></div>
        <div class="col-md-4"><label class="form-label">Tuition location</label><input type="text" name="tuition_location" class="form-control" value="{{ old('tuition_location', $educator->tuition_location) }}"></div>
        <div class="col-md-4"><label class="form-label">Tuition timings</label><input type="text" name="tuition_timings" class="form-control" value="{{ old('tuition_timings', $educator->tuition_timings) }}"></div>
        <div class="col-md-4"><label class="form-label">Tuition charges</label><input type="text" name="tuition_charges" class="form-control" value="{{ old('tuition_charges', $educator->tuition_charges) }}"></div>
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

    <button type="submit" class="btn btn-primary ems-btn-primary px-4">Save profile</button>
  </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const templates = {
    subject: (i) => `<div class="row g-2 mb-2 js-repeat-row"><div class="col-md-7"><input type="text" name="subjects[${i}][name]" class="form-control" placeholder="Subject name"></div><div class="col-md-4"><select name="subjects[${i}][level]" class="form-select"><option value="primary">Primary</option><option value="secondary">Secondary</option><option value="specialized">Specialized</option></select></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div></div>`,
    qualification: (i) => `<div class="row g-2 mb-2 js-repeat-row"><div class="col-md-4"><input type="text" name="qualifications[${i}][degree]" class="form-control" placeholder="Degree"></div><div class="col-md-4"><input type="text" name="qualifications[${i}][institution]" class="form-control" placeholder="Institution"></div><div class="col-md-3"><input type="text" name="qualifications[${i}][year]" class="form-control" placeholder="Year"></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div></div>`,
    experience: (i) => `<div class="border rounded p-3 mb-2 js-repeat-row"><div class="row g-2"><div class="col-md-4"><input type="text" name="experiences[${i}][title]" class="form-control" placeholder="Title"></div><div class="col-md-4"><input type="text" name="experiences[${i}][organization]" class="form-control" placeholder="Organization"></div><div class="col-md-3"><input type="text" name="experiences[${i}][duration]" class="form-control" placeholder="Duration"></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div><div class="col-12"><textarea name="experiences[${i}][description]" class="form-control" rows="2" placeholder="Description"></textarea></div></div></div>`,
    availability: (i) => `<div class="row g-2 mb-2 js-repeat-row"><div class="col-md-4"><input type="text" name="availability[${i}][day]" class="form-control" placeholder="Day"></div><div class="col-md-7"><input type="text" name="availability[${i}][slots]" class="form-control" placeholder="Time slots"></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 js-remove-row">&times;</button></div></div>`
  };

  document.querySelectorAll('[data-add]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const wrap = document.querySelector(btn.dataset.add);
      const i = wrap.querySelectorAll('.js-repeat-row').length;
      wrap.insertAdjacentHTML('beforeend', templates[btn.dataset.template](i));
    });
  });

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('js-remove-row')) {
      const row = e.target.closest('.js-repeat-row');
      if (row && row.parentElement.querySelectorAll('.js-repeat-row').length > 1) row.remove();
    }
  });

  document.querySelector('form').addEventListener('submit', function () {
    document.querySelectorAll('.js-lines').forEach(function (el) {
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
@endpush
