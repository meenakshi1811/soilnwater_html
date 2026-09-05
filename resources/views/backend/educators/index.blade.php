@extends('backend.layouts.app')

@section('title', 'Teachers & Tutors')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Educator Management</p>
            <h2 class="admin-title mb-1">Teachers & Tutors</h2>
            <p class="mb-0 text-secondary">Review educator registrations, approve accounts, and manage profiles.</p>
        </div>
        @include('backend.partials.create-account-button', [
            'role' => 'teacher',
            'label' => 'Add Teacher / Tutor',
            'modalTitle' => 'Add Teacher / Tutor',
        ])
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="educatorsTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
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
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('backend.partials.create-account-scripts')
<script>
(function ($) {
    if (!$ || !$('#educatorsTable').length) return;
    var table = $('#educatorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: '{{ route('admin.educators.data') }}' },
        columns: [
            { data: 'name', name: 'display_name' },
            { data: 'type_label', name: 'type' },
            { data: 'email_display', name: 'email', orderable: false },
            { data: 'phone_display', name: 'phone', orderable: false },
            { data: 'city_display', name: 'city' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']]
    });

    function toast(type, message) {
        if (window.FormHelper?.showToast) FormHelper.showToast(type === 'success' ? 'success' : 'danger', message);
        else if (window.toastr) toastr[type]?.(message);
        else alert(message);
    }

    $(document).on('click', '.js-approve-educator', function () {
        var id = $(this).data('id');
        $.post(@json(url('/admin/educators')) + '/' + id + '/approve', { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(function (r) { toast('success', r.message); table.ajax.reload(null, false); })
            .fail(function () { toast('error', 'Unable to approve educator.'); });
    });

    $(document).on('click', '.js-reject-educator', function () {
        var id = $(this).data('id');
        var submitReject = function (reason) {
            $.post(@json(url('/admin/educators')) + '/' + id + '/reject', { _token: $('meta[name="csrf-token"]').attr('content'), reason: reason })
                .done(function (r) { toast('success', r.message); table.ajax.reload(null, false); })
                .fail(function (xhr) {
                    toast('error', xhr.responseJSON?.errors?.reason?.[0] || xhr.responseJSON?.message || 'Unable to reject.');
                });
        };
        if (window.Swal) {
            Swal.fire({
                title: 'Reject this educator?',
                input: 'textarea',
                inputLabel: 'Rejection reason',
                inputPlaceholder: 'Explain why this application is being rejected...',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#dc3545',
                inputValidator: function (value) {
                    if (!value || String(value).trim().length < 5) return 'Reason must be at least 5 characters.';
                }
            }).then(function (result) {
                if (result.isConfirmed) submitReject(String(result.value || '').trim());
            });
        } else {
            var reason = prompt('Enter rejection reason (min 5 characters):');
            if (reason && reason.trim().length >= 5) submitReject(reason.trim());
        }
    });

    $(document).on('click', '.js-delete-educator', function () {
        if (!confirm('Delete this educator permanently?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: @json(url('/admin/educators')) + '/' + id,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).done(function (r) {
            toast('success', r.message);
            table.ajax.reload(null, false);
        }).fail(function () { toast('error', 'Unable to delete educator.'); });
    });
})(window.jQuery);
</script>
@endpush
