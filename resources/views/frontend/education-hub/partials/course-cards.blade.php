{{-- The course cards of one load (the page, or a batch while filtering / scrolling) --}}
@forelse($items as $course)
  @include('frontend.education-hub.partials.course-card', ['course' => $course])
@empty
  @include('frontend.education-hub.partials.listing-empty', ['page' => $page])
@endforelse
