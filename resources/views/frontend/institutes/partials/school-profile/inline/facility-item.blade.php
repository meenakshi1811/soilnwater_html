<div class="sch-manage-item" data-facility="{{ $name }}">
  <div class="sch-manage-item__head">
    <div class="d-flex align-items-center gap-3">
      @if($image)
        <img src="{{ asset($image) }}" alt="" class="sch-manage-item__thumb">
      @endif
      <strong>{{ $name }}</strong>
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm js-inst-facility-delete"
            data-url="{{ route($listingContext.'.inline.facilities.remove', $institute->slug) }}"
            data-del-key="name" data-del-value="{{ $name }}"
            aria-label="Delete facility">
      <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
    </button>
  </div>
</div>
