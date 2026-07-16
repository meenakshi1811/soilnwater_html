@extends('backend.layouts.app')

@section('title', 'Services')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/admin-service-page-review.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Service Management</p>
            <h2 class="admin-title mb-1">Services</h2>
            <p class="mb-0 text-secondary">Review service registrations, approve accounts, and manage company profiles.</p>
        </div>
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="service_providersTable" class="table table-bordered align-middle w-100">
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

<div class="modal fade" id="service_providerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="service_providerForm" method="POST" action="#" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company name</label>
                            <input type="text" name="company_name" id="service_providerCompany" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact person</label>
                            <input type="text" name="contact_person" id="service_providerContact" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service slug</label>
                            <input type="text" name="slug" id="service_providerSlug" class="form-control" required>
                            <small class="text-muted">/service/<span id="slugPreview">slug</span></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="service_providerStatus" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="service_providerPhone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" id="service_providerWhatsapp" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="service_providerEmail" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="service_providerCity" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" id="service_providerState" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" id="service_providerPincode" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" id="service_providerAddress" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PAN</label>
                            <input type="text" name="pan_number" id="service_providerPan" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Do you have a GST number?</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input js-service_provider-has-gst" type="radio" name="has_gst" id="service_providerHasGstNo" value="0" checked>
                                <label class="form-check-label" for="service_providerHasGstNo">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input js-service_provider-has-gst" type="radio" name="has_gst" id="service_providerHasGstYes" value="1">
                                <label class="form-check-label" for="service_providerHasGstYes">Yes</label>
                            </div>
                        </div>
                        <div class="col-md-6 d-none" id="service_providerGstWrap">
                            <label class="form-label">GST</label>
                            <input type="text" name="gst_number" id="service_providerGst" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Any Other Government Certificate Number</label>
                            <input type="text" name="government_certificate_number" id="service_providerGovernmentCertificate" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="service_providerSubmitBtn" class="btn btn-primary ems-btn-primary">Save</button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/admin-service-providers.js') }}?v={{ now()->timestamp }}"></script>
@endpush
