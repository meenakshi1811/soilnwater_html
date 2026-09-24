<div class="modal fade" id="schAdmissionModal" tabindex="-1" aria-labelledby="schAdmissionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable sch-admission-modal">
    <div class="modal-content sch-admission-modal__content">
      <div class="modal-header sch-admission-modal__header">
        <div>
          <span class="sch-admission-modal__eyebrow"><i class="fa-solid fa-door-open" aria-hidden="true"></i> Admission</span>
          <h5 class="modal-title sch-admission-modal__title" id="schAdmissionModalLabel">Admission information</h5>
          <p class="sch-admission-modal__lead mb-0">{{ $profile->admissionLead() }}</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body sch-admission-modal__body">
        @if($profile->admissionHighlights() !== [])
          <ul class="sch-check-list sch-admission-modal__list">
            @foreach($profile->admissionHighlights() as $point)
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $point }}</li>
            @endforeach
          </ul>
        @endif
        <div class="sch-admission-modal__details">{!! nl2br(e($profile->admissionDetailsText())) !!}</div>
      </div>
    </div>
  </div>
</div>
