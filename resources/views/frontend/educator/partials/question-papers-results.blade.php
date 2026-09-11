@if($questionPapers->count())
  <div class="edu-notes-page__grid">
    @foreach($questionPapers as $paper)
      @include('frontend.educator.partials.note-card', ['note' => $paper, 'showActions' => true, 'materialLabel' => 'Question Paper'])
    @endforeach
  </div>

  @if($questionPapers->hasPages())
    <nav class="edu-courses-pagination" aria-label="Question papers pagination">
      <ul class="pagination mb-0">
        @if($questionPapers->onFirstPage())
          <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
        @else
          <li class="page-item"><a class="page-link js-edu-papers-page" href="{{ $questionPapers->previousPageUrl() }}" data-page="{{ $questionPapers->currentPage() - 1 }}">&lsaquo;</a></li>
        @endif

        @foreach($questionPapers->getUrlRange(max(1, $questionPapers->currentPage() - 2), min($questionPapers->lastPage(), $questionPapers->currentPage() + 2)) as $page => $url)
          <li class="page-item {{ $page === $questionPapers->currentPage() ? 'active' : '' }}">
            @if($page === $questionPapers->currentPage())
              <span class="page-link">{{ $page }}</span>
            @else
              <a class="page-link js-edu-papers-page" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
            @endif
          </li>
        @endforeach

        @if($questionPapers->hasMorePages())
          <li class="page-item"><a class="page-link js-edu-papers-page" href="{{ $questionPapers->nextPageUrl() }}" data-page="{{ $questionPapers->currentPage() + 1 }}">Next &rsaquo;</a></li>
        @else
          <li class="page-item disabled"><span class="page-link">Next &rsaquo;</span></li>
        @endif
      </ul>
    </nav>
  @endif
@else
  <div class="edu-courses-empty">
    <i class="fa-solid fa-file-circle-question" aria-hidden="true"></i>
    <h3>No question papers found</h3>
    <p>Try adjusting your search or filters to find question papers from this educator.</p>
  </div>
@endif
