(function ($) {
    if (!$) {
        return;
    }

    $('#premiumPaymentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: '/admin/premium-payments/data' },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'profile_type_label', name: 'profile_type', orderable: false, searchable: false },
            { data: 'expected_amount_display', name: 'expected_amount', orderable: false, searchable: false },
            { data: 'profile_name', name: 'profile_id', orderable: false },
            { data: 'user_name', name: 'user.name', orderable: false },
            { data: 'user_email', name: 'user.email', orderable: false },
            { data: 'transaction_reference_display', name: 'transaction_reference', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'submitted_at', name: 'submitted_at' },
            { data: 'reviewed_display', name: 'reviewed_at', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[8, 'desc']]
    });
})(window.jQuery);
