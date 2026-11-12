{{-- A hub listing with nothing to show: its icon, a title and a hint --}}
@php [$emptyIcon, $emptyTitle, $emptyText] = $page['empty']; @endphp
<div class="vendors-empty-state">
  <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid {{ $emptyIcon }}"></i></div>
  <h3>{{ $emptyTitle }}</h3>
  <p>{{ $emptyText }}</p>
</div>
