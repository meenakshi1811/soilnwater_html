{{-- Everything else the public page shows (InstituteProfileDetails), with what it shows now --}}
@php
  $details = \App\Support\InstituteProfileDetails::forForm($institute);
  // (a school's words, or a college's or institute's)
  $isSchoolProfile = $institute->user?->isSchool() ?? true;
  $entityName = \App\Support\SchoolInstituteHelper::label($institute->user?->role ?? 'school');
  $detailTextFields = [
    'co_educational_label' => $isSchoolProfile
      ? ['Co-education line', 'e.g. Co-Educational Day School', 'Shown under your name.']
      : ['Line under your name', 'e.g. Coaching for Classes 9–12', 'Shown under your name.'],
    'school_type' => [$entityName.' type', $isSchoolProfile ? 'e.g. Day School, Boarding School' : 'e.g. Coaching Centre, Training Institute', null],
    'medium_of_instruction' => ['Medium of instruction', 'e.g. English', null],
    'campus_area' => ['Campus area', 'e.g. 5 Acres', null],
    'school_code' => [$entityName.' code', 'e.g. 81234', 'Blank: your government certificate number.'],
    'affiliation_number' => ['Affiliation number', 'e.g. 3330456', 'Blank: your government certificate number.'],
    'operating_hours' => ['Operating hours', 'e.g. Mon – Sat: 08:00 AM – 04:00 PM', null],
    'board_result_percent' => ['Board result', 'e.g. 98% Board Result (2025)', 'Shown in the numbers strip.'],
  ];
