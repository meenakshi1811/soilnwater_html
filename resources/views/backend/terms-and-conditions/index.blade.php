@extends('backend.layouts.app')

@section('title', 'Terms & Conditions')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Admin CMS</p>
            <h2 class="admin-title mb-1">Terms &amp; Conditions</h2>
            <p class="mb-0 text-secondary">Create one terms and conditions entry for each module and manage them from this table.</p>
        </div>
        <button type="button" class="btn btn-primary ems-btn-primary" id="openTermsModalBtn">
            <i class="fa-solid fa-file-circle-plus me-2"></i> Add Terms &amp; Conditions
        </button>
    </div>

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">Terms and conditions listing</h5>
        </div>
        <div id="termsAlert" class="alert d-none" role="alert"></div>
        <div class="table-responsive">
            <table id="termsTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Module</th>
                    <th>Content Preview</th>
                    <th>Last Updated</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalTitle">Add Terms &amp; Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="termsForm" method="POST" action="{{ route('admin.terms-and-conditions.store') }}" novalidate>
                @csrf
                <input type="hidden" id="termsId" name="terms_id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Module</label>
                            <select name="module_key" id="moduleKey" class="form-select">
                                <option value="">Select module</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Terms &amp; Conditions Content</label>
                            <textarea name="content" id="termsContent" class="form-control" rows="8"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="termsSubmitBtn" class="btn btn-primary ems-btn-primary">
                        <span class="btn-text">Save Terms</span>
                        <span class="btn-loader d-none" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/admin-terms-and-conditions.js') }}?v={{ now()->timestamp }}"></script>
@endpush
