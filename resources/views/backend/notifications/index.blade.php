@extends('backend.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="admin-panel ems-page notifications-page">
    <div class="ems-hero mb-4 d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div>
            <p class="ems-kicker mb-1">Inbox</p>
            <h2 class="admin-title mb-1">All Notifications</h2>
            <p class="mb-0 text-secondary">
                @if($unreadCount > 0)
                    You have {{ $unreadCount }} unread {{ \Illuminate\Support\Str::plural('notification', $unreadCount) }}.
                @else
                    You’re all caught up. No unread notifications.
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
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

    <div class="chart-card notifications-panel p-0 overflow-hidden">
        @forelse($notifications as $notification)
            @php
                $title = $notification->data['title'] ?? 'Notification';
                $message = $notification->data['message'] ?? '';
                $isUnread = is_null($notification->read_at);
            @endphp
            <div class="notifications-row {{ $isUnread ? 'is-unread' : '' }}">
                <form method="POST" action="{{ route('notifications.read', $notification) }}" class="notifications-row-main m-0">
                    @csrf
                    <button type="submit" class="notifications-row-btn">
                        <span class="notifications-row-status" aria-hidden="true"></span>
                        <span class="notifications-row-body">
                            <span class="notifications-row-title">
                                {{ $title }}
                                @if($isUnread)
                                    <span class="notifications-unread-pill">Unread</span>
                                @endif
                            </span>
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
                        <span class="notifications-row-open" title="Open">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </button>
                </form>

                <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="notifications-row-delete m-0"
                    onsubmit="return confirm('Delete this notification permanently?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="notifications-delete-btn" title="Delete notification" aria-label="Delete notification">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </form>
            </div>
        @empty
            <div class="notifications-empty">
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
