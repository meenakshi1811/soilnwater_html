@php
    $audienceLabels = $audienceLabels ?? \App\Support\InstituteDiaryConfig::applicableAudiences(auth()->user());
@endphp
<div class="sch-manage-item js-diary-leave-item" data-item-id="{{ $rule->id }}"
     data-leave-type="{{ $rule->leave_type }}"
     data-allowed-days="{{ $rule->allowed_days }}"
     data-applicable-to="{{ json_encode($rule->applicable_to ?? []) }}"
     data-is-paid="{{ $rule->is_paid ? '1' : '0' }}"
     data-description="{{ e($rule->description) }}"
     data-is-active="{{ $rule->is_active ? '1' : '0' }}"
     data-update-url="{{ $portalRoute('diary.leave-rules.update', $rule) }}">
  <div class="sch-manage-item__head">
    <div>
      <strong>{{ $rule->leaveTypeLabel() }}</strong>
      <div class="sch-manage-item__meta">
        <span>{{ $rule->allowed_days }} day{{ $rule->allowed_days === 1 ? '' : 's' }} allowed</span>
        <span>{{ $rule->is_paid ? 'Paid' : 'Unpaid' }}</span>
        <span>{{ $rule->applicableToLabels($audienceLabels) }}</span>
        @if($rule->is_active)
          <span class="badge text-bg-success">Active</span>
        @else
          <span class="badge text-bg-secondary">Inactive</span>
        @endif
      </div>
      @if($rule->description)
        <p class="mb-0 mt-2 text-secondary small">{{ $rule->description }}</p>
      @endif
    </div>
    <div class="d-flex gap-1 flex-shrink-0">
      <button type="button" class="btn btn-outline-primary btn-sm js-diary-leave-edit" aria-label="Edit leave rule">
        <i class="fa-solid fa-pen" aria-hidden="true"></i>
      </button>
      <button type="button" class="btn btn-outline-danger btn-sm js-diary-leave-delete" data-url="{{ $portalRoute('diary.leave-rules.destroy', $rule) }}" aria-label="Delete leave rule">
        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
      </button>
    </div>
  </div>
</div>
