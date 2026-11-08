@php
  $subjectRows = \App\Support\EducatorSubjects::forForm($educator->subjects);
@endphp
<form class="edu-inline-form" novalidate data-save-url="{{ route('educator.inline.update', [$educator->slug, 'subjects']) }}">
  @csrf
  <input type="hidden" name="_method" value="PUT">
  <div class="alert d-none mb-3" role="alert"></div>
  <p class="text-secondary small">Each subject shows as a card with its classes and boards. Classes and boards: one per line.</p>

  <div class="edu-inline-rows js-edu-rows" data-next-index="{{ count($subjectRows) }}">
    @foreach($subjectRows as $i => $subject)
      @include('frontend.educator.partials.inline.subject-row', ['i' => $i, 'subject' => $subject])
    @endforeach
  </div>
  <template class="js-edu-row-template">
    @include('frontend.educator.partials.inline.subject-row', ['i' => '__i__', 'subject' => \App\Support\EducatorSubjects::emptyRow()])
  </template>
  <button type="button" class="btn btn-sm btn-outline-primary js-edu-row-add"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i> Add subject</button>

  @include('frontend.educator.partials.inline.save-button')
</form>
