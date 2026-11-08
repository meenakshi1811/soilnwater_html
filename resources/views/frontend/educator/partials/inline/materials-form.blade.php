{{-- Courses and study material are uploaded on the Study Material pages (files, type, class…) --}}
<div class="edu-inline-link-panel">
  <span class="edu-inline-link-panel__icon"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
  <div>
    <h6 class="fw-bold mb-1">Courses &amp; study material</h6>
    <p class="text-secondary mb-3">Notes, question papers, worksheets and courses are uploaded with their files on your Study Material pages. Approved ones show here under Courses and Study Material.</p>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('educator.materials.create') }}" class="btn btn-primary" target="_blank" rel="noopener">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> Upload study material
      </a>
      <a href="{{ route('educator.materials.index') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
        <i class="fa-solid fa-list me-1" aria-hidden="true"></i> Manage your materials
      </a>
    </div>
  </div>
</div>
