<aside class="admin-sidebar">
    <div class="admin-sidebar-logo d-none d-lg-flex">
        <a href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater logo">
        </a>
    </div>

    <ul class="admin-sidebar-menu list-unstyled mb-0">
        <li>
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-border-all"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li><a href="#"><i class="fa-solid fa-map-location-dot"></i><span>Regions</span></a></li>
        <li><a href="#"><i class="fa-solid fa-gears"></i><span>Services</span></a></li>
        <li><a href="#"><i class="fa-solid fa-users"></i><span>Customers</span></a></li>
        <li><a href="#"><i class="fa-solid fa-route"></i><span>Route Planning</span></a></li>
        <li><a href="#"><i class="fa-solid fa-briefcase"></i><span>All Jobs</span></a></li>
        <li><a href="#"><i class="fa-solid fa-sliders"></i><span>Settings</span></a></li>
        <li>
            <a class="{{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}">
                <i class="fa-solid fa-user-gear"></i>
                <span>Profile</span>
            </a>
        </li>
        <li>
            <form method="POST" action="{{ route('logout') }}" class="w-100">
                @csrf
                <button type="submit" class="admin-sidebar-logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </li>
    </ul>
</aside>
