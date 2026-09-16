<div class="sch-manage-item" data-item-id="{{ $achievement->id }}">
  <div class="sch-manage-item__head">
    <div class="d-flex align-items-start gap-3">
      @if($achievement->imageUrl())
        <img src="{{ $achievement->imageUrl() }}" alt="" class="sch-manage-item__thumb">
      @endif
      <div>
        <strong>{{ $achievement->title }}</strong>
        <div class="sch-manage-item__meta">
          @if($achievement->category)<span>{{ $achievement->category }}</span>@endif
          @if($achievement->year)<span>{{ $achievement->year }}</span>@endif
        </div>
        @if($achievement->description)
          <p class="mb-0 mt-1 text-secondary small">{{ \Illuminate\Support\Str::limit($achievement->description, 160) }}</p>
        @endif
      </div>
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm js-inst-achievement-delete" data-url="{{ $portalRoute('achievements.destroy', $achievement) }}" aria-label="Delete achievement">
      <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
    </button>
  </div>
</div>
