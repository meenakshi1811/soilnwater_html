<aside class="vendors-sidebar">
  <div class="vendors-sidebar__head">
    <div>
      <h2>Filters</h2>
      <p class="vendors-sidebar__lead">Refine your school search.</p>
    </div>
    <button type="button" class="vendors-sidebar__reset" id="institutesMarketResetFilters">Reset All</button>
  </div>

  <div id="institutesFilterBar">
    <div class="vendors-filter-group">
      <label for="institutesMarketFilterSearch">Search</label>
      <div class="vendors-filter-search-wrap">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input id="institutesMarketFilterSearch" class="form-control" placeholder="School name, board or city..." value="{{ request('search', request('q')) }}">
      </div>
    </div>

    <div class="vendors-filter-group">
      <label for="institutesMarketFilterType">Institution type</label>
      <select id="institutesMarketFilterType" class="form-select">
        <option value="">All types</option>
        @foreach (['school' => 'School', 'college' => 'College', 'university' => 'University', 'coaching' => 'Coaching Institute', 'other' => 'Other'] as $value => $label)
          <option value="{{ $value }}" @selected((string) request('type') === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div class="vendors-filter-group">
      <label for="institutesMarketFilterCity">City</label>
      <select id="institutesMarketFilterCity" class="form-select">
        <option value="">All cities</option>
        @foreach ($cities as $city)
          <option value="{{ $city }}" @selected((string) request('city') === (string) $city)>{{ $city }}</option>
        @endforeach
      </select>
    </div>

    <div class="vendors-filter-group">
      <label for="institutesMarketFilterBoard">Board / affiliation</label>
      <select id="institutesMarketFilterBoard" class="form-select">
        <option value="">All boards</option>
        @foreach ($boards as $board)
          <option value="{{ $board }}" @selected((string) request('board') === (string) $board)>{{ $board }}</option>
        @endforeach
      </select>
    </div>

    <div class="vendors-filter-group">
      <label for="institutesMarketFilterLocation">Your location</label>
      <div class="vendors-filter-location-wrap">
        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
        <input id="institutesMarketFilterLocation" class="form-control" type="text" value="{{ $locationDisplay }}" readonly>
      </div>
      @unless ($hasLocation)
        <small class="vendors-location-note">Set your location from the header to enable distance filtering.</small>
      @endunless
    </div>

    <div class="vendors-filter-group">
      <label for="institutesMarketFilterRadius">Within</label>
      <select id="institutesMarketFilterRadius" class="form-select" @disabled(! $hasLocation)>
        <option value="">Any distance</option>
        @foreach ([5, 10, 25, 50, 100] as $km)
          <option value="{{ $km }}" @selected(request('radius') == $km)>{{ $km }} km</option>
        @endforeach
      </select>
    </div>

    <div class="vendors-filter-group">
      <label class="form-check">
        <input type="checkbox" class="form-check-input" id="institutesMarketFilterVerified" @checked(request()->boolean('verified'))>
        Verified only
      </label>
    </div>

    <button type="button" class="btn btn-primary w-100" id="institutesMarketApplyFilters">Apply filters</button>
  </div>
</aside>
