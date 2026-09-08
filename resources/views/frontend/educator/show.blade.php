@extends('frontend.layouts.app')

@section('meta_title', $educator->display_name.' · '.$educator->roleLabel().' | SoilnWater')
@section('meta_description', $educator->tagline ?: ($educator->professional_headline ?: 'Teacher and tutor profile on SoilnWater'))

@push('styles')
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
  $testimonials = collect($profileReviews ?? [])->filter(fn ($item) => filled(trim((string) ($item->body ?? ''))))->take(6);
  $aboutText = trim((string) $educator->about);
  $aboutNeedsToggle = strlen($aboutText) > 280;

  $navItems = [
    ['id' => 'edu-overview', 'label' => 'Profile Overview', 'icon' => 'fa-user'],
    ['id' => 'edu-about', 'label' => 'About Me', 'icon' => 'fa-circle-info'],
    ['id' => 'edu-subjects', 'label' => 'Subjects & Classes', 'icon' => 'fa-book'],
    ['id' => 'edu-experience', 'label' => 'Experience & Education', 'icon' => 'fa-briefcase'],
    ['id' => 'edu-courses', 'label' => 'Courses', 'icon' => 'fa-graduation-cap'],
    ['id' => 'edu-notes', 'label' => 'Notes & Materials', 'icon' => 'fa-file-lines'],
    ['id' => 'edu-videos', 'label' => 'Videos', 'icon' => 'fa-circle-play'],
    ['id' => 'edu-papers', 'label' => 'Question Papers', 'icon' => 'fa-file-circle-question'],
    ['id' => 'edu-reviews', 'label' => 'Students & Reviews', 'icon' => 'fa-star'],
    ['id' => 'edu-achievements', 'label' => 'Achievements', 'icon' => 'fa-trophy'],
    ['id' => 'edu-articles', 'label' => 'Articles', 'icon' => 'fa-newspaper'],
    ['id' => 'edu-gallery', 'label' => 'Gallery', 'icon' => 'fa-images'],
    ['id' => 'edu-availability', 'label' => 'Availability & Locations', 'icon' => 'fa-calendar-check'],
    ['id' => 'edu-fees', 'label' => 'Fees & Packages', 'icon' => 'fa-indian-rupee-sign'],
    ['id' => 'edu-question', 'label' => 'Ask a Question', 'icon' => 'fa-circle-question'],
    ['id' => 'edu-contact', 'label' => 'Contact & Enquiry', 'icon' => 'fa-envelope'],
  ];
@endphp

