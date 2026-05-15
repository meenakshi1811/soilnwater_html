<header class="header container-fluid d-flex align-items-center" id="frontendHeader">
  <a href="/" class="logo">
    <img class="logo-icon" src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater logo">
  </a>

  <button class="header-menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#mobileHeaderMenu" aria-controls="mobileHeaderMenu" aria-expanded="false" aria-label="Toggle header menu">
    <i class="fa-solid fa-bars"></i>
  </button>

  <div class="loc-wrap" id="headerLocationToggle" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
    <span class="loc-pin"><i class="fa-solid fa-location-dot"></i></span>
    <input
      id="headerCurrentLocation"
      class="loc-text-input"
      type="text"
      data-default-location="Detecting location..."
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
      $activeSearchModule = request('module')
        ?? (request()->routeIs('frontend.ads.*') ? 'ads' : 'offers');
    @endphp
    <select name="module" class="search-module-select" aria-label="Search module">
      <option value="offers" @selected($activeSearchModule === 'offers')>Offers</option>
      <option value="ads" @selected($activeSearchModule === 'ads')>Ads</option>
    </select>
    <input
      class="search-query-input"
      type="text"
      name="q"
      placeholder="Search offers or ads..."
      value="{{ request('q', request('search')) }}"
      aria-label="Search query"
    >
    <button type="submit" class="search-submit-btn" aria-label="Search">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>
  </form>

  <div class="header-actions header-actions-desktop">
    <a class="btn-offer" href="{{ auth()->check() ? route('post-offer') : route('login') }}">Post Offer</a>
    <a class="btn-post" href="{{ auth()->check() ? route('ads.create.size') : route('login') }}">Post Ad</a>

    @auth
      @php
        $dashboardUrl = auth()->user()->isGeneralUser() ? route('user.dashboard') : route('admin.dashboard');
      @endphp
      <div class="dropdown user-menu-dropdown">
        <button
          class="btn-login dropdown-toggle user-menu-toggle"
          type="button"
          id="headerUserMenu"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
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

    if (!header || !menu || typeof bootstrap === 'undefined') {
      return;
    }

    menu.addEventListener('show.bs.collapse', function () {
      header.classList.add('header-mobile-open');
    });

    menu.addEventListener('hide.bs.collapse', function () {
      header.classList.remove('header-mobile-open');
    });
  });
</script>
