@php
  use App\Support\StudyMaterialUploadConfig;

  $isEdit = $material->exists;
  $uploadType = $uploadType ?? old('material_type', $material->material_type ?: 'notes');
  $typeConfig = StudyMaterialUploadConfig::type($uploadType);
  $allTypes = StudyMaterialUploadConfig::types();
  $meta = old('meta', is_array($material->meta) ? $material->meta : []);
  $selectedRole = old('meta.uploader_role', data_get($meta, 'uploader_role', 'teacher'));
  $selectedOptions = collect(old('meta.options', data_get($meta, 'options', [])))->map(fn ($v) => (string) $v)->all();
  $tagsText = old('tags', is_array($material->tags) ? implode(', ', $material->tags) : '');
  $classes = ['Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12', 'Graduate', 'Postgraduate'];
  $subjects = ['Mathematics', 'Physics', 'Chemistry', 'Biology', 'English', 'Hindi', 'Science', 'Social Science', 'Computer Science'];
  $boards = ['CBSE', 'ICSE', 'State Board', 'NCERT', 'IB', 'IGCSE'];
  $languages = ['English', 'Hindi', 'Gujarati', 'Marathi', 'Tamil', 'Telugu', 'Bengali', 'Kannada'];
  $examTypes = ['Board Exam', 'School Exam', 'Competitive Exam', 'Entrance Exam', 'Unit Test', 'Mid Term', 'Final Exam'];
  $terms = ['Annual', 'Half Yearly', 'Term 1', 'Term 2', 'Semester 1', 'Semester 2'];
  $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