<div
  class="edu-page"
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
      <a href="{{ route('educator.listings', ['subject' => $primarySubject]) }}">{{ $primarySubject }} Teachers</a>
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
                <span class="edu-overview__badge"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $educator->verifiedBadgeLabel() }}</span>
              @else
                <span class="edu-overview__badge edu-overview__badge--muted">{{ $educator->roleLabel() }}</span>
              @endif

              <h1 class="edu-overview__name">
                {{ $educator->display_name }}
                @if($educator->isVerified())
                  <i class="fa-solid fa-star" aria-hidden="true" title="Verified educator"></i>
                @endif
              </h1>

              <p class="edu-overview__headline">{{ $educator->professional_headline ?: 'Educator' }}</p>
              @if($educator->tagline)
                <p class="edu-overview__tagline">{{ $educator->tagline }}</p>
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
              <button type="button" class="edu-btn edu-btn-outline js-edu-open-enquiry">
                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book a Session
              </button>
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
                  <span class="text-muted">{{ collect([$exp['organization'] ?? null, $exp['duration'] ?? null])->filter()->implode(' · ') }}</span>
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
          <h2 class="edu-section__title"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Courses</h2>
          @if($courses->isNotEmpty())
            <div class="edu-courses-track">
              @foreach($courses as $course)
                @php $typeMeta = \App\Models\StudyMaterial::materialTypeMeta($course->material_type); @endphp
                <a href="{{ $course->publicUrl() }}" class="edu-course-card">
                  <div class="edu-course-card__thumb">
                    <img src="{{ $course->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
                    <span class="edu-course-card__badge">{{ $typeMeta['label'] }}</span>
                  </div>
                  <div class="edu-course-card__body">
                    <h4>{{ $course->title }}</h4>
                    <div class="edu-course-card__price">{{ $course->is_free ? 'Free' : 'Premium' }}</div>
                    <div class="edu-course-card__meta">
                      <span><i class="fa-solid fa-star" aria-hidden="true"></i> {{ number_format((float) $course->average_rating, 1) }}</span>
                      <span><i class="fa-solid fa-users" aria-hidden="true"></i> {{ \App\Models\StudyMaterial::formatCompactCount($course->downloads_count) }}</span>
                    </div>
                  </div>
                </a>
              @endforeach
            </div>
          @else
            <p class="edu-empty">No courses published yet.</p>
          @endif
        </section>

        {{-- 6. Notes & Materials --}}
        <section class="edu-section" id="edu-notes">
          <h2 class="edu-section__title"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Notes &amp; Materials</h2>
          @if($notes->isNotEmpty())
            <div class="edu-notes-grid">
              @foreach($notes as $note)
                @php $fileMeta = $note->fileTypeMeta(); @endphp
                <a href="{{ $note->publicUrl() }}" class="edu-note-card">
                  <span class="edu-note-card__icon edu-note-card__icon--{{ $fileMeta['tone'] }}">
                    <i class="fa-solid {{ $fileMeta['icon'] }}" aria-hidden="true"></i>
                  </span>
                  <div class="edu-note-card__body">
                    <h4>{{ $note->title }}</h4>
                    <p>{{ $note->subject ?: 'Notes' }} · {{ $fileMeta['label'] }}</p>
                    <div class="edu-note-card__meta">
                      @if($note->pages)
                        <span><i class="fa-solid fa-file" aria-hidden="true"></i> {{ number_format($note->pages) }} pages</span>
                      @endif
                      <span><i class="fa-solid fa-download" aria-hidden="true"></i> {{ \App\Models\StudyMaterial::formatCompactCount($note->downloads_count) }}</span>
                    </div>
                  </div>
                </a>
              @endforeach
            </div>
          @else
            <p class="edu-empty">No notes published yet.</p>
          @endif
        </section>

        {{-- 7. Videos --}}
        <section class="edu-section" id="edu-videos">
          <h2 class="edu-section__title"><i class="fa-solid fa-circle-play" aria-hidden="true"></i> Videos</h2>
          @if($videos->isNotEmpty())
            <div class="edu-media-grid">
              @foreach($videos as $video)
                <a href="{{ $video->publicUrl() }}" class="edu-media-card">
                  <div class="edu-media-card__thumb">
                    @if($video->thumbnailUrl())
                      <img src="{{ $video->thumbnailUrl() }}" alt="">
                    @else
                      <i class="fa-solid fa-circle-play" aria-hidden="true"></i>
                    @endif
                    <span class="edu-media-card__play"><i class="fa-solid fa-play" aria-hidden="true"></i></span>
                  </div>
                  <div class="edu-media-card__body">
                    <h4>{{ $video->title }}</h4>
                  </div>
                </a>
              @endforeach
            </div>
          @else
            <p class="edu-empty">No videos published yet.</p>
          @endif
        </section>

        {{-- 8. Question Papers --}}
        <section class="edu-section" id="edu-papers">
          <h2 class="edu-section__title"><i class="fa-solid fa-file-circle-question" aria-hidden="true"></i> Question Papers</h2>
          @if($questionPapers->isNotEmpty())
            <div class="edu-notes-grid">
              @foreach($questionPapers as $paper)
                @php $fileMeta = $paper->fileTypeMeta(); @endphp
                <a href="{{ $paper->publicUrl() }}" class="edu-note-card">
                  <span class="edu-note-card__icon edu-note-card__icon--{{ $fileMeta['tone'] }}">
                    <i class="fa-solid {{ $fileMeta['icon'] }}" aria-hidden="true"></i>
                  </span>
                  <div class="edu-note-card__body">
                    <h4>{{ $paper->title }}</h4>
                    <p>{{ $paper->subject ?: 'Question Paper' }} · {{ $fileMeta['label'] }}</p>
                    <div class="edu-note-card__meta">
                      @if($paper->pages)
                        <span><i class="fa-solid fa-file" aria-hidden="true"></i> {{ number_format($paper->pages) }} pages</span>
                      @endif
                      <span><i class="fa-solid fa-download" aria-hidden="true"></i> {{ \App\Models\StudyMaterial::formatCompactCount($paper->downloads_count) }}</span>
                    </div>
                  </div>
                </a>
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
            <div class="edu-testimonials__track js-edu-testimonial-track" id="eduTestimonialTrack">
              @foreach($testimonials as $item)
                @include('frontend.educator.partials.testimonial-item', ['item' => $item])
              @endforeach
            </div>
          </div>

          <div id="educatorReviewsSection" data-review-url="{{ route('educator.review', $educator->slug) }}">
            <h3 class="h6 mb-3">All Reviews</h3>
            <p class="edu-empty mb-3">
              <span class="js-edu-avg-rating">{{ number_format((float) $educator->average_rating, 1) }}</span>
              average ·
              <span class="js-edu-reviews-count">{{ number_format($educator->reviews_count) }}</span>
              reviews (profile + study materials)
            </p>

            @auth
              <form id="educatorReviewForm" class="edu-review-form mb-4" novalidate>
                @csrf
                <h4 class="edu-review-form__title">{{ ($userReview ?? null) ? 'Update your review' : 'Write a review' }}</h4>
                <p class="edu-review-form__hint mb-2">Share your experience with this teacher / tutor.</p>

                @php $selectedRating = (int) old('rating', $userReview?->rating ?: 5); @endphp
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

                <div class="row g-2 mt-2">
                  <div class="col-md-4">
                    <label class="form-label" for="educatorStudentClass">Class / Course (optional)</label>
                    <input type="text" id="educatorStudentClass" name="student_class" class="form-control" value="{{ old('student_class', $userReview?->student_class) }}" placeholder="e.g. Class 12">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label" for="educatorReviewText">Your feedback</label>
                    <textarea id="educatorReviewText" name="review" class="form-control" rows="2" maxlength="2000" placeholder="What was helpful about learning with them?">{{ old('review', $userReview?->review) }}</textarea>
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  <button type="submit" class="edu-btn edu-btn-primary" id="educatorReviewSubmitBtn">
                    <span class="btn-text">{{ ($userReview ?? null) ? 'Update review' : 'Submit review' }}</span>
                  </button>
                </div>
              </form>
            @else
              <div class="edu-review-login mb-4">
                <p class="mb-2">Sign in to leave a review for this educator.</p>
                <button type="button" class="edu-btn edu-btn-outline js-edu-guest-action" data-action="review">Login to review</button>
              </div>
            @endauth

            <div id="educatorReviewsList">
              @forelse(($profileReviews ?? collect()) as $item)
                @include('frontend.educator.partials.review-item', ['item' => $item])
              @empty
                <p class="edu-empty mb-0" id="educatorReviewsEmpty">No reviews yet. Be the first to share your experience.</p>
              @endforelse
            </div>
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
          <h2 class="edu-section__title"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Availability &amp; Locations</h2>

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
        </section>

        {{-- 14. Fees & Packages --}}
        <section class="edu-section" id="edu-fees">
          <h2 class="edu-section__title"><i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i> Fees &amp; Packages</h2>
          @if($educator->tuition_charges)
            <div class="edu-fees-box">
              <p class="mb-1 text-muted small">Tuition charges</p>
              <strong>{{ $educator->tuition_charges }}</strong>
            </div>
          @else
            <p class="edu-empty">Fee details not published. Send an enquiry to discuss packages.</p>
          @endif
        </section>

        {{-- 15. Ask a Question --}}
        <section class="edu-section" id="edu-question">
          <h2 class="edu-section__title"><i class="fa-solid fa-circle-question" aria-hidden="true"></i> Ask a Question</h2>
          <div class="edu-ask-cta">
            <p>Have a question about classes, subjects or availability? Send a message directly to {{ $educator->display_name }}.</p>
            @auth
              <form id="eduQuickQuestionForm" class="edu-quick-form" method="POST" action="{{ route('educator.enquiry', $educator->slug) }}" novalidate>
                @csrf
                <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                <input type="hidden" name="phone" value="{{ auth()->user()->phone_number }}">
                <div class="mb-2">
                  <label class="form-label" for="eduQuickSubject">Subject (optional)</label>
                  <input type="text" id="eduQuickSubject" name="subject" class="form-control" placeholder="e.g. Class 10 Maths tuition">
                </div>
                <div class="mb-3">
                  <label class="form-label" for="eduQuickMessage">Your question</label>
                  <textarea id="eduQuickMessage" name="message" class="form-control" rows="3" required placeholder="Write your question here..."></textarea>
                </div>
                <button type="submit" class="edu-btn edu-btn-primary" id="eduQuickQuestionSubmit">
                  <span class="btn-text"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send Question</span>
                </button>
              </form>
            @else
              <button type="button" class="edu-btn edu-btn-primary js-edu-guest-action" data-action="question">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Ask a Question
              </button>
            @endauth
          </div>
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

          @if(!$educator->phone && !$educator->email && !$educator->whatsapp && !$educator->residential_address)
            <p class="edu-empty">Contact details not published.</p>
          @endif

          <div class="edu-social-row mb-3">
            @foreach([
              'facebook_url' => 'fa-facebook',
              'instagram_url' => 'fa-instagram',
              'youtube_url' => 'fa-youtube',
              'linkedin_url' => 'fa-linkedin',
              'whatsapp_url' => 'fa-whatsapp',
            ] as $field => $icon)
              @if($educator->{$field})
                <a href="{{ $educator->{$field} }}" target="_blank" rel="noopener" class="edu-social-btn" aria-label="{{ ucfirst(str_replace('_url', '', $field)) }}">
                  <i class="fa-brands {{ $icon }}" aria-hidden="true"></i>
                </a>
              @endif
            @endforeach
          </div>

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
          @if(!$educator->phone && !$educator->email && !$educator->residential_address)
            <p class="edu-empty mb-0">Contact details not published.</p>
          @endif
          <div class="edu-social-row">
            @foreach([
              'facebook_url' => 'fa-facebook',
              'instagram_url' => 'fa-instagram',
              'youtube_url' => 'fa-youtube',
              'linkedin_url' => 'fa-linkedin',
              'whatsapp_url' => 'fa-whatsapp',
            ] as $field => $icon)
              @if($educator->{$field})
                <a href="{{ $educator->{$field} }}" target="_blank" rel="noopener" class="edu-social-btn" aria-label="{{ ucfirst(str_replace('_url', '', $field)) }}">
                  <i class="fa-brands {{ $icon }}" aria-hidden="true"></i>
                </a>
              @endif
            @endforeach
          </div>
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
