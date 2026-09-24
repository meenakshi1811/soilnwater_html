<div class="modal fade" id="schArticlesModal" tabindex="-1" aria-labelledby="schArticlesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable sch-articles-modal">
    <div class="modal-content sch-articles-modal__content">
      <div class="modal-header sch-articles-modal__header">
        <div>
          <span class="sch-articles-modal__eyebrow"><i class="fa-solid fa-newspaper" aria-hidden="true"></i> Articles &amp; News</span>
          <h5 class="modal-title sch-articles-modal__title" id="schArticlesModalLabel">More articles</h5>
          <p class="sch-articles-modal__lead mb-0">Browse earlier announcements and updates from this institution.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body sch-articles-modal__body">
        <div class="sch-articles-modal__grid">
          @foreach($articles as $news)
            @include('frontend.institutes.partials.school-profile.news-card', ['news' => $news, 'inModal' => true])
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
