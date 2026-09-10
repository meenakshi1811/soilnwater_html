@extends('backend.layouts.app')

@section('title', 'Parent Profiles')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Parent Management</p>
            <h2 class="admin-title mb-1">Parent Profiles</h2>
            <p class="mb-0 text-secondary">Users who enabled parent/guardian profiles and manage child accounts.</p>
        </div>
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="parentProfilesTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Location</th>
                    <th>Children</th>
                    <th>Completion</th>
                    <th>Enabled</th>
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
    if (!$ || !$('#parentProfilesTable').length) return;

    function toast(type, message) {
        if (window.toastr) toastr[type === 'success' ? 'success' : 'error'](message);
        else alert(message);
    }

    var table = $('#parentProfilesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: @json(route('admin.parent-profiles.data')) },
        columns: [
            { data: 'name', name: 'user.full_name' },
            { data: 'role_label', orderable: false, searchable: false },
            { data: 'email_display', orderable: false },
            { data: 'phone_display', orderable: false },
            { data: 'location_display', orderable: false },
            { data: 'children_display', orderable: false, searchable: false },
            { data: 'completion_display', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[7, 'desc']]
    });

    $(document).on('click', '.js-delete-parent-profile', function () {
        var id = $(this).data('id');
        var url = @json(url('/admin/parent-profiles')) + '/' + id;
        var submit = function () {
            $.ajax({
                url: url,
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'DELETE' },
                headers: { Accept: 'application/json' }
            }).done(function (r) {
                toast('success', r.message);
                table.ajax.reload(null, false);
            }).fail(function () {
                toast('error', 'Unable to disable parent profile.');
            });
        };

        Swal.fire({
            title: 'Disable parent profile?',
            text: 'This will disable the parent profile for this user.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, disable',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (result.isConfirmed) submit();
        });
    });
})(window.jQuery);
</script>
@endpush
