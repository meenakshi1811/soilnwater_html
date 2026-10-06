@php
  $deliveryKey = $deliveryKey ?? 'home';
  $index = $index ?? 0;
  $offering = $offering ?? \App\Support\EducatorTuitionDelivery::emptyOffering($deliveryKey);
  $showBatchStrength = $deliveryKey !== 'online';
  $prefix = 'tuition_delivery_options.'.$deliveryKey.'.offerings.'.$index;
  $enrolOpen = filter_var(old($prefix.'.enrolment_open', $offering['enrolment_open'] ?? true), FILTER_VALIDATE_BOOLEAN);
@endphp
<div class="edu-delivery-offering js-repeat-row" data-delivery-offering-row>
  <div class="edu-delivery-offering__grid">
    <div>
      <label class="form-label d-md-none">Class</label>
      <input type="text" name="tuition_delivery_options[{{ $deliveryKey }}][offerings][{{ $index }}][class]" class="form-control" placeholder="Class" value="{{ old($prefix.'.class', $offering['class'] ?? '') }}">
    </div>
    <div>
      <label class="form-label d-md-none">Subject</label>
      <input type="text" name="tuition_delivery_options[{{ $deliveryKey }}][offerings][{{ $index }}][subject]" class="form-control" placeholder="Subject" value="{{ old($prefix.'.subject', $offering['subject'] ?? '') }}">
    </div>
    <div>
      <label class="form-label d-md-none">Board</label>
      <input type="text" name="tuition_delivery_options[{{ $deliveryKey }}][offerings][{{ $index }}][board]" class="form-control" placeholder="Board" value="{{ old($prefix.'.board', $offering['board'] ?? '') }}">
    </div>
    @if($showBatchStrength)
      <div>
        <label class="form-label d-md-none">Students per batch</label>
        <input type="text" name="tuition_delivery_options[{{ $deliveryKey }}][offerings][{{ $index }}][batch_strength]" class="form-control" placeholder="e.g. 8" value="{{ old($prefix.'.batch_strength', $offering['batch_strength'] ?? '') }}">
      </div>
    @endif
    <div>
      <label class="form-label d-md-none">Fee / charges</label>
      <input type="text" name="tuition_delivery_options[{{ $deliveryKey }}][offerings][{{ $index }}][fee]" class="form-control" placeholder="₹ / month" value="{{ old($prefix.'.fee', $offering['fee'] ?? '') }}">
    </div>
    <div>
      <label class="form-label d-md-none">Batch timings</label>
      <input type="text" name="tuition_delivery_options[{{ $deliveryKey }}][offerings][{{ $index }}][timings]" class="form-control" placeholder="Weekdays 5–8 PM" value="{{ old($prefix.'.timings', $offering['timings'] ?? '') }}">
    </div>
    <div class="edu-delivery-offering__enrol">
      <label class="form-label d-md-none">Enrolment</label>
      <label class="edu-availability-pill mb-0 h-100">
        <input class="form-check-input mt-0 me-2" type="checkbox" name="tuition_delivery_options[{{ $deliveryKey }}][offerings][{{ $index }}][enrolment_open]" value="1" @checked($enrolOpen)>
        <span>Enrolment open</span>
      </label>
    </div>
    <div class="edu-delivery-offering__note">
      <label class="form-label d-md-none">Note</label>
      <input type="text" name="tuition_delivery_options[{{ $deliveryKey }}][offerings][{{ $index }}][note]" class="form-control" placeholder="Optional note" value="{{ old($prefix.'.note', $offering['note'] ?? '') }}">
    </div>
    <div class="edu-delivery-offering__actions">
      <button type="button" class="btn btn-outline-danger edu-btn-remove w-100 js-remove-row" title="Remove">&times;</button>
    </div>
  </div>
</div>
