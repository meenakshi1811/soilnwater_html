@extends('backend.layouts.app')

@section('title', 'Child Profiles')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Parent Management</p>
            <h2 class="admin-title mb-1">Child Profiles</h2>
            <p class="mb-0 text-secondary">Review child profiles created by parents. Approve or decline before children can sign in.</p>
        </div>
    </div>

    <div class="chart-card">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary js-filter-status" data-status="">All</button>
            <button type="button" class="btn btn-sm btn-outline-warning js-filter-status" data-status="pending">Pending</button>
            <button type="button" class="btn btn-sm btn-outline-success js-filter-status" data-status="approved">Approved</button>
            <button type="button" class="btn btn-sm btn-outline-danger js-filter-status" data-status="rejected">Declined</button>
        </div>
        <div class="table-responsive">
            <table id="childProfilesTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Child</th>
                    <th>Parent</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Class / Board</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function ($) {
    if (!$ || !$('#childProfilesTable').length) return;

    function toast(type, message) {
        if (window.toastr) toastr[type === 'success' ? 'success' : 'error'](message);
        else alert(message);
    }

    var statusFilter = '';
    var table = $('#childProfilesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: @json(route('admin.child-profiles.data')),
            data: function (d) { d.status = statusFilter; }
        },
        columns: [
            { data: 'child_name', name: 'full_name' },
            { data: 'parent_name', orderable: false },
            { data: 'email_display', name: 'email' },
            { data: 'phone_display', name: 'phone_number' },
            { data: 'class_display', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']]
    });

    $('.js-filter-status').on('click', function () {
        statusFilter = $(this).data('status') || '';
        $('.js-filter-status').removeClass('active btn-warning btn-success btn-danger').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active');
        table.ajax.reload();
    });

    $(document).on('click', '.js-approve-child', function () {
        var id = $(this).data('id');
        $.post(@json(url('/admin/child-profiles')) + '/' + id + '/approve', { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(function (r) { toast('success', r.message); table.ajax.reload(null, false); })
            .fail(function () { toast('error', 'Unable to approve child profile.'); });
    });

    $(document).on('click', '.js-reject-child', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Decline child profile?',
            input: 'textarea',
            inputLabel: 'Reason (optional)',
            inputPlaceholder: 'Enter reason for declining...',
            showCancelButton: true,
            confirmButtonText: 'Decline',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post(@json(url('/admin/child-profiles')) + '/' + id + '/reject', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                reason: result.value || ''
            }).done(function (r) {
                toast('success', r.message);
                table.ajax.reload(null, false);
            }).fail(function (xhr) {
                toast('error', xhr.responseJSON?.message || 'Unable to decline child profile.');
            });
        });
    });

    $(document).on('click', '.js-delete-child', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete child profile?',
            text: 'This will permanently remove the child profile and login account.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: @json(url('/admin/child-profiles')) + '/' + id,
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'DELETE' },
                headers: { Accept: 'application/json' }
            }).done(function (r) {
                toast('success', r.message);
                table.ajax.reload(null, false);
            }).fail(function () {
                toast('error', 'Unable to delete child profile.');
            });
        });
    });
})(window.jQuery);
</script>
@endpush
