@extends('backend.layouts.app')

@section('title', 'Employees')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Employee Management System</p>
            <h2 class="admin-title mb-1">Employees</h2>
            <p class="mb-0 text-secondary">Create employees and assign a Spatie role — module permissions apply automatically from that role.</p>
        </div>
        <button type="button" class="btn btn-primary ems-btn-primary" id="openEmployeeModalBtn">
            <i class="fa-solid fa-user-plus me-2"></i> Add Employee
        </button>
    </div>

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">Employee listing</h5>
        </div>
        <div id="employeeAlert" class="alert d-none" role="alert"></div>
        <div class="table-responsive">
            <table id="employeesTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeModalTitle">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="employeeForm" method="POST" action="{{ route('admin.employees.store') }}" novalidate>
                @csrf
                <input type="hidden" id="employeeId" name="employee_id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="employeeName" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="employeeEmail" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone number</label>
                            <input type="text" name="phone_number" id="employeePhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign role</label>
                            <select name="role_id" id="employeeRoleId" class="form-select">
                                <option value="">Select role</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" id="employeePassword" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm password</label>
                            <input type="password" name="password_confirmation" id="employeePasswordConfirmation" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="employeeStatus" checked>
                                <label class="form-check-label" for="employeeStatus">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="employeeSubmitBtn" class="btn btn-primary ems-btn-primary">
                        <span class="btn-text">Save Employee</span>
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
<script src="{{ asset('assets/js/admin-employees.js') }}?v={{ now()->timestamp }}"></script>
@endpush
