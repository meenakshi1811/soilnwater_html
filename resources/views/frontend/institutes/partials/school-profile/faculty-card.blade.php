@php
  $name = $member['name'] ?? 'Faculty';
  $role = trim((string) ($member['role'] ?? 'Faculty member'));
  $subject = trim((string) ($member['subject'] ?? ''));
  $initial = strtoupper(substr($name, 0, 1));
  $profileUrl = $member['profile_url'] ?? null;
  $photoUrl = $member['photo_url'] ?? null;
@endphp
@if(filled($profileUrl))
  <a href="{{ $profileUrl }}" class="sch-faculty-card sch-faculty-card--linked" aria-label="View {{ $name }} teacher profile">
@else
  <article class="sch-faculty-card">
@endif
  @if($photoUrl)
    <img src="{{ $photoUrl }}" alt="" class="sch-faculty-card__avatar sch-faculty-card__avatar--photo">
  @else
    <span class="sch-faculty-card__avatar" aria-hidden="true">{{ $initial }}</span>
  @endif
  <div class="sch-faculty-card__body">
    <h3 class="sch-faculty-card__name">{{ $name }}</h3>
    <p class="sch-faculty-card__role mb-0">{{ $role }}</p>
    @if($subject !== '' && $subject !== 'Academics')
      <p class="sch-faculty-card__meta mb-0">{{ $subject }}</p>
    @endif
    @if($profileUrl)
      <span class="sch-faculty-card__link-label">View teacher profile <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
    @endif
  </div>
@if(filled($profileUrl))
  </a>
@else
  </article>
@endif
