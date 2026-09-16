@forelse ($institutes as $institute)
  @php
    $photo = $institute->logoUrl() ?: asset('assets/images/logo_soilnwater.webp');
    $location = $institute->locationLabel() ?: 'India';
    $distance = isset($institute->distance_km) && ($hasLocation ?? false)
      ? number_format((float) $institute->distance_km, 1).' km'
      : null;
  @endphp
  <article class="vendors-compact-card institutes-card{{ $institute->is_verified ? ' is-verified' : '' }}">
    <a href="{{ $institute->publicUrl() }}" class="vendors-compact-card__media institutes-card__media" aria-label="View {{ $institute->displayName() }} profile">
      @if($institute->is_verified)
        <span class="vendors-compact-card__badge institutes-card__badge"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
      @endif
      <img
        src="{{ $photo }}"
        alt="{{ $institute->displayName() }}"
        class="vendors-compact-card__cover institutes-card__photo"
        loading="lazy"
        onerror="this.onerror=null;this.src='{{ asset('assets/images/logo_soilnwater.webp') }}';"
      >
    </a>
    <div class="vendors-compact-card__body">
      <h3 class="vendors-compact-card__name">
        <a href="{{ $institute->publicUrl() }}">{{ $institute->displayName() }}</a>
      </h3>
      <div class="vendors-compact-card__meta">
        <span class="vendors-compact-card__category">{{ $institute->institutionTypeLabel() }}</span>
        @if($institute->board_affiliation)
          <span class="institutes-card__board">{{ $institute->board_affiliation }}</span>
        @endif
      </div>
      <p class="vendors-compact-card__location">
        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $location }}{{ $distance ? ' · '.$distance : '' }}
      </p>
      <p class="institutes-card__tagline">{{ \Illuminate\Support\Str::limit($institute->tagline ?: $institute->about, 70) }}</p>
    </div>
  </article>
@empty
  <div class="vendors-empty-state">
    <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-school"></i></div>
    <h3>No schools or institutes found</h3>
    <p>Try adjusting your filters or search term.</p>
  </div>
@endforelse
