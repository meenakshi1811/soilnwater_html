(function ($) {
    if (!$ || !$.fn.DataTable || !$('#offerReportsTable').length) {
        return;
    }

    $('#offerReportsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/admin/offers/reports/data'
        },
        columns: [
            { data: 'offer_title', name: 'offer.title' },
            { data: 'reporter_name', name: 'reporter.full_name', orderable: false },
            { data: 'reason', name: 'reason' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[3, 'desc']]
    });
})(window.jQuery);
