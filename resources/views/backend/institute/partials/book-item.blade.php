<div class="sch-manage-item" data-item-id="{{ $book->id }}">
  <div class="sch-manage-item__head">
    <div class="d-flex align-items-start gap-3">
      @if($book->coverUrl())
        <img src="{{ $book->coverUrl() }}" alt="" class="sch-manage-item__thumb sch-manage-item__thumb--book">
      @endif
      <div>
        <strong>{{ $book->title }}</strong>
        <div class="sch-manage-item__meta">
          <span>By {{ $book->author }}</span>
          @if($book->class_name)<span>{{ $book->class_name }}</span>@endif
          @if($book->subject)<span>{{ $book->subject }}</span>@endif
        </div>
        @if($book->publisher || $book->isbn)
          <p class="mb-0 mt-1 text-secondary small">
            @if($book->publisher){{ $book->publisher }}@endif
            @if($book->isbn) · ISBN {{ $book->isbn }}@endif
          </p>
        @endif
      </div>
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm js-inst-book-delete" data-url="{{ $portalRoute('books.destroy', $book) }}" aria-label="Delete book">
      <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
    </button>
  </div>
</div>
