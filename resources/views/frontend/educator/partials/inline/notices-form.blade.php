{{-- Add a notice (the portal's notices endpoint) and remove one; the list reloads after each change --}}
<div class="row g-4">
  <div class="col-lg-6">
    <h6 class="fw-bold mb-2">Add a notice</h6>
    <form class="edu-inline-form" novalidate enctype="multipart/form-data" data-store-url="{{ route('educator.notices.store') }}">
      @csrf
      <div class="alert d-none mb-3" role="alert"></div>
      <div class="mb-2">
        <label class="form-label" for="eduNoticeTitle">Title *</label>
        <input type="text" id="eduNoticeTitle" name="title" class="form-control" maxlength="120" required>
      </div>
      <div class="mb-2">
        <label class="form-label" for="eduNoticeMessage">Message *</label>
        <textarea id="eduNoticeMessage" name="message" class="form-control" rows="4" maxlength="5000" required></textarea>
      </div>
      <div class="row g-2">
        <div class="col-sm-6">
          <label class="form-label" for="eduNoticeExpires">Show until *</label>
          <input type="date" id="eduNoticeExpires" name="expires_at" class="form-control" min="{{ now()->toDateString() }}" required>
        </div>
        <div class="col-sm-6">
          <label class="form-label" for="eduNoticeImage">Image *</label>
          <input type="file" id="eduNoticeImage" name="image" class="form-control" accept="image/jpeg,image/png,image/webp" required>
        </div>
      </div>
      @include('frontend.educator.partials.inline.save-button', ['label' => 'Publish notice'])
    </form>
  </div>

  <div class="col-lg-6">
    <h6 class="fw-bold mb-2">Your notices ({{ $educator->notices->count() }})</h6>
    <div class="edu-inline-list">
      @forelse($educator->notices as $notice)
        <div class="edu-inline-list__item">
          @if($notice->imageUrl())
            <img src="{{ $notice->imageUrl() }}" alt="" class="edu-inline-list__thumb">
          @endif
          <div class="flex-grow-1 min-w-0">
            <strong class="d-block text-truncate">{{ $notice->displayTitle() }}</strong>
            <span class="small {{ $notice->isExpired() ? 'text-danger' : 'text-secondary' }}">
              {{ $notice->isExpired() ? 'Expired' : 'Shown until' }} {{ $notice->expires_at?->format('j M Y') }}
            </span>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger js-edu-inline-delete" data-url="{{ route('educator.notices.destroy', $notice) }}" data-confirm="Remove this notice from your page?">Remove</button>
        </div>
      @empty
        <p class="text-secondary small mb-0">No notices yet.</p>
      @endforelse
    </div>
  </div>
</div>
