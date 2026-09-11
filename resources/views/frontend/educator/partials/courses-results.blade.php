@if($courses->count())
  <div class="edu-courses-page__grid">
    @foreach($courses as $course)
      @include('frontend.educator.partials.course-card', ['course' => $course, 'showActions' => true])
    @endforeach
  </div>

  @if($courses->hasPages())
    <nav class="edu-courses-pagination" aria-label="Courses pagination">
      <ul class="pagination mb-0">
        @if($courses->onFirstPage())
          <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
        @else
          <li class="page-item"><a class="page-link js-edu-courses-page" href="{{ $courses->previousPageUrl() }}" data-page="{{ $courses->currentPage() - 1 }}">&lsaquo;</a></li>
        @endif

        @foreach($courses->getUrlRange(max(1, $courses->currentPage() - 2), min($courses->lastPage(), $courses->currentPage() + 2)) as $page => $url)
          <li class="page-item {{ $page === $courses->currentPage() ? 'active' : '' }}">
            @if($page === $courses->currentPage())
              <span class="page-link">{{ $page }}</span>
            @else
              <a class="page-link js-edu-courses-page" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
            @endif
          </li>
        @endforeach

        @if($courses->hasMorePages())
          <li class="page-item"><a class="page-link js-edu-courses-page" href="{{ $courses->nextPageUrl() }}" data-page="{{ $courses->currentPage() + 1 }}">Next &rsaquo;</a></li>
        @else
          <li class="page-item disabled"><span class="page-link">Next &rsaquo;</span></li>
        @endif
      </ul>
    </nav>
  @endif
@else
  <div class="edu-courses-empty">
    <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
    <h3>No courses found</h3>
    <p>Try adjusting your search or filters to find courses from this educator.</p>
  </div>
@endif
