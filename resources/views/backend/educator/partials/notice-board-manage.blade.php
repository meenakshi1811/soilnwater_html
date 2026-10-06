<section class="edu-profile-section edu-notice-manage">
  <header class="edu-profile-section__head">
    <span class="edu-profile-section__icon edu-profile-section__icon--amber"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i></span>
    <div>
      <h3 class="edu-profile-section__title">Notice board</h3>
      <p class="edu-profile-section__desc">Publish notices for students and parents. Active notices appear on your public profile below the banner.</p>
    </div>
  </header>

  <form id="educatorNoticeForm" class="edu-notice-manage-form" novalidate>
    @csrf
    <div id="educatorNoticeAlert" class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-4">
        <label for="noticeTitle" class="form-label">Title *</label>
        <input type="text" id="noticeTitle" name="title" class="form-control" maxlength="120" placeholder="e.g. Summer batch registration">
      </div>
      <div class="col-md-4">
        <label for="noticeExpiresAt" class="form-label">Expiry date *</label>
        <input type="date" id="noticeExpiresAt" name="expires_at" class="form-control" min="{{ now()->toDateString() }}">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100" id="educatorNoticeSubmitBtn">
          <span class="btn-text"><i class="fa-solid fa-paper-plane me-1"></i> Publish notice</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
      <div class="col-12">
        <label for="noticeMessage" class="form-label">Notice message *</label>
        <textarea id="noticeMessage" name="message" class="form-control" rows="4" maxlength="5000" placeholder="Share exam dates, holiday schedules, batch updates, or important announcements."></textarea>
      </div>
      <div class="col-md-6">
        <label for="noticeImage" class="form-label">Notice image *</label>
        <input type="file" id="noticeImage" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
        <p class="form-text mb-0">JPG, PNG, or WebP up to 2 MB.</p>
      </div>
    </div>
  </form>

  <div class="edu-notice-manage-list" id="educatorNoticeList">
    @forelse($notices ?? [] as $notice)
      @include('backend.educator.partials.notice-board-item', ['notice' => $notice])
    @empty
      <p class="edu-notice-manage-empty mb-0" id="educatorNoticeEmpty">No notices published yet.</p>
    @endforelse
  </div>
</section>
