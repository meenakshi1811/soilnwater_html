@php
  $listingContext = $listingContext ?? 'schools';
  $portalPrefix = \App\Support\SchoolInstituteHelper::portalForListing($listingContext);
  $owner = $institute->user;
  $leaveTypes = \App\Support\InstituteDiaryConfig::leaveTypes();
  $audiences = \App\Support\InstituteDiaryConfig::applicableAudiences($owner);
  $rules = $institute->leaveRules()->orderBy('sort_order')->orderBy('id')->get();
@endphp
<section>
  <form id="instLeaveForm" class="sch-portal-form mb-4" novalidate
        data-store-url="{{ route($portalPrefix.'.diary.leave-rules.store') }}">
    @csrf
    <div class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Leave type *</label>
        <select name="leave_type" class="form-select">
          @foreach($leaveTypes as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Days allowed *</label>
        <input type="number" name="allowed_days" class="form-control" min="0" max="366" value="0">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" name="is_paid" value="1" id="instLeavePaid" checked>
          <label class="form-check-label" for="instLeavePaid">Paid leave</label>
        </div>
      </div>
      <div class="col-12">
        <label class="form-label d-block">Applicable to</label>
        @foreach($audiences as $key => $label)
          <div class="form-check form-check-inline">
            <input type="checkbox" class="form-check-input" name="applicable_to[]" value="{{ $key }}" id="instLeaveAud{{ $key }}">
            <label class="form-check-label" for="instLeaveAud{{ $key }}">{{ $label }}</label>
          </div>
        @endforeach
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2" maxlength="5000"></textarea>
      </div>
      <input type="hidden" name="is_active" value="1">
      <div class="col-12">
        <button type="submit" class="btn btn-primary js-inst-submit-btn">
          <span class="btn-text"><i class="fa-solid fa-plus me-1"></i> Add leave rule</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>

  <div class="sch-manage-list" data-empty-text="No leave rules added yet.">
    @forelse($rules as $rule)
      @include('backend.institute.partials.diary-leave-rule-item', ['rule' => $rule, 'audienceLabels' => $audiences])
    @empty
      <p class="sch-manage-empty mb-0">No leave rules added yet.</p>
    @endforelse
  </div>
</section>
