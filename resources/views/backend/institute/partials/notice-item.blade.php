<div class="sch-manage-item" data-item-id="{{ $notice->id }}">
  <div class="sch-manage-item__head">
    <div>
      <strong>{{ $notice->displayTitle() }}</strong>
      <div class="sch-manage-item__meta">
        Expires {{ $notice->expires_at?->format('d M Y') }}
        @if($notice->isExpired())
          <span class="badge text-bg-secondary ms-1">Expired</span>
        @else
          <span class="badge text-bg-success ms-1">Active</span>
        @endif
      </div>
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm js-inst-notice-delete" data-url="{{ route(($portalPrefix ?? auth()->user()?->portalRoutePrefix() ?? 'school').'.notices.destroy', $notice) }}" aria-label="Delete notice">
      <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
    </button>
  </div>
  <p class="mb-0 text-secondary">{{ $notice->message }}</p>
</div>
