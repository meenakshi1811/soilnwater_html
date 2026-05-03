(function ($) {
    if (!$ || !$.fn.DataTable || !$('#adReportsTable').length) {
        return;
    }

    $('#adReportsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/admin/ads/reports/data'
        },
        columns: [
            { data: 'ad_title', name: 'ad.title' },
            { data: 'reporter_name', name: 'reporter.full_name', orderable: false },
            { data: 'reason', name: 'reason' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[3, 'desc']]
    });
})(window.jQuery);
