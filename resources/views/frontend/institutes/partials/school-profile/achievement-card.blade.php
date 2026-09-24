@php
  $achievementImage = $achievement->imageUrl();
  $metaParts = array_filter([$achievement->category, $achievement->year]);
  $carousel = ! empty($carousel);
@endphp
<article class="sch-achievement-card {{ $achievementImage ? 'sch-achievement-card--with-image' : 'sch-achievement-card--text-only' }}{{ $carousel ? ' sch-achievement-card--carousel' : '' }}">
  @if($achievementImage)
    <div class="sch-achievement-card__media">
      <img src="{{ $achievementImage }}" alt="{{ $achievement->title }}" loading="lazy" decoding="async">
    </div>
  @endif
  <div class="sch-achievement-card__body">
    @if($metaParts !== [])
      <p class="sch-achievement-card__meta">{{ implode(' ', $metaParts) }}</p>
    @endif
    <h3 class="sch-achievement-card__title">{{ $achievement->title }}</h3>
    @if($achievement->description)
      <p class="sch-achievement-card__desc">{{ $achievement->description }}</p>
    @endif
  </div>
</article>
