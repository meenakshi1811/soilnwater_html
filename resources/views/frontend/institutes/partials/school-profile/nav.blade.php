@php
  // On another page (the diary) the section links and Enquire Now go back to the profile
  $sectionBaseUrl = $sectionBaseUrl ?? null;
@endphp
<aside class="sch-nav" aria-label="School profile sections">
  <div class="sch-nav__inner">
    <ul class="sch-nav__list">
      @foreach($navItems as $loopIndex => $item)
        @php
          $activeNavId = $activeNavId ?? null;
          $linkActive = $activeNavId
            ? $activeNavId === $item['id']
            : ($loopIndex === 0 && empty($item['href']));
          $onThisPage = empty($item['href']) && ! $sectionBaseUrl;
        @endphp
        <li>
          <a
            href="{{ $item['href'] ?? ($sectionBaseUrl ? $sectionBaseUrl.'#'.$item['id'] : '#'.$item['id']) }}"
            class="sch-nav__link {{ $onThisPage ? 'js-sch-nav-link' : 'sch-nav__link--page' }} {{ $linkActive ? 'is-active' : '' }}"
            @if($linkActive) aria-current="page" @endif
          >
            <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
            {{ $item['label'] }}
          </a>
        </li>
      @endforeach
    </ul>
    <div class="sch-nav__actions">
      @if($sectionBaseUrl)
        <a href="{{ $sectionBaseUrl }}#sch-contact" class="sch-btn sch-btn-primary">Enquire Now</a>
      @else
        <button type="button" class="sch-btn sch-btn-primary js-sch-open-enquiry">Enquire Now</button>
      @endif
    </div>
  </div>
</aside>
