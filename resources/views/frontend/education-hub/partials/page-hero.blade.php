{{-- The header of a hub listing page (courses, jobs): a tinted banner with its illustration, the
     title and text, a search (keeps the open tab) and a few numbers.
     $tone: blue | purple; $art: the illustration (assets/images/education-hub/art-{art}.webp);
     $title, $text; $action: the page's URL; $search: the words searched for; $placeholder;
     $kind: the open tab (or null); $stats: list of [icon, number, label]. --}}
<header class="eh-listhero eh-listhero--{{ $tone }}">
  <div class="eh-listhero__copy">
    <h1 class="eh-listhero__title">{{ $title }}</h1>
    <p class="eh-listhero__text">{{ $text }}</p>

    <form class="eh-listhero__search" action="{{ $action }}" method="GET" role="search">
      <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
      <label class="visually-hidden" for="ehListSearch">{{ $placeholder }}</label>
      <input type="search" id="ehListSearch" name="q" value="{{ $search }}" placeholder="{{ $placeholder }}" autocomplete="off">
      @if($kind)
        <input type="hidden" name="kind" value="{{ $kind }}">
      @endif
      <button type="submit">Search</button>
    </form>

    @if($stats !== [])
      <ul class="eh-listhero__stats">
        @foreach($stats as [$icon, $number, $label])
          <li><i class="fa-solid {{ $icon }}" aria-hidden="true"></i> <strong>{{ number_format($number) }}</strong> {{ $label }}</li>
        @endforeach
      </ul>
    @endif
  </div>
  <img src="{{ asset('assets/images/education-hub/art-'.$art.'.webp') }}" alt="" class="eh-listhero__art" width="130" height="150">
</header>
