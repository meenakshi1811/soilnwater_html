{{-- A row in a page-details list ($kind; $index numbers the fields, __INDEX__ in the template) --}}
@php
  $isLine = array_key_exists($kind, \App\Support\InstituteProfileDetails::LINE_LISTS);
  $name = fn (string $field) => 'details['.$kind.']['.$index.']['.$field.']';
  $value = fn (string $field) => is_array($row) ? (string) ($row[$field] ?? '') : '';
@endphp
@if($isLine)
  <div class="input-group input-group-sm js-repeater-row">
    <input type="text" class="form-control" name="details[{{ $kind }}][]" value="{{ is_string($row) ? $row : '' }}" maxlength="{{ \App\Support\InstituteProfileDetails::LINE_LISTS[$kind][1] }}" aria-label="Line">
    <button type="button" class="btn btn-outline-danger js-repeater-remove" aria-label="Remove">&times;</button>
  </div>
@else
  <div class="inst-detail-row js-repeater-row">
    <div class="row g-2 flex-grow-1">
      @switch($kind)
        @case('quick_stats')
          <div class="col-sm-4">
            <select class="form-select form-select-sm" name="{{ $name('icon') }}" aria-label="Icon">
              @foreach(\App\Support\InstituteProfileDetails::STAT_ICONS as $iconClass => $iconName)
                <option value="{{ $iconClass }}" @selected($value('icon') === $iconClass)>{{ $iconName }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" name="{{ $name('value') }}" value="{{ $value('value') }}" maxlength="60" placeholder="Value, e.g. 6 Labs" aria-label="Value">
          </div>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" name="{{ $name('label') }}" value="{{ $value('label') }}" maxlength="60" placeholder="Label, e.g. Laboratories" aria-label="Label">
          </div>
          @break
        @case('faculty')
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" name="{{ $name('name') }}" value="{{ $value('name') }}" maxlength="120" placeholder="Name *" aria-label="Name">
          </div>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" name="{{ $name('role') }}" value="{{ $value('role') }}" maxlength="120" placeholder="Role, e.g. Principal" aria-label="Role">
          </div>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" name="{{ $name('subject') }}" value="{{ $value('subject') }}" maxlength="120" placeholder="Subject" aria-label="Subject">
          </div>
          @break
        @case('events')
          <div class="col-sm-6">
            <input type="text" class="form-control form-control-sm" name="{{ $name('title') }}" value="{{ $value('title') }}" maxlength="160" placeholder="Event, e.g. Annual Sports Day *" aria-label="Event">
          </div>
          <div class="col-sm-3">
            <input type="date" class="form-control form-control-sm" name="{{ $name('date') }}" value="{{ $value('date') }}" aria-label="Date">
          </div>
          <div class="col-sm-3">
            <input type="text" class="form-control form-control-sm" name="{{ $name('time') }}" value="{{ $value('time') }}" maxlength="40" placeholder="Time, e.g. 10:00 AM" aria-label="Time">
          </div>
          @break
        @case('reviews')
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" name="{{ $name('name') }}" value="{{ $value('name') }}" maxlength="120" placeholder="Name *" aria-label="Reviewer's name">
          </div>
          <div class="col-sm-3">
            <input type="text" class="form-control form-control-sm" name="{{ $name('relation') }}" value="{{ $value('relation') }}" maxlength="60" placeholder="e.g. Parent" aria-label="Relation">
          </div>
          <div class="col-sm-2">
            <select class="form-select form-select-sm" name="{{ $name('rating') }}" aria-label="Stars">
              @foreach([5, 4, 3, 2, 1] as $stars)
                <option value="{{ $stars }}" @selected((int) ($value('rating') ?: 5) === $stars)>{{ $stars }} ★</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-3">
            <input type="text" class="form-control form-control-sm" name="{{ $name('ago') }}" value="{{ $value('ago') }}" maxlength="40" placeholder="When, e.g. 2 weeks ago" aria-label="When">
          </div>
          <div class="col-12">
            <textarea class="form-control form-control-sm" name="{{ $name('comment') }}" rows="2" maxlength="1000" placeholder="Review *" aria-label="Review">{{ $value('comment') }}</textarea>
          </div>
          @break
      @endswitch
    </div>
    <button type="button" class="btn btn-sm btn-outline-danger js-repeater-remove" aria-label="Remove">&times;</button>
  </div>
@endif
