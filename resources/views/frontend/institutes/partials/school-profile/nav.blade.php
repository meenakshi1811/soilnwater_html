<aside class="sch-nav" aria-label="School profile sections">
  <div class="sch-nav__inner">
    <ul class="sch-nav__list">
      @foreach($navItems as $loopIndex => $item)
        <li>
          <a
            href="#{{ $item['id'] }}"
            class="sch-nav__link js-sch-nav-link {{ $loopIndex === 0 ? 'is-active' : '' }}"
          >
            <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
            {{ $item['label'] }}
          </a>
        </li>
      @endforeach
    </ul>
    <div class="sch-nav__actions">
      <a href="#sch-contact" class="sch-btn sch-btn-primary js-sch-nav-link">Enquire Now</a>
      @if($institute->website_url)
        <a href="{{ $institute->website_url }}" target="_blank" rel="noopener" class="sch-btn sch-btn-outline">
          <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Visit Website
        </a>
      @endif
    </div>
  </div>
</aside>
