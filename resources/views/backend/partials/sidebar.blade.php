@php
    $emsModules = \App\Support\ModulePermissions::modules();
    $isGeneralUser = auth()->user()->isGeneralUser();
    $isAdmin = auth()->user()->isAdmin();
    $isEmployee = auth()->user()->isEmployee();
    $canAccessOffers = $isAdmin || $isGeneralUser || auth()->user()->canModule('vendors', 'read');

    if ($isGeneralUser) {
        $dashboardUrl = route('user.dashboard');
        $dashboardActive = request()->routeIs('user.dashboard');
    } elseif ($isAdmin) {
        $dashboardUrl = route('admin.dashboard');
        $dashboardActive = request()->routeIs('admin.dashboard');
    } else {
        $slug = auth()->user()->firstReadableModuleSlug();
        $dashboardUrl = $slug ? route('modules.show', $slug) : route('home');
        $dashboardActive = request()->routeIs('modules.show');
    }
@endphp
<aside class="admin-sidebar">
    <div class="admin-sidebar-logo d-none d-lg-flex">
        <a href="{{ route('frontend.index') }}" title="Go to Index Page">
            <img src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater logo">
        </a>
    </div>

    <ul class="admin-sidebar-menu list-unstyled mb-0">
        <li>
            <a class="{{ $dashboardActive ? 'active' : '' }}" href="{{ $dashboardUrl }}">
                <i class="fa-solid fa-border-all"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @if($isAdmin)
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
            <li>
                <a class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                    <i class="fa-solid fa-folder-tree"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('admin.terms-and-conditions.*') ? 'active' : '' }}" href="{{ route('admin.terms-and-conditions.index') }}">
                    <i class="fa-solid fa-file-contract"></i>
                    <span>Terms &amp; Conditions</span>
                </a>
            </li>
            <hr>
            <li>
                <a class="{{ request()->routeIs('offers.*') ? 'active' : '' }}" href="{{ route('offers.index') }}">
                    <i class="fa-solid fa-tags"></i>
                    <span>Offers &amp; Discounts</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('admin.ads.templates.*') ? 'active' : '' }}" href="{{ route('admin.ads.templates.index') }}">
                    <i class="fa-solid fa-palette"></i>
                    <span>Ad Templates</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('admin.ads.submissions.*') ? 'active' : '' }}" href="{{ route('admin.ads.submissions.index') }}">
                    <i class="fa-solid fa-inbox"></i>
                    <span>Ad Submissions</span>
                </a>
            </li>
        @endif

         @foreach($emsModules as $slug => $label)
            @if($isAdmin || auth()->user()->can($slug.'.read'))
                <li>
                    <a class="{{ request()->routeIs('modules.show') && request()->route('module') === $slug ? 'active' : '' }}" href="{{ route('modules.show', $slug) }}">
                        <i class="fa-solid fa-cube"></i><span>{{ $label }}</span>
                    </a>
                </li>
            @endif
        @endforeach

        @if($isAdmin)
            <li>
                <a class="{{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Profile</span>
                </a>
            </li>
        @elseif($isGeneralUser)
            @if($canAccessOffers)
            <li>
                <a class="{{ request()->routeIs('offers.*') ? 'active' : '' }}" href="{{ route('offers.index') }}">
                    <i class="fa-solid fa-tags"></i>
                    <span>Offers &amp; Discounts</span>
                </a>
            </li>
            @endif
            <li>
                <a class="{{ request()->routeIs('ads.*') ? 'active' : '' }}" href="{{ route('ads.index') }}">
                    <i class="fa-solid fa-rectangle-ad"></i>
                    <span>My Ads</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('user.profile.*') ? 'active' : '' }}" href="{{ route('user.profile.edit') }}">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Profile</span>
                </a>
            </li>
        @elseif($isEmployee)
            @if($canAccessOffers)
                <li>
                    <a class="{{ request()->routeIs('offers.*') ? 'active' : '' }}" href="{{ route('offers.index') }}">
                        <i class="fa-solid fa-tags"></i>
                        <span>Offers &amp; Discounts</span>
                    </a>
                </li>
            @endif
            <li>
                <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                    <i class="fa-solid fa-house"></i>
                    <span>Home</span>
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
