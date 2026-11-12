{{-- A scholarship as a compact listing card: its type and last date, the name, the benefit, who it's
     for, who offers it, and a link to its full details on their page --}}
@php
  [, $kindIcon, $kindTone] = \App\Http\Controllers\Frontend\EducationHubController::NOTICE_KINDS[$item['kind']];
  $daysLeft = $item['deadline'] ? (int) now()->startOfDay()->diffInDays($item['deadline'], false) : null;
@endphp
<article class="vendors-compact-card hub-card hub-card--scholarship">
  <div class="hub-card__top">
    <span class="hub-card__chip hub-card__chip--green"><i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i> {{ $item['type'] }}</span>
    @if($item['start'])
      <span class="hub-card__when hub-card__when--soon">Opens {{ $item['start']->format('j M') }}</span>
    @elseif($daysLeft !== null)
      <span class="hub-card__when {{ $daysLeft <= 7 ? 'hub-card__when--near' : '' }}">
        {{ $daysLeft === 0 ? 'Last day today' : ($daysLeft <= 7 ? $daysLeft.' '.($daysLeft === 1 ? 'day' : 'days').' left' : 'By '.$item['deadline']->format('j M Y')) }}
      </span>
    @endif
  </div>
  <h3 class="hub-card__title"><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></h3>
  @if(filled($item['amount']))
    <p class="hub-card__amount"><i class="fa-solid fa-gift" aria-hidden="true"></i> {{ $item['amount'] }}</p>
  @endif
  <p class="hub-card__org">
    @if($item['logo'])
      <img src="{{ $item['logo'] }}" alt="" class="hub-card__logo" loading="lazy" width="24" height="24">
    @else
      <span class="eh-icon eh-icon--{{ $kindTone }} hub-card__logo"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i></span>
    @endif
    <span class="hub-card__org-name">{{ $item['institute'] }}</span>
    @if($item['verified'])
      <i class="fa-solid fa-circle-check hub-card__verified" aria-label="Verified"></i>
    @endif
  </p>
  <ul class="hub-card__facts">
    @if(filled($item['for']))
      <li><i class="fa-solid fa-user-graduate" aria-hidden="true"></i> {{ $item['for'] }}</li>
    @endif
    @if($item['awards'])
      <li><i class="fa-solid fa-users" aria-hidden="true"></i> {{ number_format($item['awards']) }} {{ $item['awards'] === 1 ? 'scholarship' : 'scholarships' }}</li>
    @endif
    @if(filled($item['location']))
      <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $item['location'] }}</li>
    @endif
  </ul>
  <div class="hub-card__foot">
    @if(filled($item['criteria']))
      <span class="hub-card__deadline hub-card__criteria" title="{{ $item['criteria'] }}"><i class="fa-solid fa-list-check" aria-hidden="true"></i> {{ $item['criteria'] }}</span>
    @else
      <span></span>
    @endif
    <a href="{{ $item['url'] }}" class="hub-card__cta hub-card__cta--green" aria-label="View details: {{ $item['title'] }}">View details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
  </div>
</article>
