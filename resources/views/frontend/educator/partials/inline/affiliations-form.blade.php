{{-- The schools, colleges and institutes the teacher works with (the portal's affiliation endpoints) --}}
<div class="row g-4">
  <div class="col-lg-6">
    <h6 class="fw-bold mb-2">Add a school, college or institute</h6>
    <div class="js-edu-affiliation-add" data-search-url="{{ route('educator.affiliations.search') }}" data-store-url="{{ route('educator.affiliations.store') }}">
      <div class="row g-2 mb-2">
        <div class="col-sm-6">
          <input type="text" class="form-control form-control-sm js-edu-affiliation-role" maxlength="255" placeholder="Your role, e.g. PGT Physics (optional)" aria-label="Your role">
        </div>
        <div class="col-sm-6">
          <input type="text" class="form-control form-control-sm js-edu-affiliation-subject" maxlength="255" placeholder="Subject (optional)" aria-label="Subject">
        </div>
      </div>
      <input type="search" class="form-control js-edu-affiliation-search" placeholder="Search by name or city…" aria-label="Search schools, colleges and institutes">
      <div class="edu-inline-list mt-2 js-edu-affiliation-results">
        <p class="text-secondary small mb-0">Type at least 2 letters to search the schools, colleges and institutes on SoilnWater.</p>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <h6 class="fw-bold mb-2">Shown on your page ({{ $educator->instituteAffiliations->count() }})</h6>
    <div class="edu-inline-list">
      @forelse($educator->instituteAffiliations as $affiliation)
        <div class="edu-inline-list__item">
          <div class="flex-grow-1 min-w-0">
            <strong class="d-block text-truncate">{{ $affiliation->institute?->displayName() ?: 'Removed listing' }}</strong>
            <span class="small text-secondary">{{ collect([\App\Support\SchoolInstituteHelper::label($affiliation->institute?->user?->role), $affiliation->role_title, $affiliation->subject, $affiliation->institute?->city])->filter()->implode(' · ') }}</span>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger js-edu-inline-delete" data-url="{{ route('educator.affiliations.destroy', $affiliation) }}" data-confirm="Remove this school / institute from your page?">Remove</button>
        </div>
      @empty
        <p class="text-secondary small mb-0">None yet. Only approved listings show on your page.</p>
      @endforelse
    </div>
  </div>
</div>
