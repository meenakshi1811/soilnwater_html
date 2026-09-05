@forelse ($featuredEducators as $educator)
  @php
    $photo = $educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp');
    $subject = $educator->primarySubject() ?: 'Teacher / Tutor';
  @endphp
  <article class="vendors-compact-card educators-card educators-card--featured is-verified">
    <a href="{{ $educator->publicUrl() }}" class="vendors-compact-card__media educators-card__media" aria-label="View {{ $educator->display_name }} profile">
      <span class="vendors-compact-card__badge educators-card__badge"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
      <img
        src="{{ $photo }}"
        alt="{{ $educator->display_name }}"
        class="vendors-compact-card__cover educators-card__photo"
        loading="lazy"
        onerror="this.onerror=null;this.src='{{ asset('assets/images/logo_soilnwater.webp') }}';"
      >
    </a>
    <div class="vendors-compact-card__body">
      <h3 class="vendors-compact-card__name">
        <a href="{{ $educator->publicUrl() }}">{{ $educator->display_name }}</a>
      </h3>
      <div class="vendors-compact-card__rating">
        <span class="vendors-rating"><i class="fa-solid fa-star" aria-hidden="true"></i>{{ number_format((float) $educator->average_rating, 1) }}</span>
        <span class="vendors-rating-count">({{ number_format((int) $educator->reviews_count) }})</span>
      </div>
      <div class="vendors-compact-card__meta">
        <span class="vendors-compact-card__category">{{ $subject }}</span>
      </div>
      <p class="vendors-compact-card__location">
        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $educator->locationLabel() ?: 'India' }}
      </p>
    </div>
  </article>
@empty
@endforelse
