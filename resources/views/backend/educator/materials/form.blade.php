@php
  use App\Support\StudyMaterialUploadConfig;

  $isEdit = $material->exists;
  $uploadType = $uploadType ?? old('material_type', $material->material_type ?: 'notes');
  $typeConfig = StudyMaterialUploadConfig::type($uploadType);
  $allTypes = StudyMaterialUploadConfig::types();
  $meta = old('meta', is_array($material->meta) ? $material->meta : []);
  $authUser = auth()->user();
  $resolvedUploaderRole = StudyMaterialUploadConfig::resolveUploaderRole($authUser);
  $visibleUploaderRoles = StudyMaterialUploadConfig::visibleUploaderRoles($authUser);
  $showUploaderRolePicker = StudyMaterialUploadConfig::shouldShowUploaderRolePicker($authUser);
  $showChildSelector = StudyMaterialUploadConfig::shouldShowChildSelector($authUser);
  $approvedChildren = $showChildSelector
      ? $authUser->childProfiles()->where('status', 'approved')->orderByDesc('is_primary')->orderBy('full_name')->get()
      : collect();
  $selectedChildId = old('meta.child_profile_id', data_get($meta, 'child_profile_id'));
  $selectedRole = old('meta.uploader_role', data_get($meta, 'uploader_role', $resolvedUploaderRole));
  $filteredOptions = StudyMaterialUploadConfig::filterOptionsForRole($typeConfig['options'], $selectedRole);
  $noteContentMode = old('meta.content_mode', data_get($meta, 'content_mode', 'upload'));
  $customNoteHtml = old('meta.custom_note_html', data_get($meta, 'custom_note_html', ''));
  $noteEditorLanguage = old('meta.editor_language', data_get($meta, 'editor_language', 'en'));
  $noteVisibility = old('meta.visibility', data_get($meta, 'visibility', 'public'));
  $notePricingMode = old('meta.pricing_mode', data_get($meta, 'pricing_mode', ($material->is_free ?? true) ? 'free' : 'paid'));
  $notePrice = old('price', $material->price);
  $selectedOptions = collect(old('meta.options', data_get($meta, 'options', [])))->map(fn ($v) => (string) $v)->all();
  $tagsText = old('tags', is_array($material->tags) ? implode(', ', $material->tags) : '');
  $classes = ['Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12', 'Graduate', 'Postgraduate'];
  $subjects = ['Mathematics', 'Physics', 'Chemistry', 'Biology', 'English', 'Hindi', 'Science', 'Social Science', 'Computer Science'];
  $boards = ['CBSE', 'ICSE', 'State Board', 'NCERT', 'IB', 'IGCSE'];
  $languages = ['English', 'Hindi', 'Gujarati', 'Marathi', 'Tamil', 'Telugu', 'Bengali', 'Kannada'];
  $examTypes = ['Board Exam', 'School Exam', 'Competitive Exam', 'Entrance Exam', 'Unit Test', 'Mid Term', 'Final Exam'];
  $terms = ['Annual', 'Half Yearly', 'Term 1', 'Term 2', 'Semester 1', 'Semester 2'];
  $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  $selectedAcademicYear = old('academic_year', $material->academic_year);
  $yearOptions = StudyMaterialUploadConfig::yearOptions($selectedAcademicYear);
  $qpIsBoard = filter_var(old('meta.is_board', data_get($meta, 'is_board', true)), FILTER_VALIDATE_BOOLEAN);
  $qpInstitutionType = old('meta.institution_type', data_get($meta, 'institution_type', 'university'));
  $qpInstitutionTypes = StudyMaterialUploadConfig::questionPaperInstitutionTypes();
  $boardExamTypes = StudyMaterialUploadConfig::boardExamTypes();
  $institutionExamTypes = StudyMaterialUploadConfig::institutionExamTypes();
  $qpSolutionMode = old('meta.solution_mode', data_get($meta, 'solution_mode', 'upload'));
  $customSolutionHtml = old('meta.custom_solution_html', data_get($meta, 'custom_solution_html', ''));
  $qpSolutionEditorLanguage = old('meta.solution_editor_language', data_get($meta, 'solution_editor_language', 'en'));
  $existingSolutionFileName = data_get($meta, 'solution_file_name');
  $worksheetSolvedMode = old('meta.solved_worksheet_mode', data_get($meta, 'solved_worksheet_mode', 'upload'));
  $customSolvedWorksheetHtml = old('meta.custom_solved_worksheet_html', data_get($meta, 'custom_solved_worksheet_html', ''));
  $worksheetSolvedEditorLanguage = old('meta.solved_worksheet_editor_language', data_get($meta, 'solved_worksheet_editor_language', 'en'));
  $existingSolvedWorksheetFileName = data_get($meta, 'solved_worksheet_file_name');
  $authorRightsConfirmed = filter_var(old('meta.author_rights_confirmed', data_get($meta, 'author_rights_confirmed')), FILTER_VALIDATE_BOOLEAN);
