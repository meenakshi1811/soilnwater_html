@php
    $isGeneralUser = auth()->user()->isGeneralUser();
    $isVendor = auth()->user()->isVendor();
    $isConsultant = auth()->user()->isConsultant();
    $isServiceProvider = auth()->user()->isServiceProvider();
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
        : ($isVendor ? 'Vendor Dashboard' : ($isConsultant ? 'Consultant Dashboard' : ($isServiceProvider ? 'Service Provider Dashboard' : 'Admin Control Panel')));
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
                <span class="d-none d-md-inline">Home</span>
            </a>
            <a class="btn btn-sm admin-link {{ $dashboardActive ? 'active' : '' }}" href="{{ $dashboardUrl }}">Dashboard</a>
            @if($isGeneralUser || auth()->user()->isAdmin() || auth()->user()->isVendor() || auth()->user()->isConsultant() || auth()->user()->isServiceProvider())
                <a class="btn btn-sm admin-header-action-offer" href="{{ route('post-offer') }}">Post Offer</a>
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
