@php
  $listingContext = $listingContext ?? 'schools';
  // (what the page shows now, the standard wording included, so the owner edits what visitors see)
  $admissionProfile = new \App\Support\SchoolProfilePresenter($institute);
@endphp
<form id="instAdmissionForm" class="sch-portal-form" novalidate
      data-store-url="{{ route($listingContext.'.inline.admission.update', $institute->slug) }}">
  @csrf
  <input type="hidden" name="_method" value="PUT">
  <div class="alert d-none mb-3" role="alert"></div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="mb-3">
        <label class="form-label" for="instAdmissionLead">Short intro</label>
        <textarea id="instAdmissionLead" name="lead" class="form-control" rows="3" maxlength="500" placeholder="e.g. Admissions are open for Nursery to Class IX for the 2026–27 session.">{{ $admissionProfile->admissionLead() }}</textarea>
        <p class="form-text mb-0">Shown under the Admission Info heading. Up to 500 characters.</p>
      </div>

      <div class="mb-3 mb-lg-0">
        <label class="form-label" for="instAdmissionHighlights">Key points <span class="text-secondary fw-normal">(one per line)</span></label>
        <textarea id="instAdmissionHighlights" name="highlights" class="form-control" rows="7" placeholder="Admission form available from 1 December&#10;Interaction for Nursery and KG&#10;Entrance test for Class VI and above">{{ implode("\n", $admissionProfile->admissionHighlights()) }}</textarea>
        <p class="form-text mb-0">The first two show on the page; all of them in the Read more popup. Up to 12 points.</p>
      </div>
    </div>

    <div class="col-lg-6">
      <label class="form-label" for="instAdmissionDetails">Full details <span class="text-secondary fw-normal">(in the Read more popup)</span></label>
      <textarea id="instAdmissionDetails" name="details" class="form-control" rows="12" maxlength="5000" placeholder="Steps, documents needed, age criteria, important dates…">{{ $admissionProfile->admissionDetailsText() }}</textarea>
      <p class="form-text mb-0">Up to 5,000 characters. Leave the intro, points and details empty to show no admission text.</p>
    </div>
  </div>

  <div class="mt-3">
    <button type="submit" class="btn btn-primary js-inst-submit-btn">
      <span class="btn-text"><i class="fa-solid fa-floppy-disk me-1"></i> Save changes</span>
      <span class="btn-loader d-none" aria-hidden="true"></span>
    </button>
  </div>
</form>
