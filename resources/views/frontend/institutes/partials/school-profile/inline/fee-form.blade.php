@php
  $listingContext = $listingContext ?? 'schools';
  $fee = \App\Support\InstituteFeeStructure::fromMeta(data_get($institute->profile_meta, 'fee_structure'))
      ?? \App\Support\InstituteFeeStructure::clean([]);
  $rowsText = '';
  if (!empty($fee['tables'][0]['rows'])) {
    $lines = [];
    foreach ($fee['tables'][0]['rows'] as $row) {
      $amount = $row['values'][0] ?? '';
      $lines[] = $row['label'].($amount !== '' ? ' : '.$amount : '');
    }
    $rowsText = implode("\n", $lines);
  }
  $hasFile = !empty($fee['file']);
@endphp
<form id="instFeeForm" class="sch-portal-form" novalidate enctype="multipart/form-data"
      data-store-url="{{ route($listingContext.'.inline.fee.update', $institute->slug) }}">
  @csrf
  <input type="hidden" name="_method" value="PUT">
  <div class="alert d-none mb-3" role="alert"></div>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" maxlength="255" value="{{ $fee['title'] }}" placeholder="Fee Structure">
    </div>
    <div class="col-md-3">
      <label class="form-label">Session</label>
      <input type="text" name="session" class="form-control" maxlength="255" value="{{ $fee['session'] }}" placeholder="2025-26">
    </div>
    <div class="col-md-3">
      <label class="form-label">Effective from</label>
      <input type="date" name="effective_from" class="form-control" value="{{ $fee['effective_from'] }}">
    </div>
    <div class="col-12">
      <label class="form-label">Intro</label>
      <textarea name="intro" class="form-control" rows="2" maxlength="5000" placeholder="Optional note shown above the fee table">{{ $fee['intro'] }}</textarea>
    </div>
    <div class="col-12">
      <label class="form-label">Fee rows</label>
      <textarea name="rows_text" class="form-control" rows="7" placeholder="One per line, e.g.&#10;Tuition Fee : 50000&#10;Admission Fee : 10000">{{ $rowsText }}</textarea>
      <p class="form-text mb-0">One fee per line as <code>Label : Amount</code>.</p>
    </div>
    <div class="col-12">
      <label class="form-label">Fee document (PDF / image, optional)</label>
      <input type="file" name="fee_file" class="form-control" accept="application/pdf,image/jpeg,image/png,image/webp">
      @if($hasFile)
        <div class="form-check mt-2">
          <input type="checkbox" class="form-check-input" name="remove_fee_file" value="1" id="instFeeRemoveFile">
          <label class="form-check-label" for="instFeeRemoveFile">Remove current document</label>
        </div>
      @endif
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary js-inst-submit-btn">
        <span class="btn-text"><i class="fa-solid fa-floppy-disk me-1"></i> Save fee structure</span>
        <span class="btn-loader d-none" aria-hidden="true"></span>
      </button>
    </div>
  </div>
</form>
