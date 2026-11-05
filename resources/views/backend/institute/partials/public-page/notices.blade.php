@php
  $inlineEditor = $inlineEditor ?? false;
  $noticesFormOpen = $institute->notices->isNotEmpty();
@endphp
<section id="inst-section-notices" class="chart-card sch-portal-section edu-notice-manage inst-profile-section mb-4">
  <header class="inst-profile-section__head edu-profile-section__head mb-3">
    <div>
      <h3 class="edu-profile-section__title mb-1"><i class="fa-solid fa-bullhorn me-2"></i>Notice board</h3>
      <p class="edu-profile-section__desc mb-0">Publish notices for students and parents. Active notices appear on your public profile carousel, same as the teacher module.</p>
    </div>
    @unless($inlineEditor)
    <div class="form-check form-switch inst-profile-section__switch mb-0">
      <input class="form-check-input js-public-page-section-toggle" type="checkbox" role="switch" id="notices_section_enabled" data-section="notices" @checked($noticesFormOpen)>
      <label class="form-check-label" for="notices_section_enabled">Show form</label>
    </div>
    @endunless
  </header>

  <div class="inst-public-page-form-body {{ ($inlineEditor || $noticesFormOpen) ? '' : 'd-none' }}" data-section-body="notices">
  <form id="instNoticeForm" class="edu-notice-manage-form sch-portal-form" enctype="multipart/form-data" novalidate data-store-url="{{ $portalRoute('notices.store') }}">
    @csrf
    <div id="instNoticeFormAlert" class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-4">
        <label for="instNoticeTitle" class="form-label">Title *</label>
        <input type="text" id="instNoticeTitle" name="title" class="form-control js-section-field" data-section="notices" maxlength="120" placeholder="e.g. Annual day celebration">
      </div>
      <div class="col-md-4">
        <label for="instNoticeExpires" class="form-label">Expiry date *</label>
        <input type="date" id="instNoticeExpires" name="expires_at" class="form-control js-section-field" data-section="notices" min="{{ now()->toDateString() }}">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
          <span class="btn-text"><i class="fa-solid fa-paper-plane me-1"></i> Publish notice</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
      <div class="col-md-8">
        <label for="instNoticeMessage" class="form-label">Notice message *</label>
        <textarea id="instNoticeMessage" name="message" class="form-control js-section-field" data-section="notices" rows="4" maxlength="5000" placeholder="Share exam dates, holiday schedules, admission updates, or important announcements."></textarea>
      </div>
      <div class="col-md-4">
        <label for="instNoticeImage" class="form-label">Notice image *</label>
        <input type="file" id="instNoticeImage" name="image" class="form-control js-section-field" data-section="notices" accept="image/jpeg,image/png,image/webp">
        <p class="form-text mb-0">JPG, PNG, or WebP up to 2 MB. Shown on your public notice board.</p>
      </div>
    </div>
  </form>
  </div>

  <div class="edu-notice-manage-list sch-manage-list" id="instNoticeList" data-empty-text="No notices published yet.">
    @forelse($institute->notices as $notice)
      @include('backend.institute.partials.notice-item', ['notice' => $notice])
    @empty
      <p class="edu-notice-manage-empty sch-manage-empty mb-0" id="instNoticeEmpty">No notices published yet.</p>
    @endforelse
  </div>
</section>
