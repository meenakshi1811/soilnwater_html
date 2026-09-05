@php
  $isEdit = $material->exists;
  $contentsItems = old('contents', is_array($material->contents) ? $material->contents : []);
  if (! is_array($contentsItems) || count($contentsItems) === 0) {
      $contentsItems = [''];
  }
  $tagsText = old('tags', is_array($material->tags) ? implode(', ', $material->tags) : '');
  $types = ['notes','question_papers','sample_papers','worksheets','assignments','reference_books','study_guides','videos'];
  $categories = [
      'School',
      'Higher Secondary',
      'Undergraduate',
      'Postgraduate',
      'Competitive Exams',
      'Skill Development',
      'Professional Courses',
      'Language Learning',
      'Teacher Resources',
      'Other',
  ];
  $difficulties = ['Beginner', 'Intermediate', 'Advanced'];
  $selectedCategory = old('category', $material->category);
@endphp
@extends('backend.layouts.app')
@section('title', $isEdit ? 'Edit Study Material' : 'Upload Study Material')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
  .tag-input-wrap:focus-within { border-color: #86b7fe !important; box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .15); }
  #tagList { row-gap: .5rem; width: 100%; }
  .community-tag-pill {
    align-items: flex-start;
    background: #e8f5ee;
    border: 1px solid #badbcc;
    border-radius: 999px;
    color: #0f5132;
    display: inline-flex;
    font-size: .8125rem;
    font-weight: 600;
    gap: .35rem;
    line-height: 1.35;
    max-width: 100%;
    min-width: 0;
    padding: .3rem .6rem;
  }
  .community-tag-pill > span:first-child { min-width: 0; overflow-wrap: anywhere; word-break: break-word; }
  .community-tag-remove { background: transparent; border: 0; color: inherit; flex-shrink: 0; line-height: 1; margin-top: .1rem; padding: 0; }
  .content-item-row .form-control { min-width: 0; }
  .ck-editor__editable_inline { min-height: 180px; }
</style>
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
          <textarea name="description" id="materialDescription" class="form-control" rows="5">{{ old('description', $material->description) }}</textarea>
          <small class="text-muted">Briefly explain what learners will get from this material.</small>
        </div>

        <div class="col-md-4">
          <label class="form-label">Category</label>
          <select name="category" id="categorySelect" class="form-select">
            <option value="">Select category</option>
            @foreach($categories as $category)
              <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
            @endforeach
            @if($selectedCategory && ! in_array($selectedCategory, $categories, true))
              <option value="{{ $selectedCategory }}" selected>{{ $selectedCategory }}</option>
            @endif
          </select>
        </div>
        <div class="col-md-4"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="{{ old('subject', $material->subject) }}" placeholder="e.g. Physics"></div>
        <div class="col-md-4"><label class="form-label">Class / Course</label><input type="text" name="class_course" class="form-control" value="{{ old('class_course', $material->class_course) }}" placeholder="e.g. Class 12 / B.Sc"></div>
        <div class="col-md-4"><label class="form-label">Board / University</label><input type="text" name="board_university" class="form-control" value="{{ old('board_university', $material->board_university) }}"></div>
        <div class="col-md-4"><label class="form-label">Topic / Chapter</label><input type="text" name="topic_chapter" class="form-control" value="{{ old('topic_chapter', $material->topic_chapter) }}"></div>
        <div class="col-md-4"><label class="form-label">Exam / Test</label><input type="text" name="exam_test" class="form-control" value="{{ old('exam_test', $material->exam_test) }}"></div>
        <div class="col-md-3"><label class="form-label">Language</label><input type="text" name="language" class="form-control" value="{{ old('language', $material->language) }}" placeholder="e.g. English"></div>
        <div class="col-md-3">
          <label class="form-label">Difficulty</label>
          <select name="difficulty" class="form-select">
            <option value="">Select</option>
            @foreach($difficulties as $level)
              <option value="{{ $level }}" @selected(old('difficulty', $material->difficulty) === $level)>{{ $level }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3"><label class="form-label">Academic year</label><input type="text" name="academic_year" class="form-control" value="{{ old('academic_year', $material->academic_year) }}" placeholder="e.g. 2025-26"></div>
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
          <label class="form-label">Tags</label>
          <div class="tag-input-wrap border rounded p-2 bg-white">
            <div id="tagList" class="d-flex flex-wrap gap-2 mb-2"></div>
            <input type="text" id="tagInput" class="form-control border-0 p-0 shadow-none" placeholder="Type a tag and press Enter or comma" autocomplete="off">
          </div>
          <input type="hidden" name="tags" id="tagsHidden" value="{{ $tagsText }}">
          <small class="text-muted">Add up to 10 tags. Press Enter or comma after each tag.</small>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_free" value="1" id="isFree" @checked(old('is_free', $material->is_free ?? true))>
            <label class="form-check-label" for="isFree">Free download</label>
          </div>
        </div>

        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div>
              <label class="form-label mb-0">Table of contents</label>
              <small class="text-muted d-block">Add each chapter or section as a separate item (shown on the material page).</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addContentItem">
              <i class="fa-solid fa-plus me-1"></i> Add item
            </button>
          </div>
          <div id="contentsList">
            @foreach($contentsItems as $index => $item)
              <div class="input-group content-item-row mb-2">
                <span class="input-group-text content-item-num">{{ $index + 1 }}</span>
                <input type="text" name="contents[]" class="form-control" value="{{ $item }}" placeholder="e.g. Chapter 1 — Introduction">
                <button type="button" class="btn btn-outline-danger js-remove-content" title="Remove">&times;</button>
              </div>
            @endforeach
          </div>
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
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
(function ($) {
  if (!$ || !window.FormHelper) {
    return;
  }

  var isEdit = @json($isEdit);
  var defaultText = isEdit ? 'Update & submit for approval' : 'Submit for approval';
  var descriptionEditor = null;
  var MAX_TAGS = 10;
  var tags = (@json($tagsText) || '')
    .split(',')
    .map(function (tag) { return tag.trim(); })
    .filter(Boolean);

  var tagList = document.getElementById('tagList');
  var tagInput = document.getElementById('tagInput');
  var tagsHidden = document.getElementById('tagsHidden');

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
  }

  function syncTags() {
    if (tagsHidden) {
      tagsHidden.value = tags.join(', ');
    }
    if (tagInput) {
      tagInput.disabled = tags.length >= MAX_TAGS;
      tagInput.placeholder = tags.length >= MAX_TAGS
        ? 'Maximum of 10 tags reached'
        : 'Type a tag and press Enter or comma';
    }
    if (!tagList) return;

    tagList.innerHTML = '';
    tags.forEach(function (tag, index) {
      var pill = document.createElement('span');
      pill.className = 'community-tag-pill';
      pill.innerHTML = '<span>#' + escapeHtml(tag) + '</span><button type="button" class="community-tag-remove" aria-label="Remove tag">&times;</button>';
      pill.querySelector('button').addEventListener('click', function () {
        tags.splice(index, 1);
        syncTags();
      });
      tagList.appendChild(pill);
    });
  }

  function addTagsFromInput() {
    if (!tagInput || tags.length >= MAX_TAGS) {
      if (tagInput) tagInput.value = '';
      return;
    }

    tagInput.value.split(',').map(function (tag) { return tag.trim(); }).filter(Boolean).forEach(function (tag) {
      if (tags.length >= MAX_TAGS) return;
      if (!tags.map(function (item) { return item.toLowerCase(); }).includes(tag.toLowerCase())) {
        tags.push(tag);
      }
    });
    tagInput.value = '';
    syncTags();
  }

  if (tagInput) {
    tagInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        addTagsFromInput();
      }
    });
    tagInput.addEventListener('blur', addTagsFromInput);
    document.querySelector('.tag-input-wrap')?.addEventListener('click', function () {
      if (!tagInput.disabled) tagInput.focus();
    });
  }
  syncTags();

  function renumberContents() {
    $('#contentsList .content-item-row').each(function (index) {
      $(this).find('.content-item-num').text(index + 1);
    });
  }

  function addContentRow(value) {
    var row = $(
      '<div class="input-group content-item-row mb-2">' +
        '<span class="input-group-text content-item-num"></span>' +
        '<input type="text" name="contents[]" class="form-control" placeholder="e.g. Chapter 1 — Introduction">' +
        '<button type="button" class="btn btn-outline-danger js-remove-content" title="Remove">&times;</button>' +
      '</div>'
    );
    row.find('input').val(value || '');
    $('#contentsList').append(row);
    renumberContents();
    row.find('input').trigger('focus');
  }

  $('#addContentItem').on('click', function () {
    addContentRow('');
  });

  $(document).on('click', '.js-remove-content', function () {
    var $rows = $('#contentsList .content-item-row');
    if ($rows.length <= 1) {
      $rows.first().find('input').val('');
      return;
    }
    $(this).closest('.content-item-row').remove();
    renumberContents();
  });

  if (window.ClassicEditor) {
    ClassicEditor.create(document.querySelector('#materialDescription'), {
      toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', 'link', '|', 'undo', 'redo']
    }).then(function (editor) {
      descriptionEditor = editor;
    }).catch(function () {
      descriptionEditor = null;
    });
  }

  FormHelper.attachAjaxForm({
    formSelector: '#studyMaterialForm',
    buttonSelector: '#studyMaterialSubmitBtn',
    alertSelector: '#studyMaterialAlert',
    defaultText: defaultText,
    loadingText: isEdit ? 'Updating...' : 'Submitting...',
    validationMessage: 'Please fix the highlighted fields and try again.',
    fallbackErrorMessage: 'Unable to save study material. Please try again.',
    beforeSubmit: function () {
      addTagsFromInput();
      if (descriptionEditor) {
        descriptionEditor.updateSourceElement();
      }
    },
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
