@php
  $experienceRows = array_values($educator->experiences ?? []) ?: [[]];
  $qualificationRows = array_values($educator->qualifications ?? []) ?: [[]];
@endphp
<form class="edu-inline-form" novalidate data-save-url="{{ route('educator.inline.update', [$educator->slug, 'experience']) }}">
  @csrf
  <input type="hidden" name="_method" value="PUT">
  <div class="alert d-none mb-3" role="alert"></div>

  <div class="row g-4">
    <div class="col-lg-7 js-edu-row-group">
      <h6 class="fw-bold mb-2">Experience</h6>
      <div class="edu-inline-rows js-edu-rows" data-next-index="{{ count($experienceRows) }}">
        @foreach($experienceRows as $i => $exp)
          @include('frontend.educator.partials.inline.experience-row', ['i' => $i, 'exp' => $exp])
        @endforeach
      </div>
      <template class="js-edu-row-template">
        @include('frontend.educator.partials.inline.experience-row', ['i' => '__i__', 'exp' => []])
      </template>
      <button type="button" class="btn btn-sm btn-outline-primary js-edu-row-add"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i> Add experience</button>
    </div>

    <div class="col-lg-5 js-edu-row-group">
      <h6 class="fw-bold mb-2">Education</h6>
      <div class="edu-inline-rows js-edu-rows" data-next-index="{{ count($qualificationRows) }}">
        @foreach($qualificationRows as $i => $qual)
          @include('frontend.educator.partials.inline.qualification-row', ['i' => $i, 'qual' => $qual])
        @endforeach
      </div>
      <template class="js-edu-row-template">
        @include('frontend.educator.partials.inline.qualification-row', ['i' => '__i__', 'qual' => []])
      </template>
      <button type="button" class="btn btn-sm btn-outline-primary js-edu-row-add"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i> Add qualification</button>
    </div>
  </div>

  @include('frontend.educator.partials.inline.save-button')
</form>
