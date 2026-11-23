@forelse ($educators as $educator)
  @php
    $photo = $educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp');
    $subject = $educator->primarySubject() ?: $educator->roleLabel();
    $location = $educator->locationLabel() ?: 'India';
    // (the same experience figure as on the teacher's profile)
    $yearsExperience = $educator->effectiveYearsExperience();
    $distance = isset($educator->distance_km) && ($hasLocation ?? false)
      ? number_format((float) $educator->distance_km, 1).' km'
      : null;
  @endphp
  <article class="vendors-compact-card educators-card{{ $educator->is_verified ? ' is-verified' : '' }}">
    <a href="{{ $educator->publicUrl() }}" class="vendors-compact-card__media educators-card__media" aria-label="View {{ $educator->display_name }} profile">
      @if($educator->is_verified)
        <span class="vendors-compact-card__badge educators-card__badge" title="Profile reviewed by SoilnWater"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Reviewed</span>
      @elseif($educator->take_tuitions)
        <span class="vendors-compact-card__badge educators-card__badge educators-card__badge--tuition"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i> Tuition</span>
      @endif
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
      {{-- (stars only for teachers with reviews: no "0.0 (0)") --}}
      @if((int) $educator->reviews_count > 0)
        <div class="vendors-compact-card__rating">
          <span class="vendors-rating"><i class="fa-solid fa-star" aria-hidden="true"></i>{{ number_format((float) $educator->average_rating, 1) }}</span>
          <span class="vendors-rating-count">({{ number_format((int) $educator->reviews_count) }})</span>
        </div>
      @endif
      <div class="vendors-compact-card__meta">
        <span class="vendors-compact-card__category">{{ $subject }}</span>
        @if($educator->is_verified)
          <span class="vendors-verified-badge vendors-verified-badge--sm" title="Profile reviewed by SoilnWater"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Reviewed</span>
        @endif
      </div>
      <p class="vendors-compact-card__location">
        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $location }}{{ $distance ? ' · '.$distance : '' }}
      </p>
      @if($yearsExperience > 0)
        <p class="educators-card__experience">
          <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
          {{ $yearsExperience }}+ {{ \Illuminate\Support\Str::plural('year', $yearsExperience) }} experience
        </p>
      @endif
      <p class="educators-card__headline">{{ \Illuminate\Support\Str::limit($educator->professional_headline ?: $educator->roleLabel(), 70) }}</p>
      {{-- (only figures that say something: no "0 materials · 0 students") --}}
      @if((int) $educator->materials_count > 0 || (int) $educator->students_taught > 0)
        <div class="educators-card__stats">
          @if((int) $educator->materials_count > 0)
            <span><i class="fa-solid fa-book-open" aria-hidden="true"></i> {{ number_format((int) $educator->materials_count) }} {{ \Illuminate\Support\Str::plural('material', (int) $educator->materials_count) }}</span>
          @endif
          @if((int) $educator->students_taught > 0)
            <span><i class="fa-solid fa-user-graduate" aria-hidden="true"></i> {{ number_format((int) $educator->students_taught) }} students</span>
          @endif
        </div>
      @endif
    </div>
  </article>
@empty
  <div class="vendors-empty-state">
    <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-chalkboard-user"></i></div>
    <h3>No teachers or tutors found</h3>
    <p>Try adjusting your filters or search term.</p>
  </div>
@endforelse
