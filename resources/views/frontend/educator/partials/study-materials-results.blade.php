@if($materials->count())
  <div class="edu-sm-grid">
    @foreach($materials as $material)
      @include('frontend.educator.partials.study-material-card', ['material' => $material, 'showActions' => true])
    @endforeach
  </div>

  @if($materials->hasPages())
    <nav class="edu-courses-pagination" aria-label="Study materials pagination">
      <ul class="pagination mb-0">
        @if($materials->onFirstPage())
          <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
        @else
          <li class="page-item"><a class="page-link js-edu-sm-page" href="{{ $materials->previousPageUrl() }}" data-page="{{ $materials->currentPage() - 1 }}">&lsaquo;</a></li>
        @endif

        @foreach($materials->getUrlRange(max(1, $materials->currentPage() - 2), min($materials->lastPage(), $materials->currentPage() + 2)) as $page => $url)
          <li class="page-item {{ $page === $materials->currentPage() ? 'active' : '' }}">
            @if($page === $materials->currentPage())
              <span class="page-link">{{ $page }}</span>
            @else
              <a class="page-link js-edu-sm-page" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
            @endif
          </li>
        @endforeach

        @if($materials->hasMorePages())
          <li class="page-item"><a class="page-link js-edu-sm-page" href="{{ $materials->nextPageUrl() }}" data-page="{{ $materials->currentPage() + 1 }}">Next &rsaquo;</a></li>
        @else
          <li class="page-item disabled"><span class="page-link">Next &rsaquo;</span></li>
        @endif
      </ul>
    </nav>
  @endif
@else
  <div class="edu-courses-empty">
    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
    <h3>No study materials yet</h3>
    <p>This educator has not published any study materials matching your filters.</p>
  </div>
@endif
