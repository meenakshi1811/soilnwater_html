@extends('backend.layouts.app')

@section('title', 'Consultants')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/admin-service-page-review.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Consultant Management</p>
            <h2 class="admin-title mb-1">Consultants</h2>
            <p class="mb-0 text-secondary">Review consultant registrations, approve accounts, and manage company profiles.</p>
        </div>
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="consultantsTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Company</th>
                    <th>Owner</th>
                    <th>Email</th>
                    <th>Phone / WhatsApp</th>
                    <th>Location</th>
                    <th>Account Status</th>
                    <th>Page Link</th>
                    <th>Premium</th>
                    <th>Registered</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="consultantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title">Edit Consultant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="consultantForm" method="POST" action="#" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company name</label>
                            <input type="text" name="company_name" id="consultantCompany" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact person</label>
                            <input type="text" name="contact_person" id="consultantContact" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Consultant slug</label>
                            <input type="text" name="slug" id="consultantSlug" class="form-control" required>
                            <small class="text-muted">/consultant/<span id="slugPreview">slug</span></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="consultantStatus" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="consultantPhone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" id="consultantWhatsapp" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="consultantEmail" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="consultantCity" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" id="consultantState" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" id="consultantPincode" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" id="consultantAddress" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PAN</label>
                            <input type="text" name="pan_number" id="consultantPan" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Do you have a GST number?</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input js-consultant-has-gst" type="radio" name="has_gst" id="consultantHasGstNo" value="0" checked>
                                <label class="form-check-label" for="consultantHasGstNo">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input js-consultant-has-gst" type="radio" name="has_gst" id="consultantHasGstYes" value="1">
                                <label class="form-check-label" for="consultantHasGstYes">Yes</label>
                            </div>
                        </div>
                        <div class="col-md-6 d-none" id="consultantGstWrap">
                            <label class="form-label">GST</label>
                            <input type="text" name="gst_number" id="consultantGst" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Any Other Government Certificate Number</label>
                            <input type="text" name="government_certificate_number" id="consultantGovernmentCertificate" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="consultantSubmitBtn" class="btn btn-primary ems-btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/admin-consultants.js') }}?v={{ now()->timestamp }}"></script>
@endpush
