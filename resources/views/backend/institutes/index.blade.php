@extends('backend.layouts.app')

@section('title', $pageTitle ?? 'Institutes')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">{{ $pageKicker ?? 'Institute Management' }}</p>
            <h2 class="admin-title mb-1">{{ $pageTitle ?? 'Institutes' }}</h2>
            <p class="mb-0 text-secondary">Review registrations, approve accounts, and manage public profiles.</p>
        </div>
        @include('backend.partials.create-account-button', [
            'role' => $createAccountRole ?? 'institute',
            'label' => $createAccountLabel ?? 'Add Institute',
            'modalTitle' => $createAccountLabel ?? 'Add Institute',
        ])
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="institutesTable" class="table table-bordered align-middle w-100" data-admin-base="{{ rtrim(route(($adminRoutePrefix ?? 'admin.institutes').'.index'), '/') }}">
                <thead>
                <tr>
                    <th>Institution</th>
                    <th>Type</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Public page</th>
                    <th>Registered</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@php
    $entityName = $entityLabel ?? 'Institute';
@endphp
<div class="modal fade" id="instituteEditModal" tabindex="-1" aria-labelledby="instituteEditModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="instituteEditModalTitle">{{ $entityName }} account details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="instituteEditForm" method="POST" action="#" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="instituteName">{{ $entityName }} name</label>
                            <input type="text" name="institution_name" id="instituteName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="instituteDisplayName">Display name <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" name="display_name" id="instituteDisplayName" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="instituteContact">Contact person</label>
                            <input type="text" name="contact_person" id="instituteContact" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="instituteSlug">Page address</label>
                            <input type="text" name="slug" id="instituteSlug" class="form-control" required>
                            <small class="text-muted">/{{ ($ownerRole ?? 'institute') === 'school' ? 'schools' : 'institutes' }}/<span id="instituteSlugPreview">slug</span></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="instituteType">Type</label>
                            <select name="institution_type" id="instituteType" class="form-select" required>
                                <option value="school">School</option>
                                <option value="college">College</option>
                                <option value="university">University</option>
                                <option value="coaching">Coaching Institute</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="instituteBoard">Board / affiliation</label>
                            <input type="text" name="board_affiliation" id="instituteBoard" class="form-control" placeholder="e.g. CBSE">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="instituteTagline">Tagline</label>
                            <input type="text" name="tagline" id="instituteTagline" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="instituteAbout">About</label>
                            <textarea name="about" id="instituteAbout" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="institutePhone">Phone</label>
                            <input type="tel" name="phone" id="institutePhone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="instituteWhatsapp">WhatsApp</label>
                            <input type="tel" name="whatsapp" id="instituteWhatsapp" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="instituteEmail">Email</label>
                            <input type="email" name="email" id="instituteEmail" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="instituteWebsite">Website <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="url" name="website_url" id="instituteWebsite" class="form-control" placeholder="https://">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="instituteAddress">Address</label>
                            <input type="text" name="address" id="instituteAddress" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="instituteCity">City</label>
                            <input type="text" name="city" id="instituteCity" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="instituteState">State</label>
                            <input type="text" name="state" id="instituteState" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="institutePincode">Pincode</label>
                            <input type="text" name="pincode" id="institutePincode" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="instituteFounded">Founded on</label>
                            <input type="date" name="date_of_establishment" id="instituteFounded" class="form-control" max="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="institutePan">PAN</label>
                            <input type="text" name="pan_number" id="institutePan" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="instituteGst">GST <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" name="gst_number" id="instituteGst" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="instituteCertificate">Government certificate number <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" name="government_certificate_number" id="instituteCertificate" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="instituteEditSubmitBtn" class="btn btn-primary ems-btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('backend.partials.create-account-modal')
@endsection

@push('styles')
@include('backend.partials.create-account-styles')
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
@include('backend.partials.create-account-scripts')
<script src="{{ asset('assets/js/admin-institutes.js') }}?v={{ now()->timestamp }}"></script>
@endpush
