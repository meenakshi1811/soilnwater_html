{{-- A visitor's review of the school (one each: writing again updates it; not on one's own page) --}}
@php
  $reviewer = auth()->user();
  $ownReview = $reviewer ? $institute->visitorReviews()->where('user_id', $reviewer->id)->first() : null;
  $isOwnProfile = $reviewer && (int) $reviewer->id === (int) $institute->user_id;
  $reviewEntity = strtolower($entityLabel ?? 'school');
@endphp
<div class="sch-review-form-wrap">
  @guest
    <p class="mb-0">
      <a href="{{ route('login') }}?redirect={{ urlencode($institute->publicUrl().'#sch-reviews') }}">Log in</a>
      to rate and review this {{ $reviewEntity }}.
    </p>
  @else
    @if($isOwnProfile)
      <p class="mb-0 text-muted small">Parents and students can rate and review your {{ $reviewEntity }} here.</p>
    @else
      <form method="POST" action="{{ route(($listingContext ?? 'schools').'.review', $institute->slug) }}" class="sch-review-form">
        @csrf
        <h3 class="sch-review-form__title">{{ $ownReview ? 'Update your review' : 'Rate this '.$reviewEntity }}</h3>
        <div class="row g-2">
          <div class="col-sm-6">
            <label class="form-label small mb-1" for="sch_review_rating">Your rating *</label>
            <select class="form-select" id="sch_review_rating" name="rating" required>
              @foreach([5 => 'Excellent', 4 => 'Very good', 3 => 'Good', 2 => 'Fair', 1 => 'Poor'] as $stars => $word)
                <option value="{{ $stars }}" @selected((int) old('rating', $ownReview->rating ?? 5) === $stars)>{{ str_repeat('★', $stars) }} {{ $word }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label small mb-1" for="sch_review_relation">I am a *</label>
            <select class="form-select" id="sch_review_relation" name="relation" required>
              @foreach(\App\Models\InstituteReview::RELATIONS as $relation)
                <option value="{{ $relation }}" @selected(old('relation', $ownReview->relation ?? 'Parent') === $relation)>{{ $relation }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small mb-1" for="sch_review_text">Your review</label>
            <textarea class="form-control" id="sch_review_text" name="review" rows="3" maxlength="1000" placeholder="What do you like about this {{ $reviewEntity }}?">{{ old('review', $ownReview->review ?? '') }}</textarea>
          </div>
        </div>
        @if($errors->has('rating') || $errors->has('relation') || $errors->has('review'))
          <div class="text-danger small mt-2">{{ $errors->first('rating') ?: ($errors->first('relation') ?: $errors->first('review')) }}</div>
        @endif
        <button type="submit" class="sch-btn sch-btn-primary mt-3">{{ $ownReview ? 'Update review' : 'Submit review' }}</button>
      </form>
    @endif
  @endguest
</div>
