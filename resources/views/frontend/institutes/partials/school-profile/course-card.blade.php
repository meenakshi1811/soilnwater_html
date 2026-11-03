{{-- A "Courses & Programs Offered" card: its photo (or icon), title, classes and description --}}
<article class="sch-course-card">
  @if(filled($wing['image'] ?? null))
    <img src="{{ asset($wing['image']) }}" alt="" class="sch-course-card__image" loading="lazy" decoding="async">
  @else
    <span class="sch-course-card__icon"><i class="fa-solid {{ $wing['icon'] }}" aria-hidden="true"></i></span>
  @endif
  <div class="sch-course-card__body">
    <h3>{{ $wing['title'] }}</h3>
    @if(filled($wing['grades']))
      <p class="sch-course-card__grades">{{ $wing['grades'] }}</p>
    @endif
    @if(filled($wing['description']))
      <p class="sch-course-card__desc">{{ $wing['description'] }}</p>
    @endif
  </div>
</article>
