@php
  $reviewText = trim((string) ($review->review ?? ''));
@endphp
<div class="sm-review-item" data-review-id="{{ $review->id }}" data-review-user="{{ $review->user_id }}">
  <div class="sm-review-item__head">
    <div class="sm-review-item__avatar" aria-hidden="true">
      {{ strtoupper(substr($review->user?->name ?: 'U', 0, 1)) }}
    </div>
    <div>
      <strong>{{ $review->user?->name ?: 'User' }}</strong>
      <div class="sm-review-item__stars" aria-label="{{ $review->rating }} out of 5">
        @foreach (range(1, 5) as $i)
          <i class="fa-{{ $i <= (int) $review->rating ? 'solid' : 'regular' }} fa-star"></i>
        @endforeach
      </div>
    </div>
    <span class="sm-review-item__date">{{ $review->updated_at?->diffForHumans() }}</span>
  </div>
  @if($reviewText !== '')
    <p class="sm-review-item__body mb-0">{{ $reviewText }}</p>
  @endif
</div>
