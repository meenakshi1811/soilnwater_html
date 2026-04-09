<header class="admin-header">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater logo">
        </a>

        <nav class="admin-nav d-flex align-items-center gap-2">
            <a class="btn btn-sm admin-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="btn btn-sm admin-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm admin-logout">Logout</button>
            </form>
        </nav>
    </div>
</header>
