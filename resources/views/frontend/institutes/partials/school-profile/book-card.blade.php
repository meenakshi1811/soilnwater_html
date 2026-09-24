@php
  $subjectKey = strtolower((string) $book->subject);
  $bookIcon = match (true) {
      str_contains($subjectKey, 'math') => 'fa-calculator',
      str_contains($subjectKey, 'phys') => 'fa-atom',
      str_contains($subjectKey, 'chem') => 'fa-flask',
      str_contains($subjectKey, 'bio') => 'fa-dna',
      str_contains($subjectKey, 'english') => 'fa-feather-pointed',
      str_contains($subjectKey, 'science') => 'fa-microscope',
      default => 'fa-book-open',
  };
  $metaParts = array_filter([$book->class_name, $book->subject]);
  $tone = ($tone ?? (($index ?? 0) % 6) + 1);
@endphp
<article class="sch-book-card sch-book-card--row sch-book-card--tone-{{ $tone }}">
  <div class="sch-book-card__media">
    @if($book->coverUrl())
      <img src="{{ $book->coverUrl() }}" alt="" class="sch-book-card__cover-img">
    @else
      <span class="sch-book-card__cover-icon" aria-hidden="true">
        <i class="fa-solid {{ $bookIcon }}"></i>
      </span>
    @endif
  </div>
  <div class="sch-book-card__body">
    @if($metaParts !== [])
      <p class="sch-book-card__meta">{{ implode(' · ', $metaParts) }}</p>
    @endif
    <h3 class="sch-book-card__title">{{ $book->title }}</h3>
    <p class="sch-book-card__author">
      <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
      <span>{{ $book->author }}</span>
    </p>
    @if($book->publisher)
      <p class="sch-book-card__publisher">{{ $book->publisher }}</p>
    @endif
  </div>
</article>
