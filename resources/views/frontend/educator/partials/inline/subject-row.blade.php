<div class="edu-inline-row js-edu-row">
  <div class="row g-2">
    <div class="col-md-4">
      <label class="form-label small mb-1">Subject</label>
      <input type="text" name="subjects[{{ $i }}][name]" class="form-control form-control-sm" maxlength="120" value="{{ $subject['name'] ?? '' }}" placeholder="e.g. Physics">
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1">Classes</label>
      <textarea name="subjects[{{ $i }}][classes_lines]" class="form-control form-control-sm" rows="2" placeholder="Class 11&#10;Class 12">{{ \App\Support\EducatorSubjects::toLines($subject['classes'] ?? []) }}</textarea>
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1">Boards</label>
      <textarea name="subjects[{{ $i }}][boards_lines]" class="form-control form-control-sm" rows="2" placeholder="CBSE&#10;ICSE">{{ \App\Support\EducatorSubjects::toLines($subject['boards'] ?? []) }}</textarea>
    </div>
    <div class="col-md-2">
      <label class="form-label small mb-1">Years</label>
      <div class="d-flex gap-1">
        <input type="text" name="subjects[{{ $i }}][years_experience]" class="form-control form-control-sm" maxlength="20" value="{{ $subject['years_experience'] ?? '' }}" placeholder="5">
        <button type="button" class="btn btn-sm btn-outline-danger js-edu-row-remove" title="Remove this subject" aria-label="Remove this subject"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
      </div>
    </div>
  </div>
</div>
