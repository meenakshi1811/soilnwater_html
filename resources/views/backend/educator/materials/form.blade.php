@php
  $isEdit = $material->exists;
  $contentsText = is_array($material->contents) ? implode("\n", $material->contents) : '';
  $tagsText = is_array($material->tags) ? implode(', ', $material->tags) : '';
  $types = ['notes','question_papers','sample_papers','worksheets','assignments','reference_books','study_guides','videos'];
@endphp
@extends('backend.layouts.app')
@section('title', $isEdit ? 'Edit Study Material' : 'Upload Study Material')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
  <div class="mb-4">
    <p class="ems-kicker mb-1">Educator Portal</p>
    <h2 class="admin-title mb-0">{{ $isEdit ? 'Edit study material' : 'Upload study material' }}</h2>
    <p class="text-secondary mb-0 mt-1">
      {{ $isEdit
        ? 'Saving changes will resubmit this material for admin approval.'
        : 'Your upload will be sent to admin for approval before it appears publicly.' }}
    </p>
  </div>

  <div class="chart-card">
    <div id="studyMaterialAlert" class="alert d-none" role="alert"></div>

    <form
      id="studyMaterialForm"
      method="POST"
      action="{{ $isEdit ? route('educator.materials.update', $material) : route('educator.materials.store') }}"
      enctype="multipart/form-data"
      novalidate
    >
      @csrf
      @if($isEdit)
        @method('PUT')
      @endif

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Title *</label>
          <input type="text" name="title" class="form-control" value="{{ old('title', $material->title) }}" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Material type *</label>
          <select name="material_type" class="form-select" required>
            @foreach($types as $type)
              <option value="{{ $type }}" @selected(old('material_type', $material->material_type ?: 'notes') === $type)>{{ ucwords(str_replace('_',' ',$type)) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4">{{ old('description', $material->description) }}</textarea>
        </div>
        <div class="col-md-4"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="{{ old('category', $material->category) }}"></div>
        <div class="col-md-4"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="{{ old('subject', $material->subject) }}"></div>
        <div class="col-md-4"><label class="form-label">Class / Course</label><input type="text" name="class_course" class="form-control" value="{{ old('class_course', $material->class_course) }}"></div>
        <div class="col-md-4"><label class="form-label">Board / University</label><input type="text" name="board_university" class="form-control" value="{{ old('board_university', $material->board_university) }}"></div>
        <div class="col-md-4"><label class="form-label">Topic / Chapter</label><input type="text" name="topic_chapter" class="form-control" value="{{ old('topic_chapter', $material->topic_chapter) }}"></div>
        <div class="col-md-4"><label class="form-label">Exam / Test</label><input type="text" name="exam_test" class="form-control" value="{{ old('exam_test', $material->exam_test) }}"></div>
        <div class="col-md-3"><label class="form-label">Language</label><input type="text" name="language" class="form-control" value="{{ old('language', $material->language) }}"></div>
        <div class="col-md-3"><label class="form-label">Difficulty</label><input type="text" name="difficulty" class="form-control" value="{{ old('difficulty', $material->difficulty) }}"></div>
        <div class="col-md-3"><label class="form-label">Academic year</label><input type="text" name="academic_year" class="form-control" value="{{ old('academic_year', $material->academic_year) }}"></div>
        <div class="col-md-3"><label class="form-label">Pages</label><input type="number" name="pages" class="form-control" value="{{ old('pages', $material->pages) }}" min="1"></div>
        <div class="col-md-6">
          <label class="form-label">File {{ $isEdit ? '' : '*' }}</label>
          <input type="file" name="file" class="form-control" {{ $isEdit ? '' : 'required' }}>
          @if($material->file_name)<small class="text-muted d-block mt-1">Current: {{ $material->file_name }}</small>@endif
        </div>
        <div class="col-md-6">
          <label class="form-label">Thumbnail</label>
          <input type="file" name="thumbnail" class="form-control" accept="image/*">
          @if($material->thumbnailUrl())
            <div class="mt-2"><img src="{{ $material->thumbnailUrl() }}" alt="" style="max-height:80px" class="rounded"></div>
          @endif
        </div>
        <div class="col-md-8">
          <label class="form-label">Tags (comma separated)</label>
          <input type="text" name="tags" class="form-control" value="{{ old('tags', $tagsText) }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_free" value="1" id="isFree" @checked(old('is_free', $material->is_free ?? true))>
            <label class="form-check-label" for="isFree">Free download</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Contents (one item per line)</label>
          <textarea name="contents" class="form-control" rows="5" placeholder="Chapter 1&#10;Chapter 2">{{ old('contents', $contentsText) }}</textarea>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button id="studyMaterialSubmitBtn" type="submit" class="btn btn-primary ems-btn-primary">
          <span class="btn-text">{{ $isEdit ? 'Update & submit for approval' : 'Submit for approval' }}</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
        <a href="{{ route('educator.materials.index') }}" class="btn btn-light">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
(function ($) {
  if (!$ || !window.FormHelper) {
    return;
  }

  var isEdit = @json($isEdit);
  var defaultText = isEdit ? 'Update & submit for approval' : 'Submit for approval';

  FormHelper.attachAjaxForm({
    formSelector: '#studyMaterialForm',
    buttonSelector: '#studyMaterialSubmitBtn',
    alertSelector: '#studyMaterialAlert',
    defaultText: defaultText,
    loadingText: isEdit ? 'Updating...' : 'Submitting...',
    validationMessage: 'Please fix the highlighted fields and try again.',
    fallbackErrorMessage: 'Unable to save study material. Please try again.',
    rules: {
      title: { required: true, maxlength: 255 },
      material_type: { required: true },
      file: isEdit ? {} : { required: true }
    },
    messages: {
      title: { required: 'Please enter a title.' },
      material_type: { required: 'Please select a material type.' },
      file: { required: 'Please upload a file.' }
    },
    onSuccess: function (response) {
      var message = response.message || (isEdit
        ? 'Study material updated and sent for admin approval.'
        : 'Study material submitted for admin approval.');

      FormHelper.showAlert($('#studyMaterialAlert'), 'success', message);
      if (window.toastr) {
        toastr.success(message);
      }

      setTimeout(function () {
        window.location.href = response.redirect || @json(route('educator.materials.index'));
      }, 900);
    }
  });
})(window.jQuery);
</script>
@endpush
