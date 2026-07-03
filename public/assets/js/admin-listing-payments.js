(function ($) {
    if (!$) {
        return;
    }

    $('#listingPaymentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: '/admin/listing-payments/data' },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'listing_type_label', name: 'listing_type', orderable: false },
            { data: 'listing_name', name: 'listing_id', orderable: false },
            { data: 'user_name', name: 'user.name', orderable: false },
            { data: 'user_email', name: 'user.email', orderable: false },
            { data: 'amount_display', name: 'amount' },
            { data: 'transaction_reference_display', name: 'transaction_reference', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'submitted_at', name: 'submitted_at' },
            { data: 'reviewed_display', name: 'reviewed_at', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[8, 'desc']]
    });
})(window.jQuery);
