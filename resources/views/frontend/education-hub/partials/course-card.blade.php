{{-- A course or program, as a compact listing card: its photo (or icon), who offers it, title,
     classes / duration, the institution and place, and a line of what it covers --}}
@php
  [, $kindIcon, $kindTone, $kindOne] = \App\Http\Controllers\Frontend\EducationHubController::COURSE_KINDS[$course['kind']];
@endphp
<article class="vendors-compact-card course-card">
  <a href="{{ $course['url'] }}" class="vendors-compact-card__media course-card__media course-card__media--{{ $kindTone }}" aria-label="{{ $course['title'] }} at {{ $course['institute'] }}">
    @if($course['image'])
      <img src="{{ $course['image'] }}" alt="" class="vendors-compact-card__cover" loading="lazy" decoding="async">
    @else
      <i class="fa-solid {{ $course['icon'] }} course-card__icon" aria-hidden="true"></i>
    @endif
    <span class="course-card__badge course-card__badge--{{ $kindTone }}"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i> {{ $kindOne }}</span>
  </a>
  <div class="vendors-compact-card__body">
    <h3 class="vendors-compact-card__name">
      <a href="{{ $course['url'] }}">{{ $course['title'] }}</a>
    </h3>
    @if(filled($course['grades']))
      <div class="vendors-compact-card__meta">
        <span class="vendors-compact-card__category"><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $course['grades'] }}</span>
      </div>
    @endif
    <p class="vendors-compact-card__location course-card__org">
      <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
      <span>{{ $course['institute'] }}</span>
      @if($course['verified'])
        <i class="fa-solid fa-circle-check course-card__verified" aria-label="Verified"></i>
      @endif
    </p>
    @if(filled($course['location']))
      <p class="vendors-compact-card__location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $course['location'] }}</p>
    @endif
    @if(filled($course['description']))
      <p class="course-card__text">{{ $course['description'] }}</p>
    @endif
  </div>
</article>
