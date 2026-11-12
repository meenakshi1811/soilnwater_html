{{-- An open job as a compact listing card: its type and when posted, the title, who posted it,
     place, experience, salary, the last date, and a link to their jobs (where people apply) --}}
@php
  [, $kindIcon, $kindTone, $kindOne] = \App\Http\Controllers\Frontend\EducationHubController::NOTICE_KINDS[$job['kind']];
@endphp
<article class="vendors-compact-card hub-card hub-card--job">
  <div class="hub-card__top">
    <span class="hub-card__chip hub-card__chip--purple">{{ $job['type'] }}</span>
    @if($job['posted'])
      <span class="hub-card__when">{{ $job['posted']->diffForHumans(null, true) }} ago</span>
    @endif
  </div>
  <h3 class="hub-card__title"><a href="{{ $job['url'] }}">{{ $job['title'] }}</a></h3>
  @if(filled($job['department']))
    <p class="hub-card__sub">{{ $job['department'] }}</p>
  @endif
  <p class="hub-card__org">
    @if($job['logo'])
      <img src="{{ $job['logo'] }}" alt="" class="hub-card__logo" loading="lazy" width="24" height="24">
    @else
      <span class="eh-icon eh-icon--{{ $kindTone }} hub-card__logo"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i></span>
    @endif
    <span class="hub-card__org-name">{{ $job['institute'] }}</span>
    @if($job['verified'])
      <i class="fa-solid fa-circle-check hub-card__verified" aria-label="Verified"></i>
    @endif
  </p>
  <ul class="hub-card__facts">
    @if(filled($job['location']))
      <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $job['location'] }}</li>
    @endif
    @if(filled($job['experience']))
      <li><i class="fa-solid fa-user-graduate" aria-hidden="true"></i> {{ $job['experience'] }}</li>
    @endif
    @if(filled($job['salary']))
      <li><i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i> {{ $job['salary'] }}</li>
    @endif
  </ul>
  <div class="hub-card__foot">
    <span class="hub-card__deadline">
      <i class="fa-regular fa-calendar" aria-hidden="true"></i>
      {{ $job['deadline'] ? 'Apply by '.$job['deadline']->format('j M Y') : 'Open till filled' }}
    </span>
    <a href="{{ $job['url'] }}" class="hub-card__cta hub-card__cta--purple" aria-label="View and apply: {{ $job['title'] }}">View &amp; apply <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
  </div>
</article>
