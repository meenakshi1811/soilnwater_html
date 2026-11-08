<div class="edu-inline-row js-edu-row">
  <div class="row g-2">
    <div class="col-12">
      <input type="text" name="qualifications[{{ $i }}][degree]" class="form-control form-control-sm" maxlength="255" value="{{ $qual['degree'] ?? '' }}" placeholder="Degree, e.g. M.Sc. Physics" aria-label="Degree">
    </div>
    <div class="col-7">
      <input type="text" name="qualifications[{{ $i }}][institution]" class="form-control form-control-sm" maxlength="255" value="{{ $qual['institution'] ?? '' }}" placeholder="University / college" aria-label="University or college">
    </div>
    <div class="col-5 d-flex gap-1">
      <input type="text" name="qualifications[{{ $i }}][year]" class="form-control form-control-sm" maxlength="20" value="{{ $qual['year'] ?? '' }}" placeholder="Year" aria-label="Year">
      <button type="button" class="btn btn-sm btn-outline-danger js-edu-row-remove" title="Remove" aria-label="Remove this qualification"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
    </div>
  </div>
</div>
