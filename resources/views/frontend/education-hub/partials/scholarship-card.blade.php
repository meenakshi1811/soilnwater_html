{{-- A scholarship in the Education Hub: its type and last date, title, benefit, who it's for, the
     criteria in short, who offers it, and a link to its full details on their page --}}
@php
  [, $kindIcon, $kindTone, $kindOne] = \App\Http\Controllers\Frontend\EducationHubController::NOTICE_KINDS[$item['kind']];
  $daysLeft = $item['deadline'] ? (int) now()->startOfDay()->diffInDays($item['deadline'], false) : null;
@endphp
<a href="{{ $item['url'] }}" class="eh-scholarship">
  <span class="eh-scholarship__top">
    <span class="eh-scholarship__type"><i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i> {{ $item['type'] }}</span>
    @if($item['start'])
      <span class="eh-scholarship__when eh-scholarship__when--soon">Opens {{ $item['start']->format('j M') }}</span>
    @elseif($daysLeft !== null)
      <span class="eh-scholarship__when {{ $daysLeft <= 7 ? 'eh-scholarship__when--near' : '' }}">
        {{ $daysLeft === 0 ? 'Last day today' : ($daysLeft <= 7 ? $daysLeft.' '.($daysLeft === 1 ? 'day' : 'days').' left' : 'Apply by '.$item['deadline']->format('j M Y')) }}
      </span>
    @else
      <span class="eh-scholarship__when">Open</span>
    @endif
  </span>

  <strong class="eh-scholarship__title">{{ $item['title'] }}</strong>
  @if(filled($item['amount']))
    <span class="eh-scholarship__amount"><i class="fa-solid fa-gift" aria-hidden="true"></i> {{ $item['amount'] }}</span>
  @endif

  @if(filled($item['for']) || $item['awards'])
    <span class="eh-scholarship__facts">
      @if(filled($item['for']))
        <span><i class="fa-solid fa-user-graduate" aria-hidden="true"></i> {{ $item['for'] }}</span>
      @endif
      @if($item['awards'])
        <span><i class="fa-solid fa-users" aria-hidden="true"></i> {{ number_format($item['awards']) }} {{ $item['awards'] === 1 ? 'scholarship' : 'scholarships' }}</span>
      @endif
    </span>
  @endif
  @if(filled($item['criteria']))
    <span class="eh-scholarship__criteria">{{ $item['criteria'] }}</span>
  @endif

  <span class="eh-scholarship__org">
    @if($item['logo'])
      <img src="{{ $item['logo'] }}" alt="" class="eh-scholarship__logo" loading="lazy" width="34" height="34">
    @else
      <span class="eh-icon eh-icon--{{ $kindTone }} eh-scholarship__logo"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i></span>
    @endif
    <span class="eh-scholarship__org-text">
      <span class="eh-scholarship__org-name">
        {{ $item['institute'] }}
        @if($item['verified'])
          <i class="fa-solid fa-circle-check" aria-label="Verified"></i>
        @endif
      </span>
      <small>{{ $kindOne }}@if(filled($item['location'])) · {{ $item['location'] }}@endif</small>
    </span>
  </span>
  <span class="eh-scholarship__foot">
    <span>View details</span>
    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
  </span>
</a>
