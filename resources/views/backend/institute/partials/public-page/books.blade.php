@php
  $inlineEditor = $inlineEditor ?? false;
  $booksFormOpen = $institute->books->isNotEmpty();
@endphp
<section id="inst-section-books" class="chart-card sch-portal-section inst-profile-section mb-4">
  <header class="inst-profile-section__head sch-portal-section__head">
    <span class="sch-portal-section__icon sch-portal-section__icon--purple"><i class="fa-solid fa-book"></i></span>
    <div class="flex-grow-1">
      <h3 class="mb-1">Books &amp; authors</h3>
      <p class="text-secondary mb-0">List prescribed textbooks and reading materials by class.</p>
    </div>
    @unless($inlineEditor)
    <div class="form-check form-switch inst-profile-section__switch mb-0">
      <input class="form-check-input js-public-page-section-toggle" type="checkbox" role="switch" id="books_section_enabled" data-section="books" @checked($booksFormOpen)>
      <label class="form-check-label" for="books_section_enabled">Show form</label>
    </div>
    @endunless
  </header>

  <div class="inst-public-page-form-body {{ ($inlineEditor || $booksFormOpen) ? '' : 'd-none' }}" data-section-body="books">
  <form id="instBookForm" class="sch-portal-form mb-4" enctype="multipart/form-data" novalidate data-store-url="{{ $portalRoute('books.store') }}">
    @csrf
    <div id="instBookFormAlert" class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Book title *</label>
        <input type="text" name="title" class="form-control js-section-field" data-section="books">
      </div>
      <div class="col-md-3">
        <label class="form-label">Author *</label>
        <input type="text" name="author" class="form-control js-section-field" data-section="books">
      </div>
      <div class="col-md-2">
        <label class="form-label">Class *</label>
        <input type="text" name="class_name" class="form-control js-section-field" data-section="books" placeholder="Class 8">
      </div>
      <div class="col-md-3">
        <label class="form-label">Subject *</label>
        <input type="text" name="subject" class="form-control js-section-field" data-section="books" placeholder="Mathematics">
      </div>
      <div class="col-md-3">
        <label class="form-label">Publisher *</label>
        <input type="text" name="publisher" class="form-control js-section-field" data-section="books">
      </div>
      <div class="col-md-3">
        <label class="form-label">ISBN *</label>
        <input type="text" name="isbn" class="form-control js-section-field" data-section="books">
      </div>
      <div class="col-md-3">
        <label class="form-label">Cover image *</label>
        <input type="file" name="cover_image" class="form-control js-section-field" data-section="books" accept="image/jpeg,image/png,image/webp">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
          <span class="btn-text">Add book</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>
  </div>

  <div class="sch-manage-list" id="instBookList" data-empty-text="No books added yet.">
    @forelse($institute->books as $book)
      @include('backend.institute.partials.book-item', ['book' => $book])
    @empty
      <p class="sch-manage-empty mb-0" id="instBookEmpty">No books added yet.</p>
    @endforelse
  </div>
</section>
