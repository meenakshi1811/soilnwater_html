{{-- Owner-only inline editor for a teacher / tutor's page: the Edit page button, the section bar
     and the popup the section forms load into (educator-inline-edit.js) --}}
@php
  $eduInlineSections = [
    'overview' => ['Photo & headline', 'fa-id-badge'],
    'about' => ['About me', 'fa-circle-info'],
    'subjects' => ['Subjects & classes', 'fa-book'],
    'experience' => ['Experience & education', 'fa-briefcase'],
    'achievements' => ['Achievements', 'fa-trophy'],
    'notices' => ['Notices', 'fa-bullhorn'],
    'affiliations' => ['Schools & institutes', 'fa-school'],
    'fees' => ['Fees & packages', 'fa-indian-rupee-sign'],
    'materials' => ['Courses & material', 'fa-folder-open'],
  ];
@endphp
<div id="eduInlineEdit" data-form-url-base="{{ route('educator.inline.form', [$educator->slug, '__SECTION__']) }}" data-csrf="{{ csrf_token() }}">
  <button type="button" class="edu-inline-fab js-edu-inline-toggle" aria-pressed="false">
    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
    <span class="js-edu-inline-toggle-label">Edit page</span>
  </button>

  <div class="edu-inline-toolbar" role="toolbar" aria-label="Edit sections">
    <span class="edu-inline-toolbar__label">Edit a section:</span>
    @foreach($eduInlineSections as $key => [$label, $icon])
      <button type="button" class="edu-inline-toolbar__btn js-edu-inline-edit" data-inline-edit="{{ $key }}" data-label="{{ $label }}">
        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }}
      </button>
    @endforeach
  </div>
</div>

<div class="modal fade edu-inline-modal" tabindex="-1" id="eduInlineEditor" aria-labelledby="eduInlineEditorLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eduInlineEditorLabel">Edit section</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="eduInlineEditorBody"></div>
    </div>
  </div>
</div>
