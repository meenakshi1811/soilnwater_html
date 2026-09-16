<div class="sch-manage-item" data-item-id="{{ $performer->id }}">
  <div class="sch-manage-item__head">
    <div class="d-flex align-items-start gap-3">
      @if($performer->photoUrl())
        <img src="{{ $performer->photoUrl() }}" alt="" class="sch-manage-item__thumb sch-manage-item__thumb--round">
      @endif
      <div>
        <strong>{{ $performer->student_name }}</strong>
        <div class="sch-manage-item__meta">
          @if($performer->class_name)<span>{{ $performer->class_name }}</span>@endif
          @if($performer->rank)<span>Rank #{{ $performer->rank }}</span>@endif
          @if($performer->academic_year)<span>{{ $performer->academic_year }}</span>@endif
        </div>
        <p class="mb-0 mt-1">{{ $performer->achievement_title }}@if($performer->score) · {{ $performer->score }}@endif</p>
      </div>
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm js-inst-performer-delete" data-url="{{ route(($portalPrefix ?? auth()->user()?->portalRoutePrefix() ?? 'school').'.performers.destroy', $performer) }}" aria-label="Delete performer">
      <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
    </button>
  </div>
</div>
