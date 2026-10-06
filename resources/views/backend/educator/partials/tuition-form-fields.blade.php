@php
  use App\Support\EducatorTuitionDelivery;
  $tuitionBatches = $tuitionBatches ?? [['class' => '', 'subject' => '', 'batch_type' => '', 'student_count' => '', 'cost' => '', 'seats_status' => 'available']];
  $availability = $availability ?? [['day' => '', 'slots' => '']];
  $tuitionDeliveryOptions = EducatorTuitionDelivery::normalizeStored($tuitionDeliveryOptions ?? null);
  $deliveryMeta = [
    'home' => ['icon' => 'fa-house', 'desc' => 'You visit the student\'s home for classes.'],
    'personal' => ['icon' => 'fa-user', 'desc' => 'One-on-one or small-group tuition at your place or theirs.'],
    'online' => ['icon' => 'fa-laptop', 'desc' => 'Live online classes (batch strength not required).'],
    'tuition_point' => ['icon' => 'fa-location-dot', 'desc' => 'Students come to your tuition centre or coaching point.'],
  ];
@endphp

<div class="edu-toggle-card mb-4">
  <input class="form-check-input" type="checkbox" name="take_tuitions" value="1" id="takeTuitions" @checked(old('take_tuitions', $educator->take_tuitions))>
  <div>
    <div class="edu-toggle-card__title">I take tuitions</div>
    <p class="edu-toggle-card__desc">When enabled, your public page can show a <strong>tutor profile</strong> with fees and batch details. When disabled, it appears as an <strong>experienced teacher profile</strong>.</p>
  </div>
</div>

<div class="edu-toggle-card mb-4">
  <input class="form-check-input" type="checkbox" name="is_available_now" value="1" id="takePrivateTuitions" @checked(old('is_available_now', $educator->is_available_now))>
  <div>
    <div class="edu-toggle-card__title">I take private tuitions</div>
    <p class="edu-toggle-card__desc">Show on your public profile that you are actively accepting private tuition students.</p>
  </div>
</div>

<div class="edu-profile-subsection">
  <div class="edu-profile-subsection__head">
    <div>
      <h4 class="edu-profile-subsection__title">Tuition options</h4>
      <p class="edu-profile-subsection__hint">Enable each mode you offer and add class, subject, board, fees, and timings.</p>
    </div>
  </div>

  <div class="edu-delivery-options">
    @foreach(EducatorTuitionDelivery::MODES as $deliveryKey => $deliveryLabel)
      @php
        $deliveryRow = $tuitionDeliveryOptions[$deliveryKey] ?? ['enabled' => false, 'offerings' => []];
        $deliveryEnabled = (bool) old('tuition_delivery_options.'.$deliveryKey.'.enabled', $deliveryRow['enabled'] ?? false);
        $offerings = old('tuition_delivery_options.'.$deliveryKey.'.offerings', $deliveryRow['offerings'] ?? []);
        if (! is_array($offerings) || $offerings === []) {
          $offerings = [EducatorTuitionDelivery::emptyOffering($deliveryKey)];
        }
        $meta = $deliveryMeta[$deliveryKey] ?? ['icon' => 'fa-circle', 'desc' => ''];
      @endphp
      <div class="edu-delivery-option" data-delivery-option="{{ $deliveryKey }}">
        <div class="edu-toggle-card edu-delivery-option__toggle">
          <input
            class="form-check-input js-delivery-option-toggle"
            type="checkbox"
            name="tuition_delivery_options[{{ $deliveryKey }}][enabled]"
            value="1"
            id="tuitionDelivery{{ ucfirst($deliveryKey) }}"
            @checked($deliveryEnabled)
          >
          <div>
            <div class="edu-toggle-card__title">
              <i class="fa-solid {{ $meta['icon'] }} me-1" aria-hidden="true"></i>
              {{ $deliveryLabel }}
            </div>
            <p class="edu-toggle-card__desc">{{ $meta['desc'] }}</p>
          </div>
        </div>
        <div class="edu-delivery-option__fields {{ $deliveryEnabled ? '' : 'd-none' }}">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-semibold text-muted text-uppercase">Offerings</span>
            <button type="button" class="btn btn-sm btn-outline-primary edu-btn-add js-add-delivery-offering" data-delivery-key="{{ $deliveryKey }}">
              <i class="fa-solid fa-plus"></i> Add row
            </button>
          </div>
          <div class="edu-delivery-offerings-wrap" id="deliveryOfferings{{ ucfirst($deliveryKey) }}" data-delivery-key="{{ $deliveryKey }}" data-online="{{ $deliveryKey === 'online' ? '1' : '0' }}">
            @foreach($offerings as $oi => $offering)
              @include('backend.educator.partials.tuition-delivery-offering-row', [
                'deliveryKey' => $deliveryKey,
                'index' => $oi,
                'offering' => is_array($offering) ? $offering : EducatorTuitionDelivery::emptyOffering($deliveryKey),
              ])
            @endforeach
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>

