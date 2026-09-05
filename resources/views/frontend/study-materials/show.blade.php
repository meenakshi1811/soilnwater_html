@extends('frontend.layouts.app')
@section('meta_title', $material->title.' | Study Materials')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string)$material->description), 150) ?: 'Study material on SoilnWater')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/study-materials.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@php
  $contents = collect($material->contents ?? []);
  $userReview = $userReview ?? null;
  $selectedRating = (int) old('rating', $userReview?->rating ?: 5);
@endphp
<div class="sm-page" id="studyMaterialShowPage"
  data-bookmark-url="{{ route('study-materials.bookmark', $material->slug) }}"
  data-review-url="{{ route('study-materials.review', $material->slug) }}"
  data-is-saved="{{ $isBookmarked ? '1' : '0' }}"
>
  <section class="sm-hero">
    <div class="container">
      @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <span class="sm-chip">{{ $material->materialTypeLabel() }}</span>
          <h1 class="mt-2">{{ $material->title }}</h1>
          <p class="mb-0">{{ $material->subject ?: 'General' }} · {{ $material->class_course ?: 'All classes' }} · {{ $material->board_university ?: 'All boards' }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          @auth
            <a href="{{ route('study-materials.download', $material->slug) }}" class="sm-btn sm-btn-primary"><i class="fa-solid fa-download"></i> Download</a>
            <button
              type="button"
              class="sm-btn sm-btn-outline js-sm-save {{ $isBookmarked ? 'is-saved' : '' }}"
              data-label-saved="Saved"
              data-label-unsaved="Save"
            >
              <i class="fa-{{ $isBookmarked ? 'solid' : 'regular' }} fa-bookmark" aria-hidden="true"></i>
              <span class="js-sm-save-label">{{ $isBookmarked ? 'Saved' : 'Save' }}</span>
            </button>
          @else
            <a href="{{ route('login') }}" class="sm-btn sm-btn-primary"><i class="fa-solid fa-download"></i> Login to download</a>
          @endauth
        </div>
      </div>
    </div>
  </section>

  <div class="container sm-detail">
    <aside class="sm-sidebar">
      <h3>Contents</h3>
      @forelse($contents as $index => $item)
        <div class="small py-1 border-bottom">{{ $index + 1 }}. {{ $item }}</div>
      @empty
        <p class="sm-empty mb-0">No contents outline provided.</p>
      @endforelse
    </aside>

    <div>
      <div class="sm-panel">
        <div class="sm-viewer">
          @if($material->thumbnailUrl())
            <img src="{{ $material->thumbnailUrl() }}" alt="{{ $material->title }}" style="max-width:100%;max-height:280px;border-radius:12px">
          @else
            <i class="fa-solid fa-file-lines fa-3x" style="color:var(--sm-primary)"></i>
          @endif
          <div>
            <strong>{{ $material->file_name ?: 'Study file' }}</strong>
            <div class="sm-card__meta">{{ strtoupper((string)$material->file_type) }} · {{ $material->fileSizeLabel() }} · {{ $material->pages ? $material->pages.' pages' : '—' }}</div>
          </div>
          @auth
            <a href="{{ route('study-materials.download', $material->slug) }}" class="sm-btn sm-btn-primary">Open / Download</a>
          @endauth
        </div>
      </div>

      <div class="sm-panel">
        <div class="sm-tabs" id="materialTabs">
          <button type="button" class="sm-tab is-active" data-tab="description">Description</button>
          <button type="button" class="sm-tab" data-tab="details">Details</button>
          <button type="button" class="sm-tab" data-tab="related">Related</button>
          <button type="button" class="sm-tab" data-tab="reviews">Reviews</button>
        </div>

        <div class="sm-tab-panel is-active" data-panel="description">
          @if(filled($material->description))
            <div class="sm-richtext">{!! $material->description !!}</div>
          @else
            <p class="mb-0">No description provided.</p>
          @endif
          @if(is_array($material->tags) && count($material->tags))
            <div class="mt-2">@foreach($material->tags as $tag)<span class="sm-chip">{{ $tag }}</span>@endforeach</div>
          @endif
        </div>

        <div class="sm-tab-panel" data-panel="details">
          <ul class="list-unstyled mb-0">
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Subject</span><strong>{{ $material->subject ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Topic</span><strong>{{ $material->topic_chapter ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Exam</span><strong>{{ $material->exam_test ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Language</span><strong>{{ $material->language ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Difficulty</span><strong>{{ $material->difficulty ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Academic year</span><strong>{{ $material->academic_year ?: '—' }}</strong></li>
            <li class="py-1 d-flex justify-content-between"><span>Views / Downloads / Saves</span><strong><span class="js-sm-views">{{ number_format($material->views_count) }}</span> / {{ number_format($material->downloads_count) }} / <span class="js-sm-saves">{{ number_format($material->saves_count) }}</span></strong></li>
          </ul>
        </div>

        <div class="sm-tab-panel" data-panel="related">
          @forelse($related as $item)
            <a href="{{ $item->publicUrl() }}" class="sm-list-item">
              <img class="sm-list-thumb" src="{{ $item->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
              <div>
                <strong>{{ $item->title }}</strong>
                <div class="sm-card__meta">{{ $item->subject ?: $item->materialTypeLabel() }}</div>
              </div>
            </a>
          @empty
            <p class="sm-empty mb-0">No related materials.</p>
          @endforelse
        </div>

        <div class="sm-tab-panel" data-panel="reviews">
          <div class="sm-review-summary mb-3">
            <div class="sm-review-summary__score">
              <strong class="js-sm-avg-rating">{{ number_format((float) $material->average_rating, 1) }}</strong>
              <div class="sm-review-item__stars" aria-hidden="true">
                @foreach (range(1, 5) as $i)
                  <i class="fa-{{ $i <= (int) round((float) $material->average_rating) ? 'solid' : 'regular' }} fa-star"></i>
                @endforeach
              </div>
              <span class="sm-review-summary__count"><span class="js-sm-reviews-count">{{ number_format($material->reviews_count) }}</span> reviews</span>
            </div>
          </div>

          @auth
            <form id="smReviewForm" class="sm-review-form mb-4" novalidate>
              @csrf
              <h4 class="sm-review-form__title">{{ $userReview ? 'Update your review' : 'Write a review' }}</h4>
              <p class="sm-review-form__hint">Rate this material and share feedback for other learners.</p>

              <div class="sm-star-picker" role="radiogroup" aria-label="Your rating">
                <input type="hidden" name="rating" id="smReviewRating" value="{{ $selectedRating }}">
                @foreach (range(1, 5) as $stars)
                  <button
                    type="button"
                    class="sm-star-picker__btn {{ $stars <= $selectedRating ? 'is-active' : '' }}"
                    data-rating="{{ $stars }}"
                    aria-label="{{ $stars }} {{ $stars === 1 ? 'star' : 'stars' }}"
                  >
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                  </button>
                @endforeach
              </div>

              <label class="form-label mt-3" for="smReviewText">Your feedback</label>
              <textarea
                id="smReviewText"
                name="review"
                class="form-control"
                rows="3"
                maxlength="2000"
                placeholder="What did you find useful? Any tips for other students?"
              >{{ old('review', $userReview?->review) }}</textarea>

              <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <small class="text-muted">Click the stars to set your rating.</small>
                <button type="submit" class="sm-btn sm-btn-primary" id="smReviewSubmitBtn">
                  <span class="btn-text">{{ $userReview ? 'Update review' : 'Submit review' }}</span>
                </button>
              </div>
            </form>
          @else
            <div class="sm-review-login mb-4">
              <p class="mb-2">Sign in to leave a review for this material.</p>
              <a href="{{ route('login') }}" class="sm-btn sm-btn-outline">Login to review</a>
            </div>
          @endauth

          <div id="smReviewsList">
            @forelse($material->reviews as $review)
              @include('frontend.study-materials.partials.review-item', ['review' => $review])
            @empty
              <p class="sm-empty mb-0" id="smReviewsEmpty">No reviews yet. Be the first to share your thoughts.</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    <aside class="sm-sidebar">
      <h3>Material info</h3>
      <div class="mb-2">
        <span class="sm-chip">{{ $material->materialTypeLabel() }}</span>
        @if($material->is_free)<span class="sm-chip">Free</span>@endif
        @if($material->is_verified)<span class="sm-chip">Verified</span>@endif
      </div>
      <div class="small mb-3">
        <div><i class="fa-solid fa-star text-warning"></i> <span class="js-sm-avg-rating">{{ number_format((float)$material->average_rating, 1) }}</span> (<span class="js-sm-reviews-count">{{ number_format($material->reviews_count) }}</span>)</div>
        <div>{{ number_format($material->views_count) }} views · {{ number_format($material->downloads_count) }} downloads</div>
      </div>
      @auth
        <a href="{{ route('study-materials.download', $material->slug) }}" class="sm-btn sm-btn-primary w-100 mb-2"><i class="fa-solid fa-download"></i> Download</a>
        <button
          type="button"
          class="sm-btn sm-btn-outline w-100 mb-3 js-sm-save {{ $isBookmarked ? 'is-saved' : '' }}"
          data-label-saved="Saved"
          data-label-unsaved="Save"
        >
          <i class="fa-{{ $isBookmarked ? 'solid' : 'regular' }} fa-bookmark" aria-hidden="true"></i>
          <span class="js-sm-save-label">{{ $isBookmarked ? 'Saved' : 'Save' }}</span>
        </button>
      @endauth
      <ul class="list-unstyled small mb-3">
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Class / Course</span><strong>{{ $material->class_course ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Subject</span><strong>{{ $material->subject ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Chapter / Topic</span><strong>{{ $material->topic_chapter ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Board</span><strong>{{ $material->board_university ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Medium</span><strong>{{ $material->medium ?: $material->language ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Academic Year</span><strong>{{ $material->academic_year ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Pages</span><strong>{{ $material->pages ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>File Type</span><strong>{{ strtoupper((string)$material->file_type) ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>File Size</span><strong>{{ $material->fileSizeLabel() }}</strong></li>
        <li class="py-1 d-flex justify-content-between gap-2"><span>Upload Date</span><strong>{{ $material->created_at?->format('d M Y') ?: '—' }}</strong></li>
      </ul>
      <h3>Uploaded by</h3>
      @if($material->educator)
        <a href="{{ $material->educator->publicUrl() }}" class="sm-list-item">
          <img class="sm-list-thumb" src="{{ $material->educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
          <div>
            <strong>{{ $material->educator->display_name }}</strong>
            <div class="sm-card__meta">
              {{ $material->educator->professional_headline ?: $material->educator->roleLabel() }}
              @if($material->educator->isVerified())
                · Verified
              @endif
            </div>
          </div>
        </a>
      @endif
    </aside>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function () {
  var page = document.getElementById('studyMaterialShowPage');
  if (!page) return;

  var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  var bookmarkUrl = page.dataset.bookmarkUrl;
  var reviewUrl = page.dataset.reviewUrl;

  function notify(type, message) {
    if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3500
      };
      window.toastr[type](message);
      return;
    }
    alert(message);
  }

  function updateSaveButtons(saved) {
    document.querySelectorAll('.js-sm-save').forEach(function (btn) {
      btn.classList.toggle('is-saved', saved);
      var icon = btn.querySelector('i[class*="fa-bookmark"]');
      if (icon) {
        icon.className = (saved ? 'fa-solid' : 'fa-regular') + ' fa-bookmark';
        icon.setAttribute('aria-hidden', 'true');
      }
      var label = btn.querySelector('.js-sm-save-label');
      var text = saved ? (btn.dataset.labelSaved || 'Saved') : (btn.dataset.labelUnsaved || 'Save');
      if (label) label.textContent = text;
    });
  }

  document.querySelectorAll('.js-sm-save').forEach(function (btn) {
    btn.addEventListener('click', async function () {
      if (!bookmarkUrl) return;
      btn.disabled = true;
      try {
        var response = await fetch(bookmarkUrl, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: new URLSearchParams({ _token: csrf })
        });
        var data = await response.json().catch(function () { return {}; });
        if (!response.ok) throw new Error(data.message || 'Unable to update save.');
        updateSaveButtons(Boolean(data.saved || data.bookmarked));
        if (typeof data.saves_count !== 'undefined') {
          document.querySelectorAll('.js-sm-saves').forEach(function (el) {
            el.textContent = Number(data.saves_count).toLocaleString();
          });
        }
        notify('success', data.message || 'Updated.');
      } catch (error) {
        notify('error', error.message || 'Unable to update save.');
      } finally {
        btn.disabled = false;
      }
    });
  });

  document.querySelectorAll('#materialTabs .sm-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      document.querySelectorAll('#materialTabs .sm-tab').forEach(function (t) { t.classList.remove('is-active'); });
      document.querySelectorAll('[data-panel]').forEach(function (p) { p.classList.remove('is-active'); });
      tab.classList.add('is-active');
      document.querySelector('[data-panel="' + tab.dataset.tab + '"]')?.classList.add('is-active');
    });
  });

  var ratingInput = document.getElementById('smReviewRating');
  var starButtons = document.querySelectorAll('.sm-star-picker__btn');

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

  document.querySelector('.sm-star-picker')?.addEventListener('mouseleave', function () {
    paintStars(ratingInput?.value || 5);
  });

  var reviewForm = document.getElementById('smReviewForm');
  if (reviewForm) {
    reviewForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      var submitBtn = document.getElementById('smReviewSubmitBtn');
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
            review: document.getElementById('smReviewText')?.value || ''
          })
        });
        var data = await response.json().catch(function () { return {}; });
        if (!response.ok) {
          var firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
          throw new Error(firstError || data.message || 'Unable to save review.');
        }

        document.querySelectorAll('.js-sm-avg-rating').forEach(function (el) {
          el.textContent = data.average_rating;
        });
        document.querySelectorAll('.js-sm-reviews-count').forEach(function (el) {
          el.textContent = Number(data.reviews_count || 0).toLocaleString();
        });

        var list = document.getElementById('smReviewsList');
        var empty = document.getElementById('smReviewsEmpty');
        if (empty) empty.remove();

        if (list && data.review_html) {
          var existing = list.querySelector('[data-review-user="{{ auth()->id() }}"]');
          if (existing) existing.remove();
          list.insertAdjacentHTML('afterbegin', data.review_html);
        }

        if (btnText) btnText.textContent = 'Update review';
        var title = reviewForm.querySelector('.sm-review-form__title');
        if (title) title.textContent = 'Update your review';
        notify('success', data.message || 'Review submitted.');
      } catch (error) {
        notify('error', error.message || 'Unable to save review.');
      } finally {
        if (submitBtn) submitBtn.disabled = false;
        if (btnText && btnText.textContent === 'Saving...') {
          btnText.textContent = 'Submit review';
        }
      }
    });
  }
})();
</script>
@endpush
