<aside class="sch-nav" aria-label="School profile sections">
  <div class="sch-nav__inner">
    <ul class="sch-nav__list">
      @foreach($navItems as $loopIndex => $item)
        @php
          $activeNavId = $activeNavId ?? null;
          $linkActive = $activeNavId
            ? $activeNavId === $item['id']
            : ($loopIndex === 0 && empty($item['href']));
        @endphp
        <li>
          <a
            href="{{ $item['href'] ?? '#'.$item['id'] }}"
            class="sch-nav__link {{ empty($item['href']) ? 'js-sch-nav-link' : 'sch-nav__link--page' }} {{ $linkActive ? 'is-active' : '' }}"
            @if($linkActive) aria-current="page" @endif
          >
            <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
            {{ $item['label'] }}
          </a>
        </li>
      @endforeach
    </ul>
    <div class="sch-nav__actions">
      <button type="button" class="sch-btn sch-btn-primary js-sch-open-enquiry">Enquire Now</button>
    </div>
  </div>
</aside>
