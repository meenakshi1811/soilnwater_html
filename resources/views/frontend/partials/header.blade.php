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
    <a class="btn-offer" href="{{ auth()->check() ? route('post-offer') : route('login') }}">Create Offer</a>
    <button class="btn-post">Post Ad</button>

    @auth
      <form method="POST" action="{{ route('logout') }}" class="d-inline">
        @csrf
        <button type="submit" class="btn-login">Logout</button>
      </form>
    @else
      <a class="btn-login" href="{{ route('login') }}">Login</a>
    @endauth
  </div>
</header>
