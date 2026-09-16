<div class="edu-notice-manage-item" data-notice-id="{{ $notice->id }}">
  <div class="edu-notice-manage-item__head">
    <div>
      <strong>{{ $notice->displayTitle() }}</strong>
      <div class="edu-notice-manage-item__meta">
        Expires {{ $notice->expires_at?->format('d M Y') }}
        @if($notice->isExpired())
          <span class="badge text-bg-secondary ms-1">Expired</span>
        @else
          <span class="badge text-bg-success ms-1">Active</span>
        @endif
      </div>
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm js-edu-notice-delete" data-url="{{ route('educator.notices.destroy', $notice) }}" aria-label="Delete notice">
      <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
    </button>
  </div>
  <p class="edu-notice-manage-item__message mb-0">{{ $notice->message }}</p>
</div>