@endphp
@extends('backend.layouts.app')
@section('title', $isEdit ? 'Edit '.$typeConfig['title'] : $typeConfig['title'])

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Tiro+Devanagari+Hindi&display=swap" rel="stylesheet">
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
      <header class="sm-upload-header" id="smUploadHeader">
        <div class="sm-upload-header__title-wrap">
          <span class="sm-upload-header__icon"><i class="fa-solid {{ $typeConfig['icon'] }} js-type-icon" aria-hidden="true"></i></span>
          <div>
            <h1 class="sm-upload-header__title js-type-title">{{ $isEdit ? 'Edit: '.$typeConfig['title'] : $typeConfig['title'] }}</h1>
            <p class="sm-upload-header__subtitle js-type-subtitle">{{ $typeConfig['subtitle'] }}</p>
          </div>
        </div>
        <div class="sm-upload-quote js-type-quote">{{ $typeConfig['quote'] }}</div>
      </header>

      <div id="studyMaterialAlert" class="alert d-none" role="alert"></div>

      <nav id="materialWizardProgress" class="sm-upload-wizard__progress" aria-label="Form progress"></nav>

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
        <div class="sm-upload-step" data-step-id="type" data-step-label="Material Type">
          <section class="sm-upload-section">
            <div class="sm-upload-section__head">
              <span class="sm-upload-section__num">1</span>
              <h2 class="sm-upload-section__title">Choose material type <span class="sm-upload-section__req">*</span></h2>
            </div>
            <div class="sm-upload-type-grid" id="smUploadTypeGrid">
              @foreach($allTypes as $key => $config)
                <button type="button" class="sm-upload-type-card {{ $uploadType === $key ? 'is-active' : '' }}" data-type="{{ $key }}">
                  <i class="fa-solid {{ $config['icon'] }}" aria-hidden="true"></i>
                  <span>{{ str_replace('Upload ', '', $config['title']) }}</span>
                </button>
              @endforeach
            </div>
          </section>

          @if($showChildSelector)
          <section class="sm-upload-section sm-upload-section--role">
            <div class="sm-upload-section__head">
              <span class="sm-upload-section__num">2</span>
              <h2 class="sm-upload-section__title">Uploading for child (student) <span class="sm-upload-section__req">*</span></h2>
            </div>
            <p class="text-muted small mb-3">Select which child profile this material is for. Child accounts use the student role.</p>
            <select name="meta[child_profile_id]" class="form-select" required>
              <option value="">Select a child</option>
              @foreach($approvedChildren as $child)
                <option value="{{ $child->id }}" @selected((string) $selectedChildId === (string) $child->id)>
                  {{ $child->full_name }}@if($child->class_grade) — {{ $child->class_grade }}@endif
                </option>
              @endforeach
            </select>
            <input type="hidden" name="meta[uploader_role]" value="student">
          </section>
          @elseif($showUploaderRolePicker)
          <section class="sm-upload-section sm-upload-section--role">
            <div class="sm-upload-section__head">
              <span class="sm-upload-section__num">2</span>
              <h2 class="sm-upload-section__title">I am uploading as <span class="sm-upload-section__req">*</span></h2>
            </div>
            <div class="sm-upload-role-grid">
              @foreach($visibleUploaderRoles as $roleKey => $role)
                <label class="sm-upload-role-card {{ $selectedRole === $roleKey ? 'is-active' : '' }}">
                  <input type="radio" name="meta[uploader_role]" value="{{ $roleKey }}" @checked($selectedRole === $roleKey) required>
                  <i class="fa-solid {{ $role['icon'] }}" aria-hidden="true"></i>
                  <span>{{ $role['label'] }}</span>
                </label>
              @endforeach
            </div>
          </section>
          @else
          <input type="hidden" name="meta[uploader_role]" value="{{ $resolvedUploaderRole }}">
          @endif
        </div>
        @else
        <input type="hidden" name="meta[uploader_role]" value="{{ $selectedRole }}">
        @endif

        <div class="sm-upload-step" data-step-id="details" data-step-label="Details">
        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">2</span>
            <h2 class="sm-upload-section__title js-section-details-title">Material Details</h2>
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
                <label class="form-label js-qp-class-label">Class / Grade / Course <span class="text-danger">*</span></label>
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

          <div class="sm-upload-field-group" data-types="notes sample_papers">
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

          <div class="sm-upload-field-group" data-types="question_papers">
            <div class="mb-3">
              <input type="hidden" name="meta[is_board]" value="0">
              <label class="sm-upload-option mb-0">
                <input type="checkbox" class="form-check-input js-qp-is-board" name="meta[is_board]" value="1" @checked($qpIsBoard)>
                <span>This is a board exam question paper</span>
              </label>
            </div>

            <div class="js-qp-board-fields {{ $qpIsBoard ? '' : 'd-none' }}">
              <div class="mb-3">
                <label class="form-label">Board Name <span class="text-danger">*</span></label>
                <input type="text" name="board_university" class="form-control js-qp-board-name" list="boardOptions" value="{{ old('board_university', $material->board_university) }}" placeholder="Select board" @disabled(! $qpIsBoard)>
              </div>
            </div>

            <div class="js-qp-institution-fields {{ $qpIsBoard ? 'd-none' : '' }}">
              <div class="mb-3">
                <label class="form-label d-block">Institution Type <span class="text-danger">*</span></label>
                <input type="hidden" name="meta[institution_type]" id="qpInstitutionTypeInput" value="{{ $qpInstitutionType }}">
                <div class="sm-upload-link-tabs mb-2">
                  @foreach($qpInstitutionTypes as $typeKey => $typeLabel)
                    <button type="button" class="sm-upload-link-tab js-qp-institution-type {{ $qpInstitutionType === $typeKey ? 'is-active' : '' }}" data-qp-institution-type="{{ $typeKey }}">
                      @if($typeKey === 'university')
                        <i class="fa-solid fa-building-columns me-1"></i>
                      @elseif($typeKey === 'college')
                        <i class="fa-solid fa-school me-1"></i>
                      @else
                        <i class="fa-solid fa-graduation-cap me-1"></i>
                      @endif
                      {{ $typeLabel }}
                    </button>
                  @endforeach
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label js-qp-institution-name-label">University Name <span class="text-danger">*</span></label>
                <input type="text" name="board_university" class="form-control js-qp-institution-name" value="{{ old('board_university', $material->board_university) }}" placeholder="Enter university name" @disabled($qpIsBoard)>
              </div>
            </div>

            <div class="sm-upload-field-row sm-upload-field-row--4 mb-3">
              <div>
                <label class="form-label">Exam Type <span class="text-danger">*</span></label>
                <input type="text" name="exam_test" id="qpExamTypeInput" class="form-control" list="{{ $qpIsBoard ? 'qpBoardExamTypeOptions' : 'qpInstitutionExamTypeOptions' }}" value="{{ old('exam_test', $material->exam_test) }}" placeholder="Select exam type">
              </div>
              <div>
                <label class="form-label">Year <span class="text-danger">*</span></label>
                <select name="academic_year" class="form-select" required>
                  <option value="" disabled @selected(! filled($selectedAcademicYear))>Select year</option>
                  @foreach($yearOptions as $year)
                    <option value="{{ $year }}" @selected((string) $selectedAcademicYear === (string) $year)>{{ $year }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="form-label js-qp-term-label">{{ $qpIsBoard ? 'Term / Session' : 'Semester / Term' }}</label>
                <input type="text" name="meta[term]" class="form-control" list="termOptions" value="{{ old('meta.term', data_get($meta, 'term')) }}" placeholder="Optional">
              </div>
              <div class="js-qp-board-only {{ $qpIsBoard ? '' : 'd-none' }}">
                <label class="form-label">Month</label>
                <input type="text" name="meta[month]" class="form-control js-qp-month-input" list="monthOptions" value="{{ old('meta.month', data_get($meta, 'month')) }}" placeholder="Optional" @disabled(! $qpIsBoard)>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Code / Serial No.</label>
              <input type="text" name="meta[set_code]" class="form-control" value="{{ old('meta.set_code', data_get($meta, 'set_code')) }}" placeholder="e.g. Set A, QP-2024-01">
            </div>
          </div>

          <div class="sm-upload-field-group" data-types="sample_papers">
            <div class="sm-upload-field-row sm-upload-field-row--4 mb-3">
              <div>
                <label class="form-label">Exam Type <span class="text-danger">*</span></label>
                <input type="text" name="exam_test" class="form-control" list="examTypeOptions" value="{{ old('exam_test', $material->exam_test) }}" placeholder="Select exam type">
              </div>
              <div>
                <label class="form-label">Year <span class="text-danger">*</span></label>
                <select name="academic_year" class="form-select" required>
                  <option value="" disabled @selected(! filled($selectedAcademicYear))>Select year</option>
                  @foreach($yearOptions as $year)
                    <option value="{{ $year }}" @selected((string) $selectedAcademicYear === (string) $year)>{{ $year }}</option>
                  @endforeach
                </select>
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
            <div class="sm-upload-field-row sm-upload-field-row--3 mb-3">
              <div>
                <label class="form-label">Solution Type</label>
                <input type="text" name="meta[solution_type]" class="form-control" value="{{ old('meta.solution_type', data_get($meta, 'solution_type')) }}" placeholder="e.g. Board Exam">
              </div>
              <div>
                <label class="form-label">Solution Format</label>
                <input type="text" name="meta[solution_format]" class="form-control" value="{{ old('meta.solution_format', data_get($meta, 'solution_format')) }}" placeholder="e.g. Step-by-step">
              </div>
              <div>
                <label class="form-label">Marks Obtained</label>
                <input type="text" name="meta[marks_obtained]" class="form-control" value="{{ old('meta.marks_obtained', data_get($meta, 'marks_obtained')) }}" placeholder="e.g. 92/100">
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
            <div class="mb-3">
              <label class="sm-upload-option mb-0">
                <input type="checkbox" class="form-check-input" name="meta[author_rights_confirmed]" id="referenceBookAuthorRights" value="1" @checked($authorRightsConfirmed) required>
                <span>I confirm that I am the author of this book and I hold all rights to upload and share it. <span class="text-danger">*</span></span>
              </label>
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
        </div>

        <div class="sm-upload-step" data-step-id="upload" data-step-label="Upload & Options">
        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">3</span>
            <h2 class="sm-upload-section__title js-upload-section-title">Upload File(s) <span class="sm-upload-section__req">*</span></h2>
          </div>

          <div class="sm-upload-field-group mb-3 js-custom-content-toggle" data-types="notes sample_papers worksheets assignments">
            <label class="form-label d-block js-custom-content-question">How would you like to add your notes? <span class="text-danger">*</span></label>
            <input type="hidden" name="meta[content_mode]" id="noteContentModeInput" value="{{ $noteContentMode }}">
            <div class="sm-upload-link-tabs">
              <button type="button" class="sm-upload-link-tab js-custom-content-mode {{ $noteContentMode === 'upload' ? 'is-active' : '' }}" data-content-mode="upload">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> <span class="js-custom-content-upload-tab">Upload File</span>
              </button>
              <button type="button" class="sm-upload-link-tab js-custom-content-mode {{ $noteContentMode === 'custom' ? 'is-active' : '' }}" data-content-mode="custom">
                <i class="fa-solid fa-pen-to-square me-1"></i> <span class="js-custom-content-write-tab">Write Custom Note</span>
              </button>
            </div>
            <small class="text-muted d-block mt-2 js-custom-content-help">Upload a PDF/DOC file, or write your notes directly using the same rich text editor as community posts.</small>
          </div>

          <div class="js-custom-content-panel sm-upload-field-group {{ $noteContentMode === 'custom' ? '' : 'd-none' }}" data-types="notes sample_papers worksheets assignments">
            @include('backend.partials.community-body-editor', [
              'editorPrefix' => 'note',
              'initialContent' => $customNoteHtml,
              'initialLanguage' => $noteEditorLanguage,
            ])
          </div>

          <div class="js-custom-content-upload-panel {{ $noteContentMode === 'custom' ? 'd-none' : '' }}">
          <div class="js-video-upload-tools d-none" data-type-panel="videos">
            <input type="hidden" name="meta[link_type]" value="{{ old('meta.link_type', data_get($meta, 'link_type', 'upload')) }}">
            <div class="sm-upload-link-tabs mb-2">
              @foreach(['upload' => 'Upload File', 'youtube' => 'YouTube Link', 'vimeo' => 'Vimeo Link', 'other' => 'Other Link'] as $tabKey => $tabLabel)
                <button type="button" class="sm-upload-link-tab {{ old('meta.link_type', data_get($meta, 'link_type', 'upload')) === $tabKey ? 'is-active' : '' }}" data-link-tab="{{ $tabKey }}">{{ $tabLabel }}</button>
              @endforeach
            </div>
            <div class="js-link-panel {{ old('meta.link_type', data_get($meta, 'link_type', 'upload')) === 'upload' ? '' : 'd-none' }}" data-link-panel="upload"></div>
            <div class="js-link-panel {{ old('meta.link_type', data_get($meta, 'link_type', 'upload')) !== 'upload' ? '' : 'd-none' }}" data-link-panel="link">
              <label class="form-label">Lesson URL</label>
              <input type="url" name="meta[external_url]" class="form-control" value="{{ old('meta.external_url', data_get($meta, 'external_url')) }}" placeholder="https://">
              <small class="text-muted">Paste a YouTube, Vimeo or other lesson link if you are not uploading a file.</small>
            </div>
          </div>

          <div class="sm-upload-dropzone js-file-dropzone" id="fileDropzone">
            <div class="sm-upload-dropzone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <p class="sm-upload-dropzone__text js-dropzone-text">Drag & drop your file here or click to browse</p>
            <button type="button" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> <span class="js-dropzone-btn-label">Choose File(s)</span></button>
            <input type="file" id="materialFileInput" name="file" class="d-none" accept="{{ $typeConfig['accept'] }}">
            <p class="sm-upload-dropzone__hint js-file-hint">{{ $typeConfig['file_hint'] }}</p>
            <div class="sm-upload-dropzone__file-name">{{ $material->file_name }}</div>
          </div>
          </div>

          <div class="js-qp-board-solution-section sm-upload-field-group {{ $qpIsBoard ? '' : 'd-none' }}" data-types="question_papers">
            <div class="sm-upload-step-divider mt-4">
              <span>Board Paper Solution (Optional)</span>
            </div>

            <label class="form-label d-block">How would you like to add the solution?</label>
            <input type="hidden" name="meta[solution_mode]" id="qpSolutionModeInput" value="{{ $qpSolutionMode }}">
            <div class="sm-upload-link-tabs mb-2">
              <button type="button" class="sm-upload-link-tab js-qp-solution-mode {{ $qpSolutionMode === 'upload' ? 'is-active' : '' }}" data-qp-solution-mode="upload">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Solution
              </button>
              <button type="button" class="sm-upload-link-tab js-qp-solution-mode {{ $qpSolutionMode === 'custom' ? 'is-active' : '' }}" data-qp-solution-mode="custom">
                <i class="fa-solid fa-pen-to-square me-1"></i> Write Solution
              </button>
            </div>
            <small class="text-muted d-block mb-3">Upload an answer key file or write the solution using the same rich text editor as community posts.</small>

            <div class="js-qp-solution-upload-panel {{ $qpSolutionMode === 'custom' ? 'd-none' : '' }}">
              <div class="sm-upload-dropzone js-solution-dropzone" id="solutionFileDropzone">
                <div class="sm-upload-dropzone__icon"><i class="fa-solid fa-file-circle-check"></i></div>
                <p class="sm-upload-dropzone__text">Drag & drop solution file here or click to browse</p>
                <button type="button" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Choose Solution File</button>
                <input type="file" id="solutionFileInput" name="solution_file" class="d-none" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <p class="sm-upload-dropzone__hint">Supported formats: PDF, DOC, DOCX, JPG, PNG | Max file size: 50 MB</p>
                <div class="sm-upload-dropzone__file-name">{{ $existingSolutionFileName }}</div>
              </div>
            </div>

            <div class="js-qp-solution-custom-panel {{ $qpSolutionMode === 'custom' ? '' : 'd-none' }}">
              @include('backend.partials.community-body-editor', [
                'editorPrefix' => 'solution',
                'editorFieldName' => 'meta[custom_solution_html]',
                'languageFieldName' => 'meta[solution_editor_language]',
                'initialContent' => $customSolutionHtml,
                'initialLanguage' => $qpSolutionEditorLanguage,
              ])
            </div>
          </div>

          <div class="js-worksheet-solved-section sm-upload-field-group" data-types="worksheets">
            <div class="sm-upload-step-divider mt-4">
              <span>Solved Worksheet (Optional)</span>
            </div>

            <label class="form-label d-block">Add a solved worksheet answer key?</label>
            <input type="hidden" name="meta[solved_worksheet_mode]" id="worksheetSolvedModeInput" value="{{ $worksheetSolvedMode }}">
            <div class="sm-upload-link-tabs mb-2">
              <button type="button" class="sm-upload-link-tab js-worksheet-solved-mode {{ $worksheetSolvedMode === 'upload' ? 'is-active' : '' }}" data-worksheet-solved-mode="upload">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Solved Worksheet
              </button>
              <button type="button" class="sm-upload-link-tab js-worksheet-solved-mode {{ $worksheetSolvedMode === 'custom' ? 'is-active' : '' }}" data-worksheet-solved-mode="custom">
                <i class="fa-solid fa-pen-to-square me-1"></i> Write Solved Worksheet
              </button>
            </div>
            <small class="text-muted d-block mb-3">Optional. Upload an answer key or write the solved worksheet using the same rich text editor as community posts.</small>

            <div class="js-worksheet-solved-upload-panel {{ $worksheetSolvedMode === 'custom' ? 'd-none' : '' }}">
              <div class="sm-upload-dropzone js-solved-worksheet-dropzone" id="solvedWorksheetFileDropzone">
                <div class="sm-upload-dropzone__icon"><i class="fa-solid fa-file-circle-check"></i></div>
                <p class="sm-upload-dropzone__text">Drag & drop solved worksheet file here or click to browse</p>
                <button type="button" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Choose Solved Worksheet File</button>
                <input type="file" id="solvedWorksheetFileInput" name="solved_worksheet_file" class="d-none" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <p class="sm-upload-dropzone__hint">Supported formats: PDF, DOC, DOCX, JPG, PNG | Max file size: 50 MB</p>
                <div class="sm-upload-dropzone__file-name">{{ $existingSolvedWorksheetFileName }}</div>
              </div>
            </div>

            <div class="js-worksheet-solved-custom-panel {{ $worksheetSolvedMode === 'custom' ? '' : 'd-none' }}">
              @include('backend.partials.community-body-editor', [
                'editorPrefix' => 'worksheetSolved',
                'editorFieldName' => 'meta[custom_solved_worksheet_html]',
                'languageFieldName' => 'meta[solved_worksheet_editor_language]',
                'initialContent' => $customSolvedWorksheetHtml,
                'initialLanguage' => $worksheetSolvedEditorLanguage,
              ])
            </div>
          </div>
        </section>

          <section class="sm-upload-section sm-upload-field-group d-none" data-types="reference_books study_guides videos">
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

          <section class="sm-upload-section sm-upload-field-group d-none" data-types="notes">
            <div class="sm-upload-section__head">
              <span class="sm-upload-section__num">4</span>
              <h2 class="sm-upload-section__title">Preview</h2>
            </div>
            <label class="sm-upload-option">
              <input class="form-check-input" type="checkbox" name="meta[generate_preview]" value="1" @checked(old('meta.generate_preview', data_get($meta, 'generate_preview', true)))>
              <span>Generate thumbnail/preview (for PDF, Images and PPT)</span>
            </label>
          </section>

          <div class="sm-upload-step-divider">
            <span>Tags, visibility &amp; options</span>
          </div>

        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">5</span>
            <h2 class="sm-upload-section__title">Tags &amp; Keywords</h2>
          </div>
          <input type="text" name="tags" class="form-control" value="{{ $tagsText }}" placeholder="Add keywords separated by commas">
          <small class="text-muted d-block mb-0">Enter keywords separated by commas (,)</small>

          <div class="sm-upload-section__sub sm-upload-field-group" data-types="notes">
            <h3 class="sm-upload-section__subtitle">Note Visibility</h3>
            <input type="hidden" name="meta[visibility]" id="noteVisibilityInput" value="{{ $noteVisibility }}">
            <div class="sm-upload-link-tabs mb-2">
              <button type="button" class="sm-upload-link-tab js-note-visibility {{ $noteVisibility === 'public' ? 'is-active' : '' }}" data-note-visibility="public">
                <i class="fa-solid fa-globe me-1"></i> Public
              </button>
              <button type="button" class="sm-upload-link-tab js-note-visibility {{ $noteVisibility === 'personal' ? 'is-active' : '' }}" data-note-visibility="personal">
                <i class="fa-solid fa-lock me-1"></i> Personal
              </button>
            </div>
            <small class="text-muted d-block">Public notes can appear in the library for everyone. Personal notes are only visible to you.</small>
          </div>
        </section>

        <section class="sm-upload-section sm-upload-field-group js-note-pricing-section {{ $noteVisibility === 'personal' ? 'd-none' : '' }}" data-types="notes">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">6</span>
            <h2 class="sm-upload-section__title">Pricing</h2>
          </div>
          <input type="hidden" name="meta[pricing_mode]" id="notePricingModeInput" value="{{ $notePricingMode }}">
          <input type="hidden" name="is_free" id="noteIsFreeInput" value="{{ $notePricingMode === 'free' ? '1' : '0' }}">
          <div class="sm-upload-link-tabs mb-3">
            <button type="button" class="sm-upload-link-tab js-note-pricing {{ $notePricingMode === 'free' ? 'is-active' : '' }}" data-note-pricing="free">
              <i class="fa-solid fa-gift me-1"></i> Free
            </button>
            <button type="button" class="sm-upload-link-tab js-note-pricing {{ $notePricingMode === 'paid' ? 'is-active' : '' }}" data-note-pricing="paid">
              <i class="fa-solid fa-indian-rupee-sign me-1"></i> For Sale
            </button>
          </div>
          <div class="js-note-price-field {{ $notePricingMode === 'paid' ? '' : 'd-none' }}">
            <label class="form-label">Price (INR) <span class="text-danger">*</span></label>
            <input type="number" name="price" id="notePriceInput" class="form-control" min="1" step="0.01" value="{{ $notePrice }}" placeholder="e.g. 99">
            <small class="text-muted">Buyers pay via UPI QR code and submit proof. Admin verifies payment before access is granted.</small>
          </div>
        </section>

        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">7</span>
            <h2 class="sm-upload-section__title">Additional Options</h2>
          </div>
          <div class="sm-upload-options-grid" id="smUploadOptionsGrid">
            @foreach($filteredOptions as $optionKey => $optionLabel)
              <label class="sm-upload-option">
                <input class="form-check-input" type="checkbox" name="meta[options][]" value="{{ $optionKey }}" @checked(in_array($optionKey, $selectedOptions, true) || ($optionKey === 'allow_download' && old('is_free', $material->is_free ?? true) && empty($selectedOptions)))>
                <span>{{ $optionLabel }}</span>
              </label>
            @endforeach
          </div>
        </section>
        </div>

        <div class="sm-upload-step" data-step-id="terms" data-step-label="Review">
        <section class="sm-upload-section">
          <div class="sm-upload-section__head">
            <span class="sm-upload-section__num">{{ $isEdit ? '3' : '4' }}</span>
            <h2 class="sm-upload-section__title">Terms & Conditions <span class="sm-upload-section__req">*</span></h2>
          </div>
          <label class="sm-upload-option">
            <input class="form-check-input" type="checkbox" name="terms_accepted" value="1" id="termsAccepted" @checked(old('terms_accepted')) required>
            <span>I agree to the Terms & Conditions and confirm that this content does not violate any copyright and is either my own work or shared with proper permission.</span>
          </label>
        </section>
        </div>

        <div class="sm-upload-footer">
          <a href="{{ route('educator.materials.index') }}" class="btn btn-light">Cancel</a>
          <div class="sm-upload-footer__actions">
            <button type="button" id="studyMaterialPrevBtn" class="btn btn-outline-secondary d-none">
              <i class="fa-solid fa-arrow-left me-1"></i> Back
            </button>
            <button type="button" id="studyMaterialNextBtn" class="btn btn-primary">
              Next <i class="fa-solid fa-arrow-right ms-1"></i>
            </button>
            <button id="studyMaterialSubmitBtn" type="button" class="btn sm-upload-btn-submit text-white d-none">
              <span class="btn-text"><i class="fa-solid fa-paper-plane me-1"></i> {{ $isEdit ? 'Update & submit for review' : 'Submit for Review' }}</span>
              <span class="btn-loader d-none" aria-hidden="true"></span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <aside class="sm-upload-sidebar" id="smUploadSidebar">
      <div class="sm-upload-side-card sm-upload-side-card--tips">
        <div class="sm-upload-side-card__title"><i class="fa-solid fa-lightbulb text-primary"></i> Helpful Tips</div>
        <ul class="sm-upload-side-list js-type-tips">
          @foreach($typeConfig['tips'] as $tip)
            <li><i class="fa-solid fa-check"></i><span>{{ $tip }}</span></li>
          @endforeach
        </ul>
      </div>

      <div class="sm-upload-side-card sm-upload-side-card--guide">
        <div class="sm-upload-side-card__title"><i class="fa-solid fa-list-check text-warning"></i> <span class="js-type-guidelines-title">Content Guidelines</span></div>
        <ul class="sm-upload-side-list js-type-guidelines">
          @foreach($typeConfig['guidelines'] as $item)
            <li><i class="fa-solid fa-check"></i><span>{{ $item }}</span></li>
          @endforeach
        </ul>
      </div>

      <div class="sm-upload-side-card sm-upload-side-card--blocked">
        <div class="sm-upload-side-card__title"><i class="fa-solid fa-ban text-danger"></i> Not Allowed</div>
        <ul class="sm-upload-side-list js-type-not-allowed">
          @foreach($typeConfig['not_allowed'] as $item)
            <li><i class="fa-solid fa-xmark"></i><span>{{ $item }}</span></li>
          @endforeach
        </ul>
      </div>

      <div class="sm-upload-side-card sm-upload-side-card--preview">
        <div class="sm-upload-side-card__title"><i class="fa-solid fa-eye text-primary"></i> <span class="js-type-preview-label">{{ $typeConfig['preview_label'] }}</span></div>
        <div class="sm-upload-preview-thumb">
          <div>
            <i class="fa-solid fa-file-pdf fa-2x text-danger mb-2"></i>
            <div class="js-type-preview-caption">{{ $typeConfig['preview_caption'] }}</div>
          </div>
        </div>
      </div>

      <div class="sm-upload-side-card sm-upload-side-card--footer">
        <i class="fa-solid fa-graduation-cap fa-lg text-success mb-2"></i>
        <div class="fw-bold text-success js-type-footer">{{ $typeConfig['footer'] }}</div>
      </div>
    </aside>
  </div>
</div>

<datalist id="classOptions">@foreach($classes as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="subjectOptions">@foreach($subjects as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="boardOptions">@foreach($boards as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="languageOptions">@foreach($languages as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="examTypeOptions">@foreach($examTypes as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="qpBoardExamTypeOptions">@foreach($boardExamTypes as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="qpInstitutionExamTypeOptions">@foreach($institutionExamTypes as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="termOptions">@foreach($terms as $item)<option value="{{ $item }}">@endforeach</datalist>
<datalist id="monthOptions">@foreach($months as $item)<option value="{{ $item }}">@endforeach</datalist>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@indic-transliteration/sanscript@1.3.3/sanscript.js"></script>
<script src="{{ asset('assets/js/krutidev-to-unicode.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/community-body-editor.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/educator-material-upload.js') }}?v={{ now()->timestamp }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.EducatorMaterialUpload) {
      window.EducatorMaterialUpload.init({
        activeType: @json($uploadType),
        isEdit: @json($isEdit),
        uploaderRole: @json($selectedRole),
        showChildSelector: @json($showChildSelector),
        noteContentMode: @json($noteContentMode),
        qpSolutionMode: @json($qpSolutionMode),
        worksheetSolvedMode: @json($worksheetSolvedMode),
        hiddenOptions: @json(StudyMaterialUploadConfig::hiddenOptionsForRole($selectedRole)),
        roleHiddenOptions: @json(collect(array_keys(StudyMaterialUploadConfig::UPLOADER_ROLES))->mapWithKeys(fn ($roleKey) => [$roleKey => StudyMaterialUploadConfig::hiddenOptionsForRole($roleKey)])->all()),
        indexUrl: @json(route('educator.materials.index')),
        typeConfigUrl: @json(route('educator.materials.type-config', ['type' => '__TYPE__'])),
        noteEditorConfig: {
          instanceKey: 'noteMaterial',
          textareaId: 'noteBodyEditor',
          mountId: 'noteBodyEditorMount',
          languageSelectId: 'noteEditorLanguageSelect',
          languageHiddenId: 'noteEditorLanguageHidden',
          transliterationHintId: 'noteEditorTransliterationHint',
          imageUploadUrl: @json(route('community.posts.uploads.image')),
          attachmentUploadUrl: @json(route('community.posts.uploads.attachment')),
          csrfToken: @json(csrf_token()),
          initialLanguage: @json($noteEditorLanguage),
        },
        solutionEditorConfig: {
          instanceKey: 'boardSolution',
          textareaId: 'solutionBodyEditor',
          mountId: 'solutionBodyEditorMount',
          languageSelectId: 'solutionEditorLanguageSelect',
          languageHiddenId: 'solutionEditorLanguageHidden',
          transliterationHintId: 'solutionEditorTransliterationHint',
          imageUploadUrl: @json(route('community.posts.uploads.image')),
          attachmentUploadUrl: @json(route('community.posts.uploads.attachment')),
          csrfToken: @json(csrf_token()),
          initialLanguage: @json($qpSolutionEditorLanguage),
        },
        worksheetSolvedEditorConfig: {
          instanceKey: 'worksheetSolved',
          textareaId: 'worksheetSolvedBodyEditor',
          mountId: 'worksheetSolvedBodyEditorMount',
          languageSelectId: 'worksheetSolvedEditorLanguageSelect',
          languageHiddenId: 'worksheetSolvedEditorLanguageHidden',
          transliterationHintId: 'worksheetSolvedEditorTransliterationHint',
          imageUploadUrl: @json(route('community.posts.uploads.image')),
          attachmentUploadUrl: @json(route('community.posts.uploads.attachment')),
          csrfToken: @json(csrf_token()),
          initialLanguage: @json($worksheetSolvedEditorLanguage),
        },
        submitText: @json($isEdit ? 'Update & submit for review' : 'Submit for Review')
      });
    }
  });
</script>
@endpush