@endphp
@extends('backend.layouts.app')
@section('title', $isEdit ? 'Edit '.$typeConfig['title'] : $typeConfig['title'])

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/educator-material-upload.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page sm-upload-page" data-active-type="{{ $uploadType }}">
  <div class="mb-3">
    <p class="ems-kicker mb-1">Educator Portal</p>
    <a href="{{ route('educator.materials.index') }}" class="text-decoration-none small"><i class="fa-solid fa-arrow-left me-1"></i> Back to my materials</a>
  </div>

  <div class="sm-upload-layout">
    <div class="sm-upload-main">
      <header class="sm-upload-header">
        <div class="sm-upload-header__title-wrap">
          <span class="sm-upload-header__icon"><i class="fa-solid {{ $typeConfig['icon'] }}" aria-hidden="true"></i></span>
          <div>
            <h1 class="sm-upload-header__title">{{ $isEdit ? 'Edit: '.$typeConfig['title'] : $typeConfig['title'] }}</h1>
            <p class="sm-upload-header__subtitle">{{ $typeConfig['subtitle'] }}</p>
          </div>
        </div>
        <div class="sm-upload-quote">{{ $typeConfig['quote'] }}</div>
      </header>

      <div id="studyMaterialAlert" class="alert d-none" role="alert"></div>

      <form
        id="studyMaterialForm"
        method="POST"
        action="{{ $isEdit ? route('educator.materials.update', $material) : route('educator.materials.store') }}"
        enctype="multipart/form-data"
        novalidate
      >
        @csrf
        @if($isEdit) @method('PUT') @endif
        <input type="hidden" name="material_type" value="{{ $uploadType }}">

        @if(! $isEdit)
          <section class="sm-upload-section">
            <div class="sm-upload-section__head">
              <span class="sm-upload-section__num">0</span>
              <h2 class="sm-upload-section__title">Choose material type</h2>
            </div>
            <div class="sm-upload-type-grid">
              @foreach($allTypes as $key => $config)
                <a href="{{ route('educator.materials.create', ['type' => $key]) }}" class="sm-upload-type-card {{ $uploadType === $key ? 'is-active' : '' }}" data-type="{{ $key }}">
                  <i class="fa-solid {{ $config['icon'] }}" aria-hidden="true"></i>
                  <span>{{ str_replace('Upload ', '', $config['title']) }}</span>
                </a>
              @endforeach
            </div>
          </section>
        @endif

        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">1</span>
            <h2 class="sm-upload-section__title">I am uploading as <span class="sm-upload-section__req">*</span></h2>
          </div>
          <div class="sm-upload-role-grid">
            @foreach(StudyMaterialUploadConfig::UPLOADER_ROLES as $roleKey => $role)
              <label class="sm-upload-role-card {{ $selectedRole === $roleKey ? 'is-active' : '' }}">
                <input type="radio" name="meta[uploader_role]" value="{{ $roleKey }}" @checked($selectedRole === $roleKey) required>
                <i class="fa-solid {{ $role['icon'] }}" aria-hidden="true"></i>
                <span>{{ $role['label'] }}</span>
              </label>
            @endforeach
          </div>
        </section>

        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">2</span>
            <h2 class="sm-upload-section__title js-section-details-title">
              @switch($uploadType)
                @case('reference_books') Book Details @break
                @case('assignments') Assignment Details @break
                @case('study_guides') Study Guide Details @break
                @case('videos') Lesson Details @break
                @case('sample_papers') Solved Paper Details @break
                @case('worksheets') Worksheet Details @break
                @case('question_papers') Question Paper Details @break
                @default Material Details
              @endswitch
            </h2>
          </div>

          <div class="sm-upload-field-group" data-types="all">
            <div class="mb-3">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" value="{{ old('title', $material->title) }}" required placeholder="Enter a clear and descriptive title">
            </div>
            <div class="mb-3">
              <label class="form-label">Description <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control" rows="4" required data-char-max="1000" data-char-counter="#descriptionCount" placeholder="Write a short description about the content">{{ old('description', $material->description) }}</textarea>
              <div id="descriptionCount" class="sm-upload-char-count">0/1000 characters</div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="reference_books">
            <div class="sm-upload-field-row sm-upload-field-row--2 mb-3">
              <div>
                <label class="form-label">Author(s)</label>
                <input type="text" name="meta[author]" class="form-control" value="{{ old('meta.author', data_get($meta, 'author')) }}" placeholder="Author name">
              </div>
              <div>
                <label class="form-label">Publisher</label>
                <input type="text" name="meta[publisher]" class="form-control" value="{{ old('meta.publisher', data_get($meta, 'publisher')) }}" placeholder="Publisher name">
              </div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="all reference_books study_guides worksheets assignments videos question_papers sample_papers notes">
            <div class="sm-upload-field-row sm-upload-field-row--3 mb-3">
              <div>
                <label class="form-label">Class / Grade / Course <span class="text-danger">*</span></label>
                <input type="text" name="class_course" class="form-control" list="classOptions" value="{{ old('class_course', $material->class_course) }}" placeholder="Select class / course">
              </div>
              <div>
                <label class="form-label">Subject <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control" list="subjectOptions" value="{{ old('subject', $material->subject) }}" placeholder="Select subject">
              </div>
              <div class="sm-upload-field-group" data-types="all reference_books study_guides worksheets assignments videos notes">
                <label class="form-label">Topic / Chapter</label>
                <input type="text" name="topic_chapter" class="form-control" value="{{ old('topic_chapter', $material->topic_chapter) }}" placeholder="Select topic / chapter">
              </div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="notes question_papers sample_papers">
            <div class="sm-upload-field-row sm-upload-field-row--2 mb-3">
              <div>
                <label class="form-label">Board / University</label>
                <input type="text" name="board_university" class="form-control" list="boardOptions" value="{{ old('board_university', $material->board_university) }}" placeholder="Select board / university">
              </div>
              <div class="sm-upload-field-group" data-types="notes">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $material->category) }}" placeholder="e.g. School, Competitive Exams">
              </div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="question_papers sample_papers">
            <div class="sm-upload-field-row sm-upload-field-row--4 mb-3">
              <div>
                <label class="form-label">Exam Type <span class="text-danger">*</span></label>
                <input type="text" name="exam_test" class="form-control" list="examTypeOptions" value="{{ old('exam_test', $material->exam_test) }}" placeholder="Select exam type">
              </div>
              <div>
                <label class="form-label">Year <span class="text-danger">*</span></label>
                <input type="text" name="academic_year" class="form-control" value="{{ old('academic_year', $material->academic_year) }}" placeholder="e.g. 2024">
              </div>
              <div>
                <label class="form-label">Term / Session</label>
                <input type="text" name="meta[term]" class="form-control" list="termOptions" value="{{ old('meta.term', data_get($meta, 'term')) }}" placeholder="Optional">
              </div>
              <div>
                <label class="form-label">Month</label>
                <input type="text" name="meta[month]" class="form-control" list="monthOptions" value="{{ old('meta.month', data_get($meta, 'month')) }}" placeholder="Optional">
              </div>
            </div>
            <div class="sm-upload-field-row sm-upload-field-row--2 mb-3">
              <div class="sm-upload-field-group" data-types="question_papers">
                <label class="form-label">Set / Code (if any)</label>
                <input type="text" name="meta[set_code]" class="form-control" value="{{ old('meta.set_code', data_get($meta, 'set_code')) }}" placeholder="Optional">
              </div>
              <div class="sm-upload-field-group" data-types="sample_papers">
                <label class="form-label">Solution Type</label>
                <input type="text" name="meta[solution_type]" class="form-control" value="{{ old('meta.solution_type', data_get($meta, 'solution_type')) }}" placeholder="e.g. Board Exam">
              </div>
              <div class="sm-upload-field-group" data-types="sample_papers">
                <label class="form-label">Solution Format</label>
                <input type="text" name="meta[solution_format]" class="form-control" value="{{ old('meta.solution_format', data_get($meta, 'solution_format')) }}" placeholder="e.g. Step-by-step">
              </div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="reference_books">
            <div class="sm-upload-field-row sm-upload-field-row--3 mb-3">
              <div>
                <label class="form-label">Book Type <span class="text-danger">*</span></label>
                <input type="text" name="meta[book_type]" class="form-control" value="{{ old('meta.book_type', data_get($meta, 'book_type')) }}" placeholder="Textbook, Guidebook">
              </div>
              <div>
                <label class="form-label">Edition (Optional)</label>
                <input type="text" name="meta[edition]" class="form-control" value="{{ old('meta.edition', data_get($meta, 'edition')) }}" placeholder="e.g. 2024 Edition">
              </div>
              <div>
                <label class="form-label">ISBN (Optional)</label>
                <input type="text" name="meta[isbn]" class="form-control" value="{{ old('meta.isbn', data_get($meta, 'isbn')) }}" placeholder="ISBN number">
              </div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="study_guides">
            <div class="sm-upload-field-row sm-upload-field-row--2 mb-3">
              <div>
                <label class="form-label">Guide Type <span class="text-danger">*</span></label>
                <input type="text" name="meta[guide_type]" class="form-control" value="{{ old('meta.guide_type', data_get($meta, 'guide_type')) }}" placeholder="Revision guide, Topic guide">
              </div>
              <div>
                <label class="form-label">Exam Focus (Optional)</label>
                <input type="text" name="meta[exam_focus]" class="form-control" value="{{ old('meta.exam_focus', data_get($meta, 'exam_focus')) }}" placeholder="Board Exam, NEET, JEE">
              </div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="worksheets">
            <div class="sm-upload-field-row sm-upload-field-row--2 mb-3">
              <div>
                <label class="form-label">Worksheet Type <span class="text-danger">*</span></label>
                <input type="text" name="meta[worksheet_type]" class="form-control" value="{{ old('meta.worksheet_type', data_get($meta, 'worksheet_type')) }}" placeholder="Practice, Revision, Activity">
              </div>
              <div>
                <label class="form-label">Difficulty Level</label>
                @php $difficulty = old('difficulty', $material->difficulty ?: 'Medium'); @endphp
                <div class="sm-upload-difficulty">
                  @foreach(['Easy', 'Medium', 'Hard'] as $level)
                    <label class="form-check form-check-inline mb-0">
                      <input class="form-check-input" type="radio" name="difficulty" value="{{ $level }}" @checked($difficulty === $level)>
                      <span class="form-check-label">{{ $level }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="sm-upload-field-row sm-upload-field-row--2 mb-3">
              <div>
                <label class="form-label">Learning Objective (Optional)</label>
                <input type="text" name="meta[learning_objective]" class="form-control" value="{{ old('meta.learning_objective', data_get($meta, 'learning_objective')) }}">
              </div>
              <div>
                <label class="form-label">Estimated Time (Optional)</label>
                <input type="text" name="meta[estimated_time]" class="form-control" value="{{ old('meta.estimated_time', data_get($meta, 'estimated_time')) }}" placeholder="e.g. 45 minutes">
              </div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="assignments">
            <div class="sm-upload-field-row sm-upload-field-row--3 mb-3">
              <div>
                <label class="form-label">Assignment Type <span class="text-danger">*</span></label>
                <input type="text" name="meta[assignment_type]" class="form-control" value="{{ old('meta.assignment_type', data_get($meta, 'assignment_type')) }}" placeholder="Homework, Project">
              </div>
              <div>
                <label class="form-label">Due Date (Optional)</label>
                <input type="date" name="meta[due_date]" class="form-control" value="{{ old('meta.due_date', data_get($meta, 'due_date')) }}">
              </div>
              <div>
                <label class="form-label">Marks (Optional)</label>
                <input type="text" name="meta[marks]" class="form-control" value="{{ old('meta.marks', data_get($meta, 'marks')) }}" placeholder="e.g. 20">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Difficulty Level</label>
              @php $assignmentDifficulty = old('difficulty', $material->difficulty ?: 'Medium'); @endphp
              <div class="sm-upload-difficulty">
                @foreach(['Easy', 'Medium', 'Hard'] as $level)
                  <label class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="radio" name="difficulty" value="{{ $level }}" @checked($assignmentDifficulty === $level)>
                    <span class="form-check-label">{{ $level }}</span>
                  </label>
                @endforeach
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Instructions for Students (Optional)</label>
              <textarea name="meta[instructions]" class="form-control" rows="3" data-char-max="1000" data-char-counter="#instructionsCount" placeholder="Write clear instructions and expected outcomes">{{ old('meta.instructions', data_get($meta, 'instructions')) }}</textarea>
              <div id="instructionsCount" class="sm-upload-char-count">0/1000 characters</div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="videos">
            <div class="mb-3">
              <label class="form-label d-block">Type of Content <span class="text-danger">*</span></label>
              @php $contentType = old('meta.content_type', data_get($meta, 'content_type', 'video')); @endphp
              <div class="sm-upload-difficulty">
                @foreach(['video' => 'Video Lesson', 'audio' => 'Audio Lesson', 'both' => 'Both (Video + Audio)'] as $value => $label)
                  <label class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="radio" name="meta[content_type]" value="{{ $value }}" @checked($contentType === $value)>
                    <span class="form-check-label">{{ $label }}</span>
                  </label>
                @endforeach
              </div>
            </div>
            <div class="sm-upload-field-row sm-upload-field-row--3 mb-3">
              <div>
                <label class="form-label">Lesson Type</label>
                <input type="text" name="meta[lesson_type]" class="form-control" value="{{ old('meta.lesson_type', data_get($meta, 'lesson_type')) }}" placeholder="Concept, Lecture">
              </div>
              <div>
                <label class="form-label">Language</label>
                <input type="text" name="language" class="form-control" list="languageOptions" value="{{ old('language', $material->language) }}" placeholder="Select language">
              </div>
              <div>
                <label class="form-label">Difficulty Level</label>
                @php $videoDifficulty = old('difficulty', $material->difficulty ?: 'Intermediate'); @endphp
                <select name="difficulty" class="form-select">
                  @foreach(['Beginner', 'Intermediate', 'Advanced'] as $level)
                    <option value="{{ $level }}" @selected($videoDifficulty === $level)>{{ $level }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="sm-upload-field-row sm-upload-field-row--3 mb-3">
              <div>
                <label class="form-label">Duration (approx.)</label>
                <input type="text" name="meta[duration]" class="form-control" value="{{ old('meta.duration', data_get($meta, 'duration')) }}" placeholder="e.g. 25 minutes">
              </div>
              <div>
                <label class="form-label d-block">Is this part of a series?</label>
                <div class="sm-upload-difficulty">
                  <label class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="radio" name="meta[is_series]" value="1" @checked(old('meta.is_series', data_get($meta, 'is_series')))>
                    <span class="form-check-label">Yes</span>
                  </label>
                  <label class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="radio" name="meta[is_series]" value="0" @checked(! old('meta.is_series', data_get($meta, 'is_series')))>
                    <span class="form-check-label">No</span>
                  </label>
                </div>
              </div>
              <div class="js-series-part {{ old('meta.is_series', data_get($meta, 'is_series')) ? '' : 'd-none' }}">
                <label class="form-label">Part Number</label>
                <input type="text" name="meta[part_number]" class="form-control" value="{{ old('meta.part_number', data_get($meta, 'part_number')) }}" placeholder="e.g. 2">
              </div>
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="reference_books study_guides worksheets assignments question_papers sample_papers notes">
            <div class="sm-upload-field-row sm-upload-field-row--2 mb-3">
              <div>
                <label class="form-label">Language</label>
                <input type="text" name="language" class="form-control js-language-field" list="languageOptions" value="{{ old('language', $material->language) }}" placeholder="Select language">
              </div>
              <div class="sm-upload-field-group" data-types="reference_books study_guides notes">
                <label class="form-label">Pages (Optional)</label>
                <input type="number" name="pages" class="form-control" min="1" value="{{ old('pages', $material->pages) }}" placeholder="Number of pages">
              </div>
            </div>
          </div>
        </section>

        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">3</span>
            <h2 class="sm-upload-section__title">
              @if($uploadType === 'videos')
                Upload File or Provide Link <span class="sm-upload-section__req">*</span>
              @else
                Upload File(s) <span class="sm-upload-section__req">*</span>
              @endif
            </h2>
          </div>

          @if($uploadType === 'videos')
            <input type="hidden" name="meta[link_type]" value="{{ old('meta.link_type', data_get($meta, 'link_type', 'upload')) }}">
            <div class="sm-upload-link-tabs mb-2">
              @foreach(['upload' => 'Upload File', 'youtube' => 'YouTube Link', 'vimeo' => 'Vimeo Link', 'other' => 'Other Link'] as $tabKey => $tabLabel)
                <button type="button" class="sm-upload-link-tab {{ old('meta.link_type', data_get($meta, 'link_type', 'upload')) === $tabKey ? 'is-active' : '' }}" data-link-tab="{{ $tabKey }}">{{ $tabLabel }}</button>
              @endforeach
            </div>
            <div class="js-link-panel {{ old('meta.link_type', data_get($meta, 'link_type', 'upload')) === 'upload' ? '' : 'd-none' }}" data-link-panel="upload">
              <div class="sm-upload-dropzone" id="fileDropzone">
                <div class="sm-upload-dropzone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <p class="sm-upload-dropzone__text">Drag & drop your lesson file here or click to browse</p>
                <button type="button" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Choose File</button>
                <input type="file" id="materialFileInput" name="file" class="d-none" accept="{{ $typeConfig['accept'] }}">
                <p class="sm-upload-dropzone__hint">{{ $typeConfig['file_hint'] }}</p>
                <div class="sm-upload-dropzone__file-name">{{ $material->file_name }}</div>
              </div>
            </div>
            <div class="js-link-panel {{ old('meta.link_type', data_get($meta, 'link_type', 'upload')) !== 'upload' ? '' : 'd-none' }}" data-link-panel="link">
              <label class="form-label">Lesson URL</label>
              <input type="url" name="meta[external_url]" class="form-control" value="{{ old('meta.external_url', data_get($meta, 'external_url')) }}" placeholder="https://">
              <small class="text-muted">Paste a YouTube, Vimeo or other lesson link if you are not uploading a file.</small>
            </div>
          @else
            <div class="sm-upload-dropzone" id="fileDropzone">
              <div class="sm-upload-dropzone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
              <p class="sm-upload-dropzone__text">Drag & drop your file here or click to browse</p>
              <button type="button" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Choose File(s)</button>
              <input type="file" id="materialFileInput" name="file" class="d-none" accept="{{ $typeConfig['accept'] }}" {{ $isEdit ? '' : 'required' }}>
              <p class="sm-upload-dropzone__hint">{{ $typeConfig['file_hint'] }}</p>
              <div class="sm-upload-dropzone__file-name">{{ $material->file_name }}</div>
            </div>
          @endif
        </section>

        @if(in_array($uploadType, ['reference_books', 'study_guides', 'videos'], true))
          <section class="sm-upload-section sm-upload-field-group" data-types="reference_books study_guides videos">
            <div class="sm-upload-section__head">
              <span class="sm-upload-section__num">4</span>
              <h2 class="sm-upload-section__title">Cover Image / Thumbnail (Optional)</h2>
            </div>
            <div class="sm-upload-cover-grid">
              <div class="sm-upload-cover-box">
                <i class="fa-solid fa-image fa-2x text-muted mb-2"></i>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('materialCoverInput').click()">Choose Image</button>
                <input type="file" id="materialCoverInput" name="thumbnail" class="d-none" accept="image/jpeg,image/png,image/webp">
                <small class="text-muted">JPG, PNG — Max 5 MB</small>
                @if($material->thumbnailUrl())
                  <img src="{{ $material->thumbnailUrl() }}" alt="" class="img-fluid rounded mt-2" style="max-height:80px">
                @endif
              </div>
              <div class="sm-upload-cover-box">
                <i class="fa-solid fa-link fa-2x text-muted mb-2"></i>
                <label class="form-label mb-1">Or provide a link (Optional)</label>
                <input type="url" name="meta[cover_url]" class="form-control" value="{{ old('meta.cover_url', data_get($meta, 'cover_url')) }}" placeholder="Cover image URL">
              </div>
            </div>
          </section>
        @endif

        @if($uploadType === 'notes')
          <section class="sm-upload-section sm-upload-field-group" data-types="notes">
            <div class="sm-upload-section__head">
              <span class="sm-upload-section__num">4</span>
              <h2 class="sm-upload-section__title">Preview</h2>
            </div>
            <label class="sm-upload-option">
              <input class="form-check-input" type="checkbox" name="meta[generate_preview]" value="1" @checked(old('meta.generate_preview', data_get($meta, 'generate_preview', true)))>
              <span>Generate thumbnail/preview (for PDF, Images and PPT)</span>
            </label>
          </section>
        @endif

        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">{{ in_array($uploadType, ['notes', 'reference_books', 'study_guides', 'videos'], true) ? '5' : '4' }}</span>
            <h2 class="sm-upload-section__title">Tags & Keywords</h2>
          </div>
          <input type="text" name="tags" class="form-control" value="{{ $tagsText }}" placeholder="Add keywords separated by commas">
          <small class="text-muted">Enter keywords separated by commas (,)</small>
        </section>

        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">{{ in_array($uploadType, ['notes', 'reference_books', 'study_guides', 'videos'], true) ? '6' : '5' }}</span>
            <h2 class="sm-upload-section__title">Additional Options</h2>
          </div>
          <div class="sm-upload-options-grid">
            @foreach($typeConfig['options'] as $optionKey => $optionLabel)
              <label class="sm-upload-option">
                <input class="form-check-input" type="checkbox" name="meta[options][]" value="{{ $optionKey }}" @checked(in_array($optionKey, $selectedOptions, true) || ($optionKey === 'allow_download' && old('is_free', $material->is_free ?? true) && empty($selectedOptions)))>
                <span>{{ $optionLabel }}</span>
              </label>
            @endforeach
          </div>
          <input type="hidden" name="is_free" value="1">
        </section>

        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">{{ in_array($uploadType, ['notes', 'reference_books', 'study_guides', 'videos'], true) ? '7' : '6' }}</span>
            <h2 class="sm-upload-section__title">Terms & Conditions <span class="sm-upload-section__req">*</span></h2>
          </div>
          <label class="sm-upload-option">
            <input class="form-check-input" type="checkbox" name="terms_accepted" value="1" id="termsAccepted" @checked(old('terms_accepted')) required>
            <span>I agree to the Terms & Conditions and confirm that this content does not violate any copyright and is either my own work or shared with proper permission.</span>
          </label>
        </section>

        <div class="sm-upload-footer">
          <a href="{{ route('educator.materials.index') }}" class="btn btn-light">Cancel</a>
          <button id="studyMaterialSubmitBtn" type="submit" class="btn sm-upload-btn-submit text-white">
            <span class="btn-text"><i class="fa-solid fa-paper-plane me-1"></i> {{ $isEdit ? 'Update & submit for review' : 'Submit for Review' }}</span>
            <span class="btn-loader d-none" aria-hidden="true"></span>
          </button>
        </div>
      </form>
    </div>

    <aside class="sm-upload-sidebar">
      <div class="sm-upload-side-card sm-upload-side-card--tips">
        <div class="sm-upload-side-card__title"><i class="fa-solid fa-lightbulb text-primary"></i> Helpful Tips</div>
        <ul class="sm-upload-side-list">
          @foreach($typeConfig['tips'] as $tip)
            <li><i class="fa-solid fa-check"></i><span>{{ $tip }}</span></li>
          @endforeach
        </ul>
      </div>

      <div class="sm-upload-side-card sm-upload-side-card--guide">
        <div class="sm-upload-side-card__title"><i class="fa-solid fa-list-check text-warning"></i>
          @if(in_array($uploadType, ['question_papers', 'sample_papers'], true))
            Types of {{ $uploadType === 'sample_papers' ? 'Solved Papers' : 'Question Papers' }}
          @elseif($uploadType === 'videos')
            Types of Educational Videos & Audio
          @else
            Content Guidelines
          @endif
        </div>
        <ul class="sm-upload-side-list">
          @foreach($typeConfig['guidelines'] as $item)
            <li><i class="fa-solid fa-check"></i><span>{{ $item }}</span></li>
          @endforeach
        </ul>
      </div>

      <div class="sm-upload-side-card sm-upload-side-card--blocked">
        <div class="sm-upload-side-card__title"><i class="fa-solid fa-ban text-danger"></i> Not Allowed</div>
        <ul class="sm-upload-side-list">
          @foreach($typeConfig['not_allowed'] as $item)
            <li><i class="fa-solid fa-xmark"></i><span>{{ $item }}</span></li>
          @endforeach
        </ul>
      </div>

      <div class="sm-upload-side-card sm-upload-side-card--preview">
        <div class="sm-upload-side-card__title"><i class="fa-solid fa-eye text-primary"></i> {{ $typeConfig['preview_label'] }}</div>
        <div class="sm-upload-preview-thumb">
          <div>
            <i class="fa-solid fa-file-pdf fa-2x text-danger mb-2"></i>
            <div>{{ $typeConfig['preview_caption'] }}</div>
          </div>
        </div>
      </div>

      <div class="sm-upload-side-card sm-upload-side-card--footer">
        <i class="fa-solid fa-graduation-cap fa-lg text-success mb-2"></i>
        <div class="fw-bold text-success">{{ $typeConfig['footer'] }}</div>
      </div>
    </aside>
  </div>
</div>

<datalist id="classOptions">@foreach($classes as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="subjectOptions">@foreach($subjects as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="boardOptions">@foreach($boards as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="languageOptions">@foreach($languages as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="examTypeOptions">@foreach($examTypes as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="termOptions">@foreach($terms as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="monthOptions">@foreach($months as $item)<option value="{{ $item }}">@endforeach</datalist>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/educator-material-upload.js') }}?v={{ now()->timestamp }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.EducatorMaterialUpload) {
      window.EducatorMaterialUpload.init({
        activeType: @json($uploadType),
        isEdit: @json($isEdit),
        indexUrl: @json(route('educator.materials.index')),
        submitText: @json($isEdit ? 'Update & submit for review' : 'Submit for Review')
      });
    }
  });
</script>
@endpush