<div class="edu-profile-subsection">
  <div class="edu-profile-subsection__head">
    <div>
      <h4 class="edu-profile-subsection__title">Tuition batches</h4>
      <p class="edu-profile-subsection__hint">Group batches with seat availability for your public profile.</p>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary edu-btn-add" data-add="#tuitionBatchesWrap" data-template="tuitionBatch">
      <i class="fa-solid fa-plus"></i> Add batch
    </button>
  </div>

  <div class="tuition-batches-table d-none d-md-grid text-muted small fw-semibold px-3 py-2 mb-2">
    <span>Class</span><span>Subject</span><span>Batch type</span><span>Students</span><span>Cost</span><span>Seats</span><span></span>
  </div>

  <div id="tuitionBatchesWrap" class="tuition-batches-wrap">
    @foreach($tuitionBatches as $i => $batch)
      @php
        $seats = old('tuition_batches.'.$i.'.seats_status', $batch['seats_status'] ?? 'available');
        if (! in_array($seats, ['available', 'full'], true)) {
          $seats = 'available';
        }
      @endphp
      <div class="tuition-batch-card js-repeat-row">
        <div class="tuition-batch-card__grid">
          <div>
            <label class="form-label d-md-none">Class</label>
            <input type="text" name="tuition_batches[{{ $i }}][class]" class="form-control" placeholder="Class 10" value="{{ $batch['class'] ?? '' }}">
          </div>
          <div>
            <label class="form-label d-md-none">Subject</label>
            <input type="text" name="tuition_batches[{{ $i }}][subject]" class="form-control" placeholder="Physics" value="{{ $batch['subject'] ?? '' }}">
          </div>
          <div>
            <label class="form-label d-md-none">Batch type</label>
            <input type="text" name="tuition_batches[{{ $i }}][batch_type]" class="form-control" placeholder="Small group" value="{{ $batch['batch_type'] ?? '' }}" list="tuitionBatchTypeOptions">
          </div>
          <div>
            <label class="form-label d-md-none">Students</label>
            <input type="number" name="tuition_batches[{{ $i }}][student_count]" class="form-control" min="1" placeholder="8" value="{{ $batch['student_count'] ?? '' }}">
          </div>
          <div>
            <label class="form-label d-md-none">Cost</label>
            <input type="text" name="tuition_batches[{{ $i }}][cost]" class="form-control" placeholder="₹500 / month" value="{{ $batch['cost'] ?? '' }}">
          </div>
          <div>
            <label class="form-label d-md-none">Seats</label>
            <select name="tuition_batches[{{ $i }}][seats_status]" class="form-select">
              <option value="available" @selected($seats === 'available')>Seats available</option>
              <option value="full" @selected($seats === 'full')>Batch full</option>
            </select>
          </div>
          <div class="tuition-batch-card__actions">
            <button type="button" class="btn btn-outline-danger edu-btn-remove w-100 js-remove-row" title="Remove batch">&times;</button>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <datalist id="tuitionBatchTypeOptions">
    <option value="1-on-1"></option>
    <option value="Small group"></option>
    <option value="Crash course"></option>
    <option value="Weekend batch"></option>
    <option value="Online batch"></option>
  </datalist>
</div>

<div class="row g-3 mt-1">
  <div class="col-12">
    <label class="form-label" for="tuition_point_address">Tuition point address</label>
    <input
      type="text"
      id="tuition_point_address"
      name="tuition_point_address"
      class="form-control"
      value="{{ old('tuition_point_address', $educator->tuition_point_address ?: $educator->tuition_location) }}"
      placeholder="Start typing your tuition centre or home address"
      autocomplete="off"
    >
    <input type="hidden" id="tuition_place_id" name="tuition_place_id" value="{{ old('tuition_place_id', $educator->tuition_place_id) }}">
    <input type="hidden" id="tuition_latitude" name="tuition_latitude" value="{{ old('tuition_latitude', $educator->tuition_latitude) }}">
    <input type="hidden" id="tuition_longitude" name="tuition_longitude" value="{{ old('tuition_longitude', $educator->tuition_longitude) }}">
    <small class="text-muted">Used on your public tutor profile map when you offer tuition at a fixed point.</small>
  </div>
  <div class="col-md-6">
    <label class="form-label">General tuition timings</label>
    <input type="text" name="tuition_timings" class="form-control" value="{{ old('tuition_timings', $educator->tuition_timings) }}" placeholder="Weekdays evenings, Saturday mornings">
  </div>
  <div class="col-12">
    <label class="form-label">Additional fee notes</label>
    <input type="text" name="tuition_charges" class="form-control" value="{{ old('tuition_charges', $educator->tuition_charges) }}" placeholder="Trial class, registration fee, package details">
  </div>
</div>

<div class="edu-profile-subsection">
  <div class="edu-profile-subsection__head">
    <div>
      <h4 class="edu-profile-subsection__title">Availability</h4>
      <p class="edu-profile-subsection__hint">When you're available for classes or consultations.</p>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary edu-btn-add" data-add="#availabilityWrap" data-template="availability">
      <i class="fa-solid fa-plus"></i> Add slot
    </button>
  </div>
  <div class="edu-repeat-table-head edu-repeat-table-head--availability">
    <span>Day</span><span>Time slots</span><span></span>
  </div>
  <div id="availabilityWrap">
    @foreach($availability as $i => $row)
      <div class="edu-repeat-row edu-repeat-row--availability js-repeat-row">
        <input type="text" name="availability[{{ $i }}][day]" class="form-control" placeholder="Monday" value="{{ $row['day'] ?? '' }}">
        <input type="text" name="availability[{{ $i }}][slots]" class="form-control" placeholder="4:00 PM – 7:00 PM" value="{{ $row['slots'] ?? '' }}">
        <button type="button" class="btn btn-outline-danger edu-btn-remove js-remove-row" title="Remove">&times;</button>
      </div>
    @endforeach
  </div>
</div>
