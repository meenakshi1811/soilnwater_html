@extends('frontend.layouts.app')

@section('meta_title', $educator->display_name.' · '.$educator->roleLabel().' | SoilnWater')
@section('meta_description', $educator->tagline ?: ($educator->professional_headline ?: 'Teacher and tutor profile on SoilnWater'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/educator-profile.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
@endphp

<div class="edu-page">
  <section class="edu-hero">
    <div class="container">
      @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif
      <div class="edu-hero__inner">
        <img src="{{ $photo }}" alt="{{ $educator->display_name }}" class="edu-avatar">
        <div>
          <span class="edu-badge">
            @if($educator->isVerified())
              <i class="fa-solid fa-certificate"></i> {{ $educator->verifiedBadgeLabel() }}
            @else
              {{ $educator->roleLabel() }}
            @endif
          </span>
          <h1 class="edu-name">{{ $educator->display_name }}</h1>
          <p class="edu-headline">{{ $educator->professional_headline ?: 'Educator' }}</p>
          @if($educator->tagline)
            <p class="edu-tagline">{{ $educator->tagline }}</p>
          @endif
          <div class="edu-meta">
            <span><i class="fa-solid fa-star"></i><span class="js-edu-avg-rating">{{ number_format((float)$educator->average_rating, 1) }}</span> (<span class="js-edu-reviews-count">{{ number_format($educator->reviews_count) }}</span> reviews)</span>
            @if($educator->locationLabel())
              <span><i class="fa-solid fa-location-dot"></i>{{ $educator->locationLabel() }}</span>
            @endif
            @if($educator->associated_institute)
              <span><i class="fa-solid fa-school"></i>{{ $educator->associated_institute }}</span>
            @endif
            @if($modes->isNotEmpty())
              <span><i class="fa-solid fa-chalkboard-user"></i>{{ $modes->implode(', ') }}</span>
            @endif
            @if($languages->isNotEmpty())
              <span><i class="fa-solid fa-language"></i>{{ $languages->implode(', ') }}</span>
            @endif
          </div>
          <div class="edu-cta">
            <button type="button" class="edu-btn edu-btn-primary" data-bs-toggle="modal" data-bs-target="#enquiryModal"><i class="fa-solid fa-envelope"></i> Send enquiry</button>
            @auth
              <form method="POST" action="{{ route('educator.follow', $educator->slug) }}">
                @csrf
                <button type="submit" class="edu-btn edu-btn-outline">
                  <i class="fa-solid {{ $isFollowing ? 'fa-user-minus' : 'fa-user-plus' }}"></i>
                  {{ $isFollowing ? 'Unfollow' : 'Follow' }}
                </button>
              </form>
            @else
              <a href="{{ route('login') }}" class="edu-btn edu-btn-outline"><i class="fa-solid fa-user-plus"></i> Follow</a>
            @endauth
            @if($educator->is_available_now)
              <span class="edu-badge"><i class="fa-solid fa-circle" style="font-size:0.55rem"></i> Available now</span>
            @endif
          </div>
        </div>
        <div class="edu-stats">
          <div class="edu-stat"><strong>{{ $educator->years_experience }}+</strong><span>Years exp.</span></div>
          <div class="edu-stat"><strong>{{ number_format($educator->students_taught) }}</strong><span>Students</span></div>
          <div class="edu-stat"><strong>{{ number_format($educator->approved_materials_count) }}</strong><span>Materials</span></div>
          <div class="edu-stat"><strong>{{ number_format($educator->followers_count) }}</strong><span>Followers</span></div>
        </div>
      </div>
    </div>
  </section>

  <div class="container edu-layout">
    <div>
      <section class="edu-card">
        <h3>About</h3>
        @if($educator->about)
          <p style="white-space:pre-line;margin:0">{{ $educator->about }}</p>
        @else
          <p class="edu-empty mb-0">No about information yet.</p>
        @endif
        @if($educator->teaching_method)
          <p class="mt-3 mb-0"><strong>Teaching method:</strong> {{ $educator->teaching_method }}</p>
        @endif
      </section>

      <section class="edu-card">
        <h3>Subjects & Classes</h3>
        @if($subjects->isNotEmpty())
          <div class="edu-chip-row mb-3">
            @foreach($subjects as $subject)
              @php $name = is_array($subject) ? ($subject['name'] ?? '') : $subject; $level = is_array($subject) ? ($subject['level'] ?? '') : ''; @endphp
              @if($name)
                <span class="edu-chip">{{ $name }}@if($level) · {{ ucfirst($level) }}@endif</span>
              @endif
            @endforeach
          </div>
        @endif
        @if($classes->isNotEmpty())
          <p class="mb-2"><strong>Classes:</strong> {{ $classes->implode(', ') }}</p>
        @endif
        @if($boards->isNotEmpty())
          <p class="mb-0"><strong>Boards:</strong> {{ $boards->implode(', ') }}</p>
        @endif
        @if($subjects->isEmpty() && $classes->isEmpty() && $boards->isEmpty())
          <p class="edu-empty mb-0">Subjects and classes will appear here once added.</p>
        @endif
      </section>

      <section class="edu-card">
        <h3>Experience & Education</h3>
        @forelse($experiences as $exp)
          <div class="edu-timeline-item">
            <strong>{{ $exp['title'] ?? 'Experience' }}</strong>
            <span class="text-muted small">{{ collect([$exp['organization'] ?? null, $exp['duration'] ?? null])->filter()->implode(' · ') }}</span>
            @if(!empty($exp['description']))<p class="mb-0 mt-1">{{ $exp['description'] }}</p>@endif
          </div>
        @empty
          <p class="edu-empty">No experience listed yet.</p>
        @endforelse
        @if($qualifications->isNotEmpty())
          <h4 class="h6 mt-3">Education</h4>
          @foreach($qualifications as $qual)
            <div class="edu-timeline-item">
              <strong>{{ $qual['degree'] ?? 'Qualification' }}</strong>
              <span class="text-muted small">{{ collect([$qual['institution'] ?? null, $qual['year'] ?? null])->filter()->implode(' · ') }}</span>
            </div>
          @endforeach
        @endif
      </section>

      @if($courses->isNotEmpty())
        <section class="edu-card">
          <h3>Courses</h3>
          @foreach($courses as $course)
            <a class="edu-course-card" href="{{ $course->publicUrl() }}">
              <img class="edu-thumb" src="{{ $course->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
              <div>
                <strong>{{ $course->title }}</strong>
                <div class="small text-muted">{{ $course->materialTypeLabel() }} · {{ $course->subject ?: 'General' }}</div>
              </div>
            </a>
          @endforeach
        </section>
      @endif

      <section class="edu-card">
        <h3>Recent Notes</h3>
        @forelse($notes as $note)
          <a class="edu-note-card" href="{{ $note->publicUrl() }}">
            <img class="edu-thumb" src="{{ $note->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
            <div>
              <strong>{{ $note->title }}</strong>
              <div class="small text-muted">{{ $note->subject ?: 'Notes' }} · {{ number_format($note->downloads_count) }} downloads</div>
            </div>
          </a>
        @empty
          <p class="edu-empty mb-0">No notes published yet.</p>
        @endforelse
      </section>

      <section class="edu-card" id="educatorReviewsSection"
        data-review-url="{{ route('educator.review', $educator->slug) }}"
      >
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
          <div>
            <h3 class="mb-1">Reviews</h3>
            <p class="edu-empty mb-0">
              <span class="js-edu-avg-rating">{{ number_format((float) $educator->average_rating, 1) }}</span>
              average ·
              <span class="js-edu-reviews-count">{{ number_format($educator->reviews_count) }}</span>
              reviews (profile + study materials)
            </p>
          </div>
        </div>

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
            <a href="{{ route('login') }}" class="edu-btn edu-btn-outline">Login to review</a>
          </div>
        @endauth

        <div id="educatorReviewsList">
          @forelse(($profileReviews ?? collect()) as $item)
            @include('frontend.educator.partials.review-item', ['item' => $item])
          @empty
            <p class="edu-empty mb-0" id="educatorReviewsEmpty">No reviews yet. Be the first to share your experience.</p>
          @endforelse
        </div>
      </section>
    </div>

    <aside>
      <div class="edu-card">
        <h3>Availability</h3>
        @forelse($availability as $slot)
          <div class="d-flex justify-content-between small py-1 border-bottom">
            <span>{{ $slot['day'] ?? '—' }}</span>
            <span class="text-muted">{{ $slot['slots'] ?? '—' }}</span>
          </div>
        @empty
          <p class="edu-empty mb-0">Availability not set.</p>
        @endforelse
      </div>

      <div class="edu-card">
        <h3>Contact</h3>
        <ul class="edu-sidebar-list">
          @if($educator->phone)<li><span>Phone</span><span>{{ $educator->phone }}</span></li>@endif
          @if($educator->email)<li><span>Email</span><span>{{ $educator->email }}</span></li>@endif
          @if($educator->whatsapp)<li><span>WhatsApp</span><span>{{ $educator->whatsapp }}</span></li>@endif
          @if($educator->tuition_charges)<li><span>Charges</span><span>{{ $educator->tuition_charges }}</span></li>@endif
        </ul>
        @if(!$educator->phone && !$educator->email && !$educator->whatsapp)
          <p class="edu-empty mb-0">Contact details not published.</p>
        @endif
        <div class="d-flex gap-2 mt-3 flex-wrap">
          @foreach([
            'facebook_url' => 'fa-facebook',
            'instagram_url' => 'fa-instagram',
            'youtube_url' => 'fa-youtube',
            'linkedin_url' => 'fa-linkedin',
            'whatsapp_url' => 'fa-whatsapp',
          ] as $field => $icon)
            @if($educator->{$field})
              <a href="{{ $educator->{$field} }}" target="_blank" rel="noopener" class="edu-btn edu-btn-outline" style="padding:0.45rem 0.7rem"><i class="fa-brands {{ $icon }}"></i></a>
            @endif
          @endforeach
        </div>
      </div>

      <div class="edu-card">
        <h3>Teaching Stats</h3>
        <ul class="edu-sidebar-list">
          <li><span>Experience</span><span>{{ $educator->years_experience }} yrs</span></li>
          <li><span>Students taught</span><span>{{ number_format($educator->students_taught) }}</span></li>
          <li><span>Success rate</span><span>{{ $educator->success_rate !== null ? $educator->success_rate.'%' : '—' }}</span></li>
          <li><span>Rating</span><span>{{ number_format((float)$educator->average_rating, 1) }}/5</span></li>
        </ul>
      </div>

      <div class="edu-card">
        <h3>Achievements</h3>
        @forelse($achievements as $item)
          <div class="small py-1"><i class="fa-solid fa-trophy text-warning me-1"></i>{{ $item }}</div>
        @empty
          <p class="edu-empty mb-0">No achievements listed.</p>
        @endforelse
      </div>

      <div class="edu-card">
        <h3>Certifications</h3>
        @forelse($certifications as $item)
          <div class="small py-1"><i class="fa-solid fa-award text-primary me-1"></i>{{ $item }}</div>
        @empty
          <p class="edu-empty mb-0">No certifications listed.</p>
        @endforelse
      </div>
    </aside>
  </div>
</div>

<div class="modal fade" id="enquiryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('educator.enquiry', $educator->slug) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Send enquiry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          @guest
            <p class="text-muted">Please <a href="{{ route('login') }}">log in</a> to send an enquiry.</p>
          @else
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}"></div>
            <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone_number }}"></div>
            <div class="mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control"></div>
            <div class="mb-0"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="4" required></textarea></div>
          @endguest
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
          @auth<button type="submit" class="btn btn-primary">Send</button>@endauth
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function () {
  var section = document.getElementById('educatorReviewsSection');
  if (!section) return;

  var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  var reviewUrl = section.dataset.reviewUrl;
  var ratingInput = document.getElementById('educatorReviewRating');
  var starButtons = document.querySelectorAll('.edu-star-picker__btn');

  function notify(type, message) {
    if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3500 };
      window.toastr[type](message);
      return;
    }
    alert(message);
  }

  function paintStars(value) {
    starButtons.forEach(function (btn) {
      btn.classList.toggle('is-active', Number(btn.dataset.rating) <= Number(value));
    });
  }

  starButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (ratingInput) ratingInput.value = btn.dataset.rating;
      paintStars(btn.dataset.rating);
    });
    btn.addEventListener('mouseenter', function () {
      paintStars(btn.dataset.rating);
    });
  });

  document.querySelector('.edu-star-picker')?.addEventListener('mouseleave', function () {
    paintStars(ratingInput?.value || 5);
  });

  var form = document.getElementById('educatorReviewForm');
  if (!form || !reviewUrl) return;

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    var submitBtn = document.getElementById('educatorReviewSubmitBtn');
    var btnText = submitBtn?.querySelector('.btn-text');
    if (submitBtn) submitBtn.disabled = true;
    if (btnText) btnText.textContent = 'Saving...';

    try {
      var response = await fetch(reviewUrl, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          _token: csrf,
          rating: Number(ratingInput?.value || 5),
          student_class: document.getElementById('educatorStudentClass')?.value || '',
          review: document.getElementById('educatorReviewText')?.value || ''
        })
      });
      var data = await response.json().catch(function () { return {}; });
      if (!response.ok) {
        var firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
        throw new Error(firstError || data.message || 'Unable to save review.');
      }

      document.querySelectorAll('.js-edu-avg-rating').forEach(function (el) {
        el.textContent = data.average_rating;
      });
      document.querySelectorAll('.js-edu-reviews-count').forEach(function (el) {
        el.textContent = Number(data.reviews_count || 0).toLocaleString();
      });

      var list = document.getElementById('educatorReviewsList');
      document.getElementById('educatorReviewsEmpty')?.remove();
      if (list && data.review_html) {
        var existing = data.review_key ? list.querySelector('[data-review-id="' + data.review_key + '"]') : null;
        if (existing) existing.remove();
        list.insertAdjacentHTML('afterbegin', data.review_html);
      }

      if (btnText) btnText.textContent = 'Update review';
      var title = form.querySelector('.edu-review-form__title');
      if (title) title.textContent = 'Update your review';
      notify('success', data.message || 'Review submitted.');
    } catch (error) {
      notify('error', error.message || 'Unable to save review.');
      if (btnText) btnText.textContent = 'Submit review';
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });
})();
</script>
@endpush
