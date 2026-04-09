@php
    $emsModules = \App\Support\ModulePermissions::modules();
@endphp
<aside class="admin-sidebar">
    <div class="admin-sidebar-logo d-none d-lg-flex">
        <a href="{{ route('frontend.index') }}" title="Go to Index Page">
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
        @if(auth()->user()->isAdmin())
            <li class="sidebar-section-label">Employee system</li>
            <li>
                <a class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Roles &amp; Permissions</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Employees</span>
                </a>
            </li>
            <hr>
        @endif

         @foreach($emsModules as $slug => $label)
            @if(auth()->user()->isAdmin() || auth()->user()->can($slug.'.read'))
                <li>
                    <a class="{{ request()->routeIs('modules.show') && request()->route('module') === $slug ? 'active' : '' }}" href="{{ route('modules.show', $slug) }}">
                        <i class="fa-solid fa-cube"></i><span>{{ $label }}</span>
                    </a>
                </li>
            @endif
        @endforeach
       
        
        @if(auth()->user()->isAdmin())
            <li>
                <a class="{{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Profile</span>
                </a>
            </li>
        @else
            <li>
                <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                    <i class="fa-solid fa-house"></i>
                    <span>Home</span>
                </a>
            </li>
        @endif
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
