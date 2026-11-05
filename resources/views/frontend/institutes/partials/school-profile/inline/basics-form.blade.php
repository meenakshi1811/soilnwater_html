@php
  $listingContext = $listingContext ?? 'schools';
@endphp
<form id="instBasicsForm" class="sch-portal-form" novalidate
      data-store-url="{{ route($listingContext.'.inline.basics.update', $institute->slug) }}">
  @csrf
  <input type="hidden" name="_method" value="PUT">
  <div class="alert d-none mb-3" role="alert"></div>

  <div class="mb-3">
    <label class="form-label" for="instBasicsTagline">Tagline *</label>
    <input type="text" id="instBasicsTagline" name="tagline" class="form-control" maxlength="255" value="{{ $institute->tagline }}" placeholder="A short line shown under your name">
  </div>

  <div class="mb-3">
    <label class="form-label" for="instBasicsAbout">About *</label>
    <textarea id="instBasicsAbout" name="about" class="form-control" rows="10" minlength="10" placeholder="Introduce your school — history, values, highlights…">{{ $institute->about }}</textarea>
    <p class="form-text mb-0">The first lines appear as the short intro on your public page.</p>
  </div>

  <button type="submit" class="btn btn-primary js-inst-submit-btn">
    <span class="btn-text"><i class="fa-solid fa-floppy-disk me-1"></i> Save changes</span>
    <span class="btn-loader d-none" aria-hidden="true"></span>
  </button>
</form>
