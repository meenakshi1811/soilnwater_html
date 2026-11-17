@php
  use App\Support\EducatorSubjects;
  $index = $index ?? 0;
  $subject = $subject ?? EducatorSubjects::emptyRow();
  $normalizedSubject = EducatorSubjects::normalizeList([$subject])[0] ?? EducatorSubjects::emptyRow();
  $subjectClasses = $normalizedSubject['classes'] ?? [];
  $subjectBoards = $normalizedSubject['boards'] ?? [];
@endphp
<div class="edu-subject-form-card js-repeat-row">
  <div class="edu-subject-form-card__head">
    <label class="form-label mb-0">Subject name</label>
    <button type="button" class="btn btn-outline-danger btn-sm edu-btn-remove js-remove-row" title="Remove subject">&times;</button>
  </div>
  <input
    type="text"
    name="subjects[{{ $index }}][name]"
    class="form-control mb-3"
    placeholder="e.g. Physics"
    value="{{ $subject['name'] ?? '' }}"
  >
  <div class="edu-subject-form-card__grid">
    <div>
      <label class="form-label">Classes</label>
      <textarea
        class="form-control"
        name="subjects[{{ $index }}][classes_lines]"
        rows="3"
        placeholder="One class per line&#10;Class 9&#10;Class 10"
      >{{ EducatorSubjects::toLines($subjectClasses) }}</textarea>
      <small class="text-muted">Multiple classes allowed — one per line.</small>
    </div>
    <div>
      <label class="form-label">Boards</label>
      <textarea
        class="form-control"
        name="subjects[{{ $index }}][boards_lines]"
        rows="3"
        placeholder="One board per line&#10;CBSE&#10;GSEB"
      >{{ EducatorSubjects::toLines($subjectBoards) }}</textarea>
      <small class="text-muted">Multiple boards allowed — one per line.</small>
    </div>
  </div>
  @php $subjectImage = $normalizedSubject['image'] ?? ''; @endphp
  <div class="mt-3 edu-subject-image-field">
    <label class="form-label">Subject image <span class="text-muted fw-normal">(optional)</span></label>
    <div class="edu-subject-image-field__row">
      @if($subjectImage !== '')
        <img src="{{ asset($subjectImage) }}" alt="" class="edu-subject-image-field__preview">
      @endif
      <div class="flex-grow-1">
        <input type="hidden" name="subjects[{{ $index }}][image]" value="{{ $subjectImage }}">
        <input type="file" name="subjects[{{ $index }}][image_file]" class="form-control" accept="image/jpeg,image/png,image/webp">
        <small class="text-muted">Shown on this subject's card on your public profile. JPG, PNG or WebP up to 2 MB.</small>
        @if($subjectImage !== '')
          <div class="form-check mt-1">
            <input class="form-check-input" type="checkbox" name="subjects[{{ $index }}][remove_image]" value="1" id="subjectRemoveImage{{ $index }}">
            <label class="form-check-label" for="subjectRemoveImage{{ $index }}">Remove this image</label>
          </div>
        @endif
      </div>
    </div>
  </div>
  <div class="mt-3">
    <label class="form-label">Years of experience (this subject)</label>
    <input
      type="text"
      name="subjects[{{ $index }}][years_experience]"
      class="form-control"
      placeholder="e.g. 8"
      value="{{ $subject['years_experience'] ?? '' }}"
    >
  </div>
</div>
