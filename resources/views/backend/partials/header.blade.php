<header class="admin-header">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3">
        <div class="admin-header-title-wrap">
            <h1 class="admin-header-title mb-0">Admin Control Panel</h1>
            <p class="mb-0">Welcome, {{ auth()->user()->name }}</p>
        </div>

        <nav class="admin-nav d-flex align-items-center gap-2">
            <a class="btn btn-sm admin-icon-link" href="{{ route('frontend.index') }}" title="Go to Index Page">
                <i class="fa-solid fa-house"></i>
                <span class="d-none d-md-inline">Index</span>
            </a>
            <a class="btn btn-sm admin-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="btn btn-sm admin-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm admin-logout">Logout</button>
            </form>
        </nav>
    </div>
</header>
