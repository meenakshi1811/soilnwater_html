@php
    $emsModules = \App\Support\ModulePermissions::modules();
    $isGeneralUser = auth()->user()->isGeneralUser();
    $isAdmin = auth()->user()->isAdmin();
    $isEmployee = auth()->user()->isEmployee();
    $isVendor = auth()->user()->isVendor();
    $vendorApproved = $isVendor && auth()->user()->vendor?->isApproved();
    $canAccessOffers = $isAdmin || $isGeneralUser || auth()->user()->canModule('vendors', 'read');
    $offersMenuActive = request()->routeIs('offers.*');
    $adsMenuActive = request()->routeIs('ads.*') || request()->routeIs('admin.ads.*');
    $vendorsMenuActive = request()->routeIs('admin.vendors.*') || request()->routeIs('admin.vendor-products.*');

    if ($isGeneralUser) {
        $dashboardUrl = route('user.dashboard');
        $dashboardActive = request()->routeIs('user.dashboard');
    } elseif ($isVendor && $vendorApproved) {
        $dashboardUrl = route('vendor.dashboard');
        $dashboardActive = request()->routeIs('vendor.dashboard');
    } elseif ($isAdmin) {
        $dashboardUrl = route('admin.dashboard');
        $dashboardActive = request()->routeIs('admin.dashboard');
    } else {
        $slug = auth()->user()->firstReadableModuleSlug();
        $dashboardUrl = $slug ? route('modules.show', $slug) : route('home');
        $dashboardActive = request()->routeIs('modules.show');
    }
@endphp
<style>
    .admin-sidebar-group {
        margin: 0;
    }

    .admin-sidebar-group summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 12px 16px;
        color: inherit;
    }

    .admin-sidebar-group summary::-webkit-details-marker {
        display: none;
    }

    .admin-sidebar-group ul {
        margin: 0;
    }

    .admin-sidebar-group details[open] summary .fa-chevron-down {
        transform: rotate(180deg);
    }
</style>
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
                <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                    <i class="fa-solid fa-folder-tree"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('admin.homepage-settings.*') ? 'active' : '' }}" href="{{ route('admin.homepage-settings.edit') }}">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Page Setting</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('admin.terms-and-conditions.*') ? 'active' : '' }}" href="{{ route('admin.terms-and-conditions.index') }}">
                    <i class="fa-solid fa-file-contract"></i>
                    <span>Terms &amp; Conditions</span>
                </a>
            </li>
            <hr>
            <li class="admin-sidebar-group">
                <details {{ $offersMenuActive ? 'open' : '' }}>
                    <summary class="{{ $offersMenuActive ? 'active' : '' }} d-flex align-items-center justify-content-between">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-tags"></i>
                            <span>Offers</span>
                        </span>
                        <i class="fa-solid fa-chevron-down small"></i>
                    </summary>
                    <ul class="list-unstyled ps-4">
                        <li>
                            <a class="{{ request()->routeIs('offers.*') ? 'active' : '' }}" href="{{ route('offers.index') }}">
                                <i class="fa-solid fa-list"></i>
                                <span>All Offers</span>
                            </a>
                        </li>
                    </ul>
                </details>
            </li>
            <li class="admin-sidebar-group">
                <details {{ $adsMenuActive ? 'open' : '' }}>
                    <summary class="{{ $adsMenuActive ? 'active' : '' }} d-flex align-items-center justify-content-between">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-rectangle-ad"></i>
                            <span>Ads</span>
                        </span>
                        <i class="fa-solid fa-chevron-down small"></i>
                    </summary>
                    <ul class="list-unstyled ps-4">
                        <li>
                            <a class="{{ request()->routeIs('ads.*') ? 'active' : '' }}" href="{{ route('ads.index') }}">
                                <i class="fa-solid fa-rectangle-list"></i>
                                <span>All Ads</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.ads.sizes.*') ? 'active' : '' }}" href="{{ route('admin.ads.sizes.index') }}">
                                <i class="fa-solid fa-ruler-combined"></i>
                                <span>Ad Sizes</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.ads.submissions.*') ? 'active' : '' }}" href="{{ route('admin.ads.submissions.index') }}">
                                <i class="fa-solid fa-inbox"></i>
                                <span>Ad Submissions</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.ads.reports.*') ? 'active' : '' }}" href="{{ route('admin.ads.reports.index') }}">
                                <i class="fa-regular fa-flag"></i>
                                <span>Report Ads</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.ads.contact-support.*') ? 'active' : '' }}" href="{{ route('admin.ads.contact-support.index') }}">
                                <i class="fa-regular fa-envelope"></i>
                                <span>Contact Support</span>
                            </a>
                        </li>
                    </ul>
                </details>
            </li>
            <li class="admin-sidebar-group">
                <details {{ $vendorsMenuActive ? 'open' : '' }}>
                    <summary class="{{ $vendorsMenuActive ? 'active' : '' }} d-flex align-items-center justify-content-between">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-store"></i>
                            <span>Vendor</span>
                        </span>
                        <i class="fa-solid fa-chevron-down small"></i>
                    </summary>
                    <ul class="list-unstyled ps-4">
                        <li>
                            <a class="{{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}" href="{{ route('admin.vendors.index') }}">
                                <i class="fa-solid fa-list"></i>
                                <span>All Vendors</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.vendor-products.*') ? 'active' : '' }}" href="{{ route('admin.vendor-products.index') }}">
                                <i class="fa-solid fa-boxes-stacked"></i>
                                <span>Products Approval</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.vendor-products.all.*') ? 'active' : '' }}" href="{{ route('admin.vendor-products.all.index') }}">
                                <i class="fa-solid fa-rectangle-list"></i>
                                <span>All Products</span>
                            </a>
                        </li>
                    </ul>
                </details>
            </li>
        @endif

         @foreach($emsModules as $slug => $label)
            @if(($isAdmin || auth()->user()->can($slug.'.read')) && !in_array($slug, ['offers', 'ads'], true))
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
                    <span>My Offers</span>
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
        @elseif($isVendor && $vendorApproved)
            <li>
                <a class="{{ request()->routeIs('offers.*') ? 'active' : '' }}" href="{{ route('offers.index') }}">
                    <i class="fa-solid fa-tags"></i>
                    <span>My Offers</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('ads.*') ? 'active' : '' }}" href="{{ route('ads.index') }}">
                    <i class="fa-solid fa-rectangle-ad"></i>
                    <span>My Ads</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('vendor.public-page.*') ? 'active' : '' }}" href="{{ route('vendor.public-page.edit') }}">
                    <i class="fa-solid fa-globe"></i>
                    <span>Public Page</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('vendor.branches.*') ? 'active' : '' }}" href="{{ route('vendor.branches.index') }}">
                    <i class="fa-solid fa-code-branch"></i>
                    <span>My Branches</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('vendor.products.*') ? 'active' : '' }}" href="{{ route('vendor.products.index') }}">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Manage Products</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('vendor.inquiries.*') ? 'active' : '' }}" href="{{ route('vendor.inquiries.index') }}">
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <span>Inquiries</span>
                </a>
            </li>
        @elseif($isEmployee)
            @if($canAccessOffers)
                <li>
                    <a class="{{ request()->routeIs('offers.*') ? 'active' : '' }}" href="{{ route('offers.index') }}">
                        <i class="fa-solid fa-tags"></i>
                        <span>My Offers</span>
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
