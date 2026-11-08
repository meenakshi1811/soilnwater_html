<form class="edu-inline-form" novalidate data-save-url="{{ route('educator.inline.update', [$educator->slug, 'about']) }}">
  @csrf
  <input type="hidden" name="_method" value="PUT">
  <div class="alert d-none mb-3" role="alert"></div>

  <div class="mb-3">
    <label class="form-label" for="eduInlineAbout">About me</label>
    <textarea id="eduInlineAbout" name="about" class="form-control" rows="9" placeholder="Your background, how you teach, what students achieve with you…">{{ $educator->about }}</textarea>
    <p class="form-text mb-0">The first lines also show as the short line under your headline.</p>
  </div>
  <div>
    <label class="form-label" for="eduInlineMethod">Teaching method</label>
    <input type="text" id="eduInlineMethod" name="teaching_method" class="form-control" maxlength="255" value="{{ $educator->teaching_method }}" placeholder="e.g. Concept first, then practice with past papers">
  </div>

  @include('frontend.educator.partials.inline.save-button')
</form>
