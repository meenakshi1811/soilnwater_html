{{-- The job cards of one load (the page, or a batch while filtering / scrolling) --}}
@forelse($items as $job)
  @include('frontend.education-hub.partials.job-card', ['job' => $job])
@empty
  @include('frontend.education-hub.partials.listing-empty', ['page' => $page])
@endforelse
