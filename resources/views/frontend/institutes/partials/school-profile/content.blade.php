<div class="sch-content-main">
    @if($aboutText)
      <section id="sch-about" class="sch-card sch-section">
        <h2 class="sch-section__title">About the {{ $entityLabel ?? 'School' }}</h2>
        <div class="sch-about">
          <div class="sch-about__text js-sch-about-text {{ $aboutNeedsToggle ? 'is-collapsed' : '' }}">{!! nl2br(e($aboutText)) !!}</div>
          @if($aboutNeedsToggle)
            <button type="button" class="sch-read-more js-sch-read-more">Read More <i class="fa-solid fa-chevron-down"></i></button>
          @endif
          <ul class="sch-check-list">
            @foreach($profile->aboutHighlights() as $point)
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $point }}</li>
            @endforeach
          </ul>
        </div>
      </section>
    @endif

    @include('frontend.institutes.partials.school-profile.sections', [
      'onlySection' => null,
      'isProfilePreview' => true,
      'profile' => $profile,
      'institute' => $institute,
      'entityLabel' => $entityLabel ?? 'School',
    ])

    <section id="sch-admission" class="sch-card sch-section sch-admission-section">
      <h2 class="sch-section__title">Admission Info</h2>
      <p class="sch-section__lead">{{ $profile->admissionLead() }}</p>
      @if($profile->admissionPreviewHighlights() !== [])
        <ul class="sch-check-list sch-admission-section__list">
          @foreach($profile->admissionPreviewHighlights() as $point)
            <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $point }}</li>
          @endforeach
        </ul>
      @endif
      @if($profile->admissionHasMore())
        <button type="button" class="sch-read-more js-sch-admission-read-more">
          Read more <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
      @endif
    </section>

    <section id="sch-contact" class="sch-card sch-section sch-enquiry-section">
      <h2 class="sch-section__title">Enquiry &amp; Contact</h2>
      @guest
        <p class="mb-0">Please <a href="{{ route('login') }}">login</a> to send an enquiry to this {{ strtolower($entityLabel ?? 'school') }}.</p>
      @else
        <form
          id="schoolEnquiryForm"
          class="sch-enquiry-form"
          method="post"
          action="{{ route(($listingContext ?? 'schools').'.enquiry', $institute->slug) }}"
          novalidate
        >
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="school_enquiry_name">Your name</label>
              <input type="text" class="form-control" id="school_enquiry_name" name="name" value="{{ $authUser->name }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="school_enquiry_email">Email</label>
              <input type="email" class="form-control" id="school_enquiry_email" name="email" value="{{ $authUser->email }}">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="school_enquiry_phone">Phone</label>
              <input type="text" class="form-control" id="school_enquiry_phone" name="phone" value="{{ $authUser->phone_number }}">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="school_enquiry_subject">Subject</label>
              <input type="text" class="form-control" id="school_enquiry_subject" name="subject" placeholder="Admission enquiry">
            </div>
            <div class="col-12">
              <label class="form-label" for="school_enquiry_message">Message</label>
              <textarea class="form-control" id="school_enquiry_message" name="message" rows="4" required placeholder="Tell us about your enquiry..."></textarea>
            </div>
            <div class="col-12">
              <div id="schoolEnquiryFeedback" class="alert d-none" role="alert"></div>
              <button type="submit" class="sch-btn sch-btn-primary js-school-enquiry-submit">
                <span class="js-enquiry-btn-text">Send enquiry</span>
                <span class="js-enquiry-btn-sending d-none">Sending...</span>
              </button>
            </div>
          </div>
        </form>
      @endguest
    </section>
</div>
