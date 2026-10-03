@php
  $user = auth()->user();
  $dashboardUrl = \App\Support\UserDashboard::url($user);
  $isHomepage = request()->routeIs('frontend.index');
@endphp

<header class="header{{ $isHomepage ? ' header--homepage' : '' }}" id="frontendHeader">
  <div class="header-inner">
  <a href="/" class="logo">
    <img class="logo-icon" src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater logo">
  </a>

  @if($isHomepage)
    <div class="header-mobile-home-search d-lg-none" id="homepageMobileSearchDock">
      @include('frontend.partials.header-search-form', [
        'searchWrapClass' => 'search-wrap search-wrap--mobile-home',
        'searchFormId' => 'mobileHomeSearchForm',
        'searchInputId' => 'mobileHomeSearchQuery',
        'showModuleSelect' => false,
        'activeSearchModule' => 'offers',
        'searchPlaceholderOverride' => 'Search businesses & offers',
        'searchSubmitText' => 'Search',
      ])
    </div>
  @endif

  <nav class="header-main-nav d-none d-xl-flex" aria-label="Main navigation">
    <div class="dropdown header-nav-dropdown">
      <button class="header-nav-link dropdown-toggle" type="button" id="navBusinesses" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">Businesses</button>
      <ul class="dropdown-menu" aria-labelledby="navBusinesses">
        <li><a class="dropdown-item" href="{{ route('frontend.vendors.index') }}">Browse Businesses</a></li>
        <li><a class="dropdown-item" href="{{ route('frontend.vendors.categories') }}">Categories</a></li>
        <li><a class="dropdown-item" href="{{ route('frontend.vendors.listings') }}">Listings</a></li>
      </ul>
    </div>
    <div class="dropdown header-nav-dropdown">
      <button class="header-nav-link dropdown-toggle" type="button" id="navServices" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">Services</button>
      <ul class="dropdown-menu" aria-labelledby="navServices">
        <li><a class="dropdown-item" href="{{ route('frontend.service_providers.index') }}">Browse Services</a></li>
        <li><a class="dropdown-item" href="{{ route('frontend.service_providers.categories') }}">Categories</a></li>
        <li><a class="dropdown-item" href="{{ route('frontend.service_providers.listings') }}">Listings</a></li>
      </ul>
    </div>
    <div class="dropdown header-nav-dropdown">
      <button class="header-nav-link dropdown-toggle" type="button" id="navConsultants" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">Consultants</button>
      <ul class="dropdown-menu" aria-labelledby="navConsultants">
        <li><a class="dropdown-item" href="{{ route('frontend.consultants.index') }}">Browse Consultants</a></li>
        <li><a class="dropdown-item" href="{{ route('frontend.consultants.categories') }}">Categories</a></li>
        <li><a class="dropdown-item" href="{{ route('frontend.consultants.listings') }}">Listings</a></li>
      </ul>
    </div>
    <div class="dropdown header-nav-dropdown">
      <button class="header-nav-link dropdown-toggle" type="button" id="navEducation" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">Education</button>
      <ul class="dropdown-menu" aria-labelledby="navEducation">
        <li><a class="dropdown-item" href="{{ route('schools.index') }}">Schools</a></li>
        <li><a class="dropdown-item" href="{{ route('institutes.index') }}">Institutes</a></li>
        <li><a class="dropdown-item" href="{{ route('educator.index') }}">Teachers &amp; Tutors</a></li>
        <li><a class="dropdown-item" href="{{ route('study-materials.library') }}">Study Materials</a></li>
      </ul>
    </div>
    <div class="dropdown header-nav-dropdown">
      <button class="header-nav-link dropdown-toggle" type="button" id="navOffers" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">Offers</button>
      <ul class="dropdown-menu" aria-labelledby="navOffers">
        <li><a class="dropdown-item" href="{{ route('frontend.offers.index') }}">Browse Offers</a></li>
        <li><a class="dropdown-item" href="{{ route('frontend.ads.index') }}">Browse Ads</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ auth()->check() ? route('post-offer') : route('login') }}">Post Offer</a></li>
        <li><a class="dropdown-item" href="{{ auth()->check() ? route('ads.create.size') : route('login') }}">Post Ad</a></li>
      </ul>
    </div>
    <div class="dropdown header-nav-dropdown">
      <button class="header-nav-link dropdown-toggle" type="button" id="navProperties" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">Properties</button>
      <ul class="dropdown-menu" aria-labelledby="navProperties">
        <li><a class="dropdown-item" href="{{ route('frontend.ads.index') }}">Property Ads</a></li>
        <li><a class="dropdown-item" href="{{ route('frontend.offers.index') }}">Property Offers</a></li>
      </ul>
    </div>
    <a class="header-nav-link header-nav-link--plain" href="{{ route('community.index') }}">Community</a>
  </nav>

  <div class="header-right-cluster">
    @if($isHomepage)
      @include('frontend.partials.header-location', ['locWrapClass' => 'loc-wrap--header-compact homepage-loc-host'])
    @endif

    <div class="header-utilities">
      @if($isHomepage)
        <button type="button" class="header-search-jump d-none d-md-inline-flex" id="headerSearchJump" aria-label="Jump to search">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      @else
        @include('frontend.partials.header-location')
        @include('frontend.partials.header-search-form')
      @endif
    </div>

    <div class="header-actions header-actions-desktop">
    @auth
      <div class="dropdown user-menu-dropdown">
        <button
          class="btn-login dropdown-toggle user-menu-toggle"
          type="button"
          id="headerUserMenu"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          @if($user->profile_image)
            <img src="{{ asset($user->profile_image) }}" alt="" width="28" height="28" class="rounded-circle object-fit-cover me-1">
          @endif
          My Account
        </button>
        <ul class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="headerUserMenu">
          <li><a class="dropdown-item" href="{{ $dashboardUrl }}">Dashboard</a></li>
          <li><a class="dropdown-item" href="{{ route('employee.login') }}">Employee portal</a></li>
          <li><a class="dropdown-item" href="{{ auth()->check() ? route('post-offer') : route('login') }}">Post Offer</a></li>
          <li><a class="dropdown-item" href="{{ auth()->check() ? route('ads.create.size') : route('login') }}">Post Ad</a></li>
          @if($user->isVendor() && ! $user->vendor?->is_premium)
            <li><a class="dropdown-item" href="{{ route('frontend.premium.show', 'vendor') }}"><i class="fa-solid fa-crown text-warning me-1"></i> Get Premium</a></li>
          @elseif($user->isConsultant() && ! $user->consultant?->is_premium)
            <li><a class="dropdown-item" href="{{ route('frontend.premium.show', 'consultant') }}"><i class="fa-solid fa-crown text-warning me-1"></i> Get Premium</a></li>
          @elseif($user->isServiceProvider() && ! $user->serviceProvider?->is_premium)
            <li><a class="dropdown-item" href="{{ route('frontend.premium.show', 'service') }}"><i class="fa-solid fa-crown text-warning me-1"></i> Get Premium</a></li>
          @endif
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item">Logout</button>
            </form>
          </li>
        </ul>
      </div>
    @else
      <a class="btn-login btn-login--outline" href="{{ route('login') }}">Login</a>
      <a class="btn-signup" href="{{ route('register') }}">Sign Up</a>
    @endauth
    </div>
  </div>
  </div>

  <button class="header-menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#mobileHeaderMenu" aria-controls="mobileHeaderMenu" aria-expanded="false" aria-label="Toggle header menu">
    <i class="fa-solid fa-bars"></i>
  </button>

  <div class="collapse header-mobile-menu" id="mobileHeaderMenu">
    <div class="header-mobile-menu-top">
      <button class="header-menu-close" type="button" data-bs-toggle="collapse" data-bs-target="#mobileHeaderMenu" aria-controls="mobileHeaderMenu" aria-expanded="true" aria-label="Close header menu">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <a class="btn-offer" href="{{ route('frontend.vendors.index') }}">Businesses</a>
    <a class="btn-offer" href="{{ route('frontend.service_providers.index') }}">Services</a>
    <a class="btn-offer" href="{{ route('frontend.consultants.index') }}">Consultants</a>
    <a class="btn-offer" href="{{ route('schools.index') }}">Education</a>
    <a class="btn-offer" href="{{ route('frontend.offers.index') }}">Offers</a>
    <a class="btn-offer" href="{{ route('community.index') }}">Community</a>
    @if($isHomepage)
      <button type="button" class="btn-offer header-mobile-search-jump" id="mobileHeaderSearchJump">Search on page</button>
    @endif
    <a class="btn-offer" href="{{ auth()->check() ? route('post-offer') : route('login') }}">Post Offer</a>
    <a class="btn-post" href="{{ auth()->check() ? route('ads.create.size') : route('login') }}">Post Ad</a>

    @auth
      <a class="btn-login" href="{{ $dashboardUrl }}">My Account</a>
      @if($user->isVendor() && ! $user->vendor?->is_premium)
        <a class="btn-offer" href="{{ route('frontend.premium.show', 'vendor') }}">Get Premium</a>
      @elseif($user->isConsultant() && ! $user->consultant?->is_premium)
        <a class="btn-offer" href="{{ route('frontend.premium.show', 'consultant') }}">Get Premium</a>
      @elseif($user->isServiceProvider() && ! $user->serviceProvider?->is_premium)
        <a class="btn-offer" href="{{ route('frontend.premium.show', 'service') }}">Get Premium</a>
      @endif
      <form method="POST" action="{{ route('logout') }}" class="header-mobile-logout">
        @csrf
        <button type="submit" class="btn-login">Logout</button>
      </form>
    @else
      <a class="btn-login" href="{{ route('login') }}">Login</a>
      <a class="btn-signup" href="{{ route('register') }}">Sign Up</a>
      <a class="btn-login" href="{{ route('employee.login') }}">Employee</a>
    @endauth
  </div>
