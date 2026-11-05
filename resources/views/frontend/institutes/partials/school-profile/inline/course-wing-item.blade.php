<div class="sch-manage-item" data-wing-title="{{ $wing['title'] }}">
  <div class="sch-manage-item__head">
    <div class="d-flex align-items-center gap-3">
      @if(!empty($wing['image']))
        <img src="{{ asset($wing['image']) }}" alt="" class="sch-manage-item__thumb">
      @else
        <span class="sch-manage-item__thumb d-inline-flex align-items-center justify-content-center bg-light">
          <i class="fa-solid {{ $wing['icon'] ?: 'fa-book-open' }}" aria-hidden="true"></i>
        </span>
      @endif
      <div>
        <strong>{{ $wing['title'] }}</strong>
        @if(!empty($wing['grades']))
          <div class="sch-manage-item__meta"><span>{{ $wing['grades'] }}</span></div>
        @endif
      </div>
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm js-inst-course-delete"
            data-url="{{ route($listingContext.'.inline.courses.remove', $institute->slug) }}"
            data-del-key="title" data-del-value="{{ $wing['title'] }}"
            aria-label="Delete course">
      <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
    </button>
  </div>
</div>
