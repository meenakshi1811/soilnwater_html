@extends('backend.layouts.app')

@section('title', 'Notifications')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page notifications-page">
    <div class="ems-hero mb-4 d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div>
            <p class="ems-kicker mb-1">Inbox</p>
            <h2 class="admin-title mb-1">All Notifications</h2>
            <p class="mb-0 text-secondary" id="notificationsUnreadSummary">
                @if($unreadCount > 0)
                    You have <span id="notificationsUnreadCount">{{ $unreadCount }}</span> unread {{ \Illuminate\Support\Str::plural('notification', $unreadCount) }}.
                @else
                    You’re all caught up. No unread notifications.
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2" id="notificationsMarkAllWrap">
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary ems-btn-primary">
                        <i class="fa-solid fa-check-double me-1"></i> Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="chart-card notifications-panel p-0 overflow-hidden" id="notificationsList">
        @forelse($notifications as $notification)
            @php
                $title = $notification->data['title'] ?? 'Notification';
                $message = $notification->data['message'] ?? '';
                $isUnread = is_null($notification->read_at);
            @endphp
            <div class="notifications-row {{ $isUnread ? 'is-unread' : '' }}" data-notification-id="{{ $notification->id }}" data-unread="{{ $isUnread ? '1' : '0' }}">
                <form method="POST" action="{{ route('notifications.read', $notification) }}" class="notifications-row-main m-0">
                    @csrf
                    <button type="submit" class="notifications-row-btn">
                        <span class="notifications-row-status" aria-hidden="true"></span>
                        <span class="notifications-row-body">
                            <span class="notifications-row-title">{{ $title }}</span>
                            @if(filled($message))
                                <span class="notifications-row-message">{{ $message }}</span>
                            @endif
                            <span class="notifications-row-meta">
                                <i class="fa-regular fa-clock"></i>
                                {{ $notification->created_at?->diffForHumans() }}
                                @if($notification->created_at)
                                    <span class="notifications-row-dot">•</span>
                                    {{ $notification->created_at->format('d M Y, h:i A') }}
                                @endif
                            </span>
                        </span>
                    </button>
                </form>

                <div class="notifications-row-actions">
                    <form method="POST" action="{{ route('notifications.read', $notification) }}" class="m-0">
                        @csrf
                        <button type="submit" class="notifications-action-btn notifications-open-btn" title="Open notification" aria-label="Open notification">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                    <button type="button"
                        class="notifications-action-btn notifications-delete-btn js-delete-notification"
                        title="Delete notification"
                        aria-label="Delete notification"
                        data-url="{{ route('notifications.destroy', $notification) }}">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>
        @empty
            <div class="notifications-empty" id="notificationsEmptyState">
                <div class="notifications-empty-icon">
                    <i class="fa-regular fa-bell-slash"></i>
                </div>
                <h3 class="mb-1">No notifications yet</h3>
                <p class="mb-0 text-secondary">When something needs your attention, it will show up here.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 4000
    };

    var unreadCount = {{ (int) $unreadCount }};

    function updateUnreadSummary() {
        var $summary = $('#notificationsUnreadSummary');
        var $markAll = $('#notificationsMarkAllWrap');

        if (unreadCount > 0) {
            $summary.html(
                'You have <span id="notificationsUnreadCount">' + unreadCount + '</span> unread notification' +
                (unreadCount === 1 ? '' : 's') + '.'
            );
            if (!$markAll.find('form').length) {
                $markAll.html(
                    '<form method="POST" action="{{ route('notifications.read-all') }}">' +
                        '@csrf' +
                        '<button type="submit" class="btn btn-primary ems-btn-primary">' +
                            '<i class="fa-solid fa-check-double me-1"></i> Mark all as read' +
                        '</button>' +
                    '</form>'
                );
            }
        } else {
            $summary.text('You’re all caught up. No unread notifications.');
            $markAll.empty();
        }
    }

    function deleteNotification($button, url, $row) {
        $button.prop('disabled', true);

        $.ajax({
            url: url,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        })
            .done(function (response) {
                var wasUnread = $row.data('unread') === 1 || $row.data('unread') === '1';

                $row.fadeOut(180, function () {
                    $row.remove();

                    if (wasUnread && unreadCount > 0) {
                        unreadCount -= 1;
                        updateUnreadSummary();
                    }

                    if (!$('#notificationsList .notifications-row').length) {
                        $('#notificationsList').html(
                            '<div class="notifications-empty" id="notificationsEmptyState">' +
                                '<div class="notifications-empty-icon"><i class="fa-regular fa-bell-slash"></i></div>' +
                                '<h3 class="mb-1">No notifications yet</h3>' +
                                '<p class="mb-0 text-secondary">When something needs your attention, it will show up here.</p>' +
                            '</div>'
                        );
                    }
                });

                FormHelper.showToast('success', response.message || 'Notification deleted.');
            })
            .fail(function (xhr) {
                $button.prop('disabled', false);
                FormHelper.showToast('danger', xhr.responseJSON?.message || 'Unable to delete notification.');
            });
    }

    $(document).on('click', '.js-delete-notification', function () {
        var $button = $(this);
        var url = $button.data('url');
        var $row = $button.closest('.notifications-row');

        if (!url || $button.prop('disabled')) {
            return;
        }

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Delete notification?',
                text: 'This notification will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'
            }).then(function (result) {
                if (result.isConfirmed) {
                    deleteNotification($button, url, $row);
                }
            });
            return;
        }

        if (window.confirm('Delete this notification permanently?')) {
            deleteNotification($button, url, $row);
        }
    });
})(window.jQuery);
</script>
@endpush
