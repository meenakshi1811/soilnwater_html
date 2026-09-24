<div class="modal fade" id="schJobModal" tabindex="-1" aria-labelledby="schJobModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable sch-job-modal">
    <div class="modal-content sch-job-modal__content">
      <div class="modal-header sch-job-modal__header border-0">
        <div>
          <span class="sch-job-modal__eyebrow"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Job opening</span>
          <h5 class="modal-title sch-job-modal__title" id="schJobModalLabel">Role details</h5>
          <p class="sch-job-modal__meta mb-0" id="schJobModalMeta"></p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body sch-job-modal__body">
        <div class="sch-job-modal__chips" id="schJobModalChips"></div>
        <div class="sch-job-modal__section">
          <h6>About this role</h6>
          <div id="schJobModalDescription" class="sch-job-modal__text"></div>
        </div>
        <div class="sch-job-modal__section d-none" id="schJobModalRequirementsWrap">
          <h6>Requirements</h6>
          <div id="schJobModalRequirements" class="sch-job-modal__text"></div>
        </div>
        @guest
          <div class="sch-job-modal__apply sch-job-modal__apply--guest">
            <p class="mb-0">Please <a href="{{ route('login') }}">log in</a> to apply for this position.</p>
          </div>
        @else
          <form id="schJobApplyForm" class="sch-job-modal__apply" novalidate>
            @csrf
            <label class="form-label" for="sch_job_cover_message">Cover message <span class="text-secondary">(optional)</span></label>
            <textarea class="form-control" id="sch_job_cover_message" name="cover_message" rows="4" maxlength="5000" placeholder="Briefly share why you are a good fit for this role…"></textarea>
            <div class="sch-job-modal__apply-actions">
              <button type="submit" class="sch-btn sch-btn-primary js-sch-job-apply-btn">
                <span class="js-job-apply-text">Apply now</span>
                <span class="js-job-apply-sending d-none">Submitting…</span>
              </button>
              <p class="sch-job-modal__applied-note d-none mb-0" id="schJobAppliedNote">
                <i class="fa-solid fa-circle-check text-success" aria-hidden="true"></i> You have already applied for this role.
              </p>
            </div>
          </form>
        @endguest
      </div>
    </div>
  </div>
</div>
