@extends('backend.layouts.app')

@section('title', 'Roles & Permissions')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Employee Management System</p>
            <h2 class="admin-title mb-1">Roles &amp; Permissions</h2>
            <p class="mb-0 text-secondary">Create roles and assign module permissions. Employees inherit permissions from their assigned role.</p>
        </div>
        <button type="button" class="btn btn-primary ems-btn-primary" id="openRoleModalBtn">
            <i class="fa-solid fa-shield-halved me-2"></i> Add Role
        </button>
    </div>

    <div class="chart-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-1">Permission matrix reference</h5>
                <small class="text-secondary">Each module supports: Add, Read, Write, Delete, Approve</small>
            </div>
        </div>
        <div class="table-responsive ems-matrix">
            <table class="table table-sm align-middle mb-0">
                <thead>
                <tr>
                    <th>Module</th>
                    <th>Add</th>
                    <th>Read</th>
                    <th>Write</th>
                    <th>Delete</th>
                    <th>Approve</th>
                </tr>
                </thead>
                <tbody>
                @foreach($modules as $slug => $label)
                    <tr>
                        <td class="fw-semibold">{{ $label }}</td>
                        @foreach($actions as $action)
                            <td><span class="ems-tick">✓</span></td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <p class="small text-secondary mb-0 mt-2">Actual access is controlled per role using the checkboxes below when creating or editing a role.</p>
    </div>

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="fa-solid fa-list me-2" style="color: #1976d2;"></i>Roles listing
            </h5>
        </div>
        <div id="roleAlert" class="alert d-none" role="alert"></div>
        <div class="table-responsive ems-roles-table">
            <table id="rolesTable" class="table table-hover align-middle w-100">
                <thead class="table-light">
                <tr>
                    <th class="fw-semibold">Name</th>
                    <th class="fw-semibold">Permissions</th>
                    <th class="fw-semibold">Created</th>
                    <th class="fw-semibold text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header border-bottom-0 pb-0">
                <div>
                    <h5 class="modal-title mb-1" id="roleModalTitle">
                        <i class="fa-solid fa-shield-halved me-2"></i>Add Role
                    </h5>
                    <p class="small text-secondary mb-0">Configure role name and assign module permissions</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="roleForm" method="POST" action="{{ route('admin.roles.store') }}" novalidate>
                @csrf
                <input type="hidden" id="roleId" name="role_id" value="">
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Role Name</label>
                            <input type="text" name="name" id="roleName" class="form-control form-control-lg" placeholder="e.g. Sub Admin, Manager, Editor">
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-semibold mb-3">
                            <i class="fa-solid fa-lock me-2"></i>Module Permissions
                        </h6>
                        <p class="small text-secondary mb-3">Select permissions for each module</p>
                    </div>

                    <div class="table-responsive ems-perm-table-wrap">
                        <table class="table table-hover align-middle ems-perm-table">
                            <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Module</th>
                                @foreach($actions as $action)
                                    <th class="text-center fw-semibold text-capitalize">
                                        <span class="badge bg-light text-dark">{{ $action }}</span>
                                    </th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($modules as $slug => $label)
                                <tr>
                                    <td class="fw-semibold">
                                        <i class="fa-solid fa-cube me-2 text-primary"></i>{{ $label }}
                                    </td>
                                    @foreach($actions as $action)
                                        <td class="text-center">
                                            <input type="hidden" name="permissions[{{ $slug }}][{{ $action }}]" value="0">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input js-perm-check" type="checkbox" name="permissions[{{ $slug }}][{{ $action }}]" id="perm_{{ $slug }}_{{ $action }}" value="1">
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 mt-3 gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="roleSubmitBtn" class="btn btn-primary ems-btn-primary">
                        <i class="fa-solid fa-check me-2"></i>
                        <span class="btn-text">Save Role</span>
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
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/admin-roles.js') }}?v={{ now()->timestamp }}"></script>
@endpush
