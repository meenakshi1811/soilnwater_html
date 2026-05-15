@extends('backend.layouts.app')

@section('title', 'Vendors')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Vendor Management</p>
            <h2 class="admin-title mb-1">Vendors</h2>
            <p class="mb-0 text-secondary">Review vendor registrations, approve accounts, and manage company profiles.</p>
        </div>
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="vendorsTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Company</th>
                    <th>Owner</th>
                    <th>Email</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="vendorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title">Edit Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="vendorForm" method="POST" action="#" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company name</label>
                            <input type="text" name="company_name" id="vendorCompany" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact person</label>
                            <input type="text" name="contact_person" id="vendorContact" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Store slug</label>
                            <input type="text" name="slug" id="vendorSlug" class="form-control" required>
                            <small class="text-muted">/store/<span id="slugPreview">slug</span></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="vendorStatus" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="vendorPhone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" id="vendorWhatsapp" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="vendorEmail" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="vendorCity" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" id="vendorState" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" id="vendorPincode" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" id="vendorAddress" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PAN</label>
                            <input type="text" name="pan_number" id="vendorPan" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GST</label>
                            <input type="text" name="gst_number" id="vendorGst" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="vendorSubmitBtn" class="btn btn-primary ems-btn-primary">Save</button>
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
<script src="{{ asset('assets/js/admin-vendors.js') }}?v={{ now()->timestamp }}"></script>
@endpush
