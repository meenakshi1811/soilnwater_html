<div class="sch-manage-item js-diary-holiday-item" data-item-id="{{ $holiday->id }}"
     data-name="{{ e($holiday->name) }}"
     data-holiday-type="{{ $holiday->holiday_type }}"
     data-start-date="{{ $holiday->start_date?->format('Y-m-d') }}"
     data-end-date="{{ $holiday->end_date?->format('Y-m-d') }}"
     data-description="{{ e($holiday->description) }}"
     data-academic-year="{{ $holiday->academic_year }}"
     data-is-recurring="{{ $holiday->is_recurring ? '1' : '0' }}"
     data-is-active="{{ $holiday->is_active ? '1' : '0' }}"
     data-update-url="{{ $portalRoute('diary.holidays.update', $holiday) }}">
  <div class="sch-manage-item__head">
    <div>
      <strong>{{ $holiday->name }}</strong>
      <div class="sch-manage-item__meta">
        <span>{{ $holiday->holidayTypeLabel() }}</span>
        <span>{{ $holiday->start_date?->format('d M Y') }}@if($holiday->end_date && !$holiday->start_date?->isSameDay($holiday->end_date)) – {{ $holiday->end_date->format('d M Y') }}@endif</span>
        <span>{{ $holiday->durationDays() }} day{{ $holiday->durationDays() === 1 ? '' : 's' }}</span>
        <span>AY {{ $holiday->academic_year }}</span>
        @if($holiday->is_recurring)<span class="badge text-bg-info">Recurring</span>@endif
        @if($holiday->is_active)
          <span class="badge text-bg-success">Active</span>
        @else
          <span class="badge text-bg-secondary">Inactive</span>
        @endif
      </div>
      @if($holiday->description)
        <p class="mb-0 mt-2 text-secondary small">{{ $holiday->description }}</p>
      @endif
    </div>
    <div class="d-flex gap-1 flex-shrink-0">
      <button type="button" class="btn btn-outline-primary btn-sm js-diary-holiday-edit" aria-label="Edit holiday">
        <i class="fa-solid fa-pen" aria-hidden="true"></i>
      </button>
      <button type="button" class="btn btn-outline-danger btn-sm js-diary-holiday-delete" data-url="{{ $portalRoute('diary.holidays.destroy', $holiday) }}" aria-label="Delete holiday">
        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
      </button>
    </div>
  </div>
</div>
