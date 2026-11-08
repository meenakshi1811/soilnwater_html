@php
  $isCurrent = filter_var($exp['is_current'] ?? false, FILTER_VALIDATE_BOOLEAN);
@endphp
<div class="edu-inline-row js-edu-row">
  <div class="row g-2">
    <div class="col-sm-6">
      <input type="text" name="experiences[{{ $i }}][title]" class="form-control form-control-sm" maxlength="255" value="{{ $exp['title'] ?? '' }}" placeholder="Role, e.g. PGT Physics" aria-label="Role">
    </div>
    <div class="col-sm-6">
      <input type="text" name="experiences[{{ $i }}][organization]" class="form-control form-control-sm" maxlength="255" value="{{ $exp['organization'] ?? '' }}" placeholder="School / institute" aria-label="School or institute">
    </div>
    <div class="col-4 col-sm-3">
      <input type="text" inputmode="numeric" name="experiences[{{ $i }}][start_year]" class="form-control form-control-sm" maxlength="4" value="{{ $exp['start_year'] ?? '' }}" placeholder="From" aria-label="From year">
    </div>
    <div class="col-4 col-sm-3">
      <input type="text" inputmode="numeric" name="experiences[{{ $i }}][end_year]" class="form-control form-control-sm" maxlength="4" value="{{ $isCurrent ? '' : ($exp['end_year'] ?? '') }}" placeholder="To" aria-label="To year">
    </div>
    <div class="col-4 col-sm-6 d-flex align-items-center justify-content-between gap-2">
      <label class="form-check-label small d-flex align-items-center gap-1">
        <input type="hidden" name="experiences[{{ $i }}][is_current]" value="0">
        <input type="checkbox" class="form-check-input mt-0" name="experiences[{{ $i }}][is_current]" value="1" @checked($isCurrent)> Current
      </label>
      <button type="button" class="btn btn-sm btn-outline-danger js-edu-row-remove" title="Remove" aria-label="Remove this experience"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
    </div>
    <div class="col-12">
      <textarea name="experiences[{{ $i }}][description]" class="form-control form-control-sm" rows="2" maxlength="1000" placeholder="What you taught or achieved (optional)" aria-label="Description">{{ $exp['description'] ?? '' }}</textarea>
    </div>
  </div>
</div>
