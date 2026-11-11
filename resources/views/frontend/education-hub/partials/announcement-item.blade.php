{{-- One notice in the Announcements & Updates box: an icon by its topic (else by who posted it),
     title, who and when. $copy: the ticker's second copy (hidden from screen readers and the keyboard). --}}
@php
  [, $kindIcon, $kindTone, $kindOne] = \App\Http\Controllers\Frontend\EducationHubController::NOTICE_KINDS[$announcement['kind']];
  if (! empty($announcement['topic'])) {
      [, $kindIcon, $kindTone] = $announcement['topic'];
  }
  $copy = $copy ?? false;
@endphp
<li>
  <a href="{{ $announcement['url'] }}" class="eh-announcement" @if($copy) tabindex="-1" @endif>
    <span class="eh-icon eh-icon--square eh-icon--{{ $kindTone }}"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i></span>
    <span class="eh-announcement__body">
      <strong>{{ $announcement['title'] }}</strong>
      <small>
        <span class="eh-announcement__source" title="{{ $kindOne }}">{{ $announcement['source'] }}</span>
        @if($announcement['date'])
          <span class="eh-announcement__sep" aria-hidden="true">|</span>
          <time datetime="{{ $announcement['date']->toDateString() }}">{{ $announcement['date']->format('d M Y') }}</time>
        @endif
      </small>
    </span>
  </a>
</li>
