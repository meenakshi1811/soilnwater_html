@php
  $registeredLocation = $registeredLocation ?? null;
  if (! isset($user)) {
    $user = auth()->user();
  }
  if ($registeredLocation === null && $user) {
    $registeredLocation = collect([
      $user->address,
      $user->city,
      $user->pincode,
    ])->filter()->implode(', ');
  }
  $locWrapClass = trim('loc-wrap ' . ($locWrapClass ?? ''));
  $isCompactHeader = str_contains($locWrapClass, 'loc-wrap--header-compact');
@endphp

<div class="{{ $locWrapClass }}" id="headerLocationToggle" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
  <span class="loc-pin"><i class="fa-solid fa-location-dot"></i></span>
  @if(!empty($showHeroStyleLabel))
    <span class="loc-stack">
      <span class="loc-stack-title">Select Location</span>
      <span class="loc-stack-sub">City, Area or Use My Location</span>
    </span>
  @endif
  <input
    id="headerCurrentLocation"
    class="loc-text-input"
    type="text"
    data-default-location="Detecting location..."
    data-registered-location="{{ $registeredLocation }}"
    placeholder="{{ !empty($showHeroStyleLabel) || $isCompactHeader ? 'Select Location' : 'Search location' }}"
    autocomplete="off"
  >
  <span class="loc-caret"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>

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
