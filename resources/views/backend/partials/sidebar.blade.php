@php
    $user = \App\Support\AuthActor::user();
    $emsModules = \App\Support\ModulePermissions::modules();
    $isGeneralUser = $user->isGeneralUser();
    $isAdmin = $user->isAdmin();
    $isEmployee = $user->isEmployee();
    $isVendor = $user->isVendor();
    $isConsultant = $user->isConsultant();
    $isServiceProvider = $user->isServiceProvider();
    $vendorApproved = $isVendor && $user->vendor?->isApproved();
    $consultantApproved = $isConsultant && $user->consultant?->isApproved();
    $serviceProviderApproved = $isServiceProvider && $user->serviceProvider?->isApproved();
    $canAccessOffers = $isAdmin || $isGeneralUser || $user->canModule('offers', 'read') || $user->canModule('vendors', 'read');
    $offersMenuActive = request()->routeIs('offers.*') || request()->routeIs('admin.offers.*') || request()->routeIs('admin.offer-prices.*');
    $adsMenuActive = request()->routeIs('ads.*') || request()->routeIs('admin.ads.*');
    $communityPostsActive = request()->routeIs('community.posts.*');
    $communitySavedActive = request()->routeIs('community.saved.*');
    $communitySubscriptionsActive = request()->routeIs('community.subscriptions.*');
    $communityAuthorQuestionsActive = request()->routeIs('community.author-questions.*');
    $communityMenuActive = request()->routeIs('admin.community-posts.*') || request()->routeIs('community.posts.*') || request()->routeIs('community.author-questions.*') || request()->routeIs('community.saved.*') || request()->routeIs('community.subscriptions.*');
    $communityChatMenuActive = request()->routeIs('admin.community-chats.*') || request()->routeIs('admin.foul-words.*');
    $vendorsMenuActive = request()->routeIs('admin.vendors.*') || request()->routeIs('admin.vendor-products.*');
    $consultantsMenuActive = request()->routeIs('admin.consultants.*') || request()->routeIs('admin.consultant-services.*');
    $serviceProvidersMenuActive = request()->routeIs('admin.service_providers.*') || request()->routeIs('admin.service-provider-services.*');
    $approvalCenterActive = request()->routeIs('admin.approvals.*');
    $paymentsMenuActive = request()->routeIs('admin.premium-payments.*');
    $premiumPricesMenuActive = request()->routeIs('admin.premium-prices.*');
    $listingPaymentsMenuActive = request()->routeIs('admin.listing-payments.*');
    $vendorPagesMenuActive = request()->routeIs('vendor.public-page.*') || request()->routeIs('vendor.branches.*') || request()->routeIs('vendor.products.*') || request()->routeIs('vendor.inquiries.*');
    $consultantPagesMenuActive = request()->routeIs('consultant.public-page.*') || request()->routeIs('consultant.branches.*') || request()->routeIs('consultant.services.*') || request()->routeIs('consultant.inquiries.*');
    $serviceProviderPagesMenuActive = request()->routeIs('service_provider.public-page.*') || request()->routeIs('service_provider.branches.*') || request()->routeIs('service_provider.services.*') || request()->routeIs('service_provider.inquiries.*');
    $premiumMenuActive = request()->routeIs('frontend.premium.show');

    if ($isGeneralUser) {
        $dashboardUrl = route('user.dashboard');
        $dashboardActive = request()->routeIs('user.dashboard');
    } elseif ($isVendor && $vendorApproved) {
        $dashboardUrl = route('vendor.dashboard');
        $dashboardActive = request()->routeIs('vendor.dashboard');
    } elseif ($isConsultant && $consultantApproved) {
        $dashboardUrl = route('consultant.dashboard');
        $dashboardActive = request()->routeIs('consultant.dashboard');
    } elseif ($isServiceProvider && $serviceProviderApproved) {
        $dashboardUrl = route('service_provider.dashboard');
        $dashboardActive = request()->routeIs('service_provider.dashboard');
    } elseif ($isAdmin) {
        $dashboardUrl = route('admin.dashboard');
        $dashboardActive = request()->routeIs('admin.dashboard');
    } elseif ($isEmployee) {
        $dashboardUrl = route('employee.dashboard');
        $dashboardActive = request()->routeIs('employee.dashboard') || request()->routeIs('modules.show');
    } else {
        $slug = $user->firstReadableModuleSlug();
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
        @if($isEmployee)
            @include('backend.partials.sidebar-module-menus', ['sidebarUser' => $user, 'sidebarIsAdmin' => false])
        @endif
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
            <li class="admin-sidebar-group">
                <details {{ $communityChatMenuActive ? 'open' : '' }}>
                    <summary class="{{ $communityChatMenuActive ? 'active' : '' }} d-flex align-items-center justify-content-between">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-comments"></i>
                            <span>Community Chat</span>
                        </span>
                        <i class="fa-solid fa-chevron-down small"></i>
                    </summary>
                    <ul class="list-unstyled ps-4">
                        <li>
                            <a class="{{ request()->routeIs('admin.community-chats.index') || request()->routeIs('admin.community-chats.show') ? 'active' : '' }}" href="{{ route('admin.community-chats.index') }}">
                                <i class="fa-solid fa-message"></i>
                                <span>All Chats</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.community-chats.users') || request()->routeIs('admin.community-chats.users.*') ? 'active' : '' }}" href="{{ route('admin.community-chats.users') }}">
                                <i class="fa-solid fa-user-slash"></i>
                                <span>Chat Users</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.foul-words.*') ? 'active' : '' }}" href="{{ route('admin.foul-words.index') }}">
                                <i class="fa-solid fa-ban"></i>
                                <span>Foul Words</span>
                            </a>
                        </li>
                    </ul>
                </details>
            </li>
            <li>
                <a class="{{ $approvalCenterActive ? 'active' : '' }}" href="{{ route('admin.approvals.index') }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Approval Center</span>
                </a>
            </li>
            <li>
                <a class="{{ $paymentsMenuActive ? 'active' : '' }}" href="{{ route('admin.premium-payments.index') }}">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Premium Payments</span>
                </a>
            </li>
            <li>
                <a class="{{ $premiumPricesMenuActive ? 'active' : '' }}" href="{{ route('admin.premium-prices.index') }}">
                    <i class="fa-solid fa-tags"></i>
                    <span>Premium Prices</span>
                </a>
            </li>
            <li>
                <a class="{{ $listingPaymentsMenuActive ? 'active' : '' }}" href="{{ route('admin.listing-payments.index') }}">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Ad &amp; Offer Payments</span>
                </a>
            </li>
            <hr>
            @include('backend.partials.sidebar-module-menus', ['sidebarUser' => $user, 'sidebarIsAdmin' => $isAdmin])
            <li class="admin-sidebar-group">
                <details {{ $communityMenuActive ? 'open' : '' }}>
                    <summary class="{{ $communityMenuActive ? 'active' : '' }} d-flex align-items-center justify-content-between">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-pen-nib"></i>
                            <span>Community</span>
                        </span>
                        <i class="fa-solid fa-chevron-down small"></i>
                    </summary>
                    <ul class="list-unstyled ps-4">
                        <li>
                            <a class="{{ request()->routeIs('admin.community-posts.index') || request()->routeIs('admin.community-posts.show') || request()->routeIs('admin.community-posts.preview') ? 'active' : '' }}" href="{{ route('admin.community-posts.index') }}">
                                <i class="fa-solid fa-clipboard-check"></i>
                                <span>Post Approvals</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('community.posts.*') ? 'active' : '' }}" href="{{ route('community.posts.index') }}">
                                <i class="fa-solid fa-user-pen"></i>
                                <span>My Posts</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $communitySavedActive ? 'active' : '' }}" href="{{ route('community.saved.index') }}">
                                <i class="fa-solid fa-bookmark"></i>
                                <span>Saved Posts</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $communitySubscriptionsActive ? 'active' : '' }}" href="{{ route('community.subscriptions.index') }}">
                                <i class="fa-solid fa-bell"></i>
                                <span>Subscriptions</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $communityAuthorQuestionsActive ? 'active' : '' }}" href="{{ route('community.author-questions.index') }}">
                                <i class="fa-solid fa-circle-question"></i>
                                <span>Reader Questions</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.community-posts.all.*') ? 'active' : '' }}" href="{{ route('admin.community-posts.all.index') }}">
                                <i class="fa-solid fa-rectangle-list"></i>
                                <span>All Posts</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.community-posts.reports.*') ? 'active' : '' }}" href="{{ route('admin.community-posts.reports.index') }}">
                                <i class="fa-solid fa-flag"></i>
                                <span>Reported Posts</span>
                            </a>
                        </li>
                    </ul>
                </details>
            </li>
        @endif

         @foreach($emsModules as $slug => $label)
            @php
                $sidebarManagedModules = ['users', 'offers', 'ads', 'vendors', 'products', 'consultants', 'service_providers'];
                $hideSidebarManagedModule = ($isAdmin || $isEmployee) && in_array($slug, $sidebarManagedModules, true);
                $canReadModule = $isAdmin || $user->canModule($slug, 'read');
                $entryRoute = \App\Support\ModulePermissions::entryRouteName($slug);
                $moduleUrl = ($entryRoute && \Illuminate\Support\Facades\Route::has($entryRoute))
                    ? route($entryRoute)
                    : route('modules.show', $slug);
                $moduleActive = false;
                if ($entryRoute) {
                    $routePrefix = preg_replace('/\.[^.]+$/', '.*', $entryRoute);
                    $moduleActive = request()->routeIs($routePrefix);
                } elseif (request()->routeIs('modules.show') && request()->route('module') === $slug) {
                    $moduleActive = true;
                }
            @endphp
            @if($canReadModule && ! $hideSidebarManagedModule)
                <li>
                    <a class="{{ $moduleActive ? 'active' : '' }}" href="{{ $moduleUrl }}">
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
                <a class="{{ $communityPostsActive ? 'active' : '' }}" href="{{ route('community.posts.index') }}">
                    <i class="fa-solid fa-pen-nib"></i>
                    <span>Community Posts</span>
                </a>
            </li>
            <li>
                <a class="{{ $communitySavedActive ? 'active' : '' }}" href="{{ route('community.saved.index') }}">
                    <i class="fa-solid fa-bookmark"></i>
                    <span>Saved Posts</span>
                </a>
            </li>
            <li>
                <a class="{{ $communitySubscriptionsActive ? 'active' : '' }}" href="{{ route('community.subscriptions.index') }}">
                    <i class="fa-solid fa-bell"></i>
                    <span>Subscriptions</span>
                </a>
            </li>
            <li>
                <a class="{{ $communityAuthorQuestionsActive ? 'active' : '' }}" href="{{ route('community.author-questions.index') }}">
                    <i class="fa-solid fa-circle-question"></i>
                    <span>Reader Questions</span>
                </a>
            </li>
            <li>
                <a class="{{ request()->routeIs('user.profile.*') ? 'active' : '' }}" href="{{ route('user.profile.edit') }}">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Profile</span>
                </a>
            </li>
        @elseif($isVendor && $vendorApproved)
            @if(! auth()->user()->vendor?->is_premium)
                <li>
                    <a class="{{ $premiumMenuActive && request()->route('type') === 'vendor' ? 'active' : '' }}" href="{{ route('frontend.premium.show', 'vendor') }}">
                        <i class="fa-solid fa-crown"></i>
                        <span>Get Premium</span>
                    </a>
                </li>
            @endif
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
                <a class="{{ $communityPostsActive ? 'active' : '' }}" href="{{ route('community.posts.index') }}">
                    <i class="fa-solid fa-pen-nib"></i>
                    <span>Community Posts</span>
                </a>
            </li>
            <li>
                <a class="{{ $communitySavedActive ? 'active' : '' }}" href="{{ route('community.saved.index') }}">
                    <i class="fa-solid fa-bookmark"></i>
                    <span>Saved Posts</span>
                </a>
            </li>
            <li>
                <a class="{{ $communitySubscriptionsActive ? 'active' : '' }}" href="{{ route('community.subscriptions.index') }}">
                    <i class="fa-solid fa-bell"></i>
                    <span>Subscriptions</span>
                </a>
            </li>
            <li>
                <a class="{{ $communityAuthorQuestionsActive ? 'active' : '' }}" href="{{ route('community.author-questions.index') }}">
                    <i class="fa-solid fa-circle-question"></i>
                    <span>Reader Questions</span>
                </a>
            </li>
            <li class="admin-sidebar-group">
                <details {{ $vendorPagesMenuActive ? 'open' : '' }}>
                    <summary class="{{ $vendorPagesMenuActive ? 'active' : '' }} d-flex align-items-center justify-content-between">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-store"></i>
                            <span>Vendor Pages</span>
                        </span>
                        <i class="fa-solid fa-chevron-down small"></i>
                    </summary>
                    <ul class="list-unstyled ps-4">
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
                    </ul>
                </details>
            </li>

        @elseif($isConsultant && $consultantApproved)
            @if(! auth()->user()->consultant?->is_premium)
                <li>
                    <a class="{{ $premiumMenuActive && request()->route('type') === 'consultant' ? 'active' : '' }}" href="{{ route('frontend.premium.show', 'consultant') }}">
                        <i class="fa-solid fa-crown"></i>
                        <span>Get Premium</span>
                    </a>
                </li>
            @endif
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
                <a class="{{ $communityPostsActive ? 'active' : '' }}" href="{{ route('community.posts.index') }}">
                    <i class="fa-solid fa-pen-nib"></i>
                    <span>Community Posts</span>
                </a>
            </li>
            <li>
                <a class="{{ $communitySavedActive ? 'active' : '' }}" href="{{ route('community.saved.index') }}">
                    <i class="fa-solid fa-bookmark"></i>
                    <span>Saved Posts</span>
                </a>
            </li>
            <li>
                <a class="{{ $communitySubscriptionsActive ? 'active' : '' }}" href="{{ route('community.subscriptions.index') }}">
                    <i class="fa-solid fa-bell"></i>
                    <span>Subscriptions</span>
                </a>
            </li>
            <li>
                <a class="{{ $communityAuthorQuestionsActive ? 'active' : '' }}" href="{{ route('community.author-questions.index') }}">
                    <i class="fa-solid fa-circle-question"></i>
                    <span>Reader Questions</span>
                </a>
            </li>
            <li class="admin-sidebar-group">
                <details {{ $consultantPagesMenuActive ? 'open' : '' }}>
                    <summary class="{{ $consultantPagesMenuActive ? 'active' : '' }} d-flex align-items-center justify-content-between">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-user-tie"></i>
                            <span>Consultant Pages</span>
                        </span>
                        <i class="fa-solid fa-chevron-down small"></i>
                    </summary>
                    <ul class="list-unstyled ps-4">
                        <li>
                            <a class="{{ request()->routeIs('consultant.public-page.*') ? 'active' : '' }}" href="{{ route('consultant.public-page.edit') }}">
                                <i class="fa-solid fa-globe"></i>
                                <span>Public Page</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('consultant.branches.*') ? 'active' : '' }}" href="{{ route('consultant.branches.index') }}">
                                <i class="fa-solid fa-code-branch"></i>
                                <span>My Branches</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('consultant.services.*') ? 'active' : '' }}" href="{{ route('consultant.services.index') }}">
                                <i class="fa-solid fa-clipboard-list"></i>
                                <span>Consultation Services</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('consultant.inquiries.*') ? 'active' : '' }}" href="{{ route('consultant.inquiries.index') }}">
                                <i class="fa-solid fa-envelope-open-text"></i>
                                <span>Inquiries</span>
                            </a>
                        </li>
                    </ul>
                </details>
            </li>

        @elseif($isServiceProvider && $serviceProviderApproved)
            @if(! auth()->user()->serviceProvider?->is_premium)
                <li>
                    <a class="{{ $premiumMenuActive && request()->route('type') === 'service' ? 'active' : '' }}" href="{{ route('frontend.premium.show', 'service') }}">
                        <i class="fa-solid fa-crown"></i>
                        <span>Get Premium</span>
                    </a>
                </li>
            @endif
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
                <a class="{{ $communityPostsActive ? 'active' : '' }}" href="{{ route('community.posts.index') }}">
                    <i class="fa-solid fa-pen-nib"></i>
                    <span>Community Posts</span>
                </a>
            </li>
            <li>
                <a class="{{ $communitySavedActive ? 'active' : '' }}" href="{{ route('community.saved.index') }}">
                    <i class="fa-solid fa-bookmark"></i>
                    <span>Saved Posts</span>
                </a>
            </li>
            <li>
                <a class="{{ $communitySubscriptionsActive ? 'active' : '' }}" href="{{ route('community.subscriptions.index') }}">
                    <i class="fa-solid fa-bell"></i>
                    <span>Subscriptions</span>
                </a>
            </li>
            <li>
                <a class="{{ $communityAuthorQuestionsActive ? 'active' : '' }}" href="{{ route('community.author-questions.index') }}">
                    <i class="fa-solid fa-circle-question"></i>
                    <span>Reader Questions</span>
                </a>
            </li>
            <li class="admin-sidebar-group">
                <details {{ $serviceProviderPagesMenuActive ? 'open' : '' }}>
                    <summary class="{{ $serviceProviderPagesMenuActive ? 'active' : '' }} d-flex align-items-center justify-content-between">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                            <span>Service Pages</span>
                        </span>
                        <i class="fa-solid fa-chevron-down small"></i>
                    </summary>
                    <ul class="list-unstyled ps-4">
                        <li>
                            <a class="{{ request()->routeIs('service_provider.public-page.*') ? 'active' : '' }}" href="{{ route('service_provider.public-page.edit') }}">
                                <i class="fa-solid fa-globe"></i>
                                <span>Public Page</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('service_provider.branches.*') ? 'active' : '' }}" href="{{ route('service_provider.branches.index') }}">
                                <i class="fa-solid fa-code-branch"></i>
                                <span>My Branches</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('service_provider.services.*') ? 'active' : '' }}" href="{{ route('service_provider.services.index') }}">
                                <i class="fa-solid fa-clipboard-list"></i>
                                <span>Services</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('service_provider.inquiries.*') ? 'active' : '' }}" href="{{ route('service_provider.inquiries.index') }}">
                                <i class="fa-solid fa-envelope-open-text"></i>
                                <span>Inquiries</span>
                            </a>
                        </li>
                    </ul>
                </details>
            </li>
        @elseif($isEmployee)
            <li>
                <a class="{{ request()->routeIs('employee.profile.*') ? 'active' : '' }}" href="{{ route('employee.profile.edit') }}">
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
            <form method="POST" action="{{ $isEmployee ? route('employee.logout') : route('logout') }}" class="w-100">
                @csrf
                <button type="submit" class="admin-sidebar-logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </li>
    </ul>
</aside>
