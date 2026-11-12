{{-- The scholarship cards of one load (the page, or a batch while filtering / scrolling) --}}
@forelse($items as $item)
  @include('frontend.education-hub.partials.scholarship-card', ['item' => $item])
@empty
  @include('frontend.education-hub.partials.listing-empty', ['page' => $page])
@endforelse
