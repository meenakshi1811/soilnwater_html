@extends('backend.layouts.app')

@section('title', 'Users')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<style>
    .admin-user-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        object-fit: cover;
        flex: 0 0 42px;
        border: 1px solid rgba(15, 23, 42, .08);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .08);
    }
    .admin-user-avatar-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e0f2fe, #ecfeff);
        color: #0f766e;
        font-weight: 800;
    }
    .text-bg-purple { background-color: #7c3aed; color: #fff; }
    .text-bg-teal { background-color: #0f766e; color: #fff; }
    .pac-container { z-index: 2000 !important; }
    .user-detail-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 48%, #0f766e 100%);
        border-radius: 22px;
        color: #fff;
        padding: 24px;
        position: relative;
        overflow: hidden;
    }
    .user-detail-hero::after {
        content: '';
        position: absolute;
        width: 220px;
        height: 220px;
        right: -70px;
        top: -80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .13);
    }
    .user-detail-photo {
        width: 96px;
        height: 96px;
        border-radius: 24px;
        object-fit: cover;
        border: 4px solid rgba(255, 255, 255, .45);
        background: rgba(255, 255, 255, .18);
    }
    .user-detail-photo-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
    }
    .detail-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 7px 12px;
        background: rgba(255, 255, 255, .14);
        color: #fff;
        font-size: .82rem;
        backdrop-filter: blur(10px);
    }
    .detail-section-card {
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .06);
        padding: 18px;
    }
    .detail-section-title {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .detail-section-title i { color: #2563eb; }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px;
    }
    .detail-field {
        border-radius: 14px;
        background: #f8fafc;
        padding: 12px 14px;
        min-height: 74px;
    }
    .detail-field-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #64748b;
        font-weight: 800;
        margin-bottom: 5px;
    }
    .detail-field-value {
        color: #0f172a;
        font-weight: 600;
        word-break: break-word;
    }
    .detail-image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
        gap: 12px;
    }
    .detail-image-tile {
        display: block;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, .24);
        background: #f8fafc;
        aspect-ratio: 4 / 3;
    }
    .detail-image-tile img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .2s ease;
    }
    .detail-image-tile:hover img { transform: scale(1.04); }
    .role-detail-item {
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 16px;
        padding: 14px;
        background: linear-gradient(180deg, #fff, #f8fafc);
    }
    .role-detail-thumb {
        width: 82px;
        height: 68px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, .25);
    }
</style>
@include('backend.partials.create-account-styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Employee Management System</p>
            <h2 class="admin-title mb-1">Users</h2>
            <p class="mb-0 text-secondary">View every registered account with role-specific vendor, consultant and service provider profile details.</p>
        </div>
        @include('backend.partials.create-account-button', [
            'label' => 'Add User',
            'modalTitle' => 'Add User',
        ])
    </div>

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-1">All users table entries</h5>
                <p class="text-secondary small mb-0">Use View to inspect full user, business profile, branch, media and service/product records.</p>
            </div>
        </div>
        <div id="userAlert" class="alert d-none" role="alert"></div>
        <div class="table-responsive">
            <table id="usersTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Location</th>
                    <th>DOB / Incorporation</th>
                    <th>Status</th>
                    <th class="text-center">Toggle</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('backend.partials.create-account-modal')

<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userForm" method="POST" action="#" novalidate>
                @csrf
                <input type="hidden" id="userId" name="user_id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="userName" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="userEmail" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone number</label>
                            <input type="text" name="phone_number" id="userPhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp number</label>
                            <input type="text" name="whatsapp_number" id="userWhatsapp" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="userAddress" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="userCity" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" id="userPincode" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of birth</label>
                            <input type="date" name="date_of_birth" id="userDateOfBirth" class="form-control" max="{{ now()->subYears(18)->toDateString() }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch pb-2">
                                <input class="form-check-input" type="checkbox" id="userStatus" checked>
                                <label class="form-check-label" for="userStatus">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="userSubmitBtn" class="btn btn-primary ems-btn-primary">
                        <span class="btn-text">Save User</span>
                        <span class="btn-loader d-none" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="userViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ems-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="ems-kicker mb-1">Complete account profile</p>
                    <h5 class="modal-title">User Details</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3" id="userViewContent">
                <div class="text-center py-5 text-secondary">Loading details...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    if (window.toastr) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4500
        };
    }
</script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
@include('backend.partials.create-account-scripts')
<script src="{{ asset('assets/js/admin-users.js') }}?v={{ now()->timestamp }}"></script>
@endpush
