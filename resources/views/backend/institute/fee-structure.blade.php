@extends('backend.layouts.app')

@section('title', 'Fee Structure')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@php
  $feeFileUrl = $fee['file'] ? asset($fee['file']) : null;
  $suggestedSections = ['Payment schedule', 'Late fee', 'Refund policy', 'Bank details for fee deposit', 'Transport fee', 'Notes'];
@endphp
<div class="admin-panel ems-page institute-portal">
  @includeWhen($managedInstitute, 'backend.institute.partials.admin-manage-bar')

  <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
      <p class="ems-kicker mb-1">{{ $portalLabel }} admission</p>
      <h2 class="admin-title mb-1">Fee structure</h2>
      <p class="mb-0 text-secondary">Shown on its own page, linked from “View fee structure” in the Admission info popup of your public profile.</p>
    </div>
    @if($institute->isApproved() && ($fee['tables'] !== [] || $fee['sections'] !== [] || $fee['file']))
      <a href="{{ $institute->publicFeeStructureUrl() }}" target="_blank" class="btn btn-outline-primary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View fee structure page
      </a>
    @endif
  </div>

  <div id="feeStructureAlert" class="alert d-none" role="alert"></div>

  <form id="feeStructureForm" method="POST" action="{{ $portalRoute('fee-structure.update') }}" enctype="multipart/form-data" class="chart-card">
    @csrf
    @method('PUT')
    <input type="hidden" name="tables_json" id="feeTablesJson" value="">

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label" for="fee_title">Title</label>
        <input type="text" class="form-control" id="fee_title" name="title" value="{{ $fee['title'] }}" maxlength="120" placeholder="e.g. Fee Structure">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="fee_session">Session</label>
        <input type="text" class="form-control" id="fee_session" name="session" value="{{ $fee['session'] }}" maxlength="40" placeholder="e.g. 2025–26">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="fee_effective_from">Effective from</label>
        <input type="date" class="form-control" id="fee_effective_from" name="effective_from" value="{{ $fee['effective_from'] }}">
      </div>
      <div class="col-12">
        <label class="form-label" for="fee_intro">Introduction</label>
        <textarea class="form-control" id="fee_intro" name="intro" rows="2" maxlength="2000" placeholder="e.g. Fees for day scholars. Hostel fees are shared on request.">{{ $fee['intro'] }}</textarea>
      </div>
    </div>

    <section class="inst-detail-group">
      <h3 class="inst-detail-group__title">Fee tables</h3>
      <p class="form-text mt-0 mb-2">Each table has its own columns and rows: e.g. fee heads down the side and classes across, or one row per class with its monthly fee. Write amounts as you like (2500, 2,500, NA); the total row adds up the numbers.</p>
      <div id="feeTables" class="fee-tables"></div>
      <div class="fee-table-add">
        <span class="text-secondary small me-1">Add a table:</span>
        @foreach($presets as $presetKey => $preset)
          <button type="button" class="btn btn-sm btn-outline-secondary js-fee-add-table" data-preset="{{ $presetKey }}">+ {{ $preset['label'] }}</button>
        @endforeach
      </div>
    </section>

    <section class="inst-detail-group">
      <h3 class="inst-detail-group__title">Sections</h3>
      <p class="form-text mt-0 mb-2">Text under the tables: payment schedule and due dates, late fee, refunds, bank details…</p>
      <div class="inst-repeater" data-repeater>
        <div class="inst-repeater__list js-repeater-list" data-next-index="{{ count($fee['sections']) }}">
          @foreach($fee['sections'] as $sectionIndex => $section)
            @include('backend.institute.partials.fee-section-row', ['index' => $sectionIndex, 'section' => $section])
          @endforeach
        </div>
        <template class="js-repeater-template">
          @include('backend.institute.partials.fee-section-row', ['index' => '__INDEX__', 'section' => ['heading' => '', 'text' => '']])
        </template>
        <div class="fee-table-add mt-2">
          <span class="text-secondary small me-1">Add a section:</span>
          @foreach($suggestedSections as $heading)
            <button type="button" class="btn btn-sm btn-outline-secondary js-repeater-add" data-heading="{{ $heading }}">+ {{ $heading }}</button>
          @endforeach
        </div>
      </div>
    </section>

    <section class="inst-detail-group mb-0">
      <h3 class="inst-detail-group__title">Fee structure file</h3>
      <p class="form-text mt-0 mb-2">Already have a fee chart? Upload it as a PDF or an image (up to 10 MB); it shows on the page with a download button.</p>
      @if($feeFileUrl)
        <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
          <a href="{{ $feeFileUrl }}" target="_blank" rel="noopener"><i class="fa-solid fa-file-lines me-1"></i>Current file</a>
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" value="1" id="remove_fee_file" name="remove_fee_file">
            <label class="form-check-label" for="remove_fee_file">Remove it</label>
          </div>
        </div>
      @endif
      <input type="file" class="form-control" id="fee_file" name="fee_file" accept="application/pdf,image/jpeg,image/png,image/webp">
    </section>

    <div class="d-flex justify-content-end gap-2 mt-4">
      <button type="submit" class="btn btn-primary" id="feeStructureSubmitBtn">Save fee structure</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
  window.instituteFeeStructure = {
    tables: @json($fee['tables']),
    presets: @json(array_map(fn ($preset) => $preset['table'], $presets)),
  };
</script>
<script src="{{ asset('assets/js/institute-fee-structure.js') }}?v={{ now()->timestamp }}"></script>
@endpush
