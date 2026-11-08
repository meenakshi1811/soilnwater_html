<form class="edu-inline-form" novalidate data-save-url="{{ route('educator.inline.update', [$educator->slug, 'achievements']) }}">
  @csrf
  <input type="hidden" name="_method" value="PUT">
  <div class="alert d-none mb-3" role="alert"></div>

  <div class="row g-3">
    <div class="col-lg-6">
      <label class="form-label" for="eduInlineAchievements">Achievements <span class="text-secondary fw-normal">(one per line)</span></label>
      <textarea id="eduInlineAchievements" name="achievements" class="form-control" rows="8" placeholder="Best Teacher Award 2023&#10;100% board results in Physics">{{ implode("\n", $educator->achievements ?? []) }}</textarea>
    </div>
    <div class="col-lg-6">
      <label class="form-label" for="eduInlineCertifications">Certifications <span class="text-secondary fw-normal">(one per line)</span></label>
      <textarea id="eduInlineCertifications" name="certifications" class="form-control" rows="8" placeholder="CTET qualified&#10;B.Ed.">{{ implode("\n", $educator->certifications ?? []) }}</textarea>
    </div>
  </div>

  @include('frontend.educator.partials.inline.save-button')
</form>
