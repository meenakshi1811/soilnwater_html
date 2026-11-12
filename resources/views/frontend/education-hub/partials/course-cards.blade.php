{{-- The course cards of one load (the page, or a batch while filtering / scrolling) --}}
@forelse($courses as $course)
  @include('frontend.education-hub.partials.course-card', ['course' => $course])
@empty
  <div class="vendors-empty-state">
    <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-book-open"></i></div>
    <h3>No courses found</h3>
    <p>Try another search or clear the filters. Colleges, universities and institutes add their courses on their profiles.</p>
  </div>
@endforelse
