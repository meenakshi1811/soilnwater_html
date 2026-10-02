@php
  $activeSearchModule = $activeSearchModule ?? request('module') ?? match (true) {
    request()->routeIs('frontend.ads.*') => 'ads',
    request()->routeIs('frontend.vendors.*') => 'vendors',
    request()->routeIs('frontend.consultants.*') => 'consultants',
    request()->routeIs('frontend.service_providers.*') => 'services',
    request()->routeIs('community.*') => 'community',
    default => 'offers',
  };
  $searchPlaceholders = [
    'offers' => 'Search offers...',
    'ads' => 'Search ads...',
    'vendors' => 'Search vendor name or product...',
    'consultants' => 'Search consultant name or service...',
    'services' => 'Search provider name or service...',
    'community' => 'Search community posts...',
  ];
  $searchWrapClass = trim('search-wrap ' . ($searchWrapClass ?? ''));
  $defaultPlaceholder = $searchPlaceholderOverride ?? ($searchPlaceholders[$activeSearchModule] ?? 'Search...');
  $showModuleSelect = $showModuleSelect ?? true;
  $showSearchLabel = $showSearchLabel ?? false;
@endphp

<form class="{{ $searchWrapClass }}" method="GET" action="{{ route('frontend.search') }}" @if(!empty($searchFormId)) id="{{ $searchFormId }}" @endif>
  @if($showModuleSelect)
    <select name="module" class="search-module-select" aria-label="Search module">
      <option value="offers" @selected($activeSearchModule === 'offers')>Offers</option>
      <option value="ads" @selected($activeSearchModule === 'ads')>Ads</option>
      <option value="vendors" @selected($activeSearchModule === 'vendors')>Vendors</option>
      <option value="consultants" @selected($activeSearchModule === 'consultants')>Consultants</option>
      <option value="services" @selected($activeSearchModule === 'services')>Services</option>
      <option value="community" @selected($activeSearchModule === 'community')>Community</option>
    </select>
  @else
    <input type="hidden" name="module" value="{{ $activeSearchModule }}">
    <span class="search-leading-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
  @endif
  <input
    class="search-query-input"
    type="text"
    name="q"
    placeholder="{{ $defaultPlaceholder }}"
    data-search-placeholders='@json($searchPlaceholders)'
    value="{{ request('q', request('search')) }}"
    aria-label="Search query"
    @if(!empty($searchInputId)) id="{{ $searchInputId }}" @endif
  >
  <button type="submit" class="search-submit-btn" aria-label="Search">
    @if(!empty($searchSubmitText))
      {{ $searchSubmitText }}
    @else
      <i class="fa-solid fa-magnifying-glass"></i>
    @endif
  </button>
</form>
