<article class="sch-facility-card">
  @if(!empty($facility['image']))
    <img src="{{ $facility['image'] }}" alt="{{ $facility['name'] }}" loading="lazy" decoding="async">
  @else
    <div class="sch-facility-card__placeholder"><i class="fa-solid fa-building" aria-hidden="true"></i></div>
  @endif
  <span>{{ $facility['name'] }}</span>
</article>
