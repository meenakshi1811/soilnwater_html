@if($notes->count())
  <div class="edu-notes-page__grid">
    @foreach($notes as $note)
      @include('frontend.educator.partials.note-card', ['note' => $note, 'showActions' => true])
    @endforeach
  </div>

  @if($notes->hasPages())
    <nav class="edu-courses-pagination" aria-label="Notes pagination">
      <ul class="pagination mb-0">
        @if($notes->onFirstPage())
          <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
        @else
          <li class="page-item"><a class="page-link js-edu-notes-page" href="{{ $notes->previousPageUrl() }}" data-page="{{ $notes->currentPage() - 1 }}">&lsaquo;</a></li>
        @endif

        @foreach($notes->getUrlRange(max(1, $notes->currentPage() - 2), min($notes->lastPage(), $notes->currentPage() + 2)) as $page => $url)
          <li class="page-item {{ $page === $notes->currentPage() ? 'active' : '' }}">
            @if($page === $notes->currentPage())
              <span class="page-link">{{ $page }}</span>
            @else
              <a class="page-link js-edu-notes-page" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
            @endif
          </li>
        @endforeach

        @if($notes->hasMorePages())
          <li class="page-item"><a class="page-link js-edu-notes-page" href="{{ $notes->nextPageUrl() }}" data-page="{{ $notes->currentPage() + 1 }}">Next &rsaquo;</a></li>
        @else
          <li class="page-item disabled"><span class="page-link">Next &rsaquo;</span></li>
        @endif
      </ul>
    </nav>
  @endif
@else
  <div class="edu-courses-empty">
    <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
    <h3>No notes found</h3>
    <p>Try adjusting your search or filters to find notes from this educator.</p>
  </div>
@endif
