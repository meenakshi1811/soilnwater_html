<div class="sch-manage-item" data-item-id="{{ $class->id }}">
  <div class="sch-manage-item__head">
    <div>
      <strong>{{ $class->displayLabel() }}</strong>
      <div class="sch-manage-item__meta">
        @if($class->class_teacher)<span>Teacher: {{ $class->class_teacher }}</span>@endif
        @if($class->strength)<span>{{ $class->strength }} students</span>@endif
        @if($class->room)<span>Room {{ $class->room }}</span>@endif
      </div>
      @if($class->description)
        <p class="mb-0 mt-1 text-secondary small">{{ $class->description }}</p>
      @endif
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm js-inst-class-delete" data-url="{{ $portalRoute('classes.destroy', $class) }}" aria-label="Delete class">
      <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
    </button>
  </div>
</div>
