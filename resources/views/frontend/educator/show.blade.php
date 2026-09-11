@extends('frontend.layouts.app')

@section('meta_title', $educator->display_name.' · '.$educator->roleLabel().' | SoilnWater')
@section('meta_description', $educator->publicProfileMetaDescription())

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/educator-module.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/educator-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
  $photo = $educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp');
  $modes = collect($educator->teaching_modes ?? []);
  $languages = collect($educator->languages ?? []);
  $subjects = collect($educator->subjects ?? []);
  $classes = collect($educator->classes ?? []);
  $boards = collect($educator->boards ?? []);
  $experiences = collect($educator->experiences ?? []);
  $qualifications = collect($educator->qualifications ?? []);
  $achievements = collect($educator->achievements ?? []);
  $certifications = collect($educator->certifications ?? []);
  $availability = collect($educator->availability ?? []);
  $primarySubject = $educator->primarySubject() ?: 'General';
  $shareUrl = $educator->publicUrl();
  $subjectIcons = ['fa-book-open', 'fa-flask', 'fa-calculator', 'fa-globe', 'fa-language', 'fa-laptop-code', 'fa-atom', 'fa-palette'];
  $testimonials = collect($testimonials ?? []);
  $aboutText = trim((string) $educator->about);
  $aboutNeedsToggle = strlen($aboutText) > 280;
  $isTutorProfile = $educator->isTutor();

  $navItems = collect([
    ['id' => 'edu-overview', 'label' => 'Profile Overview', 'icon' => 'fa-user'],
    ['id' => 'edu-about', 'label' => 'About Me', 'icon' => 'fa-circle-info'],
    ['id' => 'edu-subjects', 'label' => 'Subjects & Classes', 'icon' => 'fa-book'],
    ['id' => 'edu-experience', 'label' => 'Experience & Education', 'icon' => 'fa-briefcase'],
    ['id' => 'edu-courses', 'label' => 'Courses', 'icon' => 'fa-graduation-cap'],
    ['id' => 'edu-notes', 'label' => 'Notes & Materials', 'icon' => 'fa-file-lines'],
    ['id' => 'edu-papers', 'label' => 'Question Papers', 'icon' => 'fa-file-circle-question'],
    ['id' => 'edu-reviews', 'label' => 'Students & Reviews', 'icon' => 'fa-star'],
    ['id' => 'edu-achievements', 'label' => 'Achievements', 'icon' => 'fa-trophy'],
    ['id' => 'edu-articles', 'label' => 'Articles', 'icon' => 'fa-newspaper'],
    ['id' => 'edu-gallery', 'label' => 'Gallery', 'icon' => 'fa-images'],
    ['id' => 'edu-availability', 'label' => $isTutorProfile ? 'Availability & Tuition' : 'Availability & Locations', 'icon' => 'fa-calendar-check'],
    ['id' => 'edu-fees', 'label' => 'Fees & Packages', 'icon' => 'fa-indian-rupee-sign', 'tutor_only' => true],
    ['id' => 'edu-question', 'label' => 'Ask a Question', 'icon' => 'fa-circle-question'],
    ['id' => 'edu-contact', 'label' => 'Contact & Enquiry', 'icon' => 'fa-envelope'],
  ])->filter(fn ($item) => empty($item['tutor_only']) || $isTutorProfile)->values()->all();
@endphp

<div
  class="edu-page {{ $isTutorProfile ? 'edu-page--tutor' : 'edu-page--teacher' }}"
  id="educatorProfilePage"
  data-share-url="{{ $shareUrl }}"
  data-share-title="{{ $educator->display_name }} · {{ $educator->roleLabel() }}"
  data-share-text="Check out {{ $educator->display_name }} on SoilnWater"
  data-login-url="{{ route('login') }}"
  data-is-auth="{{ auth()->check() ? '1' : '0' }}"
  data-enquiry-url="{{ route('educator.enquiry', $educator->slug) }}"