</header>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const header = document.getElementById('frontendHeader');
    const menu = document.getElementById('mobileHeaderMenu');
    const moduleSelect = header?.querySelector('.search-module-select');
    const searchInput = header?.querySelector('.search-query-input');

    if (moduleSelect && searchInput) {
      const placeholders = JSON.parse(searchInput.dataset.searchPlaceholders || '{}');
      moduleSelect.addEventListener('change', function () {
        searchInput.placeholder = placeholders[moduleSelect.value] || 'Search...';
      });
    }

    const heroSearchInput = document.getElementById('heroSearchQuery');
    const heroModuleSelect = document.querySelector('#heroSearchForm .search-module-select');
    if (heroModuleSelect && heroSearchInput) {
      const placeholders = JSON.parse(heroSearchInput.dataset.searchPlaceholders || '{}');
      heroModuleSelect.addEventListener('change', function () {
        heroSearchInput.placeholder = placeholders[heroModuleSelect.value] || 'Search...';
      });
    }

    function jumpToHeroSearch() {
      const mobileDock = document.getElementById('homepageMobileSearchDock');
      const mobileInput = document.getElementById('mobileHomeSearchQuery');
      if (mobileDock && mobileInput && window.matchMedia('(max-width: 991.98px)').matches) {
        mobileDock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(function () { mobileInput.focus(); }, 350);
        return;
      }
      const target = document.getElementById('heroSearchDock') || document.getElementById('heroSearchQuery');
      if (!target) return;
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
      const input = document.getElementById('heroSearchQuery');
      if (input) {
        window.setTimeout(function () { input.focus(); }, 350);
      }
    }

    const searchJump = document.getElementById('headerSearchJump');
    if (searchJump) {
      searchJump.addEventListener('click', jumpToHeroSearch);
    }

    const mobileSearchJump = document.getElementById('mobileHeaderSearchJump');
    if (mobileSearchJump) {
      mobileSearchJump.addEventListener('click', function () {
        jumpToHeroSearch();
        const mobileMenu = document.getElementById('mobileHeaderMenu');
        if (mobileMenu && typeof bootstrap !== 'undefined') {
          bootstrap.Collapse.getOrCreateInstance(mobileMenu, { toggle: false }).hide();
        }
      });
    }

    const heroLocationTrigger = document.getElementById('heroLocationTrigger');
    const headerLocationInput = document.getElementById('headerCurrentLocation');
    const heroLocationTitle = document.getElementById('heroLocationTitle');
    if (heroLocationTrigger && headerLocationInput) {
      const syncHeroLocationLabel = function () {
        if (!heroLocationTitle) return;
        const value = (headerLocationInput.value || '').trim();
        heroLocationTitle.textContent = value || 'Select Location';
      };
      heroLocationTrigger.addEventListener('click', function () {
        headerLocationInput.focus();
        headerLocationInput.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      });
      heroLocationTrigger.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          headerLocationInput.focus();
        }
      });
      headerLocationInput.addEventListener('input', syncHeroLocationLabel);
      headerLocationInput.addEventListener('change', syncHeroLocationLabel);
      syncHeroLocationLabel();
    }

    const mobileHomeLocBar = document.getElementById('mobileHomeLocBar');
    const mobileHomeLocLabel = document.getElementById('mobileHomeLocLabel');
    if (mobileHomeLocBar && headerLocationInput) {
      const syncMobileHomeLocLabel = function () {
        if (!mobileHomeLocLabel) return;
        const value = (headerLocationInput.value || '').trim();
        mobileHomeLocLabel.textContent = value || 'Select Location';
      };
      mobileHomeLocBar.addEventListener('click', function () {
        const locHost = document.getElementById('headerLocationToggle');
        if (locHost) {
          locHost.classList.add('is-mobile-loc-open');
        }
        headerLocationInput.focus();
      });
      headerLocationInput.addEventListener('blur', function () {
        window.setTimeout(function () {
          const locHost = document.getElementById('headerLocationToggle');
          const active = document.activeElement;
          if (locHost && active !== headerLocationInput && !locHost.contains(active)) {
            locHost.classList.remove('is-mobile-loc-open');
          }
        }, 180);
      });
      headerLocationInput.addEventListener('input', syncMobileHomeLocLabel);
      headerLocationInput.addEventListener('change', syncMobileHomeLocLabel);
      syncMobileHomeLocLabel();
    }

    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
      document.querySelectorAll('.header-nav-dropdown > .dropdown-toggle[data-bs-toggle="dropdown"]').forEach(function (toggle) {
        bootstrap.Dropdown.getOrCreateInstance(toggle, { display: 'static' });
      });
    }

    if (!header || !menu || typeof bootstrap === 'undefined') {
      return;
    }

    menu.addEventListener('shown.bs.collapse', function () {
      header.classList.add('header-mobile-open');
    });

    menu.addEventListener('hide.bs.collapse', function () {
      header.classList.remove('header-mobile-open');
    });

    menu.addEventListener('hidden.bs.collapse', function () {
      header.classList.remove('header-mobile-open');
    });
  });
</script>

@push('scripts')
<script>
  (function () {
    function initHeaderNavDropdowns() {
      if (typeof bootstrap === 'undefined' || !bootstrap.Dropdown) {
        return;
      }
      document.querySelectorAll('.header-nav-dropdown > .dropdown-toggle[data-bs-toggle="dropdown"]').forEach(function (toggle) {
        bootstrap.Dropdown.getOrCreateInstance(toggle, { display: 'static' });
      });
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initHeaderNavDropdowns);
    } else {
      initHeaderNavDropdowns();
    }
  })();
</script>
@endpush
