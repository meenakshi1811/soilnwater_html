@php
  $name = $member['name'] ?? 'Faculty';
  $role = trim((string) ($member['role'] ?? 'Faculty member'));
  $subject = trim((string) ($member['subject'] ?? ''));
  $initial = strtoupper(substr($name, 0, 1));
@endphp
<article class="sch-faculty-card">
  <span class="sch-faculty-card__avatar" aria-hidden="true">{{ $initial }}</span>
  <div class="sch-faculty-card__body">
    <h3 class="sch-faculty-card__name">{{ $name }}</h3>
    <p class="sch-faculty-card__role mb-0">{{ $role }}</p>
    @if($subject !== '' && $subject !== 'Academics')
      <p class="sch-faculty-card__meta mb-0">{{ $subject }}</p>
    @endif
  </div>
</article>
