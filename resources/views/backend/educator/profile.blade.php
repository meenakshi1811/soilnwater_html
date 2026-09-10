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
  $isTutorProfile = (bool) old('take_tuitions', $educator->take_tuitions);
  $photoUrl = $educator->photoUrl();
@endphp
<div class="admin-panel ems-page edu-profile-page">
  <div class="edu-profile-hero ems-hero">
    <div class="edu-profile-hero__inner">
      <div class="edu-profile-hero__identity">
        @if($photoUrl)
          <img src="{{ $photoUrl }}" alt="" class="edu-profile-hero__avatar">
        @else
          <span class="edu-profile-hero__avatar edu-profile-hero__avatar--placeholder" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
        @endif
        <div>
          <p class="ems-kicker mb-2">Educator Portal</p>
          <h2 class="edu-profile-hero__title">{{ $educator->display_name ?: 'Professional profile' }}</h2>
          <div class="edu-profile-hero__meta">
            <span class="edu-profile-type-badge {{ $isTutorProfile ? 'edu-profile-type-badge--tutor' : 'edu-profile-type-badge--teacher' }}">
              <i class="fa-solid {{ $isTutorProfile ? 'fa-chalkboard-user' : 'fa-school' }}" aria-hidden="true"></i>
              {{ $isTutorProfile ? 'Tutor profile' : 'Experienced teacher profile' }}
            </span>
            @if($educator?->isApproved())
              <span class="badge text-bg-success"><i class="fa-solid fa-circle-check me-1"></i> Approved</span>
            @endif
          </div>
        </div>
      </div>
      @if($educator?->isApproved())
        <a href="{{ $educator->publicUrl() }}" target="_blank" class="btn btn-outline-primary">
          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View public profile
        </a>
      @endif
    </div>
  </div>

  @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
  @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif

  <div id="educatorProfileAlert" class="alert d-none" role="alert"></div>

  @include('backend.partials.parent-profile-toggle', ['user' => $user])

  <div class="edu-profile-layout">
    <nav class="edu-profile-nav" aria-label="Profile sections">
      <p class="edu-profile-nav__label">Sections</p>
      <a href="#edu-section-account" class="edu-profile-nav__link is-active"><i class="fa-solid fa-user"></i> Account</a>
      <a href="#edu-section-about" class="edu-profile-nav__link"><i class="fa-solid fa-chalkboard"></i> About & teaching</a>
      <a href="#edu-section-subjects" class="edu-profile-nav__link"><i class="fa-solid fa-book"></i> Subjects</a>
      <a href="#edu-section-tuition" class="edu-profile-nav__link"><i class="fa-solid fa-indian-rupee-sign"></i> Tuition</a>
      <a href="#edu-section-socials" class="edu-profile-nav__link"><i class="fa-solid fa-share-nodes"></i> Socials</a>
    </nav>

    <div class="edu-profile-content">
      <form id="educatorProfileForm" method="POST" action="{{ route('educator.profile.update') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        <section id="edu-section-account" class="edu-profile-section">
          <header class="edu-profile-section__head">
            <span class="edu-profile-section__icon"><i class="fa-solid fa-id-card" aria-hidden="true"></i></span>
            <div>
              <h3 class="edu-profile-section__title">Account details</h3>
              <p class="edu-profile-section__desc">Your contact and login information — the same required details collected at signup.</p>
            </div>
          </header>

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="profile_photo">Profile photo{{ $photoUrl ? '' : ' *' }}</label>
              <div class="edu-photo-upload">
                @if($photoUrl)
                  <img src="{{ $photoUrl }}" alt="" class="edu-photo-upload__preview" id="eduProfilePhotoPreview">
                @else
                  <span class="edu-photo-upload__placeholder" id="eduProfilePhotoPreview"><i class="fa-solid fa-camera"></i></span>
                @endif
                <div class="edu-photo-upload__input">
                  <input id="profile_photo" type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" {{ $photoUrl ? '' : 'required' }}>
                  <small class="text-muted d-block mt-2">JPG, PNG, or WebP up to 2 MB. Shown on your public profile.</small>
                  @error('profile_photo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            @include('backend.partials.registration-profile-fields', ['profile' => $educator, 'showMarketplaceFields' => false])
          </div>
        </section>

        <section id="edu-section-about" class="edu-profile-section">
          <header class="edu-profile-section__head">
            <span class="edu-profile-section__icon edu-profile-section__icon--green"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></span>
            <div>
              <h3 class="edu-profile-section__title">About & teaching</h3>
              <p class="edu-profile-section__desc">Tell students about your background, qualifications, and teaching approach.</p>
            </div>
          </header>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Professional headline</label>
              <input type="text" name="professional_headline" class="form-control" value="{{ old('professional_headline', $educator->professional_headline) }}" placeholder="e.g. CBSE Physics expert · 12+ years">
            </div>
            <div class="col-md-6">
              <label class="form-label">Associated institute</label>
              <input type="text" name="associated_institute" class="form-control" value="{{ old('associated_institute', $educator->associated_institute) }}" placeholder="Current school or college">
              <input type="hidden" name="institute_latitude" value="{{ old('institute_latitude', $educator->institute_latitude) }}">
              <input type="hidden" name="institute_longitude" value="{{ old('institute_longitude', $educator->institute_longitude) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">State</label>
              <input type="text" name="state" class="form-control" value="{{ old('state', $educator->state) }}">
            </div>
            <div class="col-md-8 d-flex align-items-end">
              <label class="edu-availability-pill w-100 mb-0">
                <input class="form-check-input mt-0 me-2" type="checkbox" name="is_available_now" value="1" id="availNow" @checked(old('is_available_now', $educator->is_available_now))>
                <span><strong>Available now</strong> — show as actively accepting students</span>
              </label>
            </div>
            <div class="col-12">
              <label class="form-label">About teacher</label>
              <textarea name="about" class="form-control" rows="5" placeholder="Introduce yourself, teaching style, and specializations">{{ old('about', $educator->about) }}</textarea>
              <small class="text-muted">The first line is used as the short intro on your public profile.</small>
            </div>
          </div>

          <div class="edu-profile-subsection">
            <div class="edu-profile-subsection__head">
              <div>
                <h4 class="edu-profile-subsection__title">Qualifications</h4>
                <p class="edu-profile-subsection__hint">Degrees, certifications, and academic credentials.</p>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary edu-btn-add" data-add="#qualificationsWrap" data-template="qualification">
                <i class="fa-solid fa-plus"></i> Add qualification
              </button>
            </div>
            <div class="edu-repeat-table-head edu-repeat-table-head--qualification">
              <span>Degree</span><span>Institution</span><span>Year</span><span></span>
            </div>
            <div id="qualificationsWrap">
              @foreach($qualifications as $i => $row)
                <div class="edu-repeat-row edu-repeat-row--qualification js-repeat-row">
                  <input type="text" name="qualifications[{{ $i }}][degree]" class="form-control" placeholder="Degree" value="{{ $row['degree'] ?? '' }}">
                  <input type="text" name="qualifications[{{ $i }}][institution]" class="form-control" placeholder="Institution" value="{{ $row['institution'] ?? '' }}">
                  <input type="text" name="qualifications[{{ $i }}][year]" class="form-control" placeholder="Year" value="{{ $row['year'] ?? '' }}">
                  <button type="button" class="btn btn-outline-danger edu-btn-remove js-remove-row" title="Remove">&times;</button>
                </div>
              @endforeach
            </div>
          </div>

          <div class="edu-profile-subsection">
            <div class="edu-profile-subsection__head">
              <div>
                <h4 class="edu-profile-subsection__title">Experience</h4>
                <p class="edu-profile-subsection__hint">Teaching roles at schools, colleges, or institutes.</p>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary edu-btn-add" data-add="#experiencesWrap" data-template="experience">
                <i class="fa-solid fa-plus"></i> Add experience
              </button>
            </div>
            <div id="experiencesWrap" class="experience-wrap">
              @foreach($experiences as $i => $row)
                @php
                  $isCurrentExperience = filter_var($row['is_current'] ?? false, FILTER_VALIDATE_BOOLEAN);
                @endphp
                <div class="experience-card js-repeat-row">
                  <span class="experience-card__badge"><i class="fa-solid fa-briefcase"></i> Experience {{ $i + 1 }}</span>
                  <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                      <label class="form-label">Job title</label>
                      <input type="text" name="experiences[{{ $i }}][title]" class="form-control" placeholder="Senior Physics Teacher" value="{{ $row['title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Place of work</label>
                      <input type="text" name="experiences[{{ $i }}][organization]" class="form-control js-experience-organization" autocomplete="off" placeholder="Search school or institute name" value="{{ $row['organization'] ?? '' }}">
                      <small class="text-muted">Start typing to search schools via Google.</small>
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
                        <input class="form-check-input js-exp-current" type="checkbox" name="experiences[{{ $i }}][is_current]" value="1" id="experienceCurrent{{ $i }}" @checked($isCurrentExperience)>
                        <label class="form-check-label" for="experienceCurrent{{ $i }}">I still work here</label>
                      </div>
                    </div>
                    <div class="col-md-1">
                      <button type="button" class="btn btn-outline-danger edu-btn-remove w-100 js-remove-row" title="Remove experience">&times;</button>
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

          <div class="edu-profile-subsection">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Teaching method</label>
                <input type="text" name="teaching_method" class="form-control" value="{{ old('teaching_method', $educator->teaching_method) }}" placeholder="e.g. Concept-first, visual learning">
              </div>
              <div class="col-md-6">
                <label class="form-label">Teaching modes</label>
                <textarea class="form-control js-lines" data-name="teaching_modes" rows="3" placeholder="One mode per line">{{ $toLines(old('teaching_modes', $educator->teaching_modes ?? [])) }}</textarea>
                <small class="text-muted">One per line — e.g. Online, Home tuition, Classroom</small>
              </div>
            </div>

            <div class="edu-tag-fields mt-3">
              <div class="edu-tag-field">
                <label class="form-label">Languages</label>
                <textarea class="form-control js-lines" data-name="languages" rows="3" placeholder="One per line">{{ $toLines(old('languages', $educator->languages ?? [])) }}</textarea>
              </div>
              <div class="edu-tag-field">
                <label class="form-label">Classes</label>
                <textarea class="form-control js-lines" data-name="classes" rows="3" placeholder="One per line">{{ $toLines(old('classes', $educator->classes ?? [])) }}</textarea>
              </div>
              <div class="edu-tag-field">
                <label class="form-label">Boards</label>
                <textarea class="form-control js-lines" data-name="boards" rows="3" placeholder="One per line">{{ $toLines(old('boards', $educator->boards ?? [])) }}</textarea>
              </div>
            </div>

            <div class="edu-stats-grid mt-3">
              <div class="edu-stat-field">
                <div class="edu-stat-field__label"><i class="fa-solid fa-clock"></i> Years experience</div>
                <input type="number" name="years_experience" class="form-control" min="0" value="{{ old('years_experience', $educator->years_experience) }}">
              </div>
              <div class="edu-stat-field">
                <div class="edu-stat-field__label"><i class="fa-solid fa-user-graduate"></i> Students taught</div>
                <input type="number" name="students_taught" class="form-control" min="0" value="{{ old('students_taught', $educator->students_taught) }}">
              </div>
              <div class="edu-stat-field">
                <div class="edu-stat-field__label"><i class="fa-solid fa-chart-line"></i> Success rate %</div>
                <input type="number" step="0.01" name="success_rate" class="form-control" value="{{ old('success_rate', $educator->success_rate) }}">
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label">Service area</label>
              <textarea class="form-control js-lines" data-name="service_area" rows="2" placeholder="One area per line">{{ $toLines(old('service_area', $educator->service_area ?? [])) }}</textarea>
            </div>
          </div>
        </section>

        <section id="edu-section-subjects" class="edu-profile-section">
          <header class="edu-profile-section__head">
            <span class="edu-profile-section__icon edu-profile-section__icon--amber"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
            <div>
              <h3 class="edu-profile-section__title">Subjects</h3>
              <p class="edu-profile-section__desc">Subjects you teach and their difficulty level.</p>
            </div>
          </header>

          <div class="edu-profile-subsection__head mb-2">
            <div></div>
            <button type="button" class="btn btn-sm btn-outline-primary edu-btn-add" data-add="#subjectsWrap" data-template="subject">
              <i class="fa-solid fa-plus"></i> Add subject
            </button>
          </div>
          <div class="edu-repeat-table-head edu-repeat-table-head--subjects">
            <span>Subject</span><span>Level</span><span></span>
          </div>
          <div id="subjectsWrap">
            @foreach($subjects as $i => $subject)
              <div class="edu-repeat-row edu-repeat-row--subject js-repeat-row">
                <input type="text" name="subjects[{{ $i }}][name]" class="form-control" placeholder="Subject name" value="{{ is_array($subject) ? ($subject['name'] ?? '') : $subject }}">
                <select name="subjects[{{ $i }}][level]" class="form-select">
                  @foreach(['primary','secondary','specialized'] as $level)
                    <option value="{{ $level }}" @selected((is_array($subject) ? ($subject['level'] ?? 'primary') : 'primary') === $level)>{{ ucfirst($level) }}</option>
                  @endforeach
                </select>
                <button type="button" class="btn btn-outline-danger edu-btn-remove js-remove-row" title="Remove">&times;</button>
              </div>
            @endforeach
          </div>
        </section>

        <section id="edu-section-tuition" class="edu-profile-section">
          <header class="edu-profile-section__head">
            <span class="edu-profile-section__icon"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i></span>
            <div>
              <h3 class="edu-profile-section__title">Tuition details</h3>
              <p class="edu-profile-section__desc">Configure batches, fees, and availability for private tuition.</p>
            </div>
          </header>

          <div class="edu-toggle-card mb-4">
            <input class="form-check-input" type="checkbox" name="take_tuitions" value="1" id="takeTuitions" @checked(old('take_tuitions', $educator->take_tuitions))>
            <div>
              <div class="edu-toggle-card__title">I take tuitions</div>
              <p class="edu-toggle-card__desc">When enabled, your public page is shown as a <strong>tutor profile</strong> with fees and batch details. When disabled, it appears as an <strong>experienced teacher profile</strong>.</p>
            </div>
          </div>

          <div class="edu-profile-subsection">
            <div class="edu-profile-subsection__head">
              <div>
                <h4 class="edu-profile-subsection__title">Tuition batches</h4>
                <p class="edu-profile-subsection__hint">Add each class batch with subject, type, student count, and cost.</p>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary edu-btn-add" data-add="#tuitionBatchesWrap" data-template="tuitionBatch">
                <i class="fa-solid fa-plus"></i> Add batch
              </button>
            </div>

            <div class="tuition-batches-table d-none d-md-grid text-muted small fw-semibold px-3 py-2 mb-2">
              <span>Class</span><span>Subject</span><span>Batch type</span><span>Students</span><span>Cost</span><span></span>
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
                      <button type="button" class="btn btn-outline-danger edu-btn-remove w-100 js-remove-row" title="Remove batch">&times;</button>
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

          <div class="row g-3 mt-1">
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
              <input type="text" name="tuition_charges" class="form-control" value="{{ old('tuition_charges', $educator->tuition_charges) }}" placeholder="Trial class, registration fee, package details">
            </div>
          </div>

          <div class="edu-profile-subsection">
            <div class="edu-profile-subsection__head">
              <div>
                <h4 class="edu-profile-subsection__title">Availability</h4>
                <p class="edu-profile-subsection__hint">When you're available for classes or consultations.</p>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary edu-btn-add" data-add="#availabilityWrap" data-template="availability">
                <i class="fa-solid fa-plus"></i> Add slot
              </button>
            </div>
            <div class="edu-repeat-table-head edu-repeat-table-head--availability">
              <span>Day</span><span>Time slots</span><span></span>
            </div>
            <div id="availabilityWrap">
              @foreach($availability as $i => $row)
                <div class="edu-repeat-row edu-repeat-row--availability js-repeat-row">
                  <input type="text" name="availability[{{ $i }}][day]" class="form-control" placeholder="Monday" value="{{ $row['day'] ?? '' }}">
                  <input type="text" name="availability[{{ $i }}][slots]" class="form-control" placeholder="4:00 PM – 7:00 PM" value="{{ $row['slots'] ?? '' }}">
                  <button type="button" class="btn btn-outline-danger edu-btn-remove js-remove-row" title="Remove">&times;</button>
                </div>
              @endforeach
            </div>
          </div>
        </section>

        <section id="edu-section-socials" class="edu-profile-section">
          <header class="edu-profile-section__head">
            <span class="edu-profile-section__icon edu-profile-section__icon--violet"><i class="fa-solid fa-award" aria-hidden="true"></i></span>
            <div>
              <h3 class="edu-profile-section__title">Achievements & social links</h3>
              <p class="edu-profile-section__desc">Highlight awards and connect your social profiles.</p>
            </div>
          </header>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Achievements</label>
              <textarea class="form-control js-lines" data-name="achievements" rows="4" placeholder="One achievement per line">{{ $toLines(old('achievements', $educator->achievements ?? [])) }}</textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Certifications</label>
              <textarea class="form-control js-lines" data-name="certifications" rows="4" placeholder="One certification per line">{{ $toLines(old('certifications', $educator->certifications ?? [])) }}</textarea>
            </div>
          </div>

          <div class="edu-social-grid">
            <div class="edu-social-input">
              <i class="fa-brands fa-facebook edu-social-input__icon"></i>
              <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $educator->facebook_url) }}" placeholder="Facebook profile URL">
            </div>
            <div class="edu-social-input">
              <i class="fa-brands fa-instagram edu-social-input__icon"></i>
              <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $educator->instagram_url) }}" placeholder="Instagram profile URL">
            </div>
            <div class="edu-social-input">
              <i class="fa-brands fa-youtube edu-social-input__icon"></i>
              <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $educator->youtube_url) }}" placeholder="YouTube channel URL">
            </div>
            <div class="edu-social-input">
              <i class="fa-brands fa-linkedin edu-social-input__icon"></i>
              <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $educator->linkedin_url) }}" placeholder="LinkedIn profile URL">
            </div>
            <div class="edu-social-input">
              <i class="fa-brands fa-whatsapp edu-social-input__icon"></i>
              <input type="url" name="whatsapp_url" class="form-control" value="{{ old('whatsapp_url', $educator->whatsapp_url) }}" placeholder="WhatsApp link">
            </div>
          </div>
        </section>

        <div class="edu-profile-savebar">
          <p class="edu-profile-savebar__hint"><i class="fa-solid fa-circle-info me-1"></i> Changes are saved to your public profile after you click Save.</p>
          <div class="d-flex gap-2">
            <a href="{{ route('educator.dashboard') }}" class="btn btn-outline-secondary">Back</a>
            <button id="educatorProfileSubmitBtn" type="submit" class="btn btn-primary ems-btn-primary px-4">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save changes
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/educator-portal-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/parent-profile.js') }}?v={{ now()->timestamp }}"></script>
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
    if (!row) return;
    const checkbox = row.querySelector('.js-exp-current');
    const endSelect = row.querySelector('.js-exp-end-year');
    if (!checkbox || !endSelect) return;
    endSelect.disabled = checkbox.checked;
    if (checkbox.checked) endSelect.value = '';
  };

  const renumberExperienceBadges = function () {
    document.querySelectorAll('#experiencesWrap .experience-card').forEach(function (card, index) {
      const badge = card.querySelector('.experience-card__badge');
      if (badge) badge.innerHTML = '<i class="fa-solid fa-briefcase"></i> Experience ' + (index + 1);
    });
  };

  const templates = {
    subject: (i) => `<div class="edu-repeat-row edu-repeat-row--subject js-repeat-row"><input type="text" name="subjects[${i}][name]" class="form-control" placeholder="Subject name"><select name="subjects[${i}][level]" class="form-select"><option value="primary">Primary</option><option value="secondary">Secondary</option><option value="specialized">Specialized</option></select><button type="button" class="btn btn-outline-danger edu-btn-remove js-remove-row" title="Remove">&times;</button></div>`,
    qualification: (i) => `<div class="edu-repeat-row edu-repeat-row--qualification js-repeat-row"><input type="text" name="qualifications[${i}][degree]" class="form-control" placeholder="Degree"><input type="text" name="qualifications[${i}][institution]" class="form-control" placeholder="Institution"><input type="text" name="qualifications[${i}][year]" class="form-control" placeholder="Year"><button type="button" class="btn btn-outline-danger edu-btn-remove js-remove-row" title="Remove">&times;</button></div>`,
    experience: (i) => `<div class="experience-card js-repeat-row"><span class="experience-card__badge"><i class="fa-solid fa-briefcase"></i> Experience ${i + 1}</span><div class="row g-2 align-items-end"><div class="col-md-6"><label class="form-label">Job title</label><input type="text" name="experiences[${i}][title]" class="form-control" placeholder="Senior Physics Teacher"></div><div class="col-md-6"><label class="form-label">Place of work</label><input type="text" name="experiences[${i}][organization]" class="form-control js-experience-organization" autocomplete="off" placeholder="Search school or institute name"><small class="text-muted">Start typing to search schools via Google.</small></div><div class="col-md-3"><label class="form-label">Start year</label><select name="experiences[${i}][start_year]" class="form-select">${experienceYearOptions('')}</select></div><div class="col-md-3"><label class="form-label">End year</label><select name="experiences[${i}][end_year]" class="form-select js-exp-end-year">${experienceYearOptions('')}</select></div><div class="col-md-5"><div class="form-check experience-current-check"><input class="form-check-input js-exp-current" type="checkbox" name="experiences[${i}][is_current]" value="1" id="experienceCurrent${i}"><label class="form-check-label" for="experienceCurrent${i}">I still work here</label></div></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger edu-btn-remove w-100 js-remove-row" title="Remove experience">&times;</button></div><div class="col-12"><label class="form-label">Description</label><textarea name="experiences[${i}][description]" class="form-control" rows="2" placeholder="Teaching responsibilities, achievements, and role details"></textarea></div></div></div>`,
    availability: (i) => `<div class="edu-repeat-row edu-repeat-row--availability js-repeat-row"><input type="text" name="availability[${i}][day]" class="form-control" placeholder="Monday"><input type="text" name="availability[${i}][slots]" class="form-control" placeholder="4:00 PM – 7:00 PM"><button type="button" class="btn btn-outline-danger edu-btn-remove js-remove-row" title="Remove">&times;</button></div>`,
    tuitionBatch: (i) => `<div class="tuition-batch-card js-repeat-row"><div class="tuition-batch-card__grid"><div><label class="form-label d-md-none">Class</label><input type="text" name="tuition_batches[${i}][class]" class="form-control" placeholder="Class 10"></div><div><label class="form-label d-md-none">Subject</label><input type="text" name="tuition_batches[${i}][subject]" class="form-control" placeholder="Physics"></div><div><label class="form-label d-md-none">Batch type</label><input type="text" name="tuition_batches[${i}][batch_type]" class="form-control" placeholder="Small group" list="tuitionBatchTypeOptions"></div><div><label class="form-label d-md-none">Students</label><input type="number" name="tuition_batches[${i}][student_count]" class="form-control" min="1" placeholder="8"></div><div><label class="form-label d-md-none">Cost</label><input type="text" name="tuition_batches[${i}][cost]" class="form-control" placeholder="₹500 / month"></div><div class="tuition-batch-card__actions"><button type="button" class="btn btn-outline-danger edu-btn-remove w-100 js-remove-row" title="Remove batch">&times;</button></div></div></div>`
  };

  document.querySelectorAll('[data-add]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const wrap = document.querySelector(btn.dataset.add);
      const i = wrap.querySelectorAll('.js-repeat-row').length;
      wrap.insertAdjacentHTML('beforeend', templates[btn.dataset.template](i));
      if (btn.dataset.template === 'experience') {
        syncExperienceCurrentState(wrap.lastElementChild);
        initExperienceOrganizationSearch();
        renumberExperienceBadges();
      }
    });
  });

  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('js-exp-current')) {
      syncExperienceCurrentState(e.target.closest('.js-repeat-row'));
    }
    if (e.target.id === 'takeTuitions') {
      const badge = document.querySelector('.edu-profile-type-badge');
      if (!badge) return;
      badge.classList.toggle('edu-profile-type-badge--tutor', e.target.checked);
      badge.classList.toggle('edu-profile-type-badge--teacher', !e.target.checked);
      badge.innerHTML = e.target.checked
        ? '<i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i> Tutor profile'
        : '<i class="fa-solid fa-school" aria-hidden="true"></i> Experienced teacher profile';
    }
  });

  document.querySelectorAll('#experiencesWrap .js-repeat-row').forEach(syncExperienceCurrentState);
  initExperienceOrganizationSearch();

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('js-remove-row')) {
      const row = e.target.closest('.js-repeat-row');
      const wrap = row?.parentElement;
      if (row && wrap && wrap.querySelectorAll('.js-repeat-row').length > 1) {
        row.remove();
        if (wrap.id === 'experiencesWrap') renumberExperienceBadges();
      }
    }
  });

  const profilePhotoInput = document.getElementById('profile_photo');
  if (profilePhotoInput) {
    profilePhotoInput.addEventListener('change', function () {
      const file = this.files && this.files[0];
      const preview = document.getElementById('eduProfilePhotoPreview');
      if (!file || !preview) return;
      const reader = new FileReader();
      reader.onload = function (event) {
        if (preview.tagName === 'IMG') {
          preview.src = event.target.result;
        } else {
          const img = document.createElement('img');
          img.src = event.target.result;
          img.alt = '';
          img.className = 'edu-photo-upload__preview';
          img.id = 'eduProfilePhotoPreview';
          preview.replaceWith(img);
        }
      };
      reader.readAsDataURL(file);
    });
  }

  const navLinks = document.querySelectorAll('.edu-profile-nav__link');
  const sections = document.querySelectorAll('.edu-profile-section[id]');
  navLinks.forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(link.getAttribute('href'));
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      navLinks.forEach(function (item) { item.classList.remove('is-active'); });
      link.classList.add('is-active');
    });
  });

  if ('IntersectionObserver' in window && sections.length) {
    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        const id = entry.target.getAttribute('id');
        navLinks.forEach(function (link) {
          link.classList.toggle('is-active', link.getAttribute('href') === '#' + id);
        });
      });
    }, { rootMargin: '-30% 0px -55% 0px', threshold: 0 });
    sections.forEach(function (section) { observer.observe(section); });
  }
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
