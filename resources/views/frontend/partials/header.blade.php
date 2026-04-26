<header class="header container-fluid d-flex flex-wrap align-items-center">
  <a href="/" class="logo">
    <img class="logo-icon" src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater logo">
  </a>

  <div class="loc-wrap">
    <span class="loc-pin"><i class="fa-solid fa-location-dot"></i></span>
    <span>New Delhi</span>
    <span class="loc-caret">▾</span>
  </div>

  <div class="search-wrap">
    <input type="text" placeholder="Search for products, services, properties...">
  </div>

  <div class="header-actions">
    <a class="btn-offer" href="{{ auth()->check() ? route('post-offer') : route('login') }}">Post Offer</a>
    <button class="btn-post">Post Ad</button>

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
</header>
