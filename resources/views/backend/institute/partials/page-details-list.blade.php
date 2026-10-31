{{-- A page-details list: its rows, a template for "+ Add", and a hint (institute-profile.js) --}}
@php
  $isLine = array_key_exists($kind, \App\Support\InstituteProfileDetails::LINE_LISTS);
@endphp
<div class="inst-repeater" data-repeater>
  @if(!empty($hint))
    <p class="form-text mt-0 mb-2">{{ $hint }}</p>
  @endif
  <div class="inst-repeater__list {{ $isLine ? 'inst-repeater__list--lines' : '' }} js-repeater-list" data-next-index="{{ count($rows) }}">
    @foreach($rows as $rowIndex => $row)
      @include('backend.institute.partials.page-details-row', ['kind' => $kind, 'index' => $rowIndex, 'row' => $row])
    @endforeach
  </div>
  <template class="js-repeater-template">
    @include('backend.institute.partials.page-details-row', ['kind' => $kind, 'index' => '__INDEX__', 'row' => $isLine ? '' : []])
  </template>
  <button type="button" class="btn btn-sm btn-outline-secondary mt-2 js-repeater-add">+ {{ $addLabel }}</button>
</div>