@endphp
<div class="border-top pt-4 mt-4 inst-page-details">
  <input type="hidden" name="details_in_form" value="1">
  <h3 class="h5 mb-1">Public page details</h3>
  <p class="text-secondary mb-3">Everything else your public profile shows. A line you clear is hidden.</p>

  <section class="inst-detail-group">
    <h4 class="inst-detail-group__title">{{ $entityName }} details</h4>
    <div class="row g-3">
      @foreach($detailTextFields as $key => [$label, $placeholder, $hint])
        <div class="col-md-6 col-xl-4">
          <label class="form-label" for="details_{{ $key }}">{{ $label }}</label>
          <input type="text" class="form-control" id="details_{{ $key }}" name="details[{{ $key }}]" value="{{ $details[$key] }}" maxlength="{{ \App\Support\InstituteProfileDetails::TEXT_FIELDS[$key] }}" placeholder="{{ $placeholder }}">
          @if($hint)
            <div class="form-text">{{ $hint }}</div>
          @endif
        </div>
      @endforeach
    </div>
  </section>

  <section class="inst-detail-group">
    <h4 class="inst-detail-group__title">Numbers strip</h4>
    <div class="row g-3">
      <div class="col-md-6 col-xl-4">
        <label class="form-label" for="details_student_count">Students</label>
        <input type="number" class="form-control" id="details_student_count" name="details[student_count]" value="{{ $details['student_count'] }}" min="0" max="1000000">
      </div>
      <div class="col-md-6 col-xl-4">
        <label class="form-label" for="details_faculty_count">Faculty</label>
        <input type="number" class="form-control" id="details_faculty_count" name="details[faculty_count]" value="{{ $details['faculty_count'] }}" min="0" max="100000">
      </div>
      @if($managedInstitute)
        {{-- (the admin's: a school doesn't rate itself) --}}
        <div class="col-md-6 col-xl-4">
          <label class="form-label" for="details_rating">Star rating <span class="badge text-bg-secondary">Admin</span></label>
          <input type="number" class="form-control" id="details_rating" name="details[rating]" value="{{ $details['rating'] }}" min="0" max="5" step="0.1">
          <div class="form-text">0 to 5. Blank hides the rating.</div>
        </div>
        <div class="col-md-6 col-xl-4">
          <label class="form-label" for="details_review_count">Number of reviews <span class="badge text-bg-secondary">Admin</span></label>
          <input type="number" class="form-control" id="details_review_count" name="details[review_count]" value="{{ $details['review_count'] }}" min="0">
          <div class="form-text">Blank: the reviews added below.</div>
        </div>
        <div class="col-md-6 col-xl-4 d-flex align-items-end">
          <div class="form-check form-switch mb-2">
            <input type="hidden" name="details[is_premium]" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="details_is_premium" name="details[is_premium]" value="1" @checked($details['is_premium'])>
            <label class="form-check-label" for="details_is_premium">“Premium Member” badge <span class="badge text-bg-secondary">Admin</span></label>
          </div>
        </div>
      @endif
    </div>
    <div class="form-text">Blank students or faculty hides that number.</div>
  </section>

  <section class="inst-detail-group">
    <h4 class="inst-detail-group__title">Quick facts</h4>
    @include('backend.institute.partials.page-details-list', ['kind' => 'quick_stats', 'rows' => $details['quick_stats'], 'addLabel' => 'Add fact', 'hint' => 'Small tiles near the top of your page. Each has a name (e.g. Laboratories), a detail shown in bold (e.g. 6 Labs) and an icon; the box on the left shows how it will look.'])
  </section>

  <div class="row g-4">
    <section class="col-lg-6 inst-detail-group">
      <h4 class="inst-detail-group__title">About highlights</h4>
      @include('backend.institute.partials.page-details-list', ['kind' => 'about_highlights', 'rows' => $details['about_highlights'], 'addLabel' => 'Add point', 'hint' => 'The ticked points under “About”.'])
    </section>
    <section class="col-lg-6 inst-detail-group">
      <h4 class="inst-detail-group__title">Why choose us</h4>
      @include('backend.institute.partials.page-details-list', ['kind' => 'why_choose_us', 'rows' => $details['why_choose_us'], 'addLabel' => 'Add point', 'hint' => 'The “Why Choose Us?” box beside your page.'])
    </section>
    <section class="col-lg-6 inst-detail-group">
      <h4 class="inst-detail-group__title">Highlights</h4>
      @include('backend.institute.partials.page-details-list', ['kind' => 'highlights', 'rows' => $details['highlights'], 'addLabel' => 'Add highlight', 'hint' => 'The “Highlights” box. Leave empty to show your facilities there.'])
    </section>
  </div>

  <section class="inst-detail-group">
    <h4 class="inst-detail-group__title">Admission info</h4>
    <div class="mb-3">
      <label class="form-label" for="details_admission_lead">Introduction</label>
      <textarea class="form-control" id="details_admission_lead" name="details[admission_lead]" rows="2" maxlength="500">{{ $details['admission_lead'] }}</textarea>
    </div>
    <label class="form-label">Key points</label>
    @include('backend.institute.partials.page-details-list', ['kind' => 'admission_highlights', 'rows' => $details['admission_highlights'], 'addLabel' => 'Add point', 'hint' => 'The first two show on the page; all of them under “Read more”.'])
    <div class="mt-3">
      <label class="form-label" for="details_admission_details">Full details</label>
      <textarea class="form-control" id="details_admission_details" name="details[admission_details]" rows="8" maxlength="5000">{{ $details['admission_details'] }}</textarea>
      <div class="form-text">Shown under “Read more”: how to apply, documents, contacts.</div>
    </div>
  </section>

  <section class="inst-detail-group">
    <h4 class="inst-detail-group__title">Faculty</h4>
    @include('backend.institute.partials.page-details-list', ['kind' => 'faculty', 'rows' => $details['faculty'], 'addLabel' => 'Add faculty member', 'hint' => 'Teachers who link their own profile to your '.strtolower($entityName).' show too. Leave empty to show your class teachers.'])
  </section>

  <section class="inst-detail-group">
    <h4 class="inst-detail-group__title">Upcoming events</h4>
    @include('backend.institute.partials.page-details-list', ['kind' => 'events', 'rows' => $details['events'], 'addLabel' => 'Add event', 'hint' => 'Past events drop off by themselves. Leave empty to show your active notices as events.'])
  </section>

  <section class="inst-detail-group mb-0">
    <h4 class="inst-detail-group__title">Reviews</h4>
    @if($managedInstitute)
      {{-- (the admin's: which reviews and rating the page shows, and the visitors' reviews) --}}
      @php
        $visitorReviews = $institute->visitorReviews()->with('user:id,name')->get();
      @endphp
      <div class="inst-review-switch mb-3">
        <div class="form-check form-switch mb-1">
          <input type="hidden" name="details[use_admin_reviews]" value="0">
          <input class="form-check-input" type="checkbox" role="switch" id="details_use_admin_reviews" name="details[use_admin_reviews]" value="1" @checked($details['use_admin_reviews'])>
          <label class="form-check-label fw-semibold" for="details_use_admin_reviews">Show the reviews and rating entered here <span class="badge text-bg-secondary">Admin</span></label>
        </div>
        <p class="form-text mb-0">On: the page shows the reviews below and the star rating above. Off: it shows the reviews visitors write on the page, their average rating, and a form for writing one.</p>
      </div>
      <div class="inst-visitor-reviews mb-3">
        <p class="small fw-semibold mb-2">Visitors' reviews ({{ $visitorReviews->count() }})</p>
        @forelse($visitorReviews as $visitorReview)
          <div class="inst-visitor-review js-visitor-review">
            <div class="flex-grow-1">
              <strong>{{ $visitorReview->user?->name ?: 'Visitor' }}</strong>
              <span class="text-secondary small">({{ $visitorReview->relation ?: 'Visitor' }}) · {{ str_repeat('★', (int) $visitorReview->rating) }} · {{ $visitorReview->created_at?->format('j M Y') }}</span>
              @if(filled($visitorReview->review))
                <p class="mb-0 small">{{ $visitorReview->review }}</p>
              @endif
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger js-visitor-review-delete" data-url="{{ $portalRoute('reviews.destroy', $visitorReview) }}">Delete</button>
          </div>
        @empty
          <p class="text-secondary small mb-0">None yet.</p>
        @endforelse
      </div>
      <p class="small fw-semibold mb-2">Reviews entered here</p>
    @endif
    @include('backend.institute.partials.page-details-list', ['kind' => 'reviews', 'rows' => $details['reviews'], 'addLabel' => 'Add review', 'hint' => 'What parents and students say (“Top Reviews”).'])
  </section>
</div>
