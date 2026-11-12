{{-- A course or program: its photo (or icon), who offers it, title, classes / duration line, a
     short description, the institution (logo, name, place) and a link to its courses --}}
@php
  [, $kindIcon, $kindTone, $kindOne] = \App\Http\Controllers\Frontend\EducationHubController::COURSE_KINDS[$course['kind']];
@endphp
<a href="{{ $course['url'] }}" class="eh-course">
  <span class="eh-course__media eh-course__media--{{ $kindTone }}">
    @if($course['image'])
      <img src="{{ $course['image'] }}" alt="" loading="lazy" decoding="async">
    @else
      <i class="fa-solid {{ $course['icon'] }}" aria-hidden="true"></i>
    @endif
    <span class="eh-course__badge eh-course__badge--{{ $kindTone }}"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i> {{ $kindOne }}</span>
  </span>
  <span class="eh-course__body">
    <strong class="eh-course__title">{{ $course['title'] }}</strong>
    @if(filled($course['grades']))
      <span class="eh-course__grades"><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $course['grades'] }}</span>
    @endif
    @if(filled($course['description']))
      <span class="eh-course__text">{{ $course['description'] }}</span>
    @endif
    <span class="eh-course__org">
      @if($course['logo'])
        <img src="{{ $course['logo'] }}" alt="" class="eh-course__logo" loading="lazy" width="34" height="34">
      @else
        <span class="eh-icon eh-icon--{{ $kindTone }} eh-course__logo"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i></span>
      @endif
      <span class="eh-course__org-text">
        <span class="eh-course__org-name">
          {{ $course['institute'] }}
          @if($course['verified'])
            <i class="fa-solid fa-circle-check" aria-label="Verified"></i>
          @endif
        </span>
        @if(filled($course['location']))
          <small><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $course['location'] }}</small>
        @endif
      </span>
    </span>
    <span class="eh-course__foot">
      <span>View course</span>
      <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </span>
  </span>
</a>
