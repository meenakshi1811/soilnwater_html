<header class="header container-fluid d-flex align-items-center" id="frontendHeader">
  <a href="/" class="logo">
    <img class="logo-icon" src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater logo">
  </a>

  <button class="header-menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#mobileHeaderMenu" aria-controls="mobileHeaderMenu" aria-expanded="false" aria-label="Toggle header menu">
    <i class="fa-solid fa-bars"></i>
  </button>

  @php
    $registeredLocation = null;
    if (auth()->check()) {
      $registeredLocation = collect([
        auth()->user()->address,
        auth()->user()->city,
        auth()->user()->pincode,
      ])->filter()->implode(', ');
    }
  @endphp

  <div class="loc-wrap" id="headerLocationToggle" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
    <span class="loc-pin"><i class="fa-solid fa-location-dot"></i></span>
    <input
      id="headerCurrentLocation"
      class="loc-text-input"
      type="text"
      data-default-location="Detecting location..."
      data-registered-location="{{ $registeredLocation }}"
      placeholder="Search location"
      autocomplete="off"
    >
    <span class="loc-caret">▾</span>

    <div class="location-dropdown" id="headerLocationDropdown" hidden>
      <label for="headerLocationSearch" class="location-dropdown-label">Search location</label>
      <input
        id="headerLocationSearch"
        type="text"
        class="location-dropdown-input"
        placeholder="Search your address..."
        autocomplete="off"
      >
      <small class="location-dropdown-note">Select an address to set your current location.</small>
    </div>
  </div>

  <form class="search-wrap" method="GET" action="{{ route('frontend.search') }}">
    @php
      $activeSearchModule = request('module') ?? match (true) {
        request()->routeIs('frontend.ads.*') => 'ads',
        request()->routeIs('frontend.vendors.*') => 'vendors',
        request()->routeIs('frontend.consultants.*') => 'consultants',
        request()->routeIs('frontend.service_providers.*') => 'services',
        default => 'offers',
      };
      $searchPlaceholders = [
        'offers' => 'Search offers...',
        'ads' => 'Search ads...',
        'vendors' => 'Search vendor name or product...',
        'consultants' => 'Search consultant name or service...',
        'services' => 'Search provider name or service...',
      ];
    @endphp
    <select name="module" class="search-module-select" aria-label="Search module">
      <option value="offers" @selected($activeSearchModule === 'offers')>Offers</option>
      <option value="ads" @selected($activeSearchModule === 'ads')>Ads</option>
      <option value="vendors" @selected($activeSearchModule === 'vendors')>Vendors</option>
      <option value="consultants" @selected($activeSearchModule === 'consultants')>Consultants</option>
      <option value="services" @selected($activeSearchModule === 'services')>Services</option>
    </select>
    <input
      class="search-query-input"
      type="text"
      name="q"
      placeholder="{{ $searchPlaceholders[$activeSearchModule] ?? 'Search...' }}"
      data-search-placeholders='@json($searchPlaceholders)'
      value="{{ request('q', request('search')) }}"
      aria-label="Search query"
    >
    <button type="submit" class="search-submit-btn" aria-label="Search">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>
  </form>

  <div class="header-actions header-actions-desktop">
    <a class="btn-offer" href="{{ route('community.index') }}">Community</a>
    <a class="btn-offer" href="{{ auth()->check() ? route('post-offer') : route('login') }}">Post Offer</a>
    <a class="btn-post" href="{{ auth()->check() ? route('ads.create.size') : route('login') }}">Post Ad</a>

    @auth
      @php
        $user = auth()->user();
        if ($user->isVendor()) {
          $dashboardUrl = route('vendor.dashboard');
        } elseif ($user->isConsultant()) {
          $dashboardUrl = route('consultant.dashboard');
        } elseif ($user->isServiceProvider()) {
          $dashboardUrl = route('service_provider.dashboard');
        } elseif ($user->isStaff()) {
          $dashboardUrl = route('admin.dashboard');
        } else {
          $dashboardUrl = route('user.dashboard');
        }
      @endphp
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
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item">Logout</button>
            </form>
          </li>
        </ul>
      </div>
    @else
      <a class="btn-login" href="{{ route('login') }}">Login</a>
    @endauth
  </div>


  <div class="collapse header-mobile-menu" id="mobileHeaderMenu">
    <div class="header-mobile-menu-top">
      <button class="header-menu-close" type="button" data-bs-toggle="collapse" data-bs-target="#mobileHeaderMenu" aria-controls="mobileHeaderMenu" aria-expanded="true" aria-label="Close header menu">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <a class="btn-offer" href="{{ route('community.index') }}">Community</a>
    <a class="btn-offer" href="{{ auth()->check() ? route('post-offer') : route('login') }}">Post Offer</a>
    <a class="btn-post" href="{{ auth()->check() ? route('ads.create.size') : route('login') }}">Post Ad</a>

    @auth
      <a class="btn-login" href="{{ $dashboardUrl }}">My Account</a>
      <form method="POST" action="{{ route('logout') }}" class="header-mobile-logout">
        @csrf
        <button type="submit" class="btn-login">Logout</button>
      </form>
    @else
      <a class="btn-login" href="{{ route('login') }}">Login</a>
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