>
  <div class="container-fluid edu-container">
    @if(session('status'))
      <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <nav class="edu-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <span class="edu-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route('educator.index') }}">Teachers &amp; Tutors</a>
      <span class="edu-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route('educator.listings', array_filter(['subject' => $primarySubject, 'takes_tuitions' => $isTutorProfile ? '1' : '0'])) }}">{{ $primarySubject }} {{ $educator->publicListingLabel() }}</a>
      <span class="edu-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="edu-breadcrumb__current" aria-current="page">{{ $educator->display_name }}</span>
    </nav>

    {{-- Mobile horizontal nav --}}
    <div class="edu-nav-mobile">
      <div class="edu-nav-mobile__inner">
        @foreach($navItems as $item)
          <a href="#{{ $item['id'] }}" class="edu-nav-mobile__link js-edu-nav-link">
            <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
            {{ $item['label'] }}
          </a>
        @endforeach
      </div>
    </div>

    <div class="edu-grid">
      {{-- Left sticky nav --}}
      <aside class="edu-nav" aria-label="Profile sections">
        <div class="edu-nav__inner">
          <ul class="edu-nav__list">
            @foreach($navItems as $loopIndex => $item)
              <li>
                <a
                  href="#{{ $item['id'] }}"
                  class="edu-nav__link js-edu-nav-link {{ $loopIndex === 0 ? 'is-active' : '' }}"
                >
                  <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                  {{ $item['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
          <div class="edu-nav__share">
            <button type="button" class="edu-btn edu-btn-outline js-edu-share-profile">
              <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Share Profile
            </button>
          </div>
        </div>
      </aside>

      {{-- Center main content --}}
      <main class="edu-main">
        {{-- 1. Profile Overview --}}
        <section class="edu-section edu-overview" id="edu-overview">
          <div class="edu-overview__grid">
            <img src="{{ $photo }}" alt="{{ $educator->display_name }}" class="edu-overview__photo">

            <div>
              @if($educator->isVerified())
                <span class="edu-overview__badge {{ $isTutorProfile ? 'edu-overview__badge--tutor' : 'edu-overview__badge--teacher' }}"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $educator->verifiedBadgeLabel() }}</span>
              @else
                <span class="edu-overview__badge edu-overview__badge--muted {{ $isTutorProfile ? 'edu-overview__badge--tutor' : 'edu-overview__badge--teacher' }}">{{ $educator->roleLabel() }}</span>
              @endif

              <h1 class="edu-overview__name">
                {{ $educator->display_name }}
                @if($educator->isVerified())
                  <i class="fa-solid fa-star" aria-hidden="true" title="Verified educator"></i>
                @endif
              </h1>

              <p class="edu-overview__headline">{{ $educator->professional_headline ?: $educator->publicHeadlineFallback() }}</p>
              @if($isTutorProfile)
                <p class="edu-overview__profile-type"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i> Tuition profile · private and batch classes</p>
              @else
                <p class="edu-overview__profile-type"><i class="fa-solid fa-school" aria-hidden="true"></i> Experienced teacher profile · school and institute teaching</p>
              @endif
              @if($educator->publicTagline())
                <p class="edu-overview__tagline">{{ $educator->publicTagline() }}</p>
              @endif

              <div class="edu-overview__rating">
                @for($s = 1; $s <= 5; $s++)
                  <i class="fa-{{ $s <= (int) round((float) $educator->average_rating) ? 'solid' : 'regular' }} fa-star" aria-hidden="true"></i>
                @endfor
                <strong class="js-edu-avg-rating">{{ number_format((float) $educator->average_rating, 1) }}</strong>
                <span>(<span class="js-edu-reviews-count">{{ number_format($educator->reviews_count) }}</span> reviews)</span>
              </div>

              <div class="edu-overview__stats">
                <div class="edu-overview__stat">
                  <strong>{{ $educator->years_experience }}+</strong>
                  <span>Years</span>
                </div>
                <div class="edu-overview__stat">
                  <strong>{{ number_format($educator->students_taught) }}</strong>
                  <span>Students</span>
                </div>
                <div class="edu-overview__stat">
                  <strong>{{ $educator->success_rate !== null ? $educator->success_rate.'%' : '—' }}</strong>
                  <span>Success Rate</span>
                </div>
              </div>
            </div>

            <div class="edu-overview__actions-col">
              @if($educator->locationLabel())
                <div class="edu-overview__meta-item">
                  <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                  <span>{{ $educator->locationLabel() }}</span>
                </div>
              @endif

              @if($modes->isNotEmpty())
                <div class="edu-pill-row">
                  @foreach($modes as $mode)
                    <span class="edu-pill edu-pill--mode">{{ $mode }}</span>
                  @endforeach
                </div>
              @endif

              @if($languages->isNotEmpty())
                <div class="edu-pill-row">
                  @foreach($languages as $language)
                    <span class="edu-pill">{{ $language }}</span>
                  @endforeach
                </div>
              @endif

              <button type="button" class="edu-btn edu-btn-primary js-edu-open-enquiry">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i> Send Enquiry
              </button>
              @if($isTutorProfile)
                <button type="button" class="edu-btn edu-btn-outline js-edu-open-enquiry">
                  <i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book a Session
                </button>
              @endif
              @auth
                <button
                  type="button"
                  class="edu-btn edu-btn-outline js-edu-follow {{ $isFollowing ? 'is-following' : '' }}"
                  data-url="{{ route('educator.follow', $educator->slug) }}"
                  data-label-follow="Follow"
                  data-label-following="Following"
                >
                  <i class="fa-solid fa-heart" aria-hidden="true"></i>
                  <span class="js-edu-follow-label">{{ $isFollowing ? 'Following' : 'Follow' }}</span>
                </button>
              @else
                <button type="button" class="edu-btn edu-btn-outline js-edu-guest-action" data-action="follow">
                  <i class="fa-solid fa-heart" aria-hidden="true"></i> Follow
                </button>
              @endauth
            </div>
          </div>
        </section>

        {{-- 2. About Me --}}
        <section class="edu-section" id="edu-about">
          <h2 class="edu-section__title"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> About Me</h2>
          @if($aboutText !== '')
            <p class="edu-about__text js-edu-about-text {{ $aboutNeedsToggle ? 'is-collapsed' : '' }}">{{ $aboutText }}</p>
            @if($aboutNeedsToggle)
              <button type="button" class="edu-read-more js-edu-read-more">
                Read More <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
              </button>
            @endif
            @if($educator->teaching_method)
              <p class="mt-3 mb-0"><strong>Teaching method:</strong> {{ $educator->teaching_method }}</p>
            @endif
          @else
            <p class="edu-empty">No about information yet.</p>
          @endif
        </section>

        {{-- 3. Subjects & Classes --}}
        <section class="edu-section" id="edu-subjects">
          <h2 class="edu-section__title"><i class="fa-solid fa-book" aria-hidden="true"></i> Subjects &amp; Classes</h2>
          @if($subjects->isNotEmpty())
            <div class="edu-subjects-grid">
              @foreach($subjects as $index => $subject)
                @php
                  $name = is_array($subject) ? ($subject['name'] ?? '') : $subject;
                  $level = is_array($subject) ? ($subject['level'] ?? 'primary') : 'primary';
                  $icon = $subjectIcons[$index % count($subjectIcons)];
                @endphp
                @if($name)
                  <div class="edu-subject-card">
                    <span class="edu-subject-card__icon edu-subject-card__icon--{{ $level }}">
                      <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                    </span>
                    <strong>{{ $name }}</strong>
                    <span>{{ ucfirst($level) }}</span>
                  </div>
                @endif
              @endforeach
            </div>
          @endif

          @if($classes->isNotEmpty())
            <p class="edu-classes-label">Classes I Teach</p>
            <div class="edu-chip-row">
              @foreach($classes as $class)
                <span class="edu-chip">{{ $class }}</span>
              @endforeach
            </div>
          @endif

          @if($boards->isNotEmpty())
            <p class="edu-classes-label mt-3">Boards</p>
            <div class="edu-chip-row">
              @foreach($boards as $board)
                <span class="edu-chip">{{ $board }}</span>
              @endforeach
            </div>
          @endif

          @if($subjects->isEmpty() && $classes->isEmpty() && $boards->isEmpty())
            <p class="edu-empty">Subjects and classes will appear here once added.</p>
          @endif
        </section>

        {{-- 4. Experience & Education --}}
        <section class="edu-section" id="edu-experience">
          <h2 class="edu-section__title"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Experience &amp; Education</h2>

          <div class="edu-exp-stats">
            <div class="edu-exp-stat">
              <i class="fa-solid fa-clock" aria-hidden="true"></i>
              <strong>{{ $educator->years_experience }}+</strong>
              <span>Years Experience</span>
            </div>
            <div class="edu-exp-stat">
              <i class="fa-solid fa-users" aria-hidden="true"></i>
              <strong>{{ number_format($educator->students_taught) }}</strong>
              <span>Students Taught</span>
            </div>
            <div class="edu-exp-stat">
              <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
              <strong>{{ $educator->success_rate !== null ? $educator->success_rate.'%' : '—' }}</strong>
              <span>Success Rate</span>
            </div>
            <div class="edu-exp-stat">
              <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
              <strong>{{ number_format($educator->approved_materials_count) }}</strong>
              <span>Materials</span>
            </div>
          </div>

          <div class="edu-exp-grid">
            <div>
              <h4>Experience</h4>
              @forelse($experiences as $exp)
                <div class="edu-timeline-item">
                  <strong>{{ $exp['title'] ?? 'Experience' }}</strong>
                  <span class="text-muted">{{ collect([$exp['organization'] ?? null, \App\Models\Educator::experienceDurationLabel($exp) ?: null])->filter()->implode(' · ') }}</span>
                  @if(!empty($exp['description']))
                    <p class="mb-0 mt-1">{{ $exp['description'] }}</p>
                  @endif
                </div>
              @empty
                <p class="edu-empty">No experience listed yet.</p>
              @endforelse
            </div>
            <div>
              <h4>Education</h4>
              @forelse($qualifications as $qual)
                <div class="edu-edu-item">
                  <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                  <div>
                    <strong>{{ $qual['degree'] ?? 'Qualification' }}</strong>
                    <div class="text-muted small">{{ collect([$qual['institution'] ?? null, $qual['year'] ?? null])->filter()->implode(' · ') }}</div>
                  </div>
                </div>
              @empty
                <p class="edu-empty">No education listed yet.</p>
              @endforelse
            </div>
          </div>
        </section>

        {{-- 5. Courses --}}
        <section class="edu-section" id="edu-courses">
          <div class="edu-section__head">
            <h2 class="edu-section__title"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Courses</h2>
            @if(($coursesTotal ?? $courses->count()) > 3)
              <a href="{{ route('educator.courses', $educator->slug) }}" class="edu-section__link">
                View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </a>
            @endif
          </div>
          @if($courses->isNotEmpty())
            <div class="edu-courses-grid edu-courses-grid--preview">
              @foreach($courses as $course)
                @include('frontend.educator.partials.course-card', ['course' => $course])
              @endforeach
            </div>
          @else
            <p class="edu-empty">No courses published yet.</p>
          @endif
        </section>

        {{-- 6. Notes & Materials --}}
        <section class="edu-section" id="edu-notes">
          <div class="edu-section__head">
            <h2 class="edu-section__title"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Notes &amp; Materials</h2>
            @if(($notesTotal ?? $notes->count()) > 3)
              <a href="{{ route('educator.notes', $educator->slug) }}" class="edu-section__link">
                View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </a>
            @endif
          </div>
          @if($notes->isNotEmpty())
            <div class="edu-notes-grid edu-notes-grid--preview">
              @foreach($notes as $note)
                @include('frontend.educator.partials.note-card', ['note' => $note])
              @endforeach
            </div>
          @else
            <p class="edu-empty">No notes published yet.</p>
          @endif
        </section>

        {{-- 7. Question Papers --}}
        <section class="edu-section" id="edu-papers">
          <div class="edu-section__head">
            <h2 class="edu-section__title"><i class="fa-solid fa-file-circle-question" aria-hidden="true"></i> Question Papers</h2>
            @if(($questionPapersTotal ?? $questionPapers->count()) > 3)
              <a href="{{ route('educator.question-papers', $educator->slug) }}" class="edu-section__link">
                View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </a>
            @endif
          </div>
          @if($questionPapers->isNotEmpty())
            <div class="edu-notes-grid edu-notes-grid--preview">
              @foreach($questionPapers as $paper)
                @include('frontend.educator.partials.note-card', ['note' => $paper, 'materialLabel' => 'Question Paper'])
              @endforeach
            </div>
          @else
            <p class="edu-empty">No question papers published yet.</p>
          @endif
        </section>

        {{-- 9. Students & Reviews --}}
        <section class="edu-section" id="edu-reviews" data-review-url="{{ route('educator.review', $educator->slug) }}">
          <h2 class="edu-section__title"><i class="fa-solid fa-star" aria-hidden="true"></i> Students &amp; Reviews</h2>

          <div class="edu-testimonials js-edu-testimonial-carousel {{ $testimonials->isEmpty() ? 'is-empty' : '' }}" id="eduTestimonialCarousel">
            <div class="edu-testimonials__head">
              <h3 class="h6 mb-0">What Students Say</h3>
              <div class="edu-testimonials__nav">
                <button type="button" class="edu-testimonials__btn js-edu-testimonial-prev" aria-label="Previous testimonial">
                  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="edu-testimonials__btn js-edu-testimonial-next" aria-label="Next testimonial">
                  <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="edu-testimonials__viewport">
              <div class="edu-testimonials__track js-edu-testimonial-track" id="eduTestimonialTrack">
                @foreach($testimonials as $item)
                  @include('frontend.educator.partials.testimonial-item', ['item' => $item])
                @endforeach
              </div>
            </div>
          </div>

          <div
            id="educatorReviewsSection"
            data-review-url="{{ route('educator.review', $educator->slug) }}"
            data-reviews-url="{{ route('educator.reviews', $educator->slug) }}"
            data-reviews-total="{{ (int) ($profileReviewsTotal ?? 0) }}"
          >
            <h3 class="h6 mb-3">All Reviews</h3>
            <p class="edu-empty mb-3">
              <span class="js-edu-avg-rating">{{ number_format((float) $educator->average_rating, 1) }}</span>
              average ·
              <span class="js-edu-reviews-count">{{ number_format($educator->reviews_count) }}</span>
              reviews (profile + study materials)
            </p>

            @auth
              @php
                $selectedRating = (int) old('rating', $userReview?->rating ?: 5);
                $ratingLabels = [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very good', 5 => 'Excellent'];
                $reviewText = old('review', $userReview?->review ?? '');
              @endphp
              <form id="educatorReviewForm" class="edu-review-form mb-4" novalidate>
                @csrf
                <div class="edu-review-form__header">
                  <div class="edu-review-form__icon" aria-hidden="true">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </div>
                  <div>
                    <h4 class="edu-review-form__title">{{ ($userReview ?? null) ? 'Update your review' : 'Write a review' }}</h4>
                    <p class="edu-review-form__hint">Share your experience with this {{ strtolower($educator->roleLabel()) }} and help other students decide.</p>
                  </div>
                </div>

                <div class="edu-review-form__rating-block">
                  <span class="edu-review-form__label">How would you rate them?</span>
                  <div class="edu-review-form__rating-row">
                    <div class="edu-star-picker" role="radiogroup" aria-label="Your rating">
                      <input type="hidden" name="rating" id="educatorReviewRating" value="{{ $selectedRating }}">
                      @foreach (range(1, 5) as $stars)
                        <button
                          type="button"
                          class="edu-star-picker__btn {{ $stars <= $selectedRating ? 'is-active' : '' }}"
                          data-rating="{{ $stars }}"
                          aria-label="{{ $stars }} {{ $stars === 1 ? 'star' : 'stars' }}"
                        >
                          <i class="fa-solid fa-star" aria-hidden="true"></i>
                        </button>
                      @endforeach
                    </div>
                    <span class="edu-review-form__rating-label js-edu-rating-label">{{ $ratingLabels[$selectedRating] ?? 'Excellent' }}</span>
                  </div>
                </div>

                <div class="edu-review-form__fields">
                  <div class="edu-review-form__field">
                    <label class="edu-review-form__label" for="educatorStudentClass">Class / Course <span class="edu-review-form__optional">(optional)</span></label>
                    <div class="edu-review-form__input-wrap">
                      <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                      <input
                        type="text"
                        id="educatorStudentClass"
                        name="student_class"
                        class="edu-review-form__input"
                        value="{{ old('student_class', $userReview?->student_class) }}"
                        placeholder="e.g. Class 12, B.Sc Physics"
                      >
                    </div>
                  </div>

                  <div class="edu-review-form__field edu-review-form__field--wide">
                    <label class="edu-review-form__label" for="educatorReviewText">Your feedback</label>
                    <textarea
                      id="educatorReviewText"
                      name="review"
                      class="edu-review-form__textarea"
                      rows="4"
                      maxlength="2000"
                      placeholder="What did you learn? How was their teaching style, clarity, and support?"
                    >{{ $reviewText }}</textarea>
                    <div class="edu-review-form__field-footer">
                      <span class="edu-review-form__char-count"><span class="js-edu-review-char-count">{{ strlen($reviewText) }}</span> / 2000</span>
                    </div>
                  </div>
                </div>

                <div class="edu-review-form__actions">
                  <p class="edu-review-form__note">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    Reviews are visible on this profile and may appear in testimonials.
                  </p>
                  <button type="submit" class="edu-btn edu-btn-primary edu-review-form__submit" id="educatorReviewSubmitBtn">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    <span class="btn-text">{{ ($userReview ?? null) ? 'Update review' : 'Submit review' }}</span>
                  </button>
                </div>
              </form>
            @else
              <div class="edu-review-login mb-4">
                <div class="edu-review-login__icon" aria-hidden="true">
                  <i class="fa-solid fa-star"></i>
                </div>
                <div class="edu-review-login__body">
                  <h4 class="edu-review-login__title">Share your experience</h4>
                  <p class="edu-review-login__text">Sign in to rate this {{ strtolower($educator->roleLabel()) }} and help other students make informed choices.</p>
                  <button type="button" class="edu-btn edu-btn-primary edu-review-login__btn js-edu-guest-action" data-action="review">
                    <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                    Login to review
                  </button>
                </div>
              </div>
            @endauth

            <div id="educatorReviewsList">
              @forelse(($profileReviews ?? collect()) as $item)
                @include('frontend.educator.partials.review-item', ['item' => $item])
              @empty
                <p class="edu-empty mb-0" id="educatorReviewsEmpty">No reviews yet. Be the first to share your experience.</p>
              @endforelse
            </div>

            @if(($profileReviewsHasMore ?? false))
              <div class="edu-reviews-load-more" id="educatorReviewsLoadMore">
                <button
                  type="button"
                  class="edu-btn edu-btn-outline edu-reviews-load-more__btn js-edu-reviews-load-more"
                  data-offset="{{ ($profileReviews ?? collect())->count() }}"
                >
                  <span class="btn-text">See more reviews</span>
                  <span class="btn-meta">({{ max(0, (int) ($profileReviewsTotal ?? 0) - ($profileReviews ?? collect())->count()) }} remaining)</span>
                </button>
              </div>
            @endif
          </div>
        </section>

        {{-- 10. Achievements --}}
        <section class="edu-section" id="edu-achievements">
          <h2 class="edu-section__title"><i class="fa-solid fa-trophy" aria-hidden="true"></i> Achievements</h2>
          @if($achievements->isNotEmpty())
            <ul class="edu-achievement-list">
              @foreach($achievements as $item)
                <li><i class="fa-solid fa-trophy" aria-hidden="true"></i> {{ $item }}</li>
              @endforeach
            </ul>
          @else
            <p class="edu-empty">No achievements listed.</p>
          @endif
        </section>

        {{-- 11. Articles --}}
        <section class="edu-section" id="edu-articles">
          <h2 class="edu-section__title"><i class="fa-solid fa-newspaper" aria-hidden="true"></i> Articles</h2>
          <p class="edu-empty">No articles published yet.</p>
        </section>

        {{-- 12. Gallery --}}
        <section class="edu-section" id="edu-gallery">
          <h2 class="edu-section__title"><i class="fa-solid fa-images" aria-hidden="true"></i> Gallery</h2>
          <p class="edu-empty">No gallery photos yet.</p>
        </section>

        {{-- 13. Availability & Locations --}}
        <section class="edu-section" id="edu-availability">
          <h2 class="edu-section__title"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> {{ $isTutorProfile ? 'Availability & Tuition' : 'Availability & Locations' }}</h2>

          @if($educator->is_available_now)
            <span class="edu-available-now"><i class="fa-solid fa-circle" aria-hidden="true"></i> Available now</span>
          @endif

          @if($availability->isNotEmpty())
            <table class="edu-schedule-table mb-3">
              <tbody>
                @foreach($availability as $slot)
                  <tr>
                    <td>{{ $slot['day'] ?? '—' }}</td>
                    <td>{{ $slot['slots'] ?? '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            <p class="edu-empty">Availability schedule not set.</p>
          @endif

          @if($modes->isNotEmpty())
            <p class="edu-classes-label">Teaching Modes</p>
            @foreach($modes as $mode)
              <span class="edu-mode-label"><i class="fa-solid fa-check" aria-hidden="true"></i> {{ $mode }}</span>
            @endforeach
          @endif

          @if($educator->locationLabel() || $educator->residential_address)
            <p class="edu-classes-label mt-3">Location</p>
            @if($educator->locationLabel())
              <p class="mb-1"><i class="fa-solid fa-location-dot text-primary me-1" aria-hidden="true"></i> {{ $educator->locationLabel() }}</p>
            @endif
            @if($educator->residential_address)
              <p class="mb-0 text-muted small">{{ $educator->residential_address }}</p>
            @endif
          @endif

          @if($educator->associated_institute)
            <p class="edu-classes-label mt-3">Institute</p>
            <p class="mb-0"><i class="fa-solid fa-school text-primary me-1" aria-hidden="true"></i> {{ $educator->associated_institute }}</p>
          @endif

          @if($isTutorProfile)
            @if($educator->tuitionPointAddressLabel())
              <p class="edu-classes-label mt-3">Tuition point</p>
              <p class="mb-2"><i class="fa-solid fa-location-dot text-primary me-1" aria-hidden="true"></i> {{ $educator->tuitionPointAddressLabel() }}</p>
              @if($educator->hasTuitionPointMap())
                <div class="edu-tuition-map">
                  @include('community.partials.location-map-embed', [
                    'lat' => $educator->tuition_latitude,
                    'lng' => $educator->tuition_longitude,
                    'title' => 'Tuition point map for '.$educator->display_name,
                    'wrapperClass' => 'edu-tuition-map__embed',
                  ])
                  <a
                    href="https://www.google.com/maps/search/?api=1&query={{ $educator->tuition_latitude }},{{ $educator->tuition_longitude }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="edu-tuition-map__link"
                  >
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Open in Google Maps
                  </a>
                </div>
              @endif
            @endif
            @if($educator->tuition_timings)
              <p class="edu-classes-label mt-3">Tuition timings</p>
              <p class="mb-0"><i class="fa-solid fa-clock text-primary me-1" aria-hidden="true"></i> {{ $educator->tuition_timings }}</p>
            @endif
          @endif
        </section>

        @if($isTutorProfile)
        {{-- 14. Fees & Packages --}}
        <section class="edu-section" id="edu-fees">
          <h2 class="edu-section__title"><i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i> Fees &amp; Packages</h2>
          @php
            $tuitionBatches = $educator->normalizedTuitionBatches();
            $tuitionDeliveryOptions = $educator->activeTuitionDeliveryOptions();
          @endphp
          @if($tuitionDeliveryOptions !== [])
            <div class="edu-delivery-grid {{ $tuitionBatches !== [] ? 'mb-3' : '' }}">
              @foreach($tuitionDeliveryOptions as $deliveryOption)
                <article class="edu-delivery-card edu-delivery-card--{{ $deliveryOption['key'] }}">
                  <div class="edu-delivery-card__head">
                    <span class="edu-delivery-card__icon" aria-hidden="true">
                      <i class="fa-solid {{ $deliveryOption['key'] === 'home' ? 'fa-house' : 'fa-user' }}"></i>
                    </span>
                    <h3 class="edu-delivery-card__title">{{ $deliveryOption['label'] }}</h3>
                  </div>
                  @if($deliveryOption['charges'])
                    <p class="edu-delivery-card__row">
                      <span>Charges</span>
                      <strong>{{ $deliveryOption['charges'] }}</strong>
                    </p>
                  @endif
                  @if($deliveryOption['timings'])
                    <p class="edu-delivery-card__row">
                      <span>Timings</span>
                      <strong>{{ $deliveryOption['timings'] }}</strong>
                    </p>
                  @endif
                </article>
              @endforeach
            </div>
          @endif
          @if($tuitionBatches !== [])
            <div class="edu-fees-table-wrap">
              <table class="edu-fees-table">
                <thead>
                  <tr>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Batch</th>
                    <th>Students</th>
                    <th>Cost</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($tuitionBatches as $batch)
                    <tr>
                      <td>{{ $batch['class'] ?: '—' }}</td>
                      <td>{{ $batch['subject'] ?: '—' }}</td>
                      <td>{{ $batch['batch_type'] ?: '—' }}</td>
                      <td>{{ $batch['student_count'] ?: '—' }}</td>
                      <td><strong>{{ $batch['cost'] ?: '—' }}</strong></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
          @if($educator->tuition_charges)
            <div class="edu-fees-box {{ $tuitionBatches !== [] ? 'mt-3' : '' }}">
              <p class="mb-1 text-muted small">Additional fee notes</p>
              <strong>{{ $educator->tuition_charges }}</strong>
            </div>
          @elseif($tuitionBatches === [] && $tuitionDeliveryOptions === [])
            <p class="edu-empty">Fee details not published. Send an enquiry to discuss packages.</p>
          @endif
        </section>
        @endif

        {{-- 15. Ask a Question --}}
        <section class="edu-section" id="edu-question">
          <h2 class="edu-section__title"><i class="fa-solid fa-circle-question" aria-hidden="true"></i> Ask a Question</h2>

          @auth
            @php
              $questionTopics = $isTutorProfile
                ? ['Tuition fees & batches', 'Availability & timings', 'Home / personal tuition', 'Demo class enquiry']
                : ['Subjects & syllabus', 'Teaching experience', 'Class schedule', 'Demo class enquiry'];
            @endphp
            <form
              id="eduQuickQuestionForm"
              class="edu-question-form"
              method="POST"
              action="{{ route('educator.enquiry', $educator->slug) }}"
              novalidate
            >
              @csrf
              <input type="hidden" name="name" value="{{ auth()->user()->name }}">
              <input type="hidden" name="email" value="{{ auth()->user()->email }}">
              <input type="hidden" name="phone" value="{{ auth()->user()->phone_number }}">

              <div class="edu-question-form__header">
                <div class="edu-question-form__icon" aria-hidden="true">
                  <i class="fa-solid fa-comments"></i>
                </div>
                <div>
                  <h3 class="edu-question-form__title">Ask {{ $educator->display_name }} directly</h3>
                  <p class="edu-question-form__hint">
                    {{ $isTutorProfile
                      ? 'Ask about tuition batches, fees, availability, or how classes are conducted.'
                      : 'Ask about subjects, teaching approach, experience, or how classes are run.' }}
                  </p>
                </div>
              </div>

              <div class="edu-question-form__topics">
                <span class="edu-question-form__label">Popular topics</span>
                <div class="edu-question-form__topic-list">
                  @foreach($questionTopics as $topic)
                    <button type="button" class="edu-question-form__topic js-edu-question-topic" data-topic="{{ $topic }}">
                      {{ $topic }}
                    </button>
                  @endforeach
                </div>
              </div>

              <div class="edu-question-form__fields">
                <div class="edu-question-form__field">
                  <label class="edu-question-form__label" for="eduQuickSubject">
                    Topic <span class="edu-question-form__optional">(optional)</span>
                  </label>
                  <div class="edu-question-form__input-wrap">
                    <i class="fa-solid fa-tag" aria-hidden="true"></i>
                    <input
                      type="text"
                      id="eduQuickSubject"
                      name="subject"
                      class="edu-question-form__input"
                      placeholder="{{ $isTutorProfile ? 'e.g. Class 10 Maths tuition batch' : 'e.g. Class 10 Science teaching experience' }}"
                    >
                  </div>
                </div>

                <div class="edu-question-form__field edu-question-form__field--wide">
                  <label class="edu-question-form__label" for="eduQuickMessage">Your question</label>
                  <textarea
                    id="eduQuickMessage"
                    name="message"
                    class="edu-question-form__textarea"
                    rows="4"
                    maxlength="5000"
                    required
                    placeholder="Write your question clearly — include class, subject, or any details that will help {{ $educator->display_name }} reply faster."
                  ></textarea>
                  <div class="edu-question-form__field-footer">
                    <span class="edu-question-form__char-count"><span class="js-edu-question-char-count">0</span> / 5000</span>
                  </div>
                </div>
              </div>

              <div class="edu-question-form__actions">
                <p class="edu-question-form__note">
                  <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                  Your message is sent privately to the educator. You will be notified when they respond.
                </p>
                <button type="submit" class="edu-btn edu-btn-primary edu-question-form__submit" id="eduQuickQuestionSubmit">
                  <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                  <span class="btn-text">Send question</span>
                </button>
              </div>
            </form>
          @else
            <div class="edu-question-login">
              <div class="edu-question-login__icon" aria-hidden="true">
                <i class="fa-solid fa-circle-question"></i>
              </div>
              <div class="edu-question-login__body">
                <h3 class="edu-question-login__title">Have a question?</h3>
                <p class="edu-question-login__text">
                  {{ $isTutorProfile
                    ? 'Sign in to ask about tuition batches, fees, availability, or teaching style.'
                    : 'Sign in to ask about subjects, classes, experience, or teaching approach.' }}
                </p>
                <button type="button" class="edu-btn edu-btn-primary edu-question-login__btn js-edu-guest-action" data-action="question">
                  <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                  Login to ask a question
                </button>
              </div>
            </div>
          @endauth
        </section>

        {{-- 16. Contact & Enquiry --}}
        <section class="edu-section" id="edu-contact">
          <h2 class="edu-section__title"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Contact &amp; Enquiry</h2>
          <ul class="edu-sidebar-list mb-3">
            @if($educator->phone)
              <li><span>Phone</span><span>{{ $educator->phone }}</span></li>
            @endif
            @if($educator->email)
              <li><span>Email</span><span>{{ $educator->email }}</span></li>
            @endif
            @if($educator->whatsapp)
              <li><span>WhatsApp</span><span>{{ $educator->whatsapp }}</span></li>
            @endif
            @if($educator->residential_address)
              <li><span>Address</span><span>{{ $educator->residential_address }}</span></li>
            @endif
          </ul>

          @if(!$educator->phone && !$educator->email && !$educator->whatsapp && !$educator->residential_address && ! $educator->hasSocialLinks())
            <p class="edu-empty">Contact details not published.</p>
          @endif

          @include('frontend.educator.partials.social-links', ['educator' => $educator])

          <button type="button" class="edu-btn edu-btn-primary js-edu-open-enquiry">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i> Send Enquiry
          </button>
        </section>
      </main>

      {{-- Right sticky sidebar --}}
      <aside class="edu-sidebar" aria-label="Profile summary">
        <div class="edu-sidebar-card">
          <h3><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Availability</h3>
          @if($educator->is_available_now)
            <span class="edu-available-now"><i class="fa-solid fa-circle" aria-hidden="true"></i> Available now</span>
          @endif
          @forelse($availability as $slot)
            <div class="d-flex justify-content-between small py-1 border-bottom">
              <span>{{ $slot['day'] ?? '—' }}</span>
              <span class="text-muted">{{ $slot['slots'] ?? '—' }}</span>
            </div>
          @empty
            <p class="edu-empty mb-2">Availability not set.</p>
          @endforelse
          @if($modes->isNotEmpty())
            <div class="mt-2">
              @foreach($modes as $mode)
                <span class="edu-mode-label"><i class="fa-solid fa-check" aria-hidden="true"></i> {{ $mode }}</span>
              @endforeach
            </div>
          @endif
        </div>

        <div class="edu-sidebar-card">
          <h3><i class="fa-solid fa-address-book" aria-hidden="true"></i> Contact</h3>
          <ul class="edu-sidebar-list">
            @if($educator->phone)<li><span>Phone</span><span>{{ $educator->phone }}</span></li>@endif
            @if($educator->email)<li><span>Email</span><span>{{ $educator->email }}</span></li>@endif
            @if($educator->residential_address)<li><span>Address</span><span>{{ $educator->residential_address }}</span></li>@endif
          </ul>
          @if(!$educator->phone && !$educator->email && !$educator->residential_address && ! $educator->hasSocialLinks())
            <p class="edu-empty mb-0">Contact details not published.</p>
          @endif
          @include('frontend.educator.partials.social-links', ['educator' => $educator, 'compact' => true])
        </div>

        <div class="edu-sidebar-card">
          <h3><i class="fa-solid fa-chart-simple" aria-hidden="true"></i> Teaching Stats</h3>
          <div class="edu-stat-row">
            <i class="fa-solid fa-clock" aria-hidden="true"></i>
            <span>Experience</span>
            <strong>{{ $educator->years_experience }} yrs</strong>
          </div>
          <div class="edu-stat-row">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <span>Students taught</span>
            <strong>{{ number_format($educator->students_taught) }}</strong>
          </div>
          <div class="edu-stat-row">
            <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
            <span>Success rate</span>
            <strong>{{ $educator->success_rate !== null ? $educator->success_rate.'%' : '—' }}</strong>
          </div>
          <div class="edu-stat-row">
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <span>Rating</span>
            <strong>{{ number_format((float) $educator->average_rating, 1) }}/5</strong>
          </div>
          <div class="edu-stat-row">
            <i class="fa-solid fa-heart" aria-hidden="true"></i>
            <span>Followers</span>
            <strong class="js-edu-followers-count">{{ number_format($educator->followers_count) }}</strong>
          </div>
        </div>

        <div class="edu-sidebar-card">
          <h3><i class="fa-solid fa-trophy" aria-hidden="true"></i> Achievements</h3>
          @forelse($achievements as $item)
            <div class="edu-cert-item"><i class="fa-solid fa-trophy" aria-hidden="true"></i> {{ $item }}</div>
          @empty
            <p class="edu-empty mb-0">No achievements listed.</p>
          @endforelse
        </div>

        <div class="edu-sidebar-card">
          <h3><i class="fa-solid fa-award" aria-hidden="true"></i> Certifications</h3>
          @forelse($certifications as $item)
            <div class="edu-cert-item"><i class="fa-solid fa-certificate" aria-hidden="true"></i> {{ $item }}</div>
          @empty
            <p class="edu-empty mb-0">No certifications listed.</p>
          @endforelse
        </div>
      </aside>
    </div>
  </div>
</div>

<div class="modal fade" id="enquiryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="educatorEnquiryForm" method="POST" action="{{ route('educator.enquiry', $educator->slug) }}" novalidate>
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Send enquiry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          @guest
            <p class="text-muted">Please <a href="{{ route('login') }}">log in</a> to send an enquiry.</p>
          @else
            <div id="educatorEnquiryAlert" class="alert d-none" role="alert"></div>
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}"></div>
            <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone_number }}"></div>
            <div class="mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control"></div>
            <div class="mb-0"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="4" required></textarea></div>
          @endguest
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
          @auth
            <button type="submit" class="btn btn-primary" id="educatorEnquirySubmitBtn">
              <span class="btn-text">Send</span>
            </button>
          @endauth
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@include('community.partials.toastr-assets')
<script src="{{ asset('assets/js/educator-profile.js') }}?v={{ now()->timestamp }}"></script>
@endpush
