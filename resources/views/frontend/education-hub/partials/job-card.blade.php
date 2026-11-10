{{-- An open job: its type, title, who posted it, place, experience, salary, last date, and a link
     to their jobs (where people apply) --}}
@php
  [, $kindIcon, $kindTone, $kindOne] = \App\Http\Controllers\Frontend\EducationHubController::NOTICE_KINDS[$job['kind']];
@endphp
<a href="{{ $job['url'] }}" class="eh-job">
  <span class="eh-job__top">
    <span class="eh-job__type">{{ $job['type'] }}</span>
    @if($job['posted'])
      <span class="eh-job__posted">{{ $job['posted']->diffForHumans(null, true) }} ago</span>
    @endif
  </span>
  <strong class="eh-job__title">{{ $job['title'] }}</strong>
  @if(filled($job['department']))
    <span class="eh-job__dept">{{ $job['department'] }}</span>
  @endif
  <span class="eh-job__org">
    @if($job['logo'])
      <img src="{{ $job['logo'] }}" alt="" class="eh-job__logo" loading="lazy" width="28" height="28">
    @else
      <span class="eh-icon eh-icon--{{ $kindTone }} eh-job__logo"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i></span>
    @endif
    <span class="eh-job__org-text">
      <span class="eh-job__org-name">{{ $job['institute'] }}</span>
      <small>{{ $kindOne }}</small>
    </span>
  </span>
  <span class="eh-job__meta">
    @if(filled($job['location']))
      <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $job['location'] }}</span>
    @endif
    @if(filled($job['experience']))
      <span><i class="fa-solid fa-user-graduate" aria-hidden="true"></i> {{ $job['experience'] }}</span>
    @endif
    @if(filled($job['salary']))
      <span><i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i> {{ $job['salary'] }}</span>
    @endif
  </span>
  <span class="eh-job__foot">
    <span class="eh-job__deadline">
      @if($job['deadline'])
        <i class="fa-regular fa-calendar" aria-hidden="true"></i> Apply by {{ $job['deadline']->format('j M Y') }}
      @else
        <i class="fa-regular fa-calendar" aria-hidden="true"></i> Open till filled
      @endif
    </span>
    <span class="eh-job__cta">View &amp; apply <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
  </span>
</a>
