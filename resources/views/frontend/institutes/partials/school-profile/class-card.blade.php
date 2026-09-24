<article class="sch-class-card sch-class-card--carousel">
  <h4 class="sch-class-card__title">{{ $class->displayLabel() }}</h4>
  @if($class->class_teacher)
    <p class="sch-class-card__meta"><i class="fa-solid fa-user-tie" aria-hidden="true"></i> {{ $class->class_teacher }}</p>
  @endif
  @if($class->strength)
    <p class="sch-class-card__meta"><i class="fa-solid fa-users" aria-hidden="true"></i> {{ $class->strength }} students</p>
  @endif
  @if($class->room)
    <p class="sch-class-card__meta"><i class="fa-solid fa-door-open" aria-hidden="true"></i> {{ $class->room }}</p>
  @endif
  @if($class->description)
    <p class="sch-class-card__desc mb-0">{{ $class->description }}</p>
  @endif
</article>
