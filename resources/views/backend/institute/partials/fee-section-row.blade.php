{{-- A text section of the fee structure ($index numbers the fields, __INDEX__ in the template) --}}
<div class="inst-detail-row js-repeater-row">
  <div class="flex-grow-1 d-grid gap-2">
    <input type="text" class="form-control form-control-sm" name="sections[{{ $index }}][heading]" value="{{ $section['heading'] }}" maxlength="120" placeholder="Heading, e.g. Payment schedule" aria-label="Section heading">
    <textarea class="form-control form-control-sm" name="sections[{{ $index }}][text]" rows="4" maxlength="5000" placeholder="e.g. Fees are due by the 15th of each month. After that, a late fee of ₹100 applies." aria-label="Section text">{{ $section['text'] }}</textarea>
  </div>
  <button type="button" class="btn btn-sm btn-outline-danger js-repeater-remove" aria-label="Remove section">&times;</button>
</div>
