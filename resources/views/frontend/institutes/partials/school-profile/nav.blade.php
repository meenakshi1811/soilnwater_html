<aside class="sch-nav" aria-label="School profile sections">
  <div class="sch-nav__inner">
    <ul class="sch-nav__list">
      @foreach($navItems as $loopIndex => $item)
        <li>
          <a
            href="{{ $item['href'] ?? '#'.$item['id'] }}"
            class="sch-nav__link {{ empty($item['href']) ? 'js-sch-nav-link' : 'sch-nav__link--page' }} {{ $loopIndex === 0 && empty($item['href']) ? 'is-active' : '' }}"
            @if(! empty($item['href'])) aria-current="false" @endif
          >
            <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
            {{ $item['label'] }}
          </a>
        </li>
      @endforeach
    </ul>
    <div class="sch-nav__actions">
      <button type="button" class="sch-btn sch-btn-primary js-sch-open-enquiry">Enquire Now</button>
      @if($institute->website_url)
        <a href="{{ $institute->website_url }}" target="_blank" rel="noopener" class="sch-btn sch-btn-outline">
          <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Visit Website
        </a>
      @endif
    </div>
  </div>
</aside>
