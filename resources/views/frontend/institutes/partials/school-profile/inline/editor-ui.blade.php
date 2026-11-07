{{-- Owner-only inline editor shell: edit-mode toggle, section toolbar, and the shared offcanvas.
     Rendered only when $canEditInline is true. --}}
@php
  $listingContext = $listingContext ?? 'schools';
  // Callers (e.g. the diary page) may pass their own section list; otherwise the full profile set.
  $inlineSections = $inlineSections ?? [
    'basics' => ['About & info', 'fa-circle-info'],
    'notices' => ['Notices', 'fa-bullhorn'],
    'courses' => ['Courses', 'fa-layer-group'],
    'classes' => ['Classes', 'fa-chalkboard'],
    'facilities' => ['Facilities', 'fa-building'],
    'gallery' => ['Gallery', 'fa-images'],
    'performers' => ['Results', 'fa-medal'],
    'achievements' => ['Achievements', 'fa-trophy'],
    'books' => ['Books', 'fa-book'],
    'jobs' => ['Jobs', 'fa-briefcase'],
    'fee' => ['Fee structure', 'fa-indian-rupee-sign'],
  ];
  $formUrlBase = route($listingContext.'.inline.form', ['slug' => $institute->slug, 'section' => '__SECTION__']);
  $sectionUrlBase = route($listingContext.'.inline.section', ['slug' => $institute->slug, 'section' => '__SECTION__']);
@endphp

<div
  id="schInlineEdit"
  data-form-url-base="{{ $formUrlBase }}"
  data-section-url-base="{{ $sectionUrlBase }}"
  data-csrf="{{ csrf_token() }}"
>
  {{-- Floating toggle --}}
  <button type="button" class="sch-inline-fab js-sch-inline-toggle" aria-pressed="false">
    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
    <span class="js-sch-inline-toggle-label">Edit page</span>
  </button>

  {{-- Section toolbar (reaches every section, including empty ones) --}}
  <div class="sch-inline-toolbar" role="toolbar" aria-label="Edit sections">
    <span class="sch-inline-toolbar__label">Edit a section:</span>
    @foreach($inlineSections as $key => [$label, $icon])
      <button type="button" class="sch-inline-toolbar__btn js-sch-inline-edit" data-inline-edit="{{ $key }}">
        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }}
      </button>
    @endforeach
  </div>
</div>

{{-- The shared popup the forms load into: wide, in the middle of the screen --}}
<div class="modal fade sch-inline-modal" tabindex="-1" id="schInlineEditor" aria-labelledby="schInlineEditorLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="schInlineEditorLabel">Edit section</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="schInlineEditorBody">
        <div class="text-center text-muted py-5 js-sch-inline-loading">
          <span class="spinner-border spinner-border-sm me-2"></span> Loading…
        </div>
      </div>
    </div>
  </div>
</div>
