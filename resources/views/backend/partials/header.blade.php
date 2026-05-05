@php
    $isGeneralUser = auth()->user()->isGeneralUser();
    $dashboardUrl = $isGeneralUser ? route('user.dashboard') : route('admin.dashboard');
    $dashboardActive = $isGeneralUser
        ? request()->routeIs('user.dashboard')
        : request()->routeIs('admin.dashboard');
    $profileUrl = $isGeneralUser ? route('user.profile.edit') : route('admin.profile.edit');
    $profileActive = $isGeneralUser
        ? request()->routeIs('user.profile.*')
        : request()->routeIs('admin.profile.*');
    $panelTitle = $isGeneralUser ? 'User Dashboard' : 'Admin Control Panel';
@endphp
<header class="admin-header">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="admin-header-title-wrap">
            <h1 class="admin-header-title mb-0">{{ $panelTitle }}</h1>
            <p class="mb-0">Welcome, {{ auth()->user()->name }}</p>
        </div>

        <nav class="admin-nav d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <a class="btn btn-sm admin-icon-link" href="{{ route('frontend.index') }}" title="Go to Index Page">
                <i class="fa-solid fa-house"></i>
                <span class="d-none d-md-inline">Index</span>
            </a>
            <a class="btn btn-sm admin-link {{ $dashboardActive ? 'active' : '' }}" href="{{ $dashboardUrl }}">Dashboard</a>
            @if($isGeneralUser || auth()->user()->isAdmin())
                <a class="btn btn-sm admin-header-action-offer" href="{{ route('post-offer') }}">Post Offer</a>
            @endif
            @if($isGeneralUser || auth()->user()->isAdmin())
                <a class="btn btn-sm admin-header-action-ad" href="{{ route('ads.create.size') }}">Post Ad</a>
            @endif
            <a class="btn btn-sm admin-link {{ $profileActive ? 'active' : '' }}" href="{{ $profileUrl }}">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm admin-logout">Logout</button>
            </form>
        </nav>
    </div>
</header>
