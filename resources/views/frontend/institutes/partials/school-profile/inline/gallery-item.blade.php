<div class="sch-manage-item" data-gallery-path="{{ $path }}">
  <div class="sch-manage-item__head">
    <div class="d-flex align-items-center gap-3">
      @if($type === 'video')
        <span class="sch-manage-item__thumb d-inline-flex align-items-center justify-content-center bg-dark text-white"><i class="fa-solid fa-video" aria-hidden="true"></i></span>
        <strong>Video</strong>
      @else
        <img src="{{ asset($path) }}" alt="" class="sch-manage-item__thumb">
        <strong>Photo</strong>
      @endif
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm js-inst-gallery-delete"
            data-url="{{ route($listingContext.'.inline.gallery.remove', $institute->slug) }}"
            data-del-key="path" data-del-value="{{ $path }}"
            aria-label="Delete gallery item">
      <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
    </button>
  </div>
</div>
