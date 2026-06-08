@php
    $user = auth()->user();
    $isGeneralUser = $user->isGeneralUser();
    $isVendor = $user->isVendor();
    $isConsultant = $user->isConsultant();
    $isServiceProvider = $user->isServiceProvider();
    $dashboardUrl = $isGeneralUser
        ? route('user.dashboard')
        : ($isVendor ? route('vendor.dashboard') : ($isConsultant ? route('consultant.dashboard') : ($isServiceProvider ? route('service_provider.dashboard') : route('admin.dashboard'))));
    $dashboardActive = $isGeneralUser
        ? request()->routeIs('user.dashboard')
        : ($isVendor ? request()->routeIs('vendor.dashboard') : ($isConsultant ? request()->routeIs('consultant.dashboard') : ($isServiceProvider ? request()->routeIs('service_provider.dashboard') : request()->routeIs('admin.dashboard'))));
    $profileUrl = $isGeneralUser
        ? route('user.profile.edit')
        : ($isVendor ? route('vendor.profile.edit') : ($isConsultant ? route('consultant.profile.edit') : ($isServiceProvider ? route('service_provider.profile.edit') : route('admin.profile.edit'))));
    $profileActive = $isGeneralUser
        ? request()->routeIs('user.profile.*')
        : ($isVendor ? request()->routeIs('vendor.profile.*') : ($isConsultant ? request()->routeIs('consultant.profile.*') : ($isServiceProvider ? request()->routeIs('service_provider.profile.*') : request()->routeIs('admin.profile.*'))));
    $panelTitle = $isGeneralUser
        ? 'User Dashboard'
        : ($isVendor ? 'Vendor Dashboard' : ($isConsultant ? 'Consultant Dashboard' : ($isServiceProvider ? 'Service Dashboard' : 'Admin Control Panel')));
    $notifications = $user->notifications()->latest()->limit(8)->get();
    $unreadNotificationCount = $user->unreadNotifications()->count();
@endphp
<header class="admin-header">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="admin-header-title-wrap d-flex align-items-center gap-2">
            @if($user->profile_image)
                <img src="{{ asset($user->profile_image) }}" alt="{{ $user->name }}" width="44" height="44" class="rounded-circle object-fit-cover">
            @endif
            <div>
            <h1 class="admin-header-title mb-0">{{ $panelTitle }}</h1>
            <p class="mb-0">Welcome, {{ $user->name }}</p>
            </div>
        </div>

        <nav class="admin-nav d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <a class="btn btn-sm admin-icon-link" href="{{ route('frontend.index') }}" title="Go to Index Page">
                <i class="fa-solid fa-house"></i>
                <span class="d-none d-md-inline">Home</span>
            </a>
            <a class="btn btn-sm admin-link {{ $dashboardActive ? 'active' : '' }}" href="{{ $dashboardUrl }}">Dashboard</a>
            @if($isGeneralUser || $user->isAdmin() || $user->isVendor() || $user->isConsultant() || $user->isServiceProvider())
                <a class="btn btn-sm admin-header-action-offer" href="{{ route('post-offer') }}">Post Offer</a>
                <a class="btn btn-sm admin-header-action-ad" href="{{ route('ads.create.size') }}">Post Ad</a>
            @endif
            <div class="dropdown admin-notification-dropdown">
                <button class="btn btn-sm admin-icon-link admin-notification-button" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    @if($unreadNotificationCount > 0)
                        <span class="admin-notification-dot" aria-label="{{ $unreadNotificationCount }} unread notifications"></span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end admin-notification-menu p-0">
                    <div class="admin-notification-menu-header d-flex align-items-center justify-content-between gap-3">
                        <strong>Notifications</strong>
                        @if($unreadNotificationCount > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                            </form>
                        @endif
                    </div>
                    <div class="admin-notification-list">
                        @forelse($notifications as $notification)
                            @php
                                $notificationTitle = $notification->data['title'] ?? 'Notification';
                                $notificationMessage = $notification->data['message'] ?? '';
                            @endphp
                            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="m-0">
                                @csrf
                                <button type="submit" class="admin-notification-item {{ $notification->read_at ? '' : 'unread' }}">
                                    <span class="admin-notification-item-title">{{ $notificationTitle }}</span>
                                    <span class="admin-notification-item-message">{{ $notificationMessage }}</span>
                                    <span class="admin-notification-item-time">{{ $notification->created_at?->diffForHumans() }}</span>
                                </button>
                            </form>
                        @empty
                            <div class="admin-notification-empty">No notifications yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <a class="btn btn-sm admin-link {{ $profileActive ? 'active' : '' }}" href="{{ $profileUrl }}">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm admin-logout">Logout</button>
            </form>
        </nav>
    </div>
</header>
