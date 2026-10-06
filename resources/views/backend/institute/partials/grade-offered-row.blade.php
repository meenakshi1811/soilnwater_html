@php
    $gradeRow = $gradeRow ?? ['class' => '', 'sections' => '', 'students_per_section' => ''];
@endphp
<div class="inst-grade-row js-repeat-row js-grade-row mb-2" data-section="grades">
    <div class="row g-2 align-items-start">
        <div class="col-md-5">
            <label class="form-label d-md-none small text-muted mb-1">Class</label>
            <input
                type="text"
                class="form-control js-section-field js-grade-class"
                name="grades_offered[{{ $index }}][class]"
                value="{{ $gradeRow['class'] ?? '' }}"
                placeholder="e.g. Class 1"
                data-section="grades"
            >
        </div>
        <div class="col-md-3">
            <label class="form-label d-md-none small text-muted mb-1">Number of sections</label>
            <input
                type="number"
                min="0"
                max="999"
                class="form-control js-section-field js-grade-sections"
                name="grades_offered[{{ $index }}][sections]"
                value="{{ $gradeRow['sections'] ?? '' }}"
                placeholder="e.g. 3"
                inputmode="numeric"
                data-section="grades"
            >
        </div>
        <div class="col-md-3">
            <label class="form-label d-md-none small text-muted mb-1">Students per section</label>
            <input
                type="number"
                min="0"
                max="9999"
                class="form-control js-section-field js-grade-students"
                name="grades_offered[{{ $index }}][students_per_section]"
                value="{{ $gradeRow['students_per_section'] ?? '' }}"
                placeholder="e.g. 40"
                inputmode="numeric"
                data-section="grades"
            >
        </div>
        <div class="col-md-1 d-flex justify-content-md-center">
            <button type="button" class="btn btn-outline-danger js-remove-row mt-md-4" aria-label="Remove grade">&times;</button>
        </div>
    </div>
</div>
