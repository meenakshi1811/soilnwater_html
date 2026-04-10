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
    <button class="btn-offer">Post Offer</button>
    <button class="btn-post">Post Ad</button>
    <a class="btn-login" href="{{ route('login') }}">Login</a>
  </div>
</header>
